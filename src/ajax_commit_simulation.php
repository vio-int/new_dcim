<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$simulation = new Simulation();
$respone = array();

$simulation->CreateDeviceObject($_POST);

$respone["status"] = "success";
echo json_encode($respone);
exit;
?>