<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = file_get_contents( "php://input" );
$dataArray = array_filter(json_decode($data, true));
$headerArray = array('Topic', 'URL');
array_unshift($dataArray, $headerArray);
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();

$label = 'Ovid Videos';
$newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $label);
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray($dataArray, NULL, 'A1');
$highestColumn = $sheet->getHighestColumn();
foreach(range('A',$highestColumn) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}
$sheet->getStyle('A1:'.$highestColumn.'1')->getFont()->setBold(true);

$fileName = 'data.xlsx';

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
$writer->save('php://output');
?>