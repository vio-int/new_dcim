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
$sort_on = "c.id";
if ($_GET['sort'] != "") {
    $sort_on = $_GET['sort'];
}
if ($_GET['sort_by'] != "") {
    $sort_by = $_GET['sort_by'] == "Asc" ? "Desc" : "Asc";
}
// END OF CODE

$Asset = new AssetCategory();

$filter = array();
$filter['sort_on'] = $sort_on;
$filter['sort_by'] = $sort_by;

if ($_GET['category']) {
    $filter['category'] = $_GET['category'];
}

$Asset_arr = $Asset->GetCategoryListRows($filter);
$Asset_res = json_decode(json_encode($Asset_arr), true);

$Asset_cat_list = $Asset->GetCategoryList();
$Asset_cat_list_res = json_decode(json_encode($Asset_cat_list), true);

/* echo "<pre>";
  print_r($Asset_res);
  echo "<pre/>";exit; */

// Delete Code Start
if (isset($_POST['action']) && $_POST["action"] == "Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $Asset->PortID = $_POST['PortID'];
        if ($Asset->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
// END - AJAX
// URL PARAMETERS
$mark = "?";
if (!empty($_SERVER['QUERY_STRING'])) {
    $cur_page = $_GET['page'];
    $que_string = str_replace("page={$cur_page}", "", $_SERVER['QUERY_STRING']);
    $que_string = rtrim($que_string, "&");
    if ($que_string != "") {
        $mark = "?" . $que_string . "&";
    }
}
// END OF URL PARAMETERS CODE
// Export Code Start
if (isset($_GET['export'])) {
    $response = $Asset->ExportReport($filter);
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
                            <li>Category</li>
                        </ol>
                        <h1>Category</h1>
                    </div>
                    <div class="col-sm-3">
                        <a href="asset_category.php" class="btn btn-primary">
                            <span class="fa fa-plus" aria-hidden="true"></span> Add
                        </a>
                        <a href="javascript:void(0);" class="btn btn-info">
                            <span class="fa fa-download" aria-hidden="true"></span> Import
                        </a>
                        <a href="<?php echo $mark . "export=xls"; ?>" class="btn btn-success">
                            <span class="fa fa-upload" aria-hidden="true"></span> Export
                        </a>
                    </div>
                    <!-- END OF BREADCRUMBS CODE -->
                    <div class="col-md-9">
                        <div class="table-responsive">
                            <table class="table table-hover table-headings table-striped">
                                <thead>
                                    <tr>
                                        <th class="orderable"><a href="?sort=c.name&sort_by=<?= $sort_by ?>">Name</a></th>
                                        <th class="primary_ip">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (count($Asset_res) > 0) {
                                        foreach ($Asset_res as $res) {
                                            ?>
                                            <tr>
                                                <td><?php echo $res['Name'] ?></td>
                                                <td><a href="asset_category.php?PortID=<?php echo $res['PortID'] ?>"><i class="fa fa-edit"></i></a>
                                                    <a href="javascript:void(0);" class="delete" data-id="<?php echo $res['PortID'] ?>"><i class="fa fa-trash"></i></a></td>
                                            </tr>    
                                        <?php }
                                    } else {
                                        ?>
                                        <tr class="text-center"><td colspan="6">Record not found!</td></tr> 
                                <?php } ?>
                                </tbody>
                                            <?php if (count($Asset_res) > 0) { ?>
                                    <tfoot>
                                        <tr><td colspan="6" class="text-right">
                                                <?php
                                                $limit = 15;

                                                $total_rec = $Asset->GetDashCategoryList();
                                                $total_pages = ceil($total_rec['total_category'] / $limit);
                                                $pagLink = "<ul class='pagination'><li class='page-item'><a class='page-link' href='" . $mark . "page=1'>&laquo;</a></li>";
                                                for ($i = 1; $i <= $total_pages; $i++) {
                                                    $selected = "";
                                                    if ($_GET['page'] == $i) {
                                                        $selected = "active";
                                                    } else if (empty($_GET['page'])) {
                                                        $selected = "active";
                                                    }
                                                    $pagLink .= "<li class='page-item'><a class='page-link " . $selected . "' href='" . $mark . "page=" . $i . "'>" . $i . "</a></li>";
                                                }
                                                echo $pagLink . "<li class='page-item'><a class='page-link' href='" . $mark . "page=" . $total_pages . "'>&raquo;</a></li></ul>";
                                                ?>
                                            </td></tr>
                                    </tfoot>
<?php } ?>
                            </table>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <span class="fa fa-search" aria-hidden="true"></span>
                                <strong>Search</strong>
                            </div>
                            <div class="panel-body">
                                <form action="asset_category_list.php" method="get" class="form">
                                    <div class="form-group">
                                        <label for="">Category</label>
                                        <select name="category" placeholder="Category Name" id="id_category" class="form-control">
                                            <option value="">-- Select --</option>
                                            <?php foreach ($Asset_cat_list_res as $val) { ?>
                                                <option value="<?php echo $val['PortID'] ?>" <?php echo $val['PortID'] == $_GET['category'] ? 'selected' : '' ?>><?php echo $val['Name'] ?></option>
<?php } ?>
                                        </select>
                                    </div>
                                    
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="fa fa-search" aria-hidden="true"></span> Apply
                                        </button>
                                        <a href="asset_category_list.php" class="btn btn-default" style="line-height:1 !important">
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
<?php if ($footer_text != "") { ?>
        <footer class="page-footer font-small footer">
            <spam><?php echo $footer_text; ?></spam>
        </footer>
<?php } ?>
    <!-- Footer -->
</html>
<script type="text/javascript">
    $(document).ready(function () {
        $('.delete').click(function () {
            if (confirm('Are you sure you want to delete this record ?')) {
                // If manufacturerid unset then just delete 
                transferto = (typeof (objectid) == 'undefined') ? 0 : objectid;
                $.post('', {PortID: $(this).data("id"), TransferTo: transferto, action: 'Delete'}, function (data) {
                    if (data) {
                        location.href = '';
                    } else {
                        alert("Something's gone horrible wrong");
                    }
                });
            }
        });
    });
</script>