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

$mfg = new AssetSupplier();
$model = new Model();
$location = new Location();
$manufacture = new Manufacture();

$model_list = $model->GetAssetModelList();
$status_list = $mfg->GetStatusList();
$supplier_list = $mfg->GetSupplierList();
$location_list = $location->GetLocationList();
$manufactureList = $manufacture->GetManufactureList();

if (isset($_REQUEST["PortID"]) && $_REQUEST["PortID"] > 0) {
    $mfg->PortID = (isset($_POST['PortID']) ? $_POST['PortID'] : $_GET['PortID']);
    $mfg->GetOrderByID();
    
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Address = trim($_POST["address"]);
    $mfg->City = trim($_POST["city"]);
    $mfg->State = trim($_POST["state"]);
    $mfg->Country = trim($_POST["country"]);
    $mfg->Zip = trim($_POST["zip"]);
    $mfg->Contact_name = trim($_POST["contact_name"]);
    $mfg->Phone = trim($_POST["phone"]);
    $mfg->Fax = trim($_POST["fax"]);
    $mfg->Email = trim($_POST["email"]);
    $mfg->Url = trim($_POST["url"]);
    $mfg->Note = trim($_POST["note"]);
    $mfg->Supplier_image = trim($_FILES['supplier_image']["name"]);
    $mfg->Supplier_img = trim($_POST["supplier_img"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("asset_supplier.php?PortID=$mfg->PortID"));
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
            $(document).ready(function () {

                $('#mform').validationEngine({});
                $('#PortID').change(function (e) {
                    location.href = 'asset_supplier.php?PortID=' + this.value;
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
                            <li><a href="asset_supplier_list.php">Supplier</a></li>
                            <li><?php echo $mfg->PortID != "" ? 'Edit' : 'Add'; ?> Supplier</li>
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
<form id="mform" method="POST" enctype="multipart/form-data">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Supplier Management</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">', __("Name"), '</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>', __("New Supplier"), '</option>';

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
       <label class="col-sm-3" for="address">', __("Address"), '</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="address" id="address" value="', $mfg->Address, '">
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="city">', __("City"), '</label>
       <div class="col-sm-9">  
        <input type="text" class="form-control" name="city" id="city" value="', $mfg->City, '">
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="state">', __("State"), '</label>
       <div class="col-sm-9">  
        <input type="text" class="form-control" name="state" id="state" value="', $mfg->State, '">
       </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="country">', __("Country"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="country" id="country" value="', $mfg->Country, '">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="zip">', __("Zip"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="zip" id="zip" value="', $mfg->Zip, '">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="contact_name">', __("Contact Name"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="contact_name" id="contact_name" value="', $mfg->Contact_name, '">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="phone">', __("Phone"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="phone" id="phone" value="', $mfg->Phone, '">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="fax">', __("Fax"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="fax" id="fax" value="', $mfg->Fax, '">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="email">', __("Email"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" onblur="validateEmail(this.value)" name="email" id="eamil" value="', $mfg->Email, '">
        <p class="text-danger" id="email_err"></p>    
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="url">', __("URL"), '</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="url" id="url" value="', $mfg->Url, '">
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="notes">', __("Notes"), '</label>
       <div class="col-sm-9">
       <textarea name="note" class="form-control">', $mfg->Note, '</textarea>
       </div>
    </div>';

                    $assetweb_path = _MEDIA_URL . "assets_supplier/{$mfg->Supplier_image}";
                    $assetfilename = _PATH . '/uploads/assets_supplier' . DIRECTORY_SEPARATOR . $mfg->Supplier_image;

                    $front_icon = "";
                    $rear_icon = "";
                    if (file_exists($assetfilename) && $mfg->Supplier_image != "") {
                        $rear_icon = "<a href='" . $assetweb_path . "' target='__blank'>" . $mfg->Supplier_image . " <i class='fa fa-eye'></i></a>";
                    }
                    echo '<div class="form-group">
       <label class="col-sm-3" for="supplier_image">', __("Upload Image"), '</label>
       <div class="col-sm-9">
       <input type="file" class="form-control" name="supplier_image" id="supplier_image" value="', $mfg->Supplier_image, '">
       <output id="filesInfo"></output>
       <input type="hidden" name="supplier_img" id="supplier_img" value="', $mfg->Supplier_img, '">  
       <input type="hidden" name="supplier_img_val" id="supplier_img_val" value="">  
       </div>
    </div>';
                    if (file_exists($assetfilename) && $mfg->Supplier_image) {
                        echo '<div class="col-sm-4 col-sm-offset-3"><img src="' . $assetweb_path . '" class="image-responsive" style="height:100px;width:180px;"><br/></div>';
                    }

                    echo '<div class="clearfix"></div>
<div class="text-center">';
                    if ($mfg->PortID > 0) {
                        echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">', __("Update"), '</button>';
                    } else {
                        echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">', __("Create"), '</button>';
                    }
                    echo '&nbsp;<a href="asset_supplier_list.php" class="btn-panel btn-success">', __("Cancel"), '</a>';
                    ?>
                </div>
            </div>
        </div>
    </div><!-- END div.table -->

</form>

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
                        $("#supplier_img_val").val(evt.target.result);
                        document.getElementById('filesInfo').appendChild(div);
                    };
                }(file));
                reader.readAsDataURL(file);
            }
        } else {
            alert('The File APIs are not fully supported in this browser.');
        }
    }

    document.getElementById('supplier_image').addEventListener('change', frontfileSelect, false);
    
    function validateEmail(sEmail) {
        /*var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
         if (filter.test(sEmail)) {
         return true;
         }
         else {
         return false;
         }*/
        
        if(sEmail != ""){
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,7})+$/.test(sEmail)) {
                $("#email_err").html("");
                return (true);
            } else {
                $("#email_err").html("Please enter valid email");
                return (false);
            }
        } else {
            $("#email_err").html("Email is required");
        }

    }
</script>