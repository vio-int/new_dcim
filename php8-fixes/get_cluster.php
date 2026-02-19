<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$group_id = $_POST['group_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$Cluster =new Cluster();

$ClusterList = $Cluster->GetGroupClusterList($group_id);
$cluster_list_res = json_decode(json_encode($ClusterList), true);

if(count($cluster_list_res) > 0)
{
    foreach($cluster_list_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>