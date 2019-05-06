<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
echo 'NAME: ' . $inputFileName;

$spreadsheet = IOFactory::load($inputFileName); //create new speedsheen object

$loadedSheetNames = $spreadsheet->getSheetNames(); //получаем имена листов

var_dump($loadedSheetNames);

foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) { // выводим для наглядности
	echo '<br/>' . ($sheetIndex . ' -> ' . $loadedSheetName) . '<br/>';
}

$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();
foreach ($rows AS $r) {
    echo '-----------------------------------------------<br/>';
    foreach ($r AS $c) {
	    if($c == "") {

	    }
	    else {
		    echo '<br>[' . $c . ']';
	    }

    }
    echo '<br/>';
}