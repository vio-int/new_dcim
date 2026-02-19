<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Device");
$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

//define("_PATH", str_replace("device.php", "", __FILE__));
$mfg = new Device_new();
$rack = new Rack();
$manufacture = new Manufacture();

$RackList = $rack->GetRackList();

$ManufactureList = $manufacture->GetManufactureList();


if (isset($_GET['from'])) {
    $simulation = new Simulation();
    $RackDetails = $simulation->GetRackDetails($_GET['rack_id']);
    $mfg->Rack = $RackDetails['id'];
    $mfg->Site = $RackDetails['site_id'];
    $mfg->Position = $_GET['position'];
    $html_content .= "<option value='" . $_GET['position'] . "' selected>" . $_GET['position'] . "</option>";
}

// AJAX Start
if (isset($_POST['action']) && $_POST["action"] == "Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $mfg->PortID = $_POST['PortID'];
        if ($mfg->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}

// END - AJAX

if (isset($_REQUEST["PortID"]) && $_REQUEST["PortID"] > 0) {
    $mfg->PortID = (isset($_POST['PortID']) ? $_POST['PortID'] : $_GET['PortID']);
    $mfg->GetOrderByID();
    //print_r($mfg);exit;

    $RackList = $rack->GetLocationRackList($mfg->Site);
    $filter = array();
    $filter['rack'] = $mfg->Rack;
    $PositionList = $rack->GetRackOne($filter);
    $rack_list_res = json_decode(json_encode($PositionList), true);
    $height = $rack_list_res[0]['Height'];

    $device_arr = $rack->GetDeviceAlocationList($filter);
    $device_res = json_decode(json_encode($device_arr), true);
    $device_pos = array();
    if (count($device_res) > 0) {
        foreach ($device_res as $val) {
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

    $html_content = "";
    if ($height > 0) {
        for ($i = 1; $i <= $height; $i++) {
            if (in_array($i, $device_pos) && $mfg->Position != $i) {
                continue;
            }
            if ($mfg->Position == $i) {
                $select_cls = "selected";
            } else {
                $select_cls = "";
            }

            $html_content .= "<option value='" . $i . "' {$select_cls}>" . $i . "</option>";
        }
    }
    //$RackList = json_decode(json_encode($RackList_temp), true);
    //print_r($RackList);exit;
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Device_role = trim($_POST["device_role"]);
    $mfg->Manufacture = trim($_POST["manufacture"]);
    $mfg->Device_type = trim($_POST["device_type"]);
    $mfg->Serial_no = trim($_POST["serial_no"]);
    $mfg->Asset_tag = trim($_POST["asset_tag"]);
    $mfg->Height = trim($_POST["height"]);
    $mfg->Weight = trim($_POST["weight"]);
    $mfg->Wattage = trim($_POST["wattage"]);
    $mfg->No_power = trim($_POST["no_power"]);
    $mfg->No_port = trim($_POST["no_port"]);
    $mfg->Snmp_version = trim($_POST["snmp_version"]);
    $mfg->Community = trim($_POST["community"]);
    $mfg->Failures = trim($_POST["failures"]);
    $mfg->Front_picture = trim($_POST["front_picture"]);
    $mfg->Front_pic = trim($_POST["front_pic"]);
    $mfg->Rear_picture = trim($_POST["rear_picture"]);
    $mfg->Rear_pic = trim($_POST["rear_pic"]);
    $mfg->Site = trim($_POST["site"]);
    $mfg->Rack = trim($_POST["rack"]);
    $mfg->Rack_face = trim($_POST["rack_face"]);
    $mfg->Position = trim($_POST["position"]);
    $mfg->Status = trim($_POST["status"]);
    $mfg->Platform = trim($_POST["platform"]);
    $mfg->Tag = trim($_POST["tag"]);
    $mfg->Comment = trim($_POST["comment"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                if (isset($_GET['from'])) {
                    header('Location: ' . redirect("rack_detail.php?id=" . $_GET['rack_id']));
                } else {
                    header('Location: ' . redirect("device.php?PortID=$mfg->PortID"));
                }
            } else {
                $status = __("Error adding new object");
            }
        } else {
            $status = __("Updated");
            $mfg->UpdateObject();
        }
    }
    //We either just created a manufacturer or updated it so reload from the db
    $mfg->GetOrderByID();
}
$mfgList = $mfg->GetDevice_newList();
$LocationList = $mfg->GetParentLocationList();
//print_r($mfgConnectorList);exit;
?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Device Class Templates</title>
        <!-- Favicon -->
        <link type="image/x-icon" href="images/favicon.ico" rel="shortcut icon" />
        
        <link rel="stylesheet" href="css/inventory.php" type="text/css">
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
        <link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css">

        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css">
        <![endif]-->
        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>

        <style type="text/css">
            #using { margin-top: 1em; }
        </style>

        <script type="text/javascript">
            $(document).ready(function () {
                $('#mform').validationEngine({});
                $('#PortID').change(function (e) {
                    location.href = 'device.php?PortID=' + this.value;
                });
                // Show number of templates using manufacturer
                UpdateCount();

                $('button[name="action"][value="Delete"]').click(DeleteObject);
            });

            function UpdateCount(e) {
                var count;
                $.ajax({
                    type: 'get',
                    async: false,
                    data: {getTemplateCount: $('#PortID').val()},
                    success: function (data) {
                        $('#count').text(data.length);
                        count = data.length;
                    }
                });
                return count;
            }

            function DeleteObject() {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $('#PortID').val(), TransferTo: transferto, action: 'Delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }

        </script>
    </head>
    <body>
<?php include( 'header_dcim.inc.php' ); ?>
        <div class="container">
            <div class="page1">
                <!-- Breadcrumb code start -->
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li><a href="device_list.php">Device</a></li>
                            <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Device</li>
                        </ol>
                    </div>
                </div>
                <!-- Breadcrumb code end -->
                <div class="col-sm-12">

        <?php
        // include( "sidebar.inc.php" );

        echo '<div class="main">
    <div class="">
<h3>', $status, '</h3>
<div class="table-center"><div>
<form id="mform" method="POST" enctype="multipart/form-data">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Device</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">', __("Name"), '</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>', __("New Device"), '</option>';

        foreach ($mfgList as $mfgRow) {
            if ($mfg->PortID == $mfgRow->PortID) {
                $selected = " selected";
            } else {
                $selected = "";
            }
            echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
        }

        echo '	</select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">', __("Name"), '</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="', $mfg->Name, '">
        </div>   
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="device_role">', __("Device Role"), '</label>
        <div class="col-sm-9">  
        <select name="device_role" id="device_role" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
        if ($mfg->Device_role == "Access Switch") {
            $select1 = " selected";
        } else if ($mfg->Device_role == "Console Server") {
            $select2 = " selected";
        } else if ($mfg->Device_role == "Core Switch") {
            $select3 = " selected";
        } else if ($mfg->Device_role == "Distribution Switch") {
            $select4 = " selected";
        } else if ($mfg->Device_role == "Firewall") {
            $select5 = " selected";
        } else if ($mfg->Device_role == "Management Switch") {
            $select6 = " selected";
        } else if ($mfg->Device_role == "PDU") {
            $select7 = " selected";
        } else if ($mfg->Device_role == "Router Switch") {
            $select8 = " selected";
        } else if ($mfg->Device_role == "Server") {
            $select9 = " selected";
        } else {
            $select1 = $select2 = $select3 = $select4 = $select5 = $select6 = $select7 = $select8 = $select9 = "";
        }
        echo '<option value="Access Switch" ' . $select1 . '>', __("Access Switch"), '</option>
            <option value="Console Server" ' . $select2 . '>', __("Console Server"), '</option>
            <option value="Core Switch" ' . $select3 . '>', __("Core Switch"), '</option>
            <option value="Distribution Switch" ' . $select4 . '>', __("Distribution Switch"), '</option>
            <option value="Firewall" ' . $select5 . '>', __("Firewall"), '</option>
            <option value="Management Switch" ' . $select6 . '>', __("Management Switch"), '</option>
            <option value="PDU" ' . $select7 . '>', __("PDU"), '</option>
            <option value="Router" ' . $select8 . '>', __("Router"), '</option>
            <option value="Server" ' . $select9 . '>', __("Server"), '</option>';
        echo '
        </select>
        </div>
    </div>
</div>
</div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Hardware</strong></div>
    <div class="panel-body">
        <div class="form-group">
            <label class="col-sm-3" for="manufacture">', __("Manufacture"), '</label>
            <div class="col-sm-9">      
             <select name="manufacture" id="manufacture" class="form-control">
                <option value="">', __("-- Select --"), '</option>';
        foreach ($ManufactureList as $mfgRow) {
            if ($mfg->Manufacture == $mfgRow->PortID) {
                $selected = " selected";
            } else {
                $selected = "";
            }
            echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
        }
        echo '</select>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
            <label class="col-sm-3" for="device_type">', __("Device Type"), '</label>
            <div class="col-sm-9">      
            <select name="device_type" id="device_type" class="form-control">
                <option value="">', __("-- Select --"), '</option>';
        if ($mfg->Device_type == "2 Post Frame") {
            $select1 = " selected";
        } else if ($mfg->Device_type == "4 Post Frame") {
            $select2 = " selected";
        } else if ($mfg->Device_type == "4 Post Cabinet") {
            $select3 = " selected";
        } else if ($mfg->Device_type == "Wall-mounted Frame") {
            $select4 = " selected";
        } else if ($mfg->Device_type == "Wall-mounted Cabinet") {
            $select5 = " selected";
        } else {
            $select1 = $select2 = $select3 = $select4 = $select5 = "";
        }
        echo '<option value="2 Post Frame" ' . $select1 . '>', __("2 Post Frame"), '</option>
                <option value="4 Post Frame" ' . $select2 . '>', __("4 Post Frame"), '</option>
                <option value="4 Post Cabinet" ' . $select3 . '>', __("4 Post Cabinet"), '</option>
                <option value="Wall-mounted Frame" ' . $select4 . '>', __("Wall-mounted Frame"), '</option>
                <option value="Wall-mounted Cabinet" ' . $select5 . '>', __("Wall-mounted Cabinet"), '</option>
            </select>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="serial_no">', __("Serial No"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="serial_no" id="serial_no" value="', $mfg->Serial_no, '">
               <span>Chassis serial number</span>
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="asset_tag">', __("Asset Tag"), '</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="asset_tag" id="asset_tag" value="', $mfg->Asset_tag, '">
           </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="hegiht">', __("Height"), '</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="height" id="height" value="', $mfg->Height, '">
           </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="weight">', __("Weight"), '</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="weight" id="weight" value="', $mfg->Weight, '">
           </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="wattage">', __("Wattage"), '</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="wattage" id="wattage" value="', $mfg->Wattage, '">
           </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="no_power">', __("No. Power Connections"), '</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="no_power" id="no_power" value="', $mfg->No_power, '">
           </div>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="no_port">', __("No. Ports"), '</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="no_port" id="no_port" value="', $mfg->No_port, '">
           </div>
        </div>
        <div class="clearfix"></div>';
        $rearweb_path = _MEDIA_URL . "devices/{$mfg->Rear_picture}";
        $frontweb_path = _MEDIA_URL . "devices/{$mfg->Front_picture}";
        $rearfilename = _PATH . '/uploads/devices' . DIRECTORY_SEPARATOR . $mfg->Rear_picture;
        $frontfilename = _PATH . '/uploads/devices' . DIRECTORY_SEPARATOR . $mfg->Front_picture;
        
        $front_icon = "";
        $rear_icon = "";
        if (file_exists($rearfilename) && $mfg->Rear_picture != "") {
            $rear_icon = "<a href='" . $rearweb_path . "' target='__blank'>" . $mfg->Rear_picture . " <i class='fa fa-eye'></i></a>";
        }
        if (file_exists($frontfilename) && $mfg->Front_picture) {
            $front_icon = "<a href='" . $frontweb_path . "' target='__blank'>" . $mfg->Front_picture . " <i class='fa fa-eye'></i></a>";
        }
        echo '<div class="form-group">
           <label class="col-sm-3" for="front_picture">', __("Front Picture File"), '</label>
           <div class="col-sm-9">
           <input type="file" class="form-control" name="front_picture" id="front_picture" value="', $mfg->Front_picture, '">
           <output id="filesInfo"></output>
           <input type="hidden" name="front_pic" id="front_pic" value="', $mfg->Front_pic, '">  
           <input type="hidden" name="front_pic_val" id="front_pic_val" value="">  
           </div>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="rear_picture">', __("Rear Picture File"), '</label>
           <div class="col-sm-9">
           <input type="file" class="form-control" name="rear_picture" id="rear_picture" value="', $mfg->Rear_picture, '">
            <output id="rearfilesInfo"></output>   
            <input type="hidden" name="rear_pic" id="rear_pic" value="', $mfg->Rear_pic, '">
            <input type="hidden" name="rear_pic_val" id="rear_pic_val" value="">
           </div>
        </div>';
        if (file_exists($frontfilename) && $mfg->Front_picture) {
            echo '<div class="col-sm-4 col-sm-offset-3"><img src="'.$frontweb_path.'" class="image-responsive" style="height:100px;width:180px;"><br/><h4> Front Picture</h4></div>';
        }
        if (file_exists($rearfilename) && $mfg->Rear_picture != "") {
            echo '<div class="col-sm-5"><img src="'.$rearweb_path.'" class="image-responsive" style="height:100px;width:180px;"><h4> Rear Picture</h4></div>';    
        }
    echo '</div>
</div> 
</div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Location</strong></div>
    <div class="panel-body">
        <div class="form-group">
            <label class="col-sm-3" for="site">', __("Location"), '</label>
            <div class="col-sm-9">      
             <select name="site" id="site" class="form-control" onchange="change_location(this.value)">
                 <option value="">', __("-- Select --"), '</option>';
        foreach ($LocationList as $mfgRow) {
            if ($mfg->Site == $mfgRow->PortID) {
                $selected = " selected";
            } else {
                $selected = "";
            }
            echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
        }
        echo '</select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="rack">', __("Rack"), '</label>
            <div class="col-sm-9">      
             <select name="rack" id="rack" class="form-control" onchange="change_rack(this.value, ', $mfg->Position, ')">
                <option value="">', __("-- Select --"), '</option>';
        foreach ($RackList as $mfgRow) {
            if ($mfg->Rack == $mfgRow->PortID) {
                $selected = " selected";
            } else {
                $selected = "";
            }
            echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
        }
        echo '</select>
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="rack_face">', __("Rack Face"), '</label>
            <div class="col-sm-9">      
            <select name="rack_face" id="rack_face" class="form-control">
                <option value="">', __("-- Select --"), '</option>';
        if ($mfg->Rack_face == "Front") {
            $Front = " selected";
        } else if ($mfg->Rack_face == "Rear") {
            $Rear = " selected";
        } else {
            $Front = $Rear = "";
        }
        echo '<option value="Front" ' . $Front . '>', __("Front"), '</option>
                <option value="Rear" ' . $Rear . '>', __("Rear"), '</option>
            </select>
            
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="position">', __("Position"), '</label>
            <div class="col-sm-9">      
            <select name="position" id="position" class="form-control">
                <option value="">', __("-- Select --"), '</option>
                ', $html_content, '    
            </select>
            <span>The lowest-numbered unit occupied by the device</span>
        </div>
        </div>
    </div>
</div> 
<div class="panel panel-default">
    <div class="panel-heading"><strong>Management</strong></div>
    <div class="panel-body">
        <div class="form-group">
            <label class="col-sm-3" for="status">', __("Status"), '</label>
            <div class="col-sm-9">      
            <select name="status" id="status" class="form-control">
                 <option value="">', __("-- Select --"), '</option>';
        if ($mfg->Status == "Active") {
            $active = " selected";
        } else if ($mfg->Status == "Offline") {
            $offline = " selected";
        } else if ($mfg->Status == "Planned") {
            $planned = " selected";
        } else if ($mfg->Status == "Staged") {
            $staged = " selected";
        } else if ($mfg->Status == "Failed") {
            $failed = " selected";
        } else if ($mfg->Status == "Inventory") {
            $inventory = " selected";
        } else {
            $select1 = $select2 = $select3 = $select4 = $select5 = "";
        }
        echo '<option value="Active" ' . $active . '>', __("Active"), '</option>
                <option value="Offline" ' . $offline . '>', __("Offline"), '</option>
                <option value="Planned" ' . $planned . '>', __("Planned"), '</option>
                <option value="Staged" ' . $staged . '>', __("Staged"), '</option>
                <option value="Failed" ' . $failed . '>', __("Failed"), '</option>
                <option value="Inventory" ' . $inventory . '>', __("Inventory"), '</option>
            </select>
           </div>    
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="platform">', __("Platform"), '</label>
           <div class="col-sm-9">      
           <select name="platform" id="platform" class="form-control">
                 <option value="">', __("-- Select --"), '</option>';
        if ($mfg->Platform == "Arista EOS") {
            $select1 = " selected";
        } else if ($mfg->Platform == "Cisco IOS") {
            $select2 = " selected";
        } else if ($mfg->Platform == "Cisco NXOS") {
            $select3 = " selected";
        } else if ($mfg->Platform == "Juniper Junos") {
            $select4 = " selected";
        } else if ($mfg->Platform == "Linux") {
            $select5 = " selected";
        } else if ($mfg->Platform == "Opengear") {
            $select6 = " selected";
        } else {
            $select1 = $select2 = $select3 = $select4 = $select5 = $select6 = "";
        }
        echo '<option value="Arista EOS" ' . $select1 . '>', __("Arista EOS"), '</option>
                <option value="Cisco IOS" ' . $select2 . '>', __("Cisco IOS"), '</option>
                <option value="Cisco NXOS" ' . $select2 . '>', __("Cisco NXOS"), '</option>
                <option value="Juniper Junos" ' . $select2 . '>', __("Juniper Junos"), '</option>
                <option value="Linux" ' . $select2 . '>', __("Linux"), '</option>
                <option value="Opengear" ' . $select2 . '>', __("Opengear"), '</option>  
             </select>
           </div>    
        </div>
    </div>
</div> 
<div class="panel panel-default">
    <div class="panel-heading"><strong>SNMP Configuration</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="snmp_version">', __("SNMP Version"), '</label>
           <div class="col-sm-9">
           <select name="snmp_version" id="snmp_version" class="form-control">
                 <option value="">', __("-- Select --"), '</option>';
        if ($mfg->Snmp_version == "1") {
            $select1 = " selected";
        } else if ($mfg->Snmp_version == "2") {
            $select2 = " selected";
        } else if ($mfg->Snmp_version == "3") {
            $select3 = " selected";
        } else if ($mfg->Snmp_version == "4") {
            $select4 = " selected";
        } else if ($mfg->Snmp_version == "5") {
            $select5 = " selected";
        } else {
            $select1 = $select2 = $select3 = $select4 = $select5 = $select6 = $select7 = "";
        }
        echo '<option value="1" ' . $select1 . '>', __("1"), '</option>
                 <option value="2" ' . $select2 . '>', __("2"), '</option>
                 <option value="3" ' . $select3 . '>', __("3"), '</option>
                 <option value="4" ' . $select4 . '>', __("4"), '</option>
                 <option value="5" ' . $select5 . '>', __("5"), '</option>
             </select>
            </div>
            <div class="clearfix"></div>
            <div class="form-group">
                <label class="col-sm-3" for="community">', __("SNMP Read Only Community"), '</label>
                <div class="col-sm-9">      
                <input type="text" class="form-control" required name="community" id="community" value="', $mfg->Community, '">
                </div>    
            </div>
            <div class="clearfix"></div>
            <div class="form-group">
                <label class="col-sm-3" for="failures">', __("Consecutive SNMP Failures"), '</label>
                <div class="col-sm-9">      
                <input type="text" class="form-control" required name="failures" id="failures" value="', $mfg->Failures, '">
                </div>    
            </div>
            <span>*Polling is disabled after three consecutive failures.</span>
        </div>
    </div>
</div> 
<div class="panel panel-default">
    <div class="panel-heading"><strong>Tags</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="tag">', __("Tag"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="tag" id="tag" value="', $mfg->Tag, '">
           </div>    
        </div>
    </div>
</div>   
<div class="panel panel-default">
    <div class="panel-heading"><strong>Comments</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="comments">', __("Comments"), '</label>
           <div class="col-sm-9">      
           <textarea class="form-control" name="comment" id="comment">', $mfg->Comment, '</textarea>
           </div>    
        </div>
    </div>
</div>   
<div class="text-center">';
        if ($mfg->PortID > 0) {
            echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">', __("Update"), '</button>';
        } else {
            echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">', __("Create"), '</button>';
        }
        echo '&nbsp;<a href="device_list.php" class="btn-panel btn-success">', __("Cancel"), '</a>';
        ?>
                </div>
            </div>
        </div>
    </div><!-- END div.table -->

</form>
</div></div>
</div><!-- END div.main -->
</div><!-- END div.page -->
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
        // Code Start
        function frontfileSelect(evt) {
            if (window.File && window.FileReader && window.FileList && window.Blob) {
                var files = evt.target.files;

                var result = '';
                var file;
                for (var i = 0; file = files[i]; i++) {
                    // if the file is not an image, continue
                    if (!file.type.match('image.*')) {
                        continue;
                    }

                    reader = new FileReader();
                    reader.onload = (function (tFile) {
                        return function (evt) {
                            var div = document.createElement('div');
                            div.innerHTML = '<img style="width: 50px; height:30px;" src="' + evt.target.result + '" />';
                            $("#front_pic_val").val(evt.target.result);
                            document.getElementById('filesInfo').appendChild(div);
                        };
                    }(file));
                    reader.readAsDataURL(file);
                }
            } else {
                alert('The File APIs are not fully supported in this browser.');
            }
        }
        
        function rearfileSelect(evt) {
            if (window.File && window.FileReader && window.FileList && window.Blob) {
                var files = evt.target.files;

                var result = '';
                var file;
                for (var i = 0; file = files[i]; i++) {
                    // if the file is not an image, continue
                    if (!file.type.match('image.*')) {
                        continue;
                    }

                    reader = new FileReader();
                    reader.onload = (function (tFile) {
                        return function (evt) {
                            var div = document.createElement('div');
                            div.innerHTML = '<img style="width: 50px; height:30px;" src="' + evt.target.result + '" />';
                            $("#rear_pic_val").val(evt.target.result);
                            document.getElementById('rearfilesInfo').appendChild(div);
                        };
                    }(file));
                    reader.readAsDataURL(file);
                }
            } else {
                alert('The File APIs are not fully supported in this browser.');
            }
        }

        document.getElementById('front_picture').addEventListener('change', frontfileSelect, false);
        document.getElementById('rear_picture').addEventListener('change', rearfileSelect, false);
        // Code End
        $('#descending').click(function () {
            if ($(this).prop("checked") == true) {
                $(this).val("Y");
            } else if ($(this).prop("checked") == false) {
                $(this).val("N");
            }
        });
        $('#front_picture').change(function (e) {
            var fileName = e.target.files[0].name;
            $("#front_pic").val(fileName);
        });
        $('#rear_picture').change(function (e) {
            var fileName = e.target.files[0].name;
            $("#rear_pic").val(fileName);
        });
    });
    function change_location(location_id) {

        $.ajax({
            url: 'get_rack.php',
            type: 'post',
            data: {location_id: location_id},
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#rack").html(res.res);
                }
            }
        });
    }
    function change_rack(rack_id, selected) {

        if (typeof selected === "undefined" || selected === null) {
            selected = selected;
        }
        $.ajax({
            url: 'get_positions.php',
            type: 'post',
            data: {rack_id: rack_id, selected: selected},
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#position").html(res.res);
                }
            }
        });
    }
</script>
