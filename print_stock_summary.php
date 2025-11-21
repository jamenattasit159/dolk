<?php
require_once __DIR__ . '/vendor/autoload.php';

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

// รับค่า Filter จาก URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// สร้าง SQL แบบมีเงื่อนไข
$sql = "SELECT * FROM items WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (itemname LIKE ? OR itemid LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($type)) {
    $sql .= " AND type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY itemid ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ตั้งค่า mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
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

// หัวกระดาษระบุเงื่อนไขการกรอง
$filterText = "";
if ($type)
    $filterText .= "ประเภท: $type ";
if ($search)
    $filterText .= "(คำค้น: $search)";

$html = '
<style>
    body { font-family: "sarabun"; font-size: 16pt; }
    table { width: 100%; border-collapse: collapse; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    th, td { border: 1px solid #000; padding: 8px; vertical-align: top; line-height: 1.2; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .header { text-align: center; font-weight: bold; font-size: 20pt; margin-bottom: 10px; }
    .sub-header { text-align: center; font-size: 14pt; margin-bottom: 20px; }
    .bg-gray { background-color: #f0f0f0; }
</style>

<div class="header">รายงานสรุปวัสดุคงเหลือ</div>
<div class="sub-header">สำนักงานที่ดินจังหวัดอ่างทอง <br> ' . $filterText . '</div>

<table>
    <thead>
        <tr class="bg-gray">
            <th width="8%">ลำดับ</th>
            <th width="15%">รหัสวัสดุ</th>
            <th width="37%">ชื่อวัสดุ</th>
            <th width="20%">ประเภท</th>
                 <th width="10%">คงเหลือ</th>
            <th width="10%">หน่วยนับ</th>
       
        </tr>
    </thead>
    <tbody>';

$i = 1;
if (count($items) > 0) {
    foreach ($items as $item) {
        $qtyClass = ($item['qty'] == 0) ? 'color: red;' : '';
        $html .= '<tr>
            <td class="text-center">' . $i++ . '</td>
            <td class="text-center">' . $item['itemid'] . '</td>
            <td>' . $item['itemname'] . '</td>
            <td class="text-center">' . $item['type'] . '</td>
              <td class="text-right" style="font-weight: bold; ' . $qtyClass . '">' . number_format($item['qty']) . '</td>
            <td class="text-center">' . $item['unit'] . '</td>
          
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" class="text-center">ไม่พบข้อมูลตามเงื่อนไข</td></tr>';
}

$html .= '</tbody></table>';

$mpdf->WriteHTML($html);
$mpdf->Output('Stock_Summary.pdf', 'I');
?>