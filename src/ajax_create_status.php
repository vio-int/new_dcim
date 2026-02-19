<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$asset = new Asset();
$respone = array();
$html_content = "<option value=''>-- select --</option>";

$asset->CreateStatus($_POST);

$status_list = $asset->GetStatusList();
$status_list_res = json_decode(json_encode($status_list), true);

if(count($status_list_res) > 0)
{
    foreach($status_list_res as $val) {
        $html_content .= "<option value='".$val['PortID']."'>".$val['Status_name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>