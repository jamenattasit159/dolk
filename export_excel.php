<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup; // เพิ่ม Class สำหรับตั้งค่าหน้ากระดาษ

// ============================================================================
// 1. เชื่อมต่อฐานข้อมูล
// ============================================================================

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

// ============================================================================
// 2. เริ่มสร้าง Excel
// ============================================================================

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("รายงานสต็อก");

// --- ตั้งค่าหน้ากระดาษ A4 แนวนอน (Landscape) ---
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1);  // บีบให้พอดีความกว้าง 1 หน้า
$sheet->getPageSetup()->setFitToHeight(0); // ความสูงปล่อยไหลตามจำนวนข้อมูล

// ตั้งค่าขอบกระดาษ (Margin) ให้พอดี
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.5);
$sheet->getPageMargins()->setLeft(0.5);
$sheet->getPageMargins()->setBottom(0.5);

// ตั้งค่า Default Font
$spreadsheet->getDefaultStyle()->getFont()->setName('Sarabun');
$spreadsheet->getDefaultStyle()->getFont()->setSize(14);

$currentRow = 1;

// ดึงข้อมูลสินค้าทั้งหมด
$items = $pdo->query("SELECT * FROM items ORDER BY itemid ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalItems = count($items); // นับจำนวนสินค้าเพื่อเช็คหน้าสุดท้าย

foreach ($items as $index => $item) {
    $itemid = $item['itemid'];

    // ---------------------------------------------------------
    // LOGIC การคำนวณ (เหมือนเดิม)
    // ---------------------------------------------------------

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

    // ---------------------------------------------------------
    // เริ่มวาดลง Excel
    // ---------------------------------------------------------

    // *** ส่วนหัวของแต่ละรายการ (Header) ***
    $sheet->mergeCells("A$currentRow:K$currentRow");
    $sheet->setCellValue("A$currentRow", "บัญชีวัสดุ: " . $item['itemname'] . " (" . $item['itemid'] . ")");
    $sheet->getStyle("A$currentRow")->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle("A$currentRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A$currentRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');
    $currentRow++;

    // Sub-Header
    $sheet->mergeCells("A$currentRow:K$currentRow");
    $sheet->setCellValue("A$currentRow", "ประเภท: " . $item['type'] . " | หน่วยนับ: " . ($item['unit'] ?: '-'));
    $sheet->getStyle("A$currentRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $currentRow++;

    // ตารางหัวข้อคอลัมน์
    $sheet->setCellValue("A$currentRow", "วัน เดือน ปี");
    $sheet->setCellValue("B$currentRow", "รายการ");
    $sheet->setCellValue("C$currentRow", "เลขที่เอกสาร");
    $sheet->setCellValue("D$currentRow", "ราคา/หน่วย");
    $sheet->setCellValue("E$currentRow", "รับ (จำนวน)");
    $sheet->setCellValue("F$currentRow", "รับ (ราคา)");
    $sheet->setCellValue("G$currentRow", "จ่าย (จำนวน)");
    $sheet->setCellValue("H$currentRow", "จ่าย (ราคา)");
    $sheet->setCellValue("I$currentRow", "คงเหลือ (จำนวน)");
    $sheet->setCellValue("J$currentRow", "คงเหลือ (ราคา)");
    $sheet->setCellValue("K$currentRow", "หมายเหตุ");

    $sheet->getStyle("A$currentRow:K$currentRow")->getFont()->setBold(true);
    $sheet->getStyle("A$currentRow:K$currentRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A$currentRow:K$currentRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("A$currentRow:K$currentRow")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
    $currentRow++;

    // --- แสดงยอดยกมา ---
    if (count($remainingLots) > 0) {
        foreach ($remainingLots as $idx => $lot) {
            $dateText = ($idx === 0) ? dateThai($startDate) : '';
            $descText = ($idx === 0) ? 'ยอดยกมา' : '';

            $sheet->setCellValue("A$currentRow", $dateText);
            $sheet->setCellValue("B$currentRow", $descText);
            $sheet->setCellValue("C$currentRow", $lot['doc_no']);
            $sheet->setCellValue("D$currentRow", $lot['price']);
            $sheet->setCellValue("I$currentRow", $lot['qty']);
            $sheet->setCellValue("J$currentRow", $lot['qty'] * $lot['price']);
            $sheet->setCellValue("K$currentRow", "Lot " . $lot['doc_no']);

            $sheet->getStyle("A$currentRow:K$currentRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $currentRow++;
        }
    } else {
        $sheet->setCellValue("A$currentRow", dateThai($startDate));
        $sheet->setCellValue("B$currentRow", "ยอดยกมา");
        $sheet->setCellValue("I$currentRow", 0);
        $sheet->setCellValue("J$currentRow", 0);
        $sheet->getStyle("A$currentRow:K$currentRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $currentRow++;
    }

    // --- แสดงรายการเคลื่อนไหว ---
    foreach ($movements as $row) {
        if ($row['type'] == 'IN') {
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

            $sheet->setCellValue("A$currentRow", dateThai($row['trx_date']));
            $sheet->setCellValue("B$currentRow", $row['description']);
            $sheet->setCellValue("C$currentRow", $row['doc_ref']);
            $sheet->setCellValue("D$currentRow", $row['price']);
            $sheet->setCellValue("E$currentRow", $row['qty']);
            $sheet->setCellValue("F$currentRow", $row['qty'] * $row['price']);
            $sheet->setCellValue("I$currentRow", $globalQty);
            $sheet->setCellValue("J$currentRow", $globalVal);
            $sheet->getStyle("A$currentRow:K$currentRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $currentRow++;
        } else {
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
                        if ($lotRemainQty <= 0)
                            unset($activeLots[$currentLotId]);
                    } else {
                        $lotRemainQty = 0;
                        $lotRemainVal = 0;
                    }

                    $remark = ($lotRemainQty == 0) ? '(ตัดหมด Lot)' : '';
                    if ($remark)
                        $sheet->getStyle("K$currentRow")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFF0000'));

                    $sheet->setCellValue("A$currentRow", dateThai($row['trx_date']));
                    $sheet->setCellValue("B$currentRow", $row['description']);
                    $sheet->setCellValue("C$currentRow", $row['doc_ref']);
                    $sheet->setCellValue("D$currentRow", $sub_price);
                    $sheet->setCellValue("G$currentRow", $sub_qty);
                    $sheet->setCellValue("H$currentRow", $sub_total);
                    $sheet->setCellValue("I$currentRow", $lotRemainQty);
                    $sheet->setCellValue("J$currentRow", $lotRemainVal);
                    $sheet->setCellValue("K$currentRow", $remark);
                    $sheet->getStyle("A$currentRow:K$currentRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $currentRow++;
                }
            }
        }
    }

    // *** สำคัญ: ใส่ Page Break หลังจบแต่ละรายการ (ยกเว้นรายการสุดท้าย) ***
    if ($index < $totalItems - 1) {
        $sheet->setBreak("A$currentRow", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        $currentRow++; // ขยับลงมา 1 บรรทัดเพื่อให้ Page Break ทำงานได้สมบูรณ์
    }
}

// จัดความกว้างคอลัมน์อัตโนมัติ
foreach (range('A', 'K') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// ============================================================================
// 3. ส่งไฟล์ให้ดาวน์โหลด
// ============================================================================
$filename = 'Stock_Report_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>