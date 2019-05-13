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
		}

		echo "ALL IMAGES ARE SAVED";*/


//======================================
//-----------------
	echo "========================++++++++++++++++++++++++========================================" . '<br/>'
		. "Номер и имя листа: " . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';


	$worksheet = $spreadsheet->getSheet($sheetIndex);
	$worksheet = $spreadsheet->setActiveSheetIndexByName($loadedSheetName);

	$rows = $worksheet->toArray();



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

	//$mergedCellsRange = $this->activeSheet->getMergeCells();

	foreach($mergeCell as $currMergedRange) {
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


