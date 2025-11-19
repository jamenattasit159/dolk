<?php
require_once __DIR__ . '/vendor/autoload.php';

// ============================================================================
// 1. ตั้งค่าเริ่มต้น
// ============================================================================
$startDate = date('Y-m-01'); // หรือกำหนดเองเช่น '2025-01-01'

function dateThai($strDate)
{
    if (!$strDate)
        return '';
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonth = date("n", strtotime($strDate));
    $strDay = date("j", strtotime($strDate));
    $strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
    return "$strDay $strMonthCut[$strMonth] $strYear";
}

$host = 'localhost';
$db_name = 'dolapidata';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// ตั้งค่า mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L', // แนวนอน
    'tempDir' => __DIR__ . '/tmp',
    'fontDir' => array_merge((new Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [__DIR__ . '/ttfonts']),
    'fontdata' => (new Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
        'sarabun' => [
            'R' => 'THSarabun.ttf',
            'B' => 'THSarabun Bold.ttf',
            'I' => 'THSarabun Italic.ttf',
            'BI' => 'THSarabun BoldItalic.ttf'
        ]
    ],
    'default_font' => 'sarabun'
]);

// CSS
$style = '
<style>
    body { font-family: "sarabun"; font-size: 14pt; }
    table { width: 100%; border-collapse: collapse; }
    /* เส้นขอบสำหรับตารางหลัก */
    th, td { border: 1px solid #000; padding: 4px; vertical-align: top; line-height: 1.2; }
    
    .header-text { font-weight: bold; font-size: 16pt; text-align: center; margin-bottom: 5px; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bg-gray { background-color: #f0f0f0; }
    
    /* ตารางย่อยในส่วนหัว (Nested Table) จะไม่มีเส้นขอบ */
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    .info-table td { border: none; padding: 2px; font-weight: bold; vertical-align: middle; text-align: left; }
    
    .dotted-line { border-bottom: 1px dotted #000; display: inline-block; width: 95%; font-weight: normal; min-height: 18px; }
    .remark-red { color: #d00000; font-size: 10pt; }
    .text-red-bold { color: #d00000; font-weight: bold; }
    
    /* ลบเส้นขอบของ th ที่ใช้ครอบส่วนหัว */
    th.no-border { border: none; padding: 0; }
</style>
';
$mpdf->WriteHTML($style);

// ============================================================================
// 2. เริ่มประมวลผลข้อมูล
// ============================================================================
$items = $pdo->query("SELECT * FROM items ORDER BY itemid ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalItems = count($items);
$count = 0;

foreach ($items as $item) {
    $count++;
    $itemid = $item['itemid'];

    // -------------------------------------------------------
    // PART A: คำนวณยอดยกมา (Logic เดิม)
    // -------------------------------------------------------
    $stmtUsed = $pdo->prepare("SELECT SUM(qty_withdraw) FROM transactions WHERE itemid = ? AND trx_date < ?");
    $stmtUsed->execute([$itemid, $startDate]);
    $totalUsed = $stmtUsed->fetchColumn() ?: 0;

    $stmtLots = $pdo->prepare("SELECT * FROM stock_lots WHERE itemid = ? AND date_in < ? ORDER BY date_in ASC");
    $stmtLots->execute([$itemid, $startDate]);
    $lotsHistory = $stmtLots->fetchAll(PDO::FETCH_ASSOC);

    $remainingLots = [];
    $current_qty = 0;
    $current_val = 0;

    foreach ($lotsHistory as $lot) {
        $lotQty = $lot['qty_initial'];
        if ($totalUsed >= $lotQty) {
            $totalUsed -= $lotQty;
        } else {
            $remain = $lotQty - $totalUsed;
            $remainingLots[] = [
                'doc_no' => $lot['doc_no'],
                'price' => $lot['lot_price'],
                'qty' => $remain,
                'total' => $remain * $lot['lot_price']
            ];
            $current_qty += $remain;
            $current_val += ($remain * $lot['lot_price']);
            $totalUsed = 0;
        }
    }

    // -------------------------------------------------------
    // PART B: ดึงรายการเคลื่อนไหว (Logic เดิม)
    // -------------------------------------------------------
    $sqlMove = "
        (SELECT 'IN' as type, 1 as sort_order, lot_id as ref_id, date_in as trx_date, doc_no as doc_ref, 'รับจากการจัดซื้อ' as description, qty_initial as qty, lot_price as price
         FROM stock_lots WHERE itemid = :id1 AND date_in >= :d1)
        UNION
        (SELECT 'OUT' as type, 2 as sort_order, tid as ref_id, t.trx_date, t.doctax_no as doc_ref, CONCAT('จ่ายให้ ', m.name) as description, t.qty_withdraw as qty, (t.total_cost / t.qty_withdraw) as price 
         FROM transactions t LEFT JOIN members m ON t.eid = m.eid WHERE t.itemid = :id2 AND t.trx_date >= :d2)
        ORDER BY trx_date ASC, sort_order ASC
    ";

    $stmtTx = $pdo->prepare($sqlMove);
    $stmtTx->execute([':id1' => $itemid, ':id2' => $itemid, ':d1' => $startDate, ':d2' => $startDate]);
    $movements = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

    // -------------------------------------------------------
    // PART C: สร้าง HTML ตาราง
    // -------------------------------------------------------
    $html = '
    <table>
        <thead>
            <tr>
                <th colspan="11" class="no-border">
                    <div class="header-text">บัญชีวัสดุ สำนักงานที่ดินจังหวัดอ่างทอง</div>
                </th>
            </tr>
            
            <tr>
                <th colspan="11" class="no-border">
                    <table class="info-table">
                        <tr>
                            <td width="10%">ประเภท</td> 
                            <td width="25%"><span class="dotted-line">' . $item['type'] . '</span></td>
                            <td width="10%" align="right">ชื่อวัสดุ</td> 
                            <td width="30%"><span class="dotted-line">' . $item['itemname'] . '</span></td>
                            <td width="10%" align="right">รหัส</td> 
                            <td width="15%"><span class="dotted-line">' . $item['itemid'] . '</span></td>
                        </tr>
                    </table>
                </th>
            </tr>

            <tr>
                <th colspan="11" class="no-border" style="padding-bottom: 5px;">
                    <table class="info-table">
                        <tr>
                            <td width="10%">ขนาด</td> 
                            <td width="25%"><span class="dotted-line">&nbsp;</span></td>
                            <td width="10%" align="right">ที่เก็บ</td> 
                            <td width="30%"><span class="dotted-line">คลังพัสดุกลาง</span></td>
                            <td width="10%" align="right">หน่วยนับ</td> 
                            <td width="15%"><span class="dotted-line">' . ($item['unit'] ? $item['unit'] : '-') . '</span></td>
                        </tr>
                    </table>
                </th>
            </tr>

            <tr class="bg-gray">
                <th rowspan="2" width="10%">วัน เดือน ปี</th>
                <th rowspan="2" width="25%">รับจาก / จ่ายให้</th>
                <th rowspan="2" width="10%">เลขที่<br>เอกสาร</th>
                <th rowspan="2" width="8%">ราคา<br>ต่อหน่วย</th>
                <th colspan="2" width="12%">รับ</th>
                <th colspan="2" width="12%">จ่าย</th>
                <th colspan="2" width="12%">คงเหลือ</th>
                <th rowspan="2" width="11%">หมายเหตุ</th>
            </tr>
            <tr class="bg-gray">
                <th>จำนวน</th> <th>ราคา</th> <th>จำนวน</th> <th>ราคา</th> <th>จำนวน</th> <th>ราคา</th>
            </tr>
        </thead>
        
        <tbody>';

    // C1. แสดงยอดยกมา
    if (count($remainingLots) > 0) {
        foreach ($remainingLots as $index => $lot) {
            $dateText = ($index === 0) ? dateThai($startDate) : '';
            $descText = ($index === 0) ? 'ยอดยกมา' : '';
            $html .= '<tr>
                <td class="text-center">' . $dateText . '</td> 
                <td>' . $descText . '</td> 
                <td class="text-center">' . $lot['doc_no'] . '</td>
                <td class="text-right">' . number_format($lot['price'], 2) . '</td>
                <td></td> <td></td> <td></td> <td></td>
                <td class="text-right"><b>' . number_format($lot['qty']) . '</b></td>
                <td class="text-right">' . number_format($lot['total'], 2) . '</td> 
                <td></td>
            </tr>';
        }
    } else {
        $html .= '<tr>
            <td class="text-center">' . dateThai($startDate) . '</td> 
            <td>ยอดยกมา</td> <td class="text-center">-</td> <td class="text-right">-</td>
            <td></td> <td></td> <td></td> <td></td>
            <td class="text-right"><b>0</b></td> <td class="text-right">0.00</td> <td></td>
        </tr>';
    }

    // C2. แสดงรายการเคลื่อนไหว (IN/OUT)
    foreach ($movements as $row) {
        if ($row['type'] == 'IN') {
            $current_qty += $row['qty'];
            $current_val += ($row['qty'] * $row['price']);

            $html .= '<tr>
                <td class="text-center">' . dateThai($row['trx_date']) . '</td> 
                <td>' . $row['description'] . '</td> 
                <td class="text-center">' . $row['doc_ref'] . '</td>
                <td class="text-right">' . number_format($row['price'], 2) . '</td>
                <td class="text-right">' . number_format($row['qty']) . '</td>
                <td class="text-right">' . number_format($row['qty'] * $row['price'], 2) . '</td>
                <td></td> <td></td>
                <td class="text-right"><b>' . number_format($current_qty) . '</b></td>
                <td class="text-right">' . number_format($current_val, 2) . '</td> 
                <td></td>
            </tr>';
        } else {
            // OUT: ตัด FIFO
            $stmtDetail = $pdo->prepare("SELECT * FROM transaction_details WHERE tid = ? ORDER BY detail_id ASC");
            $stmtDetail->execute([$row['ref_id']]);
            $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

            if (count($details) > 0) {
                foreach ($details as $detail) {
                    $sub_qty = $detail['qty_deducted'];
                    $sub_price = $detail['current_price'];
                    $sub_total = $sub_qty * $sub_price;

                    $current_qty -= $sub_qty;
                    $current_val -= $sub_total;

                    // เช็คว่าหมด Lot หรือยัง
                    $stmtCheck = $pdo->prepare("
                        SELECT s.qty_initial, s.doc_no,
                               (SELECT SUM(qty_deducted) FROM transaction_details td WHERE td.lot_id = s.lot_id AND td.detail_id <= ?) as used_so_far
                        FROM stock_lots s WHERE s.lot_id = ?
                    ");
                    $stmtCheck->execute([$detail['detail_id'], $detail['lot_id']]);
                    $chk = $stmtCheck->fetch();

                    $remark_text = '';
                    $displayName = $row['description'];
                    $display_qty = $current_qty;
                    $display_val = $current_val;

                    if (($chk['qty_initial'] - $chk['used_so_far']) <= 0) {
                        $displayName = '<span class="text-red-bold">' . $row['description'] . '</span>';
                        $remark_text = '<span class="remark-red">(ตัดหมด Lot ' . $chk['doc_no'] . ')</span>';
                        $display_qty = 0;
                        $display_val = 0;
                    }

                    $html .= '<tr>
                        <td class="text-center">' . dateThai($row['trx_date']) . '</td>
                        <td>' . $displayName . '</td>
                        <td class="text-center">' . $row['doc_ref'] . '</td>
                        <td class="text-right">' . number_format($sub_price, 2) . '</td>
                        <td></td> <td></td>
                        <td class="text-right">' . number_format($sub_qty) . '</td>
                        <td class="text-right">' . number_format($sub_total, 2) . '</td>
                        
                        <td class="text-right"><b>' . number_format($display_qty) . '</b></td>
                        <td class="text-right">' . number_format($display_val, 2) . '</td>
                        
                        <td class="text-center">' . $remark_text . '</td>
                    </tr>';
                }
            } else {
                // Fallback ข้อมูลเก่า
                $current_qty -= $row['qty'];
                $current_val -= ($row['qty'] * $row['price']);
                $html .= '<tr>
                    <td class="text-center">' . dateThai($row['trx_date']) . '</td> 
                    <td>' . $row['description'] . '</td> 
                    <td class="text-center">' . $row['doc_ref'] . '</td>
                    <td class="text-right">' . number_format($row['price'], 2) . '</td>
                    <td></td> <td></td>
                    <td class="text-right">' . number_format($row['qty']) . '</td>
                    <td class="text-right">' . number_format($row['qty'] * $row['price'], 2) . '</td>
                    <td class="text-right"><b>' . number_format($current_qty) . '</b></td>
                    <td class="text-right">' . number_format($current_val, 2) . '</td> 
                    <td></td>
                </tr>';
            }
        }
    }

    // ถ้าไม่มีข้อมูลเลย
    if (count($movements) == 0 && count($remainingLots) == 0) {
        $html .= '<tr><td colspan="11" class="text-center" style="padding:10px; color:#aaa;">- ไม่มีรายการ -</td></tr>';
    }

    // เติมบรรทัดว่าง
    for ($i = 0; $i < 5; $i++) {
        $html .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
    }

    $html .= '</tbody></table>';
    $mpdf->WriteHTML($html);

    if ($count < $totalItems) {
        $mpdf->WriteHTML('<pagebreak />');
    }
}

$mpdf->Output('Stock_Report_Final.pdf', 'I');
?>