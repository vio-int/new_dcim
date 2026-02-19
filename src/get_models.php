<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$asset = new Model();
$respone = array();
$html_content = "<option value=''>-- select --</option>";

$model_list = $asset->GetAssetModelList();
$model_list_res = json_decode(json_encode($model_list), true);

if(count($model_list_res) > 0)
{
    foreach($model_list_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>