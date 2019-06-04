<?php

$dbHost = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "products";

$dbc = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUsername, $dbPassword);

$key1 = isset($_REQUEST['key1']) ? $_REQUEST['key1'] :"";
$key2 = isset($_REQUEST['key2']) ? $_REQUEST['key2'] :"";

//die("$key1,$key2");

$sql = "INSERT INTO ms_products (name, barcodes) VALUES (:name, :barcodes)";

try {
    $sth = $dbc->prepare($sql);

    // bind parameter values
    $sth->bindValue(':name', $key1, PDO::PARAM_STR);
    $sth->bindValue(':barcodes', $key2, PDO::PARAM_STR);
    $sth->execute();
    echo "Товар загружен в базу данных";

} catch (PDOException $e) {
    echo "something went wrong";
    // log an error or whatever
}
