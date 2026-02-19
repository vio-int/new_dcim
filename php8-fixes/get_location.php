<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$location_id = $_POST['location_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$Group =new IpamVLANGroup();

$GroupList = $Group->GetGroupLocationList($location_id);
$group_list_res = json_decode(json_encode($GroupList), true);

if(count($group_list_res) > 0)
{
    foreach($group_list_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>