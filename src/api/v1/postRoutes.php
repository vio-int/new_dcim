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
  *		API POST Methods go here
  *
  *		POST Methods are for updating existing records
  *
  **/

//
//	URL:	/api/v1/people
//	Method: POST
//	Params: userid (required)
//			lastname, firstname, phone1, phone2, phone3, email, adminowndevices, 
//			readaccess, writeaccess, deleteaccess, contactadmin, rackrequest, 
//			rackadmin, siteadmin
//	Returns: record as modified
//

$app->post('/people/:personid', function($personid) use ($person) {
	if(!$person->ContactAdmin){
		$r['error']=true;
		$r['errorcode']=401;
		$r['message']=__("Access Denied");
	} else {
		$r = array();
		$p=new People();
		$p->PersonID=$personid;
		if(!$p->GetPerson()){
			$r['error']=true;
			$r['errorcode']=404;
			$r['message']=__("UserID=" . $p->PersonID . " not found in database.");
		} else {	
			// Slim Framework will simply return null for any variables that were not passed, so this is safe to call without blowing up the script
			$vars = getParsedBody();
			foreach($p as $prop => $val){
				if ( isset( $vars[$prop] ) ){
					$p->$prop=$vars[$prop];
				}
			}
			$p->Disabled=false;
			
			if(!$p->UpdatePerson()){
				$r['error']=true;
				$r['errorcode']=403;
				$r['message']=__("Unable to update People resource with the given parameters.");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
				$r['message']=sprintf(__('People resource for UserID=%s updated successfully.'),$p->UserID);
				$r['people']=$p;
			}
		}
	}

	// Possible to-do list for someone to figure out...  why the $app->view scope isn't included
	// when you have the use($person) clause - also doesn't work if you make it use ($app, $person)
	echoResponse( $r );
});

//
//	URL:	/api/v1/people
//	Method: POST
//	Params:
//		Required: peopleid, newpeopleid
//	Returns: true / false on the updates being successful 
//

$app->post('/people/:peopleid/transferdevicesto/:newpeopleid', function($peopleid, $newpeopleid) use ( $person) {
	if ( ! $person->ContactAdmin ) {
		$r['error'] = true;
		$r['errorcode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$r['error']=false;
		$r['errorcode']=200;

		// Verify the userids are real
		foreach(array('peopleid','newpeopleid') as $var){
			$p=new People();

			$p->UserID=$$var;
			if(!$p->GetPerson() && ($var!='newpeopleid' && $$var==0)){
				$r['error']=true;
				$r['message']="$var is not valid";
				continue;
			}
		}

		// If we error above don't attempt to make changes
		if(!$r['error']){
			$dev=new Device();
			$dev->PrimaryContact=$peopleid;
			$updated = 0;
			foreach($dev->Search() as $d){
				$d->PrimaryContact=$newpeopleid;
				if(!$d->UpdateDevice()){
					// If we encounter an error stop immediately
					$r['error']=true;
					$r['message']=__("Device update has failed");
					continue;
				} else {
					$updated++;
				}
			}

			if ( $r['error'] == false ) {
				$r['message'] = $updated." ".__("devices updated");
			}
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/powerport/:deviceid
//	Method:	POST
//	Params:	
//		required: DeviceID, PortNumber
//		optional: Label, ConnectedDeviceID, ConnectedPort, Notes
//	Returns:  true/false on update operation
//

$app->post( '/powerport/:deviceid', function($deviceid) use ($person) {
	if ( ! $person->WriteAccess ) {
		$r['error'] = true;
		$r['errorcode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$pp=new PowerPorts();
		$pp->DeviceID=$deviceid;
		$vars = getParsedBody();
		foreach($vars as $prop => $val){
			$pp->$prop=$val;
		}

		$r['error']=($pp->updatePort())?false:true;
		$r['errorcode']=200;
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/colorcode/:colorid
//	Method:	POST
//	Params:	
//		required: ColorID, Name
//		optional: DefaultNote 
//	Returns:  true/false on update operation
//

$app->post( '/colorcode/:colorid', function($colorid) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['error'] = true;
		$r['errorcode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$cc=new ColorCoding();
		$vars = getParsedBody();
		foreach($vars as $prop => $val){
			if ( property_exists($cc, $prop)) {
				$cc->$prop=$val;
			}
		}

		$cc->ColorID = $colorid;

		if ( $cc->UpdateCode() ) {
			$r['error']=false;
			$r['errorcode']=200;
		} else {
			$r['error'] = true;
			$r['errorcode'] = 400;
			$r['message'] = __("Error updating color code.");
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/colorcode/:colorid/replacewith/:newcolorid
//	Method:	POST
//	Params:	
//		required: ColorID, NewColorID
//		optional: DefaultNote, Name
//	Returns:  true/false on update operation
//

$app->post( '/colorcode/:colorid/replacewith/:newcolorid', function($colorid, $newcolorid) use ( $person ) {
	if ( ! $person->SiteAdmin ) {
		$r['error'] = true;
		$r['errorcode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		if ( ColorCoding::ResetCode($colorid, $newcolorid)) {
			$r['error']=false;
			$r['errorcode']=200;
		} else {
			$r['error'] = true;
			$r['errorcode'] = 401;
			$r['message'] = __("Invalid operation");
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/device/:deviceid
//	Method:	POST
//	Params:	deviceid (passed in URL)
//	Returns:  true/false on update operation
//

$app->post( '/device/:deviceid', function($deviceid) {
	// Rights are handled in the back end classes based upon the UserID attached to $person, so skip checks here
	$dev=new Device();
	$dev->DeviceID=$deviceid;
	
	if(!$dev->GetDevice()){
		$r['error']=true;
		$r['errorcode']=404;
		$r['message']=__("No device found with DeviceID")." $deviceid";
	}else{
		if($dev->Rights!="Write"){
			$r['error']=true;
			$r['errorcode']=401;
			$r['message']=__("Access Denied");
		}else{
			$vars = getParsedBody();
			foreach($vars as $prop => $val){
				if ( property_exists( $dev, $prop )) {
					$dev->$prop=$val;
				}
			}
			if(!$dev->UpdateDevice()){
				$r['error']=true;
				$r['errorcode']=401;
				$r['message']=__("Update failed");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
			}
		}
	}

	echoResponse( $r );
});

$app->post( '/device/:deviceid/store', function($deviceid) {
	// Have to process all the extra bits involved with moving something to storage
	// so that's why this is a different routine than simply updating a device

	$dev=new Device();
	$dev->DeviceID=$deviceid;

	if(!$dev->GetDevice()){
		$r['error']=true;
		$r['errorcode']=404;
		$r['message']=__("No device found with DeviceID")." $deviceid";
	}else{
		if($dev->Rights!="Write"){
			$r['error']=true;
			$r['errorcode']=401;
			$r['message']=__("Access Denied");
		}else{
			if(!$dev->MoveToStorage()){
				$r['error']=true;
				$r['errorcode']=401;
				$r['message']=__("Update failed");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
			}
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/devicetemplate/:templateid
//	Method:	POST
//	Params:	
//		Required: templateid
//		Optional: everything else
//	Returns: true/false on update operation 
//

$app->post( '/devicetemplate/:templateid', function($templateid) use ($person) {
	$dt=new DeviceTemplate($templateid);
	if(!$person->WriteAccess){
		$r['error']=true;
		$r['errorcode']=401;
		$r['message']=__("Access Denied");
	}else{
		if(!$dt->GetTemplateByID()){
			$r['error']=true;
			$r['errorcode']=404;
			$r['message']=__("No device template found with TemplateID: ").$templateid;
		}else{
			$vars = getParsedBody();
			foreach($vars as $prop => $val){
				if ( property_exists( $dt, $prop )) {
					$dt->$prop=$val;
				}
			}
			if(!$dt->UpdateTemplate()){
				$r['error']=true;
				$r['errorcode']=400;
				$r['message']=__("Device template update failed");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
			}
		}
	}
	echoResponse( $r );
});

//
//	URL:	/api/v1/devicetemplate/:templateid/dataport/:portnumber
//	Method:	POST
//	Params:	
//		Required: templateid, portnumber, portlabel
//		Optional: everything else
//	Returns: true/false on update operation
//

$app->post( '/devicetemplate/:templateid/dataport/:portnumber', function($templateid, $portnumber) use ($person) {
	$tp=new TemplatePorts();
	$tp->TemplateID=$templateid;
	$tp->PortNumber=$portnumber;

	if(!$person->WriteAccess){
		$r['error']=true;
		$r['errorcode']=401;
		$r['message']=__("Access Denied");
	}else{
		if(!$tp->getPort()){
			$r['error']=true;
			$r['errorcode']=404;
			$r['message']=__("Template port not found with id: ")." $templateid:$portnum";
		}else{
			$vars = getParsedBody();
			foreach($vars as $prop => $val){
				if ( property_exists( $tp, $prop )) {
					$tp->$prop=$val;
				}
			}
			if(!$tp->updatePort()){
				$r['error']=true;
				$r['errorcode']=400;
				$r['message']=__("Template port update failed");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
				$r['dataport']=$tp;
			}
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/devicetemplate/:templateid/slot/:slotnum
//	Method:	POST
//	Params:	
//		Required: templateid, slutnum
//		Optional: everything else
//	Returns: true/false on update operation
//

$app->post( '/devicetemplate/:templateid/slot/:slotnum', function($templateid, $slotnum) use ($person) {
	$s=new Slot();
	$s->TemplateID=$templateid;
	$s->PortNumber=$slotnum;

	if(!$person->WriteAccess){
		$r['error']=true;
		$r['errorcode']=401;
		$r['message']=__("Access Denied");
	}else{
		if(!$s->GetSlot()){
			$r['error']=true;
			$r['errorcode']=404;
			$r['message']=__("Template slot not found with id: ")." $templateid:$slotnum";
		}else{
			$vars = getParsedBody();
			foreach($vars as $prop => $val){
				if ( property_exists( $s, $prop )) {
					$s->$prop=$val;
				}
			}
			// Just to make sure 
			$s->TemplateID=$templateid;
			$s->PortNumber=$slotnum;
			if(!$s->UpdateSlot()){
				$r['error']=true;
				$r['errorcode']=400;
				$r['message']=__("Template slot update failed");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
				$r['dataport']=$s;
			}
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/devicestatus/:statusid
//	Method:	POST
//	Params: 
//		Required: StatusID
//		Optional: Status, ColorCode
//	Returns: true/false on update operations 
//

$app->post( '/devicestatus/:statusid', function($statusid) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['error'] = true;
		$r['errorcode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$ds=new DeviceStatus($statusid);
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $ds, $prop )) {
				$ds->$prop = $val;
			}
		}
		$ds->StatusID=$statusid;

		if(!$ds->updateStatus()){
			$r['error']=true;
			$r['errorcode']=400;
			$r['message']=__("Error creating new status.");
		}else{
			$r['error']=false;
			$r['errorcode']=200;
			$r['message']=__("Status updated successfully.");
			$r['devicestatus']=$ds;
		}
	}

	echoResponse( $r );
});

//
//	URL:	/api/v1/manufacturer
//	Method:	POST
//	Params:	none
//	Returns: true/false on update operation
//

$app->post( '/manufacturer/:manufacturerid', function($manufacturerid) use ($person) {
	$man=new Manufacturer();
	$man->ManufacturerID=$manufacturerid;
	
	$r['error']=true;
	$r['errorcode']=400;

	if(!$person->SiteAdmin){
		$r['errorcode']=401;
		$r['message']=__("Access Denied");
	}else{
		if(!$man->GetManufacturerByID()){
			$r['errorcode'] = 404;
			$r['message']=__("Manufacturer not found with id: ").$args['manufacturerid'];
		}else{
			$vars = getParsedBody();
			foreach($vars as $prop => $val){
				if ( property_exists($man, $prop)) {
					$man->$prop=$val;
				}
			}
			if(!$man->UpdateManufacturer()){
				$r['message']=__("Manufacturer update failed");
			}else{
				$r['error']=false;
				$r['errorcode']=200;
			}
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/create_location
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_location', function() use($person) {
    $location = new Location();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $location, $prop )) {
                $location->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$location->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['location']=$location;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['location']=$location;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_room
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_room', function() use($person) {
    $room = new Room();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $room, $prop )) {
                $room->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$room->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['room']=$room;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['room']=$room;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_rack
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_rack', function() use($person) {
    $rack = new Rack();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $rack, $prop )) {
                $rack->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$rack->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['rack']=$rack;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['rack']=$rack;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_devices
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_devices', function() use($person) {
    $device_new = new Device_new();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $device_new, $prop )) {
            $device_new->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$device_new->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['device']=$device_new;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['device']=$device_new;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_interface
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_interface', function() use($person) {
    $interface = new InterfaceConn();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $interface, $prop )) {
                $interface->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$interface->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['interface']=$interface;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['interface']=$interface;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_console
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_console', function() use($person) {
    $console = new ConsoleConn();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $console, $prop )) {
                $console->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$console->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['console']=$console;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['console']=$console;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_power
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_power', function() use($person) {
    $power = new PowerConn();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $power, $prop )) {
                $power->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$power->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['power_connection']=$power;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['power_connection']=$power;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_assets
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_assets', function() use($person) {
    $assets = new Asset();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $assets, $prop )) {
                $assets->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$assets->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['assets']=$assets;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['assets']=$assets;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_assets_status
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_assets_status', function() use($person) {
    $assets = new Asset();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $assets, $prop )) {
                $assets->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$assets->CreateStatus($params)){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['assets_status']=$assets;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['assets_status']=$assets;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_assets_supplier
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_assets_supplier', function() use($person) {
    $assets = new AssetSupplier();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $assets, $prop )) {
                $assets->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$assets->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['assets_supplier']=$assets;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['assets_supplier']=$assets;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_assets_category
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_assets_category', function() use($person) {
    $assets = new AssetCategory();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $assets, $prop )) {
                $assets->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$assets->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['assets_category']=$assets;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['assets_category']=$assets;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_assets_model
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_assets_model', function() use($person) {
    $assets = new Model();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $assets, $prop )) {
                $assets->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$assets->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['assets_model']=$assets;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['assets_model']=$assets;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_cluster
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_cluster', function() use($person) {
    $cluster = new Cluster();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $cluster, $prop )) {
                $cluster->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$cluster->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['cluster']=$cluster;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['cluster']=$cluster;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_cluster_type
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_cluster_type', function() use($person) {
    $cluster_type = new ClusterType();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $cluster_type, $prop )) {
                $cluster_type->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$cluster_type->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['cluster_type']=$cluster_type;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['cluster_type']=$cluster_type;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_cluster_group
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_cluster_group', function() use($person) {
    $cluster_group = new ClusterGroup();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $cluster_group, $prop )) {
                $cluster_group->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$cluster_group->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['cluster_group']=$cluster_group;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['cluster_group']=$cluster_group;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_virtual_machine
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_virtual_machine', function() use($person) {
    $virtual_machine = new Virtual_machine();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $virtual_machine, $prop )) {
                $virtual_machine->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$virtual_machine->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['virtual_machine']=$virtual_machine;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['virtual_machine']=$virtual_machine;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_aggregate
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_aggregate', function() use($person) {
    $ipam_aggregate = new IpamAggreget();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_aggregate, $prop )) {
                $ipam_aggregate->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_aggregate->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['aggregate']=$ipam_aggregate;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['aggregate']=$ipam_aggregate;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_prefix
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_prefix', function() use($person) {
    $ipam_prefix = new IpamPrefix();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_prefix, $prop )) {
                $ipam_prefix->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_prefix->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['prefix']=$ipam_prefix;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['prefix']=$ipam_prefix;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_prefix_role
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_prefix_role', function() use($person) {
    $ipam_prefix_role = new IpamPrefixRole();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_prefix_role, $prop )) {
                $ipam_prefix_role->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_prefix_role->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['prefix_role']=$ipam_prefix_role;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['prefix_role']=$ipam_prefix_role;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_ipaddress
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_ipaddress', function() use($person) {
    $ipam_ipaddress = new IpamIPaddress();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_ipaddress, $prop )) {
                $ipam_ipaddress->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_ipaddress->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['ip_address']=$ipam_ipaddress;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['ip_address']=$ipam_ipaddress;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_vlan
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_vlan', function() use($person) {
    $ipam_vlan = new IpamVLAN();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_vlan, $prop )) {
                $ipam_vlan->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_vlan->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['vlan']=$ipam_vlan;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['vlan']=$ipam_vlan;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_vlan_group
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_vlan_group', function() use($person) {
    $ipam_vlan_group = new IpamVLANGroup();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_vlan_group, $prop )) {
                $ipam_vlan_group->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_vlan_group->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['vlan_group']=$ipam_vlan_group;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['vlan_group']=$ipam_vlan_group;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_rir
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_rir', function() use($person) {
    $ipam_rir = new IpamRIR();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_rir, $prop )) {
                $ipam_rir->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_rir->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['rir']=$ipam_rir;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['rir']=$ipam_rir;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_vrf
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_vrf', function() use($person) {
    $ipam_vrf = new IpamVRF();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $ipam_vrf, $prop )) {
                $ipam_vrf->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$ipam_vrf->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['vrf']=$ipam_vrf;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['vrf']=$ipam_vrf;
        }
    }

    echoResponse( $r );
});

//	URL:	/api/v1/create_service
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_service', function() use($person) {
    $service = new Service();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $service, $prop )) {
                $service->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$service->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['service']=$service;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['service']=$service;
        }
    }

    echoResponse( $r );
});
//	URL:	/api/v1/create_interface
//	Method:	POST
//	Params:	none
//	Returns: true/false on create operation

$app->post( '/create_idrac', function() use($person) {
    $idrac_settings = new IdracSetting();

    $vars = getParsedBody();

    foreach( $vars as $prop=>$val ) {
        if ( property_exists( $idrac_settings, $prop )) {
                $idrac_settings->$prop = $val;
        }
    }

    if(!$person->SiteAdmin){
        $r['errorcode']=401;
        $r['message']=__("Access Denied");
    } else {
        if(!$idrac_settings->CreateObject()){
            $r['status']="Fail";
            $r['statuscode']=405;
            $r['idrac_settings']=$idrac_settings;
        } else {
            $r['status']="Success";
            $r['statuscode']=201;
            $r['idrac_settings']=$idrac_settings;
        }
    }

    echoResponse( $r );
});

/* UPDATE API CALLs CODE START */

//	URL:	/api/v1/update_location/:location_id
//	Method:	POST
//	Params: Required: locationID
//	Returns: true/false on update operations 

$app->post( '/update_location/:location_id', function($location_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$location=new Location();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $location, $prop )) {
				$location->$prop = $val;
			}
		}
		$location->PortID=$location_id;

		if(!$location->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['location']=$location;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_room/:room_id
//	Method:	POST
//	Params: Required: roomID
//	Returns: true/false on update operations 

$app->post( '/update_room/:room_id', function($room_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$room=new Room();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $room, $prop )) {
				$room->$prop = $val;
			}
		}
		$room->PortID=$room_id;

		if(!$room->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['room']=$room;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_rack/:rack_id
//	Method:	POST
//	Params: Required: rackID
//	Returns: true/false on update operations 

$app->post( '/update_rack/:rack_id', function($rack_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$rack=new Rack();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $rack, $prop )) {
				$rack->$prop = $val;
			}
		}
		$rack->PortID=$rack_id;

		if(!$rack->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['rack']=$rack;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_device/:device_id
//	Method:	POST
//	Params: Required: deviceID
//	Returns: true/false on update operations 

$app->post( '/update_device/:device_id', function($device_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$device=new Device_new();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $device, $prop )) {
				$device->$prop = $val;
			}
		}
		$device->PortID=$device_id;

		if(!$device->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['device']=$device;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_interface/:interface_id
//	Method:	POST
//	Params: Required: interfaceID
//	Returns: true/false on update operations 

$app->post( '/update_interface/:interface_id', function($interface_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$interface=new InterfaceConn();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $interface, $prop )) {
				$interface->$prop = $val;
			}
		}
		$interface->PortID=$interface_id;

		if(!$interface->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['interface']=$interface;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_console/:console_id
//	Method:	POST
//	Params: Required: consoleID
//	Returns: true/false on update operations 

$app->post( '/update_console/:console_id', function($console_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$console = new ConsoleConn();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $console, $prop )) {
				$console->$prop = $val;
			}
		}
		$console->PortID=$console_id;

		if(!$console->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['interface']=$console;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_power/:power_id
//	Method:	POST
//	Params: Required: powerID
//	Returns: true/false on update operations 

$app->post( '/update_power/:power_id', function($power_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$power = new PowerConn();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $power, $prop )) {
				$power->$prop = $val;
			}
		}
		$power->PortID=$power_id;

		if(!$power->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['power']=$power;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_assets/:assets_id
//	Method:	POST
//	Params: Required: AssetsID
//	Returns: true/false on update operations 

$app->post( '/update_assets/:assets_id', function($assets_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$assets = new Asset();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $assets, $prop )) {
				$assets->$prop = $val;
			}
		}
		$assets->PortID=$assets_id;

		if(!$assets->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['assets']=$assets;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_assets_status/:assets_status_id
//	Method:	POST
//	Params: Required: AssetsID
//	Returns: true/false on update operations 

$app->post( '/update_assets_status/:assets_status_id', function($assets_status_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$assets = new AssetManage();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $assets, $prop )) {
				$assets->$prop = $val;
			}
		}
		$assets->PortID=$assets_status_id;

		if(!$assets->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['assets']=$assets;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_assets_supplier/:assets_supplier_id
//	Method:	POST
//	Params: Required: AssetsID
//	Returns: true/false on update operations 

$app->post( '/update_assets_supplier/:assets_supplier_id', function($assets_supplier_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$assets = new AssetSupplier();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $assets, $prop )) {
				$assets->$prop = $val;
			}
		}
		$assets->PortID=$assets_supplier_id;

		if(!$assets->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['assets_supplier']=$assets;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_assets_category/:assets_category_id
//	Method:	POST
//	Params: Required: AssetsID
//	Returns: true/false on update operations 

$app->post( '/update_assets_category/:assets_category_id', function($assets_category_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$assets = new AssetCategory();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $assets, $prop )) {
				$assets->$prop = $val;
			}
		}
		$assets->PortID=$assets_category_id;

		if(!$assets->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['assets_category']=$assets;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_assets_model/:assets_model_id
//	Method:	POST
//	Params: Required: AssetsID
//	Returns: true/false on update operations 

$app->post( '/update_assets_model/:assets_model_id', function($assets_model_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$assets = new Model();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $assets, $prop )) {
				$assets->$prop = $val;
			}
		}
		$assets->PortID=$assets_model_id;

		if(!$assets->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['assets_model']=$assets;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_cluster/:cluster_id
//	Method:	POST
//	Params: Required: ClusterID
//	Returns: true/false on update operations 

$app->post( '/update_cluster/:cluster_id', function($cluster_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$cluster = new Cluster();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $cluster, $prop )) {
				$cluster->$prop = $val;
			}
		}
		$cluster->PortID=$cluster_id;

		if(!$cluster->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['cluster']=$cluster;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_cluster_group/:cluster_group_id
//	Method:	POST
//	Params: Required: ClusterGroupID
//	Returns: true/false on update operations 

$app->post( '/update_cluster_group/:cluster_group_id', function($cluster_group_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$cluster = new ClusterGroup();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $cluster, $prop )) {
				$cluster->$prop = $val;
			}
		}
		$cluster->PortID=$cluster_group_id;

		if(!$cluster->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['cluster_group']=$cluster;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_cluster_type/:cluster_type_id
//	Method:	POST
//	Params: Required: ClusterTypeID
//	Returns: true/false on update operations 

$app->post( '/update_cluster_type/:cluster_type_id', function($cluster_type_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$cluster = new ClusterType();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $cluster, $prop )) {
				$cluster->$prop = $val;
			}
		}
		$cluster->PortID=$cluster_type_id;

		if(!$cluster->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		}else{
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['cluster_type']=$cluster;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_virtual_machine/:vm_id
//	Method:	POST
//	Params: Required: VMID
//	Returns: true/false on update operations 

$app->post( '/update_virtual_machine/:vm_id', function($vm_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$virtual_machine = new Virtual_machine();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $virtual_machine, $prop )) {
				$virtual_machine->$prop = $val;
			}
		}
		$virtual_machine->PortID=$vm_id;

		if(!$virtual_machine->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['virtual_machine']=$virtual_machine;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_virtual_machine/:vm_id
//	Method:	POST
//	Params: Required: VMID
//	Returns: true/false on update operations 

$app->post( '/update_virtual_machine/:vm_id', function($vm_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$virtual_machine = new Virtual_machine();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $virtual_machine, $prop )) {
				$virtual_machine->$prop = $val;
			}
		}
		$virtual_machine->PortID=$vm_id;

		if(!$virtual_machine->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['virtual_machine']=$virtual_machine;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_aggregate/:aggregate_id
//	Method:	POST
//	Params: Required: aggregate_id
//	Returns: true/false on update operations 

$app->post( '/update_aggregate/:aggregate_id', function($aggregate_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$aggregate = new IpamAggreget();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $aggregate, $prop )) {
				$aggregate->$prop = $val;
			}
		}
		$aggregate->PortID=$aggregate_id;

		if(!$aggregate->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['aggregate']=$aggregate;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_prefix/:prefix_id
//	Method:	POST
//	Params: Required: prefix_id
//	Returns: true/false on update operations 

$app->post( '/update_prefix/:prefix_id', function($prefix_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$prefix = new IpamPrefix();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $prefix, $prop )) {
				$prefix->$prop = $val;
			}
		}
		$prefix->PortID=$prefix_id;

		if(!$prefix->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['prefix']=$prefix;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_prefix_role/:prefix_role_id
//	Method:	POST
//	Params: Required: prefix_role_id
//	Returns: true/false on update operations 

$app->post( '/update_prefix_role/:prefix_role_id', function($prefix_role_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$prefix_role = new IpamPrefixRole();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $prefix_role, $prop )) {
				$prefix_role->$prop = $val;
			}
		}
		$prefix_role->PortID=$prefix_role_id;

		if(!$prefix_role->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['prefix_role']=$prefix_role;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_ipaddress/:ipaddress
//	Method:	POST
//	Params: Required: ipaddress_id
//	Returns: true/false on update operations 

$app->post( '/update_ipaddress/:ipaddress_id', function($ipaddress_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$ipaddress = new IpamIPaddress();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $ipaddress, $prop )) {
				$ipaddress->$prop = $val;
			}
		}
		$ipaddress->PortID=$ipaddress_id;

		if(!$ipaddress->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['ipaddress']=$ipaddress;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_vlan/:vlan_id
//	Method:	POST
//	Params: Required: vlan_id
//	Returns: true/false on update operations 

$app->post( '/update_vlan/:vlan_id', function($vlan_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$vlan = new IpamVLAN();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $vlan, $prop )) {
				$vlan->$prop = $val;
			}
		}
		$vlan->PortID=$vlan_id;

		if(!$vlan->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['vlan']=$vlan;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_vlan_group/:vlan_group_id
//	Method:	POST
//	Params: Required: vlan_group_id
//	Returns: true/false on update operations 

$app->post( '/update_vlan_group/:vlan_group_id', function($vlan_group_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$vlan_group = new IpamVLANGroup();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $vlan_group, $prop )) {
				$vlan_group->$prop = $val;
			}
		}
		$vlan_group->PortID=$vlan_group_id;

		if(!$vlan_group->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['vlan_group']=$vlan_group;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_rir/:rir_id
//	Method:	POST
//	Params: Required: rir_id
//	Returns: true/false on update operations 

$app->post( '/update_rir/:rir_id', function($rir_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$rir = new IpamRIR();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $rir, $prop )) {
				$rir->$prop = $val;
			}
		}
		$rir->PortID=$rir_id;

		if(!$rir->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['rir']=$rir;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_vrf/:vrf_id
//	Method:	POST
//	Params: Required: vrf_id
//	Returns: true/false on update operations 

$app->post( '/update_vrf/:vrf_id', function($vrf_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$vrf = new IpamVRF();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $vrf, $prop )) {
				$vrf->$prop = $val;
			}
		}
		$vrf->PortID=$vrf_id;

		if(!$vrf->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['vrf']=$vrf;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_service/:service_id
//	Method:	POST
//	Params: Required: service_id
//	Returns: true/false on update operations 

$app->post( '/update_service/:service_id', function($service_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$service = new Service();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $service, $prop )) {
				$service->$prop = $val;
			}
		}
		$service->PortID=$service_id;

		if(!$service->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['service']=$service;
		}
	}

	echoResponse( $r );
});

//	URL:	/api/v1/update_idrac/:idrac_id
//	Method:	POST
//	Params: Required: idrac_id
//	Returns: true/false on update operations

$app->post( '/update_idrac/:idrac_id', function($idrac_id) use ($person) {
	if ( ! $person->SiteAdmin ) {
		$r['status'] = "Fail";
		$r['statuscode'] = 401;
		$r['message'] = __("Access Denied");
	} else {
		$idrac_setting = new IdracSetting();
		$vars = getParsedBody();

		foreach( $vars as $prop=>$val ) {
			if ( property_exists( $idrac_setting, $prop )) {
				$idrac_setting->$prop = $val;
			}
		}
		$idrac_setting->PortID=$idrac_id;

		if(!$idrac_setting->UpdateObject()){
			$r['status']="Fail";
			$r['statuscode']=400;
			$r['message']=__("Error creating while update.");
		} else {
			$r['status']="Success";
			$r['statuscode']=200;
			$r['message']=__("Record updated successfully.");
			$r['idrac_setting']=$idrac_setting;
		}
	}

	echoResponse( $r );
});
/* UPDATE API CALLs CODE END */
?>
