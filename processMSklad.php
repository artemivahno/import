<?php

require_once 'config.php';
require_once 'core.php';
require_once 'MySklad/moysklad.php';

downloadProducts('', '');

header( "refresh:5;url=/" );
echo 'Все товары загружены в локальную Базу данных, <br> 
Вы будете перенаправлены на главную, если нет, то <br>
нажмите <a href="/">Назад</a>.';


