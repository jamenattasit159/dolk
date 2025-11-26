<?php
header("Content-Type: application/json; charset=UTF-8");
$host = 'localhost';
$db_name = 'dolapidata';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_members') {
    $stmt = $pdo->query("SELECT * FROM members ORDER BY name ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($action == 'get_items') {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $type = isset($_GET['type']) ? $_GET['type'] : '';

    // เริ่มต้น SQL หลัก
    $sql = "SELECT i.itemid, i.itemname, i.type, i.unit,i.spec, i.storage_location,
            COALESCE(SUM(s.qty_remain), 0) as qty, 
            (SELECT lot_price FROM stock_lots WHERE itemid = i.itemid AND qty_remain > 0 ORDER BY date_in ASC LIMIT 1) as current_price
            FROM items i
            LEFT JOIN stock_lots s ON i.itemid = s.itemid";

    $conditions = [];
    $params = [];

    // ถ้ามีการค้นหา (ชื่อ หรือ รหัส)
    if (!empty($search)) {
        $conditions[] = "(i.itemname LIKE ? OR i.itemid LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // ถ้ามีการเลือกประเภท
    if (!empty($type)) {
        $conditions[] = "i.type = ?";
        $params[] = $type;
    }

    // ประกอบร่าง WHERE clause
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // ต่อท้ายด้วย Group By และ Order By
    $sql .= " GROUP BY i.itemid, i.itemname, i.type, i.spec, i.storage_location ORDER BY i.itemid ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($action == 'add_item' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // รับค่า Spec และ Storage (ถ้าไม่มีให้ Default)
    $spec = isset($data['spec']) ? $data['spec'] : '-';
    $storage = isset($data['storage_location']) ? $data['storage_location'] : 1;

    try {
        $pdo->beginTransaction();

        // 1. ตรวจสอบ Master Item
        $stmtCheck = $pdo->prepare("SELECT * FROM items WHERE itemid = ?");
        $stmtCheck->execute([$data['itemid']]);
        $existingItem = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // [กรณีของเดิม] เช็คว่าชื่อตรงกันไหม
            if (trim($existingItem['itemname']) !== trim($data['itemname'])) {
                echo json_encode([
                    "success" => false,
                    "error" => "ชื่อรายการไม่ตรงกับฐานข้อมูล! รหัส " . $data['itemid'] . " คือ \"" . $existingItem['itemname'] . "\""
                ]);
                $pdo->rollBack();
                exit;
            }
            // ถ้าชื่อตรงกัน ก็ข้ามไปเพิ่ม Lot เลย (ไม่แก้ไข Master Data เดิม)
        } else {
            // [กรณีของใหม่] Insert ลงตาราง items
            $stmtInsertItem = $pdo->prepare("INSERT INTO items (itemid, itemname, unit, type, spec, storage_location) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsertItem->execute([
                $data['itemid'],
                $data['itemname'],
                $data['unit'],
                $data['type'],
                $spec,
                $storage
            ]);
        }

        // 2. Insert Lot (เพิ่มสต็อก)
        $sqlLot = "INSERT INTO stock_lots (itemid, lot_price, qty_initial, qty_remain, doc_no, date_in) 
                   VALUES (:id, :price, :qty, :qty, :doc, :custom_date)";
        $stmtLot = $pdo->prepare($sqlLot);
        $stmtLot->execute([
            ':id' => $data['itemid'],
            ':price' => floatval($data['price']),
            ':qty' => intval($data['qty']),
            ':doc' => $data['doc_no'],
            ':custom_date' => $data['custom_date']
        ]);

        // 3. Update cache qty
        $pdo->exec("UPDATE items SET qty = (SELECT SUM(qty_remain) FROM stock_lots WHERE itemid = '{$data['itemid']}') WHERE itemid = '{$data['itemid']}'");

        $pdo->commit();
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }

}// 4. บันทึกการเบิก (Withdraw) + ตัดสต็อก (แก้ไขสมบูรณ์)
elseif ($action == 'withdraw' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $eid = $data['eid'];
    $itemid = $data['itemid'];
    $qty_needed = intval($data['qty']);

    // [เพิ่ม 1] ประกาศตัวแปรเลขที่เอกสาร และ วันที่
    $doc_input = !empty($data['doctax_no']) ? $data['doctax_no'] : '-';
    $dateTrans = !empty($data['custom_date']) ? $data['custom_date'] . ' 10:00:00' : date('Y-m-d H:i:s');

    try {
        $pdo->beginTransaction();

        // 1. ดึง Lot ตาม FIFO
        $stmt = $pdo->prepare("SELECT * FROM stock_lots WHERE itemid = ? AND qty_remain > 0 ORDER BY date_in ASC");
        $stmt->execute([$itemid]);
        $lots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (array_sum(array_column($lots, 'qty_remain')) < $qty_needed) {
            throw new Exception("ของในสต็อกไม่พอ");
        }

        // 2. สร้าง Transaction หลัก
        // [แก้ SQL] เปลี่ยน NOW() เป็น ? เพื่อรับค่าวันที่ custom ได้
        // SQL นี้มีเครื่องหมาย ? ทั้งหมด 5 ตัว (eid, itemid, doctax, qty, date)
        $insertTrans = "INSERT INTO transactions (eid, itemid, doctax_no, qty_withdraw, total_cost, trx_date) 
                        VALUES (?, ?, ?, ?, 0, ?)";

        $pdo->prepare($insertTrans)->execute([
            $eid,           // 1
            $itemid,        // 2
            $doc_input,     // 3 (ตัวแปรนี้ต้องประกาศไว้ข้างบนก่อนใช้)
            $qty_needed,    // 4
            $dateTrans      // 5 (ตัวแปรนี้ต้องประกาศไว้ข้างบนก่อนใช้)
        ]);

        $tid = $pdo->lastInsertId();

        $qty_to_deduct = $qty_needed;
        $total_withdraw_value = 0.0;

        // 3. ลูปตัด Lot และบันทึก Detail
        foreach ($lots as $lot) {
            if ($qty_to_deduct <= 0)
                break;

            $can_take = min($qty_to_deduct, $lot['qty_remain']);
            $lot_cost = $can_take * floatval($lot['lot_price']);
            $total_withdraw_value += $lot_cost;

            // ตัดสต็อก
            $updateLot = $pdo->prepare("UPDATE stock_lots SET qty_remain = qty_remain - ? WHERE lot_id = ?");
            $updateLot->execute([$can_take, $lot['lot_id']]);

            // บันทึก Log FIFO
            $insertDetail = "INSERT INTO transaction_details (tid, lot_id, qty_deducted, current_price) VALUES (?, ?, ?, ?)";
            $pdo->prepare($insertDetail)->execute([$tid, $lot['lot_id'], $can_take, $lot['lot_price']]);

            $qty_to_deduct -= $can_take;
        }

        // อัปเดตราคารวมกลับไปที่ Transaction หลัก
        $pdo->prepare("UPDATE transactions SET total_cost = ? WHERE tid = ?")->execute([$total_withdraw_value, $tid]);

        // Update cache qty
        $pdo->exec("UPDATE items SET qty = (SELECT SUM(qty_remain) FROM stock_lots WHERE itemid = '$itemid') WHERE itemid = '$itemid'");

        $pdo->commit();
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }

} elseif ($action == 'get_history') {
    // JOIN เพื่อดึงรายละเอียดการตัดสต็อก (ใช้ GROUP_CONCAT เพื่อรวมหลาย Row เป็น 1 บรรทัด)
    $sql = "SELECT t.tid, t.trx_date, t.qty_withdraw, t.total_cost,t.doctax_no,
                   m.name AS member_name, m.department, 
                   i.itemname, i.type,
                   GROUP_CONCAT(
                       CONCAT('ล็อต ', s.doc_no, ': ', td.qty_deducted, ' ชิ้น (@', td.current_price, ')') 
                       SEPARATOR '<br>'
                   ) as fifo_details
            FROM transactions t
            JOIN members m ON t.eid = m.eid
            JOIN items i ON t.itemid = i.itemid
            LEFT JOIN transaction_details td ON t.tid = td.tid
            LEFT JOIN stock_lots s ON td.lot_id = s.lot_id
            GROUP BY t.tid
            ORDER BY t.trx_date DESC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// 6. แก้ไขข้อมูลวัสดุ (Update Item Info)
elseif ($action == 'update_item' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $itemid = $data['itemid'];
    $new_qty = isset($data['qty']) ? intval($data['qty']) : null; // รับค่าจำนวนใหม่

    try {
        $pdo->beginTransaction();

        // 1. อัปเดตข้อมูลทั่วไป (ชื่อ, ประเภท, หน่วย)
        $sql = "UPDATE items SET itemname = :name, type = :type, unit = :unit WHERE itemid = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['itemname'],
            ':type' => $data['type'],
            ':unit' => $data['unit'],
            ':id' => $itemid
        ]);

        // 2. ตรวจสอบและปรับปรุงสต็อก (ถ้ามีการส่งค่า qty มา)
        if ($new_qty !== null) {
            // ดึงยอดปัจจุบัน
            $stmtCheck = $pdo->prepare("SELECT qty FROM items WHERE itemid = ?");
            $stmtCheck->execute([$itemid]);
            $current_qty = intval($stmtCheck->fetchColumn());

            $diff = $new_qty - $current_qty;

            if ($diff != 0) {
                if ($diff > 0) {
                    // กรณีเพิ่มขึ้น: สร้าง Lot ใหม่ (Correction Add)
                    // ดึงราคาล่าสุดมาใช้ หรือใช้ 0 ถ้าไม่มี
                    $stmtPrice = $pdo->prepare("SELECT lot_price FROM stock_lots WHERE itemid = ? ORDER BY date_in DESC LIMIT 1");
                    $stmtPrice->execute([$itemid]);
                    $last_price = $stmtPrice->fetchColumn() ?: 0;

                    $sqlLot = "INSERT INTO stock_lots (itemid, lot_price, qty_initial, qty_remain, doc_no, date_in) 
                               VALUES (?, ?, ?, ?, 'แก้ไขยอด', NOW())";
                    $pdo->prepare($sqlLot)->execute([$itemid, $last_price, $diff, $diff]);

                } else {
                    // กรณีลดลง: ตัดออกจาก Lot ล่าสุดย้อนกลับไป (LIFO Correction)
                    // ที่ใช้ LIFO เพราะสมมติว่าเพิ่งคีย์ผิด ก็ควรไปแก้ Lot ที่เพิ่งเข้า
                    $remove_qty = abs($diff);

                    $stmtLots = $pdo->prepare("SELECT * FROM stock_lots WHERE itemid = ? AND qty_remain > 0 ORDER BY date_in DESC");
                    $stmtLots->execute([$itemid]);
                    $lots = $stmtLots->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($lots as $lot) {
                        if ($remove_qty <= 0)
                            break;

                        $can_deduct = min($remove_qty, $lot['qty_remain']);

                        $updateLot = $pdo->prepare("UPDATE stock_lots SET qty_remain = qty_remain - ? WHERE lot_id = ?");
                        $updateLot->execute([$can_deduct, $lot['lot_id']]);

                        $remove_qty -= $can_deduct;
                    }
                }

                // อัปเดต Cache ยอดรวมในตาราง items ทันที
                $pdo->exec("UPDATE items SET qty = (SELECT SUM(qty_remain) FROM stock_lots WHERE itemid = '$itemid') WHERE itemid = '$itemid'");
            }
        }

        $pdo->commit();
        echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} elseif ($action == 'cancel_withdraw' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $tid = $data['tid'];

    try {
        $pdo->beginTransaction();

        // 1. ดึงข้อมูล Item ID ของ Transaction นี้มาก่อน (เพื่อเอาไปอัปเดต Cache ตอนจบ)
        $stmtGetItem = $pdo->prepare("SELECT itemid FROM transactions WHERE tid = ?");
        $stmtGetItem->execute([$tid]);
        $itemid = $stmtGetItem->fetchColumn();

        if (!$itemid)
            throw new Exception("ไม่พบรายการนี้ในระบบ");

        // 2. ดึงรายละเอียดการตัด Lot (Transaction Details) เพื่อคืนของกลับเข้า Lot เดิม
        $stmtDetails = $pdo->prepare("SELECT lot_id, qty_deducted FROM transaction_details WHERE tid = ?");
        $stmtDetails->execute([$tid]);
        $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $detail) {
            // คืนยอดกลับเข้า Stock Lots (Reverse Stock)
            $stmtReturn = $pdo->prepare("UPDATE stock_lots SET qty_remain = qty_remain + ? WHERE lot_id = ?");
            $stmtReturn->execute([$detail['qty_deducted'], $detail['lot_id']]);
        }

        // 3. ลบข้อมูลใน transaction_details (ลูก)
        $pdo->prepare("DELETE FROM transaction_details WHERE tid = ?")->execute([$tid]);

        // 4. ลบข้อมูลใน transactions (แม่)
        $pdo->prepare("DELETE FROM transactions WHERE tid = ?")->execute([$tid]);

        // 5. อัปเดตยอดรวมคงเหลือในตาราง items (Cache)
        $pdo->exec("UPDATE items SET qty = (SELECT SUM(qty_remain) FROM stock_lots WHERE itemid = '$itemid') WHERE itemid = '$itemid'");

        $pdo->commit();
        echo json_encode(["success" => true, "message" => "ยกเลิกรายการและคืนสต็อกเรียบร้อยแล้ว"]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} elseif ($action == 'get_item_lots') {
    $itemid = $_GET['itemid'];

    try {
        // 1. ดึงข้อมูลสินค้าหลัก
        $stmtItem = $pdo->prepare("SELECT * FROM items WHERE itemid = ?");
        $stmtItem->execute([$itemid]);
        $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

        // 2. ดึงประวัติ Lot ทั้งหมด (เรียงจากล่าสุดไปเก่าสุด)
        $stmtLots = $pdo->prepare("SELECT * FROM stock_lots WHERE itemid = ? ORDER BY date_in DESC");
        $stmtLots->execute([$itemid]);
        $lots = $stmtLots->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'item' => $item, 'lots' => $lots]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($action == 'get_dashboard_data') {
    try {
        // 1. สรุปตัวเลข (เหมือนเดิม)
        $totalItems = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
        $lowStock = $pdo->query("SELECT COUNT(*) FROM items WHERE qty < 5")->fetchColumn();
        $totalValue = $pdo->query("SELECT SUM(qty_remain * lot_price) FROM stock_lots")->fetchColumn();
        $todayTrx = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(trx_date) = CURDATE()")->fetchColumn();

        // 2. กราฟเบิกจ่าย 7 วัน (เหมือนเดิม)
        $sqlChart = "SELECT DATE_FORMAT(trx_date, '%d/%m') as date_label, SUM(qty_withdraw) as total_qty 
                     FROM transactions WHERE trx_date >= DATE(NOW()) - INTERVAL 6 DAY 
                     GROUP BY DATE(trx_date) ORDER BY trx_date ASC";
        $chartData = $pdo->query($sqlChart)->fetchAll(PDO::FETCH_ASSOC);

        // [NEW] 3. Top 5 สินค้ายอดฮิต (เบิกเยอะสุด)
        $sqlTop = "SELECT i.itemname, SUM(t.qty_withdraw) as total_qty 
                   FROM transactions t JOIN items i ON t.itemid = i.itemid 
                   GROUP BY t.itemid ORDER BY total_qty DESC LIMIT 5";
        $topItems = $pdo->query($sqlTop)->fetchAll(PDO::FETCH_ASSOC);

        // [NEW] 4. สัดส่วนประเภทสินค้า (Pie Chart)
        $sqlPie = "SELECT type, COUNT(*) as count FROM items GROUP BY type";
        $pieData = $pdo->query($sqlPie)->fetchAll(PDO::FETCH_ASSOC);

        // [NEW] 5. รายการเคลื่อนไหวล่าสุด 5 รายการ (Recent Activity)
        $sqlRecent = "SELECT t.trx_date, m.name as member_name, m.department, i.itemname, t.qty_withdraw 
                      FROM transactions t 
                      JOIN members m ON t.eid = m.eid 
                      JOIN items i ON t.itemid = i.itemid 
                      ORDER BY t.trx_date DESC LIMIT 5";
        $recentData = $pdo->query($sqlRecent)->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'summary' => ['items' => $totalItems, 'low' => $lowStock, 'value' => $totalValue ?: 0, 'today' => $todayTrx],
            'chart' => $chartData,
            'top_items' => $topItems,
            'pie_data' => $pieData,
            'recent' => $recentData
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}// ในไฟล์ api.php หา action == 'delete_item' แล้วแก้เป็นแบบนี้ครับ
elseif ($action == 'delete_item' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $itemid = $data['itemid'];
    $confirm_code = isset($data['confirm_code']) ? $data['confirm_code'] : '';

    try {
        // กรณี 1: พิมพ์รหัสยืนยันถูกต้อง (Force Delete)
        if ($confirm_code === 'angthongdol') {
            $pdo->beginTransaction();

            // 1. ลบ Transactions (ประวัติการเบิก) ของสินค้านี้ก่อน 
            // (เพราะใน SQL ไม่มี ON DELETE CASCADE ที่ตาราง transactions)
            $stmtDelTrans = $pdo->prepare("DELETE FROM transactions WHERE itemid = ?");
            $stmtDelTrans->execute([$itemid]);

            // 2. ลบ Item (Stock Lots จะหายไปเองเพราะมี ON DELETE CASCADE ใน DB)
            $stmtDelItem = $pdo->prepare("DELETE FROM items WHERE itemid = ?");
            $stmtDelItem->execute([$itemid]);

            $pdo->commit();
            echo json_encode(["success" => true, "message" => "ลบรายการและประวัติทั้งหมดเรียบร้อย"]);
        }
        // กรณี 2: ไม่ได้พิมพ์รหัส หรือพิมพ์ผิด (ใช้ Logic เดิม)
        else {
            // ตรวจสอบว่ามีสต็อกหรือไม่
            $check = $pdo->prepare("SELECT COUNT(*) FROM stock_lots WHERE itemid = ?");
            $check->execute([$itemid]);
            if ($check->fetchColumn() > 0) {
                // แจ้งเตือนให้ผู้ใช้รู้ว่าต้องใส่รหัส
                throw new Exception("ลบไม่ได้! มีประวัติสต็อก (กรุณาพิมพ์รหัสยืนยัน angthongdol เพื่อลบ)");
            }

            // ถ้าไม่มีสต็อก ก็ลบตามปกติ
            $stmt = $pdo->prepare("DELETE FROM items WHERE itemid = ?");
            $stmt->execute([$itemid]);
            echo json_encode(["success" => true, "message" => "ลบรายการเรียบร้อย"]);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
// แก้ไขส่วนนี้ใน api.php (หรือเพิ่มใหม่ถ้ายังไม่มี)
elseif ($action == 'update_lot' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $lot_id = $data['lot_id'];
    $itemid = $data['itemid'];
    $date_in = $data['date_in'];
    $doc_no = $data['doc_no'];
    $lot_price = $data['lot_price'];
    $qty_initial = $data['qty_initial']; // [รับค่าใหม่]
    $qty_remain = $data['qty_remain'];

    try {
        $pdo->beginTransaction();

        // เพิ่ม qty_initial ลงใน SQL Update
        $sql = "UPDATE stock_lots 
                SET date_in = :date_in, 
                    doc_no = :doc_no, 
                    lot_price = :price,
                    qty_initial = :qty_init,
                    qty_remain = :qty_remain 
                WHERE lot_id = :lid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':date_in' => $date_in . ' 00:00:00',
            ':doc_no' => $doc_no,
            ':price' => $lot_price,
            ':qty_init' => $qty_initial,   // [ผูกค่า]
            ':qty_remain' => $qty_remain,
            ':lid' => $lot_id
        ]);

        // อัปเดต Cache
        $pdo->exec("UPDATE items SET qty = (SELECT SUM(qty_remain) FROM stock_lots WHERE itemid = '$itemid') WHERE itemid = '$itemid'");

        $pdo->commit();
        echo json_encode(["success" => true]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>