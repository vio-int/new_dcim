<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$simulation = new RackSimulation();
$respone = array();

$simulation->CreateRackObject($_POST);

$respone["status"] = "success";
echo json_encode($respone);
exit;
?>