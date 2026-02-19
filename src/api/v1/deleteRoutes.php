<?php

	/*	Even though we're including these files in to an upstream index.php that already declares
		the namespaces, PHP treats it as a difference context, so we have to redeclare in each
		included file.
	
		Framework v3 Specific

	use Psr\Http\Message\ServerRequestInterface as Request;
	use Psr\Http\Message\ResponseInterface as Response;

	*/
/**
  *
  *		API DELETE Methods go here
  *
  *		DELETE Methods are for removing records 
  *
  **/


//
//	URL:	/api/v1/powerport/:deviceid
//	Method:	DELETE
//	Params:	
//		required: DeviceID, PortNumber
//		optional: Label, ConnectedDeviceID, ConnectedPort, Notes
//	Returns:  true/false on update operation
//

// $app->delete( '/powerport/{deviceid}', function( Request $request, Response $response, $args ) use ($person) {
$app->delete( '/powerport/:deviceid', function( $deviceid ) use ($app) {
	$pp=new PowerPorts();
	$pp->DeviceID=$deviceid;
	$vars = getParsedBody();

	foreach($vars as $prop => $val){
		if ( property_exists( $pp, $prop )) {
			$pp->$prop=$val;
		}
	}

	function updatedevice($deviceid){
		$dev=new Device();
		$dev->DeviceID=$deviceid;
		$dev->GetDevice();
		$dev->PowerSupplyCount=$dev->PowerSupplyCount-1;
		$dev->UpdateDevice();
	}

	// If this port isn't the last port then we're gonna shuffle ports to keep the ids in orderish
	$lastport=end($pp->getPorts());
	if($lastport->PortNumber!=$pp->PortNumber){
		foreach($lastport as $prop=>$value){
			if($prop!="PortNumber"){
				$pp->$prop=$value;
			}
		}
		if($pp->updatePort()){
			if($lastport->removePort()){
				updatedevice($pp->DeviceID);
				$r['error']=false;
			}else{
				$r['error']=true;
			}
		}else{
			$r['error']=true;
		}
	}else{ // Last available port, just delete it.
		if($pp->removePort()){
			updatedevice($pp->DeviceID);
			$r['error']=false;
		}else{
			$r['error']=true;
		}
	}

	$r['errorcode']=200;

	echoResponse( $r );
});

//
//	URL:	/api/v1/colorcode/:colorid
//	Method:	DELETE
//	Params:	colorid (passed in URL)
//	Returns:  true/false on update operation
//

$app->delete( '/colorcode/:colorid', function( $colorid ) use($person) {
	if ( ! $person->SiteAdmin ) {
		$r['error'] = true;
		$r['errorcode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$cc=new ColorCoding();
		$cc->ColorID=$colorid;
		
		if(!$cc->DeleteCode()){
			$r['error']=true;
			$r['errorcode']=404;
			$r['message']=__("Failed to delete color with ColorID")." $cc->ColorID";
		}else{
			$r['error']=false;
			$r['errorcode']=200;
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/device/:deviceid
//	Method:	DELETE
//	Params:	deviceid (passed in URL)
//	Returns:  true/false on update operation
//

$app->delete( '/device/:deviceid', function( $deviceid ) {
	$dev=new Device();
	$dev->DeviceID=$args['deviceid'];
	
	if(!$dev->GetDevice()){
		$r['error']=true;
		$r['errorcode']=404;
		$r['message']=__("Device doesn't exist");
	}else{
		if($dev->Rights!="Write"){
			$r['error']=true;
			$r['errorcode']=403;
			$r['message']=__("Unauthorized");
		}else{
			if(!$dev->DeleteDevice()){
				$r['error']=true;
				$r['errorcode']=404;
				$r['message']=__("An unknown error has occured");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
			}
		}
	}
	echoResponse( $r );
});

//
//	URL:	/api/v1/devicestatus/:statusid
//	Method:	DELETE
//	Params: 
//		Required: StatusID
//	Returns: true/false on update operations 
//

$app->delete( '/devicestatus/:statusid', function($statusid) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['error'] = true;
		$r['errorcode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$ds=new DeviceStatus($statusid);

		if(!$ds->removeStatus()){
			$r['error']=true;
			$r['errorcode']=400;
			$r['message']=__("Error removing status, check to make sure it isn't in use on any devices.");
		}else{
			$r['error']=false;
			$r['errorcode']=200;
			$r['message']=__("Status removed successfully.");
			$r['devicestatus'][$ds->StatusID]=$ds;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/delete_location/:locationid
//	Method:	DELETE
//	Params:	locationid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_location/:locationid', function( $locationid ) {
	$location = new Location();
	$location->PortID=$locationid;
        
	if(!$location->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$location->DeleteObject()){
                    $r['status']="Fail";
                    $r['statuscode']=404;
                    $r['message']=__("An unknown error has occured");
            } else {
                    $r['status']="Success";
                    $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_room/:roomid
//	Method:	DELETE
//	Params:	roomid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_room/:roomid', function( $roomid ) {
	$room = new Room();
	$room->PortID=$roomid;
        
	if(!$room->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$room->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_rack/:rackid
//	Method:	DELETE
//	Params:	rackid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_rack/:rackid', function( $rackid ) {
	$rack = new Rack();
	$rack->PortID=$rackid;
        
	if(!$rack->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$rack->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_device/:deviceid
//	Method:	DELETE
//	Params:	deviceid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_device/:deviceid', function( $deviceid ) {
	$device = new Device_new();
	$device->PortID=$deviceid;
        
	$filter = array();
        $filter['device'] = $device->PortID;
        
	if(!$device->GetDeviceOne($filter)){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$device->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_idrac/:idracid
//	Method:	DELETE
//	Params:	idracid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_idrac/:idracid', function( $idracid ) {
	$idrac_setting = new IdracSetting();
	$idrac_setting->PortID=$idracid;
        
	if(!$idrac_setting->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$idrac_setting->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_asset/:assetid
//	Method:	DELETE
//	Params:	assetid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_asset/:assetid', function( $assetid ) {
	$assets = new Asset();
	$assets->PortID=$assetid;
        
	if(!$assets->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$assets->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_asset/:assetid
//	Method:	DELETE
//	Params:	assetid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_asset_status/:assetstatusid', function( $assetstatusid ) {
	$assets = new Asset();
	$assets->PortID=$assetstatusid;
        
	if(!$assets->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$assets->DeleteStatus()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_asset_supplier/:assetsupplierid
//	Method:	DELETE
//	Params:	assetid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_asset_supplier/:assetsupplierid', function( $assetsupplierid ) {
	$assets = new AssetSupplier();
	$assets->PortID=$assetsupplierid;
        
	if(!$assets->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$assets->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_asset_category/:assetcategoryid
//	Method:	DELETE
//	Params:	assetid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_asset_category/:assetcategoryid', function( $assetcategoryid ) {
	$assets = new AssetCategory();
	$assets->PortID=$assetcategoryid;
        
	if(!$assets->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$assets->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_asset_category/:assetcategoryid
//	Method:	DELETE
//	Params:	assetid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_asset_model/:assetmodelid', function( $assetmodelid ) {
	$assets_model = new Model();
	$assets_model->PortID=$assetmodelid;
        
	if(!$assets_model->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$assets_model->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_interface/:interfaceid
//	Method:	DELETE
//	Params:	interfaceid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_interface/:interfaceid', function( $interfaceid ) {
	$interface = new InterfaceConn();
	$interface->PortID=$interfaceid;
        
	if(!$interface->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$interface->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_console/:consoleid
//	Method:	DELETE
//	Params:	consoleid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_console/:consoleid', function( $consoleid ) {
	$console = new ConsoleConn();
	$console->PortID=$consoleid;
        
	if(!$console->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$console->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_power/:powerid
//	Method:	DELETE
//	Params:	powerid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_power/:powerid', function( $powerid ) {
	$power = new PowerConn();
	$power->PortID=$powerid;
        
	if(!$power->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$power->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_cluster/:clusterid
//	Method:	DELETE
//	Params:	clusterid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_cluster/:clusterid', function( $clusterid ) {
	$cluster = new Cluster();
	$cluster->PortID=$clusterid;
        
	if(!$cluster->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$cluster->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_cluster_type/:clustertypeid
//	Method:	DELETE
//	Params:	clustertypeid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_cluster_type/:clustertypeid', function( $clustertypeid ) {
	$cluster = new ClusterType();
	$cluster->PortID=$clustertypeid;
        
	if(!$cluster->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$cluster->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_cluster_type/:clustertypeid
//	Method:	DELETE
//	Params:	clustertypeid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_cluster_group/:clustergroupid', function( $clustergroupid ) {
	$cluster = new ClusterGroup();
	$cluster->PortID=$clustergroupid;
        
	if(!$cluster->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$cluster->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_cluster_type/:clustertypeid
//	Method:	DELETE
//	Params:	clustertypeid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_virtual_machine/:virtualmachineid', function( $virtualmachineid ) {
	$virtual_machine = new Virtual_machine();
	$virtual_machine->PortID=$virtualmachineid;
        
	if(!$virtual_machine->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$virtual_machine->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_aggregate/:aggregateid
//	Method:	DELETE
//	Params:	aggregateid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_aggregate/:aggregateid', function( $aggregateid ) {
	$ipam_aggregate = new IpamAggreget();
	$ipam_aggregate->PortID=$aggregateid;
        
	if(!$ipam_aggregate->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipam_aggregate->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_prefix/:prefixid
//	Method:	DELETE
//	Params:	clustertypeid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_prefix/:prefixid', function( $prefixid ) {
	$ipam_prefix = new IpamPrefix();
	$ipam_prefix->PortID=$prefixid;
        
	if(!$ipam_prefix->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipam_prefix->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_prefix_role/:prefixroleid
//	Method:	DELETE
//	Params:	clustertypeid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_prefix_role/:prefixroleid', function( $prefixroleid ) {
	$ipam_prefix = new IpamPrefixRole();
	$ipam_prefix->PortID=$prefixroleid;
        
	if(!$ipam_prefix->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipam_prefix->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_ipaddress/:ipaddressid
//	Method:	DELETE
//	Params:	ipaddressid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_ipaddress/:ipaddressid', function( $ipaddressid ) {
	$ipaddress = new IpamIPaddress();
	$ipaddress->PortID=$ipaddressid;
        
	if(!$ipaddress->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipaddress->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_vlan/:vlanid
//	Method:	DELETE
//	Params:	vlanid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_vlan/:vlanid', function( $vlanid ) {
	$ipamVLAN = new IpamVLAN();
	$ipamVLAN->PortID=$vlanid;
        
	if(!$ipamVLAN->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipamVLAN->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_vlan_group/:vlangroupid
//	Method:	DELETE
//	Params:	vlanid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_vlan_group/:vlangroupid', function( $vlangroupid ) {
	$ipamVLAN = new IpamVLANGroup();
	$ipamVLAN->PortID=$vlangroupid;
        
	if(!$ipamVLAN->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipamVLAN->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_rir/:ririd
//	Method:	DELETE
//	Params:	vlanid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_rir/:ririd', function( $ririd ) {
	$ipamRIR = new IpamRIR();
	$ipamRIR->PortID=$ririd;
        
	if(!$ipamRIR->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipamRIR->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_vrf/:vrfid
//	Method:	DELETE
//	Params:	vrfid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_vrf/:vrfid', function( $vrfid ) {
	$ipamVRF = new IpamVRF();
	$ipamVRF->PortID=$vrfid;
        
	if(!$ipamVRF->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$ipamVRF->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});

//	URL:	/api/v1/delete_service/:serviceid
//	Method:	DELETE
//	Params:	serviceid (passed in URL)
//	Returns:  true/false on update operation

$app->delete( '/delete_service/:serviceid', function( $serviceid ) {
	$service = new Service();
	$service->PortID=$serviceid;
        
	if(!$service->GetOrderByID()){
            $r['status']="Fail";
            $r['statuscode'] = 404;
            $r['message']=__("Record doesn't exist");
	} else { 
            if(!$service->DeleteObject()){
                $r['status']="Fail";
                $r['statuscode']=404;
                $r['message']=__("An unknown error has occured");
            } else {
                $r['status']="Success";
                $r['statuscode']=200;
            }	
	}
	echoResponse( $r );
});
?>
