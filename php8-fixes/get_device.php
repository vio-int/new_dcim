<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$rack_id = $_POST['rack_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$Device =new Device_new();

$DeviceList = $Device->GetRackDeviceList($rack_id);
$device_list_res = json_decode(json_encode($DeviceList), true);

if(count($device_list_res) > 0)
{
    foreach($device_list_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>