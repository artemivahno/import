<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once 'vendor/autoload.php';
//include_once 'imgFromExcel.php';

//Для загрузки файла на сервер, на данный момент не используем
/*@mkdir("files", 0777);
$uploaddir = './files/';
$uploadfile = $uploaddir . basename($_FILES['uploadfile']['name']);

// Копируем файл из каталога для временного хранения файлов:
if(copy($_FILES['uploadfile']['tmp_name'], $uploadfile)) {
	echo "<h3>Файл успешно загружен на сервер</h3>";
}
else {
	echo "<h3>Ошибка! Не удалось загрузить файл на сервер!</h3>";
}

// Выводим информацию о загруженном файле:
echo "<h3>Информация о загруженном на сервер файле: </h3>";
echo "<p><b>Оригинальное имя загруженного файла: " . $_FILES['uploadfile']['name'] . "</b></p>";
echo "<p><b>Размер загруженного файла в байтах: " . $_FILES['uploadfile']['size'] . "</b></p>";
echo "<p><b>Временное имя файла: " . $_FILES['uploadfile']['tmp_name'] . "</b></p>";*/


$inputFileName = $_FILES['uploadfile']["tmp_name"];
echo 'TMP-FILE-NAME: ' . $inputFileName;

$spreadsheet = IOFactory::load($inputFileName); //create new speedsheen object

$loadedSheetNames = $spreadsheet->getSheetNames(); //получаем имена листов
echo '<br>';

foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) { // выводим для наглядности Имена листов
	echo '<br/>' . "Номер и имя листа: " . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';
}
// выводим весь ezcel
foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {

    $worksheet = $spreadsheet->getSheet($sheetIndex);
    $worksheet = $spreadsheet->setActiveSheetIndexByName($loadedSheetName);

    $rows = $worksheet->toArray();
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
//-----------------
    echo "========================++++++++++++++++++++++++========================================" . '<br/>'
		. "Номер и имя листа: " . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';





//--------------
	//количество столбцов считает вместе с пустыми - не подходит
	/*$highestColumn = $worksheet->getHighestDataColumn(); // количество столбцов буквами
	echo "<br>" . "---" . "<br>"."highestColumn" . "=" . $highestColumn."<br>" . "---" . "<br>";

	$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); //количество столбцов цифрами
	echo "highestColumnIndex " . "=" . $highestColumnIndex;*/
//--------------



//Получаем количество строк
	$highestRow = $worksheet->getHighestRow(); //количество строк


//--Mergin cells
	$mergeCell = $worksheet->getMergeCells(); //taking margin cells on the sheet
// horizontalMarginArray
	$horizontalMargin = preg_grep('"A\d"', $mergeCell);//taking horizontal margin cells A-start on the sheet
	$margeCellCoordinate = array_keys($horizontalMargin);//-takes keys = cell coordinate

//Получаем количество колонок через первую объединенную ячейку
	//$margeCellCoordinate  - A1 / A5
	foreach ($margeCellCoordinate AS $value) { //
		$cellCoordinate = preg_replace('(.*:)', '', $value);//удаляем первую часть координат ячейки
	}
	$letterCellCoordinate = preg_replace('[\d]', '', $cellCoordinate[0]);//удаляем цыфру из координат ячейки - получаем "О"
	$lastColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($letterCellCoordinate); //количество столбцов цифрами

//Выводим кол-во строк и колонок
	echo "<br>" . "---" . "<br>" . "highestRow" . "=" . $highestRow  ;
	echo "<br>" . "---" . "<br>" . "highestColumn" . "=" . $lastColumnIndex . "<br>" . "---" . "<br>";

//Заполнияем MergeCells

	$mergedCellsRange = $worksheet->getMergeCells();

	foreach($mergedCellsRange as $currMergedRange) {
        $cell = [];
        if($cell->isInRange($currMergedRange)) {
		$currMergedCellsArray = PHPExcel_Cell::splitRange($currMergedRange);
			$cell = $this->activeSheet->getCell($currMergedCellsArray[0][0]);
			break;
		}
	}

//Getting CATEGORY

	echo "CATEGORY IS " . $category;


//--end Mergin cells

	echo "<table border=\"1\">";
	foreach ($rows AS $row) {

		echo "<tr>";
		foreach ($row AS $cell) {
			echo "<td>" . $cell . "</td>";
		}

	}
	echo '<br/>';
	echo "</table>";
}


function printArrayAsTable($arr) {
    if (empty($arr[0])) {
        pr('Массив пустой');
        return true;
    }
    $keys = array_keys($arr[0]);
    echo '<table class="table table-bordered table-hover table-responsive sortable-theme-bootstrap" data-sortable="" data-sortable-initialized="true">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>#</th>';
    foreach($keys as $key) {
        echo '<th>'.$key.'</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    $i=1;
    foreach($arr as $row) {
        echo '<tr>';
        echo '<td>'.$i++.'</td>';
        foreach($row as $column) {
            echo '<td>'.$column.'</td>';
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

function process($data) {
    $result = [];

    $data = removeEmptyColumns($data);

    foreach ($data as $item) {
        if (isEmptyRow($item)) {
            continue;
        }
        $result[] = $item;
    }

    $category = '';
    foreach ($result as $k=>$row) {
        if (isCategoryRow($row)) {
            $category = $row[0];
        }
        array_unshift($result[$k] , $category);
    }

    return $result;
}

function isEmptyRow($row) {
    $empty = true;
    foreach($row as $item) {
        if (!empty($item)) {
            $empty = false;
            return $empty;
        }
    }
    return $empty;
}

function removeEmptyColumns($data) {
    $columns = [];

    if (empty($data[0])) {
        return [];
    }

    $columns = array_keys($data[0]);
    foreach ($columns as $k=>$item) {
        $columns[$k] = false;
    }

    foreach ($data as $row) {
        foreach ($row as $k=>$item) {
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
        foreach ($row as $k=>$item) {
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

function isCategoryRow($row) {
    $result = true;
    if (empty($row[0])) {
        return false;
    }

    foreach($row as $k=>$item) {
        if ($k > 0) {
            if (!empty($item)) {
                return false;
            }
        }
    }

    return true;
}
function pr($v) {
    echo '<pre>';
    print_r($v);
    echo '</pre>';
}

?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title></title>
</head>
<body>
<h1></h1>
<?//printArrayAsTable($data);?>

<?//sendMessage($message);?>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>


