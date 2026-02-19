<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$asset = new Asset();
$response = array();

$response = $asset->UpdateAssetObject();

//echo json_encode($message);
echo json_encode($response);
die;
?>