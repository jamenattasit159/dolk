<?php
require_once __DIR__ . '/vendor/autoload.php';

// เชื่อมต่อฐานข้อมูล
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

// 1. รับค่า Filter และวันที่
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// กำหนดค่าเริ่มต้นวันที่ (ถ้าไม่ส่งมา ให้ใช้วันที่ 1 ของเดือนปัจจุบัน จนถึง วันปัจจุบัน)
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// แปลงวันที่สำหรับใช้ใน Query (Start: 00:00:00, End: 23:59:59)
$start_ts = $start_date . " 00:00:00";
$end_ts = $end_date . " 23:59:59";

// 2. สร้าง SQL Query พร้อม Subqueries เพื่อคำนวณยอดต่าง ๆ
// - ยอดยกมา (bring_forward): ผลรวมรับเข้าก่อนวันเริ่ม - ผลรวมจ่ายออกก่อนวันเริ่ม
// - รับเข้า (received): ผลรวมรับเข้าในช่วงวันที่
// - จ่ายออก (issued): ผลรวมจ่ายออกในช่วงวันที่
$sql = "SELECT 
            i.itemid, 
            i.itemname, 
            i.type, 
            i.unit,
            (
                COALESCE((SELECT SUM(qty_initial) FROM stock_lots WHERE itemid = i.itemid AND date_in < :start_ts1), 0) 
                - 
                COALESCE((SELECT SUM(qty_withdraw) FROM transactions WHERE itemid = i.itemid AND trx_date < :start_ts2), 0)
            ) AS bring_forward,
            
            (
                COALESCE((SELECT SUM(qty_initial) FROM stock_lots WHERE itemid = i.itemid AND date_in BETWEEN :start_ts3 AND :end_ts1), 0)
            ) AS received,
            
            (
                COALESCE((SELECT SUM(qty_withdraw) FROM transactions WHERE itemid = i.itemid AND trx_date BETWEEN :start_ts4 AND :end_ts2), 0)
            ) AS issued

        FROM items i 
        WHERE 1=1";

$params = [
    ':start_ts1' => $start_ts,
    ':start_ts2' => $start_ts,
    ':start_ts3' => $start_ts,
    ':end_ts1' => $end_ts,
    ':start_ts4' => $start_ts,
    ':end_ts2' => $end_ts
];

if (!empty($search)) {
    $sql .= " AND (i.itemname LIKE :search1 OR i.itemid LIKE :search2)";
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
}

if (!empty($type)) {
    $sql .= " AND i.type = :type";
    $params[':type'] = $type;
}

$sql .= " ORDER BY i.itemid ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. ตั้งค่า mPDF เป็นแนวนอน (A4-L)
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L', // Landscape
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

// แปลงวันที่เป็นรูปแบบไทย (ฟังก์ชันเสริมอย่างง่าย)
function dateTh($strDate)
{
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonth = date("n", strtotime($strDate));
    $strDay = date("j", strtotime($strDate));
    $strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
    return "$strDay $strMonthCut[$strMonth] $strYear";
}

// สร้างข้อความ Header
$filterText = "ข้อมูลตั้งแต่วันที่ " . dateTh($start_date) . " ถึง " . dateTh($end_date);
if ($type)
    $filterText .= " <br>ประเภท: $type";
if ($search)
    $filterText .= " (คำค้น: $search)";

// 4. สร้าง HTML ตาราง
$html = '
<style>
    body { font-family: "sarabun"; font-size: 14pt; } /* ปรับขนาดฟอนต์ให้พอดีกับแนวนอน */
    table { width: 100%; border-collapse: collapse; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    th, td { border: 1px solid #000; padding: 6px; vertical-align: middle; line-height: 1.2; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .header { text-align: center; font-weight: bold; font-size: 20pt; margin-bottom: 5px; }
    .sub-header { text-align: center; font-size: 14pt; margin-bottom: 15px; }
    .bg-gray { background-color: #f0f0f0; }
</style>

<div class="header">รายงานสรุปวัสดุคงเหลือ</div>
<div class="sub-header">สำนักงานที่ดินจังหวัดอ่างทอง <br> ' . $filterText . '</div>

<table>
    <thead>
        <tr class="bg-gray">
            <th width="5%">ลำดับ</th>
            <th width="10%">รหัสวัสดุ</th>
            <th width="25%">ชื่อวัสดุ</th>
            <th width="10%">ยอดยกมา</th>
            <th width="10%">รับเข้า</th>
            <th width="10%">จ่ายออก</th>
            <th width="10%">คงเหลือ</th>
            <th width="10%">หน่วยนับ</th>
        </tr>
    </thead>
    <tbody>';

$i = 1;
if (count($items) > 0) {
    foreach ($items as $item) {
        // คำนวณยอดคงเหลือปลายงวด (Balance) = ยกมา + รับ - จ่าย
        $balance = $item['bring_forward'] + $item['received'] - $item['issued'];

        $qtyClass = ($balance <= 0) ? 'color: red;' : '';

        // จัดรูปแบบตัวเลข ถ้าเป็น 0 ให้แสดง - เพื่อความสวยงาม (หรือจะแสดง 0 ก็ได้)
        $showBF = $item['bring_forward'] == 0 ? '-' : number_format($item['bring_forward']);
        $showIn = $item['received'] == 0 ? '-' : number_format($item['received']);
        $showOut = $item['issued'] == 0 ? '-' : number_format($item['issued']);

        $html .= '<tr>
            <td class="text-center">' . $i++ . '</td>
            <td class="text-center">' . $item['itemid'] . '</td>
            <td>' . $item['itemname'] . '</td>
            <td class="text-right">' . $showBF . '</td>
            <td class="text-right" style="color: green;">' . $showIn . '</td>
            <td class="text-right" style="color: red;">' . $showOut . '</td>
            <td class="text-right" style="font-weight: bold; ' . $qtyClass . '">' . number_format($balance) . '</td>
            <td class="text-center">' . $item['unit'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="8" class="text-center">ไม่พบข้อมูลตามเงื่อนไข</td></tr>';
}

$html .= '</tbody></table>';

// Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output('Stock_Summary_' . date('Ymd') . '.pdf', 'I');
?>