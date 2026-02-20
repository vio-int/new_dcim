<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Location {
        
	var $PortID;
	var $Name;
        var $Slug;
        var $Status;
        var $Location;
        var $Facility;
        var $ASN;
        var $Time_zone;
        var $Description;
        var $Physical_address;
        var $Shipping_address;
        var $Latitude;
        var $Longitude;
        var $Contact_name;
        var $Contact_email;
        var $Contact_no;
        var $Tag;
        var $Comment;
	
	public function __construct($manufacturerid=false){
            if($manufacturerid){
                $this->PortID=$manufacturerid;
            }
            return $this;
	}

	function MakeSafe(){
            $this->PortID=intval($this->PortID);
            $this->Name=sanitize($this->Name);
            $this->Slug=sanitize($this->Slug);
            $this->Status=sanitize($this->Status);
            $this->Location=sanitize($this->Location);
            $this->Facility=sanitize($this->Facility);
            $this->ASN=sanitize($this->ASN);
            $this->Time_zone=sanitize($this->Time_zone);
            $this->Description=sanitize($this->Description);
            $this->Physical_address=sanitize($this->Physical_address);
            $this->Shipping_address=sanitize($this->Shipping_address);
            $this->Latitude=sanitize($this->Latitude);
            $this->Longitude=sanitize($this->Longitude);
            $this->Contact_name=sanitize($this->Contact_name);
            $this->Contact_email=sanitize($this->Contact_email);
            $this->Contact_no=sanitize($this->Contact_no);
            $this->Tag=sanitize($this->Tag);
            $this->Comment=sanitize($this->Comment);
	}

	function MakeDisplay(){
            $this->Name=stripslashes($this->Name);
        }

	static function RowToObject($row){
            $m=new Location();
            $m->PortID=isset($row["id"])?$row["id"]:'';
            $m->Name=isset($row["name"])?$row["name"]:'';
            $m->Slug=isset($row["slug"])?$row["slug"]:'';
            $m->Status=isset($row["status"])?$row["status"]:'';
            $m->Location=isset($row["location_id"])?$row["location_id"]:'';
            $m->Facility=isset($row["facility"])?$row["facility"]:'';
            $m->ASN=isset($row["asn"])?$row["asn"]:'';
            $m->Time_zone=isset($row["time_zone"])?$row["time_zone"]:'';
            $m->Description=isset($row["description"])?$row["description"]:'';
            $m->Physical_address=isset($row["physical_address"])?$row["physical_address"]:'';
            $m->Shipping_address=isset($row["shipping_address"])?$row["shipping_address"]:'';
            $m->Latitude=isset($row["latitude"])?$row["latitude"]:'';
            $m->Longitude=isset($row["longitude"])?$row["longitude"]:'';
            $m->Contact_name=isset($row["contact_name"])?$row["contact_name"]:'';
            $m->Contact_email=isset($row["contact_email"])?$row["contact_email"]:'';
            $m->Contact_no=isset($row["contact_no"])?$row["contact_no"]:'';
            $m->Tag=isset($row["tag"])?$row["tag"]:'';
            $m->Comment=isset($row["comment"])?$row["comment"]:'';
            $m->Created_at=isset($row["created"])?$row["created"]:'';
            $m->Updated_at=isset($row["last_updated"])?$row["last_updated"]:'';
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Location();
            $m->PortID=isset($row["id"])?$row["id"]:'';
            $m->Name=isset($row["name"])?$row["name"]:'';
            $m->Slug=isset($row["slug"])?$row["slug"]:'';
            $m->Status=isset($row["status"])?$row["status"]:'';
            $m->Location=isset($row["location_id"])?$row["location_id"]:'';
            $m->Facility=isset($row["facility"])?$row["facility"]:'';
            $m->ASN=isset($row["asn"])?$row["asn"]:'';
            $m->Time_zone=isset($row["time_zone"])?$row["time_zone"]:'';
            $m->Description=isset($row["description"])?$row["description"]:'';
            $m->Physical_address=isset($row["physical_address"])?$row["physical_address"]:'';
            $m->Shipping_address=isset($row["shipping_address"])?$row["shipping_address"]:'';
            $m->Latitude=isset($row["latitude"])?$row["latitude"]:'';
            $m->Longitude=isset($row["longitude"])?$row["longitude"]:'';
            $m->Contact_name=isset($row["contact_name"])?$row["contact_name"]:'';
            $m->Contact_email=isset($row["contact_email"])?$row["contact_email"]:'';
            $m->Contact_no=isset($row["contact_no"])?$row["contact_no"]:'';
            $m->Tag=isset($row["tag"])?$row["tag"]:'';
            $m->Comment=isset($row["comment"])?$row["comment"]:'';
            $m->Created_at=isset($row["created"])?$row["created"]:'';
            $m->Updated_at=isset($row["last_updated"])?$row["last_updated"]:'';
            
            $m->MakeDisplay();
            unset($m->PortID);
            unset($m->Location);
            
            return $m;
	}
        
	function prepare( $sql ) {
            global $dbh;
            return $dbh->prepare( $sql );
	}
	
	function query($sql){
            global $dbh;
            return $dbh->query($sql);
	}
	
	function exec($sql){
            global $dbh;
            return $dbh->exec($sql);
	}
        
	function Search($indexedbyid=false,$loose=false){
		$o=new stdClass();
		// Store any values that have been added before we make them safe 
                
		foreach($this as $prop => $val){
                    if(isset($val)){
                        $o->$prop=$val;
                    }
		}
                
		// Make everything safe for us to search with
		$this->MakeSafe();

		// This will store all our extended sql
		$sqlextend="";
		foreach($this as $prop => $val){
                    if($val){
                        extendsql($prop,$val,$sqlextend,$loose);
                    }
		}
                
		$sql="SELECT * FROM location WHERE is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["LocationID"]]=Location::RowToSearchObject($row);
                    }else{
                        $dcList[]=Location::RowToSearchObject($row);
                    }
		}
                
		return $dcList;
	}
        
	// Wrapper to make this method like the other classes
	function GetObject(){
            return $this->GetOrderByID();
	}
	
	function GetOrderByID(){
            $this->MakeSafe();

            $sql="SELECT * FROM location WHERE is_deleted='N' AND id=$this->PortID;";

            $result = $this->query($sql);
            if($result && $row=$result->fetch()){
                    foreach(Location::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM location WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Location::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetLocationList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM location WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql);
            if ($result) {
                foreach($result as $row){
                        if($indexbyid){
                                $ManufacturerList[$row['PortID']]=Location::RowToObject($row);
                        }else{
                                $ManufacturerList[]=Location::RowToObject($row);
                        }
                }
            }
            
            return $ManufacturerList;
	}
                    
        static function GetLocationListRow($filter){
            global $dbh;
            
            $limit = 15; 
            if (isset($_GET["page"])) {
                $page = $_GET["page"]; 
            } else { 
                $page=1;
            }
            $start_from = ($page-1) * $limit; 
            
            $incr = "";
            if(isset($filter['sort_on'])){
                $sort_on = $filter['sort_on'];
            }
            if(isset($filter['sort_by'])){
                $sort_by = $filter['sort_by'];
            }
            if(isset($filter['location'])){
                $incr .= " AND id =".$filter['location'];
            }
            if(isset($filter['status'])){
                $incr .= " AND status ='".$filter['status']."'";
            }
            if(isset($filter['time_zone'])){
                $incr .= " AND time_zone ='".$filter['time_zone']."'";
            }
            if(isset($filter['contact_name'])){
                $incr .= " AND contact_name ='".$filter['contact_name']."'";
            }
            if(isset($filter['contact_email'])){
                $incr .= " AND contact_email ='".$filter['contact_email']."'";
            }
            
            $sql="SELECT * FROM location WHERE is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            
            $ManufacturerList=array();
            $result = $dbh->query($sql);
            if ($result) {
                foreach($result as $row){
                        if($indexbyid){
                                $ManufacturerList[$row['PortID']]=Location::RowToObject($row);
                        }else{
                                $ManufacturerList[]=Location::RowToObject($row);
                        }
                }
            }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetLocationOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['location'])){
                $incr .= " AND id =".$filter['location'];
            }
            
            $sql="SELECT * FROM location WHERE is_deleted='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if(isset($indexbyid)){
                    $RowList[$row['PortID']]= Location::RowToObject($row);
                }else{
                    $RowList[]= Location::RowToObject($row);
                }
            }
            return $RowList;
	}
        
        function GetLocationCounter($filter){
            global $dbh;
            
            $RowList=array();
            $incr = "";
            if(isset($filter['location'])){
                $incr .= " AND site_id =".$filter['location'];
            }
            
            $rack_sql="SELECT COUNT(*) as total_rack FROM rack WHERE is_deleted='N' AND is_simulation='N' ".$incr."";
            $rack_result = $dbh->query($rack_sql);
            $RowList['total_rack'] = $rack_result ? $rack_result->fetch() : array('total_rack' => 0);
            
            $device_sql="SELECT COUNT(*) as total_device FROM device WHERE is_deleted='N' AND is_simulation='N' ".$incr."";
            $device_result = $dbh->query($device_sql);
            $RowList['total_device'] = $device_result ? $device_result->fetch() : array('total_device' => 0);
            
            $prefix_sql="SELECT COUNT(*) as total_prefix FROM ipam_prefix WHERE is_deleted='N' ".$incr."";
            $prefix_result = $dbh->query($prefix_sql);
            $RowList['total_prefix'] = $prefix_result ? $prefix_result->fetch() : array('total_prefix' => 0);
            
            $vlan_sql="SELECT COUNT(*) as total_vlan FROM ipam_vlan WHERE is_deleted='N' ".$incr."";
            $vlan_result = $dbh->query($vlan_sql);
            $RowList['total_vlan'] = $vlan_result ? $vlan_result->fetch() : array('total_vlan' => 0);
            
            $machine_sql="SELECT COUNT(*) as total_machine FROM virtual_machine v JOIN cluster c ON (c.id=v.cluster_id) WHERE v.is_deleted='N' ".$incr."";
            $machine_result = $dbh->query($machine_sql);
            $RowList['total_machine'] = $machine_result ? $machine_result->fetch() : array('total_machine' => 0);
            
            return $RowList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashLocationList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_location FROM location WHERE is_deleted='N'";
            
            $LocationList = array('total_location' => 0);
            $result = $dbh->query($sql);
            if($result){
                $LocationList = $result->fetch();
            }
            
            return $LocationList;
	}
	function CreateObject($params= array()){
            global $dbh;

            $this->MakeSafe();
            
            if(!empty($params)){
                $location_name = sanitize($params['location_name']);
            } else {
                $location_name = $this->Name;
            }
            
            
            $sql="INSERT INTO location SET name='$location_name', slug='$this->Slug', status='$this->Status', location_id='$this->Location', facility='$this->Facility', asn='$this->ASN', time_zone='$this->Time_zone', description='$this->Description', physical_address='$this->Physical_address', shipping_address='$this->Shipping_address', latitude='$this->Latitude', longitude='$this->Longitude', contact_name='$this->Contact_name', contact_email='$this->Contact_email', contact_no='$this->Contact_no', tag='$this->Tag', comment='$this->Comment', created='".date('Y-m-d')."'";
            
            if(!$dbh->exec($sql)){
                    error_log( "SQL Error: " . $sql );
                    return false;
            }else{
                    $this->PortID=$dbh->lastInsertID();
                    (class_exists('LogActions'))?LogActions::LogThis($this):'';
                    $this->MakeDisplay();
                    return true;
            }
	}

	function DeleteObject($TransferTo=null){
            $this->MakeSafe();

            $sql="UPDATE location SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();

            $sql="UPDATE location SET name='$this->Name', slug='$this->Slug', status='$this->Status', location_id='$this->Location', facility='$this->Facility', asn='$this->ASN', time_zone='$this->Time_zone', description='$this->Description', physical_address='$this->Physical_address', shipping_address='$this->Shipping_address', latitude='$this->Latitude', longitude='$this->Longitude', contact_name='$this->Contact_name', contact_email='$this->Contact_email', contact_no='$this->Contact_no', tag='$this->Tag', comment='$this->Comment', last_updated='".date('Y-m-d')."' WHERE id=$this->PortID;";
            
            $old=new Location();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
        
        function ExportReport($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['sort_on'])){
                $sort_on = $filter['sort_on'];
            }
            if(isset($filter['sort_by'])){
                $sort_by = $filter['sort_by'];
            }
            if(isset($filter['location'])){
                $incr .= " AND id =".$filter['location'];
            }
            if(isset($filter['status'])){
                $incr .= " AND status ='".$filter['status']."'";
            }
            if(isset($filter['time_zone'])){
                $incr .= " AND time_zone ='".$filter['time_zone']."'";
            }
            if(isset($filter['contact_name'])){
                $incr .= " AND contact_name ='".$filter['contact_name']."'";
            }
            if(isset($filter['contact_email'])){
                $incr .= " AND contact_email ='".$filter['contact_email']."'";
            }
            
            $sql="SELECT * FROM location WHERE is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Location::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Location::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Location_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Status".$tab_sep."Facility".$tab_sep."ASN".$tab_sep."Time Zone".$tab_sep."Physical Address".$tab_sep."Shipping Address".$tab_sep."Latitude".$tab_sep."Longitude".$tab_sep."Contact Number".$tab_sep."Email".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Status'].$tab_sep.$row['Facility'].$tab_sep.$row['ASN'].$tab_sep.$row['Time_zone'].$tab_sep.$row['Physical_address'].$tab_sep.$row['Shipping_address'].$tab_sep.$row['Latitude'].$tab_sep.$row['Longitude'].$tab_sep.$row['Contact_no'].$tab_sep.$row['Contact_email'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
        
        function GetDataCenter(){
		$this->MakeSafe();
		$sql="SELECT * FROM location WHERE id=$this->PortID;";
                
		if($row=$this->query($sql)->fetch()){
                    	foreach(Location::RowToObject($row) as $prop => $value){
                            //print_r($row);exit;
                            $this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
}
?>