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

	$mfg=new AssetManage();
        $device = new Device_new();
        $rack = new Rack();
        
        $deviceList = $device->GetDevice_newList();
        $rackList = $rack->GetRackList();
        $departmentList = $mfg->GetDepartment();
        
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
            
            $deviceList = $device->GetRackDeviceList($mfg->Rack);
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->PortID=$_POST["PortID"];
            $mfg->Name=trim($_POST["name"]);
            $mfg->Status=trim($_POST["status"]);
            $mfg->Label=trim($_POST["label"]);
            $mfg->Serial_no=trim($_POST["serial_no"]);
            $mfg->Asset_tag=trim($_POST["asset_tag"]);
            $mfg->Primary_ip=trim($_POST["primary_ip"]);
            $mfg->Manufacture_date=trim($_POST["manufacture_date"]);
            $mfg->Install_date=trim($_POST["install_date"]);
            $mfg->Company=trim($_POST["warranty_company"]);
            $mfg->Expiration_date=trim($_POST["warranty_date"]);
            $mfg->Rack=trim($_POST["rack"]);
            $mfg->Device=trim($_POST["device"]);
            $mfg->Height=trim($_POST["height"]);
            $mfg->Position=trim($_POST["position"]);
            $mfg->Half_depth=trim($_POST["depth"]);
            $mfg->Data_ports=trim($_POST["data_port"]);
            $mfg->Back_side=trim($_POST["back_side"]);
            $mfg->Watts=trim($_POST["watts"]);
            $mfg->Weight=trim($_POST["weight"]);
            $mfg->Power_connection=trim($_POST["power_connection"]);
            $mfg->Device_role=trim($_POST["device_role"]);
            $mfg->SNMP_version=trim($_POST["snmp_version"]);
            $mfg->SNMP_community=trim($_POST["snmp_community"]);
            $mfg->SNMP_failure=trim($_POST["snmp_failure"]);
            $mfg->Department=trim($_POST["department"]);

            if($mfg->Name != null && $mfg->Name != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateObject()){
                                    header('Location: '.redirect("asset_manage.php?PortID=$mfg->PortID"));
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
	$mfgList=$mfg->GetAssetManageList();
        
        
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
  <!--[if lt IE 9]>
  <link rel="stylesheet"  href="css/ie.css" type="text/css">
  <![endif]-->
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
  <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
  <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>
  <script type="text/javascript" src="scripts/bootstrap-datepicker.js"></script>

  <style type="text/css">
	#using { margin-top: 1em; }
  </style>

  <script type="text/javascript">
	$(document).ready(function() {
            $('#install_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            });
            $('#warranty_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            });
            $('#manufacture_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            });
            $('#mform').validationEngine({});
            $('#PortID').change(function(e){
                    location.href='asset_manage.php?PortID='+this.value;
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
<?php include( 'header_dcim.inc.php' ); ?>
<div class="container">
<div class="page1">
    <!-- Breadcrumb code start -->
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <ol class="breadcrumb">
                <li><a href="index_dcim.php">Dashboard</a></li>
                <li><a href="assets_list.php">Assets</a></li>
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
<h3>',$status,'</h3>
<div class="table-center"><div>
<form id="mform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Asset Management</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">',__("Name"),'</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("New Asset"),'</option>';

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
            <option value="">',__("-- Select --"),'</option>
            <option value="Development" ',$mfg->Status=="Development"?"Selected":"",'>',__("Development"),'</option>
            <option value="Disposed" ',$mfg->Status=="Disposed"?"Selected":"",'>',__("Disposed"),'</option>
            <option value="Production" ',$mfg->Status=="Production"?"Selected":"",'>',__("Production"),'</option>
            <option value="QA" ',$mfg->Status=="QA"?"Selected":"",'>',__("QA"),'</option>
            <option value="Reserved" ',$mfg->Status=="Reserved"?"Selected":"",'>',__("Reserved"),'</option>
            <option value="Spare" ',$mfg->Status=="Spare"?"Selected":"",'>',__("Spare"),'</option>
        </select>
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="label">',__("Label"),'</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="label" id="label" value="',$mfg->Label,'">
       </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="serial_no">',__("Serial Number"),'</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="serial_no" id="serial_no" value="',$mfg->Serial_no,'">
        </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="asset_tag">',__("Asset Tag"),'</label>
       <div class="col-sm-9">      
       <input type="text" class="form-control" name="asset_tag" id="asset_tag" value="',$mfg->Asset_tag,'">
       </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="primary_id">',__("Primary IP/ Host Name"),'</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="primary_ip" id="primary_ip" value="',$mfg->Primary_ip,'">
        </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
        <label class="col-sm-3" for="manufacture_date">',__("Manufacture Date"),'</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="manufacture_date" id="manufacture_date" value="',$mfg->Manufacture_date!="0000-00-00"?date('m/d/Y',strtotime($mfg->Manufacture_date)):"",'">
        </div>    
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
        <label class="col-sm-3" for="install_date">',__("Install Date"),'</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="install_date" id="install_date" value="',$mfg->Install_date!="0000-00-00"?date('m/d/Y',strtotime($mfg->Install_date)):"",'">
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
        <label class="col-sm-3" for="warranty_company">',__("Warranty Company"),'</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="warranty_company" id="warranty_company" value="',$mfg->Company,'">
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
        <label class="col-sm-3" for="warranty_date">',__("Warranty Expiration"),'</label>
        <div class="col-sm-9">
        <input type="text" class="form-control" name="warranty_date" id="warranty_date" value="',$mfg->Expiration_date!="0000-00-00"?date('m/d/Y',strtotime($mfg->Expiration_date)):"",'">
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
        <label class="col-sm-3" for="department">',__("Department"),'</label>
        <div class="col-sm-9">  
        <select name="department" id="department" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($departmentList as $mfgRow){
                if($mfg->Department==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
    echo '</select>
        </div>    
    </div>
</div>
</div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Physical Infrastructure</strong></div>
    <div class="panel-body">
        <div class="form-group">
            <label class="col-sm-3" for="rack">',__("Rack"),'</label>
            <div class="col-sm-9">  
            <select name="rack" id="rack" class="form-control" onchange="change_rack(this.value)">
                <option value="">',__("-- Select --"),'</option>';
                foreach($rackList as $mfgRow){
                    if($mfg->Rack==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                }
        echo '</select>
            </div>    
        </div>
        <div class="form-group">
            <label class="col-sm-3" for="device">',__("Device"),'</label>
            <div class="col-sm-9">  
            <select name="device" id="device" class="form-control">
                <option value="">',__("-- Select --"),'</option>';
                foreach($deviceList as $mfgRow){
                    if($mfg->Device==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
                }
        echo '</select>
            </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="height">',__("Height"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="height" id="height" value="',$mfg->Height,'">
           </div>
        </div>
        <div class="form-group">
           <label class="col-sm-3" for="position">',__("Position"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="position" id="position" value="',$mfg->Position,'">
           </div>
        </div>
        <div class="form-group">
            <label for="depth" class="col-sm-3">',__("Half Depth"),'</label>
            <div class="col-sm-9">    
            <input type="checkbox" class="" name="depth" id="depth" value="',$mfg->Half_depth==""?"N":$mfg->Half_depth,'" ',$mfg->Half_depth=="Y"?"checked":"",'>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
            <label for="back_side" class="col-sm-3">',__("Back side"),'</label>
            <div class="col-sm-9">    
            <input type="checkbox" class="" name="back_side" id="back_side" value="',$mfg->Back_side==""?"N":$mfg->Back_side,'" ',$mfg->Back_side=="Y"?"checked":"",'>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="data_port">',__("Number of data ports"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="data_port" id="data_port" value="',$mfg->Data_ports,'">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="watts">',__("Nominal Draw (Watts)"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="watts" id="watts" value="',$mfg->Watts,'">
           </div>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="weight">',__("Weight"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="weight" id="weight" value="',$mfg->Weight,'">
           </div>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="power_connection">',__("Power Connection"),'</label>
           <div class="col-sm-9">
           <input type="text" class="form-control" name="power_connection" id="power_connection" value="',$mfg->Power_connection,'">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
            <label class="col-sm-3" for="device_role">',__("Device Role"),'</label>
            <div class="col-sm-9">  
            <select name="device_role" id="device_role" class="form-control">
                <option value="">',__("-- Select --"),'</option>
                <option value="Access Switch" ',$mfg->Device_role=="Access Switch"?"Selected":"",'>',__("Access Switch"),'</option>
                <option value="Console Server" ',$mfg->Device_role=="Console Server"?"Selected":"",'>',__("Console Server"),'</option>
                <option value="Core Switch" ',$mfg->Device_role=="Core Switch"?"Selected":"",'>',__("Core Switch"),'</option>
                <option value="Distribution Switch" ',$mfg->Device_role=="Distribution Switch"?"Selected":"",'>',__("Distribution Switch"),'</option>
                <option value="Firewall" ',$mfg->Device_role=="Firewall"?"Selected":"",'>',__("Firewall"),'</option>
                <option value="Management Switch" ',$mfg->Device_role=="Management Switch"?"Selected":"",'>',__("Management Switch"),'</option>
                <option value="PDU" ',$mfg->Device_role=="PDU"?"Selected":"",'>',__("PDU"),'</option>
                <option value="Router" ',$mfg->Device_role=="Router"?"Selected":"",'>',__("Router"),'</option>
                <option value="Server" ',$mfg->Device_role=="Server"?"Selected":"",'>',__("Server"),'</option>';
            echo '
            </select>
            </div>
        </div>
    </div>
</div> 
<div class="panel panel-default">
    <div class="panel-heading"><strong>SNMP Configuration</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="snmp_version">',__("SNMP Version"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="snmp_version" id="snmp_version" value="',$mfg->SNMP_version,'">
           </div>
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="snmp_community">',__("SNMP Read Only Community"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="snmp_community" id="snmp_community" value="',$mfg->SNMP_community,'">
           </div>    
        </div>
        <div class="clearfix"></div>
        <div class="form-group">
           <label class="col-sm-3" for="snmp_failure">',__("Consecutive SNMP Failure"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control" name="snmp_failure" id="snmp_failure" value="',$mfg->SNMP_failure,'">
           </div>    
        </div>
        <span>*Polling is disabled after three consecutive failures.</span>
    </div>
</div>  
<div class="text-center">';
	if($mfg->PortID >0){
            echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">',__("Update"),'</button>';
	}else{
            echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">',__("Create"),'</button>';
	}
        echo '&nbsp;<a href="assets_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
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
<?php if($footer_text!=""){?>
    <footer class="page-footer font-small footer">
        <spam><?php echo $footer_text; ?></spam>
    </footer>
<?php } ?>
<!-- Footer -->
</html>
<script type="text/javascript">
$(document).ready(function(){
    $('#depth').click(function(){
        if($(this).prop("checked") == true){
            $(this).val("Y");
        }
        else if($(this).prop("checked") == false){
            $(this).val("N");
        }
    });
    $('#back_side').click(function(){
        if($(this).prop("checked") == true){
            $(this).val("Y");
        }
        else if($(this).prop("checked") == false){
            $(this).val("N");
        }
    });
});  
function change_rack(rack_id) {

    $.ajax({
        url: 'get_device.php',
        type: 'post',
        data: {rack_id: rack_id},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#device").html(res.res);
            }
        }
    });
}
</script>