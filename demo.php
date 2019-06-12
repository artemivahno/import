<?php

use MoySklad\MoySklad;


require_once 'vendor/tooyz/moysklad/src/Entities/Products/Product.php';
require_once 'config.php';
require_once 'vendor/tooyz/moysklad/src/Entities';

auth();
function auth()
{
    $sklad = MoySklad::getInstance(MS_LOGIN, MS_PASSWORD);
    $product = new \Tests\Cases\AuthTest();
    //$list = Product::query($sklad)->getList();
    print_r($product);
    /*    $product = new Product($sklad, [
        "name" => "Банан"
    ])*/;

    /*$sklad = MoySklad::getInstance(MS_LOGIN, MS_PASSWORD);
    //$sklad1 = MoySklad::getInstance(MS_LOGIN, MS_PASSWORD);
    print_r($sklad);*/
}