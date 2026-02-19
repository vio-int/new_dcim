<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );

	$subheader=__("IpamPrefix");
        $footer_text = "";

	if(!$person->SiteAdmin){
            // No soup for you.
            header('Location: '.redirect());
            exit;
	}

	$mfg=new IpamPrefix();
        $Location=new Location();
        $Group =new IpamVLANGroup();
        $Role =new IpamPrefixRole();
        $Vlan =new IpamVLAN();
        $vrf = new IpamVRF();
        
        $RoleList = $Role->GetIpamPrefixRoleList();
        $GroupList = $Group->GetIpamVLANGroupList();
        $VlanList = $Vlan->GetIpamVLANList();
        $VrfList = $vrf->GetIpamVRFList();
        
        
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
            
            $GroupList = $Group->GetGroupLocationList($mfg->Site);
            $VlanList = $Vlan->GetVlanLocationList($mfg->Group);
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->PortID=$_POST["PortID"];
            $mfg->Name=trim($_POST["name"]);
            $mfg->Site=trim($_POST["site"]);
            $mfg->Status=trim($_POST["status"]);
            $mfg->Vrf=trim($_POST["vrf"]);
            $mfg->Vlan=trim($_POST["vlan"]);
            $mfg->Group=trim($_POST["group"]);
            $mfg->Role=trim($_POST["role"]);
            $mfg->Description=trim($_POST["description"]);
            $mfg->Pool=trim($_POST["pool"]);
            $mfg->Tag=trim($_POST["tag"]);
            
            if($mfg->Name != null && $mfg->Name != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateObject()){
                                    header('Location: '.redirect("ipam_prefix.php?PortID=$mfg->PortID"));
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
        
	$mfgList=$mfg->GetIpamPrefixList();
        $SiteList = $Location->GetLocationList();
        
        //$VrfList = $vrf->GetIpamVRFList();
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
                    location.href='ipam_prefix.php?PortID='+this.value;
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
    <!-- Breadcrumb code start -->
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <ol class="breadcrumb">
                <li><a href="index_ithardware.php">Dashboard</a></li>
                <li><a href="ipam_prefix_list.php">Prefix</a></li>
                <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Prefix</li>
            </ol>
        </div>
    </div>
    <!-- Breadcrumb code end -->
<div class="col-sm-12">

<?php
	// include( "sidebar.inc.php" );

echo '<div class="main">
    <div class="">
<h3>',$status,'</h3>
<div class="table-center"><div>
<form id="mform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Prefix</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">',__("Name"),'</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("New Ipam Prefix"),'</option>';

            foreach($mfgList as $mfgRow){
                if($mfg->PortID==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }

    echo '	</select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">',__("Name"),'</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="',$mfg->Name,'">
        </div>   
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="status">',__("Status"),'</label>
       <div class="col-sm-9">      
       <select name="status" id="status" class="form-control">
            <option value="Container" ',$mfg->Status=="Container"?"Selected":"",'>',__("Container"),'</option>
            <option value="Active" ',$mfg->Status=="Active"?"Selected":"",'>',__("Active"),'</option>
            <option value="Reserved" ',$mfg->Status=="Reserved"?"Selected":"",'>',__("Reserved"),'</option>
            <option value="Deprecated" ',$mfg->Status=="Deprecated"?"Selected":"",'>',__("Deprecated"),'</option>
        </select>
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="vrf">',__("VRF"),'</label>
        <div class="col-sm-9">  
       <select name="vrf" id="vrf" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($VrfList as $mfgRow){
                    if($mfg->Vrf==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="role">',__("Role"),'</label>
       <div class="col-sm-9">      
       <select name="role" id="role" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($RoleList as $mfgRow){
                    if($mfg->Role==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '</select>
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="description">',__("Description"),'</label>
       <div class="col-sm-9">      
        <input type="text" class="form-control" name="description" id="description" value="',$mfg->Description,'">
       </div>    
    </div>
    <div class="form-group">
        <div class="col-sm-9 col-sm-offset-3">    
        <input type="checkbox" class="" name="pool" id="pool" value="',$mfg->Pool==""?"N":$mfg->Pool,'" ',$mfg->Pool=="Y"?"checked":"",'>
        <label for="pool">',__("Is a pool"),'</label></br>
        <span>All IP addresses within this prefix are considered usable</span>    
        </div>
    </div>
</div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Site/VLAN Assignment</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="site">',__("Location"),'</label>
        <div class="col-sm-9">  
       <select name="site" id="site" class="form-control" onchange="change_location(this.value)">
            <option value="">',__("-- Select --"),'</option>';
            foreach($SiteList as $mfgRow){
                    if($mfg->Site==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="group">',__("VLAN Group"),'</label>
        <div class="col-sm-9">  
       <select name="group" id="group" class="form-control" onchange="change_group(this.value)">
            <option value="">',__("-- Select --"),'</option>';
            foreach($GroupList as $mfgRow){
                    if($mfg->Group==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="vlan">',__("VLAN"),'</label>
        <div class="col-sm-9">  
       <select name="vlan" id="vlan" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($VlanList as $mfgRow){
                    if($mfg->Vlan==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    
</div>
</div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Tags</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="tag">',__("Tag"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="tag" id="tag" value="',$mfg->Tag,'">
           </div>    
        </div>
    </div>
</div>   
  
<div class="text-center">';
	if($mfg->PortID >0){
            echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">',__("Update"),'</button>';
	}else{
            echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">',__("Create"),'</button>';
	}
        echo '&nbsp;<a href="ipam_prefix_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
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
    $('#pool').click(function(){
        if($(this).prop("checked") == true){
            $(this).val("Y");
        }
        else if($(this).prop("checked") == false){
            $(this).val("N");
        }
    });
});  
function change_location(location_id) {

    $.ajax({
        url: 'get_group.php',
        type: 'post',
        data: {location_id: location_id},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#group").html(res.res);
            }
        }
    });
}
function change_group(group_id) {

    $.ajax({
        url: 'get_vlan.php',
        type: 'post',
        data: {group_id: group_id},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#vlan").html(res.res);
            }
        }
    });
}
</script>
