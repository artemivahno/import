<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once 'vendor/autoload.php';

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
echo '<br/>';

foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) { // выводим для наглядности
	echo '<br/>' ."Номер и имя листа: ". ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';
}

foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) { // выводим весь ezcel

	$sheet = $spreadsheet->getSheet($sheetIndex);

	echo "<table border=\"1\">";

	$rows = $sheet->toArray();
	foreach ($rows AS $row) {

		//echo '-----------------------------------------------<br/>';
		echo "<tr>";
		foreach ($row AS $cell) {
			/*if ($cell == "") {

			} else {*/
			echo "<td>" . $cell . "</td>";
		}

	}
	echo '<br/>';
}
echo "</table>";
