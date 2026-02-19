<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$asset = new AssetCategory();
$respone = array();
$html_content = "<option value=''>-- select --</option>";

$asset->CreateObject($_POST);

$status_list = $asset->GetCategoryList();
$status_list_res = json_decode(json_encode($status_list), true);

if(count($status_list_res) > 0)
{
    foreach($status_list_res as $val) {
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>