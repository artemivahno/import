<?
/*
ALTER TABLE ms_demand_attributes
ADD COLUMN `type`  varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `_twin`,
ADD COLUMN `name`  varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `type`,
ADD COLUMN `value`  text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `name`;

ALTER TABLE `ms_demand_shipments`
ADD COLUMN `product_uuid`  varchar(36) NULL AFTER `_twin`;

ALTER TABLE `ms_return_attributes`
DROP INDEX `uniq` ,
ADD UNIQUE INDEX `uniq` (`return_uuid`, `metadataUuid`) USING BTREE ;


*/
class CKayaMoyskladSave {
	public function pr($v) {
		echo '<pre>';
		print_r($v);
		echo '</pre>';
	}
	
	// Возвращает значение ключа массива
	static function getKeyValue(&$arr, $key, $type=null, $default=null) {
		if (isset($arr[$key])) {
			$value = $arr[$key];
			switch ($type) {
				case 'bool':
					$value = ($value===true || $value==='true' || $value===1 || $value==='1') ? 1 : 0;
					break;
				case 'datetime':
					$value = date('Y-m-d H:i:s', strtotime($value));
					break;
				case 'summa':
					if (strpos($value, 'E') !== false) {
						$value = str_replace('E', 'E+', $value);
					}
					$value = (float)$value/100;
				default:
					break;
			}
			
			unset($arr[$key]);
			return $value;
		} else {
			if (isset($arr[$key])){
				unset($arr[$key]);
			}
			return $default;
		}
	}
	
	// Удаляет ключ из массива, если тот существует
	static function unsetKeyIfSet(&$arr, $key) {
		if (isset($arr[$key])) {
			unset($arr[$key]);
		}
	}
	
	// Удаляет ключ из массива, если значение пустое
	static function unsetKeyIfEmpty(&$arr, $key) {
		if (empty($arr[$key])) {
			unset($arr[$key]);
		}
	}
	
	// Удаляет ключ из массива, если значение равно 0
	static function unsetKeyIfZero(&$arr, $key) {
		if (isset($arr[$key]) && $arr[$key] == 0) {
			unset($arr[$key]);
		}
	}
	
	// Удаляет emoji из строки
	// В описании заказа умудряются всунуть
	static function removeEmoji($text) {

		$clean_text = "";

		// Match Emoticons
		$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
		$clean_text = preg_replace($regexEmoticons, '', $text);

		// Match Miscellaneous Symbols and Pictographs
		$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
		$clean_text = preg_replace($regexSymbols, '', $clean_text);

		// Match Transport And Map Symbols
		$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
		$clean_text = preg_replace($regexTransport, '', $clean_text);

		// Match Miscellaneous Symbols
		$regexMisc = '/[\x{2600}-\x{26FF}]/u';
		$clean_text = preg_replace($regexMisc, '', $clean_text);

		// Match Dingbats
		$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
		$clean_text = preg_replace($regexDingbats, '', $clean_text);

		return $clean_text;
	}
	
	// Готовим строку, чтобы вставить в базу MySQL
	static function beauty($text) {
		$text = str_replace(["'", '\\'], '', $text);
		return $text;
	}
	
	static function getFieldsValues($fields) {
		$columns = [];
		foreach($fields as $field=>$data) {
			$columns[] = $field;
		}
		$f = "`".join($columns, "`, `")."`"; // поля
		$v = ''; // значения
		$u = ''; // обновление
	
		foreach($columns as $field) {
			$value = 'null';
			if (!empty($fields[$field])) {
				$value = "'".str_replace("'", "\'", $fields[$field])."'";
			}
			if ($fields[$field] === 0) {
				$value = '0';
			}
			$v .= ", $value";
			
			if ($field == 'uuid'){continue;}
			
			$value = 'null';
			if (!empty($fields[$field])) {
				$value = "'".str_replace("'", "\'", $fields[$field])."'";
			}
			if ($fields[$field] === 0) {
				$value = '0';
			}
			$u .= ", `{$field}`=$value";
		}
		$v = substr($v, 2);
		$u = substr($u, 2);
		return [$f, $v, $u];
	}

	// Из ссылки выдирает Id (после последнего слеша)
	static function getIdFromUrl($url) {
		if(preg_match("/\/([^\/]+)$/", $url, $matches)) {
			return $matches[1];
		} else{
			return null;
		}
	}
}