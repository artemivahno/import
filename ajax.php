<?php

require_once 'config.php';
require_once 'core.php';


//$usd        = !empty($_REQUEST['usd'])          ? $_REQUEST['usd'] : '';
$action     = !empty($_REQUEST['action'])       ? $_REQUEST['action'] : '';
$uuid       = !empty($_REQUEST['uuid'])         ? $_REQUEST['uuid']      : '';
$value      = !empty($_REQUEST['value'])        ? $_REQUEST['value']     : '';

switch($action) {
    case 'addproduct':    list($uuid, $error) = addProduct(); echo $uuid, $error;  break;

    case 'updateproduct': updateProduct($uuid, $value); break;
}

function addProduct() {
    $name =             !empty($_REQUEST['name'])           ? $_REQUEST['name'] : '';
    $barcodes =         !empty($_REQUEST['barcodes'])       ? $_REQUEST['barcodes'] : '';
    $description =      !empty($_REQUEST['description'])    ? $_REQUEST['description'] : '';
    $weight =           !empty($_REQUEST['weight'])         ? $_REQUEST['weight'] : '';
    $volume =           !empty($_REQUEST['volume'])         ? $_REQUEST['volume'] : '';

    $buyPrice =         !empty($_REQUEST['buyPrice'])       ? $_REQUEST['buyPrice'] : '';
    $msrpPrice =        !empty($_REQUEST['msrpPrice'])      ? $_REQUEST['msrpPrice'] : '';
    $usd =              !empty($_REQUEST['usd'])            ? $_REQUEST['usd'] : '';
    $manufacturer =     !empty($_REQUEST['manufacturer'])   ? $_REQUEST['manufacturer'] : '';

    //$packingQty =       !empty($_REQUEST['packingQty'])     ? $_REQUEST['packingQty'] : '';
    $innerQty =         !empty($_REQUEST['innerQty'])&& is_numeric($_REQUEST['innerQty'])
                                ? (float)$_REQUEST['innerQty'] : '';

    $buyPrice = dellUSD($buyPrice,$usd);
    $msrpPrice = dellUSD($msrpPrice,$usd);
    pr($innerQty);
    $weight = prepareWeight($weight);
    $volume = prepareVolume($volume);
//die('first');

    $formdata = [
        'name'              => $name,
        'weight'            => $weight,
        'volume'            => $volume,
        'vat'               => 20,
        'barcodes'          => [$barcodes],
        'description'       => $description,
        //не работает добавление в это поле 'minimumBalance'    => $packingQty,
        // метаданные ШТ
        'uom'               => [
                                'meta'=> [
                                    'href' => 'https://online.moysklad.ru/api/remap/1.1/entity/uom/19f1edc0-fc42-4001-94cb-c9ec9c62ec10',
                                    'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/uom/metadata',
                                    'type'			=> 'uom',
                                    'mediaType'		=> 'application/json'
                                        ]

                            ],
        // метаданные Страна
        'country'           =>  [
                                'meta'=>    [
                                    'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/country/fd44cd2e-b398-4222-9c43-f75688bdf327',
                                    'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/country/metadata',
                                    'type'			=> 'country',
                                    'mediaType'		=> 'application/json',
                                    'uuidHref'		=> 'https://online.moysklad.ru/app/#country/edit?id=fd44cd2e-b398-4222-9c43-f75688bdf327'
                                            ]
                                ],

        'packs'           =>    [
                                    [
                                        'uom' =>[
                                                'meta'=>    [
                                                    'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/uom/e4db6a0a-9641-11e9-9ff4-31500012e807',
                                                    'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/uom/metadata',
                                                    'type'          => 'uom',
                                                    'mediaType'		=> 'application/json',
                                                    ]
                                                ],
                                        'quantity' => $innerQty,
                                    ],
                                ],


        'buyPrice'           =>    [
                                'value' => $buyPrice,
                                'currency' =>[
                                    'meta'=>    [
                                        'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/currency/f7b01c7f-86b9-11e9-9107-504800062334',
                                        'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/currency/metadata',
                                        'type'			=> 'currency',
                                        'mediaType'		=> 'application/json',
                                        'uuidHref'		=> 'https://online.moysklad.ru/app/#currency/edit?id=f7b01c7f-86b9-11e9-9107-504800062334'
                                                ]
                                        ],
                                ],
        'salePrices'           =>    [
                                        [
                                'value' => $msrpPrice,
                                'currency' =>[
                                    'meta'=>    [
                                        'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/currency/f7b01c7f-86b9-11e9-9107-504800062334',
                                        'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/currency/metadata',
                                        'type'			=> 'currency',
                                        'mediaType'		=> 'application/json',
                                        'uuidHref'		=> 'https://online.moysklad.ru/app/#currency/edit?id=f7b01c7f-86b9-11e9-9107-504800062334'
                                                ]
                                        ],
                                'priceType' => 'Цена EXW China',
                                        ],
                                ],
    ];
    //проверяем является ли количество числом больше 0 если нет, удаляем его из массива
    if (!is_numeric($innerQty)&& !$innerQty>0){
        unset($formdata['packs']);
    }
    //pr($innerQty);
//die();
    $body = putJSONarray('product', $formdata, 'POST');

    $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
    $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';

    return [$uuid, $error];
}

/**
 * @param string $buyPrice
 * @return string|string[]|null
 */
function dellUSD($buyPrice, $usd)
{
    $buyPrice = preg_replace("/[^,.0-9]/", '', $buyPrice);
    $buyPrice = (float)$buyPrice*100;
    return $buyPrice;
}


function updateProduct($uuid, $value) {
    $parameter = !empty($_REQUEST['parameter']) ? $_REQUEST['parameter'] : '';
    switch($parameter) {
        case 'ProductAliasValue':           updateProductSimple($uuid, 'name', $value);            break;
        case 'DescriptionAliasValue':       updateProductSimple($uuid, 'description', $value);     break;

        case 'weight':                      updateProductWeight($uuid, $value);          break;
        case 'volume':                      updateProductVolume($uuid, $value);          break;

        case 'PriceUSDAlias':               updateProductIncomingPrice($uuid, $value);   break;

        case 'MSRP_USD_Alias':              updateProductSalePrice($uuid, 'weight', $value);       break;
        case 'quantitypb':                  updateProductQuantityPBox($uuid, 'weight', $value);    break;
        case 'box':                         updateProductBox($uuid, 'weight', $value);             break;
        case 'volumebox':                   updateProductVolumeBox($uuid, 'weight', $value);       break;
    }
}
//Обновление полей без метаданных
function updateProductSimple($uuid, $key, $value) {
    $formdata =[
        'id'       => $uuid,
        $key       => $value
    ];
    $body = putJSON($uuid,'product', $formdata);

    $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
    $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';
    pr($value ." / ". $uuid);
    return [$uuid, $error];
}

function updateProductWeight($uuid, $value) {
    $value = prepareWeight($value);
    $formdata =[
        'id'       => $uuid,
        'weight'   => $value
    ];

    $body = putJSON($uuid,'product', $formdata);

    $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
    $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';
    pr($value ." / ". $uuid);
    return [$uuid, $error];
}
function updateProductVolume($uuid, $value) {
    $value = prepareVolume($value);
    $formdata =[
        'id'       => $uuid,
        'volume'   => $value
    ];

    $body = putJSON($uuid,'product', $formdata);

    $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
    $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';
    pr($value ." / ". $uuid);
    return [$uuid, $error];
}

function updateProductIncomingPrice($uuid, $value) {
    $formdata =[
        'id'        => $uuid,
        'value'     => $value,
        'currency'  =>[
            'meta'  =>    [
                'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/currency/f7b01c7f-86b9-11e9-9107-504800062334',
                'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/currency/metadata',
                'type'			=> 'currency',
                'mediaType'		=> 'application/json',
                'uuidHref'		=> 'https://online.moysklad.ru/app/#currency/edit?id=f7b01c7f-86b9-11e9-9107-504800062334'
            ]
        ],
    ];

    $body = putJSON($uuid,'buyPrice', $formdata);

    $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
    $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';
    pr($value ." / ". $uuid);
    return [$uuid, $error];
}

function updateProductSalePrice() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $saleprice   = !empty($_REQUEST['value'])       ? $_REQUEST['value']        : '';
    echo "$saleprice";
}
function updateProductQuantityPBox() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $quantitypb  = !empty($_REQUEST['quantitypb'])  ? $_REQUEST['quantitypb']   : '';
}
function updateProductBox() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $box         = !empty($_REQUEST['box'])         ? $_REQUEST['box']          : '';
}
function updateProductVolumeBox() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $volumebox   = !empty($_REQUEST['volumebox'])   ? $_REQUEST['volumebox']    : '';
}

function prepareWeight ($weight){
    $weight = is_numeric($weight) ? $weight/1000 : 0;//переводим вес в килограммы
    return $weight;
}

/**
 * @param string $volume
 * @return float|int|string
 */
//переводим объем в м3
function prepareVolume(string $volume)
{
    $volume = array_product(explode("*", $volume));//габариты перемножаем с разделением по * получаем объем
    $volume = is_numeric($volume) ? $volume / 1000000 : 0;
    return $volume;
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
    echo $body;

    //die('asdfasdfa');

    $body = json_decode($body, true);
    return $body;
}

// Обновление
function putJSON($id, $type, $data) {
    // Когда помимо проектов еще что-то добавлять надо будет в МС - взять код из addProject
    $poststring = json_encode($data);

    // Добавляем проект в МойСклад
    $fp = fsockopen("ssl://online.moysklad.ru", 443, $errno, $errstr, 30);
    if (!$fp) {
        echo "Error connect to MS: $errstr ($errno)\n";
        return false;
    }

    $out = "PUT /api/remap/1.1/entity/$type/$id";
    $out .= " HTTP/1.1\r\n";
    $out .= "Host: online.moysklad.ru\r\n";
    $out .= "Authorization: Basic " . base64_encode(MS_LOGIN.':'.MS_PASSWORD) . "\r\n";
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