<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$rack_id = $_POST['rack_id'];
$html_content = "";
$respone = array();

$simulation=new Simulation();

$RackList = $simulation->GetDeviceList($rack_id);
$rack_list_res = json_decode(json_encode($RackList), true);

$RackDetails = $simulation->GetRackDetails($rack_id);
$custom_device = $RackDetails['height'];

$position_arr = array();
$allocat_space = 0;
if(count($rack_list_res) > 0){
    foreach($rack_list_res as $val){
        $position_arr[$val['Position']] = $val;
        $allocat_space = $allocat_space + $val['Device_height'];
    }
}

// Flag for continue without generating blank position
$free_space = $custom_device - $allocat_space;
/* echo "<pre>";
print_r($position_arr);
echo "</pre>";exit; */
if(count($rack_list_res) > 0)
{
    if($custom_device > 0)
    {
        
        for($i=1;$i<=$custom_device;$i++)
        {
            if(array_key_exists($i, $position_arr))
            {   
                $sim_trash = "";
                if($position_arr[$i]['Is_simulation'] == "Y"){
                    $sim_trash = " <i class='fa fa-times sim_trash' data-id='".$position_arr[$i]['PortID']."'></i>";
                }
                $image_content = "<div class='col-sm-7'></div>";
                $device_content = "<div class='col-sm-5 device_title tooltip' id='rack_div_".$position_arr[$i]['PortID']."' data-id='".$position_arr[$i]['PortID']."' data-height='".$position_arr[$i]['Device_height']."' data-power='".$position_arr[$i]['Device_power']."' data-position='".$i."'>".$position_arr[$i]['Device_name'].' '.$sim_trash."<span class='tooltiptext'>".$position_arr[$i]['Device_name']."</span></div>";
                
                $frontweb_path = _MEDIA_URL . "devices/{$position_arr[$i]['Front_picture']}";
                $frontfilename = _PATH . '/uploads/devices' . DIRECTORY_SEPARATOR . $position_arr[$i]['Front_picture'];
                if (file_exists($frontfilename) && $position_arr[$i]['Front_picture'] != "")
                {
                    $image_content = "<div class='col-sm-7'><img src='".$frontweb_path."' class='img-responsive device_image'></div>";
                }   
                if($position_arr[$i]['Device_height'] > 1){
                    $class_name = "multi_div";
                } else {
                    $class_name = "device_div";
                }
                $html_content .= "<div class='".$class_name."' id='rack_div_".$position_arr[$i]['PortID']."' data-id='".$position_arr[$i]['PortID']."' data-height='".$position_arr[$i]['Device_height']."' data-power='".$position_arr[$i]['Device_power']."' data-position='".$i."'>".$image_content." ".$device_content."</div>";
            } else {
                if($free_space > 0)
                {
                    $html_content .= "<div class='device_div' data-position='".$i."' id='custom_div_".$i."' ondrop='drop(event, this)' ondragover='allowDrop(event)'></div>";
                    $free_space = $free_space - 1;
                }
            }
        } 
    }
}

$respone["status"] = "success";
$respone["res"] = $html_content;
echo json_encode($respone);
exit;
?>