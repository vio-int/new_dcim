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

$assets = new Model();
$category = new AssetCategory();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if($_GET['model']){
    $filter['model'] = $_GET['model'];
}
if($_GET['category']){
    $filter['category'] = $_GET['category'];
}

$assets_arr = $assets->GetAssetModelListRow($filter);
$assets_res = json_decode(json_encode($assets_arr), true); 

$assets_list_arr = $assets->GetAssetModelList();
$assets_list_res = json_decode(json_encode($assets_list_arr), true); 

$category_list_arr = $category->GetCategoryList();
$category_list_res = json_decode(json_encode($category_list_arr), true); 

/* echo "<pre>";
print_r($assets_res);
echo "<pre/>";exit; */

// Delete Code Start
if(isset($_POST['action']) && $_POST["action"]=="Delete"){
    header('Content-Type: application/json');
    $response=false;
    if(isset($_POST["TransferTo"])){
        $assets->PortID=$_POST['PortID'];
        if($assets->DeleteObject($_POST["TransferTo"])){
            $response=true;
        }
    }
    echo json_encode($response);
    exit;
}
// END - AJAX
// URL PARAMETERS
$mark = "?";
if(!empty($_SERVER['QUERY_STRING'])){
    $cur_page = $_GET['page'];
    $que_string = str_replace("page={$cur_page}", "", $_SERVER['QUERY_STRING']);
    $que_string = rtrim($que_string,"&");
    if($que_string!="")
    {
        $mark = "?".$que_string."&";
    }
}
// END OF URL PARAMETERS CODE
// Export Code Start
if(isset($_GET['export'])){
    $response = $assets->ExportReport($filter);
    exit;
}
// Export Code End
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
                        <li>Assets Model</li>
                    </ol>
                    <h1>Models</h1>
                </div>
                <div class="col-sm-3">
                    <a href="asset_model.php" class="btn btn-primary">
                        <span class="fa fa-plus" aria-hidden="true"></span> Add
                    </a>
                    <a href="javascript:void(0);" class="btn btn-info">
                        <span class="fa fa-download" aria-hidden="true"></span> Import
                    </a>
                    <a href="<?php echo $mark."export=xls"; ?>" class="btn btn-success">
                        <span class="fa fa-upload" aria-hidden="true"></span> Export
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
                                        <th class="asc orderable"><a href="?sort=a.name&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="orderable"><a href="?sort=a.manufacture_id&sort_by=<?= $sort_by ?>">Manufacture</a></th>
                                        <th class="orderable"><a href="?sort=a.total_assets&sort_by=<?= $sort_by ?>">Assets</a></th>
                                        <th class="orderable"><a href="?sort=a.model_no&sort_by=<?= $sort_by ?>">Model No</a></th>
                                        <th class="orderable"><a href="?sort=a.category_id&sort_by=<?= $sort_by ?>">Category</a></th>
                                        <th class="orderable"><a href="?sort=a.eol&sort_by=<?= $sort_by ?>">EOL</a></th>
                                        <th class="orderable"><a href="?sort=dp.note&sort_by=<?= $sort_by ?>">Notes</a></th>
                                        <th class="">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if(count($assets_res) > 0){
                                        foreach($assets_res as $res) { ?>
                                    <tr>
                                        <td><?php echo $res['Name'] ?></td>
                                        <td><?php echo $res['Manufacture_name'] ?></td>
                                        <td><?php echo $res['Total_assets'] ?></td>
                                        <td><?php echo $res['Model_no'] ?></td>
                                        <td><?php echo $res['Category_name'] ?></td>
                                        <td><?php echo $res['EOL'] ?></td>
                                        <td><?php echo $res['Note'] ?></td>
                                        <td><a href="asset_model.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                        <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a></td>
                                    </tr>    
                                    <?php } } else { ?>
                                    <tr class="text-center"><td colspan="15">Record not found!</td></tr>
                                    <?php } ?>
                                </tbody>
                                <?php if(count($assets_res) > 0){ ?>
                                <tfoot>
                                    <tr><td colspan="15" class="text-right">
                                        <?php 
                                            $limit = 15;
                                            
                                            $total_rec = $assets->GetDashModelList(); 
                                           
                                            $total_pages = ceil($total_rec['total_models'] / $limit); 
                                            $pagLink = "<ul class='pagination'><li class='page-item'><a class='page-link' href='".$mark."page=1'>&laquo;</a></li>"; 
                                            for ($i=1; $i<=$total_pages; $i++) {
                                            $selected = "";
                                            if($_GET['page'] == $i)
                                            {
                                                $selected = "active";
                                            } else if(empty($_GET['page'])){
                                                $selected = "active";
                                            }
                                            $pagLink .= "<li class='page-item'><a class='page-link ".$selected."' href='".$mark."page=".$i."'>".$i."</a></li>";	
                                            }
                                            echo $pagLink . "<li class='page-item'><a class='page-link' href='".$mark."page=".$total_pages."'>&raquo;</a></li></ul>"; 
                                        ?>
                                    </td></tr>
                                </tfoot>
                                <?php } ?>
                            </table>
                        </div>
                    </form>
                    <div class="clearfix"></div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="fa fa-search" aria-hidden="true"></span>
                            <strong>Search</strong>
                        </div>
                        <div class="panel-body">
                            <form action="asset_model_list.php" method="get" class="form">
                                <div class="form-group">
                                    <label for="id_model">Models</label>
                                    <select name="model" placeholder="Models" id="id_model" class="form-control">
                                        <option value=""> -- Select -- </option>
                                        <?php foreach($assets_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['model']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="id_category">Category</label>
                                    <select name="category" placeholder="None" id="id_category" class="form-control">
                                        <option value="">-- Select --</option>
                                        <?php foreach($category_list_res as $val) { ?>
                                        <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID']==$_GET['category']?'selected':''?>><?php echo $val['Name'] ?></option>
                                        <?php } ?>
                                        
                                    </select>
                                </div>
                                
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fa fa-search" aria-hidden="true"></span> Apply
                                    </button>
                                    <a href="asset_model_list.php" class="btn btn-default" style="line-height:1 !important">
                                        <span class="fa fa-remove" aria-hidden="true"></span> Clear
                                    </a>
                                </div>
                            </form>
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