<?php
require_once 'MySklad/products.php';
require_once 'core.php';
require_once 'config.php';

$keyName = isset($_REQUEST['key1']) ? $_REQUEST['key1'] :"";
$keyCode = isset($_REQUEST['key2']) ? $_REQUEST['key2'] :"";
$keyDescr = isset($_REQUEST['key3']) ? $_REQUEST['key3'] :"";
$weight = isset($_REQUEST['key4']) ? $_REQUEST['key4'] :"";// вес
$volume = isset($_REQUEST['key5']) ? $_REQUEST['key5'] :"";// объем
$buyPrice = isset($_REQUEST['key6']) ? $_REQUEST['key6'] :"";

$manufacturer =  $_POST['manufacturer'];
$volume = array_product(explode("*", $volume));//габариты перемножаем с разделением по * получаем объем

addProductMS($keyName,$keyCode,$keyDescr,$weight,$volume,$buyPrice);

function addProductMS($name,$barcodes,$description,$weight,$volume,$buyPrice) {

    $formdata = [
        'name'              => $name,
        'weight'            => $weight/1000,
        'volume'            => $volume/1000000,
        //'buyPrice'          => $buyPrice,
        'vat'               => 20,
        'barcodes'          => [$barcodes],
        'description'       => $description,
    ];
    $body = putJSONarray('product', $formdata, 'POST');

    $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
    $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';

    return [$uuid, $error];
}

function putJSONarray($type, $data, $method='PUT') {
    $poststring = json_encode($data);

    // Добавляем проект в МойСклад
    $fp = fsockopen("ssl://online.moysklad.ru", 443, $errno, $errstr, 30);
    if (!$fp) {
        echo "Error connect to MS: $errstr ($errno)\n";
        return false;
    }

    $out = "$method /api/remap/1.1/entity/$type";
    $out .= " HTTP/1.1\r\n";
    $out .= "Host: online.moysklad.ru\r\n";
    $out .= "Authorization: Basic " . base64_encode( MS_LOGIN.':'.MS_PASSWORD) . "\r\n";
    $out .= "Content-Type: application/json \r\n";
    $out .= "Accept: */*\r\n";
    $out .= "Content-length: ".strlen($poststring)."\r\n";
    $out .= "Connection: close\r\n\r\n";
    $out .= "$poststring\r\n\r\n";

    $body = '';
    fwrite($fp, $out);
    while ($str = trim(fgets($fp, 4096)));
    while (!feof($fp)) {
        $str = fgets($fp, 128);
        if (strlen($str)==6) {
            continue;
        }
        $body .= $str;
    }
    fclose($fp);

    $body = json_decode($body, true);
    return $body;
}



//Добавляет товар в базу с указанными полями
/*$item = [];
$item['name'] = $key1;
$item['barcodes'] = $key2;
//$item['group'] = "stdClass Object";
//die(print_r($item));
$saveItem = new CKayaMoyskladProducts();
//die(print_r($item));
$items[]=$item;

$n= $saveItem::saveItems($items);
pr($n);*/

/*$dbc = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_BASE, DB_USER, DB_PASS);
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
}*/
