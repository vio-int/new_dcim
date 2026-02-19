<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$device = new Device_new();
$respone = array();

$device->UpdateDevicePosition($_POST);

$respone["status"] = "success";
echo json_encode($respone);
exit;
?>