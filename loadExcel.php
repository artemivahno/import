<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'core.php';

$inputTmpFileName = $_FILES['uploadfile']["tmp_name"]; //получаем ссыдку на загруженный файл

$excelArray = getExcelData($inputTmpFileName);//помещаем данные в Excel файл
$dbArray = callDataDB();//получаем данные из Базы Данных
$dbPrice = dbQueryArray(callDBPrice());

//запускаем сравнение базы и Excel
$diffBarcodes = compareExistance($dbArray, $excelArray);
$diffPrice = compareSame($dbArray, $excelArray);

$productDifference = printDiffPrice($diffPrice,$excelArray,$dbPrice);

// получаем весь excel Здесь обработка всего массива перед выводом на страницу
function getExcelData($inputFileName){
    $allExcelSheet = [];//весь массив из excel
    $spreadsheet = IOFactory::load($inputFileName); //создаем объект Лист
    $loadedSheetNames = $spreadsheet->getSheetNames(); //получаем имена листов

    foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
        $worksheet = $spreadsheet->setActiveSheetIndexByName($loadedSheetName);
        $mergedCellsRange = $worksheet->getMergeCells();//получаем массив объединенных ячеек
        foreach ($mergedCellsRange as $currMergedRange) {
            $columnIndex = Coordinate::extractAllCellReferencesInRange($currMergedRange);//A1,B1.C1
            $currMergedCellsArray = Coordinate::splitRange($currMergedRange);//freom A1:O1       get array A1,O1
            $cellAdres = $currMergedCellsArray[0][0];//получаем значенеие первой ячейки
            $cell = $worksheet->getCell($cellAdres)->getValue();
            foreach ($columnIndex as $adres) { //заполняем все ячейки из первой
                $worksheet->setCellValue($adres, $cell);
            }
        }
        $worksheetArray = $worksheet->toArray();//преобразуем в array
        $worksheetArray = dellEmptyRowFillCategory($worksheetArray);//удаляем пустые строки, добавляем столбец с категорией
        $worksheetArray = dellCategoryRow($worksheetArray);//удаляем дубли строки категорий
        $worksheetArray = setAlias($worksheetArray, getAliases()); //меняем название колонок на Алиасы
        $worksheetArray = setCollumnAsKey($worksheetArray); //меняем ключ ячейки на название столбца .меняем ключ стороки на код товара
        $allExcelSheet[$loadedSheetName] = $worksheetArray;//
    }
    return $allExcelSheet;
}

//все алиасы
function getAliases (){
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
        "Picture" => "ImageAliasValue",

        "PACKAGE" => "PackageAliasValue",
        "Package" => "PackageAliasValue",

        "Description" => "DescriptionAliasValue",
        "DESCRIPTION" => "DescriptionAliasValue",

        "Price (USD)" => "Price(USD)Alias",
        "Wholesale Price(USD)" => "Price(USD)Alias",
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
    return $alias;
}

function callDataDB(){
    $query = "SELECT `uuid` ,`name`,`barcodes` FROM ms_products";
    $dbArray = dbQueryArray($query);
    return $dbArray;
}
function callDBPrice(){
    $queryPrice =   "SELECT `uuid` ,`name`,`barcodes`,`value` 
                FROM ms_products
                LEFT JOIN ms_product_prices ON ms_products.uuid=ms_product_prices.productUuid
                WHERE ms_product_prices.type='Оптовая цена'
                ";
    return $queryPrice;
}

//сравнение базы данных и Excel
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

function printDiffPrice($diffPrice,$excelArray,$dbPrice){
    $usd = $_POST['usdRate'];//
    $result = [];
    $tmpArr = [];

    //получаем массив баркод-цена(из базы)
    $dbArray=[];
    foreach ($dbPrice as $row){
        $dbArray[$row['barcodes']] = $row['value'];
    }

    foreach ($excelArray as $row) {
        $tmpArr[] = (array_intersect_key($row, array_flip($diffPrice)));
    }

    //очищаем все лишнее в строке Price(USD)Alias
    foreach ($tmpArr as $row){
        foreach ($row as $value){
            $value['CodeAliasValue'] = trim($value['CodeAliasValue']);
            //убираем знак $ и оставляем только цифры в Price(USD)Alias
            $value['Price(USD)Alias'] = preg_replace("/[^,.0-9]/", '', $value['Price(USD)Alias']);
            //если товар с таким кодом есть в базе данных вставить цену
            if ($dbArray[$value['CodeAliasValue']]){
                array_unshift($value, /*$value['PriceDataBase'] =*/
                    round($dbArray[$value['CodeAliasValue']]/$usd,2));
            }
            $tmp[]=$value;
            $result = $tmp;
        }
    }
    return $result;
}

//проверяет есть ли товар из Excel в Базе Данных
function compareSame($dbArray, $excelArray)
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
    $sameBarcodes = array_uintersect($dbBarcodes, $excelBarcodes, 'strcasecmp');

    return $sameBarcodes;
}

//удаляет пустые строки и заполняет столбец категорий по названию листа
function dellEmptyRowFillCategory($data) {
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

//Отсекает пустые строки
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


//Отсекает пустые колонки
function removeEmptyColumns($data)
{
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

//определяет является ли строка Категориями
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

//удаляет строку категорий (для отсутствия дублей эт.строки)
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

function trimall($string)
{
    return preg_replace("/(^\s*)|(\s*$)/", "",
        preg_replace("/\s+/", " ", trim($string)));
}

//заменяет названия колонок на названия из списка Алиасов
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

//устанавливает имена колонок в ключи
function setCollumnAsKey($inputArray)
{
    $tmpArray = [];
    $tmpArray2 = [];
    foreach ($inputArray as $row) {
        //определяем строку заголовков
        if (array_search('ProductAliasValue', $row) == true) {
            $collumnArray = array_values($row); //массив имен колонок
            if (!empty($collumnArray)) {
                $tmp = $collumnArray;
            }
        }
        //добавляем имена колонок в ключи
        if (!empty($tmp) && !empty($row)) {
            $tmpArray2 = array_combine($tmp, $row);
        }
            //удаляем неиспользуемые колонки
            unset($tmpArray2['ImageAliasValue']);
            unset($tmpArray2['PackageAliasValue']);
        //добавляем CODE   в качестве ключа строки
        $tmpArray[trimall($tmpArray2['CodeAliasValue'])] = $tmpArray2;
    }
    $inputArray = $tmpArray;
    return $inputArray;
}


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
            //кнопка добавки одного товара
            $(".product").click(function () {
                var keyName = $(this).data('key1');
                var keyCode = $(this).data('key2');
                var keyDescr = $(this).data('key3');
                var keyWeidht = $(this).data('key4');
                var keyVolume = $(this).data('key5');
                var keyByPrice = $(this).data('key6');
                alert(keyByPrice);
                $.ajax({
                    url: "saveArrMS.php",
                    type: "POST",
                    data: { key1: keyName,
                            key2: keyCode,
                            key3: keyDescr,
                            key4: keyWeidht,
                            key5: keyVolume,
                            key6: keyByPrice,
                    },
                    success: function (result) {
                        alert('Товар загружен в Мой склад');
                    }
                });
                $(this).remove();
            });

            $(".all").click(function () {
                work();
            });
            //кнопка добавки всех товаров
            function work() {
                if ($('.product')[0]) {
                    alert("Добавить все товары в Мой Склад. Для отмены нажми F5");
                    setInterval(function () {
                        $('.product').first().click()
                    }, 1000);
                } else {
                    alert("Нет товаров для загрузки");
                }
            }
                //альтернативный показ уведомлений
            /*function alert() {
                var alertSuccess = $('.alert-success');

                alertSuccess.css('display', 'block');
                setTimeout(function () {
                    alertSuccess.hide();
                }, 500);
            }*/
        })


    </script>
</head>
<body>

<hr>
<div class="container"><h1>Сводные таблицы</h1>
    <span>
    <a href="/">Выбрать другой файл</a>

    </span>

    <span>
    <div class="alert alert-success" style="display: none;">Товар загружен в базу данных</div>
    </span>

</div>
<br>


<div id="exTab2" class="container">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
               aria-selected="true">Из Excel</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
               aria-controls="profile"
               aria-selected="false">Из Базы Данных</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="productNew-tab" data-toggle="tab" href="#productNew" role="tab"
               aria-controls="productNew"
               aria-selected="false">Новые товары</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="productNew-tab" data-toggle="tab" href="#diffrentPrice" role="tab"
               aria-controls="diffrentPrice"
               aria-selected="false">Поменялась цена товара</a>
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
            serialize($table);

            $arr = [];
            foreach ($table as $row) {
                foreach ($row as $v) {
                    $displayArr[] = $v;
                    $arr = $displayArr;
                }
            }
            ?>
            <input type='hidden' name='tableDifferences' value='<?php serialize($table); ?>' />

            <table cellpadding="5" cellspacing="0" border="1">

                <thead>
                <tr>
                    <th>
                        <button class="all">Добавить все товары в Мой Склад</button>
                    </th>
                    <th><?php echo implode('</th><th>', array_keys(current($arr))); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($arr as $row):
                array_map('htmlentities', $row);
                ?>
                <?php if ($row['CategoryAliasValue'] == "CategoryAliasValue"): //если заголовок
                continue; ?>
                <thead>
                <td></td>
                <td><?php echo implode('</td><td>', $row); ?></td>
                </thead>
                <? else: ?>
                    <tr>
                        <td>
                            <button class="product"
                                    data-key1="<?php echo $row['ProductAliasValue'].' '.$row['ColorAliasValue'] ?>"
                                    data-key2="<?php echo $row['CodeAliasValue'] ?>"
                                    data-key3="<?php echo $row['DescriptionAliasValue'] ?>"
                                    data-key4="<?php echo $row['Product Weight (g)Alias'] ?>"
                                    data-key5="<?php echo $row['Color box Size (cm)Alias'] ?>"
                                    data-key6="<?php echo $row['Price(USD)Alias'] ?>"
                            >Добавить в Мой Склад
                            </button>
                        </td>
                        <td><?php echo implode('</td><td>', $row); ?></td>
                    </tr>
                <? endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="diffrentPrice" role="tabpanel" aria-labelledby="diffrentPrice">
            <h2>Товары, цена которых поменялась. /Курс пересчета: <?php echo (double)$_POST['usdRate'] ?>/</h2>
            <?php
            //pr($productDifference);
            if (!empty($productDifference)){
                printArrayAsTable($productDifference);
            }else{
                echo "Нет товаров в которых поменялась цена";
            }
            ?>
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


