<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );

	$subheader=__("Room");
        $footer_text = "";

	if(!$person->SiteAdmin){
            // No soup for you.
            header('Room: '.redirect());
            exit;
	}

	$mfg=new Room();
        
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
            $mfg->RoomNo=trim($_POST["room_no"]);
            $mfg->Location=trim($_POST["location"]);
            $mfg->Rows=trim($_POST["rows"]);
            $mfg->Columns=trim($_POST["columns"]);
            $mfg->Rows_per_rack=trim($_POST["rows_per_rack"]);
            $mfg->Group_columns=trim($_POST["group_columns"]);
            $mfg->Group_rows=trim($_POST["group_rows"]);
            $mfg->Front_picture = trim($_FILES['front_picture']["name"]);
            $mfg->Front_pic = $_POST['front_pic'];
            
            if($mfg->Name != null && $mfg->Name != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateObject()){
                                    header('Room: '.redirect("room.php?PortID=$mfg->PortID"));
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
	$mfgList=$mfg->GetRoomList();
        $LocationList=$mfg->GetParentLocationList();
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
                    location.href='room.php?PortID='+this.value;
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
                <li><a href="room_list.php">Room</a></li>
                <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Room</li>
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
<form id="mform" method="POST" enctype="multipart/form-data">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Room</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-5" for="name">',__("Name"),'</label>
       <div class="col-sm-7">        
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("New Room"),'</option>';

            foreach($mfgList as $mfgRow){
                    if($mfg->PortID==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }

    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-5" for="location">',__("Location"),'</label>
       <div class="col-sm-7">        
       <select name="location" id="location" class="form-control">
       <option value="">',__("-- Select --"),'</option>';
            foreach($LocationList as $mfgRow){
                    if($mfg->Location==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-5" for="name">',__("Name"),'</label>
       <div class="col-sm-7">    
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="',$mfg->Name,'">
       </div>  
    </div>
    <div class="form-group">
       <label class="col-sm-5" for="room_no">',__("Room No"),'</label>
       <div class="col-sm-7">        
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="room_no" id="room_no" maxlength="40" value="',$mfg->RoomNo,'">
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-5" for="rows">',__("Rows"),'</label>
       <div class="col-sm-7">        
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="rows" id="rows" maxlength="40" value="',$mfg->Rows,'">
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-5" for="columns">',__("Columns"),'</label>
       <div class="col-sm-7">        
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="columns" id="columns" maxlength="40" value="',$mfg->Columns,'">
       </div>
    </div>
    <div class="form-group">
       <label class="col-sm-5" for="rack_rows">',__("How many Racks in a group ?"),'</label>
       <div class="col-sm-7">        
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="rows_per_rack" id="rows_per_rack" maxlength="40" value="',$mfg->Rows_per_rack,'">
       </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-5" for="group_columns">',__("How many group in a column ?"),'</label>
       <div class="col-sm-7">        
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="group_columns" id="group_columns" maxlength="40" value="',$mfg->Group_columns,'">
       </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-group">
       <label class="col-sm-5" for="group_rows">',__("How many group in a row ?"),'</label>
       <div class="col-sm-7">        
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="group_rows" id="group_rows" maxlength="40" value="',$mfg->Group_rows,'">
       </div>
    </div>
    <div class="clearfix"></div>';
        
        $frontweb_path = _MEDIA_URL . "room/{$mfg->Front_picture}";
        $frontfilename = _PATH . '/uploads/room' . DIRECTORY_SEPARATOR . $mfg->Front_picture;
        
        echo '<div class="form-group">
           <label class="col-sm-5" for="front_picture">', __("Picture File"), '</label>
           <div class="col-sm-7">
           <input type="file" class="form-control" name="front_picture" id="front_picture" value="', $mfg->Front_picture, '">
           <output id="filesInfo"></output>
           <input type="hidden" name="front_pic" id="front_pic" value="', $mfg->Front_pic, '">  
           <input type="hidden" name="front_pic_val" id="front_pic_val" value="">  
           </div>
        </div>
        <div class="clearfix"></div>';
        if (file_exists($frontfilename) && $mfg->Front_picture) {
        echo '<div class="col-sm-6 col-sm-offset-5"><img src="'.$frontweb_path.'" class="image-responsive" style="height:200px;width:320px;"></div>';
        }
echo '</div>
</div>   
<div class="text-center">';
	if($mfg->PortID >0){
            echo '<button type="submit" class="btn btn-primary btn-lg" name="action" value="Update">',__("Update"),'</button>';
	}else{
            echo '<button type="submit" name="action" class="btn btn-primary btn-lg" value="Create">',__("Create"),'</button>';
	}
        echo '&nbsp;<a href="room_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
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
    $(document).ready(function () {
        // Code Start
        function frontfileSelect(evt) {
            if (window.File && window.FileReader && window.FileList && window.Blob) {
                var files = evt.target.files;

                var result = '';
                var file;
                for (var i = 0; file = files[i]; i++) {
                    // if the file is not an image, continue
                    if (!file.type.match('image.*')) {
                        continue;
                    }

                    reader = new FileReader();
                    reader.onload = (function (tFile) {
                        return function (evt) {
                            var div = document.createElement('div');
                            div.innerHTML = '<img style="width: 50px; height:30px;" src="' + evt.target.result + '" />';
                            $("#front_pic_val").val(evt.target.result);
                            document.getElementById('filesInfo').appendChild(div);
                        };
                    }(file));
                    reader.readAsDataURL(file);
                }
            } else {
                alert('The File APIs are not fully supported in this browser.');
            }
        }
        
        document.getElementById('front_picture').addEventListener('change', frontfileSelect, false);
        
        // Code End
        $('#descending').click(function () {
            if ($(this).prop("checked") == true) {
                $(this).val("Y");
            } else if ($(this).prop("checked") == false) {
                $(this).val("N");
            }
        });
        $('#front_picture').change(function (e) {
            var fileName = e.target.files[0].name;
            $("#front_pic").val(fileName);
        });
    });
</script>
