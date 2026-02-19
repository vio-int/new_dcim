<?php
# catch for assholes that don't read the install instructions
if (!file_exists("db.inc.php")) {
    require_once("preflight.inc.php");
    exit;
}
/* 	if ( ! $_SERVER["HTTPS"] ) {
  printf( "<meta http-equiv='refresh' content='0; url='https://%s'>", $_SERVER["SERVER_NAME"] );
  exit();
  } */

require_once( 'db.inc.php' );
require_once( 'facilities.inc.php' );

$subheader = __("Data Center Operations Metrics");
$footer_text = "";

$rack = new Rack();
$log = new LogActions();
$device = new Device_new();
$capacity = new Capacity();

$filter = array();
$filter['rack'] = $_GET['id'];

$rack_arr = $rack->GetRackOne($filter);
$rack_res = json_decode(json_encode($rack_arr), true);

$device_arr = $rack->GetDeviceAlocationList($filter);
$device_res = json_decode(json_encode($device_arr), true);


$cap_filter = array();
$cap_filter["room_id"] = $_REQUEST["id"];

$capacity_res = $capacity->GetRackCapacityRowOne($cap_filter);
$capacity_arr = json_decode(json_encode($capacity_res), true);

$device_pos = array();
$allocat_space = 0;
if (count($device_res) > 0) {
    foreach ($device_res as $val) {
        $device_pos[$val['position']] = $val;
        $allocat_space = $allocat_space + $val['height'];
    }
}

$custom_device = $rack_res[0]['Height'];
// Flag for continue without generating blank position
$free_space = $custom_device - $allocat_space;
$free_space_rear = $custom_device - $allocat_space;
$free_space_li = $custom_device - $allocat_space;
$free_space_rear_li = $custom_device - $allocat_space;
$free_space_no = $custom_device - $allocat_space;
$free_space_rear_no = $custom_device - $allocat_space;
/* echo "<pre>";
  print_r($device_pos);
  echo "</pre>";exit; */

$filter_2 = array();
$filter_2['sort_on'] = "Time";
$filter_2['sort_by'] = "DESC";
$filter_2['class'] = "Rack";
$filter_2['object'] = $rack_res[0]['PortID'];
$log_arr = $log->GetClassLog($filter_2);
$log_res = json_decode(json_encode($log_arr), true);

/* echo "<pre>";
  print_r($log_res);
  echo "</pre>";exit; */

// Delete Code Start
if (isset($_POST['action']) && $_POST["action"] == "Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $rack->PortID = $_POST['PortID'];
        if ($rack->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
if (isset($_POST['action']) && $_POST["action"] == "Device_Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $device->PortID = $_POST['PortID'];
        if ($device->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
// END - AJAX
// URL PARAMETERS
$mark = "?";
if (!empty($_SERVER['QUERY_STRING'])) {
    $cur_page = $_GET['page'];
    $que_string = str_replace("page={$cur_page}", "", $_SERVER['QUERY_STRING']);
    $que_string = rtrim($que_string, "&");
    if ($que_string != "") {
        $mark = "?" . $que_string . "&";
    }
}
// END OF URL PARAMETERS CODE
?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Data Center Inventory</title>
        <link rel="stylesheet" href="css/inventory.php" type="text/css">
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css" />
        <![endif]-->

        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        
        <!-- Favicon -->
        <link type="image/x-icon" href="images/favicon.ico" rel="shortcut icon" />
    </head>
    <style>
        .freespace{
            background-color: #f5f5f5;
        }
        .frontfigure{
            font-size:12px;
            line-height:21px;
            text-align:left; 
            color: black;
        }
        .backfigure{
            font-size:12px; 
            line-height:21px;
            text-align:left; 
            color: black;
        }
        .pos-td{
            width: auto;
        }
        .bs-example{
            margin: 150px 50px;
        }
        .popover-content {
            background-color: white;
            border: solid 1px #b5b5b5;
            text-align: left;
            font-size: 14px;
        }
        .popover-title{
            font-size: 17px !important;
        }
        .popover.right {
            width: 180px;
        }
    </style>
    <body>
        <?php include('header_dcim.inc.php'); ?>
        <!-- LISTING CODE START -->
        <div class="container wrapper">
            <div class="main">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li><a href="rack_list.php">Rack</a></li>
                            <li><?php echo $rack_res[0]['Name']; ?></li>
                        </ol>
                    </div>
                </div>
                <div class="pull-right">
                    <a href="rack.php?PortID=<?php echo $rack_res[0]['PortID'] ?>" class="btn btn-warning">
                        <span class="fa fa-edit" aria-hidden="true"></span>
                        Edit this rack
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger" id="delete" data-id="<?php echo $rack_res[0]['PortID'] ?>">
                        <span class="fa fa-trash" aria-hidden="true"></span>
                        Delete this rack
                    </a>
                </div>
                <h1><?php echo $rack_res[0]['Name']; ?></h1>
                <?php /* <p>
                    <small class="text-muted">Created <?php echo $rack_res[0]['Created_at'] != "0000-00-00" ? date("M. d, Y", strtotime($rack_res[0]['Created_at'])) : "-"; ?> &middot; Updated <?php echo $rack_res[0]['Updated_at'] != "0000-00-00" ? date("M. d, Y", strtotime($rack_res[0]['Updated_at'])) : "-"; ?></small>
                </p> */ ?>
                <ul class="nav nav-tabs" style="margin-bottom: 20px">
                    <li class="tablinks active" onclick="openTab(event, 'Rack')" >
                        <a href="javascript:void(0);">Rack</a>
                    </li>
                    <li class="tablinks" onclick="openTab(event, 'Change_log')">
                        <a href="javascript:void(0);">Change log</a>
                    </li>
                </ul>

                <div id="Rack" class="tabcontent">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Rack</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <td>Site</td>
                                        <td><?php echo $rack_res[0]['Location_Name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Facility</td>
                                        <td><?php echo $rack_res[0]['Facility']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Serial No</td>
                                        <td><span><?php echo $rack_res[0]['Serial_no']; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Dimensions</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <td>Type</td>
                                        <td><?php echo $rack_res[0]['Type']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Width</td>
                                        <td><?php echo $rack_res[0]['Width']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Height</td>
                                        <td><span><?php echo $rack_res[0]['Height']; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Tags</strong>
                                </div>
                                <div class="panel-body">
                                    <span class="text-muted"><?php echo $rack_res[0]['Tag']; ?></span>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Comments</strong>
                                </div>
                                <div class="panel-body">
                                    <span class="text-muted"><?php echo $rack_res[0]['Comment']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Rack</strong>
                                </div>
                                <div class="panel-body">
                                    <section class="container3d">
                                        <div id="box" class="show-front">
                                            <figure class="front frontfigure">

                                                <!-- CODE FOR FRONT END RACK -->
                                                <div class="col-sm-5">
                                                    <table class="table table-headings table-responsive table-bordered cabinet" id="cabinet76" style="width: 242px; max-width: 240px;">
                                                        <tbody>
                                                            <tr style="">
                                                                <th colspan="3"><?php echo $rack_res[0]['Name'] ?> (Front)</th>
                                                            </tr>
                                                            <tr>
                                                                <th class="pos" style="width: 20%;">Pos</th>
                                                                <td align="center" style="width: 60%;">Device</td>
                                                                <th class="pos" style="width: 20%;">Pos</th>
                                                            </tr>
                                                            <?php
                                                            // DESCENDING CODE START
                                                            if ($rack_res[0]['Descending'] == "Y") {
                                                                $rowspan_count = 1;
                                                                $blank_rows = 'N';
                                                                if ($rack_res[0]['Height'] > 0) {
                                                                    for ($i = $rack_res[0]['Height']; $i > 0; $i--) {
                                                                        if (array_key_exists($i, $device_pos)) {
                                                                            ?>
                                                                            <tr id="pos<?= $i ?>" class="">
                                                                                <td id="postd<?= $i ?>" style="background-color: #12e2e2" class="pos Reserved pos-td dept<?= $i ?>"><?= $i ?></td>
                                                                                <?php if ($blank_rows == 'N') { ?>
                                                                                    <td style="cursor: all-scroll; padding:0px 0px 0px 0px !important;" rowspan="<?php echo $device_pos[$i]['height'] ?>">
                                                                                        <div class="picture" style="width: 220px; height: <?php echo $device_pos[$i]['height'] > 1 ? 33 * $device_pos[$i]['height'] : 33 ?>px;" id="<?= $device_pos[$i]['id'] ?>">  
                                                                                            <?php
                                                                                            $frontweb_path = _MEDIA_URL . "devices/{$device_pos[$i]['front_picture']}";
                                                                                            $frontfilename = _PATH . '/uploads/devices' . DIRECTORY_SEPARATOR . $device_pos[$i]['front_picture'];
                                                                                            if (file_exists($frontfilename)) {
                                                                                                ?>
                                                                                                <img data-deviceid="<?= $device_pos[$i]['id'] ?>" src="<?php echo $frontweb_path ?>" alt="<?php echo $device_pos[$i]['name'] ?>" data-label="<?php echo $device_pos[$i]['name'] ?>" data-toggle="popover" style="width: -webkit-fill-available;" title="<?php echo $device_pos[$i]['name'] ?>" data-html="true" data-content="<table><tr><td>Name:</td><td><?php echo $device_pos[$i]['name'] ?></td></tr><tr><td>Weight:</td><td><?php echo $device_pos[$i]['weight'] ?></td></tr><tr><td>Height:</td><td><?php echo $device_pos[$i]['height'] ?></td></tr></table>" draggable="true" ondragstart="drag(event)" id="device_<?= $i ?>" data-device="<?= $device_pos[$i]['id'] ?>" data-label="<?php echo $device_pos[$i]['name'] ?>">
                                                                                            <?php } ?>
                                                                                            <div class="label"><?php echo $device_pos[$i]['name'] ?></div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <?php
                                                                                    $rowspan_count = $device_pos[$i]['height'] - 1;
                                                                                    if ($rowspan_count > 0) {
                                                                                        $blank_rows = "Y";
                                                                                    } else {
                                                                                        $blank_rows = "N";
                                                                                    }
                                                                                } else {
                                                                                    $rowspan_count = $device_pos[$i]['height'] - 1;
                                                                                    if ($rowspan_count > 0) {
                                                                                        $blank_rows = "Y";
                                                                                    } else {
                                                                                        $blank_rows = "N";
                                                                                    }
                                                                                }
                                                                                ?>
                                                                                <td style="background-color: #12e2e2" class="pos pos-td"><?= $i ?></td>
                                                                            </tr>
                                                                        <?php } else { ?>
                                                                            <tr id="pos<?= $i ?>" class="">
                                                                                <td <?php echo $blank_rows == "Y" ? 'style="background-color: #12e2e2"' : '' ?> id="postd<?= $i ?>" class="pos pos-td"><?= $i ?></td>
                                                                                <?php if ($blank_rows == 'N') { ?>
                                                                                    <td data-position="<?= $i ?>" id="drop_id_<?= $i ?>" ondrop="drop(event, this)" ondragover="allowDrop(event)"></td>
                                                                                <?php
                                                                                } else {
                                                                                    $rowspan_count = $rowspan_count - 1;
                                                                                    if ($blank_rows == "Y") {
                                                                                        $set_color = "Y";
                                                                                    } else {
                                                                                        $set_color = "N";
                                                                                    }
                                                                                    if ($rowspan_count > 0) {
                                                                                        $blank_rows = "Y";
                                                                                    } else {
                                                                                        $blank_rows = "N";
                                                                                    }
                                                                                }
                                                                                ?>
                                                                                <td <?php echo $set_color == "Y" ? 'style="background-color: #12e2e2"' : ''; ?> class="pos pos-td"><?= $i ?></td>
                                                                                <?php $set_color = "N"; ?>
                                                                            </tr>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                                // ASCENDING CODE START    
                                                            } else {
                                                                $rowspan_count = 1;
                                                                $blank_rows = 'N';
                                                                if ($rack_res[0]['Height'] > 0) {
                                                                    for ($i = 1; $i <= $rack_res[0]['Height']; $i++) {
                                                                        if (array_key_exists($i, $device_pos)) {
                                                                            ?>
                                                                            <tr id="pos<?= $i ?>" class="">
                                                                                <td id="postd<?= $i ?>" style="background-color: #12e2e2" class="pos Reserved pos-td dept<?= $i ?>"><?= $i ?></td>
                                                                                <?php if ($blank_rows == 'N') { ?>
                                                                                <td style="cursor: all-scroll; padding:0px 0px 0px 0px !important;" rowspan="<?php echo $device_pos[$i]['height'] ?>" >
                                                                                        <div class="picture" style="width: 220px; height: <?php echo $device_pos[$i]['height'] > 1 ? 33 * $device_pos[$i]['height'] : 33 ?>px;" id="<?= $device_pos[$i]['id'] ?>">  
                                                                                            <?php
                                                                                            $frontweb_path = _MEDIA_URL . "devices/{$device_pos[$i]['front_picture']}";
                                                                                            $frontfilename = _PATH . '/uploads/devices' . DIRECTORY_SEPARATOR . $device_pos[$i]['front_picture'];
                                                                                            if (file_exists($frontfilename)) {
                                                                                                ?>
                                                                                                <img data-deviceid="<?= $device_pos[$i]['id'] ?>" src="<?php echo $frontweb_path ?>" alt="<?php echo $device_pos[$i]['name'] ?>" data-label="<?php echo $device_pos[$i]['name'] ?>" data-toggle="popover" style="width: -webkit-fill-available;" title="<?php echo $device_pos[$i]['name'] ?>" data-html="true" data-content="<table><tr><td>Name:</td><td><?php echo $device_pos[$i]['name'] ?></td></tr><tr><td>Weight:</td><td><?php echo $device_pos[$i]['weight'] ?></td></tr><tr><td>Height:</td><td><?php echo $device_pos[$i]['height'] ?></td></tr></table>" draggable="true" ondragstart="drag(event)" id="device_<?= $i ?>" data-device="<?= $device_pos[$i]['id'] ?>" data-label="<?php echo $device_pos[$i]['name'] ?>">
                                                                                            <?php } ?>
                                                                                            <div class="label"><?php echo $device_pos[$i]['name'] ?></div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <?php
                                                                                    $rowspan_count = $device_pos[$i]['height'] - 1;

                                                                                    if ($rowspan_count > 0) {
                                                                                        $blank_rows = "Y";
                                                                                    } else {
                                                                                        $blank_rows = "N";
                                                                                    }
                                                                                } else {
                                                                                    $rowspan_count = $device_pos[$i]['height'] - 1;
                                                                                    if ($rowspan_count > 0) {
                                                                                        $blank_rows = "Y";
                                                                                    } else {
                                                                                        $blank_rows = "N";
                                                                                    }
                                                                                }
                                                                                ?>
                                                                                <td style="background-color: #12e2e2" class="pos pos-td"><?= $i ?></td>
                                                                            </tr>
            <?php } else { ?>
                                                                            <tr id="pos<?= $i ?>" class="">
                                                                                <td <?php echo $blank_rows == "Y" ? 'style="background-color: #12e2e2"' : '' ?> id="postd<?= $i ?>" class="pos pos-td"><?= $i ?></td>
                <?php if ($blank_rows == 'N') { ?>
                                                                                    <td data-position="<?= $i ?>" id="drop_id_<?= $i ?>" ondrop="drop(event, this)" ondragover="allowDrop(event)"></td>
                                                                                <?php
                                                                                } else {
                                                                                    $rowspan_count = $rowspan_count - 1;
                                                                                    if ($blank_rows == "Y") {
                                                                                        $set_color = "Y";
                                                                                    } else {
                                                                                        $set_color = "N";
                                                                                    }
                                                                                    if ($rowspan_count > 0) {
                                                                                        $blank_rows = "Y";
                                                                                    } else {
                                                                                        $blank_rows = "N";
                                                                                    }
                                                                                }
                                                                                ?>
                                                                                <td <?php echo $set_color == "Y" ? 'style="background-color: #12e2e2"' : ''; ?> class="pos pos-td"><?= $i ?></td>
                                                                                <?php $set_color = "N"; ?>
                                                                            </tr>
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>                
                                                <!-- CODE FOR BACKEND END RACK -->
                                                
                                                <!-- CABINET METRICS CODE START -->
                                                <?php 
                                                $space_per = (($capacity_arr[0]['Total_used_space'] * 100) / $capacity_arr[0]['Total_space']); 
                                                $weight_per = (($capacity_arr[0]['Total_used_weight'] * 100) / $capacity_arr[0]['Total_weight']); 
                                                $power_per = (($capacity_arr[0]['Total_used_power'] * 100) / $capacity_arr[0]['Total_power']); 
                                                ?>
                                                <div class="col-sm-5" style="margin-left:90px">
                                                    <div id="infopanel" class="item">
                                                    <fieldset id="metrics">
                                                        <legend>Cabinet Metrics</legend>
                                                        <table style="background: white;" border="1">
                                                            <tbody><tr>
                                                                    <td>Space
                                                                        <div class="meter-wrap">
                                                                            <div class="meter-value" style="background-color: <?php echo number_format((float)$space_per, 2, '.', '') > 100 ?'#CC0000':'#00AA00'?>; width: <?php echo $space_per ?>%;">
                                                                                <div class="meter-text"><?php echo number_format((float)$space_per, 2, '.', '') > 100?100:number_format((float)$space_per, 2, '.', '')?>%</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Weight [<?php echo $capacity_arr[0]['Total_used_weight']?>]
                                                                        <div class="meter-wrap">
                                                                            <div class="meter-value" style="background-color: <?php echo number_format((float)$weight_per, 2, '.', '') > 100 ?'#CC0000':'#00AA00'?>; width: <?php echo $weight_per?>%;">
                                                                                <div class="meter-text"><?php echo number_format((float)$weight_per, 2, '.', '') > 100 ?100:number_format((float)$weight_per, 2, '.', '');?>%</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Computed Watts
                                                                        <div class="meter-wrap">
                                                                            <div class="meter-value" style="background-color: <?php echo $capacity_arr[0]['Total_used_power'] > $capacity_arr[0]['Total_power'] ?'#CC0000':'#00AA00'?>; width: <?php echo $power_per ?>%;">
                                                                                <div class="meter-text"><?php echo $capacity_arr[0]['Total_used_power']?> kW / <?php echo $capacity_arr[0]['Total_power']?> kW</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Measured Watts
                                                                        <div class="meter-wrap">
                                                                            <div class="meter-value" style="background-color: #00AA00; width: 100%;">
                                                                                <div class="meter-text">Ok / Ok</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                        <p>Approximate Center of Gravity: <span id="tippingpoint">0U</span></p>
                                                    </fieldset>
                                                    </div>
                                                </div>
                                            </figure>

                                        </div>
                                    </section>
                                    <?php /* <div class="col-md-6 col-sm-6 col-xs-12">
                                      <div class="rack_header">
                                      <h4>Front</h4>
                                      </div>
                                      <ul class="rack_legend">
                                      <?php if($rack_res[0]['Height'] > 0){
                                      for($i=1;$i<=$rack_res[0]['Height'];$i++) {
                                      if(array_key_exists($i, $device_pos)) {?>
                                      <li><?php //echo $i; ?></li>
                                      <?php } else {
                                      if($free_space_no > 0) { ?>
                                      <li></li>
                                      <?php $free_space_no = $free_space_no - 1; ?>
                                      <?php } } } } ?>
                                      </ul>
                                      <div class="rack_frame">
                                      <!-- Render rear view of devices on far face -->
                                      <ul class="rack rack_far_face">
                                      <?php if($rack_res[0]['Height'] > 0){
                                      for($i=1;$i<=$rack_res[0]['Height'];$i++) {
                                      if(array_key_exists($i, $device_pos)) {?>
                                      <li></li>
                                      <?php } else {
                                      if($free_space_li > 0) { ?>
                                      <li></li>
                                      <?php $free_space_li = $free_space_li - 1; ?>
                                      <?php } } } } ?>
                                      </ul>

                                      <!-- Render front view of devices on near face -->
                                      <ul class="rack rack_near_face">
                                      <?php if($rack_res[0]['Height'] > 0){
                                      for($i=1;$i<=$rack_res[0]['Height'];$i++) {
                                      if(array_key_exists($i, $device_pos)) {?>
                                      <li class="<?php echo $device_pos[$i]["height"]>1?'multi_occupied':'occupied'?>">
                                      <a href="javascript:void(0);"><?php echo $device_pos[$i]["name"]?> <i class="fa fa-times delete_device" data-id="<?php echo $device_pos[$i]["id"] ?>"></i></a>
                                      </li>
                                      <?php } else {
                                      if($free_space > 0) { ?>
                                      <li class="available">
                                      <a href="device.php?from=rack_detail&rack_id=<?php echo $_GET['id']?>&position=<?= $i?>" class="">add device</a>
                                      <?php $free_space = $free_space - 1; ?>
                                      </li>
                                      <?php } } } } ?>
                                      </ul>
                                      </div>
                                      </div>
                                      <div class="col-md-6 col-sm-6 col-xs-12">
                                      <div class="rack_header">
                                      <h4>Rear</h4>
                                      </div>
                                      <ul class="rack_legend">
                                      <?php if($rack_res[0]['Height'] > 0){
                                      for($i=1;$i<=$rack_res[0]['Height'];$i++) {
                                      if(array_key_exists($i, $device_pos)) {?>
                                      <li><?php // echo $i; ?></li>
                                      <?php } else {
                                      if($free_space_rear_no > 0) { ?>
                                      <li></li>
                                      <?php $free_space_rear_no = $free_space_rear_no - 1; ?>
                                      <?php } } } } ?>
                                      </ul>
                                      <div class="rack_frame">
                                      <!-- Render rear view of devices on far face -->
                                      <ul class="rack rack_far_face">
                                      <?php if($rack_res[0]['Height'] > 0){
                                      for($i=1;$i<=$rack_res[0]['Height'];$i++) {
                                      if(array_key_exists($i, $device_pos)) {?>
                                      <li></li>
                                      <?php } else {
                                      if($free_space_rear_li > 0) { ?>
                                      <li></li>
                                      <?php $free_space_rear_li = $free_space_rear_li - 1; ?>
                                      <?php } } } } ?>
                                      </ul>

                                      <!-- Render front view of devices on near face -->
                                      <ul class="rack rack_near_face">
                                      <?php if($rack_res[0]['Height'] > 0){
                                      for($i=1;$i<=$rack_res[0]['Height'];$i++) {
                                      if(array_key_exists($i, $device_pos)) {?>
                                      <li class="occupied">
                                      <a href="javascript:void(0);"><?php echo $device_pos[$i]["name"]?> <i class="fa fa-times delete_device" data-id="<?php echo $device_pos[$i]["id"] ?>"></i> </a>
                                      </li>
                                      <?php } else {
                                      if($free_space_rear > 0)
                                      { ?>
                                      <li class="available">
                                      <a href="device.php?from=rack_detail&&rack_id=<?php echo $_GET['id']?>&position=<?= $i?>" class="">add device</a>
                                      <?php $free_space_rear = $free_space_rear - 1; ?>
                                      </li>
                                      <?php } } } } ?>
                                      </ul>
                                      </div>
                                      </div> */ ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  
                <div id="Change_log" class="tabcontent" style="display:none">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Change Log</strong>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-headings table-striped">
                                            <tr>
                                                <th>User Name</th>
                                                <th>Class</th>
                                                <th>Action</th>
                                                <th>Old Value</th>
                                                <th>New Value</th>
                                                <th>Time</th>
                                            </tr>
                                            <tbody id="log_table">
                                                <?php
                                                if (count($log_res) > 0) {
                                                    foreach ($log_res as $val) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $val['UserID']; ?></td>
                                                            <td><?php echo $val['Class']; ?></td>
                                                            <td><?php echo $val['Property']; ?></td>
                                                            <td><?php echo $val['OldVal']; ?></td>
                                                            <td><?php echo $val['NewVal']; ?></td>
                                                            <td><?php echo $val['Time']; ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="text-center"><td colspan="6">Record not found!</td></tr> 
                                                <?php } ?>
                                            </tbody>    
                                            <?php /* if(count($log_res) > 0){?>
                                              <tfoot>
                                              <tr><td colspan="6" class="text-right">
                                              <?php
                                              $limit = 50;
                                              $total_rec = $log->GetDashClassLog($filter_2);
                                              $total_pages = ceil($total_rec['total_logs'] / $limit);
                                              $pagLink = "<ul class='pagination'><li class='page-item page_go' data-id='1' data-obj='".$rack_res[0]['PortID']."'><a class='page-link' href='javascript:void(0);'>&laquo;</a></li>";
                                              for ($i=1; $i<=$total_pages; $i++) {
                                              $selected = "";
                                              if($_GET['page'] == $i)
                                              {
                                              $selected = "active";
                                              } else if(empty($_GET['page'])){
                                              $selected = "active";
                                              }
                                              $pagLink .= "<li class='page-item page_go' data-id='".$i."' data-obj='".$rack_res[0]['PortID']."'><a class='page-link ".$selected."' href='javascript:void(0);'>".$i."</a></li>";
                                              }
                                              echo $pagLink . "<li class='page-item page_go' data-id='".$total_pages."' data-obj='".$rack_res[0]['PortID']."'><a class='page-link' href='javascript:void(0);'>&raquo;</a></li></ul>";
                                              ?>
                                              </td></tr>
                                              </tfoot>
                                              <?php } */ ?>    
                                        </table>
                                    </div>    
                                </div>
                            </div>    
                        </div>
                    </div>
                </div>  
            </div>
        </div>
    </body>
    <!-- Footer -->
    <?php if ($footer_text != "") { ?>
        <footer class="page-footer font-small footer">
            <spam><?php echo $footer_text; ?></spam>
        </footer>
    <?php } ?>
    <!-- Footer -->
</html>
<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="popover"]').popover({
            placement: 'right'
        });

        $('#delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Delete'}, function (data) {
                    if (data) {
                        location.href = 'location_list.php';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }
        });
        $('.delete_device').click(function () {
            Swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    // If manufacturerid unset then just delete 
                    transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                    $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Device_Delete'}, function (data) {
                        if (data) {
                            Swal(
                                    'Deleted!',
                                    'Your device has been deleted.',
                                    'success'
                                    )
                            location.href = '';
                        } else {
                            alert("Something's gone horrible wrong");
                        }
                    });
                }
            })
        });
        $('.page_go').click(function () {
            var page_no = $(this).data("id");
            var obj_id = $(this).data("obj");
            $.ajax({
                method: "POST",
                url: "ajax_activity_log.php",
                data: {page: page_no, class: "Rack", object: obj_id},
                dataType: 'JSON',
                success: function (res) {
                    if (res.status == 'success') {
                        $("#log_table").html(res.res);
                    }
                }
            });

        });
    });
    function openTab(evt, tabName) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("Text", ev.target.id);
        //console.log(ev.target.id);
    }

    function drop(ev, target) {
        ev.preventDefault();
        var source_id = $("#" + ev.dataTransfer.getData("text")).attr('id');
        var drop_id = target.id;

        var data_position = $("#" + drop_id).data("position");
        var data_device = $("#" + source_id).data("device");
        //console.log(source_id, data_position, data_device);

        var data = ev.dataTransfer.getData("Text");
        //ev.target.appendChild(document.getElementById(data));
        // ADD NEW HTML INTO THE RACK DIV ON DROP
        /* var custome_div = document.createElement('div');
        custome_div.innerHTML = '<div class="picture" style="width: 220px; height: 20px;"><img data-deviceid="3" src="'+ data + '" alt="">';
        document.getElementById(drop_id).appendChild(custome_div); */



        if (data_position != "" && data_device != "")
        {
            $.ajax({
                url: 'ajax_change_device_position.php',
                type: 'post',
                data: {device_id: data_device, position: data_position},
                dataType: 'JSON',
                success: function (res) {
                    if (res.status == 'success') {
                        var main_device_pos = source_id.replace("device_", "");
                        //$("#postd" + main_device_pos).after("<td></td>");
                        $("#postd" + main_device_pos);
                        location.reload();
                    }
                }
            });
        }
    }
</script>