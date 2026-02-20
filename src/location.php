<?php
require_once('db.inc.php');
require_once('facilities.inc.php');

$subheader = __("Data Center Detail");

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

$status = "";

$dc = new DataCenter();
$mfg = new Location();
$location = new Location();

// AJAX Action
if (isset($_POST['action']) && $_POST["action"] == "Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $mfg->PortID = $_POST['PortID'];
        if ($mfg->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}

if (isset($_POST['action']) && (($_POST['action'] == 'Create') || ($_POST['action'] == 'Update'))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Slug = trim($_POST["slug"]);
    $mfg->Status = trim($_POST["status"]);
    $mfg->Location = trim($_POST["region"]);
    $mfg->Facility = trim($_POST["facility"]);
    $mfg->ASN = trim($_POST["asn"]);
    $mfg->Time_zone = trim($_POST["time_zone"]);
    $mfg->Description = trim($_POST["description"]);
    $mfg->Physical_address = trim($_POST["physical_address"]);
    $mfg->Shipping_address = trim($_POST["shipping_address"]);
    $mfg->Latitude = trim($_POST["latitude"]);
    $mfg->Longitude = trim($_POST["longitude"]);
    $mfg->Contact_name = trim($_POST["contact_name"]);
    $mfg->Contact_email = trim($_POST["contact_email"]);
    $mfg->Contact_no = trim($_POST["contact_no"]);
    $mfg->Tag = trim($_POST["tag"]);
    $mfg->Comment = trim($_POST["comment"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("location.php?PortID=$mfg->PortID"));
            } else {
                $status = __("Error adding new object");
            }
        } else {
            $status = __("Updated");
            $mfg->UpdateObject();
        }
    }
    //We either just created a manufacturer or updated it so reload from the db
    $mfg->GetOrderByID();
}

if (isset($_REQUEST['PortID']) && $_REQUEST['PortID'] > 0) {

    $mfg->PortID = (isset($_POST['PortID']) ? $_POST['PortID'] : $_GET['PortID']);
    //$dc->DataCenterID = (isset($_POST['datacenterid']) ? $_POST['datacenterid'] : $_GET['datacenterid']);
    $mfg->GetDataCenter();
    //$dc->GetDataCenter();
}
$imageselect = '<div id="preview"></div><div id="filelist">';
$path = './drawings';
$dir = @scandir($path);
if ($dir) {
    foreach ($dir as $i => $f) {
        if (is_file($path . DIRECTORY_SEPARATOR . $f)) {
            $mimeType = mime_content_type($path . DIRECTORY_SEPARATOR . $f);
            if (preg_match('/^image/i', $mimeType)) {
                $imageselect .= "<span>$f</span>\n";
            }
        }
    }
}
$imageselect .= "</div>";

$mfgList = $mfg->GetLocationList();
$LocationList = $location->GetLocationList();
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
            $(document).ready(function() {
            $('#mform').validationEngine({});
            $('#PortID').change(function(e){
            location.href = 'location.php?PortID=' + this.value;
            });
            // Show number of templates using manufacturer
            UpdateCount();
            $('button[name="action"][value="Delete"]').click(DeleteObject);
            });
            function UpdateCount(e){
            var count;
            $.ajax({
            type:'get',
                    async: false,
                    data:{getTemplateCount: $('#PortID').val()},
                    success: function(data){
                    $('#count').text(data.length);
                    count = data.length;
                    }
            });
            return count;
            }

            function DeleteObject(){
            // If manufacturerid unset then just delete 
            transferto = (typeof (objectid) == 'undefined')?0:objectid;
            $.post('', {PortID: $('#PortID').val(), TransferTo: transferto, action: 'Delete'}, function(data){
            if (data){
            location.href = '';
            } else{
            alert("Something's gone horrible wrong");
            }
            });
            }

        </script>

        <script type="text/javascript">
            $(document).ready(function() {
            $('#datacenterform').validationEngine({});
            $('#drawingfilename').click(function(){
            $("#imageselection").dialog({
            resizable: false,
                    height:500,
                    width: 600,
                    modal: true,
                    buttons: {
<?php echo '					', __("Select"), ': function() {'; ?>
                    if ($('#imageselection #preview').attr('image') != ""){
                    $('#drawingfilename').val($('#imageselection #preview').attr('image'));
                    }
                    $(this).dialog("close");
                    }
            }
            });
            $("#imageselection span").each(function(){
            var preview = $('#imageselection #preview');
            $(this).click(function(){
            preview.css({'border-width': '5px', 'width': '380px', 'height': '380px'});
            preview.html('<img src="drawings/' + $(this).text() + '" alt="preview">').attr('image', $(this).text());
            preview.children('img').load(function(){
            var topmargin = 0;
            var leftmargin = 0;
            if ($(this).height() < $(this).width()){
            $(this).width(preview.innerHeight());
            $(this).css({'max-width': preview.innerWidth() + 'px'});
            topmargin = Math.floor((preview.innerHeight() - $(this).height()) / 2);
            } else{
            $(this).height(preview.innerHeight());
            $(this).css({'max-height': preview.innerWidth() + 'px'});
            leftmargin = Math.floor((preview.innerWidth() - $(this).width()) / 2);
            }
            $(this).css({'margin-top': topmargin + 'px', 'margin-left': leftmargin + 'px'});
            });
            $("#imageselection span").each(function(){
            $(this).removeAttr('style');
            });
            $(this).css('border', '1px dotted black')
            });
            if ($('#drawingfilename').val() == $(this).text()){
            $(this).click();
            }
            });
            });
            });
            function coords(evento){
            mievento = evento || window.event;
            yo = document.getElementById("yo");
            x = mievento.layerX;
            y = mievento.layerY;
            yo.style.left = (x - 12) + "px";
            yo.style.top = (y - 12) + "px";
            yo.hidden = false;
            CoorX = document.getElementById("x");
            CoorX.value = x * 2;
            CoorY = document.getElementById("y");
            CoorY.value = y * 2;
            }
            function mueve(){
            tam = 50;
            red = .5;
            tam = tam * red;
            yo = document.getElementById("yo");
            cont = document.getElementById("containerimg");
            CoorX = document.getElementById("x");
            CoorY = document.getElementById("y");
            if (CoorX.value < 0) CoorX.value = 0;
            if (CoorX.value * red > cont.offsetWidth) CoorX.value = cont.offsetWidth / red;
            if (CoorY.value < 0) CoorY.value = 0;
            if (CoorY.value * red > cont.offsetHeight) CoorY.value = cont.offsetHeight / red;
            yo.style.left = (CoorX.value * red - tam / 2) + "px";
            yo.style.top = (CoorY.value * red - tam / 2) + "px";
            if (CoorX.value < 0 || CoorX.value * red > cont.offsetWidth
                    || CoorY.value < 0 || CoorY.value * red > cont.offsetHeight)
                    yo.hidden = true;
            else
                    yo.hidden = false;
            }

            function cambio_container(){
            document.getElementById("cambio_cont").value = "SI";
            document.getElementById("datacenterform").submit();
            }
        </script>

        <style type="text/css">
            .container2{height:300px}
            #status{position:fixed;left:0px;top:0px;width:100%;height:140px;overflow:hidden}
            #status div{background-color:rgba(13, 13, 13, 0.5);width:100%;height:100%;padding:10px 10px 10px 10px;font:13px bold sans-serif;color:#fff}
        </style>


        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/maptalks@0.40.3/dist/maptalks.css">
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/maptalks@0.40.3/dist/maptalks.min.js"></script>
    </head>
    <body>
        <?php include( 'header_dcim.inc.php' ); ?>
        <div class="container">
            <div class="page1">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li><a href="location_list.php">Location</a></li>
                            <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Location</li>
                        </ol>
                    </div>
                </div>
                <div class="col-sm-12">
                    <?php
                    // include( 'sidebar.inc.php' );

                    echo '<div class="main">
    <div class="">
<h3>', $status, '</h3>
<div class="table-center"><div>
<form id="datacenterform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Location</strong></div>
    <div class="panel-body">
    
<div>
<div class="form-group">
   <label class="col-sm-3" for="PortID">', __("Name"), '</label>
       <div class="col-sm-9">   
        <select name="PortID" class="form-control" id="PortID" onChange="form.submit()">
      <option value="0">', __("New Location"), '</option>';

                    foreach ($mfgList as $mfgRow) {
                        if ($mfg->PortID == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                    }

                    echo '	</select></div>
</div>
</div>
<div class="form-group">
       <label class="col-sm-3" for="name">', __("Name"), '</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="', $mfg->Name, '">
        </div>   
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="slug">', __("Slug"), '</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="slug" id="name" maxlength="40" value="', $mfg->Slug, '">
        </div>   
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="status">', __("Status"), '</label>
       <div class="col-sm-9">  
        <select name="status" id="" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
                    if ($mfg->Status == "Active") {
                        $active_select = " selected";
                    } else if ($mfg->Status == "Inactive") {
                        $inact_select = " selected";
                    } else {
                        $inact_select = $active_select = "";
                    }
                    echo '<option value="Active" ' . $active_select . '>', __("Active"), '</option>
            <option value="Inactive" ' . $inact_select . '>', __("Inactive"), '</option>
        </select>
       </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="facility">', __("Facility"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="facility" id="facility" value="', $mfg->Facility, '">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="asn">', __("ASN"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="asn" id="asn" value="', $mfg->ASN, '">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="time_zone">', __("Time Zone"), '</label>
       <div class="col-sm-9">      
        <select name="time_zone" id="time_zone" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
                    if ($mfg->Time_zone == "UTC") {
                        $UTCselect = " selected";
                    } else if ($mfg->Time_zone == "PST") {
                        $PSTselect = " selected";
                    } else if ($mfg->Time_zone == "CST") {
                        $CSTselect = " selected";
                    } else if ($mfg->Time_zone == "EST") {
                        $ESTselect = " selected";
                    } else if ($mfg->Time_zone == "EDT") {
                        $EDTselect = " selected";
                    } else if ($mfg->Time_zone == "CIT") {
                        $CITselect = " selected";
                    } else if ($mfg->Time_zone == "EIT") {
                        $EITselect = " selected";
                    } else if ($mfg->Time_zone == "WIT") {
                        $WITselect = " selected";
                    } else {
                        $UTCselect = $PSTselect = $CSTselect = $ESTselect = $EDTselect = $CITselect = $EITselect = $WITselect = "";
                    }
                    echo '<option value="UTC" "', $UTCselect, '">', __("UTC"), '</option>
            <option value="PST" ' . $PSTselect . '>', __("PST"), '</option>
            <option value="CST" ' . $CSTselect . '>', __("CST"), '</option>
            <option value="EST" ' . $ESTselect . '>', __("EST"), '</option>
            <option value="EDT" ' . $EDTselect . '>', __("EDT"), '</option>
            <option value="CIT" ' . $CITselect . '>', __("CIT"), '</option>
            <option value="EIT" ' . $EITselect . '>', __("EIT"), '</option> 
            <option value="WIT" ' . $WITselect . '>', __("WIT"), '</option>
        </select>   
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="description">', __("Description"), '</label>
       <div class="col-sm-9">  
       <input type="text" class="form-control" name="description" id="description" value="', $mfg->Description, '">
       </div>
    </div>
</div>
</div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Contact Info</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="Physical Address">', __("Physical Address"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="physical_address" id="physical_address" maxlength="40" value="', $mfg->Physical_address, '">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="Shipping_address">', __("Shipping Address"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="shipping_address" id="shipping_address" maxlength="40" value="', $mfg->Shipping_address, '">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="contact_name">', __("Contact Name"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="contact_name" id="contact_name" maxlength="40" value="', $mfg->Contact_name, '">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="contact_no">', __("Contact No"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="contact_no" id="contact_no" maxlength="11" value="', $mfg->Contact_no, '">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="contact_email">', __("Contact Email"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="contact_email" id="contact_email" maxlength="40" value="', $mfg->Contact_email, '">
           </div>    
        </div>
    </div>
</div> 
<div class="panel panel-default">
    <div class="panel-heading"><strong>Tags</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="tag">', __("Tag"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="tag" id="tag" value="', $mfg->Tag, '">
           </div>    
        </div>
    </div>
</div>   
<div class="panel panel-default">
    <div class="panel-heading"><strong>Comments</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="comments">', __("Comments"), '</label>
           <div class="col-sm-9">      
           <textarea class="form-control" name="comment" id="comment">', $mfg->Comment, '</textarea>
           </div>    
        </div>
    </div>
</div>  
<div><input type="hidden" name="cambio_cont" id="cambio_cont" value=""></div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Location Info</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="latitude">', __("Latitude"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="latitude" id="x" maxlength="40" value="', $mfg->Latitude, '">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="longitude">', __("Longitude"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="longitude" id="y" maxlength="40" value="', $mfg->Longitude, '">
           </div>    
        </div>
        <div class="clearfix"></div>';
                    print "<div id=map class=container2>\n";
                    print "</div>";
                    if ($mfg->PortID > 0) {
                        
                        if ($_SERVER['HTTP_HOST'] == "localhost") {
                            $db = new mysqli('localhost', 'root', '', 'yanto_dcim');
                        } else {
                            $db = new mysqli('localhost', 'root', 'Admin1@#4', 'yanto_dcim');
                        }

                        //$sql = "SELECT MapX as 'lat', MapY as 'lng' FROM `fac_DataCenter` WHERE DataCenterID='$dc->DataCenterID'";
                        $sql = "SELECT latitude as 'lat', longitude as 'lng' FROM location WHERE id='$mfg->PortID'";

                        $res = $db->query($sql);
                        $places = array();

                        while ($row = $res->fetch_assoc()) {
                            $places[] = $row;
                        }

                        $mapping = "[";

                        foreach ($places as $key) {
                            $mapping .= $key['lat'] . ",";
                            $mapping .= $key['lng'] . "]" . ",";
                            // echo "["."'".$key['name']."'".",";
                            // echo $key['lat'].",";
                            // echo $key['lng']."]".",";
                            // echo "<br>";
                        }

                        $mapping = substr_replace($mapping, "", -1);
                    } else {
                        $mapping = "[114.815640,-3.450670],";
                        $mapping = substr_replace($mapping, "", -1);
                    }
                    echo '</div>
</div>';

                    echo '<div class="text-center">';
                    if ($mfg->PortID > 0) {
                        echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">', __("Update"), '</button>';
                    } else {
                        echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">', __("Create"), '</button>';
                    }
                    echo '&nbsp;<a href="location_list.php" class="btn-panel btn-success">', __("Cancel"), '</a>';
                    ?>
                </div>
            </div> <!-- END div.table -->
        </form>
        <?php echo '
			<div id="imageselection" title="Image file selector">
				', $imageselect, '
			</div>
</div></div>
<!-- hiding modal dialogs here so they can be translated easily -->
<div class="hide">
	<div title="', __("Data Center Deletion Confirmation"), '" id="deletemodal">
		<div id="modaltext"><img src="images/mushroom_cloud.jpg" class="floatleft">', __("Are you sure that you want to delete this data center and all contents within it?"), '
			&nbsp;&nbsp;<select><option value="delete">', __("Delete"), '</option></select></p>
		</div>
	</div>
</div>

'; ?>
    </div><!-- END div.main -->
    <script>
                var mapping = <?php echo json_encode($mapping) ?>;
                var mapping = JSON.parse(mapping);
                var map = new maptalks.Map('map', {
                center: mapping,
                        zoom: 17,
                        centerCross: true,
                        zoomControl : true,
                        baseLayer: new maptalks.TileLayer('base', {
                        urlTemplate: 'http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png',
                                subdomains: ['a', 'b', 'c', 'd'],
                                attribution: '&copy; <a href="http://vioint.co.id">VIO DCIM</a> '
                        }),
                        layers: [
                                new maptalks.VectorLayer('v')
                        ]
                });
                map.on('zoomend moving moveend', getStatus);
                getStatus();
                function getStatus() {
                var extent = map.getExtent(),
                        ex = [
                                '{',
                                'xmin:' + extent.xmin.toFixed(5),
                                ', ymin:' + extent.ymin.toFixed(5),
                                ', xmax:' + extent.xmax.toFixed(5),
                                ', ymax:' + extent.xmax.toFixed(5),
                                '}'
                        ].join('');
                var center = map.getCenter();
                var mapStatus = [
                        'Center : [' + [center.x.toFixed(5), center.y.toFixed(5)].join() + ']',
                ];
                CoorX = document.getElementById("x");
                CoorX.value = center.x.toFixed(5);
                CoorY = document.getElementById("y");
                CoorY.value = center.y.toFixed(5);
                // document.getElementById('status').innerHTML = '<div>' + mapStatus.join('<br>') + '</div>';
                }


    </script>
</body>
</html>
