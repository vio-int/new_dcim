<?php
	require_once("../db.inc.php");
	require_once("../facilities.inc.php");
/*
 * All tooltips will come through this file from here out.
 *
 * Submit the id of the object you want a tooltip for as tooltip.
 *
 * $_POST['tooltip'] = id for object of tooltip - REQUIRED
 * $_POST['cab'] = Required for cabinet tooltips
 * $_POST['cdu'] = Required for cdu tooltips
 * $_POST['dev'] = Required for device tooltips
 */

// Use the global configuration
global $config;
global $dbh;

// We're gonna use this as an intval wherever anyhow so just get it done.
$object=(isset($_POST['tooltip']))?intval($_POST['tooltip']):0;

// Default tooltip 
$tooltip=__("Error");

// Init Objects
$cab=new Cabinet();
$dev=new Device();
$dep=new Department();
$rack=new Rack();

if($config->ParameterArray["mUnits"]=="english"){
	$weightunit="lbs";
	$tempunit="F";
}else{
	$weightunit="Kg";
	$tempunit="C";
}

// If the object id isn't set then don't bother with anything else.
if($object>0){
	// Cabinet
	if(isset($_POST['tooltip']) && $_POST['tooltip']!=''){
            
                $filter = array();
                if($_POST['tooltip']!=""){
                    $filter['rack'] = $_POST['tooltip'];
                }
		$rack_data = $rack->GetRackOne($filter);
                $rack_res = json_decode(json_encode($rack_data), true);
                $scolor = "";
                
		if($rack_res){
                        $tooltip="<span>".$rack_res[0]['Name']."</span><ul>\n";
			$tooltip.="<li>".__("Rack").": ".$rack_res[0]['Name']."</li>\n";
			$tooltip.="<li class=\"$scolor\">".__("Space").": ".$rack_res[0]['Width']."</li>\n";
			$tooltip.="<li class=\"$wcolor\">".__("Weight").": ".$rack_res[0]['Max_weight']."</li>\n";
			$tooltip.="<li class=\"$pcolor\">".__("Height").": ".$rack_res[0]['Height']."</li>\n";
			$tooltip.="<li class=\"$rpcolor\">".__("Position").": ".$rack_res[0]['Position']."</li>\n";
			$tooltip.="<li class=\"$tcolor\">".__("Max kw").": ".$rack_res[0]['Max_kw']."</li>\n";
		} else {
			$tooltip=__("Quit that! You don't have rights to view this.");
		}
	}
}

$tooltip="<div>$tooltip</div>";
print $tooltip;
exit;

?>
