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
$aggreget=new IpamAggreget();
$prefix = new IpamPrefix();
$role = new IpamPrefixRole();
$ipaddress = new IpamIPaddress();
$vlan = new IpamVLAN();
$vlan_group = new IpamVLANGroup();
$rir = new IpamRIR();
$vrf = new IpamVRF();
$service = new Service();

// CALL METHOD FOR DASHBOARD LIST
$aggreget_row = $aggreget->GetDashIpamAggregetList();
$prefix_row = $prefix->GetDashIpamPrefixList();
$role_row = $role->GetDashIpamRoleList();
$ipaddress_row = $ipaddress->GetDashIpaddressList();
$vlan_row = $vlan->GetDashIpamVLANList();
$group_row = $vlan_group->GetDashIpamGroupList();
$rir_row = $rir->GetDashRIRList();
$vrf_row = $vrf->GetDashVRFList();
$service_row = $service->GetDashServiceList();

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
        <div class="container wrapper">
            <div class="row col-sm-12 dashboard-body">
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>IPAM</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $aggreget_row['total_aggreeget'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_aggreget_list.php">Aggregates</a></h4>
                                <p class="list-group-item-text text-muted">Aggregates</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $prefix_row['total_prefix'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_prefix_list.php">Prefixes</a></h4>
                                <p class="list-group-item-text text-muted">prefixes</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $role_row['total_role'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_prefix_role_list.php">Prefix/VLAN Role</a></h4>
                                <p class="list-group-item-text text-muted">Prefix/VLAN role</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $ipaddress_row['total_ipaddress'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_ipaddress_list.php">IP Address</a></h4>
                                <p class="list-group-item-text text-muted">IP Addresses</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $vlan_row['total_vlan'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_vlan_list.php">VLANs</a></h4>
                                <p class="list-group-item-text text-muted">VLANs</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $group_row['total_group'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_vlan_group_list.php">VLAN Groups</a></h4>
                                <p class="list-group-item-text text-muted">VLAN Groups</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $rir_row['total_rir'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_rir_list.php">RIRs</a></h4>
                                <p class="list-group-item-text text-muted">RIRs</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $vrf_row['total_vrf'] ?></span>
                                <h4 class="list-group-item-heading"><a href="ipam_vrf_list.php">VRF</a></h4>
                                <p class="list-group-item-text text-muted">vrfs</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right"><?= $service_row['total_service'] ?></span>
                                <h4 class="list-group-item-heading"><a href="service_list.php">Service</a></h4>
                                <p class="list-group-item-text text-muted">Service</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Configuration Management</strong>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">General</a></h4>
                                <p class="list-group-item-text text-muted">general</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Workflow</a></h4>
                                <p class="list-group-item-text text-muted">workflow</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Style</a></h4>
                                <p class="list-group-item-text text-muted">style</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Email</a></h4>
                                <p class="list-group-item-text text-muted">email</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Reporting</a></h4>
                                <p class="list-group-item-text text-muted">Reporting</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Tooltips</a></h4>
                                <p class="list-group-item-text text-muted">tooltips</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Cabling</a></h4>
                                <p class="list-group-item-text text-muted">Cabling</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Custom device attributes</a></h4>
                                <p class="list-group-item-text text-muted">Custom device attributes</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">LDAP</a></h4>
                                <p class="list-group-item-text text-muted">ladp</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">SAML</a></h4>
                                <p class="list-group-item-text text-muted">saml</p>
                            </div>
                            <div class="list-group-item">
                                <span class="badge pull-right">1</span>
                                <h4 class="list-group-item-heading"><a href="configuration.php">Pre-Flight Check</a></h4>
                                <p class="list-group-item-text text-muted">pre flight check</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="push"></div>
        </div>
    </body>
</html>