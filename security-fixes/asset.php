<?php
require_once("db.inc.php");
require_once("facilities.inc.php");
require_once("security.inc.php"); // HIGH-011 & HIGH-012: XSS Protection

$subheader = __("Port Compatibility Listing");
$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

// CRITICAL-005: Validate CSRF token on POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        die("Error: Invalid or expired security token. Please refresh the page and try again.");
    }
}

$mfg = new Asset();
$model = new Model();
$location = new Location();
$manufacture = new Manufacture();
$rack = new Rack();
$room = new Room();
$asset_cat = new AssetCategory();

$model_list = $model->GetAssetModelList();
$status_list = $mfg->GetStatusList();
$supplier_list = $mfg->GetSupplierList();
$location_list = $location->GetLocationList();
$manufactureList = $manufacture->GetManufactureList();
$roomList = $room->GetRoomList();
$rackList = $rack->GetRackList();
$asset_cat_list = $asset_cat->GetCategoryList();

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

    $roomList = $room->GetLocationRoomList($mfg->Location);
    $rackList = $rack->GetLocationRoomList($mfg->Room);
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Asset_tag = trim($_POST["asset_tag"]);
    $mfg->Model = trim($_POST["model"]);
    $mfg->Status = trim($_POST["status"]);
    $mfg->Serial_no = trim($_POST["serial_no"]);
    $mfg->Purchase_date = trim($_POST["purchase_date"]);
    $mfg->First_main_date = trim($_POST["first_main_date"]);
    $mfg->Maintenance = trim($_POST["maintenance"]);
    $mfg->Supplier = trim($_POST["supplier"]);
    $mfg->Order_no = trim($_POST["order_no"]);
    $mfg->Purchase_cost = trim($_POST["purchase_cost"]);
    $mfg->Warranty = trim($_POST["warranty"]);
    $mfg->Notes = trim($_POST["notes"]);
    $mfg->Location = trim($_POST["location"]);
    $mfg->Requestable = trim($_POST["requestable"]);
    $mfg->Asset_image = trim($_FILES['asset_image']["name"]);
    $mfg->Asset_img = trim($_POST["asset_img"]);
    $mfg->Room = trim($_POST["room"]);
    $mfg->Rack = trim($_POST["rack"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("asset.php?PortID=$mfg->PortID"));
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
$mfgList = $mfg->GetAssetList();


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
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/datepicker.css" type="text/css">
  
  
  <!--[if lt IE 9]>
  <link rel="stylesheet"  href="css/ie.css" type="text/css">
  <![endif]-->
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
  <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
  <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>
  <script type="text/javascript" src="scripts/bootstrap-datepicker.js"></script>
  <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>-->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

  <style type="text/css">
	#using { margin-top: 1em; }
  </style>

  <script type="text/javascript">
	$(document).ready(function() {
            $('#purchase_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            });
            $('#first_main_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            });
            /* $('#manufacture_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            }); */
            $('#mform').validationEngine({});
            $('#PortID').change(function(e){
                    location.href='asset.php?PortID='+this.value;
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
                        count=data.length;
                }
            });
            return count;
	}

	function DeleteObject(){
            // If manufacturerid unset then just delete 
            transferto=(typeof(objectid)=='undefined')?0:objectid;
            $.post('',{
                PortID: $('#PortID').val(), 
                TransferTo: transferto, 
                action: 'Delete',
                csrf_token: <?php echo js_escape(csrf_token()); ?>
            },function(data){
                if(data){
                        location.href='';
                }else{
                        alert("Something's gone horrible wrong");
                }
            });	
	}

  </script>
</head>
<body>
<?php include('header_dcim.inc.php'); ?>
<div class="container">
<div class="page1">
    <!-- Breadcrumb code start -->
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <ol class="breadcrumb">
                <li><a href="index_dcim.php">Dashboard</a></li>
                <li><a href="asset_list.php">Assets</a></li>
                <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Assets</li>
            </ol>
        </div>
    </div>
    <!-- Breadcrumb code end -->
<div class="col-sm-12">

<?php
	// include( "sidebar.inc.php" );

echo '<div class="main">
    <div class="">
    <!-- Create Model Modal -->
    <div class="modal fade" id="create_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Asset Model</h4>
            </div>
            <div class="modal-body">
              <form id="model_form">
                ', csrf_token_field(), '
                <div class="form-group">
                  <label for="name" class="col-form-label">Name:</label>
                  <input type="text" class="form-control" id="name" name="name">
                </div>
                <div class="form-group">
                    <label for="manufacture" class="col-form-label">Manufacture:</label>
                    <select class="form-control" id="manufacture" name="manufacture">
                        <option vlaue="">-- Select --</option>';
                        foreach ($manufactureList as $val) {
                            echo "<option value=\"" . html_escape($val->PortID) . "\">" . html_escape($val->Name) . "</option>\n";
                        }
                    echo '</select>
                </div>
                <div class="form-group">
                    <label for="category_name" class="col-form-label">Category:</label>
                    <select class="form-control" id="category_name" name="category_name">
                        <option vlaue="">-- Select --</option>';
                        foreach ($asset_cat_list as $val) {
                            echo "<option value=\"" . html_escape($val->PortID) . "\">" . html_escape($val->Name) . "</option>\n";
                        }
                    echo '</select>
                </div>
                <div class="form-group">
                  <label for="model_no" class="col-form-label">Model No:</label>
                  <input type="text" class="form-control" id="model_no" name="model_no">
                </div>
                <div class="form-group">
                    <label for="fieldset" class="col-form-label">Fieldset:</label>
                    <select class="form-control" id="fieldset" name="fieldset">
                        <option vlaue="">-- Select --</option>
                        <option vlaue="mac_address">Asset with MAC address</option>
                    </select>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" id="model_submit" class="btn btn-primary">Save</button>
            </div>
          </div>
        </div>
    </div>
    <!-- END Modal -->
    <!-- Status Modal -->
    <div class="modal fade" id="create_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Asset Status</h4>
            </div>
            <div class="modal-body">
              <form id="status_form">
                ', csrf_token_field(), '
                <div class="form-group">
                  <label for="status" class="col-form-label">Status:</label>
                  <input type="text" class="form-control" id="status_name" name="status_name">
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" id="status_submit" class="btn btn-primary">Save</button>
            </div>
          </div>
        </div>
    </div>
    <!-- END OF MODAL CODE -->
    <!-- Supplier Modal -->
    <div class="modal fade" id="create_supplier" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Asset Supplier</h4>
            </div>
            <div class="modal-body">
              <form id="supplier_form">
                ', csrf_token_field(), '
                <div class="form-group">
                  <label for="status" class="col-form-label">Supplier:</label>
                  <input type="text" class="form-control" id="supplier_name" name="supplier_name">
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" id="supplier_submit" class="btn btn-primary">Save</button>
            </div>
          </div>
        </div>
    </div>
    <!-- END OF MODAL CODE -->
    <!-- Location Modal -->
    <div class="modal fade" id="create_location" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Asset Location</h4>
            </div>
            <div class="modal-body">
              <form id="location_form">
                ', csrf_token_field(), '
                <div class="form-group">
                  <label for="location" class="col-form-label">Name:</label>
                  <input type="text" class="form-control" id="location_name" name="location_name">
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" id="location_submit" class="btn btn-primary">Save</button>
            </div>
          </div>
        </div>
    </div>
    <!-- END OF MODAL CODE -->
<h3>', html_escape($status), '</h3>
<div class="table-center"><div>
<form id="mform" method="POST" enctype="multipart/form-data">
', csrf_token_field(), '
<div class="panel panel-default">
    <div class="panel-heading"><strong>Asset Management</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">', __("Name"), '</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>', __("New Asset"), '</option>';

foreach ($mfgList as $mfgRow) {
    if ($mfg->PortID == $mfgRow->PortID) {
        $selected = " selected";
    } else {
        $selected = "";
    }
    echo "<option value=\"" . html_escape($mfgRow->PortID) . "\"$selected>" . html_escape($mfgRow->Name) . "</option>\n";
}

    echo '	</select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">', __("Name"), '</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="', html_escape($mfg->Name), '">
        </div>   
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="asset_tag">', __("Asset Tag"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="asset_tag" id="asset_tag" value="', html_escape($mfg->Asset_tag), '">
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="model">', __("Model"), '</label>
       <div class="col-sm-7">  
        <select name="model" id="model" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
                foreach ($model_list as $val) {
                    if ($mfg->Model == $val->PortID) {
                        $selected = " selected";
                    } else {
                        $selected = "";
                    }
                    echo "<option value=\"" . html_escape($val->PortID) . "\" $selected>" . html_escape($val->Name) . "</option>\n";
                }    
        echo '</select>
       </div>    
       <div class="col-sm-2">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#create_model">New</button> 
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="status">', __("Status"), '</label>
       <div class="col-sm-7">  
        <select name="status" id="status" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
            
            foreach ($status_list as $val) {
                if ($mfg->Status == $val->PortID) {
                    $selected = " selected";
                } else {
                    $selected = "";
                }
                echo "<option value=\"" . html_escape($val->PortID) . "\" $selected>" . html_escape($val->Status_name) . "</option>\n";
            }
        echo '</select>
       </div>    
       <div class="col-sm-2">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#create_status">New</button> 
       </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="serial_no">', __("Serial Number"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="serial_no" id="serial_no" value="', html_escape($mfg->Serial_no), '">
        </div>    
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="purchase_date">', __("Purchase Date"), '</label>
        <div class="input-group col-sm-9" style="padding-left: 16px; padding-right: 14px;">
        <input type="text" class="form-control" name="purchase_date" id="purchase_date" value="', ($mfg->Purchase_date!="0000-00-00"?html_escape(date('m/d/Y',strtotime($mfg->Purchase_date))):""),'">
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
        </div>    
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="first_main_date">', __("First Maintannce Date"), '</label>
        <div class="input-group col-sm-9" style="padding-left: 16px; padding-right: 14px;">
        <input type="text" class="form-control" name="first_main_date" id="first_main_date" value="', ($mfg->First_main_date!="0000-00-00"?html_escape(date('m/d/Y',strtotime($mfg->First_main_date))):""),'">
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
        </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="maintenance">', __("Maintenance Every"), '</label>
       <div class="input-group col-sm-9" style="padding-left: 16px; padding-right: 14px;">
       <input type="text" class="form-control" name="maintenance" id="maintenance" onchange="change_maintenance(this.value)" value="', html_escape($mfg->Maintenance), '">
        <span class="input-group-addon">
            Months
        </span>
       </div>
    </div>';
        
    $next_main_date = date('m/d/Y', strtotime("+".$mfg->Maintenance." months", strtotime($mfg->First_main_date)));
    
    echo '<div class="form-group">
        <label class="col-sm-3" for="next_main_date">', __("Next Maintannce Date"), '</label>
        <div class="input-group col-sm-9" style="padding-left: 16px; padding-right: 14px;">
        <input type="text" class="form-control" name="next_main_date" id="next_main_date" value="', html_escape($next_main_date),'" readonly>
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
        </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="supplier">', __("Supplier"), '</label>
       <div class="col-sm-7">
        <select name="supplier" id="supplier" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
            foreach ($supplier_list as $val) {
                if ($mfg->Supplier == $val->PortID) {
                    $selected = " selected";
                } else {
                    $selected = "";
                }
                echo "<option value=\"" . html_escape($val->PortID) . "\" $selected>" . html_escape($val->Name) . "</option>\n";
            }
        echo '</select>
       </div>   
       <div class="col-sm-2">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#create_supplier">New</button> 
       </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="order_no">', __("Order Number"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="order_no" id="order_no" value="', html_escape($mfg->Order_no), '">
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="purchase_cost">', __("Purchase Cost"), '</label>
       <div class="input-group col-sm-9" style="padding-left: 16px; padding-right: 14px;">      
       <input type="text" class="form-control" name="purchase_cost" id="purchase_cost" value="', html_escape($mfg->Purchase_cost), '">
        <span class="input-group-addon">
            USD
        </span>
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="warranty">', __("Warranty"), '</label>
       <div class="input-group col-sm-9" style="padding-left: 16px; padding-right: 14px;">
       <input type="text" class="form-control" name="warranty" id="warranty" value="', html_escape($mfg->Warranty), '">
        <span class="input-group-addon">
            Months
        </span>
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="notes">', __("Notes"), '</label>
       <div class="col-sm-9">
       <textarea name="notes" class="form-control">', html_escape($mfg->Notes), '</textarea>
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="location">', __("Default Location"), '</label>
       <div class="col-sm-7">
        <select name="location" id="location" class="form-control" onchange="change_location(this.value)">
            <option value="">', __("-- Select --"), '</option>';
            foreach ($location_list as $val) {
                if ($mfg->Location == $val->PortID) {
                    $selected = " selected";
                } else {
                    $selected = "";
                }
                echo "<option value=\"" . html_escape($val->PortID) . "\" $selected>" . html_escape($val->Name) . "</option>\n";
            }
        echo '</select>
       </div>
       <div class="col-sm-2">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#create_location">New</button> 
       </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-3" for="room">', __("Default Room"), '</label>
       <div class="col-sm-9">
        <select name="room" id="room" class="form-control" onchange="change_room(this.value)">
            <option value="">', __("-- Select --"), '</option>';
            foreach ($roomList as $mfgRow) {
                if ($mfg->Room == $mfgRow->PortID) {
                    $selected = " selected";
                } else {
                    $selected = "";
                }
                echo "<option value=\"" . html_escape($mfgRow->PortID) . "\"$selected>" . html_escape($mfgRow->Name) . "</option>\n";
            }
        echo '</select>
       </div>
       <div class="clearfix"></div>
       <div class="form-group">
       <label class="col-sm-3" for="rack">', __("Default Rack"), '</label>
       <div class="col-sm-9">
        <select name="rack" id="rack" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
            foreach ($rackList as $mfgRow) {
                if ($mfg->Rack == $mfgRow->PortID) {
                    $selected = " selected";
                } else {
                    $selected = "";
                }
                echo "<option value=\"" . html_escape($mfgRow->PortID) . "\"$selected>" . html_escape($mfgRow->Name) . "</option>\n";
            }
        echo '</select>
       </div>
    </div>
    <div class="form-group">
        <label for="requestable" class="col-sm-3">', __("Requestable"), '</label>
        <div class="col-sm-1">    
        <input type="checkbox" class="" name="requestable" id="requestable" value="', html_escape($mfg->Requestable==""?"N":$mfg->Requestable), '" ', ($mfg->Requestable=="Y"?"checked":""),' >
        </div>
    </div>
    <div class="clearfix"></div>';
    
    $assetweb_path = _MEDIA_URL . "assets/" . html_escape($mfg->Asset_image);
    $assetfilename = _PATH . '/uploads/assets' . DIRECTORY_SEPARATOR . $mfg->Asset_image;

    $front_icon = "";
    $rear_icon = "";
    if (file_exists($assetfilename) && $mfg->Asset_image != "") {
        $rear_icon = "<a href='" . html_escape($assetweb_path) . "' target='__blank'>" . html_escape($mfg->Asset_image) . " <i class='fa fa-eye'></i></a>";
    }
    echo '<div class="form-group">
       <label class="col-sm-3" for="asset_image">', __("Upload Image"), '</label>
       <div class="col-sm-9">
       <input type="file" class="form-control" name="asset_image" id="asset_image" value="', html_escape($mfg->Asset_image), '">
       <output id="filesInfo"></output>
       <input type="hidden" name="asset_img" id="asset_img" value="', html_escape($mfg->Asset_img), '">  
       <input type="hidden" name="asset_img_val" id="asset_img_val" value="">  
       </div>
    </div>';
    if (file_exists($assetfilename) && $mfg->Asset_image) {
        echo '<div class="col-sm-4 col-sm-offset-3"><img src="'.html_escape($assetweb_path).'" class="image-responsive" style="height:100px;width:180px;"><br/><h4> Assets Picture</h4></div>';
    }
    
    echo '<div class="clearfix"></div>
<div class="text-center">';
	if ($mfg->PortID > 0) {
            echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">', __("Update"), '</button>';
	} else {
            echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">', __("Create"), '</button>';
	}
        echo '&nbsp;<a href="asset_list.php" class="btn-panel btn-success">', __("Cancel"), '</a>';
        ?>
</div>
    </div>
</div>
</div><!-- END div.table -->

</form>
</div></div>
</div><!-- END div.main -->
</div><!-- END div.page -->
</div>
</div>
</body>
<!-- Footer -->
<?php if ($footer_text != "") { ?>
    <footer class="page-footer font-small footer">
        <spam><?php echo html_escape($footer_text); ?></spam>
    </footer>
<?php } ?>
<!-- Footer -->
</html>
<script type="text/javascript">
$(document).ready(function(){
    $('#requestable').click(function(){
        if($(this).prop("checked") == true){
            $(this).val("Y");
        }
        else if($(this).prop("checked") == false){
            $(this).val("N");
        }
    });
    
    $("#first_main_date").change(function(){
        var days = $("#maintenance").val();
        change_maintenance(days);
    });
    $("#model_submit").click(function(){
        $.ajax({
            url: 'ajax_create_model.php',
            type: 'post',
            data: $("#model_form").serialize(),
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#model").html(res.res);
                    $('#create_model').modal('hide');
                }
            }
        });
    });
    
    $("#status_submit").click(function(){
        $.ajax({
            url: 'ajax_create_status.php',
            type: 'post',
            data: $("#status_form").serialize(),
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#status").html(res.res);
                    $('#create_status').modal('hide');
                }
            }
        });
    });
    
    $("#supplier_submit").click(function(){
        $.ajax({
            url: 'ajax_create_supplier.php',
            type: 'post',
            data: $("#supplier_form").serialize(),
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#supplier").html(res.res);
                    $('#create_supplier').modal('hide');
                }
            }
        });
    });
    
    $("#location_submit").click(function(){
        $.ajax({
            url: 'ajax_create_location.php',
            type: 'post',
            data: $("#location_form").serialize(),
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#location").html(res.res);
                    $('#create_location').modal('hide');
                }
            }
        });
    });
});  


function change_location(location_id) {

    $.ajax({
        url: 'get_room.php',
        type: 'post',
        data: {location_id: location_id, csrf_token: <?php echo js_escape(csrf_token()); ?>},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#room").html(res.res);
                $("#rack").html("");
            }
        }
    });
}

function change_room(room_id){
    $.ajax({
        url: 'get_room_rack.php',
        type: 'post',
        data: {room_id: room_id, csrf_token: <?php echo js_escape(csrf_token()); ?>},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#rack").html(res.res);
            }
        }
    });
}

/* function get_models() {
    $.ajax({
        url: 'get_models.php',
        type: 'post',
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#model").html(res.res);
            }
        }
    });
}

function get_status() {
    $.ajax({
        url: 'get_asset_status.php',
        type: 'post',
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#status").html(res.res);
            }
        }
    });
}

function get_supplier() {
    $.ajax({
        url: 'get_asset_supplier.php',
        type: 'post',
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#supplier").html(res.res);
            }
        }
    });
}
function get_location() {
    $.ajax({
        url: 'get_asset_location.php',
        type: 'post',
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#location").html(res.res);
            }
        }
    });
} */
    
    function change_maintenance(days){
        var tt = $("#first_main_date").val();
        
        var x = parseInt(days, 10);
        var newdate = new Date(tt);
        newdate.setMonth(newdate.getMonth() + x);
        
        var dd = newdate.getDate();
        var mm = newdate.getMonth() + 1;
        var y = newdate.getFullYear();

        var someFormattedDate = mm + '/' + dd + '/' + y;
        
        $("#next_main_date").val(someFormattedDate);
    }
    
    function frontfileSelect(evt) {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            var files = evt.target.files;

            var result = '';
            var file;
            for (var i = 0; file = files[i]; i++) {
                // if the file is not an image, continue
                if (!file.type.match('image.*')) {
                    continue;
                }

                reader = new FileReader();
                reader.onload = (function (tFile) {
                    return function (evt) {
                        var div = document.createElement('div');
                        div.innerHTML = '<img style="width: 50px; height:30px;" src="' + evt.target.result + '" />';
                        $("#asset_img_val").val(evt.target.result);
                        document.getElementById('filesInfo').appendChild(div);
                    };
                }(file));
                reader.readAsDataURL(file);
            }
        } else {
            alert('The File APIs are not fully supported in this browser.');
        }
    }
    
    document.getElementById('asset_image').addEventListener('change', frontfileSelect, false);
</script>
