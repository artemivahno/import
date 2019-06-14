<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Document</title>


    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
          integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"
            integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k"
            crossorigin="anonymous"></script>
</head>
<body>
<form method='post' enctype="multipart/form-data" action="loadExcel.php">
    <div class="form-group">

        <label for="FormControlFile">Импорт Прайса</label>
        <br>
        <label for="usdRate">Курс пересчета USD</label>
        <input type="number" name="usdRate" min="1" max="5" value="2.08" step="0.01" required>
        <br>
        <label for="manufacturer">Введите производителя</label>
        <input type="text" size="50" name="manufacturer"  placeholder=" Если пустой - =NO NAME">
        <br>
        <br>
	    <input required name="uploadfile" type=file class="form-control-file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,.csv">
        <br>
        <button type="submit" class="btn btn-primary pull-right"> Загрузить</button>
        <button type="reset" class="btn btn-danger">Отмена</button>
    </div>
</form>

<form method='post' enctype="multipart/form-data" action="processMSklad.php">
    <div class="form-group">
        <br>
        <button type="submit" class="btn btn-primary pull-right"> Загрузить все товары из М.Склад в Базу</button>
    </div>
</form>

<hr>
<div class="container"><h1>Сводные таблицы</h1>
    <span>
    <a href="/">Выбрать другой файл</a>

    </span>

    <span>
    <div class="alert alert-success" style="display: none;">Товар загружен в базу данных</div>
    </span>

</div>
<br>


<div id="exTab2" class="container">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
               aria-selected="true">Из Excel</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
               aria-controls="profile"
               aria-selected="false">Из Базы Данных</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="productNew-tab" data-toggle="tab" href="#productNew" role="tab"
               aria-controls="productNew"
               aria-selected="false">Новые товары</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="productNew-tab" data-toggle="tab" href="#diffrentPrice" role="tab"
               aria-controls="diffrentPrice"
               aria-selected="false">Поменялась цена товара</a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            <h2>Таблица данных из Excel файла <?php echo $inputFileName; ?></h2>
            <?php
            $table = getExcelData($_FILES['uploadfile']["tmp_name"]);
            $result = [];
            foreach ($table as $sheet => $data) {
                foreach ($data as $key => $item) {
                    if ($key == 'CodeAliasValue') {
                        continue;
                    }
                    array_unshift($item, $item['sheet'] = $sheet);
                    $result[] = $item;
                }
            }
            printArrayAsTable($result);
            ?>
        </div>

        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <h2>Содержимое Базы Данных</h2>
            <?php printArrayAsTable($dbArray); ?>
        </div>

        <div class="tab-pane fade" id="productNew" role="tabpanel" aria-labelledby="productNew-tab">
            <h2>Товары, которых нет в Базе Данных</h2>
            <?php
            $table = printTableDifference($diffBarcodes, $excelArray);
            serialize($table);

            $arr = [];
            foreach ($table as $row) {
                foreach ($row as $v) {
                    $displayArr[] = $v;
                    $arr = $displayArr;
                }
            }
            ?>
            <input type='hidden' name='tableDifferences' value='<?php serialize($table); ?>' />

            <table cellpadding="5" cellspacing="0" border="1">

                <thead>
                <tr>
                    <th>
                        <button class="all">Добавить все товары в Мой Склад</button>
                    </th>
                    <th><?php echo implode('</th><th>', array_keys(current($arr))); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($arr as $row):
                array_map('htmlentities', $row);
                ?>
                <?php if ($row['CategoryAliasValue'] == "CategoryAliasValue"): //если заголовок
                continue; ?>
                <thead>
                <td></td>
                <td><?php echo implode('</td><td>', $row); ?></td>
                </thead>
                <? else: ?>
                    <tr>
                        <td>
                            <button class="product"
                                    data-key1="<?php echo $row['ProductAliasValue'].' '.$row['ColorAliasValue'] ?>"
                                    data-key2="<?php echo $row['CodeAliasValue'] ?>"
                                    data-key3="<?php echo $row['DescriptionAliasValue'] ?>"
                                    data-key4="<?php echo $row['Product Weight (g)Alias'] ?>"
                                    data-key5="<?php echo $row['Color box Size (cm)Alias'] ?>"
                                    data-key6="<?php echo $row['Price(USD)Alias'] ?>"
                            >Добавить в Мой Склад
                            </button>
                        </td>
                        <td><?php echo implode('</td><td>', $row); ?></td>
                    </tr>
                <? endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="diffrentPrice" role="tabpanel" aria-labelledby="diffrentPrice">
            <h2>Товары, цена которых поменялась. /Курс пересчета: <?php echo (double)$_POST['usdRate'] ?>/</h2>
            <?php
            //pr($productDifference);
            if (!empty($productDifference)){
                printArrayAsTable($productDifference);
            }else{
                echo "Нет товаров в которых поменялась цена";
            }
            ?>
        </div>
    </div>
</div>
<hr>
<!-- jQuery first, then Popper.js, then Bootstrap JS -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>


</body>
</html>

<?php
