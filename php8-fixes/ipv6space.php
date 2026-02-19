<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Port Compatibility Listing");

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

$mfg = new IPv6();

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
    //print_r($mfg);exit;
}

$status = "";
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    $mfg->Name = trim($_POST["name"]);
    $mfg->Prefix = trim($_POST["prefix"]);
    $mfg->Vlan = trim($_POST["vlan"]);
    $mfg->Tag = trim($_POST["tag"]);

    if ($mfg->Name != null && $mfg->Name != "") {
        if ($_POST["action"] == "Create") {
            if ($mfg->CreateObject()) {
                header('Location: ' . redirect("ipv6space.php?PortID=$mfg->PortID"));
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
$mfgList = $mfg->GetIPv6List();
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
                $('#PortID').change(function (e) {
                    location.href = 'ipv6space.php?PortID=' + this.value;
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
        <?php include( 'header.inc.php' ); ?>
        <div class="container">
            <div class="page1">
                <div class="col-sm-12">

                    <?php
                    // include( "sidebar.inc.php" );

                    echo '<div class="main">
    <div class="form_container">
<h3>', $status, '</h3>
<div class="center"><div>
<form id="mform" method="POST">
<div class="table">
<div class="form-group">
   <label for="PortID">', __("IP v6"), '</label>
   <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
   <option value=0>', __("IP v6"), '</option>';

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
<div class="form-group">
   <label for="name">', __("Name"), '</label>
   <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="', $mfg->Name, '">
</div>
<div class="form-group">
   <label for="prefix">', __("Prifix"), '</label>
   <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="prefix" id="prefix" maxlength="40" value="', $mfg->Prefix, '">
</div>';
                    if ($mfg->Vlan == "Default") {
                        $select1 = " selected";
                    } else if ($mfg->Vlan == "Second") {
                        $select2 = " selected";
                    } else if ($mfg->Vlan == "Third") {
                        $select3 = " selected";
                    } else if ($mfg->Vlan == "Fourth") {
                        $select4 = " selected";
                    } else if ($mfg->Vlan == "Fifth") {
                        $select5 = " selected";
                    } else if ($mfg->Vlan == "Sixth") {
                        $select6 = " selected";
                    } else if ($mfg->Vlan == "Seventh") {
                        $select7 = " selected";
                    } else if ($mfg->Vlan == "Eighth") {
                        $select8 = " selected";
                    } else if ($mfg->Vlan == "Ninth") {
                        $select9 = " selected";
                    } else if ($mfg->Vlan == "Tenth") {
                        $select10 = " selected";
                    } else if ($mfg->Vlan == "Eleventh") {
                        $select11 = " selected";
                    } else if ($mfg->Vlan == "Twelth") {
                        $select12 = " selected";
                    } else if ($mfg->Vlan == "Thirteenth") {
                        $select13 = " selected";
                    } else if ($mfg->Vlan == "Fouteenth") {
                        $select14 = " selected";
                    } else if ($mfg->Vlan == "Fifteenth") {
                        $select15 = " selected";
                    } else {
                        $select1 = $select2 = $select3 = $select4 = $select5 = $select6 = $select7 = $select8 = $select9 = $select10 = $select11 = $select12 = $select13 = $select14 = $select15 = " selected";
                    }
                    echo '<div class="form-group">
   <label for="vlan">', __("VLAN"), '</label>
   <input type="hidden" name="action" value="query"><select name="vlan" id="vlan" class="form-control">
   <option value="Default" ' . $select1 . '>', __("1 Default"), '</option>
   <option value="Second" ' . $select2 . '>', __("2 Second"), '</option>
   <option value="Third" ' . $select3 . '>', __("3 Third"), '</option>
   <option value="Fourth" ' . $select4 . '>', __("4 Fourth"), '</option>
   <option value="Fifth" ' . $select5 . '>', __("5 Fifth"), '</option>
   <option value="Sixth" ' . $select6 . '>', __("6 Sixth"), '</option>
   <option value="Seventh" ' . $select7 . '>', __("7 Seventh"), '</option>
   <option value="Eighth" ' . $select8 . '>', __("8 Eighth"), '</option>
   <option value="Ninth" ' . $select9 . '>', __("9 Ninth"), '</option>
   <option value="Tenth" ' . $select10 . '>', __("10 Tenth"), '</option>
   <option value="Eleventh" ' . $select11 . '>', __("11 Eleventh"), '</option>
   <option value="Twelth" ' . $select12 . '>', __("12 Twelth"), '</option>
   <option value="Thirteenth" ' . $select13 . '>', __("13 Thirteenth"), '</option>
   <option value="Fouteenth" ' . $select14 . '>', __("14 Fouteenth"), '</option>
   <option value="Fifteenth" ' . $select15 . '>', __("15 Fifteenth"), '</option>
</select>
</div>
<div class="form-group">
   <label for="tag">', __("Tag"), '</label>
   <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="tag" id="tag" maxlength="40" value="', $mfg->Tag, '">
</div>
<div class="caption">';
                    if ($mfg->PortID > 0) {
                        echo '<button type="submit" class="btn btn-primary" name="action" value="Update">', __("Update"), '</button>
	<button type="button" name="action" class="btn btn-primary" value="Delete">', __("Delete"), '</button>';
                    } else {
                        echo '<button type="submit" class="btn btn-primary" name="action" value="Create">', __("Create"), '</button>';
                    }
                    ?>
                </div>
            </div>
        </div><!-- END div.table -->

    </form>
</div></div>
<?php echo '
<!-- hiding modal dialogs here so they can be translated easily -->
<div class="hide">
	<div title="', __("Port comptilibity delete confirmation"), '" id="deletemodal">
		<div id="modaltext"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-alert"></span>', __("Are you sure that you want to delete this Port Comptilibity?"), '
		</div>
	</div>
	<div title="', __("Are you REALLY sure?"), '" id="doublecheck">
		<div id="modaltext" class="warning"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-alert"></span>', __("Are you sure REALLY sure?  There is no undo!!"), '
		<br><br>
		</div>
	</div>
</div>'; ?>
</div><!-- END div.main -->
</div><!-- END div.page -->
</div>
</div>
</body>
</html>
