<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );

	$subheader=__("Service");
        $footer_text = "";

	if(!$person->SiteAdmin){
            // No soup for you.
            header('Service: '.redirect());
            exit;
	}

	$mfg=new DeviceInterface();
        $Device=new Device_new();
        
        $Device_list = $Device->GetDevice_newList();
        
	if(isset($_REQUEST["PortID"]) && $_REQUEST["PortID"] >0){
            $mfg->PortID=(isset($_POST['PortID']) ? $_POST['PortID'] : $_GET['PortID']);
            $mfg->GetOrderByID();
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->PortID=$_POST["PortID"];
            $mfg->Name=trim($_POST["name"]);
            $mfg->Form_factor=trim($_POST["form_factor"]);
            $mfg->Enable=trim($_POST["enable"]);
            $mfg->Parent_lag=trim($_POST["parent_lag"]);
            $mfg->Mac_address=trim($_POST["mac_address"]);
            $mfg->Is_oob=trim($_POST["is_oob"]);
            $mfg->Mode=trim($_POST["mode"]);
            $mfg->MTU=trim($_POST["mtu"]);
            $mfg->Description=trim($_POST["description"]);
            $mfg->Tag=trim($_POST["tag"]);
            $mfg->Device=trim($_POST["device"]);
            
            if($mfg->Name != null && $mfg->Name != ""){
                if($_POST["action"]=="Create"){
                    if($mfg->CreateObject()){
                        header('Service: '.redirect("device_interface_add.php?PortID=$mfg->PortID"));
                    }else{
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
	$mfgList=$mfg->GetDeviceInterfaceList();
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
                    location.href='device_interface_add.php?PortID='+this.value;
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
<div class="col-sm-12">

<?php
// include( "sidebar.inc.php" );

echo '<div class="main">
    <div class="">
<h3>',$status,'</h3>
<div class="table-center"><div>
<form id="mform" method="POST">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Interface</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">',__("Name"),'</label>
       <div class="col-sm-9">
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("New Interface"),'</option>';

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
        <label class="col-sm-3" for="device">',__("Device"),'</label>
        <div class="col-sm-9">  
        <select name="device" id="device" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($Device_list as $mfgRow){
                if($mfg->Device==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="form_factor">',__("Form Factor"),'</label>
       <div class="col-sm-9">      
       <select name="form_factor" id="form_factor" class="form-control">
            <option value="">',__("-- Select --"),'</option>
            <optgroup label="Virtual interfaces">
                <option value="11" ',$mfg->Form_factor=="11"?"Selected":"",'>Virtual</option>
                <option value="12" ',$mfg->Form_factor=="12"?"Selected":"",'>Link Aggregation Group (LAG)</option>
            </optgroup>
            <optgroup label="Ethernet (fixed)">
                <option value="21" ',$mfg->Form_factor=="21"?"Selected":"",'>100BASE-TX (10/100ME)</option>
                <option value="22" ',$mfg->Form_factor=="22"?"Selected":"",'>1000BASE-T (1GE)</option>
                <option value="23" ',$mfg->Form_factor=="23"?"Selected":"",'>10GBASE-T (10GE)</option>
                <option value="24" ',$mfg->Form_factor=="24"?"Selected":"",'>10GBASE-CX4 (10GE)</option>
            </optgroup>
            <optgroup label="Ethernet (modular)">
                <option value="31" ',$mfg->Form_factor=="31"?"Selected":"",'>GBIC (1GE)</option>
                <option value="32" ',$mfg->Form_factor=="32"?"Selected":"",'>SFP (1GE)</option>
                <option value="33" ',$mfg->Form_factor=="33"?"Selected":"",'>SFP+ (10GE)</option>
                <option value="34" ',$mfg->Form_factor=="34"?"Selected":"",'>XFP (10GE)</option>
                <option value="35" ',$mfg->Form_factor=="35"?"Selected":"",'>XENPAK (10GE)</option>
                <option value="36" ',$mfg->Form_factor=="36"?"Selected":"",'>X2 (10GE)</option>
                <option value="37" ',$mfg->Form_factor=="37"?"Selected":"",'>SFP28 (25GE)</option>
                <option value="38" ',$mfg->Form_factor=="38"?"Selected":"",'>QSFP+ (40GE)</option>
                <option value="39" ',$mfg->Form_factor=="39"?"Selected":"",'>CFP (100GE)</option>
                <option value="310" ',$mfg->Form_factor=="310"?"Selected":"",'>CFP2 (100GE)</option>
                <option value="311" ',$mfg->Form_factor=="311"?"Selected":"",'>CFP4 (100GE)</option>
                <option value="312" ',$mfg->Form_factor=="312"?"Selected":"",'>Cisco CPAK (100GE)</option>
                <option value="313" ',$mfg->Form_factor=="313"?"Selected":"",'>QSFP28 (100GE)</option>
            </optgroup>
            <optgroup label="Wireless">
                <option value="41" ',$mfg->Form_factor=="41"?"Selected":"",'>IEEE 802.11a</option>
                <option value="42" ',$mfg->Form_factor=="42"?"Selected":"",'>IEEE 802.11b/g</option>
                <option value="43" ',$mfg->Form_factor=="43"?"Selected":"",'>IEEE 802.11n</option>
                <option value="44" ',$mfg->Form_factor=="44"?"Selected":"",'>IEEE 802.11ac</option>
                <option value="45" ',$mfg->Form_factor=="45"?"Selected":"",'>IEEE 802.11ad</option>
            </optgroup>
            <optgroup label="FibreChannel">
                <option value="51" ',$mfg->Form_factor=="51"?"Selected":"",'>SFP (1GFC)</option>
                <option value="52" ',$mfg->Form_factor=="52"?"Selected":"",'>SFP (2GFC)</option>
                <option value="53" ',$mfg->Form_factor=="53"?"Selected":"",'>SFP (4GFC)</option>
                <option value="54" ',$mfg->Form_factor=="54"?"Selected":"",'>SFP+ (8GFC)</option>
                <option value="55" ',$mfg->Form_factor=="55"?"Selected":"",'>SFP+ (16GFC)</option>
            </optgroup>
            <optgroup label="Serial">
                <option value="61" ',$mfg->Form_factor=="61"?"Selected":"",'>T1 (1.544 Mbps)</option>
                <option value="62" ',$mfg->Form_factor=="62"?"Selected":"",'>E1 (2.048 Mbps)</option>
                <option value="63" ',$mfg->Form_factor=="63"?"Selected":"",'>T3 (45 Mbps)</option>
                <option value="64" ',$mfg->Form_factor=="64"?"Selected":"",'>E3 (34 Mbps)</option>
            </optgroup>
            <optgroup label="Stacking">
                <option value="71" ',$mfg->Form_factor=="71"?"Selected":"",'>Cisco StackWise</option>
                <option value="72" ',$mfg->Form_factor=="72"?"Selected":"",'>Cisco StackWise Plus</option>
                <option value="73" ',$mfg->Form_factor=="73"?"Selected":"",'>Cisco FlexStack</option>
                <option value="74" ',$mfg->Form_factor=="74"?"Selected":"",'>Cisco FlexStack Plus</option>
                <option value="75" ',$mfg->Form_factor=="75"?"Selected":"",'>Juniper VCP</option>
                <option value="76" ',$mfg->Form_factor=="76"?"Selected":"",'>Extreme SummitStack</option>
                <option value="77" ',$mfg->Form_factor=="77"?"Selected":"",'>Extreme SummitStack-128</option>
                <option value="78" ',$mfg->Form_factor=="78"?"Selected":"",'>Extreme SummitStack-256</option>
                <option value="79" ',$mfg->Form_factor=="79"?"Selected":"",'>Extreme SummitStack-512</option>
            </optgroup>
            <optgroup label="Other">
                <option value="81" ',$mfg->Form_factor=="81"?"Selected":"",'>Other</option>
            </optgroup>
        </select>
       </div>    
    </div>
    <div class="form-group">
        <div class="col-sm-9 col-sm-offset-3">    
        <input type="checkbox" class="" name="enable" id="enable" value="',$mfg->Enable==""?"N":$mfg->Enable,'" ',$mfg->Enable=="Y"?"checked":"",'>
        <label for="Enable">',__("Enable"),'</label>  
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="parent_lag">',__("Parent LAG"),'</label>
        <div class="col-sm-9">      
        <select name="parent_lag" id="parent_lag" class="form-control">
            <option value="">',__("-- Select --"),'</option>
        </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="mtu">',__("MTU"),'</label>
        <div class="col-sm-9">      
        <select name="mtu" id="mtu" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            for($i=1;$i<11;$i++){
                echo '<option value="',$i,'" ',$mfg->MTU==$i?"Selected":"",'>',$i,'</option>';
            }    
        echo '</select>
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="mac_address">',__("MAC Address"),'</label>
       <div class="col-sm-9">
        <input type="text" class="form-control" name="mac_address" id="mac_address" value="',$mfg->Mac_address,'">
       </div>
    </div>
    <div class="form-group">
        <div class="col-sm-9 col-sm-offset-3">    
        <input type="checkbox" class="" name="is_oob" id="is_oob" value="',$mfg->Is_oob==""?"N":$mfg->Is_oob,'" ',$mfg->Is_oob=="Y"?"checked":"",'>
        <label for="is_oob">',__("OOB Management"),'</label><br/>
        <span>This interface is used only for out-of-band management</span>    
        </div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="description">',__("Description"),'</label>
       <div class="col-sm-9">      
        <input type="text" class="form-control" name="description" id="description" value="',$mfg->Description,'">
       </div>    
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="mode">',__("Mode"),'</label>
        <div class="col-sm-9">      
        <select name="mode" id="mode" class="form-control">
            <option value="">',__("-- Select --"),'</option>
            <option value="Access" ',$mfg->Mode=="Access"?"Selected":"",'>Access</option>
            <option value="Tagged" ',$mfg->Mode=="Tagged"?"Selected":"",'>Tagged</option>
            <option value="Tagged All" ',$mfg->Mode=="Tagged All"?"Selected":"",'>Tagged All</option>    
        </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="tag">',__("Tag"),'</label>
        <div class="col-sm-9">      
        <input type="text" class="form-control" name="tag" id="tag" value="',$mfg->Tag,'">
        </div>    
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
    $('#enable').click(function(){
        if($(this).prop("checked") == true){
            $(this).val("Y");
        }
        else if($(this).prop("checked") == false){
            $(this).val("N");
        }
    });
    $('#is_oob').click(function(){
        if($(this).prop("checked") == true){
            $(this).val("Y");
        }
        else if($(this).prop("checked") == false){
            $(this).val("N");
        }
    });
});
</script>
