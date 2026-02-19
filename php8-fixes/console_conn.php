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

	$mfg = new ConsoleConn();
        $device = new Device_new();
        $console_server = new ConsoleServer();
        
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
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->PortID=$_POST["PortID"];
            $mfg->Name=trim($_POST["name"]);
            $mfg->Console_server=trim($_POST["console_server"]);
            $mfg->Device=trim($_POST["device"]);
            $mfg->Port=trim($_POST["port"]);
            $mfg->Console_port=trim($_POST["console_port"]);
            
            if($mfg->Name != null && $mfg->Name != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateObject()){
                                    header('Location: '.redirect("console_conn.php?PortID=$mfg->PortID"));
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
	$mfgList=$mfg->GetConsoleConnList();
        $DeviceList=$device->GetDevice_newList();
        $ServerList= $console_server->GetConsoleServerList();
        //int_r($mfgList);exit;
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
                    location.href='console_conn.php?PortID='+this.value;
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
                <li><a href="console_conn_list.php">Console Connection</a></li>
                <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Console Connection</li>
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
    <div class="panel-heading"><strong>Console Connection</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="name">',__("Name"),'</label>
       <div class="col-sm-9">        
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("Connection Name"),'</option>';

            foreach($mfgList as $mfgRow){
                    if($mfg->PortID==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }

    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">',__("Name"),'</label>
       <div class="col-sm-9">    
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="',$mfg->Name,'">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="console_server">',__("Console Server"),'</label>
       <div class="col-sm-9">
            <select name="console_server" id="console_server" class="form-control" onchange="server_change(this.value)">
            <option value="">',__("-- Select --"),'</option>';
            foreach($ServerList as $mfgRow){
                    if($mfg->Console_server==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
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
            foreach($DeviceList as $mfgRow){
                    if($mfg->Device==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="port">',__("Port"),'</label>
       <div class="col-sm-9">
       <input type="text" class="form-control" name="port" id="port" value="',$mfg->Port,'">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="console_port">',__("Console Port"),'</label>
       <div class="col-sm-9">
       <input type="text" class="form-control" name="console_port" id="console_port" value="',$mfg->Console_port,'">
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
    echo '&nbsp;<a href="console_conn_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
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
    
}); 
function server_change(server_id){
    $.ajax({
        url: 'get_server_device.php',
        type: 'post',
        data: {server_id: server_id},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#device").html(res.res);
            }
        }
    });
}
</script>
