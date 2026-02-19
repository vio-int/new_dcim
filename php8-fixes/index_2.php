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

$sql = 'select count(*) as DCs from fac_datacenter';
$row = $dbh->query($sql)->fetch();
$DCs = $row['DCs'];

// Overall Statistics
$sql = 'SELECT SUM(NominalWatts) AS Power,
		(SELECT COUNT(*) FROM fac_device WHERE DeviceType!="Server" LIMIT 1) AS Devices, 
		(SELECT COUNT(*) FROM fac_device WHERE DeviceType="Server" LIMIT 1) AS Servers,
		(SELECT SUM(Height) FROM fac_device LIMIT 1) AS Size,
		(SELECT COUNT(*) FROM fac_vminventory LIMIT 1) AS VMcount,
		(select count(*) FROM fac_device where Hypervisor!="None") as VMhosts,
		(select count(*) FROM fac_cabinet) as CabinetCount
		FROM fac_device LIMIT 1;';

$row = $dbh->query($sql)->fetch();

$StatsDevices = $row['Devices'];
$StatsServers = $row['Servers'];
$StatsSize = $row['Size'];
$StatsVM = $row['VMcount'];
$StatsHost = $row["VMhosts"];
$StatsCabinet = $row["CabinetCount"];
$StatsPower = $row['Power'];
$StatsHeat = $StatsPower * 3.412 / 12000;

$dc = new DataCenter();
$dcList = $dc->GetDCList();

// Build table to display pending rack requests for inclusion later
$rackrequest = '';
if ($config->ParameterArray["RackRequests"] == "enabled" && $person->RackAdmin) {
    $rackrequest = "<h3 class='pull-left'>" . __("Pending Rack Requests") . "</h3><div class='clearfix'></div><div class='table-responsive'> <table class=\"table table-headings table-striped table-bordered\">\n<tr>\n  <th>" . __("Submit Time") . "</th>\n  <th>" . __("Requestor") . "</th>\n  <th>" . __("System Name") . "</th>\n  <th>" . __("Department") . "</th>\n  <th>" . __("Due By") . "</th>\n</tr><br>\n";

    $rack = new RackRequest();
    $tmpContact = new People();
    $dept = new Department();

    $rackList = $rack->GetOpenRequests();

    foreach ($rackList as $request) {
        $tmpContact->PersonID = $request->RequestorID;
        $tmpContact->GetPerson();

        $dept->DeptID = $request->Owner;
        $dept->GetDeptByID();

        $reqDate = getdate(strtotime($request->RequestTime));
        $dueDate = date('M j Y H:i:s', mktime($reqDate['hours'], $reqDate['minutes'], $reqDate['seconds'], $reqDate['mon'], $reqDate['mday'] + 1, $reqDate['year']));

        if ((strtotime($dueDate) - strtotime('now')) < intval($config->ParameterArray['RackOverdueHours'] * 3600)) {
            $colorCode = 'overdue';
        } elseif ((strtotime($dueDate) - strtotime('now')) < intval($config->ParameterArray['RackWarningHours'] * 3600)) {
            $colorCode = 'soon';
        } else {
            $colorCode = 'clear';
        }
        $rackrequest .= "<tr class=\"$colorCode\"><td>" . date("M j Y H:i:s", strtotime($request->RequestTime)) . "</td><td>$tmpContact->FirstName $tmpContact->LastName</td><td><a href=\"rackrequest.php?requestid=$request->RequestID\">$request->Label</a></td><td>$dept->Name</td><td>$dueDate</td></tr>\n";
    }
    $rackrequest .= '</table></div><!-- END div.table -->';
}
?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Data Center Inventory</title>
        <link rel="stylesheet" href="css/inventory.php" type="text/css">
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css" />
        <![endif]-->

        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
    </head>
    <body>
        <?php include( 'header_main.inc.php' ); ?>

        <div class="container">
            <div class="page1 index">
                <div class="makecenter">
                    <?php
                    // include( 'sidebar.inc.php' );
                    echo '
<div class="main">
<div class="row">
<div class="sol-sm-12">
<div class="table-responsive">
', $rackrequest, '
<h3 class="pull-left">', __("Data Center Inventory"), ' <a href="search_export.php"></a></h3>
<table class="table table-headings table-striped table-bordered">
<tr>
<th colspan=2 class="text-center">
', __("Hosted Systems"), '
</th>
</tr>';
                    echo '
<tr>
  <td>', __("DC Count"), '</td>
  <td>', $DCs, '</td>
</td>';
                    echo '
<tr>
  <td>', __("Physical Server Count"), '</td>
  <td>', $StatsServers, '</td>
</tr>
<tr>
  <td>', __("Other Device Count"), '</td>
  <td>', $StatsDevices, '</td>
</tr>
<tr>
  <td>', __("Space"), ' (1U=1.75")</td>
  <td>', $StatsSize, ' U</td>
</tr>
<tr>
  <td>', __("Power Consumption"), '</td>
  <td>', sprintf("%.2f kW", $StatsPower / 1000), '</td>
</tr>
<tr>
  <td>', __("Heat Produced"), '</td>
  <td>', sprintf("%.2f Tons", $StatsHeat), '</td>
</tr>
<tr>
  <td>', __("Virtual Machines"), '</td>
  <td>', $StatsVM, '</td>
</tr>
<tr>
	<td>', __("VM Hosts"), '</td>
	<td>', $StatsHost, '</td>
</tr>
<tr>
	<td>', __("Virtualization Ratio"), '</td>
	<td>', intval($StatsVM / (($StatsHost == 0) ? 1 : $StatsHost)), ':1</td>
</tr>
<tr>
	<td>', __("Total Cabinets"), '</td>
	<td>', $StatsCabinet, '</td>
</tr>
</table></div></div></div></div><!-- END div.table -->
<div  class="clearfix"></div>
<div class="col-sm-12 pull-right">
<a style="color:#ffffff" class="btn btn-success" href="search_export.php"><span class="fa fa-upload" aria-hidden="true"></span> ', __("Export Inventory"), '</a>
	</div>';

                    if (file_exists("sitecontact.html")) {
                        include( "sitecontact.html" );
                    }
                    echo '	</div>
';
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>