<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Manage Maintenance");
$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Manage Maintenance: ' . redirect());
    exit;
}

$assets = new Asset();

$asset_list_res = $assets->GetAssetList();
$asset_list_rows = json_decode(json_encode($asset_list_res), true); 

$event_arr = array();

if(count($asset_list_rows) > 0)
{
    foreach($asset_list_rows as $val) { 

        $event_arr[] = array(
            "id" => $val['PortID'],
            "title" => $val['Name'],
            "description" => $val['Asset_tag'],
            "start" => $val['Next_main_date'],
            "status" => $val['Maintenance_status']
        );
    }
}
?>
<!doctype html>
<html>
    <head>
        <style type="text/css">
            #using { margin-top: 1em; }

            .fc-header-left,.fc-header-center{
                padding-top: 20px !important;
            }
            .fc-header-right{
                padding: 20px 10px 0px 0px !important;
            }
            .fc-header{
                background: #f8f8f8 !important;
            }
            .fc-state-default{
                color: #000 !important;
            }
            .fc-button{
                text-transform: capitalize;
            }
        </style>
        
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Device Class Templates</title>
        <!-- Favicon -->
        <link type="image/x-icon" href="images/favicon.ico" rel="shortcut icon" />
        
        <link rel="stylesheet" href="css/inventory.php" type="text/css">
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
        <link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css">
        <link rel="stylesheet" href="css/fullcalendar.css" type="text/css">
        <link rel="stylesheet" href="css/fullcalendar.print.css" type="text/css">
        <link rel="stylesheet" href="css/calendar.css" type="text/css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="scripts/sweetalert2/src/sweetalert2.css">
        <link rel="stylesheet" href="css/datepicker.css" type="text/css">


        <!--[if lt IE 9]>
        <link rel="stylesheet"  href="css/ie.css" type="text/css">
        <![endif]-->
        <script type="text/javascript" src="scripts/jquery.min.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui.min.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="scripts/jquery.validationEngine.js"></script>
        <script type="text/javascript" src="scripts/fullcalendar.js"></script>
        <script type="text/javascript" src="scripts/bootstrap-datepicker.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="scripts/sweetalert2/src/sweetalert2.all.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                /* $('#start_date').datepicker({
                    format: 'm/d/yyyy',
                    autoclose: true
                }); */
                
            });
        </script>
    </head>
    <body>
        <?php include( 'header_dcim.inc.php' ); ?>
        <div class="container wrapper">
            <div class="main">
                <!-- Breadcrumb code start -->
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li><a href="manage_maintenance.php">Manage Maintenance</a></li>
                        </ol>
                    </div>
                
                <!-- Breadcrumb code end -->
                
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"  style="border-top: 1px solid #dfe4e8;">
                        <div class="main-box clearfix" style="padding-bottom: 20px">
                            <div class="main-box-body table-responsive clearfix">
                                <div id='calendar'></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EVENT MODEL CODE START HERE -->
                <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Maintenance Detail</h4>
                            </div>
                            <form role="form" id="form_customer" data-parsley-validate>
                                <input type="hidden" name="edit_data_id" id="edit_data_id" value='<?php echo $args['3'] ?>'>

                                <div class="modal-body">
                                    <?php // echo form_open(site_url("calendar/add_event"), array("class" => "form-horizontal")) ?>
                                    <div class="form-group">
                                        <label for="event_name">Asset Name</label>
                                        <input type="text" class="form-control" id="event_name" name="name" value="<?= $customer_data['event_name'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Asset Tag</label>
                                        <input type="text" class="form-control" id="description" name="asset_tag" value="<?= $customer_data['description'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="start_date">Maintenance Date</label>
                                        <input type="text" class="form-control" id="start_date" name="maintenance_date" value="<?= $customer_data['start_date'] ?>" readonly="">
                                    </div>
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="">-- Select --</option>
                                            <option value="pendding">Pendding</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <!--<div class="form-group">
                                        <label for="end_date">End Date</label>
                                        <input type="text" class="form-control" id="end_date" name="end_date" value="<?= $customer_data['end_date'] ?>" required="">
                                    </div> -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="add_event" onclick="saveData()">Save</button>
                                    <?php //echo form_close() ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- EVENT MODEL CODE END -->
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
        var event_arr = '<?php echo json_encode($event_arr) ?>';
        var new_event_arr = $.parseJSON(event_arr);
        
        $('#calendar').fullCalendar({
            editable: false,
            aspectRatio: 1.8,
            scrollTime: '00:00',
            dragScroll: true,
            header: {
                left: 'promptResource today prev,next',
                center: 'title',
                right: 'timelineDay,timelineThreeDays,agendaWeek,month'
            }, dayClick: function (date, jsEvent, view) {
                /* date_last_clicked = $(this);
                $(this).css('background-color', '#bed7f3');
                $("#addModal").modal('show');

                var date = new Date(date),
                    mnth = ("0" + (date.getMonth() + 1)).slice(-2),
                    day = ("0" + date.getDate()).slice(-2);
                var final_date = [mnth, day, date.getFullYear()].join("/");
                $('#start_date').val(final_date);
                //console.log(final_date); */

            }, eventClick: function (event, jsEvent, view) {
                $('#event_name').val(event.title);
                $('#description').val(event.description);
                $('#status').val(event.status);

                //console.log(event.start);
                //console.log(event.end);
                //$('#start_date').val(moment(event.start).format('YYYY/MM/DD HH:mm'));
                if (event.start) {
                    var date = new Date(event.start),
                            mnth = ("0" + (date.getMonth() + 1)).slice(-2),
                            day = ("0" + date.getDate()).slice(-2);
                    var final_date = [mnth, day, date.getFullYear()].join("/");
                    $('#start_date').val(final_date);
                }
                if (event.end) {
                    var date = new Date(event.end),
                            mnth = ("0" + (date.getMonth() + 1)).slice(-2),
                            day = ("0" + date.getDate()).slice(-2);
                    var final_date = [mnth, day, date.getFullYear()].join("/");
                    $('#end_date').val(final_date);
                    //$('#end_date').val(moment(event.end).format('YYYY/MM/DD HH:mm'));
                } else {
                    var date = new Date(),
                            mnth = ("0" + (date.getMonth() + 1)).slice(-2),
                            day = ("0" + date.getDate()).slice(-2);
                    var final_date = [mnth, day, date.getFullYear()].join("/");
                    //$('#end_date').val(final_date);
                }
                $('#edit_data_id').val(event.id);
                $("#addModal").modal('show');
            }, resources: [

            ], events: new_event_arr,
            

        });
        
    });
    
    function saveData(edit) {
        var name = $("#name").val();
        var maintenance_date = $("#maintenance_date").val();
        
        if (name == "") {
            _jsError("Name is required!");
            return false;
        } else if (maintenance_date == "") {
            _jsError("Maintenance date is required!");
            return false;
        } 

        $.ajax({
            url: 'ajax_manage_maintenance.php',
            type : 'post',
            data: {saveData: 1, form_customer: $('#form_customer').serialize()},
            dataType: 'JSON',
            success: function (res) {

                if (res.status == 'success') {

                    if (res.action == 'update') {
                        swal({
                            text: "Maintenance updated successfully",
                            type: 'success',
                        });
                    } else {
                        swal({
                            text: "Maintenance inserted successfully",
                            type: 'success',
                        });
                    }

                    setTimeout(function () {
                        window.location.href = 'manage_maintenance.php';
                    }, 2000);
                } else {
                    $.each(res.errors, function(index, value) {
                        var error_index = index;
                        _jsError(value);
                        //return false;
                    });
                }
            }
        });
    }
    function _jsError(msg) {
        $.notify({
            message: msg
        },
                {
                    type: 'danger',
                    placement: {
                        from: "top",
                        align: "center"
                    },
                    showProgressbar: false,
                    delay: 1000,
                });
    }
    function _jsSuccess(msg) {
        $.notify({
            message: msg
        },
                {
                    type: 'success',
                    placement: {
                        from: "top",
                        align: "center"
                    },
                    showProgressbar: false,
                    delay: 1000,
                });
    }
</script>