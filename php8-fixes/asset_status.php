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

	$mfg=new Asset();
        
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
            $asset_list=$mfg->GetStatusByID();
            
            $asset_list_res = json_decode(json_encode($asset_list), true); 
            
	}

	$status="";
	if(isset($_POST["action"])&&(($_POST["action"]=="Create")||($_POST["action"]=="Update"))){
            $mfg->PortID=$_POST["PortID"];
            $mfg->Name=trim($_POST["name"]);
            $mfg->Status_type=trim($_POST["status_type"]);
            
            
            if($mfg->Name != null && $mfg->Name != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateStatus()){
                                    header('Location: '.redirect("asset.php?PortID=$mfg->PortID"));
                            }else{
                                    $status=__("Error adding new object");
                            }
                    }else{
                            $status=__("Updated");
                            $mfg->UpdateStatus();
                    }
            }
            //We either just created a manufacturer or updated it so reload from the db
            $mfg->GetStatusList();    
	}
	$mfgList=$mfg->GetAssetList();
        
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
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/datepicker.css" type="text/css">
  
  
  <!--[if lt IE 9]>
  <link rel="stylesheet"  href="css/ie.css" type="text/css">
  <![endif]-->
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
  <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
  <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>
  <script type="text/javascript" src="scripts/bootstrap-datepicker.js"></script>
  <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>-->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

  <style type="text/css">
	#using { margin-top: 1em; }
  </style>

  <script type="text/javascript">
	$(document).ready(function() {
            $('#purchase_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            });
            /* $('#warranty_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            });
            $('#manufacture_date').datepicker({
                format: 'm/d/yyyy',
                autoclose: true
            }); */
            $('#mform').validationEngine({});
            $('#PortID').change(function(e){
                    location.href='asset.php?PortID='+this.value;
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
                <li><a href="asset_status_list.php">Status</a></li>
                <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Assets Status</li>
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
<form id="status_form" method="POST" enctype="multipart/form-data">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Asset Status Management</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="name">',__("Name"),'</label>
        <div class="col-sm-9">  
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="status_name" id="status_name" maxlength="40" value="',$asset_list_res[0]['Status_name'],'">
        </div>   
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="status_type">',__("Status Type"),'</label>
       <div class="col-sm-9">      
       <select class="form-control" name="status_type" id="status_type">
        <option value="">-- Select --</option>
        <option value="Deployable" ',$asset_list_res[0]['Status_type']=="Deployable"?"selected":"",'>Deployable</option>
        <option value="Pending" ',$asset_list_res[0]['Status_type']=="Pending"?"selected":"",'>Pending</option>
        <option value="Undeployable" ',$asset_list_res[0]['Status_type']=="Undeployable"?"selected":"",'>Undeployable</option>
        <option value="Archived" ',$asset_list_res[0]['Status_type']=="Archived"?"selected":"",'>Archived</option>
       </select>
       </div>
    </div>
    
    <div class="clearfix"></div>
<div class="text-center">';
	
        if($_GET['PortID'] >0){
            echo '<input type="hidden" name="status_id" value="'.$_GET['PortID'].'">';
            echo '<button type="button" name="status_update_submit" id="status_update_submit" class="btn btn-primary btn-lg">',__("Update"),'</button>';
	}else{
            echo '<button type="button" name="status_submit" id="status_submit" class="btn btn-primary btn-lg">',__("Create"),'</button>';
	}
        echo '&nbsp;<a href="asset_status_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
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
    $("#status_submit").click(function(){
        $.ajax({
            url: 'ajax_create_status.php',
            type: 'post',
            data: $("#status_form").serialize(),
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    location.href='asset_status_list.php';
                }
            }
        });
    });
    $("#status_update_submit").click(function(){
        $.ajax({
            url: 'ajax_update_status.php',
            type: 'post',
            data: $("#status_form").serialize(),
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    location.href='asset_status_list.php';
                }
            }
        });
    });
});  
</script>