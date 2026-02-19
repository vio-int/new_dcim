<?php
# catch for assholes that don't read the install instructions
if (!file_exists("db.inc.php")) {
    require_once( "preflight.inc.php" );
    exit;
}

require_once( 'db.inc.php' );
require_once( 'facilities.inc.php' );

$subheader = __("DCIM Dashboard");

// INCLUDE CLASSES FOR GET DATA
$zone=new Zone();
$cabrow = new CabRow();
$cabinet = new Cabinet();
$device = new Device();
$virtual_machine = new Virtual_machine();

// CALL METHOD FOR DASHBOARD LIST
$location_row = $zone->GetDashZoneList();
$room_row = $cabrow->GetDashCabRowList();
$rack_row = $cabinet->GetDashRackList();
$devices_row = $device->GetDashDeviceList();
$machine_row = $virtual_machine->GetDashVirtual_machineList();

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
        <?php include('header_dcfm.inc.php'); ?>
        <div class="container wrapper">
            <div class="row col-sm-12 dashboard-body">
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Power</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right">3</span>
                                <h4 class="list-group-item-heading"><a href="javascript:void(0);">Clusters</a></h4>
                                <p class="list-group-item-text text-muted">Clusters of physical hosts in which VMs reside</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?php echo $machine_row['total_machine'] ?></span>
                                <h4 class="list-group-item-heading"><a href="virtual_machine_list.php">Virtual Machines</a></h4>
                                <p class="list-group-item-text text-muted">Virtual compute instances running inside clusters</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Cooling System</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right">0</span>
                                <h4 class="list-group-item-heading"><a href="javascript:void(0);">Providers</a></h4>
                                <p class="list-group-item-text text-muted">Organizations which provide circuit connectivity</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">0</span>
                                <h4 class="list-group-item-heading"><a href="javascript:void(0);">Circuits</a></h4>
                                <p class="list-group-item-text text-muted">Communication links for Internet transit, peering, and other services</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Security</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right">0</span>
                                <h4 class="list-group-item-heading"><a href="javascript:void(0);">Secrets</a></h4>
                                <p class="list-group-item-text text-muted">Sensitive data (such as passwords) which has been stored securely</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="push"></div>
        </div>
    </body>
</html>