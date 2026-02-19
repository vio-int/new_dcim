<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );

	$subheader=__("Port Compatibility Listing");

	if(!$person->SiteAdmin){
            // No soup for you.
            header('Location: '.redirect());
            exit;
	}

	$mfg=new IPv6Allocation();

	// AJAX Start
	if(isset($_POST['action']) && $_POST["action"]=="Delete"){
            header('Content-Type: application/json');
            $response=false;
            if(isset($_POST["TransferTo"])){
                    $mfg->MainID=$_POST['MainID'];
                    if($mfg->DeleteObject($_POST["TransferTo"])){
                            $response=true;
                    }
            }
            echo json_encode($response);
            exit;
	}

	// END - AJAX

	if(isset($_REQUEST["MainID"]) && $_REQUEST["MainID"] >0){
            $mfg->MainID=(isset($_POST['MainID']) ? $_POST['MainID'] : $_GET['MainID']);
            $mfg->GetOrderByID();
            
            $mfgIPVList = $mfg->GetIPv6ID($mfg->IpID);
            //print_r($mfg);exit;
	}
        
        if(isset($_REQUEST["IpID"]) && $_REQUEST["IpID"] >0){
            $mfg->IpID=(isset($_POST['IpID']) ? $_POST['IpID'] : $_GET['IpID']);
            $mfgIPVList = $mfg->GetIPv6ID($_REQUEST["IpID"]);
        } else {
            $mfgIPVList = $mfg->GetIPv6ID();
        }

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->MainID=$_POST["MainID"];
            $mfg->Name=trim($_POST["name"]);
            $mfg->IpID=trim($_POST["IpID"]);
            $mfg->ObjectID=trim($_POST["ObjectID"]);
            
            if($mfg->Name != null && $mfg->Name != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateObject()){
                                    header('Location: '.redirect("ipv6allocation.php?MainID=$mfg->MainID"));
                            }else{
                                    $status=__("Error adding new object");
                            }
                    }else{
                            $status=__("Updated");
                            $mfg->UpdateObject();
                    }
            }
            //We either just created a manufacturer or updated it so reload from the db
            $mfg->GetOrderByID();    
	}
	$mfgList=$mfg->GetIPv6AllocationList();
        $mfgObjList=$mfg->GetObjectList();
        
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
            $('#MainID').change(function(e){
                    location.href='ipv6allocation.php?MainID='+this.value;
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
                data:{getTemplateCount: $('#MainID').val()},
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
            $.post('',{MainID: $('#MainID').val(), TransferTo: transferto, action: 'Delete'},function(data){
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
<?php include( 'header.inc.php' ); ?>
<div class="backgroundpage">
<div class="page1">
<div class="makecenter">

<?php
	// include( "sidebar.inc.php" );

echo '<div class="main">
<h3>',$status,'</h3>
<div class="center"><div>
<form id="mform" method="POST">
<div class="table">
<div>
   <div><label for="MainID">',__("Allocation"),'</label></div>
   <div><input type="hidden" name="action" value="query"><select name="MainID" id="MainID">
   <option value=0>',__("Allocation"),'</option>';

	foreach($mfgList as $mfgRow){
                if($mfg->MainID==$mfgRow->MainID){$selected=" selected";}else{$selected="";}
		echo "<option value=\"$mfgRow->MainID\"$selected>$mfgRow->Name</option>\n";
	}

echo '	</select></div>
</div>
<div>
   <div><label for="name">',__("Name"),'</label></div>
   <div><input type="text" class="validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="',$mfg->Name,'"></div>
</div>
<div>
   <div><label for="IpID">',__("IPv6"),'</label></div>
   <div><input type="hidden" name="action" value="query"><select name="IpID" id="IpID">';
	foreach($mfgIPVList as $mfgRow){
                if($mfg->PortID==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
		echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
	}
echo '	</select></div>
</div>
<div>
   <div><label for="ObjectID">',__("Objects"),'</label></div>
   <div><input type="hidden" name="action" value="query"><select name="ObjectID" id="ObjectID">
   <option value=0>',__("Objects"),'</option>';

	foreach($mfgObjList as $mfgRow){
                if($mfg->ObjectID==$mfgRow->ObjectID){$selected=" selected";}else{$selected="";}
		echo "<option value=\"$mfgRow->ObjectID\"$selected>$mfgRow->Name</option>\n";
	}

echo '	</select></div>
</div>
<div class="caption">';
	if($mfg->MainID >0){
            echo '<button type="submit" name="action" value="Update">',__("Update"),'</button>
	<button type="button" name="action" value="Delete">',__("Delete"),'</button>';
	}else{
            echo '<button type="submit" name="action" value="Create">',__("Create"),'</button>';
	}
?>
</div>
</div><!-- END div.table -->

</form>
</div></div>
<?php echo '
<!-- hiding modal dialogs here so they can be translated easily -->
<div class="hide">
	<div title="',__("Port comptilibity delete confirmation"),'" id="deletemodal">
		<div id="modaltext"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-alert"></span>',__("Are you sure that you want to delete this Port Comptilibity?"),'
		</div>
	</div>
	<div title="',__("Are you REALLY sure?"),'" id="doublecheck">
		<div id="modaltext" class="warning"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-alert"></span>',__("Are you sure REALLY sure?  There is no undo!!"),'
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
