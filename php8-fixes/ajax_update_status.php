<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$asset = new Asset();
$respone = array();

$asset->UpdateStatus($_POST);
$respone["status"] = "success";

echo json_encode($respone);
exit;
?>