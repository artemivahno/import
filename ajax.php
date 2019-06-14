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
        case 'description': updateProductDescription(); break;
        case 'weight':      updateProductWeight();      break;
    }
}

function updateProductDescription() {
    $uuid        = !empty($_REQUEST['uuid'])        ? $_REQUEST['uuid']        : '';
    $description = !empty($_REQUEST['description']) ? $_REQUEST['description'] : '';
}

function updateProductWeight() {
    $uuid   = !empty($_REQUEST['uuid'])   ? $_REQUEST['uuid']   : '';
    $weight = !empty($_REQUEST['weight']) ? $_REQUEST['weight'] : '';
}