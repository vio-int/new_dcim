<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$room_id = $_POST['room_id'];
$group_no = $_POST['group_no'];
$is_group = $_POST['is_group'];
$is_position = $_POST['is_position'];

$html_content = "<option value=''>-- select --</option>";
$respone = array();

$Simulation =new RackSimulation();

if($room_id!="" && $is_group == "Y")
{
    $RoomDetails = $Simulation->GetRoomDetails($room_id);
    $RoomDetails_res = json_decode(json_encode($RoomDetails), true);
    $room_picture = $RoomDetails_res['picture'];
    $total_group = $RoomDetails_res['group_columns'] * $RoomDetails_res['group_rows'];

    if(count($total_group) > 0)
    {
        for($i=1;$i<=$total_group;$i++){
            $html_content .="<option value=\"$i\"$selected>$i</option>\n";
        }
    }
} else if($room_id!="" && $group_no!="" && $is_position == "Y")
{
    $RoomDetails = $Simulation->GetRoomDetails($room_id);
    $RoomDetails_res = json_decode(json_encode($RoomDetails), true);
    $room_picture = $RoomDetails_res['picture'];
    $RackDetails = $Simulation->GetRackDetails($room_id);
    $RackDetails_res = json_decode(json_encode($RackDetails), true);
    
    $available_arr = array();
    if(count($RackDetails_res) > 0){
        foreach($RackDetails_res as $val){
            if($val['group_no'] == $group_no){
                $available_arr[$val['row_position']] = $val['row_position'];
            }
        }
    }
    
    $total_group = $RoomDetails_res['rows_per_rack'];

    if($total_group > 0)
    {
        for($i=1;$i<=$total_group;$i++){
            if(!array_key_exists($i, $available_arr)){
                $html_content .="<option value=\"$i\"$selected>$i</option>\n";
            }
        }
    }
}

$respone["status"] = "success";
$respone["res"]['option'] = $html_content;
$respone["res"]['img'] = "uploads/room/".$room_picture;
echo json_encode($respone);
exit;
?>