<?php
# catch for assholes that don't read the install instructions
if (!file_exists("db.inc.php")) {
    require_once( "preflight.inc.php" );
    exit;
}
/* 	if ( ! $_SERVER["HTTPS"] ) {
  printf( "<meta http-equiv='refresh' content='0; url='https://%s'>", $_SERVER["SERVER_NAME"] );
  exit();
  } */

require_once( 'db.inc.php' );
require_once( 'facilities.inc.php' );

$subheader = __("Data Center Operations Metrics");
$footer_text = "";

// SORTING CODE 
$sort_by = "Asc";
$sort_on = "a.id";
if($_GET['sort'] != "")
{
    $sort_on = $_GET['sort'];
}
if($_GET['sort_by'] != "")
{
    $sort_by = $_GET['sort_by']=="Asc"?"Desc":"Asc";
}
// END OF CODE

$assets = new Asset();

$assets_arr = $assets->GetStatusListRows();
$assets_res = json_decode(json_encode($assets_arr), true); 

/* echo "<pre>";
print_r($assets_res);
echo "<pre/>";exit; */

// Delete Code Start
if(isset($_POST['action']) && $_POST["action"]=="Delete"){
    header('Content-Type: application/json');
    $response=false;
    if(isset($_POST["TransferTo"])){
        $assets->PortID=$_POST['PortID'];
        if($assets->DeleteStatus($_POST["TransferTo"])){
            $response=true;
        }
    }
    echo json_encode($response);
    exit;
}

?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Data Center Inventory</title>
        <!-- Favicon -->
        <link type="image/x-icon" href="images/favicon.ico" rel="shortcut icon" />
        
        <link rel="stylesheet" href="css/inventory.php" type="text/css">
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css" />
        <![endif]-->

        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
    </head>
    <body>
    <?php include('header_dcim.inc.php'); ?>
        
    <!-- LISTING CODE START -->
        <div class="container wrapper">
            <div class="main">
            <div class="row">
                <!-- BREADCRUMBS CODE START -->
                <div class="col-sm-9">
                    <ol class="breadcrumb">
                        <li><a href="index_dcim.php">Dashboard</a></li>
                        <li>Status</li>
                    </ol>
                    <h1>Status</h1>
                </div>
                <div class="col-sm-3">
                    <a href="asset_status.php" class="btn btn-primary">
                        <span class="fa fa-plus" aria-hidden="true"></span> Add
                    </a>
                </div>
                <!-- END OF BREADCRUMBS CODE -->
                <div class="col-md-9">
                    <form method="post" class="form form-horizontal">
                        <input type='hidden' name='csrfmiddlewaretoken'/>
                        <input type="hidden" name="return_url" value="/dcim/devices/" />
                        <div class="table-responsive">
                            <table class="table table-hover table-headings table-striped">
                                <thead>
                                    <tr>
                                        <!-- <th class="pk"><input type="checkbox" class="toggle" title="Toggle all" /></th>-->
                                        <th class="asc orderable"><a href="?sort=a.status&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=a.status_type&sort_by=<?= $sort_by ?>">Status Type</a></th>
                                        <th class="orderable"><a href="?sort=a.status_type&sort_by=<?= $sort_by ?>">Assets</a></th>
                                        <th class="">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if(count($assets_res) > 0){
                                        foreach($assets_res as $res) { ?>
                                    <tr>
                                        <td><?php echo $res['Status_name'] ?></td>
                                        <td><?php echo $res['Status_type'] ?></td>
                                        <td><?php echo $res['Total_assets'] ?></td>
                                        <td><a href="asset_status.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                        <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a></td>
                                    </tr>    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="15">Record not found!</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>About Status Labels</strong></div>
                        <div class="panel-body">
                                        <p>Status labels are used to describe the various states your assets could be in. They may be out for repair, lost/stolen, etc. You can create new status labels for deployable, pending and archived assets.</p>

                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body">
                          <p><i class="fa fa-circle text-success"></i> <strong>Deployable</strong>: These assets can be checked out. Once they are assigned, they will assume a meta status of <i class="fa fa-circle text-info"></i> <strong>Deployed</strong>.</p>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-body">
                              <p><i class="fa fa-circle text-danger"></i> <strong>Pending</strong>: These assets can not yet be assigned to anyone, often used for items that are out for repair, but are expected to return to circulation.</p>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <p><i class="fa fa-times text-danger"></i> <strong>Undeployable</strong>: These assets cannot be assigned to anyone.</p>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-body">
                            <p><i class="fa fa-times text-danger"></i> <strong>Archived</strong>: These assets cannot be checked out, and will only show up in the Archived view. This is useful for retaining information about assets for budgeting/historic purposes but keeping them out of the day-to-day asset list.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="push"></div>
            </div>
        </div>
    <!-- LISTING CODE END -->
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
    $(document).ready(function() {
        $('.delete').click(function(){
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto=(typeof(objectid)=='undefined')?0:objectid;
                $.post('',{PortID: $(this).data("id"), TransferTo: transferto, action: 'Delete'},function(data){
                    if(data){
                            location.href='';
                    }else{
                            alert("Something's gone horrible wrong");
                    }
                });
            }
        });
    });
</script>