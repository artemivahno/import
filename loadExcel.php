<?php
var_dump($_FILES);
            $fname = $_FILES['file']["tmp_name"];
            echo 'NAME: ' . $fname;
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fname);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            foreach ($rows AS $r) {
                echo '-----------------------------------------------<br/>';
                foreach ($r AS $c) {
                    echo '[' . $c . ']';
                }
                echo '<br/>';
            }