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

	$mfg=new Capacity();
        $Location = new Location();
        $Rack = new Rack();
        $Room = new Room();
        
        $Location_list = $Location->GetLocationList();
        $Rack_list = $Rack->GetRackList();
        $Room_list = $Room->GetRoomList();
        
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
            
            $Location_list = $Location->GetLocationList();
            $Rack_list = $Rack->GetLocationRackList($mfg->Site);
            $inner_filter = array();
            $inner_filter_2 = array();
            $inner_filter['sort_on'] = "r.id";
            $inner_filter['sort_by'] = "Asc";
            $inner_filter_2['sort_on'] = "r.id";
            $inner_filter_2['sort_by'] = "Asc";
            if($mfg->Capacity_type != "is_location" && $mfg->Site !="")
            {
                $inner_filter['location'] = $mfg->Site;
                $inner_filter_2['room'] = $mfg->Room;
            }
            
            $Room_list = $Room->GetRoomListRows($inner_filter);
            $Rack_list = $Rack->GetRackListRows($inner_filter_2);
            
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->PortID=$_POST["PortID"];
            $mfg->Name=trim($_POST["name"]);
            $mfg->Capacity_type = $_POST['capacity_type'];
            $mfg->Space=trim($_POST["space"]);
            $mfg->Power=$_POST["power"];
            $mfg->Site=trim($_POST["site"]);
            $mfg->Rack=trim($_POST["rack"]);
            $mfg->Room=trim($_POST["room"]);
            $mfg->Tag=trim($_POST["tag"]);

            if($mfg->Name != null && $mfg->Name != ""){
                if($_POST["action"]=="Create"){
                    if($mfg->CreateObject()){
                            header('Location: '.redirect("capacity.php?PortID=$mfg->PortID"));
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
	$mfgList=$mfg->GetCapacityList();
        //print_r($mfgConnectorList);exit;
?>
<!doctype html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  
  <title>VIO DCIM Room Class Templates</title>
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
                    location.href='capacity.php?PortID='+this.value;
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
    <div class="panel-heading"><strong>Capacity</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="PortID">',__("Name"),'</label>
       <div class="col-sm-9">    
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("New Capacity"),'</option>';
            foreach($mfgList as $mfgRow){
                if($mfg->PortID==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
    echo '</select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="name">',__("Name"),'</label>
        <div class="col-sm-9">      
        <input type="text" class="form-control" name="name" id="name" value="',$mfg->Name,'">
        </div>    
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="name">',__("Capacity For"),'</label>
        <div class="col-sm-9">      
        <input type="radio" class="radio-inline capacity_type" name="capacity_type" value="is_location" id="is_location" 
        ',$mfg->Capacity_type=="is_location"?'checked':'','>Location
        <input type="radio" class="radio-inline capacity_type" name="capacity_type" value="is_room" id="is_room" 
        ',$mfg->Capacity_type=="is_room"?'checked':'','>Room
        <input type="radio" class="radio-inline capacity_type" name="capacity_type" value="is_rack" id="is_rack" 
        ',$mfg->Capacity_type=="is_rack"?'checked':'','>Rack
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
        <label class="col-sm-3" for="site">',__("Location"),'</label>
        <div class="col-sm-9">  
        <select name="site" id="site" class="form-control" onchange="change_location(this.value)">
             <option value="">',__("-- Select --"),'</option>';
             foreach($Location_list as $mfgRow){
                    if($mfg->Site==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
             }
         echo '
        </select></div>
    </div>
    <div class="form-group" id="room_div"  style="display:',$mfg->Capacity_type=='is_room'||$mfg->Capacity_type=='is_rack'?'block':'none','">
        <label class="col-sm-3" for="room">',__("Room"),'</label>
        <div class="col-sm-9">  
        <select name="room" id="room" class="form-control" onchange="change_room(this.value)">
            <option value="">',__("-- Select --"),'</option>';
            foreach($Room_list as $mfgRow){
                if($mfg->Room==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    <div class="form-group" id="rack_div" style="display:',$mfg->Capacity_type=='is_rack'?'block':'none','">
        <label class="col-sm-3" for="rack">',__("Rack"),'</label>
        <div class="col-sm-9">  
        <select name="rack" id="rack" class="form-control">
            <option value="">',__("-- Select --"),'</option>';
            foreach($Rack_list as $mfgRow){
                if($mfg->Rack==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
        echo '
        </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="space">',__("Space"),'</label>
        <div class="col-sm-9">      
        <input type="text" class="form-control" name="space" id="space" value="',$mfg->Space,'">
        </div>    
    </div>
    <div class="form-group">
        <label class="col-sm-3" for="power">',__("Power"),'</label>
        <div class="col-sm-9">      
        <input type="text" class="form-control" name="power" id="power" value="',$mfg->Power,'">
        </div>
    </div>
</div>
</div>   
<div class="panel panel-default">
    <div class="panel-heading"><strong>Tag</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="tag">',__("Tag"),'</label>
           <div class="col-sm-9">      
           <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="tag" id="tag" maxlength="40" value="',$mfg->Tag,'">
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
        echo '&nbsp;<a href="capacity_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
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
    $('.capacity_type').on('click', function(){
        if($(this).val() == 'is_location') {
            $('#room_div').fadeOut("slow");
            $('#rack_div').fadeOut("slow");
        } else if($(this).val() == 'is_room') {
            $('#room_div').fadeIn("slow");
            $('#rack_div').fadeOut("slow");
            $('#site').trigger("change");
        } else if($(this).val() == 'is_rack') {
            $('#room_div').fadeIn("slow");
            $('#rack_div').fadeIn("slow");
            $('#site').trigger("change");
            $('#room').trigger("change");
        } else {
            $('#room_div').fadeOut("slow");
            $('#rack_div').fadeOut("slow");
        }
    });
});  
function change_location(location_id) {
    $.ajax({
        url: 'get_room.php',
        type: 'post',
        data: {location_id: location_id},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#room").html(res.res);
            }
        }
    });
}
function change_room(room_id) {

    $.ajax({
        url: 'get_room_rack.php',
        type: 'post',
        data: {room_id: room_id},
        dataType: 'JSON',
        success: function (res) {
            if (res.status == 'success') {
                $("#rack").html(res.res);
            }
        }
    });
}
</script>
