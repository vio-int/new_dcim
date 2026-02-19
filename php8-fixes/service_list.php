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
$sort_on = "s.id";
if($_GET['sort'] != "")
{
    $sort_on = $_GET['sort'];
}
if($_GET['sort_by'] != "")
{
    $sort_by = $_GET['sort_by']=="Asc"?"Desc":"Asc";
}
// END OF CODE

$Service = new Service();
$machine = new Virtual_machine();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if($_GET['name']){
    $filter['name'] = $_GET['name'];
}
if($_GET['machine']){
    $filter['machine'] = $_GET['machine'];
}
if($_GET['port_type']){
    $filter['port_type'] = $_GET['port_type'];
}

$Service_arr = $Service->GetServiceListRows($filter);
$Service_res = json_decode(json_encode($Service_arr), true);

$Service_list_arr = $Service->GetServiceList();
$Service_list_res = json_decode(json_encode($Service_list_arr), true);

$machine_list_arr = $machine->GetVirtual_machineList();
$machine_list_res = json_decode(json_encode($machine_list_arr), true); 


/* echo "<pre>";
print_r($Service_res);
echo "<pre/>";exit; */

// Delete Code Start
if(isset($_POST['action']) && $_POST["action"]=="Delete"){
    header('Content-Type: application/json');
    $response=false;
    if(isset($_POST["TransferTo"])){
        $Service->PortID=$_POST['PortID'];
        if($Service->DeleteObject($_POST["TransferTo"])){
            $response=true;
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
// Export Code Start
if(isset($_GET['export'])){
    $response = $Service->ExportReport($filter);
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
                        <li>Service</li>
                    </ol>
                    <h1>Service</h1>
                </div>
                <div class="col-sm-3">
                    <a href="service_add.php" class="btn btn-primary">
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
                                        <th class="orderable"><a href="?sort=v.name&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=v.status&sort_by=<?= $sort_by ?>">Parent</a></th>
                                        <th class="orderable"><a href="?sort=l.name&sort_by=<?= $sort_by ?>">Protocol</a></th>
                                        <th class="orderable"><a href="?sort=g.name&sort_by=<?= $sort_by ?>">Port Number</a></th>
                                        <th class="orderable"><a href="?sort=v.description&sort_by=<?= $sort_by ?>">Description</a></th>
                                        <th class="primary_ip">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($Service_res) > 0){
                                        foreach($Service_res as $res) { ?>
                                    <tr>
                                        <td><?php echo '<a href="service_add.php?PortID='.$res['PortID'].'">'.$res['Name'].'</a>'; ?></td>
                                        <td><?php echo $res['Machine_name'] ?></td>
                                        <td><?php echo $res['Port_type'] ?></td>
                                        <td><?php echo $res['Port']; ?></td>
                                        <td><?php echo $res['Description'] ?></td>
                                        <td><a href="service_add.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                        <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a></td>
                                    </tr>    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="7">Record not found!</td></tr> 
                                    <?php } ?>
                                </tbody>
                                <?php if(count($Service_res) > 0){ ?>
                                <tfoot>
                                    <tr><td colspan="7" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            
                                            $total_rec = $Service->GetDashServiceList(); 
                                            $total_pages = ceil($total_rec['total_service'] / $limit); 
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
                        <?php /* <div class="paginator pull-right">
                            <div class="text-right text-muted">
                                Showing 1-1 of 1
                            </div>
                        </div> */ ?>
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
                            <form action="service_list.php" method="get" class="form">
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <select name="name" placeholder="Name" size="6" multiple="multiple" id="id_name" class="form-control">
                                        <?php foreach($Service_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['name']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="machine">Virtual Machine</label>
                                    <select name="machine" placeholder="None" size="3" multiple="multiple" id="machine" class="form-control">
                                        <?php foreach($machine_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['machine']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_port_type">Protocol</label>
                                    <select name="port_type" id="port_type" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="TCP" <?php echo $_GET['port_type']=="TCP"?'Selected':''?>>TCP</option>
                                        <option value="UDP" <?php echo $_GET['port_type']=="UDP"?'Selected':''?>>UDP</option>
                                    </select>
                                </div>
                                
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fa fa-search" aria-hidden="true"></span> Apply
                                    </button>
                                    <a href="service_list.php" class="btn btn-default" style="line-height:1 !important">
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