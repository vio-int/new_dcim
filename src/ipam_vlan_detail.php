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

$ipamVLAN = new IpamVLAN();
$log = new LogActions();
$ipamPrefix = new IpamPrefix();

$filter = array();
$filter['vlan'] = $_GET['id'];

$ipamVLAN_arr = $ipamVLAN->GetVLANOne($filter);
$ipamVLAN_res = json_decode(json_encode($ipamVLAN_arr), true);
//print_r($ipamVLAN_res);exit;
$filter_2 = array();
$filter_2['sort_on'] = "Time";
$filter_2['sort_by'] = "DESC";
$filter_2['class'] = "IpamVLAN";
$filter_2['object'] = $ipamVLAN_res[0]['PortID'];
$log_arr = $log->GetClassLog($filter_2);
$log_res = json_decode(json_encode($log_arr), true);

$filter_3 = array();
$filter_3['sort_by'] = "Asc";
$filter_3['sort_on'] = "p.id";
$filter_3['vlan'] = $ipamVLAN_res[0]['PortID'];
$ipamPrefix_arr = $ipamPrefix->GetIpamPrefixListRows($filter_3);
$ipamPrefix_res = json_decode(json_encode($ipamPrefix_arr), true);

/* echo "<pre>";
  print_r($log_res);
  echo "</pre>";exit; */

// Delete Code Start
if (isset($_POST['action']) && $_POST["action"] == "Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $ipamVLAN->PortID = $_POST['PortID'];
        if ($ipamVLAN->DeleteObject($_POST["TransferTo"])) {
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
        <?php include('header_ithardware.inc.php'); ?>
        <!-- LISTING CODE START -->
        <div class="container wrapper">
            <div class="main">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_ithardware.php">Dashboard</a></li>
                            <li><a href="ipam_vlan_list.php">VLAN</a></li>
                            <li><?php echo $ipamVLAN_res[0]['Name']; ?></li>
                        </ol>
                    </div>
                </div>
                <div class="pull-right">
                    <a href="ipam_vlan.php?PortID=<?php echo $ipamVLAN_res[0]['PortID'] ?>" class="btn btn-warning">
                        <span class="fa fa-edit" aria-hidden="true"></span>
                        Edit this VLAN
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger" id="delete" data-id="<?php echo $ipamVLAN_res[0]['PortID'] ?>">
                        <span class="fa fa-trash" aria-hidden="true"></span>
                        Delete this VLAN
                    </a>
                </div>
                <h1><?php echo $ipamVLAN_res[0]['Name']; ?></h1>
                <p>
                    <small class="text-muted">Created <?php echo $ipamVLAN_res[0]['Created_at']!="0000-00-00"?date("M. d, Y", strtotime($ipamVLAN_res[0]['Created_at'])):"-"; ?> &middot; Updated <?php echo $ipamVLAN_res[0]['Updated_at']!="0000-00-00"?date("M. d, Y", strtotime($ipamVLAN_res[0]['Updated_at'])):"-"; ?></small>
                </p>
                <ul class="nav nav-tabs" style="margin-bottom: 20px">
                    <li class="tablinks active" onclick="openTab(event, 'VLAN')" >
                        <a href="javascript:void(0);">VLAN</a>
                    </li>
                    <li class="tablinks" onclick="openTab(event, 'Change_log')">
                        <a href="javascript:void(0);">Change log</a>
                    </li>
                </ul>

                <div id="VLAN" class="tabcontent">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>VLAN</strong>
                                </div>
                                <table class="table table-hover panel-body attr-table">
                                    <tr>
                                        <td>Name</td>
                                        <td><?php echo $ipamVLAN_res[0]['Name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Site</td>
                                        <td><?php echo $ipamVLAN_res[0]['Location_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Group</td>
                                        <td><a href="ipam_vlan_group.php?PortID=<?php echo $ipamVLAN_res[0]['Group']; ?>"><?php echo $ipamVLAN_res[0]['Group_name']; ?></a></td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td><?php echo $ipamVLAN_res[0]['Status']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Role</td>
                                        <td><a href="ipam_prefix_role.php?PortID=<?php echo $ipamVLAN_res[0]['Role']; ?>"><?php echo $ipamVLAN_res[0]['Role_name']; ?></a></td>
                                    </tr>
                                    <tr>
                                        <td>Description</td>
                                        <td><span><?php echo $ipamVLAN_res[0]['Description']; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Tags</strong>
                                </div>
                                <div class="panel-body">
                                    <span class="text-muted"><?php echo $ipamVLAN_res[0]['Tag']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>Prefixes</strong>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                    <table class="table table-hover table-headings table-striped">
                                        <tr>
                                            <th>Prefix</th>
                                            <th>Status</th>
                                            <th>VRF</th>
                                            <th>Location</th>
                                            <th>Role</th>
                                            <th>Description</th>
                                        </tr>
                                        <?php if(count($ipamPrefix_res) > 0){
                                            foreach($ipamPrefix_res as $val){?>
                                            <tr>
                                                <td><?php echo $val['Name']; ?></td>
                                                <td><?php echo $val['Status']; ?></td>
                                                <td><?php echo $val['VRFName']; ?></td>
                                                <td><?php echo $val['LocationName']; ?></td>
                                                <td><?php echo $val['RoleName']; ?></td>
                                                <td><?php echo $val['Description']; ?></td>
                                            </tr>
                                        <?php } } else { ?>
                                            <tr><td colspan="6" class="text-center">Record not found!</td></tr>
                                        <?php } ?>
                                    </table>
                                    <div class="pull-right"><a href="ipam_prefix.php" class="btn btn-primary"><i class="fa fa-plus"></i> Add a prefix</a></div>
                                    </div>
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
                                    <?php /* if(count($log_res) > 0){?>
                                    <tfoot>
                                        <tr><td colspan="6" class="text-right">
                                            <?php 
                                                $limit = 15;
                                                $total_rec = $log->GetDashClassLog($filter_2); 
                                                $total_pages = ceil($total_rec['total_logs'] / $limit); 
                                                $pagLink = "<ul class='pagination'><li class='page-item page_go' data-id='1' data-obj='".$ipamVLAN_res[0]['PortID']."'><a class='page-link' href='javascript:void(0);'>&laquo;</a></li>"; 
                                                for ($i=1; $i<=$total_pages; $i++) {
                                                    $selected = "";
                                                    if($_GET['page'] == $i)
                                                    {
                                                        $selected = "active";
                                                    } else if(empty($_GET['page'])){
                                                        $selected = "active";
                                                    }
                                                    $pagLink .= "<li class='page-item page_go' data-id='".$i."' data-obj='".$ipamVLAN_res[0]['PortID']."'><a class='page-link ".$selected."' href='javascript:void(0);'>".$i."</a></li>";	
                                                }
                                                echo $pagLink . "<li class='page-item page_go' data-id='".$total_pages."' data-obj='".$ipamVLAN_res[0]['PortID']."'><a class='page-link' href='javascript:void(0);'>&raquo;</a></li></ul>"; 
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
        $('#delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Delete'}, function (data) {
                    if (data) {
                        location.href = 'ipam_vlan_list.php';
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
                data: {page:page_no, class: "IpamVLAN", object:obj_id},
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