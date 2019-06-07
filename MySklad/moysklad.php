<?
	
    /*include_once ('counterparties.php');
    include_once ('paymentins.php');
    include_once ('paymentouts.php');
    include_once ('organizations.php');*/
    include_once ('demands.php');
    include_once('products.php');
//    include_once ('stock_reports.php');
//    include_once ('storages.php');
//    include_once ('variants.php');
   /* include_once ('contracts.php');
    include_once ('cashins.php');
    include_once ('cashouts.php');
    include_once ('metadata_states.php');
    include_once ('projects.php');*/


	// Скачиваем динамические данные за последний час
	function main($debug=false) {
		$stop = 300;
		
		// Настройки выборчи из Мой Склад
		$options = [];
		$options['limit']		= 100;	// Лимит максимальный 100
		$options['offset']		= 0; 	// Смещение
		//$options['sort']		= 'updated';	// Сортируем по времени обновления
		//$options['direction']	= 'desc';		// Сортируем по времени обновления
		
		// updated
		//$today1 = date('2018-01-01 00:00:00');
		//$today2 = date('2018-01-31 23:59:59');
		//$options['filter'] = urlencode("updated>$today1;updated<$today2");
		//$options['filter'] = urlencode("created>$today1;created<$today2");
		//$options['filter'] = urlencode("moment>$today1;moment<$today2");
		
		// moment
		//$today1 = date('2017-06-01 00:00:00');
		//$today2 = date('2017-06-07 23:59:59');
		//$options['filter'] = urlencode("moment>$today1;moment<$today2");
		
		// archived
		//$options['filter']		= urlencode("archived=true");
		
		// isDeleted
		//$options['filter'] = urlencode("isDeleted=true;isDeleted=false");
		
		
		
		// Скачиваем и сохраняем данные 
		$result = [];
		// $result = array_merge($result, downloadStorages($options, $stop, $debug));		// Склады - редко
        $result = array_merge($result, downloadStockReports($options, $stop, $debug));	// Отчет по остаткам

        if ($debug) {
        	pr($result);
        }

		return $result;
	}

    function getTotal($type, $options, $prefix='entity', $postfix=''){
        $options['limit'] = 1;
        $json = getJSON($type, $options, $postfix, $prefix);
        $arr = json_decode($json, true);
        return !empty($arr['meta']['size']) ? $arr['meta']['size'] : -1;
    }

    // Скачивание данных из Мой Склад рекурсивно
    // Не более 10 раз
    function download($type, $options, $stop=10, $postfix='', $prefix='entity') {
        $data = getJSON($type, $options, $postfix, $prefix);

        if (!empty($data)) {
            $data	= json_decode($data, true);
            $rows	= !empty($data['rows'])			? $data['rows']			: [];
            $size	= !empty($data['meta']['size'])	? $data['meta']['size']	: 0;

            if ($options['offset'] < $size && $stop > 0) {
                // Не более 99 раз качаем данные из Мой Склад
                $options['offset'] += $options['limit'];
                $stop--;
                list($r, $s) = download($type, $options, $stop, $postfix, $prefix);
                $rows = array_merge($rows, $r);
                $size = ($s > 0) ? $s : $size;
            }
        }
        return [$rows, $size];
    }

    // Получаем JSON из Мой Склад API
    function getJSON($type='', $options, $postfix='', $prefix='entity') {
        // Скачиваем информацию с МойСклад
        $fp = fsockopen("ssl://online.moysklad.ru", 443, $errno, $errstr, 30);
        if (!$fp) {
            echo "Error connect to MS: $errstr ($errno)\n";
            return false;
        }

        $url = "https://online.moysklad.ru/api/remap/1.1/{$prefix}/{$type}/{$postfix}";
        if (!empty($options)) {
            $url .= '?';
            foreach ($options as $k=>$v) {
                $url .= "$k=$v&";
            }
            $url .= '1=1';
        }
        $body = getContent($url, MS_LOGIN, MS_PASSWORD);
        //pr($url);
        //pr($body);


        // Проверка качества JSON
        $data = json_decode($body, true);
        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            //echo "<p>slug: $slug</p>";
            //echo "<p>URL: $url</p>";
            switch ($error) {
                case JSON_ERROR_NONE:
                    echo ' json_decode: Ошибок нет. ';
                    break;
                case JSON_ERROR_DEPTH:
                    echo ' json_decode: Достигнута максимальная глубина стека. ';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo ' json_decode: Некорректные разряды или не совпадение режимов. ';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo ' json_decode: Некорректный управляющий символ. ';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo ' json_decode: Синтаксическая ошибка, не корректный JSON. ';
                    break;
                case JSON_ERROR_UTF8:
                    echo ' json_decode: Некорректные символы UTF-8, возможно неверная кодировка. ';
                    break;
                default:
                    echo ' json_decode: Неизвестная ошибка. ';
                    break;
            }
        }

        return $body;
    }

    function getContent($url, $login, $password) {
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = "Authorization: Basic ".base64_encode($login.':'.$password);
        //$headers[] = "Authorization: Basic YWRtaW5AYXZyb3JhbWFya2V0Ynk6cGU0cFdMeXY=";
        $headers[] = "Accept-Encoding: gzip, deflate, br";
        $headers[] = "Accept-Language: ru,en;q=0.8,de;q=0.6,pl;q=0.4,pt;q=0.2,be;q=0.2,sv;q=0.2";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
        $headers[] = "Cache-Control: max-age=0";
        $headers[] = "Cookie: JSESSIONID=9tvuSseOkQGP6-MFZh6h7sibVFiPs4fsFQ2Z_ajp.exchange; moysklad.firstEntryPoint=http%3A%2F%2Fonline.moysklad.ru%2Flogon; moysklad.reseller=LogneX; _ym_uid=1507056972431309527; _ym_isad=1; roistat_marker_old=; roistat_visit=8816518; ___dc=755d0738-ebd4-4eaf-b34b-8cf5f8e85a0a; roistat_call_tracking=0; roistat_emailtracking_email=null; JSESSIONID=9tvuSseOkQGP6-MFZh6h7sibVFiPs4fsFQ2Z_ajp.MOYsklad; MSSESSIONIDONLINE=qck0tol5nvz61m23aelwbthen_2ebd731f-c0a9-11e4-90a2-8ecb00002bf9; _ga=GA1.2.1170343631.1507056972; _gid=GA1.2.237387032.1507056972";
        $headers[] = "Connection: keep-alive";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        return $result;
    }

    // Скачиваем и сохраняем Контрагентов
    function downloadCounterparties($options, $stop=10, $debug=false) {
        $type = 'counterparty';
        $options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        $options['expand'] .= 'accounts';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladCounterparties::saveItems($items, $size);
    }

    // Скачиваем и сохраняем Входящие платежи
    function downloadPaymentins($options, $stop=10, $debug=false) {
        $type = 'paymentin';
        $options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        //$options['expand'] .= 'accounts';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladPaymentins::saveItems($items, $size);
    }

    // Скачиваем и сохраняем Исходящие платежи
    function downloadPaymentouts($options, $stop=10, $debug=false) {
        $type = 'paymentout';
        $options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        //$options['expand'] .= 'accounts';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladPaymentouts::saveItems($items, $size);
    }

    // Скачиваем и сохраняем Организации
    function downloadOrganizations($options, $stop=10, $debug=false) {
        $type = 'organization';
        $options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        $options['expand'] .= 'accounts';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladOrganizations::saveItems($items, $size);
    }

    // Скачиваем и сохраняем Договора
    function downloadContracts($options, $stop=10, $debug=false) {
        $type = 'contract';
        //$options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        //$options['expand'] .= 'accounts';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladContracts::saveItems($items, $size);
    }

    // Скачиваем и сохраняем Отгрузки
    function downloadDemands($options, $stop=10, $debug=false) {
        $type = 'demand';
        $options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        $options['expand'] .= 'positions,positions.assortment';
        $options['filter'] = !empty($options['filter']) ? $options['filter'].urlencode(';') : '';
        $options['filter'] .= urlencode('isDeleted=true;isDeleted=false');
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);
        return CKayaMoyskladDemands::saveItems($items, $size);
    }

    // Скачиваем и сохраняем Продукты
    function downloadProducts($options, $stop=10, $debug=false) {
        $type = 'product';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladProducts::saveItems($items, $size, 'Products');
    }

    // Скачиваем и сохраняем Приходные ордера
    function downloadCashins($options, $stop=10, $debug=false) {
        $type = 'cashin';
        $options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        $options['expand'] .= 'agent';
        $options['filter'] = urlencode("isDeleted=true;isDeleted=false");

        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladCashins::saveItems($items, $size, 'Cashins');
    }

    // Скачиваем и сохраняем Расходные ордера
    function downloadCashouts($options, $stop=10, $debug=false) {
        $type = 'cashout';
        $options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        $options['expand'] .= 'agent';
        //$options['expand'] .= 'accounts';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladCashouts::saveItems($items, $size, 'Cashins');
    }

    function downloadStockReports($options, $stop=10, $debug=false, $storageUuid='') {
        // Очищаем таблицу
        //$sql = "TRUNCATE ms_stock_reports;";
        //dbQuery($sql);

        $type = 'stock';
        $prefix = 'report';
        $postfix = 'all';
        if (!empty($storageUuid)) {
            if (is_array($storageUuid)) {
                $options['store.id'] = join($storageUuid, ';');
            }
            if (is_string($storageUuid)) {
                $options['store.id'] = $storageUuid;
            }
        }
        $options['limit']=1000;
        $options['stockMode']='nonEmpty';
        $options['groupBy']='product';
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".($stop*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options, $prefix, $postfix).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop, $postfix, $prefix);

        return CKayaMoyskladStockReports::saveItems($items, $size, $storageUuid);
    }

	// Скачиваем и сохраняем Склады
	function downloadStorages($options, $stop=10, $debug=false) {
		$type = 'store';
		if ($debug) {
			echo "Тип: $type<br>";
			echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
			echo 'Всего значений: ';
			//echo getTotal($type, $options).'<br>';
			echo 'Настройки:';
			pr($options);
            return [];
		}
		list($items, $size)	= download($type, $options, $stop);
	
		return CKayaMoyskladStorages::saveItems($items, $size);
	}

    // Скачиваем и сохраняем Варианты
    function downloadVariants($options, $stop=10, $debug=false) {
        $type = 'variant';
        //$options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        //$options['expand'] .= 'positions';//,positions.assortment
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            //echo self::getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }
        list($items, $size)	= download($type, $options, $stop);

        return CKayaMoyskladVariants::saveItems($items, $size);
    }

    // Скачиваем и сохраняем Статусы сущностей
    function downloadMetadataStates($options, $stop=10, $debug=false) {
        $type = 'metadata';
        //$options['expand'] = !empty($options['expand']) ? $options['expand'].urlencode(',') : '';
        //$options['expand'] .= 'positions';//,positions.assortment
        if ($debug) {
            echo "Тип: $type<br>";
            echo "Стоп: $stop — хватит для выкачивания ".(($stop+1)*$options['limit'])." значений<br>";
            echo 'Всего значений: ';
            //echo self::getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }

        $data = getJSON($type, $options, '', 'entity');

        if (!empty($data)) {
            $arr = json_decode($data, true);
        }

        return CKayaMoyskladMetadataStates::saveItems($arr);
    }




    // Скачиваем и сохраняем Проекты
    function downloadProjects($options, $stop=10, $debug=false) {
        $type = 'project';

        if ($debug) {
            echo "Тип: $type<br>";
            echo "STOP: $stop — хватит для ".($stop*100)." значений<br>";
            echo 'Всего значений: ';
            echo getTotal($type, $options).'<br>';
            echo 'Настройки:';
            pr($options);
            return [];
        }

        $filter = !empty($options['filter']) ? $options['filter'] : [];

        // Обычные проекты
        $options['filter'] = !empty($filter) ? $filter.';' : '';
        $options['filter'] .= urlencode("archived=false");
        list($items, $size)	= download($type, $options, $stop);
        $r1 = CKayaMoyskladProjects::saveItems($items, $size);

        // Архивные проекты
        $options['filter'] = !empty($filter) ? $filter.';' : '';
        $options['filter'] .= urlencode("archived=true");
        list($items, $size)	= download($type, $options, $stop);
        $r2 = CKayaMoyskladProjects::saveItems($items, $size);

        return ['notarchived'=>$r1, 'archived'=>$r2];
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

// Обновление массива. Отправка данных в Мой Склад
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