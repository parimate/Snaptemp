<?php
$serverName = "S-HQDEV-SQL17\DEVSQL,20000"; //serverName\instanceName ถ้าฐานข้อมูลอยู่ในเครื่องเราใช้ localhost 
$connectionInfo = array(
    "Database" => "snaptemp",
    "UID" => "snaptemp",
    "PWD" => "dev@snaptemp_2021"
);

$conn = sqlsrv_connect( $serverName, $connectionInfo);

if ( $conn ) {
    echo "Connected successfully.";
} else {
    echo "Connection could not be established.<br />";
    die( print_r( sqlsrv_errors(), true));
}
?>

