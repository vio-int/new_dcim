<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$room_id = $_POST['room_id'];
$html_content = "<div class='table-responsive'>
    <table class='table table-hover table-headings table-striped table-bordered'>";
$respone = array();

$simulation=new RackSimulation();

$RackList = $simulation->GetRackDetails($room_id);
$rack_list_res = json_decode(json_encode($RackList), true);

$rack_ids = array();
$rack_arr = array();
if(count($rack_list_res) > 0)
{
    foreach($rack_list_res as $key => $val){
        $rack_arr[$val['group_no'].$val['row_position']] = $val;
        if($val['is_simulation'] == "Y"){
            $rack_ids[] = $val['parent_id'];
        } else {
            $rack_ids[] = $val['id'];
        }
    }
}

$RoomDetail = $simulation->GetRoomDetails($room_id);
$room_res = json_decode(json_encode($RoomDetail), true);
//print_r($room_res);exit;

$total_group_rows = $room_res['rows_per_rack'];
$total_columns = ($room_res['group_rows'] * 2 * 2) + 1;
$total_rows = ($room_res['group_columns'] * $total_group_rows) + $room_res['group_columns'];

if(count($room_res) > 0)
{
    // CODE FOR HEADER
    $html_content .= "<tr>";
    if ($total_columns > 0) {
        $html_content .="<td></td>";
        $k = 1;
        for ($char = 'AA'; $char <= 'ZZ'; $char++) {
            $html_content .= "<td>".$char."</td>";
            if($k == $total_columns){
                break;
            }
            $k++;    
        }
    }
    $html_content .= "</tr>";
    // END OF CODE FOR HEADER
    
    // CODE FOR ROWS
    if ($total_rows > 0) {
        $blank_rows = "false";
        $after_blank = $total_group_rows;
        $group_chang = 0;
        $group_row = 1;
        
        for ($i = 1; $i <= $total_rows; $i++) {
            $group_no = 1 + $group_chang;
            
            if ($blank_rows == "false") {
                // TABLE NOT BLANK ROWS CODE
                $html_content .="<tr>";
                if ($total_columns > 0) {
                    $html_content .="<td>".$i."</td>";
                    $columns = 0;
                    $columns_span = "false";
                    for ($j = 1; $j <= $total_columns; $j++) {
                        $custom_key = $group_no.$group_row;
                        if ($columns >= 2 && $columns_span == "false") {
                            
                            $sim_trash = "";
                            $drag_class = "ondrop='drop(event, this)' ondragover='allowDrop(event)'";
                            $td_drag_class = "ondrop='drop(event, this)' ondragover='allowDrop(event)'";
                            $is_simulation = "N";
                            if($rack_arr[$custom_key]['is_simulation']=='Y'){
                                $sim_trash = " <i class='fa fa-times sim_trash' data-id='".$rack_arr[$custom_key]['id']."'></i>";
                                $drag_class = "draggable='true' ondragstart='drag(event)'";
                                $td_drag_class = "";
                                $is_simulation = "Y";
                                //$drag_class = "ondrop='drop(event, this)' ondragover='allowDrop(event)'";
                            }
                            $html_content .= "<td ".$td_drag_class." class='rack_occupied' colspan='2' id='custom_div_".$i.$j."' data-simulation='".$is_simulation."' data-id='".$rack_arr[$custom_key]['id']."' data-height='".$rack_arr[$custom_key]['height']."' data-power='".$rack_arr[$custom_key]['max_kw']."' data-parent='".$rack_arr[$custom_key]['parent_id']."' data-group='".$group_no."' data-position='".$group_row."'><div ".$drag_class." id='device_".$rack_arr[$custom_key]['id']."' class='' data-height='".$rack_arr[$custom_key]['height']."' data-power='".$rack_arr[$custom_key]['max_kw']."' data-device='".$rack_arr[$custom_key]['name']."'><div class='col-sm-12 rack_title'>".$rack_arr[$custom_key]['name']."</div><div>".$sim_trash."</div></td>";
                            unset($rack_arr[$custom_key]);
                            $columns = $columns + 1;
                            //$custom_key = $custom_key + 1;
                            $group_no = $group_no + 1;
                            $columns_span = "true";
                        } else if($columns_span == "true"){
                            $columns = $columns + 1;
                            if ($columns > 3) {
                                $columns = 0;
                                $columns_span = "false";
                            }
                        } else {
                            $html_content .="<td></td>";
                            $columns = $columns + 1;
                        }
                    }
                    $group_row = $group_row + 1;
                }
                $html_content .="</tr>";
                // END OF CODE TABLE NOT BLANK ROWS
            } else {
                // TABLE BLANK ROWS CODE
                $html_content .= "<tr>";
                if ($total_columns > 0) {
                    $html_content .= "<td>".$i."</td>";
                    for ($l = 1; $l <= $total_columns; $l++) {
                        $html_content .= "<td></td>";
                    }
                }
                $html_content .= "</tr>";
                $blank_rows = "false"; 
                // END OF TABLE BLANK ROWS CODE
            }
            
            // FLAG FOR BLANK ROWS
            if ($i == $after_blank) {
                $blank_rows = "true";
                
                $group_chang = $group_chang + $group_no - 1;
                $group_row = 1;
                $after_blank = $after_blank + $total_group_rows + 1;
            }
            // END OF CODE FOR FLAG
        }
    }    
    // END OF CODE FOR ROWS
}
$html_content .="</table></div>";

$respone["status"] = "success";
$respone["res"] = $html_content;
$respone["rack_ids"] = $rack_ids;
echo json_encode($respone);
exit;
?>
            