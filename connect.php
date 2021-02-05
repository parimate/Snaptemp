<?php
$serverName = "localhost\IOT"; //serverName\instanceName ถ้าฐานข้อมูลอยู่ในเครื่องเราใช้ localhost 
$connectionInfo = array(
    "Database" => "snaptemp",
    "UID" => "u_sersor",
    "PWD" => "sensor"
);

$conn = sqlsrv_connect( $serverName, $connectionInfo);

if ( $conn ) {
    echo "Connected successfully. 5555 ";
} else {
    echo "Connection could not be established.<br />";
    die( print_r( sqlsrv_errors(), true));
}
?>

