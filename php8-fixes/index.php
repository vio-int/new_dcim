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

$subheader = __("Data Center Dashboard");
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
        <?php include('header.inc.php'); ?>
        <div class="container wrapper">
            <div class="row col-sm-12 dashboard-body">
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>DCIM</strong>
                        </div>
                        <!-- .bg-yellow,.bg-light-blue,.bg-olive -->
                        <div class="list-group main-menu-box">
                            <div class="list-group-item">
                                <span class="info-box-icon bg-maroon"><i class="fa fa-building float-right"></i></span>
                                <div class="info-box-content">
                                    <a href="index_dcim.php"><span class="info-box-text">DCIM</span></a>
                                </div>
                            </div>
                        </div>    
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>DCFM</strong>
                        </div>
                        <div class="list-group main-menu-box">
                            <div class="list-group-item">
                                <span class="info-box-icon bg-teal"><i class="fa fa-power-off float-right"></i></span>
                                <div class="info-box-content">
                                    <a href="index_dcfm.php"><span class="info-box-text">DCFM</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>IT Hardware Management</strong>
                        </div>
                        <div class="list-group main-menu-box">
                            <div class="list-group-item">
                                <span class="info-box-icon bg-yellow"><i class="fa fa-hdd float-right"></i></span>
                                <div class="info-box-content">
                                    <a href="index_ithardware.php"><span class="info-box-text">IT Hardware Management</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>    
    </body>
</html>