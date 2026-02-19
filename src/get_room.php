<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$location_id = $_POST['location_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$room=new Room();
$filter = array();
$filter["sort_on"] = "r.id";
$filter["sort_by"] = "Asc";
$filter["location"] = $location_id;
$RoomList = $room->GetRoomListRows($filter);
$room_list_res = json_decode(json_encode($RoomList), true);

if(count($room_list_res) > 0)
{
    foreach($room_list_res as $val){
        $html_content .= "<option value='".$val['PortID']."'>".$val['Name']."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>