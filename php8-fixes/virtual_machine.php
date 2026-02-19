<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Virtal Machine");
$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

$mfg = new Virtual_machine();
$Role = new IpamPrefixRole();
$Group = new ClusterGroup();
$Cluster = new Cluster();

$RoleList = $Role->GetIpamPrefixRoleList();
$Cluster_group_list = $Group->GetClusterGroupList();
$Cluster_list = $Cluster->GetClusterList();

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
    
    $Cluster_list = $Cluster->GetGroupClusterList($mfg->Group);
    //print_r($mfg);exit;
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Role = trim($_POST["role"]);
    $mfg->Group = trim($_POST["cluster_group"]);
    $mfg->Cluster = trim($_POST["cluster"]);
    $mfg->Status = $_POST["status"];
    $mfg->Platform = trim($_POST["platform"]);
    $mfg->Ipv_4 = trim($_POST["ipv_4"]);
    $mfg->Ipv_6 = trim($_POST["ipv_6"]);
    $mfg->Vcpus = trim($_POST["vcpus"]);
    $mfg->Memory = $_POST["memory"];
    $mfg->Disk = trim($_POST["disk"]);
    $mfg->Context = trim($_POST["context"]);
    $mfg->Comment = trim($_POST["comment"]);
    $mfg->Tag = trim($_POST["tag"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("virtual_machine.php?PortID=$mfg->PortID"));
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
$mfgList = $mfg->GetVirtual_machineList();
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
        <link rel="stylesheet" href="css/datepicker.css" type="text/css">
        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css">
        <![endif]-->
        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>
        <script type="text/javascript" src="scripts/bootstrap-datepicker.js"></script>

        <style type="text/css">
            #using { margin-top: 1em; }
        </style>

        <script type="text/javascript">
            $(document).ready(function () {
                $('#date_added').datepicker({
                    format: 'm/d/yyyy',
                    autoclose: true
                });
                $('#mform').validationEngine({});
                $('#PortID').change(function (e) {
                    location.href = 'virtual_machine.php?PortID=' + this.value;
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
                            <li><a href="virtual_machine_list.php">Virtual Machine</a></li>
                            <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Virtual Machine</li>
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
<form id="mform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Virtual Machine</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">', __("Name"), '</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>', __("New Virtual Machine"), '</option>';

                    foreach ($mfgList as $mfgRow) {
                        if ($mfg->PortID == $mfgRow->PortID) {
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
       <label class="col-sm-3" for="name">', __("Name"), '</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="', $mfg->Name, '">
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="role">', __("Role"), '</label>
       <div class="col-sm-9">  
        <select name="role" id="role" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
                    foreach ($RoleList as $mfgRow) {
                        if ($mfg->Role == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                    }
                    echo '
        </select>
       </div>    
    </div>
</div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Cluster</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="cluster_group">', __("Cluster Group"), '</label>
       <div class="col-sm-9">  
        <select name="cluster_group" id="cluster_group" class="form-control" onchange="change_group(this.value)">
            <option value="">', __("-- Select --"), '</option>';
                    foreach ($Cluster_group_list as $mfgRow) {
                        if ($mfg->Group == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                    }
                    echo '
        </select>
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="cluster">', __("Cluster"), '</label>
       <div class="col-sm-9">  
        <select name="cluster" id="cluster" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
                    foreach ($Cluster_list as $mfgRow) {
                        if ($mfg->Cluster == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                    }
                    echo '
        </select>
       </div>    
    </div>
</div>
</div>
<div class="clearfix"></div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Management</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="status">', __("Status"), '</label>
       <div class="col-sm-9">  
        <select name="status" id="status" class="form-control">
            <option value="">', __("-- Select --"), '</option>
            <option value="Active" ', $mfg->Status == "Active" ? "Selected" : "", '>Active</option>    
            <option value="Offline" ', $mfg->Status == "Offline" ? 'selected' : '', '>Offline</option>    
            <option value="Staged" ', $mfg->Status == "Staged" ? 'selected' : '', '>Staged</option>    
        </select>
       </div>    
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="platform">', __("Platform"), '</label>
        <div class="col-sm-9">      
        <select name="platform" id="platform" class="form-control">
            <option value="">', __("-- Select --"), '</option>
            <option value="Arista EOS" ', $mfg->Platform == "Arista EOS" ? "Selected" : "", '>', __("Arista EOS"), '</option>
            <option value="Cisco IOS" ', $mfg->Platform == "Cisco IOS" ? "Selected" : "", '>', __("Cisco IOS"), '</option>
            <option value="Cisco NXOS" ', $mfg->Platform == "Cisco NXOS" ? "Selected" : "", '>', __("Cisco NXOS"), '</option>
            <option value="Juniper Junos" ', $mfg->Platform == "Juniper Junos" ? "Selected" : "", '>', __("Juniper Junos"), '</option>
            <option value="Linux" ', $mfg->Platform == "Linux" ? "Selected" : "", '>', __("Linux"), '</option>
            <option value="Opengear" ', $mfg->Platform == "Opengear" ? "Selected" : "", '>', __("Opengear"), '</option>  
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="Primary IPv4">', __("Primary IPv4"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="ipv_4" id="ipv_4" value="', $mfg->Ipv_4, '">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="ipv_6">', __("Primary Ipv6"), '</label>
       <div class="col-sm-9">
       <input type="text" class="form-control" name="ipv_6" id="ipv_6" value="', $mfg->Ipv_6, '">
       </div>
    </div>
</div> 
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Resources</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="vcpus">', __("VCPUs"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="vcpus" id="vcpus" value="', $mfg->Vcpus,'">
           </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="memory">', __("Memory (MB)"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="memory" id="memory" value="', $mfg->Memory,'">
           </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="disk">', __("Disk (GB)"), '</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="disk" id="disk" value="', $mfg->Disk, '">
           </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Local Config Context Data</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <div class="col-sm-12">      
           <textarea class="form-control" name="context" id="context">', $mfg->Context, '</textarea>
           </div>    
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Tag</strong></div>
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
           <div class="col-sm-12">  
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
                    echo '&nbsp;<a href="virtual_machine_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
                    ?>
                </div>
            </div>
        </div>
    </div><!-- END div.table -->

</form>
</div></div>
<?php echo '
<!-- hiding modal dialogs here so they can be translated easily -->
<div class="hide">
	<div title="', __("Port comptilibity delete confirmation"), '" id="deletemodal">
		<div id="modaltext"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-alert"></span>', __("Are you sure that you want to delete this Port Comptilibity?"), '
		</div>
	</div>
	<div title="', __("Are you REALLY sure?"), '" id="doublecheck">
		<div id="modaltext" class="warning"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-alert"></span>', __("Are you sure REALLY sure?  There is no undo!!"), '
		<br><br>
		</div>
	</div>
</div>'; ?>
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
$(document).ready(function(){
    
});
function change_group(group_id) {

    $.ajax({
        url: 'get_cluster.php',
        type: 'post',
        data: {group_id: group_id},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#cluster").html(res.res);
            }
        }
    });
}
</script>
