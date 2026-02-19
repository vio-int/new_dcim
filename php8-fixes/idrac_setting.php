<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Port Compatibility Listing");
$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

$mfg = new IdracSetting();
$device = new Device_new();

$breadcrumb_arr = array();
$breadcrumb_arr[0]['title'] = "Dashboard";
$breadcrumb_arr[0]['link'] = "index_dcim.php";
$breadcrumb_arr[1]['title'] = "Device";
$breadcrumb_arr[1]['link'] = "device_list.php";

if(isset($_GET['device_id']) && $_GET['device_id']!= "") {
    $filter['device'] = $_GET['device_id'];
    $get_name = $device->GetDeviceOne($filter);
    
    $breadcrumb_arr[2]['title'] = $get_name[0]->Name;
    $breadcrumb_arr[2]['link'] = "device_detail.php?id=".$_GET['device_id'];
    $breadcrumb_arr[3]['title'] = "iDRAC Settings";
    $breadcrumb_arr[3]['link'] = "javacript:void(0);";
    
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
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Device = trim($_POST["device"]);
    $mfg->Nic_selection = trim($_POST["nic_selection"]);
    $mfg->Is_enable_ipv4 = trim($_POST["is_enable_ipv4"]);
    $mfg->Is_enable_dhcp = trim($_POST["is_enable_dhcp"]);
    $mfg->Mac_address = trim($_POST["mac_address"]);
    $mfg->Static_ip_address = trim($_POST["static_ip_address"]);
    $mfg->Static_gateway = trim($_POST["static_gateway"]);
    $mfg->Static_subnet_mask = trim($_POST["static_subnet_mask"]);
    
    if ($mfg->Device != null && $mfg->Device != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("idrac_setting.php?PortID=$mfg->PortID&device_id=".$_GET['device_id'].""));
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

$mfgList = $mfg->GetIdracSettingList();
$DeviceList = $device->GetDevice_newList();
//int_r($mfgList);exit;

?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Device Class Templates</title>
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
                    device_id = '<?php echo $_GET['device_id'] ?>';
                    location.href = 'idrac_setting.php?PortID=' + this.value +'&device_id=' + device_id;
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
                
                <!-- BREADCRUMBS CODE START -->
                <?php 
                $breadcrumb_html = "";
                foreach($breadcrumb_arr as $val) { 
                    $breadcrumb_html .='<li><a href="'.$val['link'].'">'.$val['title'].'</a></li>';
                } ?>
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <?php echo $breadcrumb_html; ?>
                    </ol>
                    <h1>iDRAC Settings</h1>
                </div>

                <!-- END OF BREADCRUMBS CODE -->
                
                <div class="col-sm-12">

                    <?php
                    // include( "sidebar.inc.php" );

                    echo '<div class="main">
    <div class="">
<h3>', $status, '</h3>
<div class="table-center"><div>
<form id="mform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>iDRAC Settings</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="name">', __("Setting"), '</label>
       <div class="col-sm-9">        
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>', __("iDRAC setting"), '</option>';

                    foreach ($mfgList as $mfgRow) {
                        if ($mfg->PortID == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name ($mfgRow->Device_name)</option>\n";
                    }

                    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="device">', __("Device"), '</label>
       <div class="col-sm-9">        
       <select name="device" id="device" class="form-control">
       <option value="">', __("-- Select --"), '</option>';
                    foreach ($DeviceList as $mfgRow) {
                        if ($mfg->Device == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name </option>\n";
                    }
                    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="mac_address">', __("Mac Address"), '</label>
       <div class="col-sm-9">
       <input type="text" class="form-control" name="mac_address" id="mac_address" value="', $mfg->Mac_address, '">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">', __("NIC Selection"), '</label>
       <div class="col-sm-9 inline-group">    
        <input type="radio" class="" name="nic_selection" id="nic_selection_Y" value="Y" ',$mfg->Nic_selection=="Y"?"checked":"",'>
        <label for="Nic_selection">',__("Enabled"),'</label>
        <input type="radio" class="" name="nic_selection" id="nic_selection_N" value="N" ',$mfg->Nic_selection=="N"?"checked":"",'>
        <label for="Nic_selection">',__("Disabled"),'</label>
       </div>    
    </div>';
    /* <div class="form-group">
       <label class="col-sm-3" for="name">', __("Enable IPv4"), '</label>
       <div class="col-sm-9 inline-group">    
        <input type="radio" class="" name="is_enable_ipv4" id="is_enable_ipv4_Y" value="Y" ',$mfg->Is_enable_ipv4=="Y"?"checked":"",'>
        <label for="is_enable_ipv4_e">',__("Enabled"),'</label>
        <input type="radio" class="" name="is_enable_ipv4" id="is_enable_ipv4_N" value="N" ',$mfg->Is_enable_ipv4=="N"?"checked":"",'>
        <label for="is_enable_ipv4_d">',__("Disabled"),'</label>
       </div>    
    </div> */
    echo '<div class="form-group">
       <label class="col-sm-3" for="name">', __("Enable DHCP"), '</label>
       <div class="col-sm-9 inline-group">    
        <input type="radio" class="" name="is_enable_dhcp" id="is_enable_dhcp_Y" value="Y" ',$mfg->Is_enable_dhcp=="Y"?"checked":"",'>
        <label for="Is_enable_dhcp_e">',__("Enabled"),'</label>
        <input type="radio" class="" name="is_enable_dhcp" id="is_enable_dhcp_N" value="N" ',$mfg->Is_enable_dhcp=="N"?"checked":"",'>
        <label for="Is_enable_dhcp_d">',__("Disabled"),'</label>
       </div>    
    </div>
    
    <div id="ipv_div" style="display:',$mfg->Is_enable_dhcp=="Y"?"none":"block",'">
    <div class="form-group">
       <label class="col-sm-3" for="static_ip_address">', __("Static IP Address"), '</label>
       <div class="col-sm-9">
       <input type="text" class="form-control" name="static_ip_address" id="static_ip_address" value="', $mfg->Static_ip_address, '" ',$mfg->Is_enable_dhcp=="Y"?"required":"",'>
       </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="static_gateway">', __("Static Gateway"), '</label>
       <div class="col-sm-9">
       <input type="text" class="form-control" name="static_gateway" id="static_gateway" value="', $mfg->Static_gateway, '" ',$mfg->Is_enable_dhcp=="Y"?"required":"",'>
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="static_subnet_mask">', __("Static Subnet Mask"), '</label>
       <div class="col-sm-9">
       <input type="text" class="form-control" name="static_subnet_mask" id="static_subnet_mask" value="', $mfg->Static_subnet_mask, '" ',$mfg->Is_enable_dhcp=="Y"?"required":"",'>
       </div>    
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
        $("#is_enable_dhcp_Y").click(function(){
           $("#ipv_div").fadeOut("slow"); 
           $("#static_ip_address").prop('required',false);
           $("#static_gateway").prop('required',false);
           $("#static_subnet_mask").prop('required',false);
        });
        $("#is_enable_dhcp_N").click(function(){
           $("#ipv_div").fadeIn("slow"); 
           $("#static_ip_address").prop('required',true);
           $("#static_gateway").prop('required',true);
           $("#static_subnet_mask").prop('required',true);
        });
    });
</script>
