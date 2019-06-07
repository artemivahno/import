<?
	include 'config.php';
	// Яндро
	
	// Выводит значение переменной
	function pr($v) {
		print('<pre>');
		print_r($v);
		print('</pre>');
	}
	
	// Выводит ассоциативный массив в виде таблицы
	function printArrayAsTable($arr) {
		if (empty($arr[0])) {
			pr('Массив пустой');
			return true;
		}
		$keys = array_keys($arr[0]);
		echo '<table class="table table-bordered table-hover table-responsive sortable-theme-bootstrap" data-sortable="" data-sortable-initialized="true">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>#</th>';
		foreach($keys as $key) {
			echo '<th>'.$key.'</th>';
		}
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		$i=1;
		foreach($arr as $row) {
			echo '<tr>';
			echo '<td>'.$i++.'</td>';
			foreach($row as $column) {
			    if (is_array($column)) {
                    echo '<td><pre>'.print_r($column, true).'</pre></td>';
                } else {
                    echo '<td>'.$column.'</td>';
                }
				
			}
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
	
	function dbQuery($query='') {
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS) or die("Couldn't connect to the MySQL server\n");
		mysqli_query($link, 'SET NAMES utf8') or die("Invalid set utf8 " . mysqli_error($link)."\n");
		$db = mysqli_select_db($link, DB_BASE) or die("db can't be selected\n");
		
		$result = mysqli_query($link, $query) or die("Query error: ".mysqli_error($link).'['.$query.']'."\n");
		mysqli_close($link);
		return $result;
	}

	function dbQueryArray($query='') {
		$result = dbQuery($query);
		$data = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = $row;
		}
		mysqli_free_result($result);
		
		return $data;
	}

	// Когда надо, не теряя подключения к БД выполнить сет запросов
	function dbSet($sqls, $sql) {
        $link = mysqli_connect(DB_HOST, DB_USER, DB_PASS) or die("Couldn't connect to the MySQL server\n");
        mysqli_query($link, 'SET NAMES utf8') or die("Invalid set utf8 " . mysqli_error($link)."\n");
        $db = mysqli_select_db($link, DB_BASE) or die("db can't be selected\n");

        foreach($sqls as $query) {
            $result = mysqli_query($link, $query) or die("Query error: ".mysqli_error($link).'['.$query.']'."\n");
        }

        // Результат выполнения последнего запроса возвращаем
        $query = $sql;
        $result = mysqli_query($link, $query) or die("Query error: ".mysqli_error($link).'['.$query.']'."\n");

        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);


        mysqli_close($link);
        return $data;
    }
	
	// Склоняет 0 1 2
	function getNormalCase($number, $Rm='', $I='', $Ro='') {
		// Проверка
		if ($Rm=='' || $I=='' || $Ro=='' || strlen($number)==0) return '';
		$rez = '';
		// Последняя цифра
		if (strlen($number)>0) $last = substr($number, strlen($number)-1);
		if (in_array($last, array('0','5','6','7','8','9'))) $rez = $Rm;
		if (in_array($last, array('1')))      $rez = $I;
		if (in_array($last, array('2','3','4')))    $rez = $Ro;
		// Исключения 11 12 13 14
		if (strlen($number) >= 2) if (substr($number, strlen($number)-2, 1) == '1') $rez = $Rm;
		// Результат
		return $rez;
	}
	
	function translit($str='') {
		if ($str == '') return '';
		$str = $str;
		$str = str_replace(array('а', 'б', 'в', 'г', 'д', 'е', 'ё',		'ж',	'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч',	'ш',	'щ',	'ь', 'ъ', 'ы', 'э', 'ю',	'я')
				, array('a', 'b', 'v', 'g', 'd', 'e', 'jo',	'zh',	'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'x', 'c', 'ch',	'sh',	'sch',	'j', 'j', 'y', 'e', 'yu',	'ja'), $str);
		$str = str_replace(array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё',		'Ж',	'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч',	'Ш',	'Щ',	'Ь', 'Ъ', 'Ы', 'Э', 'Ю',	'Я')
				, array('A', 'B', 'V', 'G', 'D', 'E', 'JO',	'ZH',	'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'X', 'C', 'CH',	'SH',	'SCH',	'J', 'J', 'Y', 'E', 'YU',	'JA'), $str);
		return $str;
	}
	
	// date
	function niceDate($date) {
		if (empty($date)) {
			return '';
		}
		return '<span class="text-muted" title="'.niceDateTitle($date).'">'.niceDateText($date).'</span>';
	}
	function niceDateTitle($date) {
		$months = array('1' => 'января','2' => 'февраля','3' => 'марта','4' => 'апреля','5' => 'мая','6' => 'июня','7' => 'июля','8' => 'августа','9' => 'сентября','10' => 'октября','11' => 'ноября','12' => 'декабря');
		$m = $months[date('n', strtotime($date))];
		return date("j {$m} Y", strtotime($date));
	}
	function niceDateText($date) {
		$time = strtotime($date);
		switch(date('Y-m-d', $time)) {
			case date('Y-m-d'):
				return 'сегодня';
			case date('Y-m-d', strtotime("yesterday")):
				return 'вчера';
		}
		$str = date('j', $time).' ';
		$months = array('1' => 'янв','2' => 'фев','3' => 'мар','4' => 'апр','5' => 'мая','6' => 'июн','7' => 'июл','8' => 'авг','9' => 'сен','10' => 'окт','11' => 'ноя','12' => 'дек');
		$str .= $months[date('n', $time)];
		if (date('Y', $time) != date('Y')) {
			$str .= ' '.date('\'y', $time);
		}
		return $str;
	}
	
	// datetime
	function niceDateTime($datetime) {
		if (empty($datetime)) {
			return '';
		}
		return '<span class="text-muted" title="'.niceDateTimeTitle($datetime).'">'.niceDateTimeText($datetime).'</span>';
	}
	function niceDateTimeTitle($datetime) {
		if (empty($datetime)) {
			return '';
		}
		$months = array('1' => 'января','2' => 'февраля','3' => 'марта','4' => 'апреля','5' => 'мая','6' => 'июня','7' => 'июля','8' => 'августа','9' => 'сентября','10' => 'октября','11' => 'ноября','12' => 'декабря');
		$m = $months[date('n', strtotime($datetime))];
		return date("j {$m} Y в H:i", strtotime($datetime));
	}
	function niceDateTimeText($datetime) {
		$time = strtotime($datetime);
		switch(date('Y-m-d', $time)) {
			case date('Y-m-d'):
				return date('H:i', $time);
			case date('Y-m-d', strtotime("yesterday")):
				return 'вчера';
		}
		$str = date('j', $time).' ';
		$months = array('1' => 'янв','2' => 'фев','3' => 'мар','4' => 'апр','5' => 'мая','6' => 'июн','7' => 'июл','8' => 'авг','9' => 'сен','10' => 'окт','11' => 'ноя','12' => 'дек');
		$str .= $months[date('n', $time)];
		if (date('Y', $time) != date('Y')) {
			$str .= ' '.date('\'y', $time);
		}
		return $str;
	}
	
	function niceNumber($n) {
		$n = number_format($n, 2, '.', ' ');
		$n = str_replace('.00', '', $n);
		return $n;
	}

	function redirect($url) {
        header("Location: $url");
        die();
    }

    // Отправляем в МС массив с данными
    function sendPoststring($poststring, $type='paymentin', $method='POST') {
        $fp = fsockopen("ssl://online.moysklad.ru", 443, $errno, $errstr, 30);
        if (!$fp) {
            echo "Error connect to MS: $errstr ($errno)\n";
            return false;
        }

        $out = "$method /api/remap/1.1/entity/$type";
        $out .= " HTTP/1.1\r\n";
        $out .= "Host: online.moysklad.ru\r\n";
        $out .= "Authorization: Basic " . base64_encode(MS_LOGIN . ':' . MS_PASSWORD) . "\r\n";
        $out .= "Content-Type: application/json \r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Content-length: " . strlen($poststring) . "\r\n";
        $out .= "Connection: close\r\n\r\n";
        $out .= "$poststring\r\n\r\n";

        $body = '';
        fwrite($fp, $out);
        while ($str = trim(fgets($fp, 4096))) ;
        //echo '<pre>str1: '.$str.'</pre>';
        while (!feof($fp)) {
            $str = fgets($fp, 128);
            //echo '<pre>str2: '.$str.'</pre>'."\n";
            if (strlen($str) == 6) {
                //continue;
            }
            $body .= $str;
        }
        fclose($fp);
        //pr($out);
        //pr($body);
        $body = json_decode($body, true);
        //pr($body);
        $uuid = !empty($body['id']) ? $body['id'] : null;
        $error = !empty($body['errors'][0]['error']) ? 'Мой Склад: ' . $body['errors'][0]['error'] : null;
        //pr('uuid');
        //pr($uuid);
        //pr('error');
        //pr($error);
        return [$uuid, $error];
    }

    function niceYesNo($n) {
        $n = str_replace(['Y', 'N'], [1, 0], $n);
        if ($n == 1) {
            return '<img src="/assets/images/yes.png" />';
        }
        if ($n == 0) {
            return '<img src="/assets/images/no.png" />';
        }
    }

?>