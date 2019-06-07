<?
include_once('save.php');

class CKayaMoyskladProducts extends CKayaMoyskladSave {
    // Сохраняем объекты
    public static function saveItems($items, $size=0, $type='Products') {
        $result = ['added'=>0, 'updated'=>0, 'size'=>$size];
        foreach($items as $item) {
            list($added, $updated) = self::saveItem($item);
            $result['added']	+= $added;
            $result['updated']	+= $updated;
        }
        return [$type=>$result];
    }

    private static function saveItem($item){
        $added = $updated = 0;

        // Считывание
        $uuid = self::getKeyValue($item, 'id');

        $fields								= [];
        $fields['uuid']						= $uuid;

       // $fields['groupUuid']			= self::getIdFromUrl(self::getKeyValue($item['group']['meta'], 'href'));
        $fields['name']					= self::getKeyValue($item, 'name');
        $fields['code']					= self::getKeyValue($item, 'code');
        //$fields['productFolder']		= self::getIdFromUrl(self::getKeyValue($item['productFolder']['meta'], 'href'));
        $fields['updated']		        = self::getKeyValue($item, 'updated', 'datetime');
        //$fields['type']		            = self::getKeyValue($item['meta'], 'type');
        $fields['description']	        = self::getKeyValue($item, 'description');
        $fields['pathName']	            = self::getKeyValue($item, 'pathName');

        //$fields['imageHref']		    = self::getKeyValue($item['image']['meta'], 'href');
        //$fields['imageMiniature']		= self::getKeyValue($item['image']['miniature'], 'href');
        //$fields['imageTiny']		    = self::getKeyValue($item['image']['tiny'], 'href');

        // Обработка считанных данных
        $fields['description']	= self::removeEmoji($fields['description']);
        $fields['description']	= self::beauty($fields['description']);

        $fields['name']                 = str_replace('\\', '', $fields['name']);
        $fields['archived']		        = self::getKeyValue($item, 'archived', 'bool');
        $fields['barcodes']	            = ''.self::getKeyValue($item, 'barcodes');
        //$fields['barcodes']	            = !empty($fields['barcodes']) ? join($fields['barcodes'], ', ') : $fields['barcodes'];

        // Сохраняем картинку товара
//        if (!empty($fields['imageMiniature'])) {
//            $url = $fields['imageMiniature'];
//            $filename = $uuid.'.png';
//            CKayaMoysklad::downloadImage($url, $filename);
//        }

        // Очистка входного массива
        self::unsetKeyIfSet($item, 'meta');
        self::unsetKeyIfSet($item, 'group');

        // Формирование и выполнение MySQL запроса
        list($f, $v, $u) = self::getFieldsValues($fields);

        $sql = "SELECT uuid FROM ms_products WHERE uuid='$uuid';";
        if (dbQueryArray($sql)) {
            $updated++;
        } else{
            $added++;
        }
        $sql = "INSERT INTO ms_products ($f, _created) VALUES($v, NOW()) ON DUPLICATE KEY UPDATE $u, _updated=NOW(), _twin = IFNULL(_twin, 0) + 1;";
        dbQuery($sql);

        // Сохраняем атрибуты attribute
        if (!empty($item['attributes'])) {
        	self::saveProductAttributes($item['attributes'], $uuid);
        }
        self::unsetKeyIfSet($item, 'attributes');

        // Сохраняем цены
        if (!empty($item['salePrices'])) {
            self::saveProductPrices($item['salePrices'], $uuid);
        }
        //self::unsetKeyIfSet($item, 'salePrices');


        // Проверяем входной массив - он должен быть пуст
        if (!empty($item) /*&& $uuid == '2ca21242-0b13-11e8-7a69-93a70021ad5a'*/) {
            //pr($item); die('Products add. Array not empty! uuid: '.$uuid);
        }

        return [$added, $updated];
    }

    // Сохраняем параметры товара
    static private function saveProductAttributes($items, $productUuid) {
        // Помечаем на удаление
        $sql = "UPDATE ms_product_attributes SET _del = 1 WHERE product_uuid = '$productUuid';";
        dbQuery($sql);

        foreach ($items as $item) {
            // Считывание
            $uuid = self::getKeyValue($item, 'id');

            $fields								= [];
            //$fields['uuid']					= $uuid;
            $fields['metadataUuid']				= $uuid;
            $fields['product_uuid']				= $productUuid;
            if ($item['type'] == 'project') {
                $fields['projectValueUuid']		= self::getIdFromUrl(self::getKeyValue($item['value']['meta'], 'href'));
                $fields['value']				= self::getKeyValue($item['value'], 'name');
            }
            if ($item['type'] == 'customentity') {
                $fields['entityValueUuid']		= self::getIdFromUrl(self::getKeyValue($item['value']['meta'], 'href'));
                $fields['value']				= self::getKeyValue($item['value'], 'name');
            }
            if ($item['type'] == 'boolean') {
                $fields['booleanValue']			= self::getKeyValue($item, 'value', 'bool');
                $fields['value']				= $fields['booleanValue'];
            }
            if ($item['type'] == 'string') {
                $fields['valueString']			= self::beauty(self::getKeyValue($item, 'value'));
                $fields['value']				= $fields['valueString'];
            }
            if ($item['type'] == 'double') {
                $fields['doubleValue']			= self::getKeyValue($item, 'value');
                $fields['value']				= $fields['doubleValue'];
            }
            if (empty($fields['value'])) {
                $fields['value'] = self::getKeyValue($item, 'value');
            }
            $fields['type']						= self::getKeyValue($item, 'type');
            $fields['name']						= self::getKeyValue($item, 'name');

            // Очистка входного массива
            self::unsetKeyIfSet($item, 'meta');
            self::unsetKeyIfSet($item['value']['meta'], 'metadataHref');
            self::unsetKeyIfSet($item['value']['meta'], 'type');
            self::unsetKeyIfSet($item['value']['meta'], 'mediaType');
            self::unsetKeyIfSet($item['value']['meta'], 'uuidHref');
            self::unsetKeyIfEmpty($item['value'], 'meta');
            self::unsetKeyIfEmpty($item, 'value');

            // Формирование и выполнение MySQL запроса
            list($f, $v, $u) = self::getFieldsValues($fields);
            $sql = "INSERT INTO ms_product_attributes ($f, _created) VALUES($v, NOW()) ON DUPLICATE KEY UPDATE $u, _updated=NOW(), _twin = IFNULL(_twin, 0) + 1, _del = null;";
            dbQuery($sql);

            // Проверяем входной массив - он должен быть пуст
            if (!empty($item)) {
                //pr($item); die('PRODUCT ATTRIBUTE add. Array not empty! uuid: '.$uuid);
            }
        }

        // Удаляем помеченные на удаление
        $sql = "DELETE FROM ms_product_attributes WHERE product_uuid = '$productUuid' AND _del = 1;";
        dbQuery($sql);

        return true;
    }

    // Сохраняем цены товара
    static private function saveProductPrices($items, $productUuid) {
        // Помечаем на удаление
        $sql = "UPDATE ms_product_prices SET _del = 1 WHERE productUuid = '$productUuid';";
        dbQuery($sql);

        foreach ($items as $item) {
            // Считывание
            $uuid = self::getKeyValue($item, 'id');

            $fields								= [];
            //$fields['uuid']					= $uuid;
            $fields['productUuid']				= $productUuid;
            $fields['value']			        = self::getKeyValue($item, 'value', 'summa');
            $fields['type']			            = self::getKeyValue($item, 'priceType');

            // Очистка входного массива
            self::unsetKeyIfSet($item, 'currency');

            // Формирование и выполнение MySQL запроса
            list($f, $v, $u) = self::getFieldsValues($fields);
            $sql = "INSERT INTO ms_product_prices ($f, _created) VALUES($v, NOW()) ON DUPLICATE KEY UPDATE $u, _updated=NOW(), _twin = IFNULL(_twin, 0) + 1, _del = null;";
            dbQuery($sql);

            // Проверяем входной массив - он должен быть пуст
            if (!empty($item)) {
                pr($item); die('PRODUCT PRICE add. Array not empty! uuid: '.$uuid);
            }
        }

        // Удаляем помеченные на удаление
        $sql = "DELETE FROM ms_product_prices WHERE productUuid = '$productUuid' AND _del = 1;";
        dbQuery($sql);

        return true;
    }
}