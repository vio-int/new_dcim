<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Port Compatibility Listing");
$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

$mfg = new Model();
$manufacture = new Manufacture();
$asset_category = new AssetCategory();

$model_list = $mfg->GetAssetModelList();
$manufacture_list = $manufacture->GetManufactureList();
$category_list = $asset_category->GetCategoryList();

if (isset($_REQUEST["PortID"]) && $_REQUEST["PortID"] > 0) {
    $mfg->PortID = (isset($_POST['PortID']) ? $_POST['PortID'] : $_GET['PortID']);
    $mfg->GetOrderByID();
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Manufacture = trim($_POST["manufacture"]);
    $mfg->Category = trim($_POST["category"]);
    $mfg->Model_no = trim($_POST["model_no"]);
    $mfg->EOL = trim($_POST["eol"]);
    $mfg->Fieldset = trim($_POST["fieldset"]);
    $mfg->Note = trim($_POST["note"]);
    $mfg->Is_user_request = trim($_POST["is_user_request"]);
    $mfg->Model_image = trim($_FILES['model_image']["name"]);
    $mfg->Model_img = trim($_POST["model_img"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("asset_model.php?PortID=$mfg->PortID"));
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
$mfgList = $mfg->GetAssetModelList();


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
            $(document).ready(function () {

                $('#mform').validationEngine({});
                $('#PortID').change(function (e) {
                    location.href = 'asset_model.php?PortID=' + this.value;
                });

            });

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
                            <li><a href="asset_model_list.php">Models</a></li>
                            <li><?php echo $mfg->PortID != "" ? 'Edit' : 'Add'; ?> Assets Model</li>
                        </ol>
                    </div>
                </div>
                <!-- Breadcrumb code end -->
                <div class="col-sm-12">

                    <?php
                    // include( "sidebar.inc.php" );

                    echo '<div class="main">
    <div class="">
    <!-- Manufacture Modal -->
    <div class="modal fade" id="create_manufacture" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Manufacture</h4>
            </div>
            <div class="modal-body">
              <form id="manufacture_form">
                <div class="form-group">
                  <label for="manufacture_name" class="col-form-label">Manufacture:</label>
                  <input type="text" class="form-control" id="manufacture_name" name="manufacture_name">
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" id="manufacture_submit" class="btn btn-primary">Save</button>
            </div>
          </div>
        </div>
    </div>
    <!-- END OF MODAL CODE -->
    <!-- Category Modal -->
    <div class="modal fade" id="create_category" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Category</h4>
            </div>
            <div class="modal-body">
              <form id="category_form">
                <div class="form-group">
                  <label for="category_name" class="col-form-label">Category:</label>
                  <input type="text" class="form-control" id="category_name" name="category_name">
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" id="category_submit" class="btn btn-primary">Save</button>
            </div>
          </div>
        </div>
    </div>
    <!-- END OF MODAL CODE -->
<h3>', $status, '</h3>
<div class="table-center"><div>
<form id="mform" method="POST" enctype="multipart/form-data">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Model Management</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">', __("Name"), '</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>', __("New Model"), '</option>';

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
       <label class="col-sm-3" for="manufacture">', __("Manufacture"), '</label>
       <div class="col-sm-7">  
        <select name="manufacture" id="manufacture" class="form-control">
            <option value="">', __("-- Select --"), '</option>';
                    foreach ($manufacture_list as $val) {
                        if ($mfg->Manufacture == $val->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$val->PortID\" $selected>$val->Name</option>\n";
                    }
                    echo '</select>
       </div>    
       <div class="col-sm-2">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#create_manufacture">New</button> 
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="category">', __("Category Name"), '</label>
       <div class="col-sm-7">  
        <select name="category" id="category" class="form-control">
            <option value="">', __("-- Select --"), '</option>';

                    foreach ($category_list as $val) {
                        if ($mfg->Category == $val->PortID) {
                            $selected = " selected";
                        } else {
                            $selected = "";
                        }
                        echo "<option value=\"$val->PortID\" $selected>$val->Name</option>\n";
                    }
                    echo '</select>
       </div>    
       <div class="col-sm-2">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#create_category">New</button> 
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="fieldset">', __("Fieldset"), '</label>
       <div class="col-sm-9">  
            <select class="form-control" id="fieldset" name="fieldset">
                <option vlaue="">-- Select --</option>
                <option vlaue="mac_address" ', $mfg->Fieldset == "Asset with MAC address" ? 'selected' : '', '>Asset with MAC address</option>
            </select>
       </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="model_no">', __("Model Number"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="model_no" id="model_no" value="', $mfg->Model_no, '">
        </div>    
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="eol">', __("EOL"), '</label>
        <div class="input-group col-sm-9" style="padding-left: 16px; padding-right: 14px;">
        <input type="text" class="form-control" name="eol" id="eol" value="', $mfg->EOL, '">
        <span class="input-group-addon">Months</span>
        </div>    
    </div>
    
    <div class="form-group">
       <label class="col-sm-3" for="notes">', __("Notes"), '</label>
       <div class="col-sm-9">
       <textarea name="note" class="form-control">', $mfg->Note, '</textarea>
       </div>
    </div>
    
    <div class="form-group">
        <label for="is_user_request" class="col-sm-3">', __("Users may request this model"), '</label>
        <div class="col-sm-1">    
        <input type="checkbox" class="" name="is_user_request" id="is_user_request" value="', $mfg->Is_user_request == "" ? "N" : $mfg->Is_user_request, '" ', $mfg->Is_user_request == "Y" ? "checked" : "", '>
        </div>
    </div>
    <div class="clearfix"></div>';

                    $assetweb_path = _MEDIA_URL . "assets_model/{$mfg->Model_image}";
                    $assetfilename = _PATH . '/uploads/assets_model' . DIRECTORY_SEPARATOR . $mfg->Model_image;

                    $front_icon = "";
                    $rear_icon = "";
                    if (file_exists($assetfilename) && $mfg->Model_image != "") {
                        $rear_icon = "<a href='" . $assetweb_path . "' target='__blank'>" . $mfg->Model_image . " <i class='fa fa-eye'></i></a>";
                    }
                    echo '<div class="form-group">
       <label class="col-sm-3" for="model_image">', __("Upload Image"), '</label>
       <div class="col-sm-9">
       <input type="file" class="form-control" name="model_image" id="model_image" value="', $mfg->Model_image, '">
       <output id="filesInfo"></output>
       <input type="hidden" name="model_img" id="model_img" value="', $mfg->Model_img, '">  
       <input type="hidden" name="model_img_val" id="model_img_val" value="">  
       </div>
    </div>';
                    if (file_exists($assetfilename) && $mfg->Model_image) {
                        echo '<div class="col-sm-4 col-sm-offset-3"><img src="' . $assetweb_path . '" class="image-responsive" style="height:100px;width:180px;"><br/></div>';
                    }

                    echo '<div class="clearfix"></div>
<div class="text-center">';
                    if ($mfg->PortID > 0) {
                        echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">', __("Update"), '</button>';
                    } else {
                        echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">', __("Create"), '</button>';
                    }
                    echo '&nbsp;<a href="asset_model_list.php" class="btn-panel btn-success">', __("Cancel"), '</a>';
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
        <spam><?php echo $footer_text; ?></spam>
    </footer>
<?php } ?>
<!-- Footer -->
</html>
<script type="text/javascript">
    $(document).ready(function () {
        $('#is_user_request').click(function () {
            if ($(this).prop("checked") == true) {
                $(this).val("Y");
            } else if ($(this).prop("checked") == false) {
                $(this).val("N");
            }
        });

        $("#manufacture_submit").click(function () {
            $.ajax({
                url: 'ajax_create_manufacture.php',
                type: 'post',
                data: $("#manufacture_form").serialize(),
                dataType: 'JSON',
                success: function (res) {
                    if (res.status == 'success') {
                        $("#manufacture").html(res.res);
                        $('#create_manufacture').modal('hide');
                    }
                }
            });
        });

        $("#category_submit").click(function () {
            $.ajax({
                url: 'ajax_create_category.php',
                type: 'post',
                data: $("#category_form").serialize(),
                dataType: 'JSON',
                success: function (res) {
                    if (res.status == 'success') {
                        $("#category").html(res.res);
                        $('#create_category').modal('hide');
                    }
                }
            });
        });

    });

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
                        $("#model_img_val").val(evt.target.result);
                        document.getElementById('filesInfo').appendChild(div);
                    };
                }(file));
                reader.readAsDataURL(file);
            }
        } else {
            alert('The File APIs are not fully supported in this browser.');
        }
    }

    document.getElementById('model_image').addEventListener('change', frontfileSelect, false);
</script>