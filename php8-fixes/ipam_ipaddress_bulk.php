<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );

	$subheader=__("Port Compatibility Listing");
        $footer_text = "";

	if(!$person->SiteAdmin){
            // No soup for you.
            header('Location: '.redirect());
            exit;
	}

	$mfg=new IpamIPaddressBulk();
        $Role=new IpamPrefixRole();
        $Vrf = new IpamVRF();
        
        $Role_list = $Role->GetIpamPrefixRoleList();
        $Vrf_list = $Vrf->GetIpamVRFList();
        
	// AJAX Start
	if(isset($_POST['action']) && $_POST["action"]=="Delete"){
            header('Content-Type: application/json');
            $response=false;
            if(isset($_POST["TransferTo"])){
                    $mfg->PortID=$_POST['PortID'];
                    if($mfg->DeleteObject($_POST["TransferTo"])){
                        $response=true;
                    }
            }
            echo json_encode($response);
            exit;
	}

	// END - AJAX

	if(isset($_REQUEST["PortID"]) && $_REQUEST["PortID"] >0){
            $mfg->PortID=(isset($_POST['PortID']) ? $_POST['PortID'] : $_GET['PortID']);
            $mfg->GetOrderByID();
            //print_r($mfg);exit;
            
            $Role_list = $Role->GetIpamPrefixRoleList();
            $Vrf_list = $Vrf->GetIpamVRFList();
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->PortID=$_POST["PortID"];
            $mfg->Address=trim($_POST["address"]);
            $mfg->Status=trim($_POST["status"]);
            $mfg->Role=trim($_POST["role"]);
            $mfg->Vrf=trim($_POST["vrf"]);
            $mfg->Description=$_POST["description"];
            
            if($mfg->Address != null && $mfg->Address != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateObject()){
                                    header('Location: '.redirect("ipam_ipaddress_bulk.php?PortID=$mfg->PortID"));
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
	$mfgList=$mfg->GetIpamIPaddressBulkList();
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
	$(document).ready(function() {
            $('#mform').validationEngine({});
            $('#PortID').change(function(e){
                    location.href='ipam_ipaddress_bulk.php?PortID='+this.value;
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
            $.post('',{PortID: $('#PortID').val(), TransferTo: transferto, action: 'Delete'},function(data){
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
<?php include( 'header_ithardware.inc.php' ); ?>
<div class="container">
<div class="page1">
<div class="col-sm-12">

<?php
	// include( "sidebar.inc.php" );

echo '<div class="main">
    <div class="">
<h3>',$status,'</h3>
<div class="table-center"><div>
<ul class="nav nav-tabs" style="margin-bottom: 20px">
    <li role="presentation">
        <a href="ipam_ipaddress.php">New IP</a>
    </li>
    <li role="presentation" class="active">
        <a href="javascript:void(0);">Bulk Create</a>
    </li>
</ul>
<form id="mform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>IP Address</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">',__("Address Pattern"),'</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("New IP Address"),'</option>';
            foreach($mfgList as $mfgRow){
                if($mfg->PortID==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Address</option>\n";
            }
    echo '</select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">',__("Address Pattern"),'</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="address" id="address" maxlength="40" value="',$mfg->Address,'">
           <span>IPv4 or IPv6 address (with mask)</span>
        </div>   
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="status">',__("Status"),'</label>
       <div class="col-sm-9">      
       <select name="status" id="status" class="form-control">
            <option value="Active" ',$mfg->Status=="Active"?"Selected":"",'>',__("Active"),'</option>
            <option value="Reserved" ',$mfg->Status=="Reserved"?"Selected":"",'>',__("Reserved"),'</option>
            <option value="Deprecated" ',$mfg->Status=="Deprecated"?"Selected":"",'>',__("Deprecated"),'</option>
            <option value="DHCP" ',$mfg->Status=="DHCP"?"Selected":"",'>',__("DHCP"),'</option>    
        </select>
        <span>The operational status of this IP</span>
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="role">',__("Role"),'</label>
       <div class="col-sm-9">      
        <select name="role" id="role" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($Role_list as $mfgRow){
                    if($mfg->Role==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '</select>
        <span>The functional role of this IP</span>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="vrf">',__("VRF"),'</label>
        <div class="col-sm-9">  
       <select name="vrf" id="vrf" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($Vrf_list as $mfgRow){
                    if($mfg->Vrf==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="description">',__("Description"),'</label>
       <div class="col-sm-9">  
       <input type="text" class="form-control" name="description" id="description" value="',$mfg->Description,'">
       </div>    
    </div>
</div>
</div>
<div class="text-center">';
	if($mfg->PortID >0){
            echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">',__("Update"),'</button>
	<button type="button" name="action" class="btn btn-primary btn-lg" value="Delete">',__("Delete"),'</button>';
	}else{
            echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">',__("Create"),'</button>';
	}
        echo '&nbsp;<a href="ipam_ipaddress_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
?>
</div>
    </div>
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
<!-- Footer -->
<?php if($footer_text!=""){?>
    <footer class="page-footer font-small footer">
        <spam><?php echo $footer_text; ?></spam>
    </footer>
<?php } ?>
<!-- Footer -->
</html>
<script type="text/javascript">
$(document).ready(function(){
    
});
</script>
