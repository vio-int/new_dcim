<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$group_id = $_POST['group_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$Vlan =new IpamVLAN();

$VlanList = $Vlan->GetVlanLocationList($group_id);
$vlan_list_res = json_decode(json_encode($VlanList), true);

if(count($vlan_list_res) > 0)
{
    foreach($vlan_list_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>