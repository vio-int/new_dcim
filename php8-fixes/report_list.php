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

?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>DCIM</title>
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
                    <div class="col-sm-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li>Reports</li>
                        </ol>
                        <h1>Reports</h1>
                    </div>
                    <!-- END OF BREADCRUMBS CODE -->
                    <div class="col-md-12">
                        <form method="post" class="form form-horizontal">
                            <input type='hidden' name='csrfmiddlewaretoken' value='' />
                            <input type="hidden" name="return_url" value="/dcim/devices/" />
                            <div class="table-responsive">
                                <table class="table table-hover table-headings table-striped">
                                    <thead>
                                        <tr>
                                            <th class="asc orderable">Report Name</th>
                                            <th class="primary_ip">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Search/Export by Data Center</td>
                                            <td><a href="search_export.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Storage Room Search/Export by Data Center</td>
                                            <td><a href="search_export_storage_room.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Export Data Center for CFD (XML)</td>
                                            <td><a href="report_xml_CFD.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Asset Report by Owner</td>
                                            <td><a href="report_contact.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Data Center Asset Report</td>
                                            <td><a href="report_asset.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Data Center Asset Report [Excel]</td>
                                            <td><a href="report_asset_Excel.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Data Center Asset Costing Report</td>
                                            <td><a href="report_cost.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Project Asset Report</td>
                                            <td><a href="report_projects.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Asset Aging Report</td>
                                            <td><a href="report_aging.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Warranty Expiration Report </td>
                                            <td><a href="report_warranty.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Virtual Machines by Department</td>
                                            <td><a href="report_vm_by_department.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Network Map</td>
                                            <td><a href="report_network_map.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Vendor/Model Report</td>
                                            <td><a href="report_vendor_model.php"><i class="fa fa-download"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>Action Log</td>
                                            <td><a href="report_logging.php"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
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