<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$room_id = $_POST['room_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$rack = new Rack();
$filter = array();
$filter["sort_on"] = "r.id";
$filter["sort_by"] = "Asc";
$filter["room"] = $room_id;
$RackList = $rack->GetRackListRows($filter);
$rack_list_res = json_decode(json_encode($RackList), true);

if(count($rack_list_res) > 0)
{
    foreach($rack_list_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>