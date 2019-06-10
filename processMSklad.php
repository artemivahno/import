<?php

require_once 'config.php';
require_once 'core.php';
//require_once 'loadExcel.php';
require_once 'MySklad/moysklad.php';
require_once 'MySklad/products.php';


saveMStoDB();
//work good - сохраняет продукты в DB
function saveMStoDB()
{
    $getProd = downloadProducts('', '');
    pr($getProd );

}

