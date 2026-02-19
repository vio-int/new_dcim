<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$location_id = $_POST['location_id'];
$html_content = "";
$respone = array();

$room=new Room();
$filter = array();
$filter["sort_on"] = "r.id";
$filter["sort_by"] = "Asc";
$filter["location"] = $location_id;
$RoomList = $room->GetRoomCapacityRows($filter);
$room_list_res = json_decode(json_encode($RoomList), true);

if(count($room_list_res) > 0)
{
    foreach($room_list_res as $val){
        $html_content .= "<tr><td>".$val['Name']."<td></tr><tbody id='sub_rack_".$val['PortID']."'></tbody>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>