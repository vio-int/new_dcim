<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$server_id = $_POST['server_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$ConsoleConn =new ConsoleConn();

$DeviceList = $ConsoleConn->GetConsoleDeviceList($server_id);
$device_list_res = json_decode(json_encode($DeviceList), true);

if(count($device_list_res) > 0)
{
    foreach($device_list_res as $val){
        $html_content .= "<option value='".$val['Device']."'>".$val['Device_name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>