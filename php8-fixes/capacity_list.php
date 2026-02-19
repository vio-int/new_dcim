<?php
# catch for assholes that don't read the install instructions
if (!file_exists("db.inc.php")) {
    require_once( "preflight.inc.php" );
    exit;
}
/* if ( ! $_SERVER["HTTPS"] ) {
  printf( "<meta http-equiv='refresh' content='0; url='https://%s'>", $_SERVER["SERVER_NAME"] );
  exit();
  } */

require_once( 'db.inc.php' );
require_once( 'facilities.inc.php' );

$subheader = __("Capacity List");
$footer_text = "";

$Capacity = new Capacity();
$Location = new Location();

$filter = array();
$breadcrumb_arr = array();
$breadcrumb_arr[0]['title'] = "Dashboard";
$breadcrumb_arr[0]['link'] = "index_dcim.php";

if($_GET['type']=="Locations"){
    
    $field_title = "Locations";
    $type = "Rooms";
    $breadcrumb_arr[1]['title'] = "Location";
    $breadcrumb_arr[1]['link'] = "capacity_list.php?type=Location";
    
    if ($_GET['modle_id']) {
        $filter['location'] = $_GET['modle_id'];
    }
    $Capacity_arr = $Capacity->GetLocationCapacityRows($filter);
    $Capacity_res = json_decode(json_encode($Capacity_arr), true);

} else if($_GET['type']=="Rooms" && $_GET['modle_id']) {
    
    $get_name = $Capacity->GetPropertyName("location", $_GET['modle_id']);
    
    $field_title = "Rooms";
    $type = "Racks";
    unset($_SESSION["room"]);
    unset($_SESSION["location"]);
    unset($_SESSION["location_name"]);
    $_SESSION["location"] = $_GET['modle_id'];
    $_SESSION["location_name"] = $get_name['name'];
    $breadcrumb_arr[1]['title'] = $get_name['name'];
    $breadcrumb_arr[1]['link'] = "capacity_list.php?type=Locations";
    $breadcrumb_arr[2]['title'] = "Room";
    $breadcrumb_arr[2]['link'] = "javacript:void(0);";
    
    if ($_GET['modle_id']) {
        $filter['location'] = $_GET['modle_id'];
    }    
    $Capacity_arr = $Capacity->GetRoomCapacityRows($filter);
    $Capacity_res = json_decode(json_encode($Capacity_arr), true);
} else if($_GET['type']=="Racks" && $_GET['modle_id']) {
    $get_name = $Capacity->GetPropertyName("room", $_GET['modle_id']);
    
    $field_title = "Racks";
    $type = "Devices";
    unset($_SESSION["room"]);
    unset($_SESSION["room_name"]);
    $_SESSION["room"] = $_GET['modle_id'];
    $_SESSION["room_name"] = $get_name['name'];
    $breadcrumb_arr[1]['title'] = $_SESSION["location_name"];
    $breadcrumb_arr[1]['link'] = "capacity_list.php?type=Locations";
    $breadcrumb_arr[2]['title'] = $get_name['name'];
    $breadcrumb_arr[2]['link'] = "capacity_list.php?type=Rooms&modle_id=".$_SESSION["location"];
    $breadcrumb_arr[3]['title'] = "Rack";
    $breadcrumb_arr[3]['link'] = "javacript:void(0);";
    
    if ($_GET['modle_id']) {
        $filter['room'] = $_GET['modle_id'];
    }    
    $Capacity_arr = $Capacity->GetRackCapacityRows($filter);
    $Capacity_res = json_decode(json_encode($Capacity_arr), true);
} else if($_GET['type']=="Devices" && $_GET['modle_id']) {
    $get_name = $Capacity->GetPropertyName("rack", $_GET['modle_id']);
    
    $field_title = "Devices";
    $type = "";
    $breadcrumb_arr[1]['title'] = $_SESSION["location_name"];
    $breadcrumb_arr[1]['link'] = "capacity_list.php?type=Locations";
    $breadcrumb_arr[2]['title'] = $_SESSION["room_name"];
    $breadcrumb_arr[2]['link'] = "capacity_list.php?type=Rooms&modle_id=".$_SESSION["location"];
    $breadcrumb_arr[3]['title'] = $get_name['name'];
    $breadcrumb_arr[3]['link'] = "capacity_list.php?type=Racks&modle_id=".$_SESSION["room"];
    $breadcrumb_arr[4]['title'] = "Device";
    $breadcrumb_arr[4]['link'] = "javacript:void(0);";
    
    if ($_GET['modle_id']) {
        $filter['rack'] = $_GET['modle_id'];
    }    
    $Capacity_arr = $Capacity->GetDeviceCapacityRows($filter);
    $Capacity_res = json_decode(json_encode($Capacity_arr), true);
}

//$location_list_arr = $Location->GetLocationList();
//$location_list_res = json_decode(json_encode($location_list_arr), true);

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
/* echo "<pre>";
  print_r($Capacity_res);
  echo "<pre/>";exit; */
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
                    <?php 
                    $breadcrumb_html = "";
                    foreach($breadcrumb_arr as $val) { 
                        $breadcrumb_html .='<li><a href="'.$val['link'].'">'.$val['title'].'</a></li>';
                    } ?>
                    <div class="col-sm-12">
                        <ol class="breadcrumb">
                            <?php echo $breadcrumb_html; ?>
                        </ol>
                        <h1>Capacity</h1>
                    </div>

                    <!-- END OF BREADCRUMBS CODE -->
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover table-headings">
                                <thead>
                                    <tr>
                                        <th class="orderable"><a href="javascript:void(0);"><?php echo $field_title ?></a></th>
                                        <?php if($type!=""){ ?>
                                        <th class="orderable"><a href="javascript:void(0);"><?php echo $type?></a></th>    
                                        <th class="orderable"><a href="javascript:void(0);">Total Space</a></th>
                                        <?php } ?>
                                        <th class="orderable"><a href="javascript:void(0);">Occupied Space</a></th>
                                        <?php if($type!=""){ ?>
                                        <th class="orderable"><a href="javascript:void(0);">Available Space</a></th>
                                        <th class="orderable"><a href="javascript:void(0);">Total Power (Watts)</a></th>
                                        <?php } ?>
                                        <th class="orderable"><a href="javascript:void(0);">Occupied Power (Watts)</a></th>
                                        <?php if($type!=""){ ?>
                                        <th class="orderable"><a href="javascript:void(0);">Available Power (Watts)</a></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                    <?php if(count($Capacity_res) > 0){
                                        foreach($Capacity_res as $site_res) { ?>
                                    <tr>
                                        <?php 
                                            if($_GET['type'] == "Locations"){
                                                $field_value = $site_res['Name'];
                                            } else if($_GET['type'] == "Rooms") {
                                                $field_value = $site_res['Room_name'];
                                            } else if($_GET['type'] == "Racks") {
                                                $field_value = $site_res['Rack_name'];
                                            } else if($_GET['type'] == "Devices") {
                                                $field_value = $site_res['Device_name'];
                                            }
                                        ?>
                                        <?php if($type!="" && ($site_res['Total_room']!=0 ||$site_res['Total_rack']!=0||$site_res['Total_device']!=0)){ ?>
                                        <td><a href="capacity_list.php?type=<?php echo $type ?>&modle_id=<?php echo $site_res['PortID'] ?>"><?php echo $field_value ?></a></td>
                                        <?php } else { ?>
                                        <td><?php echo $field_value ?></td>
                                        <?php }?>
                                        <?php if($type!=""){ ?>
                                        <td><?php if($_GET['type'] == "Locations"){
                                                echo $site_res['Total_room']!=""?$site_res['Total_room']:'0';
                                            } else if($_GET['type'] == "Rooms") {
                                                echo $site_res['Total_rack']!=""?$site_res['Total_rack']:'0';
                                            } else if($_GET['type'] == "Racks") {
                                                echo $site_res['Total_device']!=""?$site_res['Total_device']:'0';
                                            }
                                            ?></td>
                                        <td><?php echo $site_res['Total_space']!=""?$site_res['Total_space']:'0'; ?></td>
                                        <?php } ?>
                                        <td><?php echo $site_res['Total_used_space']!=""?$site_res['Total_used_space']:'0'; ?></td>
                                        <?php if($type!=""){ ?>
                                        <td><?php echo $site_res['Total_free_space']!=""?$site_res['Total_free_space']:'0'; ?></td>
                                        <td><?php echo $site_res['Total_power']!=""?$site_res['Total_power']:'0'; ?></td>
                                        <?php } ?>
                                        <td><?php echo $site_res['Total_used_power']!=""?$site_res['Total_used_power']:'0'; ?></td>
                                        <?php if($type!=""){ ?>
                                        <td><?php echo $site_res['Total_free_power']!=""?$site_res['Total_free_power']:'0'; ?></td>
                                        <?php } ?>
                                    </tr>
                                    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="<?= $type!=""?'8':'3'?>">Record not found!</td></tr> 
                                    <?php } ?>
                                <?php if(count($Capacity_res) > 0){ ?>
                                <tfoot>
                                    <tr><td colspan="<?= $type!=""?'8':'3'?>" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            if($_GET['type'] == "Locations"){
                                                $total_rec = $Capacity->GetPageLocationCapacityList($filter);
                                                $total_pages = ceil($total_rec['total_location'] / $limit); 
                                            } else if($_GET['type'] == "Rooms"){
                                                $total_rec = $Capacity->GetPageRoomCapacityList(); 
                                                $total_pages = ceil($total_rec['total_room'] / $limit); 
                                            } else if($_GET['type'] == "Racks"){
                                                $total_rec = $Capacity->GetPageRackCapacityList(); 
                                                $total_pages = ceil($total_rec['total_rack'] / $limit); 
                                            } else if($_GET['type'] == "Devices"){
                                                $total_rec = $Capacity->GetPageDeviceCapacityList(); 
                                                $total_pages = ceil($total_rec['total_device'] / $limit); 
                                            }
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
                    <?php /* <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <span class="fa fa-search" aria-hidden="true"></span>
                                <strong>Search</strong>
                            </div>
                            <div class="panel-body">
                                <form action="capacity_list.php" method="get" class="form">
                                    <div class="form-group">
                                        <label for="id_location">Location</label>
                                        <select name="location" placeholder="None" id="id_location" class="form-control">
                                            <option value="">-- Select --</option>
<?php foreach ($location_list_res as $val) { ?>
                                                <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID'] == $_GET['location'] ? 'selected' : '' ?>><?php echo $val['Name'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="fa fa-search" aria-hidden="true"></span> Apply
                                        </button>
                                        <a href="capacity_list.php" class="btn btn-default" style="line-height:1 !important">
                                            <span class="fa fa-remove" aria-hidden="true"></span> Clear
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div> */ ?>
                </div>
                <div class="push"></div>
            </div>
        </div>
        <!-- LISTING CODE END -->
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

    });
</script>