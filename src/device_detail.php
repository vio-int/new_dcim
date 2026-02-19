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

$device = new Device_new();
$log = new LogActions();
$port_class = new ConsolePowerPort();
$service = new Service();
$interface = new DeviceInterface();
$console_server = new ConsoleServer();
$idrac_setting = new IdracSetting();

$filter = array();
$filter['device'] = $_GET['id'];

$device_arr = $device->GetDeviceOne($filter);
$device_res = json_decode(json_encode($device_arr), true);

$port_arr = $port_class->GetPortList();
$port_res = json_decode(json_encode($port_arr), true);

$interface_arr = $interface->GetDeviceInterfaceList();
$interface_res = json_decode(json_encode($interface_arr), true);
$con_server_arr = $console_server->GetConsoleServerList();
$con_server_res = json_decode(json_encode($con_server_arr), true);

$pass_device_id = "";
if(isset($_GET['id']) && $_GET['id'] != ""){
    $pass_device_id = $_GET['id'];
}
$setting_arr = $idrac_setting->GetIdracSettingDeviceList($pass_device_id);
$setting_res = json_decode(json_encode($setting_arr), true);
//print_r($con_server_res);exit;
$filter_2 = array();
$filter_2['sort_on'] = "Time";
$filter_2['sort_by'] = "DESC";
$filter_2['class'] = "Device_new";
$filter_2['object'] = $device_res[0]['PortID'];
$log_arr = $log->GetClassLog($filter_2);
$log_res = json_decode(json_encode($log_arr), true);

$filter_4 = array();
$filter_4['sort_by'] = "Asc";
$filter_4['sort_on'] = "s.id";
$filter_4['device'] = $device_res[0]['PortID'];
$service_arr = $service->GetServiceListRows($filter_4);
$service_res = json_decode(json_encode($service_arr), true);

/* echo "<pre>";
  print_r($log_res);
  echo "</pre>";exit; */

// Delete Code Start
if (isset($_POST['action']) && $_POST["action"] == "Port_Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $port_class->PortID = $_POST['PortID'];
        if ($port_class->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
if (isset($_POST['action']) && $_POST["action"] == "Service_Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $service->PortID = $_POST['PortID'];
        if ($service->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
if (isset($_POST['action']) && $_POST["action"] == "Interface_Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $interface->PortID = $_POST['PortID'];
        if ($interface->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
if (isset($_POST['action']) && $_POST["action"] == "Server_Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $console_server->PortID = $_POST['PortID'];
        if ($console_server->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
if (isset($_POST['action']) && $_POST["action"] == "Idrac_delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $idrac_setting->PortID = $_POST['PortID'];
        if ($idrac_setting->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
// END - AJAX

// URL PARAMETERS
$mark = "?";
if(!empty($_SERVER['QUERY_STRING'])){
    $cur_page = $_GET['page'];
    $que_string = str_replace("page={$cur_page}", "", $_SERVER['QUERY_STRING']);
    $que_string = rtrim($que_string,"&");
    if($que_string!="")
    {
        $mark = "?".$que_string."&";
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
        <!-- Favicon -->
        <link type="image/x-icon" href="images/favicon.ico" rel="shortcut icon" />
        
        <link rel="stylesheet" href="css/inventory.php" type="text/css">
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css" />
        <![endif]-->

        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
    </head>
    <body>
        <?php include('header_dcim.inc.php'); ?>
        <!-- LISTING CODE START -->
        <div class="container wrapper">
            <div class="main">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li><a href="device_list.php">Device</a></li>
                            <li><?php echo $device_res[0]['Name']; ?></li>
                        </ol>
                    </div>
                </div>
                <div class="pull-right">
                    <a href="device.php?PortID=<?php echo $device_res[0]['PortID'] ?>" class="btn btn-warning">
                        <span class="fa fa-edit" aria-hidden="true"></span>
                        Edit this device
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger" id="delete" data-id="<?php echo $device_res[0]['PortID'] ?>">
                        <span class="fa fa-trash" aria-hidden="true"></span>
                        Delete this device
                    </a>
                </div>
                <h1><?php echo $device_res[0]['Name']; ?></h1>
                <p>
                    <small class="text-muted">Created <?php echo $device_res[0]['Created_at']!="0000-00-00"?date("M. d, Y", strtotime($device_res[0]['Created_at'])):"-"; ?> &middot; Updated <?php echo $device_res[0]['Updated_at']!="0000-00-00"?date("M. d, Y", strtotime($device_res[0]['Updated_at'])):"-"; ?></small>
                </p>
                <ul class="nav nav-tabs" style="margin-bottom: 20px">
                    <li class="tablinks active" onclick="openTab(event, 'Device')" >
                        <a href="javascript:void(0);">Device</a>
                    </li>
                    <li class="tablinks" onclick="openTab(event, 'Change_log')">
                        <a href="javascript:void(0);">Change log</a>
                    </li>
                </ul>

                <div id="Device" class="tabcontent">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Device</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <td>Site</td>
                                        <td><?php echo $device_res[0]['Location_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Rack</td>
                                        <td><?php echo $device_res[0]['Rack_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Position</td>
                                        <td><?php echo $device_res[0]['Position']."U / ".$device_res[0]['Rack_face']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Device Type</td>
                                        <td><?php echo $device_res[0]['Device_type']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Serial Number</td>
                                        <td><?php echo $device_res[0]['Serial_no']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Asset tag</td>
                                        <td><span><?php echo $device_res[0]['Asset_tag']; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Management</strong>
                                </div>
                                
                                    <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <td>Role</td>
                                        <td><?php echo $device_res[0]['Device_role']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Platform</td>
                                        <td><?php echo $device_res[0]['Platform']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td><?php echo $device_res[0]['Status']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Primary IPv4</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Primary IPv6</td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Tags</strong>
                                </div>
                                <div class="panel-body">
                                    <span class="text-muted"><?php echo $device_res[0]['Tag']; ?></span>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Comments</strong>
                                </div>
                                <div class="panel-body">
                                    <span class="text-muted"><?php echo $device_res[0]['Comment']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Console / Power</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <?php if(count($port_res) > 0) { 
                                        foreach ($port_res as $val) { ?>
                                        <tr>
                                            <td><?php echo $val['Port_type']=="Console"?"<i class='fa fa-keyboard'></i> ":"<i class='fa fa-bolt'></i> "; ?><?php echo $val['Name']; ?></td>
                                            <td><?php echo $val['Tag']; ?></td>
                                            <td class="text-right">
                                                <?php if($val['Port_type']=="Console") { ?>
                                                    <a href="console_conn.php" title="Connect" class="btn btn-success btn-sm">
                                                        <i class="fa fa-link" aria-hidden="true"></i>
                                                    </a>
                                                    <a href="console_port.php?PortID=<?= $val['PortID'] ?>" title="Edit port" class="btn btn-info btn-sm">
                                                        <i class="fa fa-edit" aria-hidden="true"></i>
                                                    </a>
                                                <?php } else { ?>
                                                    <a href="power_conn.php" title="Connect" class="btn btn-success btn-sm">
                                                        <i class="fa fa-link" aria-hidden="true"></i>
                                                    </a>
                                                    <a href="power_port.php?PortID=<?= $val['PortID'] ?>" title="Edit port" class="btn btn-info btn-sm">
                                                        <i class="fa fa-edit" aria-hidden="true"></i>
                                                    </a>
                                                <?php } ?>
                                                <a href="javascript:void(0);" data-id="<?= $val['PortID'] ?>" title="Delete port" class="btn btn-danger btn-sm port_delete">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>    
                                    <?php }    
                                    } ?>
                                </table>
                                <div class="panel-footer text-right">
                                    <a href="console_port.php" class="btn btn-primary">
                                        <span class="fa fa-plus" aria-hidden="true"></span> Add console port
                                    </a>
                                    <a href="power_port.php" class="btn btn-primary">
                                        <span class="fa fa-plus" aria-hidden="true"></span> Add power port
                                    </a>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Services</strong>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                    <table class="table table-hover table-headings table-striped">
                                        <?php if(count($service_res) > 0){
                                        foreach($service_res as $val){?>
                                            <tr>
                                                <td><?php echo $val['Name']?></td>
                                                <td><?php echo $val['Port_type']."/".$val['Port']?></td>
                                                <td><?php echo $val['IP_address']?></td>
                                                <td><?php echo $val['Description']?></td>
                                                <td>
                                                    <a href="device_service_add.php?PortID=<?= $val['PortID'] ?>" title="Edit Service" class="btn btn-info btn-sm">
                                                        <i class="fa fa-edit" aria-hidden="true"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" data-id="<?= $val['PortID'] ?>" title="Delete Service" class="btn btn-danger btn-sm service_delete">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </a>
                                                </td>
                                            </tr>        
                                        <?php }
                                        } ?>
                                    </table>
                                    </div>    
                                </div>
                                <div class="panel-footer text-right">
                                    <a href="device_service_add.php" class="btn btn-primary">
                                        <span class="fa fa-plus" aria-hidden="true"></span> Assign Service
                                    </a>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Images</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                <tr>
                                    <td>Front Face</td>
                                    <td>
                                    <?php 
                                    $web_path = _MEDIA_URL . "devices/{$device_res[0]['Front_picture']}";
                                    $filename = _PATH.'/uploads/devices'.DIRECTORY_SEPARATOR.$device_res[0]['Front_picture'];
                                    if(file_exists($filename) && $device_res[0]['Front_picture']!=""){ ?>    
                                        <a href="<?php echo $web_path; ?>" target="__blank"><i class="fa fa-camera"></i> <?php echo $device_res[0]['Front_picture']; ?></a>
                                    <?php } ?>    
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rear Face</td>
                                    <td>
                                    <?php 
                                    $rearweb_path = _MEDIA_URL . "devices/{$device_res[0]['Rear_picture']}";
                                    $rearfilename = _PATH.'/uploads/devices'.DIRECTORY_SEPARATOR.$device_res[0]['Rear_picture'];
                                    if(file_exists($rearfilename) && $device_res[0]['Rear_picture']!=""){ ?>
                                        <a href="<?php echo $rearweb_path; ?>" target="__blank"><i class="fa fa-camera"></i> <?php echo $device_res[0]['Rear_picture']; ?></a>
                                    <?php } ?></td>
                                </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Interfaces</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <th>Name</th>
                                        <th>LAG</th>
                                        <th>Description</th>
                                        <th>MTU</th>
                                        <th>Mode</th>
                                        <th>Connection</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php if(count($interface_res) > 0) {
                                        foreach($interface_res as $val) {
                                            if(substr($val['Form_factor'],0,1)== 1){
                                                $connection = "Virtual Interface";    
                                            } else if(substr($val['Form_factor'],0,1)== 2){
                                                $connection = "Ethernet (Fixed)";    
                                            } else if(substr($val['Form_factor'],0,1)== 3){
                                                $connection = "Ethernet (Modular)";    
                                            } else if(substr($val['Form_factor'],0,1)== 4){
                                                $connection = "Wireless";    
                                            } else if(substr($val['Form_factor'],0,1)== 5){
                                                $connection = "Fiber Channel";    
                                            } else if(substr($val['Form_factor'],0,1)== 6){
                                                $connection = "Serial";    
                                            } else if(substr($val['Form_factor'],0,1)== 7){
                                                $connection = "Stacking";    
                                            } else {
                                                $connection = "Other";    
                                            } ?> 
                                    <tr>
                                        <td><?php echo "<i class='fa fa-wrench'></i> ".$val['Name']; ?></td>
                                        <td><?php echo $val['Parent_lag']; ?></td>
                                        <td><?php echo $val['Description']; ?></td>
                                        <td><?php echo $val['MTU']; ?></td>
                                        <td><?php echo $val['Mode']; ?></td>
                                        <td><?php echo $connection; ?></td>
                                        <td><a href="device_interface_add.php?PortID=<?= $val['PortID'] ?>" title="Edit Interface" class="btn btn-info btn-sm">
                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                            </a>
                                            <a href="javascript:void(0);" data-id="<?= $val['PortID'] ?>" title="Delete Interface" class="btn btn-danger btn-sm interface_delete">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } } ?>
                                </table>
                                <div class="panel-footer text-right">
                                    <a href="device_interface_add.php" class="btn btn-primary">
                                        <span class="fa fa-plus" aria-hidden="true"></span> Add Interfaces
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Console Server Ports</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <th>Name</th>
                                        <th>Connection</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php if(count($con_server_res) > 0) {
                                        foreach($con_server_res as $val){ ?>
                                        <tr>
                                            <td><?php echo $val['Name']; ?></td>
                                            <td><?php echo $val['Device_name']; ?></td>
                                            <td>
                                            <a href="console_conn.php" title="Connect" class="btn btn-success btn-sm">
                                                    <i class="fa fa-link" aria-hidden="true"></i>
                                            </a>
                                            <a href="console_server.php?PortID=<?= $val['PortID'] ?>" title="Edit" class="btn btn-info btn-sm">
                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                            </a>
                                            <a href="javascript:void(0);" data-id="<?= $val['PortID'] ?>" title="Delete" class="btn btn-danger btn-sm server_delete">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </a></td>
                                        </tr>   
                                    <?php }
                                    } else { ?>
                                        <tr><td colspan="3" class="text-center">Records not found</td></tr>
                                    <?php } ?>
                                    
                                </table>
                                <div class="panel-footer text-right">
                                    <a href="console_server.php" class="btn btn-primary">
                                        <span class="fa fa-plus" aria-hidden="true"></span> Add console server ports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>iDRAC Settings</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <th>Device</th>
                                        <th>Mac IPaddress</th>
                                        <th>IPv4 Address</th>
                                        <th>Gateway</th>
                                        <th>Subnet Mask</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php if(count($setting_res) > 0) {
                                        foreach($setting_res as $val){?>
                                        <tr>
                                            <td><?php echo $val['Device_name']; ?></td>
                                            <td><?php echo $val['Mac_address']; ?></td>
                                            <td><?php echo $val['Static_ip_address']; ?></td>
                                            <td><?php echo $val['Static_gateway']; ?></td>
                                            <td><?php echo $val['Static_subnet_mask']; ?></td>
                                            <td>
                                                <a href="idrac_setting.php?PortID=<?= $val['PortID'] ?>&device_id=<?php echo $_GET['id'] ?>" title="Edit" class="btn btn-info btn-sm">
                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                            </a>
                                            <a href="javascript:void(0);" data-id="<?= $val['PortID'] ?>" title="Delete" class="btn btn-danger btn-sm idrac_delete">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </a></td>
                                        </tr>   
                                    <?php }
                                    } else { ?>
                                        <tr><td colspan="6" class="text-center">Records not found</td></tr>
                                    <?php } ?>
                                    
                                </table>
                                <div class="panel-footer text-right">
                                    <a href="idrac_setting.php?device_id=<?php echo $_GET['id'] ?>" class="btn btn-primary">
                                        <span class="fa fa-plus" aria-hidden="true"></span> Add iDRAC Settings
                                    </a>
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
                                    <?php if(count($log_res) >0){ 
                                        foreach($log_res as $val){?>
                                        <tr>
                                            <td><?php echo $val['UserID'];?></td>
                                            <td><?php echo $val['Class'];?></td>
                                            <td><?php echo $val['Property'];?></td>
                                            <td><?php echo $val['OldVal'];?></td>
                                            <td><?php echo $val['NewVal'];?></td>
                                            <td><?php echo $val['Time'];?></td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr class="text-center"><td colspan="6">Record not found!</td></tr> 
                                    <?php } ?>
                                    </tbody>    
                                    <?php /* if(count($log_res) > 0){ ?>
                                    <tfoot>
                                        <tr><td colspan="6" class="text-right">
                                            <?php 
                                                $limit = 15;
                                                $total_rec = $log->GetDashClassLog($filter_2); 
                                                $total_pages = ceil($total_rec['total_logs'] / $limit);
                                                $pagLink = "<ul class='pagination'><li class='page-item page_go' data-id='1' data-obj='".$device_res[0]['PortID']."'><a class='page-link' href='javascript:void(0);'>&laquo;</a></li>"; 
                                                for ($i=1; $i<=$total_pages; $i++) {
                                                    $selected = "";
                                                    if($_GET['page'] == $i)
                                                    {
                                                        $selected = "active";
                                                    } else if(empty($_GET['page'])){
                                                        $selected = "active";
                                                    }
                                                    $pagLink .= "<li class='page-item page_go' data-id='".$i."' data-obj='".$device_res[0]['PortID']."'><a class='page-link ".$selected."' href='javascript:void(0);'>".$i."</a></li>";	
                                                }
                                                echo $pagLink . "<li class='page-item page_go' data-id='".$total_pages."' data-obj='".$device_res[0]['PortID']."'><a class='page-link' href='javascript:void(0);'>&raquo;</a></li></ul>"; 
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
        $('.port_delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Port_Delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }
        });
        $('.service_delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Service_Delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }
        });
        $('.interface_delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Interface_Delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }
        });
        $('.server_delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Server_Delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }
        });
        $('.idrac_delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Idrac_delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }
        });
        
        $('.page_go').click(function () {
            var page_no = $(this).data("id");
            var obj_id = $(this).data("obj");
            $.ajax({
                method : "POST",
                url: "ajax_activity_log.php", 
                data: {page:page_no, class: "Device_new", object:obj_id},
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
</script>