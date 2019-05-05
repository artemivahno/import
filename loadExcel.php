<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
require_once 'vendor/autoload.php';

/*$inputFileName = './sampleData/example1.xls';
$spreadsheet = IOFactory::load($inputFileName);*/

var_dump($_FILES);
$inputFileName = $_FILES['file']["tmp_name"];
echo 'NAME: ' . $inputFileName;
$spreadsheet = IOFactory::load($inputFileName); //create new speedsheen object

$loadedSheetNames = $spreadsheet->getSheetNames(); //получаем имена листов
foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
    $helper->log($sheetIndex . ' -> ' . $loadedSheetName);
}

$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();
foreach ($rows AS $r) {
    echo '-----------------------------------------------<br/>';
    foreach ($r AS $c) {
        echo '[' . $c . ']';
    }
    echo '<br/>';
}