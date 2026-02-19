<?php
$header = (!isset($header)) ? $config->ParameterArray["OrgName"] : $header;
$subheader = (!isset($subheader)) ? "" : $subheader;
$version = $config->ParameterArray["Version"];
?>

<!-- Ganti Header dan menambahkan condition-condition untuk rights -->
<!-- Diubah Oleh Firdauz Fanani 23 April 2018 -->

<style>
    .navbar-template {
        padding: 40px 15px;
    }
    @media (min-width: 767px) {
        .navbar-nav .dropdown-menu .caret {
            transform: rotate(-90deg);
        }
    }
    h1, h2, h3 {
        font-family: "Helvetica Neue",Helvetica,Arial,sans-serif !important;
        font-size: 25px !important;
        color: black !important;
    }
    .makecenter {
        margin-top: 2% !important;
    }
    
</style>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/navbar.css" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
<link href="css/demo.css" rel="stylesheet" type="text/css" />
<link href="css/search.css" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow" rel="stylesheet"> 

<style type="text/css">
    #topNav ul { max-height:600px; overflow-y:auto; }
</style>

<!-- HEADER CODE START -->
<header class="navbar" id="header-navbar">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index_2.php">
                    <img src="images/dcim_logo.png" class="image-responsive" style="margin:-13px 0px 0px 8px;width: 170px;height: 47px;">
                </a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">

                <ul class="nav navbar-nav">
                    <li class=""><a href="index.php"><i class="fas fa-home"></i> Home </b></a></li>

                    <li class="dropdown"><?php
                        if ($_SERVER['PHP_SELF'] == "/container_stats.php") {
                            $NamaTab = "Data Center";
                        } elseif ($_SERVER['PHP_SELF'] == "/dc_stats.php") {
                            $NamaTab = "Zone";
                        } elseif ($_SERVER['PHP_SELF'] == "/zone_stats.php") {
                            $NamaTab = "Cabinet Row";
                        } elseif ($_SERVER['PHP_SELF'] == "/rowview.php") {
                            $NamaTab = "Cabinet";
                        } else {
                            $NamaTab = "Container";
                        }
                        ?>
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $NamaTab; ?> <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <?php
                            //connection
                            if ($_SERVER['HTTP_HOST'] == "localhost") {
                                $conn = new mysqli('localhost', 'root', '', 'yanto_dcim');
                            } else {
                                $conn = new mysqli('localhost', 'root', 'Admin1@#4', 'yanto_dcim');
                            }
                            ?>

                            <?php
                            if ($_SERVER['PHP_SELF'] == "/container_stats.php") {

                                $NamaTab = "Data Center";
                                $sql = "SELECT * FROM fac_datacenter WHERE ContainerID=$c->ContainerID";
                                $query = $conn->query($sql);

                                while ($row = $query->fetch_assoc()) {
                                    echo "
                                                    <li class='dropdown-toggle' data-toggle='dropdown'><a href='dc_stats.php?dc=" . $row['DataCenterID'] . "'>" . $row['Name'] . "</a></li>";
                                }
                            } elseif ($_SERVER['PHP_SELF'] == "/dc_stats.php") {
                                $sql = "SELECT * FROM fac_zone WHERE DataCenterID=$dc->DataCenterID";
                                $query = $conn->query($sql);

                                while ($row = $query->fetch_assoc()) {
                                    echo "
                                                    <li class='dropdown-toggle' data-toggle='dropdown'><a href='zone_stats.php?zone=" . $row['ZoneID'] . "'>" . $row['Description'] . "</a></li>
                                                ";
                                }

                                echo "<li><a href='storageroom.php?dc=" . $dc->DataCenterID . "'>Data Center Storage Room</a></li>";
                            } elseif ($_SERVER['PHP_SELF'] == "/zone_stats.php") {
                                $sql = "SELECT * FROM fac_cabrow WHERE ZoneID=$zone->ZoneID";
                                $query = $conn->query($sql);

                                while ($row = $query->fetch_assoc()) {
                                    echo "
                                                    <li class='dropdown-toggle' data-toggle='dropdown'><a href='rowview.php?row=" . $row['CabRowID'] . "'>" . $row['Name'] . "</a></li>
                                                ";
                                }
                            } elseif ($_SERVER['PHP_SELF'] == "/rowview.php") {
                                $sql = "SELECT * FROM fac_cabinet WHERE CabRowID=$cabrow->CabRowID";
                                $query = $conn->query($sql);

                                while ($row = $query->fetch_assoc()) {
                                    echo "
                                                    <li class='dropdown-toggle' data-toggle='dropdown'><a href='cabnavigator.php?cabinetid=" . $row['CabinetID'] . "'>" . $row['Location'] . "</a></li>
                                                ";
                                }
                            } else {
                                $sql = "SELECT * FROM fac_container";
                                $query = $conn->query($sql);

                                if (count($query) > 0) {
                                    while ($row = $query->fetch_assoc()) {
                                        echo "
                                                        <li class='dropdown-toggle' data-toggle='dropdown'><a href='container_stats.php?container=" . $row['ContainerID'] . "'>" . $row['Name'] . "</a></li>
                                                    ";
                                    }
                                }
                                echo "<li><a href='storageroom.php'>General Storage Room</a></li>";
                            }
                            ?>
                        </ul>
                    </li>
                    
                    <!-- Data Center Facility Management Menu Code Start -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Data Center Facility Management <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Template Management <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <?php
                                    if ($person->WriteAccess) {
                                        echo '
                                    	<li><a href="device_templates.php">Device Template</a></li>
                                        <li><a href="image_management.php">Device Image Management</a></li>';
                                    }
                                    if ($person->SiteAdmin) {
                                        echo '
                                    	<li><a href="device_manufacturers.php">Manufacture</a></li>
                                        <li><a href="repository_sync_ui.php">Repository Sync</a></li>';
                                    }
                                    ?>
                                </ul>
                            </li>

                            <?php
                            if ($person->SiteAdmin) {
                                echo '
                                <li>
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Power Management <b class="caret"></b></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="power_panel.php">Power Panels</a></li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Path Connections <b class="caret"></b></a>                                  <ul class="dropdown-menu">
                                        <li><a href="paths.php">View Path Connection</a></li>
                                        <li><a href="pathmaker.php">Make Path Connection</a></li>
                                    </ul>
                                </li>';
                            }
                            ?>
                            <li><a href="project_mgr.php">Project Catalog</a></li>
                            <?php
                            if ($person->ContactAdmin) {
                                echo '
                            <li>
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Issue Escalation <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="timeperiods.php">Time Period</a></li>
                                <li><a href="escalations.php">Escalation Rules</a></li>
                            </ul>
                            </li>';
                            }
                            ?>

                            <?php
                            if ($person->SiteAdmin) {
                                echo '
                        <li>
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Material Management <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="supplybin.php">Manage Supply Bins</a></li>
                                <li><a href="supplies.php">Manage Supplies</a></li>
                                <li><a href="disposition.php">Manage Disposal Methods</a></li>
                            </ul>
                        </li>';
                            }
                            ?>

                            <?php
                            if ($person->BulkOperations) {
                                echo '
                        <li>
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Import Management <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="bulk_container.php">Import Container/Data Center/Zone/Row</a></li>
                                <li><a href="bulk_users.php">Import User Accounts</a></li>
                                <li><a href="bulk_departments.php">Import Departments/Customers</a></li>
                                <li><a href="bulk_templates.php">Import Device Templates</a></li>
                                <li><a href="bulk_cabinet.php">Import Cabinets</a></li>
                                <li><a href="bulk_importer.php">Import Devices</a></li>
                                <li><a href="bulk_network.php">Import Network Connections</a></li>
                                <li><a href="bulk_power.php">Import Power Connections</a></li>
                                <li><a href="bulk_moves.php">Process Bulk Moves</a></li>
                            </ul>
                        </li>';
                            }
                            ?>

                            <?php
                            if ($config->ParameterArray["RackRequests"] == "enabled" && $person->RackRequest) {
                                echo '<li><a href="rackrequest.php">Rack Request Form</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <!-- Data Center Facility Management Menu Code End -->
                    
                    <?php /* <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Manage <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <?php
                            if ($person->ContactAdmin) {
                                echo '
                                    <li>
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"> Administration <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="usermgr.php">User</a></li>
                                            <li><a href="departments.php">Department</a></li>
                                        </ul>
                                    </li>';
                            }
                            ?>

                            <?php
                            if ($person->SiteAdmin) {
                                echo '<li><a href="configuration.php">Configuration</a></li>';
                            }
                            ?>
                        </ul>
                    </li> */ ?>

                    <!-- IT HARDWARE MENU -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">IT Hardware Platforms<b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Configuration <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Patch Cables <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="port_connectors.php">Connectors</a></li>
                                            <li><a href="cable_type.php">Cable Types</a></li>
                                            <li><a href="connector_comp.php">Connector Compatibility</a></li>
                                            <li><a href="oicomp.php">Outer Interface Compatibility</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">IP Spaces & Allocation <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="ipv4space.php">IPv4 Space</a></li>
                                            <li><a href="ipv6space.php">IPv6 Space</a></li>
                                            <li><a href="ipv4list.php">IPv4 List</a></li>
                                            <li><a href="ipv6list.php">IPv6 List</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="objects.php">Object List</a></li>
                                    <li><a href="objects_comp.php">Object Container Compaitibility</a></li>
                                    <li><a href="port_comp.php">Port Compatibility</a></li>    
                                    <li><a href="port_status.php">Enabled Port Types</a></li>
                                    <li><a href="attributes.php">Attributes</a></li>
                                    <li>
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dictionary Management <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="chapter.php">Chapter</a></li>
                                            <li><a href="dictionary.php">Dictionary</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="port_ointer.php">Port Outer Interface</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">IPAM <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="ipam_ipaddress.php">IP Addresses</a></li>
                                    <li><a href="ipam_vrf.php">VRF</a></li>
                                </ul>
                            </li>
                            <?php
                            if ($person->ContactAdmin) {
                                echo '
                                    <li>
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"> Administration <b class="caret"></b></a>
                                        <ul class="dropdown-menu">
                                            <li><a href="usermgr.php">User</a></li>
                                            <li><a href="departments.php">Department</a></li>
                                        </ul>
                                    </li>';
                            }
                            ?>

                            <?php
                            if ($person->SiteAdmin) {
                                echo '<li><a href="configuration.php">General Configuration</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <!-- IT HARDWARE MENU CODE END -->
                    
                    <!-- DCIM MENU -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">DCIM <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Infrastructure Management <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <?php
                                    if ($person->SiteAdmin) {
                                        echo '
                                    	<li><a href="container.php">Container</a></li>
                                    	<li><a href="datacenter.php">Data Centers</a></li>
                                    	<li><a href="zone.php">Zones</a></li>
                                    	<li><a href="cabrow.php">Rows of Cabinet</a></li>';
                                    }
                                    ?>

                                    <?php
                                    if ($person->WriteAccess) {
                                        echo '
                                    	<li><a href="cabinets.php">Cabinets</a></li>
                                        <li><a href="ac.php">PAC Data Center</a></li>
                                        <li><a href="facpowatt.php">Facility Power Attributes</a></li>';
                                    }
                                    ?>  

                                    <?php
                                    if ($person->SiteAdmin) {
                                        echo '
                                    	<li><a href="image_management.php#drawings">Facilities Image Management</a></li>';
                                    }
                                    ?>                                          

                                </ul>
                            </li>
                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Asset Reports <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="search_export.php">Search/Export by Data Center</a></li>
                                    <li><a href="search_export_storage_room.php">Storage Room Search/Export by Data Center</a></li>
                                    <li><a href="report_xml_CFD.php">Export Data Center for CFD (XML)</a></li>
                                    <li><a href="report_contact.php">Asset Report by Owner</a></li>
                                    <li><a href="report_asset.php">Data Center Asset Report</a></li>
                                    <li><a href="report_asset_Excel.php">Data Center Asset Report [Excel]</a></li>
                                    <li><a href="report_cost.php">Data Center Asset Costing Report</a></li>
                                    <li><a href="report_projects.php">Project Asset Report</a></li>
                                    <li><a href="report_aging.php">Asset Aging Report</a></li>
                                    <li><a href="report_warranty.php">Warranty Expiration Report</a></li>
                                    <li><a href="report_vm_by_department.php">Virtual Machines by Department</a></li>
                                    <li><a href="report_network_map.php">Network Map</a></li>
                                    <li><a href="report_vendor_model.php">Vendor/Model Report</a></li>
                                </ul>
                            </li>

                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"> Operational Reports <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="report_exception.php">Data Exceptions Report</a></li>
                                    <li><a href="report_diverse_power_exceptions.php">Diverse Power Exceptions Report</a></li>
                                    <li><a href="report_outage_simulator.php">Simulated Power Outage Report</a></li>
                                    <li><a href="report_project_outage_simulator.php">Project Power Outage Report</a></li>
                                    <li><a href="report_power_distribution.php">Power Distribution by Data Center</a></li>
                                    <li><a href="report_power_utilization.php">Server Tier Classification Report</a></li>
                                    <li><a href="report_panel_schedule.php">Power Panel Schedule Report</a></li>
                                    <li><a href="report_cabinets.php">Cabinet List</a></li>
                                    <li><a href="report_pac.php">PAC List</a></li>
                                </ul>
                            </li>

                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"> Auditing Reports <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="report_audit.php">Cabinet Audit Logs</a></li>
                                    <li><a href="report_audit_frequency.php">Cabinet Audit Frequency</a></li>
                                    <li><a href="report_surplus.php">Surplus/Salvage Audit Report</a></li>
                                    <li><a href="report_supply_status.php">Supplies Status Report</a></li>
                                    <li><a href="report_logging.php">Actions Log</a></li>
                                </ul>
                            </li>

                            <li><a href="report_department.php">Contact Reports</a></li> 
                        </ul>
                    </li>
                    <!-- DCIM MENU CODE END -->
                    
                </ul>
            </div>
        </div>
    </nav>
</header>
<script src="scripts/navbar.js"></script>
<!-- HEADER CODE END -->

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<!-- Include all compiled plugins (below), or include individual files as needed -->
<!--         <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> -->

<!-- Latest compiled and minified CSS -->
<!--         <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"></link> -->

<!-- Optional theme -->

<!-- Latest compiled and minified JavaScript -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script> -->

<script type="text/javascript">
    function addlookup(inputobj, lookuptype) {
// clear any existing autocompletes
        if (inputobj.hasClass('ui-autocomplete-input')) {
            inputobj.autocomplete('destroy');
        }
// clear out previous search arrows
        inputobj.next('.text-arrow').remove();
// Position the arrow
        var inputpos = inputobj.position();
        var arrow = $('<div />').addClass('text-arrow');
        arrow.click(function () {
            inputobj.autocomplete("search", "");
        });
// add the autocomplete
        inputobj.autocomplete({
            minLength: 0,
            delay: 600,
            autoFocus: true,
            source: function (req, add) {
                $.getJSON('scripts/ajax_search.php?' + lookuptype, {q: req.term}, function (data) {
                    var suggestions = [];
                    $.each(data, function (i, val) {
                        suggestions.push(val);
                    });
                    ey
                    add(suggestions);
                });
            },
            open: function () {
                $(this).autocomplete("widget").css({'width': inputobj.width() + 6 + 'px'});
            }
        }).next().after(arrow);
        arrow.css({'top': inputpos.top + 'px', 'left': inputpos.left + inputobj.width() - (arrow.width() / 2)});
    }
    $('#advsrch, #searchadv ~ .ui-icon.ui-icon-close').click(function () {
        var here = $(this).position();
        $('#searchadv, #searchname').val('');
        $('#searchadv').parents('form').height(here.top).toggle('slide', 200).removeClass('hide');
        if ($('#searchadv').hasClass('ui-autocomplete-input')) {
            $('#searchadv').autocomplete('destroy');
        }
        if ($(this).text() == '<?php echo __("Advanced"); ?>') {
            $(this).text('<?php echo __("Basic"); ?>');
            $('#searchadv ~ select[name="key"]').trigger('change');
        } else {
            $(this).text('<?php echo __("Advanced"); ?>');
        }
    });
</script>
<script type="text/javascript" src="scripts/mktree.js"></script> 
<script type="text/javascript" src="scripts/konami.js"></script> 

<?php
/*
  function buildmenu($menu) {
  $level = '';
  foreach ($menu as $key => $item) {
  $level .= "<li>";
  if (!is_array($item)) {
  $level .= "$item";
  } else {
  $level .= "<a>$key</a><ul>";
  $level .= buildmenu($item);
  $level .= "</ul>";
  }
  $level .= "</li>";
  }
  return $level;
  }

  $menu = buildmenu(array_merge_recursive($rmenu, $rrmenu, $camenu, $wamenu, $samenu, $lmenu));

  print "
  <div style='margin-left:10px;'>
  <a href=\"index.php\">" . __("Home") . "</a>\n";

  $lang = GetValidTranslations();
  //strip any encoding info and keep just the country lang pair
  $locale = explode(".", $locale);
  $locale = $locale[0];
  echo '  <div class="langselect hide">
  <label for="language">Language</label>
  <select name="language" id="language" current="' . $locale . '">';
  foreach ($lang as $cc => $translatedname) {
  // This is for later. For now just display list
  //$selected=""; //
  if ($locale == $cc) {
  $selected = " selected";
  } else {
  $selected = "";
  }
  print "\t\t\t<option value=\"$cc\"$selected>$translatedname</option>";
  }
  echo '      </select>
  </div>

  <div id="nav_placeholder"></div>'; */
// Moved the navigation menu to an ajax load item   
?>
</div>
<script type="text/javascript">

    $("#sidebar .nav a").each(function () {
        var loc = window.location;
        if ($(this).attr("href") == "<?php echo basename($_SERVER['SCRIPT_NAME']); ?>" || $(this).attr("href") == loc.href.substr(loc.href.indexOf(loc.host) + loc.host.length + 1)) {
            $(this).addClass("active");
            $(this).parentsUntil("#ui-id-1", "li").children('a:first-child').addClass("active");
        }
    });
    $("#sidebar .nav").menu();

    $('#searchname').width($('#sidebar').innerWidth() - $('#searchname ~ button').outerWidth());
    addlookup($('#searchname'), 'name');
    $('#searchadv ~ select[name="key"]').change(function () {
        addlookup($('#searchadv'), $(this).val())
    }).outerHeight($('#searchadv').outerHeight()).outerWidth(157);

// Really long cabinet / zone / dc combinations are making the screen jump around.
// If they make this thing so big it's unusable, fuck em.
    $('#sidebar > hr ~ div').css({'width': $('#sidebar > hr ~ ul').width() + 'px', 'overflow': 'hidden'});

    function resize() {
        // Reset widths to make shrinking screens work better
        $('#header,div.main,div.page').css('width', 'auto');
        // This function will run each 500ms for 2.5s to account for slow loading content
        var count = 0;
        subresize();
        var longload = setInterval(function () {
            subresize();
            if (count > 4) {
                clearInterval(longload);
                window.resized = true;
            }
            ++count;
        }, 500);

        function subresize() {
            // page width is calcuated different between ie, chrome, and ff
            $('#header').width(Math.floor($(window).outerWidth() - (16 * 3))); //16px = 1em per side padding
            var widesttab = 0;
            // make all the tabs on the config page the same width
            $('#configtabs > ul ~ div').each(function () {
                widesttab = ($(this).width() > widesttab) ? $(this).width() : widesttab;
            });
            $('#configtabs > ul ~ div').each(function () {
                $(this).width(widesttab);
            });

            if (typeof getCookie == 'function' && getCookie("layout") == "Landscape") {
                // edge case where a ridiculously long device type can expand the field selector out too far
                var rdivwidth = $('div.right').outerWidth();
                $('div.right fieldset').each(function () {
                    rdivwidth = ($(this).outerWidth() > rdivwidth) ? $(this).outerWidth() : rdivwidth;
                });
                // offset for being centered
                rdivwidth = (rdivwidth > 495) ? (rdivwidth - 495) + rdivwidth : rdivwidth;
            } else {
                rdivwidth = 0;
            }

            var pnw = $('#pandn').outerWidth(), hw = $('#header').outerWidth(), maindiv = $('div.main').outerWidth(),
                    sbw = $('#sidebar').outerWidth(), width, mw = $('div.left').outerWidth() + rdivwidth + 20,
                    main, cw = $('.main > .center').outerWidth();
            widesttab += 58;

            // find widths
            width = (cw > mw) ? cw : mw;
            main = (pnw > width) ? pnw : width; // Find the largest width of possible content in maindiv
            main += 12; // add in padding and borders
            width = ((main + sbw) > hw) ? main + sbw : hw; // find the widest point of the page

            // The math just isn't adding up across browsers and FUCK IE
            if ((main + sbw) < width) { // page is larger than content expand main to fit
                $('#header').outerWidth(width);
                $('div.main').outerWidth(width - sbw - 4);
                $('div.page').outerWidth(width);
            } else { // page is smaller than content expand the page to fit
                $('div.main').width(width - sbw - 12);
                $('#header').width(width + 4);
                $('div.page').width(width + 6);
            }

            // If the function MoveButtons is defined run it
            if (typeof movebuttons == 'function') {
                movebuttons();
            }
        }
    }
    $(document).ready(function () {
        resize();
        // redraw the screen if the window size changes for some reason
        $(window).resize(function () {
            if (this.resizeTO) {
                clearTimeout(this.resizeTO);
            }
            this.resizeTO = setTimeout(function () {
                resize();
            }, 500);
        });
        $('#header').append($('.langselect'));
        $(".langselect").css({"right": "3px", "z-index": "99", "position": "absolute"}).removeClass('hide').appendTo("#header");
        $(".langselect").css({"bottom": $(".langselect").height() + "px"});
        $("#language").change(function () {
            $.ajax({
                type: 'POST',
                url: 'scripts/ajax_language.php',
                data: 'sl=' + $("#language").val(),
                success: function () {
                    // new cookie was set. reload the page for the translation.
                    location.reload();
                }
            });
        });
<?php
// No navigation menu if you're not logged in, yet
if (!strpos($_SERVER['SCRIPT_NAME'], "login")) {
    ?>
            $.get('scripts/ajax_navmenu.php').done(function (data) {
                $('#nav_placeholder').replaceWith(data);
                if (("#").readyState === "complete" && $('#datacenters .bullet').length == 0) {
                    window.convertTrees();
                }
            });
    <?php
}
?>
    });

</script>

<script type="text/javascript">
    var s = $('.searchname'),
            f = $('#formsearch'),
            a = $('.after'),
            m = $('h4');

    s.focus(function () {
        if (f.hasClass('open'))
            return;
        f.addClass('in');
        setTimeout(function () {
            f.addClass('open');
            f.removeClass('in');
        }, 1300);
    });

    a.on('click', function (e) {
        e.preventDefault();
        if (!f.hasClass('open'))
            return;
        s.val('');
        f.addClass('close');
        f.removeClass('open');
        setTimeout(function () {
            f.removeClass('close');
        }, 1300);
    })

</script>
