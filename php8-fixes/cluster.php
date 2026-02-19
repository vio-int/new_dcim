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

	$mfg = new Cluster();
        $cluster_type = new ClusterType();
        $cluster_group = new ClusterGroup();
        $location = new Location();
        
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
            $mfg->Type=trim($_POST["type"]);
            $mfg->Group=trim($_POST["group"]);
            $mfg->Location=trim($_POST["location"]);
            $mfg->Tag=trim($_POST["tag"]);
            $mfg->Comment=trim($_POST["comment"]);
            
            if($mfg->Name != null && $mfg->Name != ""){
                    if($_POST["action"]=="Create"){
                            if($mfg->CreateObject()){
                                    header('Location: '.redirect("cluster.php?PortID=$mfg->PortID"));
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
	$mfgList=$mfg->GetClusterList();
        $LocationList=$location->GetLocationList();
        $TypeList = $cluster_type->GetClusterTypeList();
        $GroupList = $cluster_group->GetClusterGroupList();
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
                    location.href='cluster.php?PortID='+this.value;
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
                <li><a href="cluster_list.php">Cluster</a></li>
                <li><?php echo $mfg->PortID!=""?'Edit':'Add';?> Cluster</li>
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
    <div class="panel-heading"><strong>Cluster</strong></div>
    <div class="panel-body">
    <div class="form-group">
       <label class="col-sm-3" for="name">',__("Name"),'</label>
       <div class="col-sm-9">        
       <input type="hidden" name="action" value="query"><select name="PortID" id="PortID" class="form-control">
       <option value=0>',__("Cluster Name"),'</option>';

            foreach($mfgList as $mfgRow){
                    if($mfg->PortID==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }

    echo '	</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="name">',__("Name"),'</label>
       <div class="col-sm-9">    
       <input type="text" class="form-control validate[required,minSize[1],maxSize[40]]" name="name" id="name" maxlength="40" value="',$mfg->Name,'">
       </div>    
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="type">',__("Type"),'</label>
       <div class="col-sm-9">        
       <select name="type" id="type" class="form-control">
       <option value="">',__("-- Select --"),'</option>';
            foreach($TypeList as $mfgRow){
                    if($mfg->Type==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                    echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="group">',__("Group"),'</label>
       <div class="col-sm-9">        
       <select name="group" id="group" class="form-control">
       <option value="">',__("-- Select --"),'</option>';
            foreach($GroupList as $mfgRow){
                if($mfg->Group==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
    echo '</select></div>
    </div>
    <div class="form-group">
       <label class="col-sm-3" for="location">',__("Location"),'</label>
       <div class="col-sm-9">        
       <select name="location" id="location" class="form-control">
       <option value="">',__("-- Select --"),'</option>';
            foreach($LocationList as $mfgRow){
                if($mfg->Location==$mfgRow->PortID){$selected=" selected";}else{$selected="";}
                echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
            }
    echo '</select></div>
    </div>
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading"><strong>Tag</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="tag">', __("Tag"), '</label>
           <div class="col-sm-9">    
           <input type="text" class="form-control" name="tag" id="tag" value="', $mfg->Tag, '">
           </div>    
        </div>
    </div>
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading"><strong>Comments</strong></div>
    <div class="panel-body">
        <div class="form-group">
           <label class="col-sm-3" for="comment">', __("Comments"), '</label>
           <div class="col-sm-9">    
           <textarea class="form-control" name="comment" id="comment">', $mfg->Comment, '</textarea>
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
    echo '&nbsp;<a href="cluster_list.php" class="btn-panel btn-success">',__("Cancel"),'</a>';
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
</script>
