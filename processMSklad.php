<?php

require_once 'config.php';
require_once 'core.php';
//require_once 'loadExcel.php';
require_once 'MySklad/moysklad.php';
require_once 'MySklad/products.php';

/*$products = getJSON('product','');
$myJSON = json_decode($products);
print_r($myJSON);*/

saveMStoDB();
function saveMStoDB()
{

    $options = main(true);
    $getProd = getJSON('product', '');
    $getProd = saveItems();
    pr($getProd);

    /*echo "json is loaded";
    pr($body);*/

    /*$products = getJSON('product', '');

    $myJSON = json_decode($products);
    print_r($myJSON);*/
}

