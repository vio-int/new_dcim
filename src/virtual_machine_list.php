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
$sort_on = "a.id";
if($_GET['sort'] != "")
{
    $sort_on = $_GET['sort'];
}
if($_GET['sort_by'] != "")
{
    $sort_by = $_GET['sort_by']=="Asc"?"Desc":"Asc";
}
// END OF CODE

$virtual_machine = new Virtual_machine();
$IpamRole = new IpamPrefixRole();
$Group = new ClusterGroup();
$Cluster = new Cluster();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if($_GET['machine']){
    $filter['machine'] = $_GET['machine'];
}
if($_GET['role']){
    $filter['role'] = $_GET['role'];
}
if($_GET['group']){
    $filter['group'] = $_GET['group'];
}
if($_GET['cluster']){
    $filter['cluster'] = $_GET['cluster'];
}
if($_GET['status']){
    $filter['status'] = $_GET['status'];
}
if($_GET['platform']){
    $filter['platform'] = $_GET['platform'];
}

$virtual_machine_arr = $virtual_machine->GetVirtualMachineListRows($filter);
$virtual_machine_res = json_decode(json_encode($virtual_machine_arr), true);

$role_list_arr = $IpamRole->GetIpamPrefixRoleList();
$role_list_res = json_decode(json_encode($role_list_arr), true); 

$virtual_machine_list_arr = $virtual_machine->GetVirtual_machineList();
$virtual_machine_list_res = json_decode(json_encode($virtual_machine_list_arr), true);

$group_list_arr = $Group->GetClusterGroupList();
$group_list_res = json_decode(json_encode($group_list_arr), true);

$cluster_list_arr = $Cluster->GetClusterList();
$cluster_list_res = json_decode(json_encode($cluster_list_arr), true);

/* echo "<pre>";
print_r($virtual_machine_res);
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
            $virtual_machine->PortID=$_POST['PortID'];
            if($virtual_machine->DeleteObject($_POST["TransferTo"])){
                    $response=true;
            }
    }
    echo json_encode($response);
    exit;
}
// END - AJAX

// Export Code Start
if(isset($_GET['export'])){
    $response = $virtual_machine->ExportReport($filter);
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
    <?php include('header_dcim.inc.php'); ?>
        
    <!-- LISTING CODE START -->
        <div class="container wrapper">
            <div class="main">
            <div class="row">
                <!-- BREADCRUMBS CODE START -->
                <div class="col-sm-9">
                    <ol class="breadcrumb">
                        <li><a href="index_dcim.php">Dashboard</a></li>
                        <li>Virtual Machine</li>
                    </ol>
                    <h1>Virtual Machine</h1>
                </div>
                <div class="col-sm-3">
                    <a href="virtual_machine.php" class="btn btn-primary">
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
                                        <th class="orderable"><a href="?sort=a.name&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=r.name&sort_by=<?= $sort_by ?>">Role</a></th>
                                        <th class="orderable"><a href="?sort=g.name&sort_by=<?= $sort_by ?>">Cluster Group</a></th>
                                        <th class="orderable"><a href="?sort=c.cluster&sort_by=<?= $sort_by ?>">Cluster</a></th>
                                        <th class="orderable"><a href="?sort=a.status&sort_by=<?= $sort_by ?>">Status</a></th>
                                        <th class="orderable"><a href="?sort=a.platform&sort_by=<?= $sort_by ?>">Platform</a></th>
                                        <th class="orderable"><a href="?sort=a.vcpus&sort_by=<?= $sort_by ?>">VCPUs</a></th>
                                        <th class="orderable"><a href="?sort=a.memory&sort_by=<?= $sort_by ?>">Memory</a></th>
                                        <th class="orderable"><a href="?sort=a.disk&sort_by=<?= $sort_by ?>">Disk</a></th>
                                        <th class="primary_ip">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($virtual_machine_res) > 0){
                                        foreach($virtual_machine_res as $res) { ?>
                                    <tr>
                                        <td><?php echo '<a href="virtual_machine_detail.php?id='.$res['PortID'].'">'.$res['Name'].'</a>'; ?></td>
                                        <td><?php echo $res['Role_name'] ?></td>
                                        <td><?php echo $res['Group_name'] ?></td>
                                        <td><?php echo $res['Cluster_name']; ?></td>
                                        <td><?php echo $res['Status'] ?></td>
                                        <td><?php echo $res['Platform'] ?></td>
                                        <td><?php echo $res['Vcpus'] ?></td>
                                        <td><?php echo $res['Memory'] ?></td>
                                        <td><?php echo $res['Disk']; ?></td>
                                        <td><a href="virtual_machine.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                            <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="10">Record not found!</td></tr> 
                                    <?php } ?>
                                </tbody>
                                <?php if(count($virtual_machine_res) > 0){?>
                                <tfoot>
                                    <tr><td colspan="10" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            $total_rec = $virtual_machine->GetDashVirtual_machineList(); 
                                            $total_pages = ceil($total_rec['total_machine'] / $limit); 
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
                    <div class="clearfix"></div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="fa fa-search" aria-hidden="true"></span>
                            <strong>Search</strong>
                        </div>
                        <div class="panel-body">
                            <form action="virtual_machine_list.php" method="get" class="form">
                                <div class="form-group">
                                    <label for="">Machine</label>
                                    <select name="machine" placeholder="Machine" id="machine" class="form-control">
                                        <option value="">-- Select --</option>
                                        <?php foreach($virtual_machine_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['machine']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_role">Role</label>
                                    <select name="role" placeholder="None" id="role" class="form-control">
                                        <option value="">-- Select --</option>
                                        <?php foreach($role_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['role']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_group">Cluster Group</label>
                                    <select name="group" placeholder="None" id="group" class="form-control">
                                        <option value="">-- Select --</option>
                                        <?php foreach($group_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['group']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_cluster">Cluster</label>
                                    <select name="cluster" placeholder="None" id="cluster" class="form-control">
                                        <option value="">-- Select --</option>
                                        <?php foreach($cluster_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['cluster']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_status">Status</label>
                                    <select name="status" placeholder="None" id="status" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="Active"  <?php echo $_GET['status']=="Active" ?'selected':''?>>Active</option>
                                        <option value="Offline"  <?php echo $_GET['status']=="Offline" ?'selected':''?>>Offline</option>
                                        <option value="Staged"  <?php echo $_GET['status']=="Staged" ?'selected':''?>>Staged</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_platform">Platform</label>
                                    <select name="platform" placeholder="None" id="platform" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="Arista EOS" <?php echo $_GET['platform']=="Arista EOS" ?'selected':''?>>Arista EOS</option>
                                        <option value="Cisco IOS" <?php echo $_GET['platform']=="Cisco IOS" ?'selected':''?>>Cisco IOS</option>
                                        <option value="Cisco NXOS" <?php echo $_GET['platform']=="Cisco NXOS" ?'selected':''?>>Cisco NXOS</option>
                                        <option value="Juniper Junos" <?php echo $_GET['platform']=="Juniper Junos" ?'selected':''?>>Juniper Junos</option>
                                        <option value="Linux" <?php echo $_GET['platform']=="Linux" ?'selected':''?>>Linux</option>
                                        <option value="Opengear" <?php echo $_GET['platform']=="Opengear" ?'selected':''?>>Opengear</option>  
                                    </select>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fa fa-search" aria-hidden="true"></span> Apply
                                    </button>
                                    <a href="virtual_machine_list.php" class="btn btn-default" style="line-height:1 !important">
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