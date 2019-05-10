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
foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) { // выводим для наглядности
	echo '<br/>' ."Номер и имя листа: ". ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';
}

/*$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileName);
$reader->setReadDataOnly(TRUE);
$spreadsheet = $reader->load($inputFileName);*/


//через Итератор активный лист
/*$worksheet = $spreadsheet->getActiveSheet();

echo '<table border="1">' . PHP_EOL;
foreach ($worksheet->getRowIterator() as $row) {
    echo '<tr>' . PHP_EOL;
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
                                                       //    even if a cell value is not set.
                                                       // By default, only cells that have a value
                                                       //    set will be iterated.
    foreach ($cellIterator as $cell) {
        echo '<td>' .
             $cell->getValue() .
             '</td>' . PHP_EOL;
    }
    echo '</tr>' . PHP_EOL;
}
echo '</table>' . PHP_EOL;

exit();*/

// выводим весь ezcel
foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {

	$sheet = $spreadsheet->getSheet($sheetIndex);

	echo "<table border=\"1\">";

	$rows = $sheet->toArray();

//--Mergin cells
	$mergeCell = $sheet->getMergeCells(); //taking margin cells on the sheet
	var_dump($mergeCell);
	echo "<br>";

	$horizontalMargin = preg_grep('"A\d"', $mergeCell);//taking horizontal margin cells A-start on the sheet
	var_dump($horizontalMargin);


	echo "<br>";
	echo $horizontalMarginArray;

	echo "<br>";
	$margeCellCoordinate = array_keys($horizontalMargin);//-takes cell coordinate

	var_dump($margeCellCoordinate);
	echo "<br>";
	//$margeCellCoordinate  - A1 / A5
	foreach ($margeCellCoordinate AS $value)
		$cellCoordinate = preg_replace('(:.*)', '', $margeCellCoordinate);
	var_dump($cellCoordinate);
	echo "<br>";

	//$cellCoordinateByRow  - dell A = 1 / 5 ...
	var_dump($cellCoordinateByRow);
	echo "<br>";

	foreach ($cellCoordinate as $value)
		$cellCoordinateByRow = preg_replace('(A)', '', $cellCoordinate);

	var_dump($cellCoordinateByRow);
	echo "<br>";

//Getting CATEGORY
	foreach ($cellCoordinateByRow as $value) {
		$check = $value;
		echo "<br> cellCoordinateByRow ".$value;
		//print_r($check);
		$tmp = $sheet->getCellByColumnAndRow(4, $check);
		echo "<br> CellByColumnAndRow ".$tmp;
		foreach ($cellCoordinate as $value)
			if ($sheet->getCell($value) != "" || $sheet->getCellByColumnAndRow(4, $check) == "")
				$category = $sheet->getCell($value)->getValue();
	}
	echo "<br>";

	echo "CATEGORY IS " . $category;



//--end Mergin cells
	foreach ($rows AS $row) {
		/*if (getMergeCells()){
		echo " НАШЕЛ ";
	}*/

		//print_r($rows);

		echo "<tr>";
		foreach ($row AS $cell) {
			echo "<td>" . $cell . "</td>";
		}

	}
	echo '<br/>';
}
echo "</table>";

