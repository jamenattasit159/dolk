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
    // แสดงราคาเฉลี่ยหรือราคาล่าสุดเพื่อให้เห็นภาพคร่าวๆ
    $sql = "SELECT i.itemid, i.itemname, i.type, i.unit,
            COALESCE(SUM(s.qty_remain), 0) as qty, 
            (SELECT lot_price FROM stock_lots WHERE itemid = i.itemid AND qty_remain > 0 ORDER BY date_in ASC LIMIT 1) as current_price
            FROM items i
            LEFT JOIN stock_lots s ON i.itemid = s.itemid
            GROUP BY i.itemid, i.itemname, i.type
            ORDER BY i.itemid ASC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($action == 'add_item' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    try {
        $pdo->beginTransaction();

        // เช็ค Master Item
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM items WHERE itemid = ?");
        $stmtCheck->execute([$data['itemid']]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsertItem = $pdo->prepare("INSERT INTO items (itemid, itemname, unit, type) VALUES (?, ?, ?, ?)");
            $stmtInsertItem->execute([$data['itemid'], $data['itemname'], $data['unit'], $data['type']]);
        }

        // Insert Lot (รองรับทศนิยม)
        $sqlLot = "INSERT INTO stock_lots (itemid, lot_price, qty_initial, qty_remain, doc_no, date_in) 
                   VALUES (:id, :price, :qty, :qty, :doc, NOW())";
        $stmtLot = $pdo->prepare($sqlLot);
        $stmtLot->execute([
            ':id' => $data['itemid'],
            ':price' => floatval($data['price']), // แปลงเป็น float
            ':qty' => intval($data['qty']),
            ':doc' => $data['doc_no']
        ]);

        // Update cache qty
        $pdo->exec("UPDATE items SET qty = (SELECT SUM(qty_remain) FROM stock_lots WHERE itemid = '{$data['itemid']}') WHERE itemid = '{$data['itemid']}'");

        $pdo->commit();
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} // 4. บันทึกการเบิก (Withdraw) + ตัดสต็อก (แก้ไขสมบูรณ์)
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
    $sql = "SELECT t.tid, t.trx_date, t.qty_withdraw, t.total_cost,
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
    try {
        $sql = "UPDATE items SET itemname = :name, type = :type, unit = :unit WHERE itemid = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['itemname'],
            ':type' => $data['type'],
            ':unit' => $data['unit'],
            ':id' => $data['itemid']
        ]);
        echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

// 7. ลบวัสดุ (Delete Item)
elseif ($action == 'delete_item' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $itemid = $data['itemid'];

    try {
        // ตรวจสอบก่อนว่ามี Transaction หรือ Stock หรือไม่ ถ้ามีห้ามลบ
        $check = $pdo->prepare("SELECT COUNT(*) FROM stock_lots WHERE itemid = ?");
        $check->execute([$itemid]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("ลบไม่ได้! เนื่องจากมีการรับเข้าสต็อกแล้ว");
        }

        // ถ้าไม่มีข้อมูลสัมพันธ์ ก็ลบได้เลย
        $stmt = $pdo->prepare("DELETE FROM items WHERE itemid = ?");
        $stmt->execute([$itemid]);
        echo json_encode(["success" => true, "message" => "ลบรายการเรียบร้อย"]);
    } catch (Exception $e) {
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
}
?>