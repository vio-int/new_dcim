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
$sort_on = "r.id";
if($_GET['sort'] != "")
{
    $sort_on = $_GET['sort'];
}
if($_GET['sort_by'] != "")
{
    $sort_by = $_GET['sort_by']=="Asc"?"Desc":"Asc";
}
// END OF CODE

$rack = new Rack();
$Room = new Room();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if($_GET['rack']){
    $filter['rack'] = $_GET['rack'];
}
if($_GET['room']){
    $filter['room'] = $_GET['room'];
}
if($_GET['height']){
    $filter['height'] = $_GET['height'];
}
if($_GET['width']){
    $filter['width'] = $_GET['width'];
}
if($_GET['type']){
    $filter['type'] = $_GET['type'];
}

$rack_arr = $rack->GetRackListRows($filter);
$rack_res = json_decode(json_encode($rack_arr), true);

$room_list_arr = $Room->GetRoomList();
$room_list_res = json_decode(json_encode($room_list_arr), true); 

$rack_list_arr = $rack->GetRackList();
$rack_list_res = json_decode(json_encode($rack_list_arr), true);

/* echo "<pre>";
print_r($rack_res);
echo "<pre/>";exit; */

// Delete Code Start
if(isset($_POST['action']) && $_POST["action"]=="Delete"){
    header('Content-Type: application/json');
    $response=false;
    if(isset($_POST["TransferTo"])){
        $rack->PortID=$_POST['PortID'];
        if($rack->DeleteObject($_POST["TransferTo"])){
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
    $response = $rack->ExportReport($filter);
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
                        <li>Rack</li>
                    </ol>
                    <h1>Rack</h1>
                </div>
                <div class="col-sm-3">
                    <a href="rack.php" class="btn btn-primary">
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
                                        <th class="orderable"><a href="?sort=r.name&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=l.name&sort_by=<?= $sort_by ?>">Room</a></th>
                                        <th class="orderable"><a href="?sort=r.model&sort_by=<?= $sort_by ?>">Model</a></th>
                                        <th class="orderable"><a href="?sort=r.facility&sort_by=<?= $sort_by ?>">Facility</a></th>
                                        <th class="orderable"><a href="?sort=r.height&sort_by=<?= $sort_by ?>">Height</a></th>
                                        <th class="orderable"><a href="?sort=r.width&sort_by=<?= $sort_by ?>">Width</a></th>
                                        <th class="orderable"><a href="?sort=r.type&sort_by=<?= $sort_by ?>">Type</a></th>
                                        <th class="orderable"><a href="?sort=r.serial_no&sort_by=<?= $sort_by ?>">Serial Number</a></th>
                                        <th class="orderable"><a href="?sort=r.is_descending&sort_by=<?= $sort_by ?>">Is Descending</a></th>
                                        <th class="orderable"><a href="?sort=r.tag&sort_by=<?= $sort_by ?>">Tag</a></th>
                                        <th class="primary_ip">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($rack_res) > 0){
                                        foreach($rack_res as $res) { ?>
                                    <tr>
                                        <td><?php echo '<a href="rack_detail.php?id='.$res['PortID'].'">'.$res['Name'].'</a>' ?></td>
                                        <td><?php echo $res['RoomName'] ?></td>
                                        <td><?php echo $res['Model'] ?></td>
                                        <td><?php echo $res['Facility'] ?></td>
                                        <td><?php echo $res['Height'] ?></td>
                                        <td><?php echo $res['Width'] ?></td>
                                        <td><?php echo $res['Type'] ?></td>
                                        <td><?php echo $res['Serial_no'] ?></td>
                                        <td><?php echo $res['Descending'] ?></td>
                                        <td><?php echo $res['Tag'] ?></td>
                                        <td><a href="rack.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                        <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a></td>
                                    </tr>    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="11">Record not found!</td></tr> 
                                    <?php } ?>
                                </tbody>
                                <?php if(count($rack_res) > 0){ ?>
                                <tfoot>
                                    <tr><td colspan="11" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            
                                            $total_rec = $rack->GetDashRackList(); 
                                            $total_pages = ceil($total_rec['total_rack'] / $limit); 
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
                            <form action="rack_list.php" method="get" class="form">
                                <div class="form-group">
                                    <label for="id_rack">Rack</label>
                                    <select name="rack" placeholder="Rack" size="6" multiple="multiple" id="id_rack" class="form-control">
                                        <?php foreach($rack_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['rack']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_room">Room</label>
                                    <select name="room" placeholder="None" size="6" multiple="multiple" id="id_room" class="form-control">
                                        <?php foreach($room_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['room']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="id_height">Height</label>
                                    <input type="text" name="height" placeholder="Height" value="<?php echo $_GET['height']?>" id="id_height" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label for="id_width">Width</label>
                                    <input type="text" name="width" placeholder="Width" value="<?php echo $_GET['width']?>" id="id_width" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label for="id_type">Type</label>
                                    <select name="type" placeholder="None" size="6" multiple="multiple" id="id_type" class="form-control">
                                        <option value="2 Post Frame" <?php echo "2 Post Frame"==$_GET['type']?'selected':''?>>2 Post Frame</option>
                                        <option value="4 Post Frame" <?php echo "4 Post Frame"==$_GET['type']?'selected':''?>>4 Post Frame</option>
                                        <option value="4 Post Cabinet" <?php echo "4 Post Cabinet"==$_GET['type']?'selected':''?>>4 Post Cabinet</option>
                                        <option value="Wall-mounted Frame" <?php echo "Wall-mounted Frame"==$_GET['type']?'selected':''?>>Wall-mounted Frame</option>
                                        <option value="Wall-mounted Cabinet" <?php echo "Wall-mounted Cabinet"==$_GET['type']?'selected':''?>>Wall-mounted Cabinet</option>
                                    </select>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fa fa-search" aria-hidden="true"></span> Apply
                                    </button>
                                    <a href="rack_list.php" class="btn btn-default" style="line-height:1 !important">
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