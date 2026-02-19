<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$rack_id = $_POST['rack_id'];
$selected_id = $_POST['selected'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$rack=new Rack();

$filter = array();
$filter['rack'] = $rack_id;

$RackList = $rack->GetRackOne($filter);
$rack_list_res = json_decode(json_encode($RackList), true);
$height = $rack_list_res[0]['Height'];

$device_arr = $rack->GetDeviceAlocationList($filter);
$device_res = json_decode(json_encode($device_arr), true);
$device_pos = array();
if(count($device_res)>0)
{
    foreach ($device_res as $val){
        $device_pos[$val['position']] = $val['position'];
        if($val['height'] > 1){
            if($rack_list_res[0]['Descending'] == "Y")
            {
                $next_position = $val['position'] - 1;
            
                for($i=1;$i<$val['height'];$i++){
                    $device_pos[$next_position] = $next_position;
                    $next_position = $next_position-1;
                }
            } else {
                $next_position = $val['position'] + 1;
            
                for($i=1;$i<$val['height'];$i++){
                    $device_pos[$next_position] = $next_position;
                    $next_position = $next_position+1;
                }
            }
        }
    }
}

if($height > 0)
{
    for($i=1;$i<=$height;$i++){
        if(in_array($i, $device_pos)){
            continue;
        }
        if($selected_id == $i)
        {
            $select_cls = "selected";
        } else {
            $select_cls = "";
        }
        
        $html_content .= "<option value='".$i."' {$select_cls}>".$i."</option>";
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>