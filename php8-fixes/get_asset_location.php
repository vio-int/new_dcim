<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$html_content = "<option value=''>-- select --</option>";
$respone = array();

$location =new Location();

$locationList = $location->GetLocationList();
$locationList_res = json_decode(json_encode($locationList), true);

if(count($locationList_res) > 0)
{
    foreach($locationList_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>