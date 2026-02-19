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
$sort_on = "id";
if($_GET['sort'] != "")
{
    $sort_on = $_GET['sort'];
}
if($_GET['sort_by'] != "")
{
    $sort_by = $_GET['sort_by']=="Asc"?"Desc":"Asc";
}
// END OF CODE

$location = new Location();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if($_GET['location']){
    $filter['location'] = $_GET['location'];
}
if($_GET['status']){
    $filter['status'] = $_GET['status'];
}
if($_GET['time_zone']){
    $filter['time_zone'] = $_GET['time_zone'];
}
if($_GET['contact_name']){
    $filter['contact_name'] = $_GET['contact_name'];
}
if($_GET['contact_email']){
    $filter['contact_email'] = $_GET['contact_email'];
}

$location_arr = $location->GetLocationListRow($filter);
$location_res = json_decode(json_encode($location_arr), true); 

$location_list_arr = $location->GetLocationList();
$location_list_res = json_decode(json_encode($location_list_arr), true); 

/* echo "<pre>";
print_r($location_res);
echo "<pre/>";exit; */

// Delete Code Start
if(isset($_POST['action']) && $_POST["action"]=="Delete"){
    header('Content-Type: application/json');
    $response=false;
    if(isset($_POST["TransferTo"])){
        $location->PortID=$_POST['PortID'];
        if($location->DeleteObject($_POST["TransferTo"])){
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
    $response = $location->ExportReport($filter);
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
                        <li>Location</li>
                    </ol>
                    <h1>Location</h1>
                </div>
                <div class="col-sm-3">
                    <a href="location.php" class="btn btn-primary">
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
                    <form method="post" class="form form-horizontal">
                        <input type='hidden' name='csrfmiddlewaretoken' value='F41o5QABdEj57NuuDw99JbDmpBNTfPycDWUELXDv13Pm5wRaYlvBUrXYFEiw34aS' />
                        <input type="hidden" name="return_url" value="/dcim/devices/" />
                        <div class="table-responsive">
                            <table class="table table-hover table-headings table-striped">
                                <thead>
                                    <tr>
                                        <!-- <th class="pk"><input type="checkbox" class="toggle" title="Toggle all" /></th>-->
                                        <th class="asc orderable"><a href="?sort=name&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=status&sort_by=<?= $sort_by ?>">Status</a></th>
                                        <th class="orderable"><a href="?sort=facility&sort_by=<?= $sort_by ?>">Facility</a></th>
                                        <th class="orderable"><a href="?sort=asn&sort_by=<?= $sort_by ?>">ASN</a></th>
                                        <th class="orderable"><a href="?sort=time_zone&sort_by=<?= $sort_by ?>">Time Zone</a></th>
                                        <th class="orderable"><a href="?sort=physical_address&sort_by=<?= $sort_by ?>">Physical Address</a></th>
                                        <th class="orderable"><a href="?sort=shipping_address&sort_by=<?= $sort_by ?>">Shipping Address</a></th>
                                        <th class="orderable"><a href="?sort=latitude&sort_by=<?= $sort_by ?>">Latitude</a></th>
                                        <th class="orderable"><a href="?sort=longitude&sort_by=<?= $sort_by ?>">Longitude</a></th>
                                        <th class="orderable"><a href="?sort=contact_name&sort_by=<?= $sort_by ?>">Contact Name</a></th>
                                        <th class="orderable"><a href="?sort=contact_no&sort_by=<?= $sort_by ?>">Contact No</a></th>
                                        <th class="orderable"><a href="?sort=contact_email&sort_by=<?= $sort_by ?>">Email</a></th>
                                        <th class="primary_ip">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if(count($location_res) > 0){
                                        foreach($location_res as $res) { ?>
                                    <tr>
                                        <td><?php echo $res['Name'] ?></td>
                                        <td><?php echo $res['Status'] ?></td>
                                        <td><?php echo $res['Facility'] ?></td>
                                        <td><?php echo $res['ASN'] ?></td>
                                        <td><?php echo $res['Time_zone'] ?></td>
                                        <td><?php echo $res['Physical_address'] ?></td>
                                        <td><?php echo $res['Shipping_address'] ?></td>
                                        <td><?php echo $res['Latitude'] ?></td>
                                        <td><?php echo $res['Longitude'] ?></td>
                                        <td><?php echo $res['Contact_name'] ?></td>
                                        <td><?php echo $res['Contact_no'] ?></td>
                                        <td><?php echo $res['Contact_email'] ?></td>
                                        <td><a href="location.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                        <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a></td>
                                    </tr>    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="13">Record not found!</td></tr>
                                    <?php } ?>
                                </tbody>
                                <?php if(count($location_res) > 0){ ?>
                                <tfoot>
                                    <tr><td colspan="13" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            
                                            $total_rec = $location->GetDashLocationList(); 
                                            $total_pages = ceil($total_rec['total_location'] / $limit); 
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
                    </form>
                    <div class="clearfix"></div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="fa fa-search" aria-hidden="true"></span>
                            <strong>Search</strong>
                        </div>
                        <div class="panel-body">
                            <form action="location_list.php" method="get" class="form">
                                <div class="form-group">
                                    <label for="id_location">Location</label>
                                    <select name="location" placeholder="Location" size="6" multiple="multiple" id="id_location" class="form-control">
                                        <?php foreach($location_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['location']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_status">Status</label>
                                    <select name="status" placeholder="None" size="4" multiple="multiple" id="id_status" class="form-control">
                                        <option value="Active" <?php echo "Active"==$_GET['status']?'selected':''?>>Active</option>
                                        <option value="Inactive" <?php echo "Inactive"==$_GET['status']?'selected':''?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_time_zone">Time Zone</label>
                                    <select name="time_zone" placeholder="None" size="4" multiple="multiple" id="id_time_zone" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="UTC" <?php echo $_GET['time_zone']=="UTC"?'selected':''?>>UTC</option>
                                        <option value="PST" <?php echo $_GET['time_zone']=="PST"?'selected':''?>>PST</option>
                                        <option value="CST" <?php echo $_GET['time_zone']=="CST"?'selected':''?>>CST</option>
                                        <option value="EST" <?php echo $_GET['time_zone']=="EST"?'selected':''?>>EST</option>
                                        <option value="EDT" <?php echo $_GET['time_zone']=="EDT"?'selected':''?>>EDT</option>
                                        <option value="CIT" <?php echo $_GET['time_zone']=="CIT"?'selected':''?>>CIT</option>
                                        <option value="EIT" <?php echo $_GET['time_zone']=="EIT"?'selected':''?>>EIT</option> 
                                        <option value="WIT" <?php echo $_GET['time_zone']=="WIT"?'selected':''?>>WIT</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_contact_name">Cotact Name</label>
                                    <input type="text" name="contact_name" placeholder="Contact Name" value="<?php echo $_GET['contact_name']?>" id="id_contact_name" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label for="id_contact_email">Contact Email</label>
                                    <input type="text" name="contact_email" placeholder="Contact Email" value="<?php echo $_GET['contact_email']?>" id="id_contact_email" class="form-control" />
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fa fa-search" aria-hidden="true"></span> Apply
                                    </button>
                                    <a href="location_list.php" class="btn btn-default" style="line-height:1 !important">
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