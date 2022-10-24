<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
$data = file_get_contents( "php://input" );
$dataArray = array_filter(json_decode($data, true));


require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$i = 0;
foreach($dataArray as $data){
    $label = $data[0];
    $rows = $data[1];
    if($label !== 'Visible Body'){
        usort($rows, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
    }
    $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $label);
    $spreadsheet->addSheet($newSheet, $i);
    $sheet = $spreadsheet->getSheetByName($label);
    $sheet->fromArray($rows, NULL, 'A1');
    $highestColumn = $sheet->getHighestColumn();
    foreach(range('A',$highestColumn) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $sheet->getStyle('A1:'.$highestColumn.'1')->getFont()->setBold(true);
    $i++;
}

$sheetIndex = $spreadsheet->getIndex(
    $spreadsheet->getSheetByName('Worksheet')
);
$spreadsheet->removeSheetByIndex($sheetIndex);

$spreadsheet->setActiveSheetIndex(0);


$fileName = 'data.xlsx';

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
$writer->save('php://output');
?>