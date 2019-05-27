<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

require_once 'vendor/autoload.php';
//require_once 'connection.php';

//$connection = new Connection();
$inputFileName = $_FILES['uploadfile']["tmp_name"];
echo 'TMP-FILE-NAME: ' . $inputFileName . '<br>';

$excelArray = [];

// выводим весь ezcel
function getExcelData($inputFileName)
{
    $worksheetArray = [];
    $allExcelSheet = [];
    $spreadsheet = IOFactory::load($inputFileName); //create new speedsheen object
    $loadedSheetNames = $spreadsheet->getSheetNames(); //получаем имена листов

    /*foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) { // выводим для наглядности Имена листов
        echo '<br/>' . "Номер и имя листа: " . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';
    }*/
    foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {

        $worksheet = $spreadsheet->getSheet($sheetIndex);
        $worksheet = $spreadsheet->setActiveSheetIndexByName($loadedSheetName);

        /*echo "========================++++++++++++++++++++++++========================================" . '<br/>'
            . "Номер и имя листа: " . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';*/

        //Заполнияем MergeCells
        $mergedCellsRange = $worksheet->getMergeCells();

        foreach ($mergedCellsRange as $currMergedRange) {

            //A1,B1.C1
            $columnIndex = Coordinate::extractAllCellReferencesInRange($currMergedRange);
            //freom A1:O1       get array A1,O1
            $currMergedCellsArray = Coordinate::splitRange($currMergedRange);
            //получаем значенеие первой ячейки
            $cellAdres = $currMergedCellsArray[0][0];
            $cell = $worksheet->getCell($cellAdres)->getValue();
            //заполняем все ячейки из первой
            foreach ($columnIndex as $adres) {
                $worksheet->setCellValue($adres, $cell);
            }
        }

        //преобразуем в array
        $worksheetArray = $worksheet->toArray();

        $worksheetArray = process($worksheetArray);
        $worksheetArray = dellCategoryRow($worksheetArray);

        $alias = array(
            "Category" => "CategoryAliasValue",

            "Product" => "ProductAliasValue",
            "PRODUCT" => "ProductAliasValue",
            "Name" => "ProductAliasValue",

            "EAN CODE" => "CodeAliasValue",
            "EAN Code" => "CodeAliasValue",

            "Colors" => "ColorAliasValue",
            "COLORS" => "ColorAliasValue",
            "Color" => "ColorAliasValue",
            "colors" => "ColorAliasValue",

            "Description" => "DescriptionAliasValue",
            "DESCRIPTION" => "DescriptionAliasValue",

            "Price (USD)" => "Price(USD)Alias",
            "21-100pcs Price USD 10% discount" => "21-100pcs Price USD 10% discount Alias",
            "101-200pcs Price USD 15% discount" => "101-200pcs Price USD 15% discount Alias",
            "Over 200pcs Price USD 20% discount" => "Over 200pcs Price USD 20% discount Alias",

            "MSRP (USD)" => "MSRP (USD)Alias",

            "Product Weight (g)" => "Product Weight (g)Alias",

            "Carton Weight (kg)" => "Carton Weight (kg)Alias",

            "Color box Size (cm)" => "Color box Size (cm)Alias",
            "Color box size (cm)" => "Color box Size (cm)Alias",

            "Inner carton packing Qty(PCS)" => "Inner carton packing Qty(PCS)Alias",

            "Small box Size(cm)" => "Small box Size(cm)Alias",

            "Carton packing Qty(PCS)" => "Carton packing Qty(PCS)Alias",

            "Carton Size(cm)" => "Carton Size(cm)Alias",
        );

        $worksheetArray = setAlias($worksheetArray, $alias); //меняем название колонок на Алиасы
        $worksheetArray = setCollumnAsKey($worksheetArray); //меняем ключ ячейки на название столбца .меняем ключ стороки на код товара

        // pr($worksheetArray);
        //printArrayAsTable(var_dump($worksheetArray));//печатаем таблицу
        //printArrayAsTable($worksheetArray);//печатаем таблицу

        $allExcelSheet[$loadedSheetName] = $worksheetArray;

    }
    //pr($allExcelSheet);
    return $allExcelSheet;

}

$excelArray = getExcelData($inputFileName);

//pr($excelArray);

$query = "SELECT `uuid` ,`name`,`barcodes` FROM ms_products";
$query = "SELECT `barcodes` FROM ms_products";
$dbArray = dbQueryArray($query);

function compareExistance($dbArray, $excelArray)
{
    $diffBarcodes = [];
    //pr($excelArray);
    //получаем ключи из Базы
    $dbBarcodes = array_column($dbArray, 'barcodes');
    //pr($dbBarcodes);
    //получаем ключи из excel
    foreach ($excelArray as $row) {
        $excelBarcodes[] = array_column($row, 'CodeAliasValue');
        // pr($excelBarcodes);
    }
    //объединяем все Barcodes из excel и удаляем дубли
    foreach ($excelBarcodes as $value) {
        $diffBarcodes = array_merge($diffBarcodes, $value);
    }
    $diffBarcodes = array_unique($diffBarcodes);
    echo 'excelBarcodes: ';
    pr($diffBarcodes);

    //сравниваем excel и базу
    $diffBarcodes1 = array_diff($diffBarcodes, $dbBarcodes);
    echo "Новые товары не представленные в Базе данных: ";
    pr($diffBarcodes1);

    $sameBarcodes = array_uintersect($dbBarcodes, $diffBarcodes, 'strcasecmp');
    echo "Товары представленные в Базе данных: ";
    pr($sameBarcodes);

    return $diffBarcodes;
}

$tmp = compareExistance($dbArray, $excelArray);
//pr($tmp);

function printArrayAsTable($arr)
{
    if (empty($arr[0])) {
        pr('Массив пустой');
        return true;
    }
    $keys = array_keys($arr[0]);
    echo '<table class="table table-bordered table-hover table-responsive sortable-theme-bootstrap" data-sortable="" data-sortable-initialized="true">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>#</th>';
    foreach ($keys as $key) {
        echo '<th>' . $key . '</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    $i = 1;
    foreach ($arr as $row) {
        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        foreach ($row as $column) {
            echo '<td>' . $column . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

function process($data)
{
    $data = removeEmptyColumns($data);
    foreach ($data as $item) {
        if (isEmptyRow($item)) {
            continue;
        }
        $result[] = $item;
    }
    //fill category column
    $category = '';
    foreach ($result as $k => $row) {
        if (isCategoryRow($row)) {
            $category = $row[0];
        }
        array_unshift($result[$k], $category);
    }

    return $result;

}

//Отсекаем пустые строки

function isEmptyRow($row)
{
    $empty = true;
    foreach ($row as $item) {
        if (!empty($item)) {
            $empty = false;
            return $empty;
        }
    }
    return $empty;
}

//Отсекаем пустые колонки
function removeEmptyColumns($data)
{
    $columns = [];
    if (empty($data[0])) {
        return [];
    }

    $columns = array_keys($data[0]);
    foreach ($columns as $k => $item) {
        $columns[$k] = false;
    }

    foreach ($data as $row) {
        foreach ($row as $k => $item) {
            if ($columns[$k]) {
                continue;
            }
            if (!empty($item)) {
                $columns[$k] = true;
            }
        }
    }

    $result = [];
    foreach ($data as $row) {
        $newrow = [];
        foreach ($row as $k => $item) {
            if (empty($columns[$k])) {
                continue;
            }
            $newrow[] = $item;
            if (!empty($item)) {
                $columns[$k] = true;
            }
        }
        $result[] = $newrow;

    }

    return $result;
}

function isCategoryRow($row)
{
    $result = true;
    if ($row[0] !== $row[1]) {
        return false;
    }

    foreach ($row as $k => $item) {
        if ($k[0] > 0) {
            if (!empty($item)) {
                return false;
            }
        }
    }
    return true;
}

function dellCategoryRow($data)
{
    $new = [];
    foreach ($data as $row) {
        if (isCategoryRow($row)) {
            continue;
        }
        $new[] = $row;
    }
    $result = $new;
    $result[0][0] = 'Category';
    return $result;
}


function pr($v)
{
    echo '<pre>';
    print_r($v);
    echo '</pre>';
}

function trimall($string)
{
    return preg_replace("/(^\s*)|(\s*$)/", "",
        preg_replace("/\s+/", " ", trim($string)));
}

function setAlias($inputArray, $alias)
{
    foreach ($inputArray as $row) {
        if ($row[0] == 'Category') {
            $tmpArray = [];
            foreach ($row as $cell) {
                $cell = preg_replace('/\t\n\r/', '', $cell);
                $cell = trimall($cell);
                if (isset($alias[$cell]) || array_key_exists($cell, $inputArray)) {
                    $cell = $alias[$cell];
                }
                $tmpArray[] = $cell;
            }
        }
    }
    $inputArray[0] = $tmpArray;
    return $inputArray;
}

function setCollumnAsKey($inputArray)
{
    $tmpArray = [];
    $tmpArray2 = [];
    ///pr($inputArray);
    foreach ($inputArray as $row) {
        //определяем строку заголовков
        if (array_search('ProductAliasValue', $row) == true) {
            $collumnArray = array_values($row); //массив имен колонок
            if (!empty($collumnArray)) {
                $tmp = $collumnArray;
            }
            //pr($row);
        }
        //pr($tmp);
        //добавляем имена колонок в ключи
//TODO - ВЫСКАКИВАЕТ ОШИБКА ПО array_combine на файле b2eb7fb725bfa61e (1)
        if (!empty($tmp) || !empty($row)) {
            //echo "Не пустой";
            $tmpArray2 = array_combine($tmp, $row);
        }
        $tmpArray[$tmpArray2['CodeAliasValue']] = $tmpArray2;
    }
    $inputArray = $tmpArray;
    return $inputArray;
}

//=====DATABASE ==================================

function dbQuery($query = '')
{
    $dbHost = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "products";


    $link = mysqli_connect("$dbHost", "$dbUsername",
        "$dbPassword", "$dbName")
    or die("Couldn't connect to the MySQL server\n");
    mysqli_query($link, 'SET NAMES utf8')
    or die("Invalid set utf8 " . mysqli_error($link) . "\n");
    $db = mysqli_select_db($link, $dbName)
    or die("db can't be selected\n");

    $result = mysqli_query($link, $query)
    or die("Query error: " . mysqli_error($link) . '[' . $query . ']' . "\n");
    mysqli_close($link);
    return $result;
}

function dbQueryArray($query = '')
{
    $result = dbQuery($query);
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_free_result($result);

    return $data;
}

//=====DATABASE ==================================


?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title></title>
</head>
<body>
<h1></h1>

<?php //printArrayAsTable($worksheetArray); ?>

<? //sendMessage($message);?>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
</body>
</html>


