<?php
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action) {
    case 'addproduct':    addProduct();    break;
    case 'updateproduct': updateProduct(); break;
}

function addProduct() {
    $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : '';
}

function updateProduct() {
    $parameter = !empty($_REQUEST['parameter']) ? $_REQUEST['parameter'] : '';

    switch($parameter) {
        case 'description':     updateProductDescription();     break;
        case 'weight':          updateProductWeight();          break;
        case 'volume':          updateProductVolume();          break;
        case 'incomprice':      updateProductIncomingPrice();   break;
        case 'saleprice':       updateProductSalePrice();       break;
        case 'quantitypb':      updateProductQuantityPBox();    break;
        case 'box':             updateProductBox();             break;
        case 'volumebox':       updateProductVolumeBox();       break;
    }
}

function updateProductDescription() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $description = !empty($_REQUEST['description']) ? $_REQUEST['description']  : '';
}
function updateProductWeight() {
    $uuid       = !empty($_REQUEST['uuid'])         ? $_REQUEST['uuid']         : '';
    $weight     = !empty($_REQUEST['weight'])       ? $_REQUEST['weight']       : '';
}
function updateProductVolume() {
    $uuid       = !empty($_REQUEST['uuid'])         ? $_REQUEST['uuid']         : '';
    $volume     = !empty($_REQUEST['volume'])       ? $_REQUEST['volume']       : '';
}
function updateProductIncomingPrice() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $incomprice  = !empty($_REQUEST['incomprice'])  ? $_REQUEST['incomprice']   : '';
}
function updateProductSalePrice() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $saleprice   = !empty($_REQUEST['saleprice'])   ? $_REQUEST['saleprice']    : '';
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
