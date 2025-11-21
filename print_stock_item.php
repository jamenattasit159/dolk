<?php
require_once __DIR__ . '/vendor/autoload.php';

// ============================================================================
// 1. ตรวจสอบ Input และตั้งค่าเริ่มต้น
// ============================================================================

$requestItemId = isset($_GET['itemid']) ? $_GET['itemid'] : '';
if (empty($requestItemId)) {
    die("กรุณาระบุรหัสวัสดุ (itemid)");
}

// ตั้งค่าปีงบประมาณ (1 ต.ค.)
if (date('n') >= 10) {
    $startYear = date('Y');
} else {
    $startYear = date('Y') - 1;
}
$startDate = $startYear . '-10-01';

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

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
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

$style = '
<style>
    body { font-family: "sarabun"; font-size: 14pt; }
    table { width: 100%; border-collapse: collapse; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    td.data-cell, th.header-cell { border: 1px solid #000; padding: 4px; vertical-align: top; line-height: 1.2; }
    td.info-cell { border: none; padding: 2px 5px; vertical-align: middle; text-align: left; }
    .header-title { font-weight: bold; font-size: 16pt; text-align: center; padding-bottom: 10px; border: none; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bg-gray { background-color: #f0f0f0; }
    .label-text { font-weight: bold; }
    .dotted-line { border-bottom: 1px dotted #000; display: block; width: 100%; min-height: 18px; }
    .remark-red { color: #d00000; font-size: 10pt; }
    .text-red-bold { color: #d00000; font-weight: bold; }
    .text-blue-small { color: #0056b3; font-size: 10pt; }
</style>
';
$mpdf->WriteHTML($style);

// ============================================================================
// 2. ดึงข้อมูลเฉพาะ Item ที่เลือก
// ============================================================================
// [แก้ไข] ดึงเฉพาะ itemid ที่ส่งมา
$stmtItem = $pdo->prepare("SELECT * FROM items WHERE itemid = ?");
$stmtItem->execute([$requestItemId]);
$item = $stmtItem->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("ไม่พบข้อมูลวัสดุรหัส: " . htmlspecialchars($requestItemId));
}

// (ไม่ต้องวนลูป items ใหญ่ เพราะมีแค่ตัวเดียว แต่ใช้โครงสร้างเดิมเพื่อความง่าย)
$itemid = $item['itemid'];

// --- PART A: ยอดยกมา ---
$stmtUsed = $pdo->prepare("SELECT SUM(qty_withdraw) FROM transactions WHERE itemid = ? AND trx_date < ?");
$stmtUsed->execute([$itemid, $startDate]);
$totalUsed = $stmtUsed->fetchColumn() ?: 0;

$stmtLots = $pdo->prepare("SELECT * FROM stock_lots WHERE itemid = ? AND date_in < ? ORDER BY date_in ASC");
$stmtLots->execute([$itemid, $startDate]);
$lotsHistory = $stmtLots->fetchAll(PDO::FETCH_ASSOC);

$remainingLots = [];
$activeLots = [];

foreach ($lotsHistory as $lot) {
    $lotQty = $lot['qty_initial'];
    if ($totalUsed >= $lotQty) {
        $totalUsed -= $lotQty;
    } else {
        $remain = $lotQty - $totalUsed;
        $lotData = [
            'lot_id' => $lot['lot_id'],
            'doc_no' => $lot['doc_no'],
            'price' => $lot['lot_price'],
            'qty' => $remain
        ];
        $remainingLots[] = $lotData;
        $activeLots[$lot['lot_id']] = $lotData;
        $totalUsed = 0;
    }
}

// --- PART B: รายการเคลื่อนไหว ---
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

// =========================================================
// สร้าง HTML
// =========================================================
$html = '
<table>
    <thead>
        <tr><th colspan="11" class="header-title">บัญชีวัสดุ สำนักงานที่ดินจังหวัดอ่างทอง</th></tr>
        <tr>
            <td class="info-cell label-text" colspan="1">ประเภท</td>
            <td class="info-cell" colspan="1"><span class="dotted-line">' . $item['type'] . '</span></td>
            <td class="info-cell label-text text-right" colspan="1">ชื่อวัสดุ</td>
            <td class="info-cell" colspan="4"><span class="dotted-line">' . $item['itemname'] . '</span></td>
            <td class="info-cell label-text text-right" colspan="2">รหัส</td>
            <td class="info-cell" colspan="2"><span class="dotted-line">' . $item['itemid'] . '</span></td>
        </tr>
        <tr>
            <td class="info-cell label-text" colspan="1">ขนาด</td>
            <td class="info-cell" colspan="1"><span class="dotted-line">&nbsp;</span></td>
            <td class="info-cell label-text text-right" colspan="1">ที่เก็บ</td>
            <td class="info-cell" colspan="4"><span class="dotted-line">คลังพัสดุกลาง</span></td>
            <td class="info-cell label-text text-right" colspan="2">หน่วยนับ</td>
            <td class="info-cell" colspan="2"><span class="dotted-line">' . ($item['unit'] ? $item['unit'] : '-') . '</span></td>
        </tr>
        <tr><td colspan="11" style="border:none; height:5px;"></td></tr>
        <tr class="bg-gray">
            <th class="header-cell" rowspan="2" width="10%">วัน เดือน ปี</th>
            <th class="header-cell" rowspan="2" width="25%">รับจาก / จ่ายให้</th>
            <th class="header-cell" rowspan="2" width="10%">เลขที่<br>เอกสาร</th>
            <th class="header-cell" rowspan="2" width="8%">ราคา<br>ต่อหน่วย</th>
            <th class="header-cell" colspan="2" width="12%">รับ</th>
            <th class="header-cell" colspan="2" width="12%">จ่าย</th>
            <th class="header-cell" colspan="2" width="12%">คงเหลือ</th>
            <th class="header-cell" rowspan="2" width="11%">หมายเหตุ</th>
        </tr>
        <tr class="bg-gray">
            <th class="header-cell">จำนวน</th> <th class="header-cell">ราคา</th> 
            <th class="header-cell">จำนวน</th> <th class="header-cell">ราคา</th> 
            <th class="header-cell">จำนวน</th> <th class="header-cell">ราคา</th>
        </tr>
    </thead>
    <tbody>';

// แสดงยอดยกมา
if (count($remainingLots) > 0) {
    foreach ($remainingLots as $index => $lot) {
        $dateText = ($index === 0) ? dateThai($startDate) : '';
        $descText = ($index === 0) ? 'ยอดยกมา' : '';

        $html .= '<tr>
            <td class="data-cell text-center">' . $dateText . '</td> 
            <td class="data-cell">' . $descText . '</td> 
            <td class="data-cell text-center">' . $lot['doc_no'] . '</td>
            <td class="data-cell text-right">' . number_format($lot['price'], 2) . '</td>
            <td class="data-cell"></td> <td class="data-cell"></td> <td class="data-cell"></td> <td class="data-cell"></td>
            <td class="data-cell text-right"><b>' . number_format($lot['qty']) . '</b></td>
            <td class="data-cell text-right">' . number_format($lot['qty'] * $lot['price'], 2) . '</td> 
            <td class="data-cell text-center"><span class="text-blue-small">(Lot ' . $lot['doc_no'] . ')</span></td>
        </tr>';
    }
} else {
    $html .= '<tr>
        <td class="data-cell text-center">' . dateThai($startDate) . '</td> 
        <td class="data-cell">ยอดยกมา</td> <td class="data-cell text-center">-</td> <td class="data-cell text-right">-</td>
        <td class="data-cell"></td> <td class="data-cell"></td> <td class="data-cell"></td> <td class="data-cell"></td>
        <td class="data-cell text-right"><b>0</b></td> <td class="data-cell text-right">0.00</td> <td class="data-cell"></td>
    </tr>';
}

// รายการเคลื่อนไหว
foreach ($movements as $row) {
    if ($row['type'] == 'IN') {
        // [IN] รับเข้า
        $activeLots[$row['ref_id']] = [
            'lot_id' => $row['ref_id'],
            'price' => $row['price'],
            'qty' => $row['qty'],
            'doc_no' => $row['doc_ref']
        ];
        $globalQty = 0;
        $globalVal = 0;
        foreach ($activeLots as $aLot) {
            $globalQty += $aLot['qty'];
            $globalVal += ($aLot['qty'] * $aLot['price']);
        }

        $html .= '<tr>
            <td class="data-cell text-center">' . dateThai($row['trx_date']) . '</td> 
            <td class="data-cell">' . $row['description'] . '</td> 
            <td class="data-cell text-center">' . $row['doc_ref'] . '</td>
            <td class="data-cell text-right">' . number_format($row['price'], 2) . '</td>
            <td class="data-cell text-right">' . number_format($row['qty']) . '</td>
            <td class="data-cell text-right">' . number_format($row['qty'] * $row['price'], 2) . '</td>
            <td class="data-cell"></td> <td class="data-cell"></td>
            <td class="data-cell text-right"><b>' . number_format($globalQty) . '</b></td>
            <td class="data-cell text-right">' . number_format($globalVal, 2) . '</td> 
            <td class="data-cell"></td>
        </tr>';

    } else {
        // [OUT] จ่ายออก
        $stmtDetail = $pdo->prepare("SELECT * FROM transaction_details WHERE tid = ? ORDER BY detail_id ASC");
        $stmtDetail->execute([$row['ref_id']]);
        $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        if (count($details) > 0) {
            foreach ($details as $detail) {
                $sub_qty = $detail['qty_deducted'];
                $sub_price = $detail['current_price'];
                $sub_total = $sub_qty * $sub_price;

                $currentLotId = $detail['lot_id'];

                if (isset($activeLots[$currentLotId])) {
                    $activeLots[$currentLotId]['qty'] -= $sub_qty;
                    $lotRemainQty = $activeLots[$currentLotId]['qty'];
                    $lotRemainVal = $lotRemainQty * $activeLots[$currentLotId]['price'];
                    if ($lotRemainQty <= 0) {
                        unset($activeLots[$currentLotId]);
                    }
                } else {
                    $lotRemainQty = 0;
                    $lotRemainVal = 0;
                }

                $remark_text = '';
                $displayName = $row['description'];

                if ($lotRemainQty == 0) {
                    $displayName = '<span class="text-red-bold">' . $row['description'] . '</span>';
                    $remark_text = '<span class="remark-red">(ตัดหมด Lot)</span>';
                }

                $html .= '<tr>
                    <td class="data-cell text-center">' . dateThai($row['trx_date']) . '</td>
                    <td class="data-cell">' . $displayName . '</td>
                    <td class="data-cell text-center">' . $row['doc_ref'] . '</td>
                    <td class="data-cell text-right">' . number_format($sub_price, 2) . '</td>
                    <td class="data-cell"></td> <td class="data-cell"></td>
                    <td class="data-cell text-right">' . number_format($sub_qty) . '</td>
                    <td class="data-cell text-right">' . number_format($sub_total, 2) . '</td>
                    <td class="data-cell text-right"><b>' . number_format($lotRemainQty) . '</b></td>
                    <td class="data-cell text-right">' . number_format($lotRemainVal, 2) . '</td>
                    <td class="data-cell text-center">' . $remark_text . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="11" class="data-cell text-center">ข้อมูลเก่าไม่รองรับการแสดงผลแบบ Lot</td></tr>';
        }
    }
}

if (count($movements) == 0 && count($remainingLots) == 0) {
    $html .= '<tr><td colspan="11" class="data-cell text-center" style="padding:10px; color:#aaa;">- ไม่มีรายการ -</td></tr>';
}

for ($i = 0; $i < 5; $i++) {
    $html .= '<tr><td class="data-cell">&nbsp;</td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td><td class="data-cell"></td></tr>';
}

$html .= '</tbody></table>';
$mpdf->WriteHTML($html);

$mpdf->Output('Stock_Report_' . $itemid . '.pdf', 'I');
?>