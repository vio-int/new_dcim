<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );

	$subheader=__("Data Center Escalation Rules");

	if(!$person->ContactAdmin){
		// No soup for you.
		header('Location: '.redirect());
		exit;
	}

	$esc=new Escalations();
	$status="";

	if(isset($_REQUEST['escalationid'])){
		$esc->EscalationID=$_REQUEST['escalationid'];
		if(isset($_POST['action'])){
			if($_POST['details']!=null && $_POST['details']!=''){
				switch($_POST['action']){
					case 'Create':
						$esc->Details=$_POST['details'];
						$esc->CreateEscalation();
						break;
					case 'Update':
						$esc->Details=$_POST['details'];
						$status=__("Updated");
						$esc->UpdateEscalation();
						break;
					case 'Delete':
						$esc->DeleteEscalation();
						header('Location: '.redirect("escalations.php"));
						exit;
				}
			}
		}
		$esc->GetEscalation();
	}
	$escList=$esc->GetEscalationList();
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
  <link rel="stylesheet"  href="css/ie.css" type="text/css">
  <![endif]-->
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
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
<form method="POST">
<div class="table">
<div class="form-group">
   <label for="escalationid">',__("Escalation Rule"),'</label>
   <input type="hidden" class="form-control" name="action" value="query"><select name="escalationid" id="escalationid" onChange="form.submit()">
   <option value=0>',__("New Escalation Rule"),'</option>';

	foreach( $escList as $escRow ) {
		if($esc->EscalationID == $escRow->EscalationID){$selected=" selected";}else{$selected="";}
		print "<option value=\"$escRow->EscalationID\"$selected>$escRow->Details</option>\n";
	}

echo '	</select>
</div>
<div class="form-group">
   <label for="details">',__("Details"),'</label>
   <input type="text" class="form-control" name="details" id="details" size="40" value="',$esc->Details,'">
</div>
<div class="caption">';

	if($esc->EscalationID >0){
		echo '   <button type="submit" name="action" value="Update">',__("Update"),'</button>
	 <button type="submit" name="action" value="Delete">',__("Delete"),'</button>';
	}else{
		echo '	 <button type="submit" class="btn btn-primary" name="action" value="Create">',__("Create"),'</button>';
	}
?>
</div>
</div><!-- END div.table -->
</form>
</div></div>
<?php echo ''; ?>
</div><!-- END div.main -->
</div><!-- END div.page -->
</div>
</div>
</div>
</body>
</html>
