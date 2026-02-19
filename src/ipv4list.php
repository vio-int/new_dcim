<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("IP v4 Listing");

if (!$person->SiteAdmin) {
    header('Location: ' . redirect());
    exit;
}

$mfg = new IPv4List();

$mfgList = $mfg->GetIPv4List();
//print_r($mfgConnectorList);exit;
?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Device Class Templates</title>
        <!-- Favicon -->
        <link type="image/x-icon" href="images/favicon.ico" rel="shortcut icon" />
        
        <link rel="stylesheet" href="css/inventory.php" type="text/css">
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
        <link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css">
        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css">
        <![endif]-->
        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>

        <style type="text/css">
            #using { margin-top: 1em; }
        </style>

        <script type="text/javascript">
            $(document).ready(function () {
                $('#mform').validationEngine({});
            });

        </script>
    </head>
    <body>
        <?php include( 'header.inc.php' ); ?>
        <div class="backgroundpage">
            <div class="page1">
                <div class="makecenter">
                    <div class="main">
                        <div class="center">
                            
                            <form id="mform" method="POST">
                                <div class="table">
                                    <?php
                                    // include( "sidebar.inc.php" );
                                    echo '<table class="table table-headings table-striped table-bordered">
                    <tr>
                    <th>Name</th>
                    <th>IP</th>
                    <th>VLAN</th>
                    <th>Tag</th>
                    </tr>';
                                    foreach ($mfgList as $mfgRow) {
                                        echo '<tr><td><a href="ipv4allocation.php?IpID='.$mfgRow->PortID.'">' . $mfgRow->Name . '</a></td>
                    <td>' . $mfgRow->Prefix . '</td>
                    <td>' . $mfgRow->Vlan . '</td>
                    <td>' . $mfgRow->Tag . '</td></tr>';
                                    }
                                    echo '</table>';
                                    ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>