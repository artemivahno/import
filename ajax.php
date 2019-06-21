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
    //echo $parameter;
    switch($parameter) {
        case 'ProductAliasValue':           updateProductName();            break;
        case 'DescriptionAliasValue':       updateProductDescription();     break;
        case 'weight':                      updateProductWeight();          break;
        case 'volume':                      updateProductVolume();          break;
        case 'PriceUSDAlias':               updateProductIncomingPrice();   break;
        case 'MSRP_USD_Alias':              updateProductSalePrice();       break;
        case 'quantitypb':                  updateProductQuantityPBox();    break;
        case 'box':                         updateProductBox();             break;
        case 'volumebox':                   updateProductVolumeBox();       break;
    }
}

function updateProductName() {
    $uuid       = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']      : '';
    $name       = !empty($_REQUEST['value'])       ? $_REQUEST['value']     : '';
    echo $uuid, $name;
}
function updateProductDescription() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']     : '';
    $description = !empty($_REQUEST['value'])       ? $_REQUEST['value']    : '';
    echo $description;
}
function updateProductWeight() {
    $uuid       = !empty($_REQUEST['uuid'])         ? $_REQUEST['uuid']         : '';
    $weight     = !empty($_REQUEST['value'])        ? $_REQUEST['value']        : '';
}
function updateProductVolume() {
    $uuid       = !empty($_REQUEST['uuid'])         ? $_REQUEST['uuid']         : '';
    $volume     = !empty($_REQUEST['value'])        ? $_REQUEST['value']        : '';
}
function updateProductIncomingPrice() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']         : '';
    $incomprice  = !empty($_REQUEST['value'])       ? $_REQUEST['value']        : '';
    echo "$incomprice";
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
