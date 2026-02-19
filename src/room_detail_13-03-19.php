<?php
# catch for assholes that don't read the install instructions
if (!file_exists("db.inc.php")) {
    require_once("preflight.inc.php");
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

$room = new Room();
$dc = new DataCenter();

$cab=new Cabinet();
$ac=new AC();

if(isset($_POST['id']) && (isset($_POST['getobjects']) || isset($_POST['getoverview']))){
    
    $payload=array();
    if(isset($_POST['getobjects'])){
            $cab->DataCenterID=$_POST['id'];
            $ac->DataCenterID=$_POST['id'];
            $zone=new Zone();
            $zone->DataCenterID=$cab->DataCenterID;

            $payload=array('cab'=>$cab->ListCabinetsByDC(true),'panel'=>PowerPanel::getPanelsForMap($_POST['id']),'ac'=>AC::getACsForMap($_POST['id']),'zone'=>$zone->GetZonesByDC(true));
    } else {
            $dc->DataCenterID=$_POST['id'];
            $dc->GetDataCenterByID();
            $payload=$dc->GetOverview();
    }

    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$filter = array();
$filter['room'] = $_GET['id'];

$room_arr = $room->GetRoomOne($filter);
$room_res = json_decode(json_encode($room_arr), true);

$dc->DataCenterID = $_GET["id"];
$dc->GetDataCenterbyID();

function MakeImageMap($room_res) {
    $mapHTML = "";

    if ($room_res[0]['Front_picture'] != "") {
        $mapfile = "uploads/room" . DIRECTORY_SEPARATOR . $room_res[0]['Front_picture'];
        
        if (file_exists($mapfile)) {
            
            if (mime_content_type($mapfile) == 'image/svg+xml') {
                $svgfile = simplexml_load_file($mapfile);
                $width = substr($svgfile['width'], 0, 4);
                $height = substr($svgfile['height'], 0, 4);
            } else {
                list($width, $height, $type, $attr) = getimagesize($mapfile);
            }
            
            $mapHTML = '<div class="canvas" style="background-image: url(uploads/room/'.$room_res[0]['Front_picture'].')">
	<img src="css/blank.gif" usemap="#datacenter" width="'.$width.'" height="'.$height.'" alt="clearmap over canvas">
	<map name="datacenter" data-dc='.$room_res[0]['PortID'].' data-zoom=1 data-x1=0 data-y1=0>
	</map>
        <canvas id="mapCanvas" width="'.$width.'" height="'.$height.'"></canvas></div>';
        
        }
    }
    return $mapHTML;
}

$height = 0;
$width = 0;
$ie8fix = "";
if ($room_res[0]['Front_picture'] != "") {
    $mapfile = "uploads/room/{$room_res[0]['Front_picture']}";
    if (file_exists($mapfile)) {
        if (mime_content_type($mapfile) == 'image/svg+xml') {
            $svgfile = simplexml_load_file($mapfile);
            $width = substr($svgfile['width'], 0, 4);
            $height = substr($svgfile['height'], 0, 4);
        } else {
            list($width, $height, $type, $attr) = getimagesize($mapfile);
        }
// There is a bug in the excanvas shim that can set the width of the canvas to 10x the width of the image
        $ie8fix = "
<script type=\"text/javascript\">
	function uselessie(){
		document.getElementById('mapCanvas').className = 'mapCanvasiefix';
	}
$(document).ready(function() {
	uselessie();
});
</script>
<style type=\"text/css\">
.mapCanvasiefix {
	    width: {$width}px !important;
}
</style>";
    }
}
// If no mapfile is set then we don't need the buttons to control drawing the map.  Adjust the CSS to hide them and make the heading centered
if ($room_res[0]['Front_picture'] != "" || !file_exists("uploads/room/{$room_res[0]['Front_picture']}")) {
    $screenadjustment = '<style type="text/css">.dcstats .heading > div { width: 100% !important;} .dcstats .heading > div + div { display: none; }</style>';
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
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="scripts/common.js?v<?php echo filemtime('scripts/common.js');?>"></script>
        <script type="text/javascript" src="scripts/jquery.ui-contextmenu.js"></script>
        <script type="text/javascript">
            var js_outlinecabinets = <?php print $config->ParameterArray["OutlineCabinets"] == 'enabled'?1:0; ?>;
            var js_labelcabinets = <?php print $config->ParameterArray["LabelCabinets"] == 'enabled'?1:0; ?>;
        </script>
        <?php if(isset($ie8fix)){print $ie8fix;} ?>
    </head>
    <style>

    </style>
    <body>
        <?php include('header_dcim.inc.php'); ?>
        <!-- LISTING CODE START -->
        <div class="container wrapper">
            <div class="main">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li><a href="room_list.php">Room</a></li>
                            <li><?php echo $room_res[0]['Name']; ?></li>
                        </ol>
                    </div>
                </div>
                <h1><?php echo $room_res[0]['Name']; ?></h1>


                <div class="row" style="margin: 10px 0px 0px 0px;">
                    <div class="col-md-12">
                        <?php echo MakeImageMap($room_res); ?>
                    </div>
                </div>
            </div>
        </div>
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
    // Turn the stats box at the top into a lampshade
    $('#dcstats .title').css({'position': 'relative'});
    $('#dcstats .title > div').css({'border': '0 none', 'bottom': '-3px', 'font-size': 'xx-small', 'position': 'absolute', 'right': 0}).hide().removeClass('hide');
    $('#msg_hide').show();
    $('#dcstats').outerWidth($('#dcstats').outerWidth());
    $('#dcstats .title').on('click', function (e) {
        // this check just prevents the shade from opening / closing
        // when you click the links in the box and it had a weird feel
        if (e.target.nodeName != 'A') {
            if ($('#msg_hide').is(':visible')) {
                $('#msg_hide').hide();
                $('#msg_show').show();
                $('#dcstats > div:nth-child(n+2)').hide();
                $('#dcstats > div:first-child > div:nth-child(n+2)').hide();
            } else {
                $('#msg_hide').show();
                $('#msg_show').hide();
                $('#dcstats > div:nth-child(n+2)').show();
                $('#dcstats > div:first-child > div:nth-child(n+2)').show();
            }
        }
    }).trigger('click');

    $(document).ready(function () {
        // Hard set widths to stop IE from being retarded
        $('#mapCanvas').css('width', $('.canvas > img[alt="clearmap over canvas"]').width() + 'px');
        $('#mapCanvas').parent('.canvas').css('width', $('.canvas > img[alt="clearmap over canvas"]').width() + 'px');

        // Don't attempt to open the datacenter tree until it is loaded
        function opentree() {
            if ($('#datacenters .bullet').length == 0) {
                setTimeout(function () {
                    opentree();
                }, 500);
            } else {
                var firstcabinet = $('#dc<?php echo $room_res[0]['PortID']; ?> > ul > li:first-child').attr('id');
                expandToItem('datacenters', firstcabinet);
            }
        }

            // Bind context menu to the cabinets
            $(".canvas > map").contextmenu({
                delegate: "area[name^=cab]",
                menu: "#options",
                select: function (event, ui) {
                    var row = (ui.item.context.parentElement.getAttribute('data-context') == 'row' || ui.item.context.parentElement.getAttribute('data-context') == 'alignment') ? true : false;
                    var cabid = ui.target.context.attributes.name.value.substr(3);
                    $.post('', {cabinetid: cabid, airflow: ui.cmd, row: row}).done(function () {
                        startmap()
                    });
                },
                beforeOpen: function (event, ui) {
                    $('#options').removeClass('hide');
                    $('.center .nav > select').val('airflow').trigger('change');
                    $(".canvas > map").contextmenu("showEntry", "row", $(ui.target.context).data('row'));
                }
            });
    
        // Bind tooltips, highlight functions to the map
        startmap();
        opentree();
    });
</script>