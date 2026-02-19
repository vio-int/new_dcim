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
$sort_on = "d.id";
if($_GET['sort'] != "")
{
    $sort_on = $_GET['sort'];
}
if($_GET['sort_by'] != "")
{
    $sort_by = $_GET['sort_by']=="Asc"?"Desc":"Asc";
}
// END OF CODE

$device = new Device_new();
$Rack = new Rack();
$Location = new Location();
$manufacture = new Manufacture();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if($_GET['rack']){
    $filter['rack'] = $_GET['rack'];
}
if($_GET['location']){
    $filter['location'] = $_GET['location'];
}
if($_GET['height']){
    $filter['height'] = $_GET['height'];
}
if($_GET['weight']){
    $filter['weight'] = $_GET['weight'];
}
if($_GET['device_type']){
    $filter['device_type'] = $_GET['device_type'];
}
if($_GET['device_role']){
    $filter['device_role'] = $_GET['device_role'];
}
if($_GET['manufacture']){
    $filter['manufacture'] = $_GET['manufacture'];
}

$device_arr = $device->GetDeviceListRows($filter);
$device_res = json_decode(json_encode($device_arr), true);

$rack_list_arr = $Rack->GetRackList();
$rack_list_res = json_decode(json_encode($rack_list_arr), true); 

$location_list_arr = $Location->GetLocationList();
$location_list_res = json_decode(json_encode($location_list_arr), true);

$Manufacture_list_arr = $manufacture->GetManufactureList();
$manufacture_list_res = json_decode(json_encode($Manufacture_list_arr), true);

/* echo "<pre>";
print_r($device_res);
echo "<pre/>";exit; */

// Delete Code Start
if(isset($_POST['action']) && $_POST["action"]=="Delete"){
    header('Content-Type: application/json');
    $response=false;
    if(isset($_POST["TransferTo"])){
        $device->PortID=$_POST['PortID'];
        if($device->DeleteObject($_POST["TransferTo"])){
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
    $response = $device->ExportReport($filter);
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
                        <li>Device</li>
                    </ol>
                    <h1>Device</h1>
                </div>
                <div class="col-sm-3">
                    <a href="device.php" class="btn btn-primary">
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
                                        <th class="orderable"><a href="?sort=d.name&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=l.name&sort_by=<?= $sort_by ?>">location</a></th>
                                        <th class="orderable"><a href="?sort=r.name&sort_by=<?= $sort_by ?>">Rack</a></th>
                                        <th class="orderable"><a href="?sort=d.device_role&sort_by=<?= $sort_by ?>">Role</a></th>
                                        <th class="orderable"><a href="?sort=m.name&sort_by=<?= $sort_by ?>">Manufacture</a></th>
                                        <th class="orderable"><a href="?sort=d.device_type&sort_by=<?= $sort_by ?>">Device Type</a></th>
                                        <th class="orderable"><a href="?sort=d.height&sort_by=<?= $sort_by ?>">Height</a></th>
                                        <th class="orderable"><a href="?sort=d.weight&sort_by=<?= $sort_by ?>">Weight</a></th>
                                        <th class="orderable"><a href="?sort=d.wattage&sort_by=<?= $sort_by ?>">Wattage</a></th>
                                        <th class="orderable"><a href="?sort=d.no_power&sort_by=<?= $sort_by ?>">No. Power</a></th>
                                        <th class="orderable"><a href="?sort=d.no_port&sort_by=<?= $sort_by ?>">No. Port</a></th>
                                        <th class="orderable"><a href="?sort=d.rack_face&sort_by=<?= $sort_by ?>">Rack Face</a></th>
                                        <th class="orderable"><a href="?sort=d.status&sort_by=<?= $sort_by ?>">Status</a></th>
                                        <th class="orderable"><a href="?sort=d.platform&sort_by=<?= $sort_by ?>">Platform</a></th>
                                        <th class="primary_ip">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($device_res) > 0){
                                        foreach($device_res as $res) { ?>
                                    <tr>
                                        <td><?php echo '<a href="device_detail.php?id='.$res['PortID'].'">'.$res['Name'].'</a>' ?></td>
                                        <td><?php echo $res['Location_name'] ?></td>
                                        <td><?php echo $res['Rack_name'] ?></td>
                                        <td><?php echo $res['Device_role'] ?></td>
                                        <td><?php echo $res['Manufacture_name'] ?></td>
                                        <td><?php echo $res['Device_type'] ?></td>
                                        <td><?php echo $res['Height'] ?></td>
                                        <td><?php echo $res['Weight'] ?></td>
                                        <td><?php echo $res['Wattage'] ?></td>
                                        <td><?php echo $res['No_power'] ?></td>
                                        <td><?php echo $res['No_port'] ?></td>
                                        <td><?php echo $res['Rack_face'] ?></td>
                                        <td><?php echo $res['Status'] ?></td>
                                        <td><?php echo $res['Platform'] ?></td>
                                        <td><a href="device.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                        <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a></td>
                                    </tr>    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="15">Record not found!</td></tr> 
                                    <?php } ?>
                                </tbody>
                                <?php if(count($device_res) > 0){ ?>
                                <tfoot>
                                    <tr><td colspan="15" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            
                                            $total_rec = $device->GetDashDevice_newList(); 
                                            $total_pages = ceil($total_rec['total_device'] / $limit); 
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
                            <form action="device_list.php" method="get" class="form">
                                <div class="form-group">
                                    <label for="id_location">Location</label>
                                    <select name="location" placeholder="None" id="id_location" class="form-control">
                                        <option value="">-- Select --</option>
                                        <?php foreach($location_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['location']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_rack">Rack</label>
                                    <select name="rack" placeholder="Rack" id="id_rack" class="form-control">
                                        <option value="">-- Select --</option>
                                        <?php foreach($rack_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['rack']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_height">Height</label>
                                    <input type="text" name="height" placeholder="Height" value="<?php echo $_GET['height']?>" id="id_height" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label for="id_weight">Weight</label>
                                    <input type="text" name="weight" placeholder="Weight" value="<?php echo $_GET['weight']?>" id="id_weight" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label for="id_device_role">Device Role</label>
                                    <select name="device_role" placeholder="None" id="id_device_role" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="Access Switch" <?php echo "Access Switch"==$_GET['device_role']?'selected':''?>>Access Switch</option>
                                        <option value="Console Server" <?php echo "Console Server"==$_GET['device_role']?'selected':''?>>Console Server</option>
                                        <option value="Core Switch" <?php echo "Core Switch"==$_GET['device_role']?'selected':''?>>Core Switch</option>
                                        <option value="Distribution Switch" <?php echo "Distribution Switch"==$_GET['device_role']?'selected':''?>>Distribution Switch</option>
                                        <option value="Firewall" <?php echo "Firewall"==$_GET['device_role']?'selected':''?>>Firewall</option>
                                        <option value="Management Switch" <?php echo "Management Switch"==$_GET['device_role']?'selected':''?>>Management Switch</option>
                                        <option value="PDU" <?php echo "PDU"==$_GET['device_role']?'selected':''?>>PDU</option>
                                        <option value="Router Switch" <?php echo "Router Switch"==$_GET['device_role']?'selected':''?>>Router Switch</option>
                                        <option value="Server" <?php echo "Server"==$_GET['device_role']?'selected':''?>>Server</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_device_type">Device Type</label>
                                    <select name="device_type" placeholder="None" id="id_device_type" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option value="2 Post Frame" <?php echo "2 Post Frame"==$_GET['device_type']?'selected':''?>>2 Post Frame</option>
                                        <option value="Console Server" <?php echo "Console Server"==$_GET['device_type']?'selected':''?>>Console Server</option>
                                        <option value="Core Switch" <?php echo "Core Switch"==$_GET['device_type']?'selected':''?>>Core Switch</option>
                                        <option value="Wall-mounted Frame" <?php echo "Wall-mounted Frame"==$_GET['device_type']?'selected':''?>>Wall-mounted Frame</option>
                                        <option value="Wall-mounted Cabinet" <?php echo "Wall-mounted Cabinet"==$_GET['device_type']?'selected':''?>>Wall-mounted Cabinet</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_manufacture">Manufacture</label>
                                    <select name="manufacture" placeholder="None"id="id_manufacture" class="form-control">
                                        <option value="">-- Select --</option>
                                         <?php foreach($manufacture_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['manufacture']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fa fa-search" aria-hidden="true"></span> Apply
                                    </button>
                                    <a href="device_list.php" class="btn btn-default" style="line-height:1 !important">
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