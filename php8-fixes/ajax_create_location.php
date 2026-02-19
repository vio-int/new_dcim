<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$location = new Location();
$respone = array();
$html_content = "<option value=''>-- select --</option>";

$location->CreateObject($_POST);

$location_list = $location->GetLocationList();
$location_list_res = json_decode(json_encode($location_list), true);

if(count($location_list_res) > 0)
{
    foreach($location_list_res as $val) {
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>