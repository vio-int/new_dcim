<?php
# catch for assholes that don't read the install instructions
if (!file_exists("db.inc.php")) {
    require_once( "preflight.inc.php" );
    exit;
}

require_once( 'db.inc.php' );
require_once( 'facilities.inc.php' );

$subheader = __("DCIM Dashboard");
$footer_text = "";

// INCLUDE CLASSES FOR GET DATA
$location=new Location();
$room = new Room();
$rack = new Rack();
$device = new Device_new();
$project = new Projects();
$ClusterGroup = new ClusterGroup();
$ClusterType = new ClusterType();
$Cluster = new Cluster();
$virtual_machine = new Virtual_machine();
$Console = new ConsoleConn();
$Interface = new InterfaceConn();
$Power = new PowerConn();
$Assets = new Asset();
$AssetsSupplier = new AssetSupplier();
$AssetsModel = new Model();
$AssetCategory = new AssetCategory();
$Simulation = new Simulation();
$RackSimulation = new RackSimulation();

// CALL METHOD FOR DASHBOARD LIST
$location_row = $location->GetDashLocationList();
$room_row = $room->GetDashRoomList();
$rack_row = $rack->GetDashRackList();
$devices_row = $device->GetDashDevice_newList();
$project_row = $project->getDashProjectList();
$cluster_group_row = $ClusterGroup->GetDashClusterGroupList();
$cluster_type_row = $ClusterType->GetDashClusterTypeList();
$cluster_row = $Cluster->GetDashClusterList();
$machine_row = $virtual_machine->GetDashVirtual_machineList();
$console_row = $Console->GetDashConsoleList();
$interface_row = $Interface->GetDashInterfaceList();
$power_row = $Power->GetDashPowerList();
$assets_row = $Assets->GetDashAssetList();
$assets_models = $AssetsModel->GetDashModelList();
$simulation_row = $Simulation->GetDashSimulation(); 
$racksimulation_row = $RackSimulation->GetDashRackSimulation(); 
$assets_status_row = $Assets->GetDashAssetStatusList();
$assets_supplier_row = $AssetsSupplier->GetDashAssetSupplierList();
$asset_category_row = $AssetCategory->GetDashCategoryList();
$asset_maintenance_row = $Assets->GetAssetList();
$asset_maintenance_res = json_decode(json_encode($asset_maintenance_row), true); 
//print_r($asset_maintenance_res);exit;
?>
<!doctype html>
<style>
    .scroll-bar-wrap {
        width: auto;
        position: relative;
        margin: 1em auto;
    }
    .scroll-box {
        width: 100%;
        height: 226px;
        overflow-y: scroll;
    }
    .scroll-box::-webkit-scrollbar {
        width: .4em; 
    }
    .scroll-box::-webkit-scrollbar,
    .scroll-box::-webkit-scrollbar-thumb {
        overflow:visible;
        border-radius: 4px;
    }
    .scroll-box::-webkit-scrollbar-thumb {
        background: #777; 
    }
    .cover-bar {
        position: absolute;
        background: #fff;
        height: 100%;  
        top: 0;
        right: 0;
        width: .4em;
        -webkit-transition: all .5s;
        opacity: 1;
    }
    /* MAGIC HAPPENS HERE */
    .scroll-bar-wrap:hover .cover-bar {
        opacity: 0;
        -webkit-transition: all .5s;
    }
    .maintenance_date{
        background: #d8f1f1 !important;
    }
</style>
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
        <div class="container wrapper">
            <div class="row col-sm-12 dashboard-body">
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>DCIM</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $location_row['total_location']?></span>
                                <h4 class="list-group-item-heading"><a href="location_list.php">Location</a></h4>
                                <p class="list-group-item-text text-muted">geographic location</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $room_row['total_room']?></span>
                                <h4 class="list-group-item-heading"><a href="room_list.php">Rooms</a></h4>
                                <p class="list-group-item-text text-muted">list of location rooms</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $rack_row['total_rack']; ?></span>
                                <h4 class="list-group-item-heading"><a href="rack_list.php">Racks</a></h4>
                                <p class="list-group-item-text text-muted">Equipment racks, optionally organized by group</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $devices_row['total_device']; ?></span>
                                <h4 class="list-group-item-heading"><a href="device_list.php">Devices</a></h4>
                                <p class="list-group-item-text text-muted">Rack-mounted network equipment, servers, and other devices</p>
                            </div>
                            <div class="list-group-item">
                                <h4 class="list-group-item-heading">Connections</h4>
                                <span class="badge pull-right"><?php echo $interface_row['total_interface'] ?></span>
                                <p style="padding-left: 20px;"><a href="interface_conn_list.php">Interfaces</a></p>
                                <span class="badge pull-right"><?php print_r($console_row['total_console']); ?></span>
                                <p style="padding-left: 20px;"><a href="console_conn_list.php">Console</a></p>
                                <span class="badge pull-right"><?php print_r($power_row['total_powers']); ?></span>
                                <p class="list-group-item-text" style="padding-left: 20px;"><a href="power_conn_list.php">Power</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Monitor and Alert</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right">14</span>
                                <h4 class="list-group-item-heading"><a href="report_list.php">Reports</a></h4>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">0</span>
                                <h4 class="list-group-item-heading"><a href="javascript:void(0);">Monitor</a></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Management</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right">0</span>
                                <h4 class="list-group-item-heading"><a href="capacity_list.php?type=Locations">Capacity</a></h4>
                                <p class="list-group-item-text text-muted">Capacity of data center</p>
                            </div>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <h4 class="list-group-item-heading">Simulation</h4>
                                <span class="badge pull-right"><?php echo $racksimulation_row['total_simulation'] ?></span>
                                <p style="padding-left: 20px;"><a href="rack_simulation.php">Room Simulation</a></p>
                                <span class="badge pull-right"><?php echo $simulation_row['total_simulation'] ?></span>
                                <p style="padding-left: 20px;"><a href="simulation.php">Rack Simulation</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Virtualization</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <h4 class="list-group-item-heading">Clusters</h4>
                                <span class="badge pull-right"><?php echo $cluster_row['total_cluster'] ?></span>
                                <p style="padding-left: 20px;"><a href="cluster_list.php">Clusters</a></p>
                                <span class="badge pull-right"><?php echo $cluster_type_row['total_type'] ?></span>
                                <p style="padding-left: 20px;"><a href="cluster_type_list.php">Cluster Type</a></p>
                                <span class="badge pull-right"><?php echo $cluster_group_row['total_group']; ?></span>
                                <p class="list-group-item-text" style="padding-left: 20px;"><a href="cluster_group_list.php">Cluster Group</a></p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?php echo $machine_row['total_machine']; ?></span>
                                <h4 class="list-group-item-heading"><a href="virtual_machine_list.php">Virtual Machines</a></h4>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Project</strong>
                        </div>
                        <div class="list-group">
                            <?php if(count($project_row) > 0) {
                                foreach($project_row as $res){?>
                                <div class="list-group-item">
                                    <span class="badge pull-right">1</span>
                                    <h4 class="list-group-item-heading"><a href="project_mgr.php"><?= $res->ProjectName?></a></h4>
                                    <p class="list-group-item-text text-muted"><?= $res->ProjectSponsor?></p>
                                </div>
                            <?php } ?> 
                            <!-- <div class="list-group-item text-right">
                                <a href="project_mgr.php">View All Project</a>
                            </div> -->
                            <?php } else { ?>
                                <div class="list-group-item">
                                    <p class="list-group-item-text text-muted">None</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Assets Management</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <h4 class="list-group-item-heading">Assets</h4>
                                <span class="badge pull-right"><?php echo $assets_row['total_assets'] ?></span>
                                <p style="padding-left: 20px;"><a href="asset_list.php">Assets</a></p>
                                <span class="badge pull-right"><?php echo $assets_status_row['total_status'] ?></span>
                                <p style="padding-left: 20px;"><a href="asset_status_list.php">Asset Status</a></p>
                                <span class="badge pull-right"><?php echo $assets_supplier_row['total_supplier'] ?></span>
                                <p style="padding-left: 20px;"><a href="asset_supplier_list.php">Asset Supplier</a></p>
                                <span class="badge pull-right"><?php echo $asset_category_row['total_category'] ?></span>
                                <p style="padding-left: 20px;"><a href="asset_category_list.php">Asset Category</a></p>
                                <span class="badge pull-right"><?php echo $assets_models['total_models'] ?></span>
                                <p style="padding-left: 20px;"><a href="asset_model_list.php">Asset Model</a></p>
                                <span class="badge pull-right"><?php echo $assets_row['total_assets'] ?></span>
                                <p style="padding-left: 20px;"><a href="asset_simulation.php">Asset Simulation</a></p>
                                <p style="padding-left: 20px;"><a href="maintenance_history.php">Maintenance History</a></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="panel panel-default scroll-bar-wrap">
                        <div class="panel-heading">
                            <strong>Assets Maintenance <a href="manage_maintenance.php" class="pull-right">Manage <i class="fa fa-calendar"></i></a></strong>
                        </div>
                        <div class="scroll-box">
                            <div class="list-group">
                                <?php if(count($asset_maintenance_res) > 0){
                                    foreach($asset_maintenance_res as $val) {
                                    $next_main_date = date('m/d/Y', strtotime("+".$val['Maintenance']." months", strtotime($val['First_main_date'])));
                                    //echo strtotime($next_main_date)." - ".strtotime(date('m/d/Y'));exit;
                                    ?>
                                <div class="list-group-item <?php echo strtotime($next_main_date)==strtotime(date('m/d/Y'))?"maintenance_date":""?>">
                                    <span class="badge pull-right"><?php echo date('m/d/Y',strtotime($val['Next_main_date']));?></span>
                                    <h4 class="list-group-item-heading"><a href="asset.php?PortID=<?php echo $val['PortID']?>"><?php echo $val['Name']?></a></h4>
                                </div>
                                <?php } } ?>
                            </div>
                            <div class="cover-bar"></div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div class="push"></div>
        </div>
    </body>
    <!-- Footer -->
    <?php if($footer_text!=""){?>
        <footer class="page-footer font-small footer">
            <spam><?php echo $footer_text; ?></spam>
        </footer>
    <?php } ?>
    <!-- Footer -->
</html>