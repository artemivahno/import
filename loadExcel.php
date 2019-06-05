<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

require_once 'vendor/autoload.php';

$inputTmpFileName = $_FILES['uploadfile']["tmp_name"];
//echo 'TMP-FILE-NAME: ' . $inputTmpFileName . '<br>';
$inputFileName = $_FILES['uploadfile']["name"];
//echo 'TMP-FILE-NAME: ' . $inputFileName . '<br>';


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

        //$worksheet = $spreadsheet->getSheet($sheetIndex);
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
        $worksheetArrayPrint = [];
        $worksheetArray = dellCategoryRow($worksheetArray);
        $worksheetArrayPrint = $worksheetArray;

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

            "IMAGE" => "ImageAliasValue",
            "Image" => "ImageAliasValue",

            "PACKAGE" => "PackageAliasValue",
            "Package" => "PackageAliasValue",

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

        //printArrayAsTable($worksheetArrayPrint);//печатаем таблицу

        //$printAllExcelSheet[$loadedSheetName] = $worksheetArray;
        $allExcelSheet[$loadedSheetName] = $worksheetArray;

    }
    //pr($allExcelSheet);
    //printArrayAsTable($allExcelSheet);
    return $allExcelSheet;

}

//помещаем данные в Excel файл
$excelArray = getExcelData($inputTmpFileName);


$query = "SELECT `uuid` ,`name`,`barcodes` FROM ms_products";
$query1 = "SELECT `barcodes` FROM ms_products";
$dbArray = dbQueryArray($query);

//запускаем сравнение базы и Excel
$diffBarcodes = compareExistance($dbArray, $excelArray);

function sendToDB($query = '', $row)
{
    $result = dbQuery($query);
    $query = "INSERT INTO ms_products (id, vwap, last, bid, ask, volume, markchg, markpct, shares, marketcap, ttmsqz) 
    VALUES ('null', '$row[1]', '$row[2]', '$row[3]', '$row[4]', '$row[5]', '$row[6]', '$row[7]', '$row[8]', '$row[9]', '$row[10]', '$row[11]')";
}

function compareExistance($dbArray, $excelArray)
{
    //получаем ключи из Базы
    $dbBarcodes = array_column($dbArray, 'barcodes');

    //получаем ключи из excel
    foreach ($excelArray as $row) {
        $excelBarcodes[] = array_column($row, 'CodeAliasValue');
    }

    //trim excelBarcodes
    foreach ($excelBarcodes as $row) {
        foreach ($row as $value) {
            $tmpArray[] = trim($value);
        }
    }
    $excelBarcodes = $tmpArray;

    //сравниваем excel и базу
    $diffBarcodes = array_diff($excelBarcodes, $dbBarcodes);
    $diffBarcodes = array_unique($diffBarcodes);
    //unset($diffBarcodes[0]);

    //echo "Нет в Базе данных этих товаров: ";
    //pr($diffBarcodes);
    $missingProducts = [];
    $i = 1;

    $sameBarcodes = array_uintersect($dbBarcodes, $excelBarcodes, 'strcasecmp');
    //echo "Товары представленные в Базе данных: ";
    //pr($sameBarcodes);

    return $diffBarcodes;
}

function printTableDifference($diffBarcodes, $excelArray)
{
    $result = [];
    foreach ($excelArray as $row) {
        $result[] = (array_intersect_key($row, array_flip($diffBarcodes)));
    }
    //pr($result);
    return $result;
}

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
    //pr($data);
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

function arrayColRemove($array, $col_index)
{
    if (is_array($array) && count($array)) {
        foreach ($array as $row_index => $row) {
            if (array_key_exists($col_index, $row)) {
                unset($array[$row_index][$col_index]);
                $array[$row_index] = array_values($array[$row_index]);
            }
        }
    }
    return $array;
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
        if (!empty($tmp) && !empty($row)) {
            $tmpArray2 = array_combine($tmp, $row);
        }
        $tmpArray[trimall($tmpArray2['CodeAliasValue'])] = $tmpArray2;
        //pr($tmpArray);
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

function saveArray($tableArray)
{


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

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
            crossorigin="anonymous"></script>
    <title></title>


    <script>
        $(document).ready(function () {
            $(".product").click(function () {
                var key1 = $(this).data('key1');
                var key2 = $(this).data('key2');
                //alert(key1, key2)
                $.ajax({
                    url: "saveArrDB.php",
                    type: "POST",
                    data: {key1: key1, key2: key2},
                    success: function (result) {
                        alert('Товар загружен в базу данных');
                    }
                });
                $(this).remove();
            });

            $(".all").click(function () {
                work();
            });
            function work() {
                if ($('.product')[0]) {
                    alert("Добавить все товары. Для отмены нажми F5");
                    setInterval(function () {
                        $('.product').first().click()
                    }, 500);
                } else {
                    alert("Нет товаров для загрузки");
                }
            }

            function alert() {
                var alertSuccess = $('.alert-success');

                alertSuccess.css('display', 'block');
                setTimeout(function () {
                    alertSuccess.hide();
                }, 500);

            }
        })


    </script>
</head>
<body>

<hr>
<div class="container"><h1>Сводные таблицы</h1>
    <span>
    <div class="alert alert-success" style="display: none;">Товар загружен в базу данных</div>
    </span>

</div>

<div id="exTab2" class="container">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
               aria-selected="true">Из Excel</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile"
               aria-selected="false">Из Базы Данных</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="productNew-tab" data-toggle="tab" href="#productNew" role="tab"
               aria-controls="productNew"
               aria-selected="false">Новые товары</a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            <h2>Таблица данных из Excel файла <?php echo $inputFileName; ?></h2>
            <?php
            $table = getExcelData($inputTmpFileName);
            $result = [];
            foreach ($table as $sheet => $data) {
                foreach ($data as $key => $item) {
                    if ($key == 'CodeAliasValue') {
                        continue;
                    }
                    array_unshift($item, $item['sheet'] = $sheet);
                    $result[] = $item;
                }
            }
            printArrayAsTable($result);
            ?>
        </div>

        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <h2>Содержимое Базы Данных</h2>
            <?php printArrayAsTable($dbArray); ?>
        </div>

        <div class="tab-pane fade" id="productNew" role="tabpanel" aria-labelledby="productNew-tab">
            <h2>Товары, которых нет в Базе Данных</h2>
            <?php
            $table = printTableDifference($diffBarcodes, $excelArray);
            $arr = [];
            foreach ($table as $row) {
                foreach ($row as $v) {
                    $displayArr[] = $v;
                    $arr = $displayArr;
                }
            }
            ?>
            <table cellpadding="5" cellspacing="0" border="1">

                <thead>
                <tr>
                    <th>
                        <button class="all">Добавить все товары</button>
                    </th>
                    <th><?php echo implode('</th><th>', array_keys(current($arr))); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($arr

                as $row):
                array_map('htmlentities', $row);
                ?>
                <?php if ($row['CategoryAliasValue'] == "CategoryAliasValue"):
                continue; ?>
                <thead>
                <td></td>
                <td><?php echo implode('</td><td>', $row); ?></td>
                </thead>
                <? else: ?>
                    <tr>
                        <td>
                            <button class="product" data-key1="<?php echo $row['ProductAliasValue'] ?>"
                                    data-key2="<?php echo $row['CodeAliasValue'] ?>">Добавить
                            </button>
                        </td>
                        <td><?php echo implode('</td><td>', $row); ?></td>
                    </tr>
                <? endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>
<!-- jQuery first, then Popper.js, then Bootstrap JS -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>


</body>
</html>


