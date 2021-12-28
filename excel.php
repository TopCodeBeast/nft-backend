<?php
// echo "yes";die();
$servername = "localhost";
//root
$username = "iMatch";
//
$password = "KbSQMTdFLA@2020";
$dbname="iMatch";
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->query("SET NAMES utf8");
mysqli_set_charset($conn,"utf8");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


include "vendor/autoload.php";
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;

$reader = ReaderFactory::create(Type::XLSX); // for XLSX files
// PhyandChemSET2MathsSet2_rest
$filePath = "iMatch _country_code.xlsx";//file name of excel
$reader->open($filePath);

foreach ($reader->getSheetIterator() as $sheet) {

    foreach ($sheet->getRowIterator() as $row) {
        // $sheet_index = $sheet->getRowIterator()->key() - 1;
//   print_r($row);

        $COUNTRY = $row[0];
        $COUNTRYCODE = $row[1];
        $ISO = $row[2];
        if(!empty($questions)|| !empty($qimage))
        {
           $insert_quesn = $conn->query("INSERT INTO `tb_countrycode`( `country`,`country_code`,`iso_code`) VALUES ('$COUNTRY','$COUNTRYCODE','$ISO')");
        }
        if($insert_quesn==true)
            {
                $q_id = $conn->insert_id;
        echo $qid." "."success <br>";

      
            }
        

    }
}


$reader->close();


