<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$html_content = "";
$respone = array();

$simulation = new Simulation();
$filter = array();
$filter["location_id"] = $_POST["site_id"];
$filter["room_id"] = $_POST["room_id"];
$filter["rack_id"] = $_POST["rack_id"];

$Capacity_arr = $simulation->GetCapacityDetails($filter);
//$Capacity_res = json_decode(json_encode($Capacity_arr), true);
//print_r($Capacity_arr);exit;

$respone["status"] = "success";
$respone["res"] = $Capacity_arr;
echo json_encode($respone);
exit;
?>