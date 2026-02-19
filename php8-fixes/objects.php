<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );

	$subheader=__("Object Listing");

	if(!$person->SiteAdmin){
		// No soup for you.
		header('Location: '.redirect());
		exit;
	}

	$mfg=new Objects();

	// AJAX Start
	if(isset($_GET['getTemplateCount']) && isset($_GET['ObjectID'])){
		$temp=new DeviceTemplate();
		$temp->ObjectID=$_GET['ObjectID'];
		header('Content-Type: application/json');
		echo json_encode($temp->GetTemplateListByManufacturer());
		exit;
	}

	if(isset($_POST['action']) && $_POST["action"]=="Delete"){
		header('Content-Type: application/json');
		$response=false;
		if(isset($_POST["TransferTo"])){
			$mfg->ObjectID=$_POST['ObjectID'];
			if($mfg->DeleteObject($_POST["TransferTo"])){
				$response=true;
			}
		}
		echo json_encode($response);
		exit;
	}

	// END - AJAX

	if(isset($_REQUEST["ObjectID"]) && $_REQUEST["ObjectID"] >0){
		$mfg->ObjectID=(isset($_POST['ObjectID']) ? $_POST['ObjectID'] : $_GET['ObjectID']);
		$mfg->GetOrderByID();
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
		$mfg->ObjectID=$_POST["ObjectID"];
		$mfg->Name=trim($_POST["name"]);
		
		if($mfg->Name != null && $mfg->Name != ""){
			if($_POST["action"]=="Create"){
				if($mfg->CreateObject()){
					header('Location: '.redirect("objects.php?ObjectID=$mfg->ObjectID"));
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
	$mfgList=$mfg->GetObjectList();
        
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
		$('#ObjectID').change(function(e){
			location.href='objects.php?ObjectID='+this.value;
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
			data:{getTemplateCount: $('#ObjectID').val()},
			success: function(data){
				$('#count').text(data.length);
				count=data.length;
			}
		});
		return count;
	}

	function DeleteObject(){
		function DeleteNow(objectid){
			// If manufacturerid unset then just delete 
                        transferto=(typeof(objectid)=='undefined')?0:objectid;
			$.post('',{ObjectID: $('#ObjectID').val(), TransferTo: transferto, action: 'Delete'},function(data){
				if(data){
					location.href='';
				}else{
					alert("Something's gone horrible wrong");
				}
			});
		}

		// if there aren't any templates using this manufacturer just delete it.
		if(parseInt(UpdateCount())){
			$('#copy').replaceWith($('#ObjectID').clone().attr('id','copy'));
			$('#copy option[value=0]').remove();
			$('#copy option[value='+$('#ObjectID').val()+']').remove();
			$('#deletemodal').dialog({
				width: 600,
				modal: true,
				buttons: {
					Transfer: function(e){
						$('#doublecheck').dialog({
							width: 600,
							modal: true,
							buttons: {
								Yes: function(e){
									DeleteNow($('#copy').val());
								},
								No: function(e){
									$('#doublecheck').dialog('destroy');
									$('#deletemodal').dialog('destroy');
								}
							}
						});
					},
					No: function(e){
						$('#deletemodal').dialog('destroy');
					}
				}
			});
		}else{
			DeleteNow();
		}
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
<h3>',$status,'</h3>
<div class="center"><div>
<form id="mform" method="POST">
<div class="table">
<div class="form-group">
    <label for="ObjectID">',__("Object"),'</label>
    <input type="hidden" name="action" value="query"><select name="ObjectID" id="ObjectID" class="form-control">
   <option value=0>',__("New Object"),'</option>';

	foreach($mfgList as $mfgRow){
                if($mfg->ObjectID==$mfgRow->ObjectID){$selected=" selected";}else{$selected="";}
		echo "<option value=\"$mfgRow->ObjectID\"$selected>$mfgRow->Name</option>\n";
	}

echo '	</select>
</div>
<div class="form-group">
   <label for="name">',__("Name"),'</label>
   <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="',$mfg->Name,'">
</div>

<div class="caption">';

	if($mfg->ObjectID >0){
		echo '<button type="submit" class="btn btn-primary" name="action" value="Update">',__("Update"),'</button>
	<button type="button" name="action" class="btn btn-primary" value="Delete">',__("Delete"),'</button>';
	}else{
		echo '<button type="submit" class="btn btn-primary" name="action" value="Create">',__("Create"),'</button>';
	}
?>
</div>
</div><!-- END div.table -->

</form>
</div>
</div></div>
<?php echo '
<!-- hiding modal dialogs here so they can be translated easily -->
<div class="hide">
	<div title="',__("Object delete confirmation"),'" id="deletemodal">
		<div id="modaltext"><span style="float:left; margin:0 7px 20px 0;" class="ui-icon ui-icon-alert"></span>',__("Are you sure that you want to delete this Object?"),'
		<br><br>
		<div>Transfer all existing templates to <select id="copy"></select></div>
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
