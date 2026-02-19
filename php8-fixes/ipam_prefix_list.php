<?php
# catch for assholes that don't read the install instructions
if (!file_exists("db.inc.php")) {
    require_once( "preflight.inc.php" );
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

// SORTING CODE 
$sort_by = "Asc";
$sort_on = "p.id";
if($_GET['sort'] != "")
{
    $sort_on = $_GET['sort'];
}
if($_GET['sort_by'] != "")
{
    $sort_by = $_GET['sort_by']=="Asc"?"Desc":"Asc";
}
// END OF CODE

$prefix = new IpamPrefix();
$IpamVRF = new IpamVRF();
$role = new IpamPrefixRole();
$location = new Location();
$group = new IpamVLANGroup();
$vlan = new IpamVLAN();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if($_GET['prefix']){
    $filter['prefix'] = $_GET['prefix'];
}
if($_GET['vrf']){
    $filter['vrf'] = $_GET['vrf'];
}
if($_GET['role']){
    $filter['role'] = $_GET['role'];
}
if($_GET['location']){
    $filter['location'] = $_GET['location'];
}
if($_GET['group']){
    $filter['group'] = $_GET['group'];
}
if($_GET['vlan']){
    $filter['vlan'] = $_GET['vlan'];
}

$prefix_arr = $prefix->GetIpamPrefixListRows($filter);
$prefix_res = json_decode(json_encode($prefix_arr), true);
 
$prefix_list_arr = $prefix->GetIpamPrefixList();
$prefix_list_res = json_decode(json_encode($prefix_list_arr), true); 

$vrf_list_arr = $IpamVRF->GetIpamVRFList();
$vrf_list_res = json_decode(json_encode($vrf_list_arr), true); 

$role_list_arr = $role->GetIpamPrefixRoleList();
$role_list_res = json_decode(json_encode($role_list_arr), true); 

$location_list_arr = $location->GetLocationList();
$location_list_res = json_decode(json_encode($location_list_arr), true); 

$group_list_arr = $group->GetIpamVLANGroupList();
$group_list_res = json_decode(json_encode($group_list_arr), true); 

$vlan_list_arr = $vlan->GetIpamVLANList();
$vlan_list_res = json_decode(json_encode($vlan_list_arr), true); 
/* echo "<pre>";
print_r($prefix_res);
echo "<pre/>";exit; */

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
// Delete Code Start
if(isset($_POST['action']) && $_POST["action"]=="Delete"){
    header('Content-Type: application/json');
    $response=false;
    if(isset($_POST["TransferTo"])){
            $prefix->PortID=$_POST['PortID'];
            if($prefix->DeleteObject($_POST["TransferTo"])){
                    $response=true;
            }
    }
    echo json_encode($response);
    exit;
}
// END - AJAX
// Export Code Start
if(isset($_GET['export'])){
    $response = $prefix->ExportReport($filter);
    exit;
}
// Export Code End
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
                <!-- BREADCRUMBS CODE START -->
                <div class="col-sm-9">
                    <ol class="breadcrumb">
                        <li><a href="index_ithardware.php">Dashboard</a></li>
                        <li>Prefix</li>
                    </ol>
                    <h1>Prefix</h1>
                </div>
                <div class="col-sm-3">
                    <a href="ipam_prefix.php" class="btn btn-primary">
                        <span class="fa fa-plus" aria-hidden="true"></span> Add
                    </a>
                    <a href="javascript:void(0);" class="btn btn-info">
                        <span class="fa fa-download" aria-hidden="true"></span> Import
                    </a>
                    <a href="<?php echo $mark."export=xls"; ?>" class="btn btn-success">
                        <span class="fa fa-upload" aria-hidden="true"></span> Export
                    </a>
                </div>
                <!-- END OF BREADCRUMBS CODE -->
                <div class="col-md-9">
                    <div class="table-responsive">
                            <table class="table table-hover table-headings table-striped">
                                <thead>
                                    <tr>
                                        <!-- <th class="pk"><input type="checkbox" class="toggle" title="Toggle all" /></th>-->
                                        <th class="orderable"><a href="?sort=p.prefix&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=p.status&sort_by=<?= $sort_by ?>">Status</a></th>
                                        <th class="orderable"><a href="?sort=v.name&sort_by=<?= $sort_by ?>">VRF</a></th>
                                        <th class="orderable"><a href="?sort=pr.name&sort_by=<?= $sort_by ?>">Role</a></th>
                                        <th class="orderable"><a href="?sort=p.description&sort_by=<?= $sort_by ?>">Description</a></th>
                                        <th class="orderable"><a href="?sort=l.name&sort_by=<?= $sort_by ?>">Location</a></th>
                                        <th class="orderable"><a href="?sort=vg.name&sort_by=<?= $sort_by ?>">Group</a></th>
                                        <th class="orderable"><a href="?sort=vl.name&sort_by=<?= $sort_by ?>">VLAN</a></th>
                                        <th class="primary_ip">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($prefix_res) > 0){
                                        foreach($prefix_res as $res) { ?>
                                    <tr>
                                        <td><?php echo '<a href="ipam_prefix_detail.php?id='.$res['PortID'].'">'.$res['Name'].'</a>'; ?></td>
                                        <td><?php echo $res['Status']; ?></td>
                                        <td><?php echo $res['VRFName']; ?></td>
                                        <td><?php echo $res['RoleName']; ?></td>
                                        <td><?php echo $res['Description']; ?></td>
                                        <td><?php echo $res['LocationName']; ?></td>
                                        <td><?php echo $res['GroupName']; ?></td>
                                        <td><?php echo $res['VlanName']; ?></td>
                                        <td><a href="ipam_prefix.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                            <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="9">Record not found!</td></tr> 
                                    <?php } ?>
                                </tbody>
                                <?php if(count($prefix_res) > 0){ ?>
                                <tfoot>
                                    <tr><td colspan="9" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            
                                            $total_rec = $prefix->GetDashIpamPrefixList(); 
                                            $total_pages = ceil($total_rec['total_prefix'] / $limit); 
                                            $pagLink = "<ul class='pagination'><li class='page-item'><a class='page-link' href='".$mark."page=1'>&laquo;</a></li>"; 
                                            for ($i=1; $i<=$total_pages; $i++) {
                                            $selected = "";
                                            if($_GET['page'] == $i)
                                            {
                                                $selected = "active";
                                            } else if(empty($_GET['page'])){
                                                $selected = "active";
                                            }
                                            $pagLink .= "<li class='page-item'><a class='page-link ".$selected."' href='".$mark."page=".$i."'>".$i."</a></li>";	
                                            }
                                            echo $pagLink . "<li class='page-item'><a class='page-link' href='".$mark."page=".$total_pages."'>&raquo;</a></li></ul>"; 
                                        ?>
                                    </td></tr>
                                </tfoot>
                                <?php } ?>
                            </table>
                        </div>
                        
                        <?php /* <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add Components <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="/dcim/devices/console-ports/add/" class="formaction">Console Ports</a></li>
                                <li><a href="/dcim/devices/console-server-ports/add/" class="formaction">Console Server Ports</a></li>
                                <li><a href="/dcim/devices/power-ports/add/" class="formaction">Power Ports</a></li>
                                <li><a href="/dcim/devices/power-outlets/add/" class="formaction">Power Outlets</a></li>
                                <li><a href="/dcim/devices/interfaces/add/" class="formaction">Interfaces</a></li>
                                <li><a href="/dcim/devices/device-bays/add/" class="formaction">Device Bays</a></li>
                            </ul>
                        </div>
                        <button type="submit" name="_edit" formaction="/dcim/virtual-chassis/add/" class="btn btn-primary btn-sm">
                            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Create Virtual Chassis
                        </button>
                        <button type="submit" name="_edit" formaction="/dcim/devices/edit/" class="btn btn-warning btn-sm">
                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Edit Selected
                        </button>
                        <button type="submit" name="_delete" formaction="/dcim/devices/delete/" class="btn btn-danger btn-sm">
                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete Selected
                        </button> */ ?>
                    
                    <div class="clearfix"></div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="fa fa-search" aria-hidden="true"></span>
                            <strong>Search</strong>
                        </div>
                        <div class="panel-body">
                            <form action="ipam_prefix_list.php" method="get" class="form">
                                <div class="form-group">
                                    <label for="">Prefix</label>
                                    <select name="prefix" placeholder="Prefix" size="3" multiple="multiple" id="id_prefix" class="form-control">
                                        <?php foreach($prefix_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['prefix']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_vrf">VRF</label>
                                    <select name="vrf" placeholder="None" size="3" multiple="multiple" id="id_vrf" class="form-control">
                                        <?php foreach($vrf_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['vrf']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_role">Role</label>
                                    <select name="role" placeholder="None" size="3" multiple="multiple" id="id_role" class="form-control">
                                        <?php foreach($role_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['role']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_location">Location</label>
                                    <select name="location" placeholder="None" size="3" multiple="multiple" id="id_location" class="form-control">
                                        <?php foreach($location_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['location']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_group">Group</label>
                                    <select name="group" placeholder="None" size="3" multiple="multiple" id="id_group" class="form-control">
                                        <?php foreach($group_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['group']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_vlan">Vlan</label>
                                    <select name="vlan" placeholder="None" size="3" multiple="multiple" id="id_vlan" class="form-control">
                                        <?php foreach($vlan_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['vlan']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fa fa-search" aria-hidden="true"></span> Apply
                                    </button>
                                    <a href="ipam_prefix_list.php" class="btn btn-default" style="line-height:1 !important">
                                        <span class="fa fa-remove" aria-hidden="true"></span> Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="push"></div>
            </div>
        </div>
    <!-- LISTING CODE END -->
    </body>
    <!-- Footer -->
    <?php if($footer_text!=""){?>
        <footer class="page-footer font-small footer">
            <spam><?php echo $footer_text; ?></spam>
        </footer>
    <?php } ?>
    <!-- Footer -->
</html>
<script type="text/javascript">
    $(document).ready(function() {
        $('.delete').click(function(){
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto=(typeof(objectid)=='undefined')?0:objectid;
                $.post('',{PortID: $(this).data("id"), TransferTo: transferto, action: 'Delete'},function(data){
                    if(data){
                            location.href='';
                    }else{
                            alert("Something's gone horrible wrong");
                    }
                });
            }
        });
    });
</script>