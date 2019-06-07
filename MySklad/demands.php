<?
include_once ('MySklad/save.php');
include_once ('MySklad/moysklad.php');
include_once ('MySklad/products.php');

class CKayaMoyskladDemands extends CKayaMoyskladSave {
    // Сохраняем объекты
    public static function saveItems($items, $size=0) {
        $result = ['added'=>0, 'updated'=>0, 'size'=>$size];
        foreach($items as $item) {
            list($added, $updated) = self::saveItem($item);
            $result['added']	+= $added;
            $result['updated']	+= $updated;
        }
        return ['Demands'=>$result];
    }

    private static function saveItem($item){
        $added = $updated = 0;


        // Считывание
        $uuid = self::getKeyValue($item, 'id');

        $fields						    = [];
        $fields['uuid']				    = $uuid;
        $fields['groupUuid']		    = self::getIdFromUrl(self::getKeyValue($item['group']['meta'], 'href'));
        $fields['owner']		        = self::getIdFromUrl(self::getKeyValue($item['owner']['meta'], 'href'));
        $fields['name']				    = self::getKeyValue($item, 'name');
        $fields['externalcode']		    = self::getKeyValue($item, 'externalCode');
        $fields['moment']			    = self::getKeyValue($item, 'moment', 'datetime');
        $fields['updated']			    = self::getKeyValue($item, 'updated', 'datetime');
        $fields['created']			    = self::getKeyValue($item, 'created', 'datetime');
        $fields['deleted']			    = self::getKeyValue($item, 'deleted', 'datetime');
        $fields['applicable']		    = self::getKeyValue($item, 'applicable', 'bool');
        $fields['currency']			    = self::getIdFromUrl(self::getKeyValue($item['rate']['currency']['meta'], 'href'));
        $fields['rate']				    = self::getKeyValue($item['rate'], 'value');
        $fields['sum']				    = self::getKeyValue($item, 'sum', 'summa');
        $fields['payedSum']		        = self::getKeyValue($item, 'payedSum', 'summa');
        $fields['store']			    = self::getIdFromUrl(self::getKeyValue($item['store']['meta'], 'href'));
        $fields['targetAgentUuid']	    = self::getIdFromUrl(self::getKeyValue($item['agent']['meta'], 'href'));
        $fields['sourceAgentUuid']	    = self::getIdFromUrl(self::getKeyValue($item['organization']['meta'], 'href'));
        $fields['accountId']		    = self::getKeyValue($item, 'accountId');
        $fields['description']		    = self::getKeyValue($item, 'description');
        $fields['project']			    = self::getIdFromUrl(self::getKeyValue($item['project']['meta'], 'href'));

        $fields['overheadSum']	        = self::getKeyValue($item['overhead'], 'sum', 'summa');
        $fields['overheadDistribution']	= self::getKeyValue($item['overhead'], 'distribution');

        $fields['vatEnabled']		    = self::getKeyValue($item, 'vatEnabled', 'bool');
        $fields['vatIncluded']		    = self::getKeyValue($item, 'vatIncluded', 'bool');
        $fields['vatSum']		        = self::getKeyValue($item, 'vatSum', 'summa');
        $fields['stateContractId']		= self::getKeyValue($item, 'stateContractId');

        //$fields['targetAccountUuid']		= self::getKeyValue($item, 'targetAccountUuid');
        //$fields['sourceAccountUuid']		= self::getKeyValue($item, 'sourceAccountUuid');
        //$fields['currencyUuid']			= self::getKeyValue($item, 'currencyUuid');
        //$fields['customerOrderUuid']		= self::getKeyValue($item, 'customerOrderUuid');

        //$fields['contractUuid']			= self::getKeyValue($item, 'contractUuid');
        //
        //$fields['accountUuid']			= self::getKeyValue($item, 'accountUuid');
        //$fields['payerVat']				= self::getKeyValue($item, 'payerVat', 'bool');
        //$fields['vatIncluded']			= self::getKeyValue($item, 'vatIncluded', 'bool');
        //$fields['shared']					= self::getKeyValue($item, 'shared', 'bool');
        //$fields['updatedBy']				= self::getKeyValue($item, 'updatedBy');
        //$fields['createdBy']				= self::getKeyValue($item, 'createdBy');
        //$fields['rate']					= self::getKeyValue($item, 'rate');
        //$fields['sumInCurrency']			= self::getKeyValue($item['sum'], 'sumInCurrency', 'summa');

        // Обработка считанных данных
        $fields['description']		= self::removeEmoji($fields['description']);
        $fields['description']		= self::beauty($fields['description']);

        // Очистка входного массива
        self::unsetKeyIfSet($item, 'meta');
        self::unsetKeyIfSet($item, 'owner');
        self::unsetKeyIfSet($item, 'version');
        self::unsetKeyIfSet($item, 'rate');
        self::unsetKeyIfSet($item, 'store');
        self::unsetKeyIfSet($item, 'documents');
        //self::unsetKeyIfSet($item, 'payedSum');
        self::unsetKeyIfSet($item, 'shared');

        self::unsetKeyIfSet($item, 'group');
        self::unsetKeyIfSet($item, 'agent');
        self::unsetKeyIfSet($item, 'organization');

        // Формирование и выполнение MySQL запроса
        list($f, $v, $u) = self::getFieldsValues($fields);
        $sql = "SELECT uuid FROM ms_demands WHERE uuid='$uuid';";
        if (dbQueryArray($sql)) {
            $updated++;
        } else{
            $added++;
        }
        $sql = "INSERT INTO ms_demands ($f, _created) VALUES($v, NOW()) ON DUPLICATE KEY UPDATE $u, _updated=NOW(), _twin = IFNULL(_twin, 0) + 1;";
        dbQuery($sql);



        // Сохраняем атрибуты attribute
        if (!empty($item['attributes'])) {
        	self::saveDemandAttributes($item['attributes'], $uuid);
        }
        self::unsetKeyIfSet($item, 'attributes');

        // Сохраняем входящие платежи paymentsUuid
        //if (!empty($item['payments'])) {
        //	self::saveDemandPayments($item['payments'], $uuid);
        //}
        //self::unsetKeyIfSet($item, 'payments');

        // Сохраняем positions
        if (!empty($item['positions']['rows'])) {
            self::saveDemandShipments($item['positions']['rows'], $uuid);
        }
        self::unsetKeyIfSet($item, 'positions');

        // Сохраняем salesReturnsUuid
        //if (!empty($item['returns'])) {
        //	self::saveDemandReturns($item['returns'], $uuid);
        //}
        //self::unsetKeyIfSet($item, 'returns');

        // Обновляем связанные заказы
        //$orderUuid = self::getIdFromUrl(self::getKeyValue($item['customerOrder']['meta'], 'href'));
        //self::updateOrderDemand($orderUuid, $uuid);
        //self::unsetKeyIfSet($item, 'customerOrder');

        // Проверяем входной массив - он должен быть пуст
        if (!empty($item)) {
            ///pr($item); die('Demand add. Array not empty! uuid: '.$uuid);
        }

        return [$added, $updated];
    }

    // Сохраняем параметры отгрузки
    static function saveDemandAttributes($items, $demandUuid) {
        // Помечаем на удаление
        $sql = "UPDATE ms_demand_attributes SET _del = 1 WHERE demand_uuid = '$demandUuid';";
        dbQuery($sql);

        foreach ($items as $item) {
            // Считывание
            $uuid = self::getKeyValue($item, 'id');
            $fields								= [];
            //$fields['uuid']					= $uuid;
            $fields['metadataUuid']				= $uuid;
            $fields['demand_uuid']				= $demandUuid;
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
            $fields['type']						= self::getKeyValue($item, 'type');
            $fields['name']						= self::getKeyValue($item, 'name');

            //$fields['operationUuid']			= self::getKeyValue($item, 'operationUuid');
            //$fields['updated']					= self::getKeyValue($item, 'updated', 'datetime');
            //$fields['updatedBy']				= self::getKeyValue($item, 'updatedBy');
            //$fields['accountUuid']				= self::getKeyValue($item, 'accountUuid');
            //$fields['accountId']				= self::getKeyValue($item, 'accountId');
            //$fields['groupUuid']				= self::getKeyValue($item, 'groupUuid');
            //$fields['ownerUid']					= self::getKeyValue($item, 'ownerUid');
            //$fields['shared']					= self::getKeyValue($item, 'shared', 'bool');

            // Очистка входного массива
            self::unsetKeyIfSet($item, 'meta');

            // Формирование и выполнение MySQL запроса
            list($f, $v, $u) = self::getFieldsValues($fields);
            $sql = "INSERT INTO ms_demand_attributes ($f, _created) VALUES($v, NOW()) ON DUPLICATE KEY UPDATE $u, _updated=NOW(), _twin = IFNULL(_twin, 0) + 1, _del = null;";
            dbQuery($sql);

            // Проверяем входной массив - он должен быть пуст
            if (!empty($item)) {
                //pr($item); die('Demand Attribute add. Array not empty! uuid: '.$uuid);
            }
        }

        // Удаляем помеченные на удаление
        $sql = "DELETE FROM ms_demand_attributes WHERE demand_uuid = '$demandUuid' AND _del = 1;";
        dbQuery($sql);

        return true;
    }

    // Сохраняем платежи в отгрузке
    function saveDemandPayments($items, $demandUuid) {
        // Помечаем на удаление
        $sql = "UPDATE ms_demand_payments SET _del = 1 WHERE demand_uuid = '$demandUuid';";
        dbQuery($sql);

        foreach($items as $item) {
            $paymentUuid = self::getIdFromUrl($item['meta']['href']);
            self::saveDemandPayment($demandUuid, $paymentUuid);
        }

        // Удаляем помеченные на удаление
        $sql = "DELETE FROM ms_demand_payments WHERE demand_uuid = '$demandUuid' AND _del = 1;";
        dbQuery($sql);

        return true;
    }

    // Сохраняем платеж из отгрузки
    static function saveDemandPayment($demandUuid, $inPaymentUuid) {
        $sql = "INSERT INTO ms_demand_payments (demand_uuid, in_payment_uuid, _created) VALUES('$demandUuid', '$inPaymentUuid', NOW()) ON DUPLICATE KEY UPDATE _updated=NOW(), _twin = IFNULL(_twin, 0) + 1, _del = null;";
        dbQuery($sql);
        return true;
    }

    // Сохраняем товары в отгрузке
    static function saveDemandShipments($items, $demandUuid) {
        // Помечаем на удаление
        $sql = "UPDATE ms_demand_shipments SET _del = 1 WHERE demand_uuid = '$demandUuid';";
        dbQuery($sql);

        foreach ($items as $item) {
            // Считывание
            $uuid = self::getKeyValue($item, 'id');
            $fields								= [];
            $fields['uuid']						= $uuid;
            $fields['demand_uuid']				= $demandUuid;

            $fields['discount']					= self::getKeyValue($item, 'discount');
            $fields['quantity']					= self::getKeyValue($item, 'quantity');
            $fields['product_uuid']				= self::getIdFromUrl(self::getKeyValue($item['assortment']['meta'], 'href'));
            $fields['vat']						= self::getKeyValue($item, 'vat', null, 0);

            $fields['accountId']				= self::getKeyValue($item, 'accountId');

            $fields['basePriceSum']				= self::getKeyValue($item, 'price', 'summa');
            $fields['priceSum']					= $fields['basePriceSum']-$fields['basePriceSum']/100*$fields['discount'];

            // Очистка входного массива
            self::unsetKeyIfSet($item, 'meta');

            // Формирование и выполнение MySQL запроса
            list($f, $v, $u) = self::getFieldsValues($fields);
            $sql = "INSERT INTO ms_demand_shipments ($f, _created) VALUES($v, NOW()) ON DUPLICATE KEY UPDATE $u, _updated=NOW(), _twin = IFNULL(_twin, 0) + 1, _del = null;";
            dbQuery($sql);

            // Проверяем входной массив - он должен быть пуст
            if (!empty($item)) {
                //pr($item); die('Demand Shipment add. Array not empty! uuid: '.$uuid);
            }
        }

        // Удаляем помеченные на удаление
        $sql = "DELETE FROM ms_demand_shipments WHERE demand_uuid = '$demandUuid' AND _del = 1;";
        dbQuery($sql);

        return true;
    }

    // Сохраняем возвраты, связанные с отгрузкой
    function saveDemandReturns($items, $demandUuid) {
        // Помечаем на удаление
        $sql = "UPDATE ms_demand_returns SET _del = 1 WHERE demand_uuid = '$demandUuid';";
        dbQuery($sql);

        foreach($items as $item) {
            $returnUuid = self::getIdFromUrl($item['meta']['href']);
            self::saveDemandReturn($demandUuid, $returnUuid);
        }

        // Удаляем помеченные на удаление
        $sql = "DELETE FROM ms_demand_returns WHERE demand_uuid = '$demandUuid' AND _del = 1;";
        dbQuery($sql);

        return true;
    }

    // Сохраняем возврат, связанный с отгрузкой
    function saveDemandReturn($demandUuid, $returnUuid) {
        $sql = "INSERT INTO ms_demand_returns (demand_uuid, return_uuid, _created) VALUES('$demandUuid', '$returnUuid', NOW()) ON DUPLICATE KEY UPDATE _updated=NOW(), _twin = IFNULL(_twin, 0) + 1, _del = null;";
        dbQuery($sql);
        return true;
    }

    // Обновляем заказ, прикрепленный к отгрузке
    private function updateOrderDemand($orderUuid, $demandUuid) {
        $sql = "INSERT INTO ms_order_demands (order_uuid, demand_uuid, _created) VALUES('$orderUuid', '$demandUuid', NOW()) ON DUPLICATE KEY UPDATE _updated=NOW(), _twin = IFNULL(_twin, 0) + 1;";
        dbQuery($sql);
        return true;
    }

    public static function overheads($hours = 2, $debug = false) {
        $hours = !empty($_REQUEST['hours']) ? $_REQUEST['hours'] : $hours;
        $date = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        $sql = "
            SELECT
                demands.`uuid`,
                demands.`name`,
                demands.moment,
                demands.updated,
                demands.sum,
                demands.vatSum,
                demand_attributes.`value`      AS XXX,
                IFNULL(demands.overheadSum, 0) AS overheadSum,
                demands.vatEnabled,
                demands.vatIncluded,
                demands.description,
                ''                             AS actions
            FROM
                ms_demands AS demands
                
                # Параметр XXX
                LEFT JOIN ms_demand_attributes AS demand_attributes ON demand_attributes.demand_uuid = demands.uuid 
                AND demand_attributes.metadataUuid = '02de153a-0a5e-11e8-6b01-4b1d00273cf7' 
            WHERE
                1
                AND demands.applicable = 1
                AND demands.deleted IS NULL
                AND demands.updated >= '$date'
            ORDER BY
                demands.moment DESC
            ;
        ";
        $data = dbQueryArray($sql);

        if ($debug) {
            echo '<form action="">';
            echo '<p>Раз в час проверяем отгрузки, измененные за последние <input name="hours" value="'.$hours.'" style="display: inline; width: 30px"> '.getNormalCase($hours, 'часов', 'час','часа').', чтобы они соответствовали следующим критериям:<br>';

            echo '1. Если стоит галочка "XXX", то галочка "НДС" не должна стоять и "Накладные расходы" пустые<br>';
            echo '2. Если не стоит галочка "XXX", то:<br>';
            echo '&nbsp; а) галочка "НДС" должна стоять<br>';
            echo '&nbsp; б) галочка "Цена включает НДС" не должна стоять<br>';
            echo '&nbsp; в) поле "Накладные расходы" равно размеру НДС отгрузки<br>';
            echo '</p>';
            echo '</form>';
            echo '<p>Показаны '.count($data).' '.getNormalCase(count($data), 'отгрузок', 'отгрузка', 'отгрузки'). ', которые изменены за последние '.$hours.' '.getNormalCase($hours, 'часов', 'час','часа').' назад (c '.niceDateTime($date).').</p>';
        }

        foreach($data as $k=>$item) {
            $xxx                = $item['XXX'];
            $demandUuid         = $item['uuid'];
            $demandName         = $item['name'];
            $demandMoment       = date('Y-m-d H:i:s', strtotime($item['moment']));
            $demandDescription  = $item['description'];
            $vatSum             = (float)$item['vatSum'];
            $vatEnabled         = $item['vatEnabled'];
            $vatIncluded        = $item['vatIncluded'];
            $overheadSum        = (float)$item['overheadSum'];

            if ($xxx == 0) {
                // Галочка "XXX" не стоит

                if (strpos($demandDescription, 'Связанный исходящий платеж: ') === false) {
                    // Платежа исходящего нет. Создаем его
                    list($uuid, $error) = self::addPaymentOut($demandName, $demandUuid, $demandMoment, $vatSum);
                    $data[$k]['paymentOutUuid'] = $uuid;
                    $data[$k]['error'] = $error;

                    if (!empty($uuid)) {
                        $data[$k]['actions'] .= 'Создали платеж на основании отгрузки<br>';
                        // Платеж создали. Обновляем отгрузку
                        $description = $demandDescription."\r\nСвязанный исходящий платеж: Автоплатеж НДС $demandName " .date('d.m.Y H:i', strtotime($demandMoment));
                        if (self::updateDemandDescription($demandUuid, $description, $uuid)) {
                            $data[$k]['description'] = $description;
                            $data[$k]['actions']    .= 'Обновили описание отгрузки<br>';
                        }
                    }
                }

                if ($vatEnabled == 0) {
                    // Ставим галочку "НДС"
                    $vatEnabled = 1;
                    $vatIncluded = 0;
                    if (self::updateDemandVat($demandUuid, $vatEnabled, $vatIncluded)) {
                        $data[$k]['vatEnabled']     = $vatEnabled;
                        $data[$k]['vatIncluded']    = $vatIncluded;
                        $data[$k]['actions']       .= 'Поставили галочку "НДС"<br>';
                    }
                }

                if ($vatEnabled == 1 && $vatIncluded == 1) {
                    // Убираем галочку "Цена включает НДС"
                    $vatEnabled = 1;
                    $vatIncluded = 0;
                    if (self::updateDemandVat($demandUuid, $vatEnabled, $vatIncluded)) {
                        $data[$k]['vatEnabled']     = $vatEnabled;
                        $data[$k]['vatIncluded']    = $vatIncluded;
                        $data[$k]['actions']       .= 'Убрали галочку "Цена включает НДС"<br>';
                    }
                }

                if (empty($overheadSum)) {
                    // Накладные расходы пустые. Заполняем их
                    $overheadSum = $vatSum;
                    if (self::updateOverheadSum($demandUuid, $overheadSum)) {
                        $data[$k]['overheadSum']    = $overheadSum;
                        $data[$k]['actions']       .= 'Заполнили пустые накладные расходы суммой равной НДС<br>';
                    }
                }

            } else {
                // Галочка "XXX" стоит

                if ($vatEnabled == 1 || $vatIncluded == 1) {
                    // Убираем галочку "НДС"
                    $vatEnabled = 0;
                    $vatIncluded = 0;
                    if (self::updateDemandVat($demandUuid, $vatEnabled, $vatIncluded)) {
                        $data[$k]['vatEnabled']     = $vatEnabled;
                        $data[$k]['vatIncluded']    = $vatIncluded;
                        $data[$k]['actions']       .= 'Убрали галочку "НДС"<br>';
                    }
                }

                if (!empty($overheadSum)) {
                    // Накладные расходы заполнены. Убираем их
                    $overheadSum = 0;
                    if (self::updateOverheadSum($demandUuid, $overheadSum)) {
                        $data[$k]['overheadSum']    = $overheadSum;
                        $data[$k]['actions']       .= 'Убрали накладные расходы<br>';
                    }
                }

            }
        }

        if ($debug) {
            echo '<table class="table">';
            echo '<tr>';
            echo '<th>Название</th>';
            echo '<th>Дата</th>';
            echo '<th>Изменена</th>';
            echo '<th>Сумма</th>';
            echo '<th>Ваты</th>';
            echo '<th>XXX</th>';
            echo '<th>Накладные расходы</th>';
            echo '<th>НДС</th>';
            echo '<th>Цена включает НДС</th>';
            echo '<th>Описание</th>';
            echo '<th>Действия</th>';
            echo '</tr>';
            foreach($data as $item) {
                echo '<tr>';
                echo '<td>'.$item['name'].'</td>';
                echo '<td>'.$item['moment'].'</td>';
                echo '<td>'.niceDateTime($item['updated']).'</td>';
                echo '<td><nobr>'.niceNumber($item['sum']).'</nobr></td>';
                echo '<td>'.niceNumber($item['vatSum']).'</td>';
                echo '<td>'.niceYesNo($item['XXX']).'</td>';
                echo '<td>'.niceNumber($item['overheadSum']).'</td>';
                echo '<td>'.niceYesNo($item['vatEnabled']).'</td>';
                echo '<td>'.niceYesNo($item['vatIncluded']).'</td>';
                echo '<td>'.nl2br($item['description']).'</td>';
                echo '<td>'.$item['actions'].'</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        //printArrayAsTable($data);
        return 'CKayaMoyskladDemands::overheads();';
    }

    // Добавляется исходящий платеж для всех отгрузок, где есть XXX
    private function addPaymentOut($demandName='42', $demandUuid='8891a274-1d52-11e8-9107-504800084734', $demandMoment='2018-03-01 16:17:59', $sum=99) {
        $name           = "Автоплатеж НДС $demandName ".date('d.m.Y H:i', strtotime($demandMoment));
        $description    = "Связанная отгрузка: $demandName от ".date('d.m.Y H:i:s');
        $sum = $sum * 100;


        $formdata = [
            'name'  => $name,
            'description'=> $description,
            'owner'=>[
                'meta'=>[
                    'href'			=> 'https://online.moysklad.ru/api/remap/1.1/entity/employee/bec7e9a6-0259-11e8-6b01-4b1d00120c58',
                    'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/employee/metadata',
                    'type'			=> 'employee',
                    'mediaType'		=> 'application/json'
                ]
            ],
            'group'=>[
                'meta'=>[
                    'href'			=> 'https://online.moysklad.ru/api/remap/1.1/entity/group/beb26e1c-0259-11e8-7a69-971100000df1',
                    'metadataHref'	=> 'https://online.moysklad.ru/api/remap/1.1/entity/group/metadata',
                    'type'			=> 'group',
                    'mediaType'		=> 'application/json'
                ]
            ],
            'moment' => $demandMoment,
            'applicable:' => 'true',
            'rate'=>[
                'currency'=>[
                    'meta'=>[
                        'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/currency/bed2d252-0259-11e8-6b01-4b1d00120c86',
                        'metadataHref'  => 'https://online.moysklad.ru/api/remap/1.1/entity/currency/metadata',
                        'type'          => 'currency',
                        'mediaType'     => 'application/json'
                    ]
                ]
            ],
            'sum' => $sum,
            'agent' => [
                'meta' => [
                    'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/organization/bed1f67d-0259-11e8-6b01-4b1d00120c7f',
                    'metadataHref'  => 'https://online.moysklad.ru/api/remap/1.1/entity/organization/metadata',
                    'type'          => 'organization',
                    'mediaType'     => 'application/json',
                ]
            ],
            'organization' => [
                'meta' => [
                    'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/organization/bed1f67d-0259-11e8-6b01-4b1d00120c7f',
                    'metadataHref'  => 'ttps://online.moysklad.ru/api/remap/1.1/entity/organization/metadata',
                    'type'          => 'organization',
                    'mediaType'     => 'application/json',
                ]
            ],
            'agentAccount' => [
                'meta' => [
                    'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/organization/bed1f67d-0259-11e8-6b01-4b1d00120c7f/accounts/7924bfdd-1cd2-11e8-9ff4-34e800001cce',
                    'type'          => 'account',
                    'mediaType'     => 'application/json',
                ]
            ],
            'organizationAccount' => [
                'meta' => [
                    'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/organization/bed1f67d-0259-11e8-6b01-4b1d00120c7f/accounts/7924bfdd-1cd2-11e8-9ff4-34e800001cce',
                    'type'          => 'account',
                    'mediaType'     => 'application/json',
                ]
            ],
            'paymentPurpose' => $demandUuid,
            'expenseItem' => [
                'meta' => [
                    'href'          => 'https://online.moysklad.ru/api/remap/1.1/entity/expenseitem/9c69689c-1cd5-11e8-9ff4-31500000228b',
                    'metadataHref'  => 'https://online.moysklad.ru/api/remap/1.1/entity/expenseitem/metadata',
                    'type'          => 'expenseitem',
                    'mediaType'     => 'application/json'
                ]
            ],
        ];

        $body = CKayaMoysklad::putJSONarray('paymentout', $formdata, 'POST');

        $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
        $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';

        return [$uuid, $error];
    }

    private static function updateDemandDescription($uuid, $description, $paymentOutUuid) {
        $formdata = [
            'description'       => $description,
            'stateContractId'   => $paymentOutUuid,
        ];
        $body = CKayaMoysklad::putJSON($uuid,'demand', $formdata);
        $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
        $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';

        if (empty($error)) {
            $sql = "UPDATE ms_demands SET description='$description', stateContractId='$paymentOutUuid', _updated = NOW() WHERE uuid='$uuid'";
            dbQuery($sql);
            return true;
        }
        return false;
    }

    private static function updateDemandVat($demandUuid, $vatEnabled=0, $vatIncluded=0) {
        $formdata = [
            'vatEnabled'    => $vatEnabled  == 1 ? true : false,
            'vatIncluded'   => $vatIncluded == 1 ? true : false,
        ];
        $body = CKayaMoysklad::putJSON($demandUuid,'demand', $formdata);
        $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
        $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';
        if (empty($error)) {
            $sql = "UPDATE ms_demands SET vatEnabled=$vatEnabled, vatIncluded=$vatIncluded, _updated = NOW() WHERE uuid='$uuid'";
            dbQuery($sql);
            return true;
        }
        return false;
    }

    private static function updateOverheadSum($demandUuid, $overheadSum=0) {
        $formdata = [
            'overhead' => [
                'sum'           => $overheadSum * 100,
                'distribution'  => 'price',
            ],
        ];
        $body = CKayaMoysklad::putJSON($demandUuid,'demand', $formdata);
        $uuid	= !empty($body['id'])                   ? $body['id']                               : null;
        $error	= !empty($body['errors'][0]['error'])   ? 'Мой Склад: '.$body['errors'][0]['error'] : '';

        if (empty($error)) {
            $sql = "UPDATE ms_demands SET overheadSum=$overheadSum, _updated = NOW() WHERE uuid='$uuid'";
            dbQuery($sql);
            return true;
        }
        return false;
    }
}