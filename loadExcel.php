<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require_once 'vendor/autoload.php';

$inputFileName = $_FILES['uploadfile']["tmp_name"];
echo 'TMP-FILE-NAME: ' . $inputFileName . '<br>';

$spreadsheet = IOFactory::load($inputFileName); //create new speedsheen object

$loadedSheetNames = $spreadsheet->getSheetNames(); //получаем имена листов

foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) { // выводим для наглядности Имена листов
    echo '<br/>' . "Номер и имя листа: " . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';
}
// выводим весь ezcel
foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {

    $worksheet = $spreadsheet->getSheet($sheetIndex);
    $worksheet = $spreadsheet->setActiveSheetIndexByName($loadedSheetName);

    echo "========================++++++++++++++++++++++++========================================" . '<br/>'
        . "Номер и имя листа: " . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';

    //printArrayAsTable($worksheetArray);

    //$rows = $worksheet->toArray();
    //======================================IMAGES
    /*
        $i = 0;
        @mkdir("files", 0777);
        @mkdir("files/$loadedSheetName", 0777);
        $uploaddir = "./files/$loadedSheetName";

        foreach ($worksheet->getDrawingCollection() as $drawing) {
                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                    ob_start();
                    call_user_func(
                        $drawing->getRenderingFunction(),
                        $drawing->getImageResource()
                    );
                    $imageContents = ob_get_contents();
                    ob_end_clean();
                    switch ($drawing->getMimeType()) {
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG :
                            $extension = 'png';
                            break;
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_GIF:
                            $extension = 'gif';
                            break;
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG :
                            $extension = 'jpg';
                            break;
                    }
                } else {
                    $zipReader = fopen($drawing->getPath(), 'r');
                    $imageContents = '';
                    while (!feof($zipReader)) {
                        $imageContents .= fread($zipReader, 1024);
                    }
                    fclose($zipReader);
                    $extension = $drawing->getExtension();
                }
            $myFileName = $uploaddir . '/'.$loadedSheetName.'_'.$sheetIndex.'_Image_' . ++$i . '.' . $extension; //Имя файла картинки
            file_put_contents($myFileName, $imageContents);
            echo "<pre>";print_r($drawing->getCoordinates());echo "</pre><hr>";
        }
        die("sdfgsdf");
        echo "ALL IMAGES ARE SAVED";*/
//======================================

    //Заполнияем MergeCells
    $mergedCellsRange = $worksheet->getMergeCells();
    /*echo "MergeCells on thie sheet is - ";
    pr($mergedCellsRange);*/

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

        //"Image" => "ImageAliasValue",
        //"IMAGE" => "ImageAliasValue",
        //"Picture" => "ImageAliasValue",


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
    $worksheetArray = setCodeAsKey($worksheetArray); //меняем название колонок на Алиасы


    pr($worksheetArray);
    //printArrayAsTable(var_dump($worksheetArray));//печатаем таблицу
    //printArrayAsTable($worksheetArray);//печатаем таблицу


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

function setCodeAsKey($inputArray)
{
    foreach ($inputArray as $row) {
        $tmpArray = [];
        $tmpArray2 = [];
        if (array_search('CodeAliasValue', $row) == true) {
            $codeKey = array_search('CodeAliasValue', $row);
            $collumnArray = array_values($row); //массив имен колонок
            //pr($collumnArray);
        }
        $codeValue = $row[$codeKey];
        pr($codeValue);

        $tmpArray[] = $codeValue;
        $tmpArray = array_fill_keys($tmpArray, $row);

        //добавляем имена колонок в ключи
        $tmpArray2 = array_combine($collumnArray, array_values($row));
    }
    pr($tmpArray2);
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


