<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Rack");
$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

$mfg = new Rack();
$room = new Room();
$Simulation = new RackSimulation();

// AJAX Start
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

// END - AJAX

if (isset($_REQUEST["PortID"]) && $_REQUEST["PortID"] > 0) {
    $mfg->PortID = (isset($_POST['PortID']) ? $_POST['PortID'] : $_GET['PortID']);
    $mfg->GetOrderByID();


    $RoomDetails = $Simulation->GetRoomDetails($mfg->Site);
    $RoomDetails_res = json_decode(json_encode($RoomDetails), true);

    $groups = $RoomDetails_res['group_columns'] * $RoomDetails_res['group_rows'];


    $RackDetails = $Simulation->GetRackDetails($mfg->Site);
    $RackDetails_res = json_decode(json_encode($RackDetails), true);

    $available_arr = array();
    if (count($RackDetails_res) > 0) {
        foreach ($RackDetails_res as $val) {
            if ($val['group_no'] == $group_no) {
                $available_arr[$val['row_position']] = $val['row_position'];
            }
        }
    }
    $total_group = $RoomDetails_res['rows_per_rack'];

    //print_r($mfg);exit;
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Site = trim($_POST["site"]);
    $mfg->Group_no = trim($_POST["group_no"]);
    $mfg->Row_position = trim($_POST["row_position"]);
    $mfg->Facility = trim($_POST["facility"]);
    $mfg->Serial_no = trim($_POST["serial_no"]);
    $mfg->Descending = trim($_POST["descending"]);
    $mfg->Type = trim($_POST["type"]);
    $mfg->Width = trim($_POST["width"]);
    $mfg->Height = trim($_POST["height"]);
    $mfg->Position = trim($_POST["position"]);
    $mfg->Model = trim($_POST["model"]);
    $mfg->Key_info = trim($_POST["key_info"]);
    $mfg->Max_kw = trim($_POST["max_kw"]);
    $mfg->Max_weight = trim($_POST["max_weight"]);
    $mfg->Installed_at = trim($_POST["installed_at"]);
    $mfg->Assign_to = trim($_POST["assign_to"]);
    $mfg->Tag = trim($_POST["tag"]);
    $mfg->Comment = trim($_POST["comment"]);
    $mfg->X1 = trim($_POST["x1"]);
    $mfg->Y1 = trim($_POST["y1"]);
    $mfg->X2 = trim($_POST["x2"]);
    $mfg->Y2 = trim($_POST["y2"]);
    $mfg->Mapzoom = trim($_POST["mapzoom"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("rack.php?PortID=$mfg->PortID"));
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
$mfgList = $mfg->GetRackList();
$SiteList = $room->GetRoomList();
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
        <link rel="stylesheet" href="css/datepicker.css" type="text/css">
        <link rel="stylesheet" href="css/imgareaselect-default.css" type="text/css">
        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css">
        <![endif]-->
        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>
        <script type="text/javascript" src="scripts/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="scripts/jquery.imgareaselect.pack.js"></script>

        <style type="text/css">
            #using { margin-top: 1em; }
        </style>

        <script type="text/javascript">
            $(document).ready(function () {
                $('#installed_at').datepicker({
                    format: 'm/d/yyyy',
                    autoclose: true
                });
                $('#mform').validationEngine({});
                $('#PortID').change(function (e) {
                    location.href = 'rack.php?PortID=' + this.value;
                });
                // Show number of templates using manufacturer
                UpdateCount();

                $('button[name="action"][value="Delete"]').click(DeleteObject);
            });

            function UpdateCount(e) {
                var count;
                $.ajax({
                    type: 'get',
                    async: false,
                    data: {getTemplateCount: $('#PortID').val()},
                    success: function (data) {
                        $('#count').text(data.length);
                        count = data.length;
                    }
                });
                return count;
            }

            function DeleteObject() {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $('#PortID').val(), TransferTo: transferto, action: 'Delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }

        </script>
    </head>
    <body>
        <?php include( 'header_dcim.inc.php' ); ?>
        <div class="container">
            <div class="page1">
                <!-- Breadcrumb code start -->
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li><a href="rack_list.php">Rack</a></li>
                            <li><?php echo $mfg->PortID != "" ? 'Edit' : 'Add'; ?> Rack</li>
                        </ol>
                    </div>
                </div>
                <!-- Breadcrumb code end -->
                <div class="col-sm-12">

                    <?php
                    // include( "sidebar.inc.php" );

                    echo '<div class="main">
    <div class="">
<h3>', $status, '</h3>
<div class="table-center"><div>
<form id="mform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Rack</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">', __("Name"), '</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>', __("New Rack"), '</option>';

                    foreach ($mfgList as $mfgRow) {
                        if ($mfg->PortID == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                    }

                    echo '	</select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">', __("Name"), '</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="', $mfg->Name, '">
        </div>   
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="site">', __("Room"), '</label>
        <div class="col-sm-9">  
       <select name="site" id="site" class="form-control" onchange="room_change(this.value)">
            <option value="">', __("-- Select --"), '</option>';
                    foreach ($SiteList as $mfgRow) {
                        if ($mfg->Site == $mfgRow->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                    }
                    echo '
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="group_no">', __("Group No"), '</label>
        <div class="col-sm-9">  
       <select name="group_no" id="group_no" class="form-control" onchange="group_change(this.value)">
            <option value="">', __("-- Select --"), '</option>';
                    for ($i = 1; $i <= $groups; $i++) {
                        if ($mfg->Group_no == $i) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$i\"$selected>$i</option>\n";
                    }
                    echo '
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="row_position">', __("Rack Position"), '</label>
        <div class="col-sm-9">  
        <select name="row_position" id="row_position" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
                    for ($i = 1; $i <= $total_group; $i++) {
                        if (!array_key_exists($i, $available_arr)) {
                            if ($mfg->Row_position == $i) {
                                $selected = " selected";
                            } else {
                                $selected = "";
                            }
                            echo "<option value=\"$i\"$selected>$i</option>\n";
                        }
                    }
                    echo '</select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="facility">', __("Facility"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="facility" id="facility" value="', $mfg->Facility, '">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="serial_no">', __("Serial No"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="serial_no" id="serial_no" value="', $mfg->Serial_no, '">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="position">', __("U1 Position"), '</label>
       <div class="col-sm-9">      
       <select name="position" id="position" class="form-control">
            <option value="Default">', __("Default"), '</option>
            <option value="Top">', __("Top"), '</option>
            <option value="Bottom">', __("Bottom"), '</option>
        </select>
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="model">', __("Model"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="model" id="model" value="', $mfg->Model, '">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="key_info">', __("Key/Lock Information"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="key_info" id="key_info" value="', $mfg->Key_info, '">
       </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="max_kw">', __("Maximum KW"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="max_kw" id="max_kw" value="', $mfg->Max_kw, '">
       </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="max_weight">', __("Maximum Weight"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="max_weight" id="max_weight" value="', $mfg->Max_weight, '">
       </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="installed_at">', __("Date of Installation"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="installed_at" id="installed_at" value="', date("m/d/Y", strtotime($mfg->Installed_at)), '">
       </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="assign_to">', __("Assig To"), '</label>
       <div class="col-sm-9">      
       <select name="assign_to" id="assign_to" class="form-control">
            <option value="General Use">', __("General Use"), '</option>
        </select>
       </div>    
    </div>
</div>
</div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Dimensions</strong></div>
    <div class="panel-body">
        <div class="form-group">
            <label class="col-sm-3" for="type">', __("Type"), '</label>
            <div class="col-sm-9">      
             <select name="type" id="type" class="form-control">
                 <option value="">', __("-- Select --"), '</option>';
                    if ($mfg->Type == "2 Post Frame") {
                        $select1 = " selected";
                    } else if ($mfg->Type == "4 Post Frame") {
                        $select2 = " selected";
                    } else if ($mfg->Type == "4 Post Cabinet") {
                        $select3 = " selected";
                    } else if ($mfg->Type == "Wall-mounted Frame") {
                        $select4 = " selected";
                    } else if ($mfg->Type == "Wall-mounted Cabinet") {
                        $select5 = " selected";
                    } else {
                        $select1 = $select2 = $select3 = $select4 = $select5 = "";
                    }
                    echo '<option value="2 Post Frame" ' . $select1 . '>', __("2 Post Frame"), '</option>
                 <option value="4 Post Frame" ' . $select2 . '>', __("4 Post Frame"), '</option>
                 <option value="4 Post Cabinet" ' . $select3 . '>', __("4 Post Cabinet"), '</option>
                 <option value="Wall-mounted Frame" ' . $select4 . '>', __("Wall-mounted Frame"), '</option>
                 <option value="Wall-mounted Cabinet" ' . $select5 . '>', __("Wall-mounted Cabinet"), '</option>
             </select>   
         </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="width">', __("Width"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="width" id="width" maxlength="40" value="', $mfg->Width, '">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="height">', __("Height"), '</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="height" id="height" maxlength="40" value="', $mfg->Height, '">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
            <div class="col-sm-9 col-sm-offset-3">    
            <input type="checkbox" class="" name="descending" id="descending" value="', $mfg->Descending == "" ? "N" : $mfg->Descending, '" ', $mfg->Descending == "Y" ? "checked" : "", '>
            <label for="Descending units">', __("Descending units"), '</label></br>
            <span>Units are numbered top-to-bottom</span>    
            </div>
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

<div class="panel panel-default">
    <div class="panel-heading"><strong>Map Area</strong></div>
    <div class="panel-body">
        <div class="form-group">
            <label class="col-sm-3" for="x1">X1</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" required="" name="x1" id="x1" value="',$mfg->X1,'">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="y1">Y1</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" required="" name="y1" id="y1" value="',$mfg->Y1,'">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="x2">X2</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" required="" name="x2" id="x2" value="',$mfg->X2,'">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="y2">Y2</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" required="" name="y2" id="y2" value="',$mfg->Y2,'">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="zoom">',__("Zoom"),' (%)</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" required="" name="mapzoom" id="mapzoom" value="',$mfg->MapZoom!=""?$mfg->MapZoom:100,'">
            </div>    
        </div>
    </div>
</div>
                                        
<div class="text-center">';
                    if ($mfg->PortID > 0) {
                        echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">', __("Update"), '</button>';
                    } else {
                        echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">', __("Create"), '</button>';
                    }
                    echo '&nbsp;<a href="rack_list.php" class="btn-panel btn-success">', __("Cancel"), '</a>';
                    ?>
                </div>
            </div>
        </div>
    </div><!-- END div.table -->

</form>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Room Area</strong></div>
    <div class="panel-body">
<?php
if (count($RoomDetails_res) > 0 && $RoomDetails_res['picture'] != "") {
    echo '<p class="instructions text-danger">', __("Note : Click and drag on the image to select an area for this rack"), '</p>
        <div style="">
            <img src="css/blank.gif" height=', $height, ' width=', $width, '>
            <div class="frame" style="margin: 0 0.3em;"> 
                <img id="map" height="auto" width="100%" src="uploads/room/', $RoomDetails_res['picture'], '"> 
            </div>
        </div>';
} else {
    echo '<p class="instructions text-danger">', __("Note : Click and drag on the image to select an area for this rack"), '</p>
        <div style="">
            <img src="css/blank.gif" height="auto" width="auto">
            <div class="frame" style="margin: 0 0.3em;"> 
                <img id="map" height="auto" width="100%" src="uploads/room/DC_GSPE2-Model.jpg"> 
            </div>
        </div>';
}
?>
    </div>
</div>
</div></div>

</div><!-- END div.main -->
</div><!-- END div.page -->
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
    $(document).ready(function () {
        $('#descending').click(function () {
            if ($(this).prop("checked") == true) {
                $(this).val("Y");
            } else if ($(this).prop("checked") == false) {
                $(this).val("N");
            }
        });
        
		$('#map').imgAreaSelect( {
	<?php
		printf( "\t\tx1: %d,\n\tx2: %d,\n\ty1: %d,\n\ty2: %d,\n", $mfg->MapX1, $mfg->MapX2, $mfg->MapY1, $mfg->MapY2 );
	?>
			handles: true,
			onSelectChange: preview
		});
    });
    function preview(img, selection) {
        if (!selection.width || !selection.height){
                return;
        }
        $('#x1').val(selection.x1);
        $('#y1').val(selection.y1);
        $('#x2').val(selection.x2);
        $('#y2').val(selection.y2);
    }
    function room_change(room_id) {
        $.ajax({
            url: 'ajax_get_group.php',
            type: 'post',
            data: {room_id: room_id, is_group: "Y"},
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#group_no").html(res.res.option);
                    //$("#map").append(res.res.img);
                    document.getElementById("map").src = res.res.img;
                    $('#map').imgAreaSelect({
                        <?php
                            printf("\t\tx1: %d,\n\tx2: %d,\n\ty1: %d,\n\ty2: %d,\n", 10, 10, 10, 10);
                        ?>
			handles: true,
			onSelectChange: preview
                    }).trigger;
                }
            }
        });
    }
    function group_change(group_no) {
        var room_id = $("#site").val();
        $.ajax({
            url: 'ajax_get_group.php',
            type: 'post',
            data: {group_no: group_no, room_id: room_id, is_position: "Y"},
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#row_position").html(res.res);
                }
            }
        });
    }
</script>
