<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$subheader = __("Port Compatibility Listing");
//$footer_text = "";

if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}

$mfg = new Simulation();
$Location = new Location();
$Rack = new Rack();
$Room = new Room();
$Device = new Device_new();

$Location_list_par = $Location->GetLocationList();
$Location_list = json_decode(json_encode($Location_list_par), true);
$Rack_list_par = $Rack->GetRackList();
$Rack_list = json_decode(json_encode($Rack_list_par), true);
$Room_list_par = $Room->GetRoomList();
$Room_list = json_decode(json_encode($Room_list_par), true);
$Device_list_par = $Device->GetDevice_newList();
$Device_list = json_decode(json_encode($Device_list_par), true);
?>
<!doctype html>
<style type="text/css">
    
</style>    
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <title>VIO DCIM Data Center Inventory</title>
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
        <script type="text/javascript" src="scripts/floating-1.12.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script> 
    </head>
    <body>
        <?php include( 'header_dcim.inc.php' ); ?>
        <div class="container wrapper">
            <div class="main">
                <div class="row">
                    <!-- BREADCRUMBS CODE START -->
                    <div class="col-sm-12">
                        <ol class="breadcrumb">
                            <li><a href="index_dcim.php">Dashboard</a></li>
                            <li>Room Simulation</li>
                        </ol>
                    </div>
                    <!-- END OF BREADCRUMBS CODE -->

                    <form id="mform" method="POST">
                        <!-- DIV FOR HIDDEN FIELDS -->
                        <div id="new_rack_ids"></div>
                        <div class="col-sm-3">
                            <div class="panel panel-default floatdiv">
                                <div class="panel-heading"><strong>Racks</strong></div>
                                <div class="panel-body">
                                    <?php
                                    if (count($Rack_list) > 0) {
                                        foreach ($Rack_list as $val) {
                                            ?>
                                            <div class="rack_div col-sm-5">
                                                <div draggable="true" ondragstart="drag(event)" id="device_<?= $val['PortID'] ?>" class="rack_list" data-height="<?php echo $val['Height'] ?>" data-power="<?php echo $val['Max_kw'] ?>" data-device="<?php echo $val['Name'] ?>">
                                                    <div class="col-sm-12 rack_title tooltip"><?php echo $val['Name'] ?></div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong> Room Simulation</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-sm-3" for="site">Location</label>
                                        <div class="col-sm-9">  
                                            <select name="site" id="site" class="form-control" onchange="change_location(this.value)">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($Location_list as $val) { ?>
                                                    <option value="<?php echo $val['PortID'] ?>"><?php echo $val['Name'] ?></option>
<?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3" for="room">Room</label>
                                        <div class="col-sm-9">  
                                            <select name="room" id="room" class="form-control" onchange="change_room(this.value)">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($Room_list as $val) { ?>
                                                    <option value=""><?php echo $val['Name']; ?></option>
<?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12 rack_str_div editable" id="rack_str" data-placeholder='Racks'>

                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="form-group text-center">
                                        <button type="button" id="submit" name="action" class="btn btn-primary btn-lg" value="Save">Save</button>
                                        <button type="button" id="commit" name="action" class="btn btn-primary btn-lg" value="Commit">Commit</button>
                                    </div>    
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="panel panel-default floatdiv">
                                <div class="panel-heading"><strong>Capacity</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-sm-4"></label>
                                        <div class="col-sm-4">  
                                            <label> Available Space</label>
                                        </div>
                                        <div class="col-sm-4">  
                                            <label> Available Power</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4">Location</label>
                                        <div class="col-sm-4">  
                                            <span id="location_capacity"></span>
                                            <input type="hidden" name="site_cap_input" id="site_cap_input" value="">
                                        </div>
                                        <div class="col-sm-4">  
                                            <span id="location_power_cap"></span>
                                            <input type="hidden" name="site_power_cap_input" id="site_power_cap_input" value="">
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="form-group">
                                        <label class="col-sm-4">Room</label>
                                        <div class="col-sm-4">  
                                            <span id="room_capacity"></span>
                                            <input type="hidden" name="room_cap_input" id="room_cap_input" value="">
                                        </div>
                                        <div class="col-sm-4">  
                                            <span id="room_power_cap"></span>
                                            <input type="hidden" name="room_power_cap_input" id="room_power_cap_input" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>    
                    </form>
                </div>    
            </div>
        </div>
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
        $('.floatdiv').addFloating(
                {
                    targetRight: 10,
                    targetTop: 55,
                    snap: true
                });

        $("#submit").click(function () {
            $.ajax({
                url: 'ajax_save_room_simulation.php',
                type: 'post',
                data: $("form").serialize(),
                dataType: 'JSON',
                success: function (res) {
                    //$("input[type='hidden']").remove();
                    $("#new_rack_ids").html('');
                    Swal(
                        'Good job!',
                        'Simulation added successfully!',
                        'success'
                    )
                }
            });
        });
        $("#commit").click(function () {
            $.ajax({
                url: 'ajax_commit_room_simulation.php',
                type: 'post',
                data: $("form").serialize(),
                dataType: 'JSON',
                success: function (res) {
                    Swal(
                        'Good job!',
                        'Simulation commited successfully!',
                        'success'
                    )
                }
            });
        });
    });

    // CODE FOR REMOVE SIMULATION FROM RACK
    $(document).on("click", '.sim_trash', function (event) {
        var tmp_sim_id = $(this).data('id');
        var tmp_sim_height = $(this).closest("td").data('height');
        //var tmp_sim_height = 1;
        var tmp_sim_power = $(this).closest("td").data('power');
        var tmp_parent_id = $(this).closest("td").attr('id');
        var tmp_parent_sim_id = $(this).closest("td").data('parent');
        
        //var tmp_sim_position = $(this).closest("td").data('position');
        
        const swalWithBootstrapButtons = Swal.mixin({
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false,
        })

        swalWithBootstrapButtons({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'ajax_delete_room_simulation.php',
                    type: 'post',
                    data: {simulation_id: tmp_sim_id},
                    dataType: 'JSON',
                    success: function (res) {
                        if (res.status == 'success') {

                            $("#" + tmp_parent_id).attr('ondrop', 'drop(event, this)');
                            $("#" + tmp_parent_id).attr('ondragover', 'allowDrop(event)');
                            //$("#" + tmp_parent_id).data('position', tmp_sim_position);
                            $("#" + tmp_parent_id).html('');
                            $("#device_" + tmp_parent_sim_id).attr("draggable", "true");
                            
                            var tmp_site_act_height = $('#site_cap_input').val();
                            var tmp_room_act_height = $('#room_cap_input').val();
                            var tmp_site_act_power = $('#site_power_cap_input').val();
                            var tmp_room_act_power = $('#room_power_cap_input').val();


                            var location_new_height = parseInt(tmp_site_act_height) - parseInt(tmp_sim_height);
                            var room_new_height = parseInt(tmp_room_act_height) - parseInt(tmp_sim_height);
                            var location_new_power = parseInt(tmp_site_act_power) - parseInt(tmp_sim_power);
                            var room_new_power = parseInt(tmp_room_act_power) - parseInt(tmp_sim_power);

                            // UPDATE NEW HEIGHT AND POWER INTO LABEL AND HIDDEN
                            $('#site_cap_input').val(location_new_height);
                            $('#room_cap_input').val(room_new_height);

                            $('#location_capacity').html(location_new_height);
                            $('#room_capacity').html(room_new_height);

                            $('#site_power_cap_input').val(location_new_power);
                            $('#room_power_cap_input').val(room_new_power);

                            $('#location_power_cap').html(location_new_power);
                            $('#room_power_cap').html(room_new_power);

                            swalWithBootstrapButtons(
                                    'Deleted!',
                                    'Your simulation has been deleted.',
                                    'success'
                                    )
                        }
                    }
                })
            } else if (
                    // Read more about handling dismissals
                    result.dismiss === Swal.DismissReason.cancel
                    ) {
                swalWithBootstrapButtons(
                        'Cancelled',
                        'Your simulation is safe :)',
                        'error'
                        )
            }
        })
    });

    // CODE FOR REMOVE DEVICE FROM RACK
    $(document).on("click", '.remove_div', function (event) {
        var tmp_reduse_div = $(this).data('id');
        var tmp_reduse_height = $('#' + tmp_reduse_div).data('height');
        //var tmp_reduse_height = 1;
        var tmp_reduse_power = $('#' + tmp_reduse_div).data('power');
        var tmp_reduse_device_id = $('#' + tmp_reduse_div).data('device_id');
        
        Swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.value) {
                $("#device_" + tmp_reduse_device_id).attr("draggable", "true");
                
                $('#' + tmp_reduse_div).html('');
                $('#hidden_' + tmp_reduse_div).remove('');
                $('#hidden_pos_' + tmp_reduse_div).remove('');
                
                // ADD DROPABLE CLASSES AND REMOVE DRAGGABLE
                $("#" + tmp_reduse_div).attr("ondrop", "drop(event, this)");
                $("#" + tmp_reduse_div).attr("ondragover", "allowDrop(event)");
                $("#" + tmp_reduse_div).removeAttr('draggable');
                $("#" + tmp_reduse_div).removeAttr('ondragstart');
                
                var tmp_site_act_height = $('#site_cap_input').val();
                var tmp_room_act_height = $('#room_cap_input').val();
                var tmp_site_act_power = $('#site_power_cap_input').val();
                var tmp_room_act_power = $('#room_power_cap_input').val();

                var location_new_height = parseInt(tmp_site_act_height) - parseInt(tmp_reduse_height);
                var room_new_height = parseInt(tmp_room_act_height) - parseInt(tmp_reduse_height);
                var location_new_power = parseInt(tmp_site_act_power) - parseInt(tmp_reduse_power);
                var room_new_power = parseInt(tmp_room_act_power) - parseInt(tmp_reduse_power);

                // UPDATE NEW HEIGHT AND POWER INTO LABEL AND HIDDEN
                $('#site_cap_input').val(location_new_height);
                $('#room_cap_input').val(room_new_height);

                $('#location_capacity').html(location_new_height);
                $('#room_capacity').html(room_new_height);

                $('#site_power_cap_input').val(location_new_power);
                $('#room_power_cap_input').val(room_new_power);

                $('#location_power_cap').html(location_new_power);
                $('#room_power_cap').html(room_new_power);

                Swal(
                        'Deleted!',
                        'Your simulation has been deleted.',
                        'success'
                        )
            }
        })
    });
    function change_location(location_id) {
        $('#site_cap_input').val('');
        $('#room_cap_input').val('');

        $('#location_capacity').html('');
        $('#room_capacity').html('');
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
        $("#location_capacity").html("");
        $("#site_cap_input").val("");
        $("#room_capacity").html("");
        $("#room_cap_input").val("");
        $("#location_power_cap").html("");
        $("#site_power_cap_input").val("");
        $("#room_power_cap").html("");
        $("#room_power_cap_input").val("");

        var location_id = $("#site").val();
        var room_id = $("#room").val();

        $.ajax({
            url: 'get_rack_capacity.php',
            type: 'post',
            data: {room_id: room_id, site_id: location_id},
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    // ADD HEIGHT AND POWER INTO LABEL AND HIDDEN
                    $("#location_capacity").html(res.res.site);
                    $("#room_capacity").html(res.res.room);
                    $("#site_cap_input").val(res.res.site);
                    $("#room_cap_input").val(res.res.room);

                    $("#location_power_cap").html(res.res.site_power);
                    $("#room_power_cap").html(res.res.room_power);
                    $("#site_power_cap_input").val(res.res.site_power);
                    $("#room_power_cap_input").val(res.res.room_power);
                }
            }
        });
        
        // EXISTING RACK GET AND INTO RACK
        $.ajax({
            url: 'get_simulation_rack.php',
            type: 'post',
            data: {room_id: room_id},
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    $("#rack_str").removeAttr('data-placeholder');
                    $("#rack_str").html(res.res);
                    $(".rack_list").attr("draggable", "true");
                    $.each(res.rack_ids, function (i, item) {
                        $("#device_" + item).attr("draggable", "false");
                    })
                }
            }
        });
    }
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("Text", ev.target.id);
        //console.log(ev.target.id);
    }

    function drop(ev, target) {
        var source_id = $("#" + ev.dataTransfer.getData("text")).closest('td').attr('id');
        var category = ev.dataTransfer.getData("Text");
        var drop_id = target.id;
        
        var data = ev.dataTransfer.getData("text");
        
        ev.target.appendChild(document.getElementById(data));
        //$('#'+drop_id).droppable("disable");
        //console.log(target.id);
        
        var tmp_height = $('#' + category).data('height');
        //var tmp_height = 1;
        var tmp_power = $('#' + category).data('power');
        var tmp_device_name = $('#' + category).data('device');
        var drop_position = $("#" + drop_id).data("position");
        var drop_group = $("#" + drop_id).data("group");
        var is_simulation = $('#' + source_id).data('simulation');
        
        // REMOVE DROPABLE CLASSES AND ADD MAKE DRAGGABLE
        $("#" + drop_id).removeAttr('ondrop');
        $("#" + drop_id).removeAttr('ondragover');
        $("#" + drop_id).attr("draggable", "true");
        $("#" + drop_id).attr("ondragstart", "drag(event)");
        
        if(is_simulation!="" && is_simulation=="Y"){
            $("#" + drop_id).data("simulation", "Y");
        }
        // SET DRAGABLE FALSE ONCE DRAG
        //$('#' + category).attr("draggable", "false");
        //$('#' + drop_id).data('device_id', category.replace("device_", ""));
        
        // ADD NEW HTML INTO THE RACK DIV ON DROP
        if(typeof is_simulation === "undefined"|| is_simulation=="N"){
            
            var custome_div = document.createElement('div');
            custome_div.innerHTML = ' <i data-id="' + drop_id + '" data-height="'+tmp_height+'" data-power="'+tmp_power+'" class="fa fa-times remove_div"></i>';
            /* $('#' + drop_id).html(tmp_device_name + ' <i data-id="' + drop_id + '" class="fa fa-times remove_div"></i>');*/
            $('#' + drop_id).data('height', tmp_height);
            $('#' + drop_id).data('power', tmp_power);
            $('#' + drop_id).data('device', tmp_device_name); 
            document.getElementById(drop_id).appendChild(custome_div);

            $("#new_rack_ids").append(
                "<input type='hidden' id='hidden_" + drop_id + "' name='new_rack_ids[]' value=" + tmp_device_name + " />\n\
                <input type='hidden' id='hidden_pos_" + drop_id + "' name='new_rack_pos[]' value=" + drop_position + " />\n\
                <input type='hidden' id='hidden_group_" + drop_id + "' name='new_rack_group[]' value=" + drop_group + " />"
            );

            //$('#new_rack_ids').val(tmp_device_name);

            var tmp_site_act_height = $('#site_cap_input').val();
            var tmp_room_act_height = $('#room_cap_input').val();
            var tmp_site_act_power = $('#site_power_cap_input').val();
            var tmp_room_act_power = $('#room_power_cap_input').val();

            var location_new_height = parseInt(tmp_site_act_height) + parseInt(tmp_height);
            var room_new_height = parseInt(tmp_room_act_height) + parseInt(tmp_height);
            var location_new_power = parseInt(tmp_site_act_power) + parseInt(tmp_power);
            var room_new_power = parseInt(tmp_room_act_power) + parseInt(tmp_power);

            // CALCULATE NEW HEIGHT AND POWER AND ADD INTO LABEL AND HIDDEN
            $('#site_cap_input').val(location_new_height);
            $('#room_cap_input').val(room_new_height);
            $('#site_power_cap_input').val(location_new_power);
            $('#room_power_cap_input').val(room_new_power);

            $('#location_capacity').html(location_new_height);
            $('#room_capacity').html(room_new_height);
            $('#location_power_cap').html(location_new_power);
            $('#room_power_cap').html(room_new_power);
        }
        
        // CODE FOR MOVE SIMULATION BEFORE SAVE
        if(source_id != "" &&  typeof source_id !== "undefined"){
            
            var tmp_reduse_div = category;
            var tmp_reduse_height = $('#' + tmp_reduse_div).data('height');
            //var tmp_reduse_height = 1;
            var tmp_reduse_power = $('#' + tmp_reduse_div).data('power');
            var tmp_reduse_device_id = $('#' + tmp_reduse_div).data('device_id');
            var device_id = category.replace("device_", "");
            
            // REMOVE DROPABLE CLASSES AND ADD MAKE DRAGGABLE
            
            $("#" + source_id).attr('ondrop', 'drop(event, this)');
            $("#" + source_id).attr('ondragover', 'allowDrop(event)');
            $("#" + source_id).removeAttr("draggable");
            $("#" + source_id).removeAttr("ondragstart");
            
            if(is_simulation!="" && is_simulation=="Y"){
                move_simulation(device_id, drop_position, drop_group);
            }else {
                drag_location(source_id, tmp_reduse_div, tmp_reduse_height, tmp_reduse_power, tmp_reduse_device_id);
            }
        }
    }
    
    function drag_location(from_id, event_id, height, power, device_name) {
        $('#' + from_id).html('');
        $('#hidden_' + from_id).remove('');
        $('#hidden_pos_' + from_id).remove('');
        $('#hidden_group_' + from_id).remove('');

        // ADD DROPABLE CLASSES AND REMOVE DRAGGABLE
        $("#" + from_id).attr("ondrop", "drop(event, this)");
        $("#" + from_id).attr("ondragover", "allowDrop(event)");
        $("#" + from_id).removeAttr('draggable');
        $("#" + from_id).removeAttr('ondragstart');

        var tmp_site_act_height = $('#site_cap_input').val();
        var tmp_room_act_height = $('#room_cap_input').val();
        var tmp_site_act_power = $('#site_power_cap_input').val();
        var tmp_room_act_power = $('#room_power_cap_input').val();

        var location_new_height = parseInt(tmp_site_act_height) - parseInt(height);
        var room_new_height = parseInt(tmp_room_act_height) - parseInt(height);
        var location_new_power = parseInt(tmp_site_act_power) - parseInt(power);
        var room_new_power = parseInt(tmp_room_act_power) - parseInt(power);

        // UPDATE NEW HEIGHT AND POWER INTO LABEL AND HIDDEN
        $('#site_cap_input').val(location_new_height);
        $('#room_cap_input').val(room_new_height);

        $('#location_capacity').html(location_new_height);
        $('#room_capacity').html(room_new_height);

        $('#site_power_cap_input').val(location_new_power);
        $('#room_power_cap_input').val(room_new_power);

        $('#location_power_cap').html(location_new_power);
        $('#room_power_cap').html(room_new_power);
    }
    
    function move_simulation(rack_id, position, group) {
        
        $.ajax({
            url: 'ajax_move_simulation.php',
            type: 'post',
            data: {rack_id: rack_id,row_position: position,group_no: group},
            dataType: 'JSON',
            success: function (res) {
                if (res.status == 'success') {
                    //$("#room").html(res.res);
                }
            }
        });
    }
</script>
