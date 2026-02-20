<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Asset {
        
	var $PortID;
	var $Name;
        var $Asset_tag;
        var $Model;
        var $Status;
        var $Serial_no;
        var $Purchase_date;
        var $First_main_date;
        var $Next_main_date;
        var $Last_main_date;
        var $Supplier;
        var $Order_no;
        var $Purchase_cost;
        var $Warranty;
        var $Maintenance;
        var $Notes;
        var $Location;
        var $Room;
        var $Rack;
        var $Requestable;
        var $Asset_image;
        var $Asset_img;
        var $Status_type;
        var $Status_name;
        var $Total_assets;
        var $Maintenance_status;
        var $Maintenance_date;
        var $params;
        
	public function __construct($manufacturerid=false){
            if($manufacturerid){
                $this->PortID=$manufacturerid;
            }
            return $this;
	}

	function MakeSafe(){
            $this->PortID=intval($this->PortID);
            $this->Name=sanitize($this->Name);
	}

	function MakeDisplay(){
            $this->Name=stripslashes($this->Name);
        }

	static function RowToObject($row){
            $m=new Asset();
            $m->PortID=$row["id"];
            $m->Name=isset($row["name"])?$row["name"]:'';
            $m->Asset_tag=isset($row["asset_tag"])?$row["asset_tag"]:'';
            $m->Model=isset($row["model_id"])?$row["model_id"]:'';
            $m->Status=isset($row["status_id"])?$row["status_id"]:'';
            $m->Serial_no=isset($row["serial_no"])?$row["serial_no"]:'';
            $m->Purchase_date=isset($row["purchase_date"])?$row["purchase_date"]:'';
            $m->First_main_date=isset($row["first_main_date"])?$row["first_main_date"]:'';
            $m->Next_main_date=isset($row["next_main_date"])?$row["next_main_date"]:'';
            $m->Last_main_date=isset($row["last_main_date"])?$row["last_main_date"]:'';
            $m->Supplier=isset($row["supplier_id"])?$row["supplier_id"]:'';
            $m->Order_no=isset($row["order_no"])?$row["order_no"]:'';
            $m->Purchase_cost=isset($row["purchase_cost"])?$row["purchase_cost"]:'';
            $m->Warranty=isset($row["warranty"])?$row["warranty"]:'';
            $m->Maintenance=isset($row["maintenance"])?$row["maintenance"]:'';
            $m->Maintenance_status=isset($row["maintenance_status"])?$row["maintenance_status"]:'';
            $m->Notes=isset($row["note"])?$row["note"]:'';
            $m->Location=isset($row["location_id"])?$row["location_id"]:'';
            $m->Room=isset($row["room_id"])?$row["room_id"]:'';
            $m->Rack=isset($row["rack_id"])?$row["rack_id"]:'';
            $m->Requestable=isset($row["requestable"])?$row["requestable"]:'';
            $m->Status_name=isset($row["status_name"])?$row["status_name"]:'';
            $m->Status_type=isset($row["status_type"])?$row["status_type"]:'';
            $m->Asset_image=isset($row["asset_image"])?$row["asset_image"]:'';
            $m->Asset_img=isset($row["asset_image"])?$row["asset_image"]:'';
            $m->Location_name=isset($row["location_name"])?$row["location_name"]:'';
            $m->Room_name=isset($row["room_name"])?$row["room_name"]:'';
            $m->Rack_name=isset($row["rack_name"])?$row["rack_name"]:'';
            $m->Model_name=isset($row["model_name"])?$row["model_name"]:'';
            $m->Supplier_name=isset($row["supplier_name"])?$row["supplier_name"]:'';
            $m->Total_assets=isset($row["total_assets"])?$row["total_assets"]:'';
            $m->Maintenance_months = isset($row["maintenance_months"])?$row["maintenance_months"]:'';
            $m->Maintenance_date = isset($row["maintenance_date"])?$row["maintenance_date"]:'';
            
            // FILE UPLOAD CODE START
            if(!empty($_FILES['asset_image'])){
                $frontfile_name = $_FILES['asset_image'];
                $fronttmp_name = $_FILES["asset_image"]["tmp_name"];
                $frontFileName = $_FILES["asset_image"]["name"];
                $fronttemp = explode(".", $frontFileName);
                
                $img = $_POST['asset_img_val'];
                $img = str_replace('data:image/jpeg;base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                //$frontnewfilename = $frontFileName . '.' . end($fronttemp);
                //$fronttarget_dir = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/';
                //$fronttarget_file = $fronttarget_dir . $frontFileName;
                $fronttarget_file = _PATH.DIRECTORY_SEPARATOR.'uploads/assets/' . $frontFileName;

                if ($fronttmp_name !="" && file_put_contents($fronttarget_file, $data)) {
                    $m->Asset_img = $frontFileName;
                }
            }
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Asset();
            $m->PortID=$row["id"];
            $m->Name=isset($row["name"])?$row["name"]:'';
            $m->Asset_tag=isset($row["asset_tag"])?$row["asset_tag"]:'';
            $m->Model=isset($row["model_id"])?$row["model_id"]:'';
            $m->Status=isset($row["status_id"])?$row["status_id"]:'';
            $m->Serial_no=isset($row["serial_no"])?$row["serial_no"]:'';
            $m->Purchase_date=isset($row["purchase_date"])?$row["purchase_date"]:'';
            $m->First_main_date=isset($row["first_main_date"])?$row["first_main_date"]:'';
            $m->Next_main_date=isset($row["next_main_date"])?$row["next_main_date"]:'';
            $m->Last_main_date=isset($row["last_main_date"])?$row["last_main_date"]:'';
            $m->Supplier=isset($row["supplier_id"])?$row["supplier_id"]:'';
            $m->Order_no=isset($row["order_no"])?$row["order_no"]:'';
            $m->Purchase_cost=isset($row["purchase_cost"])?$row["purchase_cost"]:'';
            $m->Warranty=isset($row["warranty"])?$row["warranty"]:'';
            $m->Maintenance=isset($row["maintenance"])?$row["maintenance"]:'';
            $m->Notes=isset($row["notes"])?$row["notes"]:'';
            $m->Location=isset($row["location_id"])?$row["location_id"]:'';
            $m->Room=isset($row["room_id"])?$row["room_id"]:'';
            $m->Rack=isset($row["rack_id"])?$row["rack_id"]:'';
            $m->Requestable=isset($row["requestable"])?$row["requestable"]:'';
            $m->Asset_image=isset($row["asset_image"])?$row["asset_image"]:'';
            $m->Asset_img=isset($row["asset_image"])?$row["asset_image"]:'';
            $m->Status_name=isset($row["status_name"])?$row["status_name"]:'';
            $m->Status_type=isset($row["status_type"])?$row["status_type"]:'';
            $m->Supplier_name=isset($row["supplier_name"])?$row["supplier_name"]:'';
            //$m->Total_assets=isset($row["total_assets"])?$row["total_assets"]:'';
            $m->Location_name=isset($row["location_name"])?$row["location_name"]:'';
            $m->Room_name=isset($row["room_name"])?$row["room_name"]:'';
            $m->Rack_name=isset($row["rack_name"])?$row["rack_name"]:'';
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Model);
            unset($m->Status);
            unset($m->Supplier);
            unset($m->Location);
            unset($m->Room);
            unset($m->Rack);
            unset($m->Last_main_date);
            
            return $m;
	}
        
        static function RowToSearchStatusObject($row){
            $m=new Asset();
            $m->PortID=$row["id"];
            $m->Status=isset($row["status"])?$row["status"]:'';
            $m->Status_type=isset($row["status_type"])?$row["status_type"]:'';
            
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Name);
            unset($m->Asset_tag);
            unset($m->Model);
            unset($m->Serial_no);
            unset($m->Purchase_date);
            unset($m->First_main_date);
            unset($m->Next_main_date);
            unset($m->Last_main_date);
            unset($m->Supplier);
            unset($m->Order_no);
            unset($m->Purchase_cost);
            unset($m->Warranty);
            unset($m->Maintenance);
            unset($m->Notes);
            unset($m->Location);
            unset($m->Room);
            unset($m->Rack);
            unset($m->Requestable);
            unset($m->Asset_image);
            unset($m->Asset_img);
            unset($m->Status_name);
            unset($m->Total_assets);
            unset($m->Maintenance_status);
            
            return $m;
	}
        
        static function RowToSearchHistoryObject($row){
            $m=new Asset();
            $m->PortID=$row["id"];
            $m->Status=isset($row["status"])?$row["status"]:'';
            $m->Name=isset($row["assets_name"])?$row["assets_name"]:'';
            $m->Maintenance_date=isset($row["maintenance_date"])?$row["maintenance_date"]:'';
            $m->Maintenance_months=isset($row["maintenance_months"])?$row["maintenance_months"]:'';
            
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Asset_tag);
            unset($m->Model);
            unset($m->Serial_no);
            unset($m->Purchase_date);
            unset($m->First_main_date);
            unset($m->Next_main_date);
            unset($m->Last_main_date);
            unset($m->Supplier);
            unset($m->Order_no);
            unset($m->Purchase_cost);
            unset($m->Warranty);
            unset($m->Maintenance);
            unset($m->Notes);
            unset($m->Location);
            unset($m->Room);
            unset($m->Rack);
            unset($m->Requestable);
            unset($m->Asset_image);
            unset($m->Asset_img);
            unset($m->Status_name);
            unset($m->Total_assets);
            unset($m->Maintenance_status);
            unset($m->Status);
            unset($m->Status_type);
            
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

		$sql="SELECT a.*, l.name as location_name, m.name as model_name, s.name as supplier_name, ss.status as status_name, ss.status_type as status_type, r.name as room_name, r1.name as rack_name
                FROM assets a
                LEFT JOIN location l ON(l.id=a.location_id)
                LEFT JOIN asset_model m ON(m.id=a.model_id)
                LEFT JOIN asset_supplier s ON(s.id=a.supplier_id)
                LEFT JOIN asset_status ss ON(ss.id=a.status_id)
                LEFT JOIN room r ON(r.id=a.room_id)
                LEFT JOIN rack r1 ON(r1.id=a.rack_id)
                WHERE a.is_deleted='N' $sqlextend ORDER BY Name ASC;";
                
		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["AssetsID"]]=Asset::RowToSearchObject($row);
			}else{
				$dcList[]=Asset::RowToSearchObject($row);
			}
		}
                
		return $dcList;
	}
        
        function SearchStatus($indexedbyid=false,$loose=false){
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

		$sql="SELECT * FROM asset_status WHERE is_deleted='N' $sqlextend ORDER BY id ASC;";
                
		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["AssetsID"]]=Asset::RowToSearchStatusObject($row);
			}else{
				$dcList[]=Asset::RowToSearchStatusObject($row);
			}
		}
                
		return $dcList;
	}
        
        function SearchHistory($indexedbyid=false,$loose=false){
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

		$sql="SELECT m.*, a.name as assets_name FROM maintenance_history m JOIN assets a ON (a.id=m.assets_id) WHERE m.id > 0 $sqlextend ORDER BY m.id ASC;";
                
		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["AssetsID"]]=Asset::RowToSearchHistoryObject($row);
			}else{
				$dcList[]=Asset::RowToSearchHistoryObject($row);
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

            $sql="SELECT * FROM assets WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(Asset::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM assets WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Asset::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetAssetList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM assets WHERE is_deleted='N' AND is_simulation='N' ORDER BY next_main_date ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql);
            if ($result) {
                foreach($result as $row){
                        if($indexbyid){
                                $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                        }else{
                                $ManufacturerList[]=Asset::RowToObject($row);
                        }
                }
            }
            
            return $ManufacturerList;
	}
        
        static function GetDepartment(){
            global $dbh;

            $sql="SELECT * FROM department WHERE is_deleted='N' ORDER BY name ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                }else{
                    $ManufacturerList[]=Asset::RowToObject($row);
                }
            } }
            
            return $ManufacturerList;
	}
                    
        static function GetAssetListRow($filter){
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
            if(isset($filter['assets'])){
                $incr .= " AND a.id =".$filter['assets'];
            }
            if(isset($filter['status'])){
                $incr .= " AND a.status_id ='".$filter['status']."'";
            }
            
            if(isset($filter['company'])){
                $incr .= " AND a.company ='".$filter['company']."'";
            }
            
            $sql="SELECT a.*, l.name as location_name, m.name as model_name, s.name as supplier_name, ss.status as status_name, ss.status_type as status_type, r1.name as room_name, r2.name as rack_name
                FROM assets a
                LEFT JOIN location l ON(l.id=a.location_id)
                LEFT JOIN room r1 ON(r1.id=a.room_id)
                LEFT JOIN rack r2 ON(r2.id=a.rack_id)
                LEFT JOIN asset_model m ON(m.id=a.model_id)
                LEFT JOIN asset_supplier s ON(s.id=a.supplier_id)
                LEFT JOIN asset_status ss ON(ss.id=a.status_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Asset::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashAssetList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_assets 
                FROM assets a
                LEFT JOIN location l ON(l.id=a.location_id)
                LEFT JOIN asset_model m ON(m.id=a.model_id)
                LEFT JOIN asset_supplier s ON(s.id=a.supplier_id)
                LEFT JOIN asset_status ss ON(ss.id=a.status_id)
                WHERE a.is_deleted='N'";
            
            $AssetList = array();
            $result_AssetList = $dbh->query($sql);
            $AssetList = $result_AssetList ? $result_AssetList->fetch() : array();
            
            return $AssetList;
	}
        
        static function GetDashAssetMainList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_assets_history 
                FROM maintenance_history m
                JOIN assets a ON (a.id = m.assets_id)
                WHERE a.is_deleted='N'";
            
            $AssetList = array();
            $result_AssetList = $dbh->query($sql);
            $AssetList = $result_AssetList ? $result_AssetList->fetch() : array();
            
            return $AssetList;
	}
        
        static function GetDashAssetStatusList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_status 
                FROM asset_status
                WHERE is_deleted='N'";
            
            $AssetList = array();
            $result_AssetList = $dbh->query($sql);
            $AssetList = $result_AssetList ? $result_AssetList->fetch() : array();
            
            return $AssetList;
	}
        
        static function GetStatusList($indexbyid=false){
            global $dbh;

            $sql="SELECT *, status as status_name FROM asset_status WHERE is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql);
            if ($result) {
                foreach($result as $row){
                        if($indexbyid){
                                $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                        }else{
                                $ManufacturerList[]=Asset::RowToObject($row);
                        }
                }
            }
            
            return $ManufacturerList;
	}
        
        static function GetStatusListRows($indexbyid=false){
            global $dbh;

            $sql="SELECT s.*, status as status_name, (SELECT count(id) as total_assets FROM assets a WHERE a.is_deleted='N' AND a.status_id=s.id) as total_assets
                FROM asset_status s 
                WHERE s.is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Asset::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        function GetStatusByID(){
            $this->MakeSafe();

            $sql="SELECT *, status as status_name FROM asset_status WHERE is_deleted='N' AND id=$this->PortID;";
            
            if($row=$this->query($sql)->fetch()){
                    $ManufacturerList[0]=Asset::RowToObject($row);
            }else{
                    $ManufacturerList[0]=Asset::RowToObject($row);
            }
            return $ManufacturerList;
	}
        
        static function GetSupplierList($indexbyid=false){
            global $dbh;

            $sql="SELECT *, name as supplier_name FROM asset_supplier ORDER BY id ASC;";

            $ManufacturerList = array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Asset::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            if($this->Purchase_date !="")
            {
                $purchase_date = date('Y-m-d',strtotime($this->Purchase_date));
            }
            
            if($this->First_main_date !="")
            {
                $first_main_date = date('Y-m-d',strtotime($this->First_main_date));
                $next_main_date = date('Y-m-d', strtotime("+".$this->Maintenance." months", strtotime($this->First_main_date)));
                $last_main_date = date('Y-m-d',strtotime($this->First_main_date));
            }
            
            if($this->Requestable != "")
            {
                $requestable = $this->Requestable;
            } else {
                $requestable = "N";
            }
            $created_at = date('Y-m-d');
            
            $sql="INSERT INTO assets SET name=\"$this->Name\", asset_tag=\"$this->Asset_tag\", model_id=\"$this->Model\", status_id=\"$this->Status\", serial_no=\"$this->Serial_no\", purchase_date=\"$purchase_date\", first_main_date=\"$first_main_date\", next_main_date=\"$next_main_date\", last_main_date=\"$last_main_date\", supplier_id=\"$this->Supplier\", order_no=\"$this->Order_no\", purchase_cost=\"$this->Purchase_cost\", warranty=\"$this->Warranty\", maintenance=\"$this->Maintenance\", note=\"$this->Notes\", location_id=\"$this->Location\", room_id=\"$this->Room\", rack_id=\"$this->Rack\", requestable='".$requestable."', asset_image=\"$this->Asset_img\", created='".$created_at."';";
            
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
        
        function CreateStatus($params = array()){
            global $dbh;

            $this->MakeSafe();
            
            $this->Name = $params['status_name'];
            $this->Status_type = $params['status_type'];
            
            $sql="INSERT INTO asset_status SET status=\"$this->Name\", status_type=\"$this->Status_type\"";
            
            if(!$dbh->exec($sql)){
                    error_log( "SQL Error: " . $sql );
                    return false;
            } else {
                    $this->PortID=$dbh->lastInsertID();
                    (class_exists('LogActions'))?LogActions::LogThis($this):'';
                    $this->MakeDisplay();
                    return true;
            }
	}
        
        function UpdateStatus($params = array()){
            global $dbh;

            $this->MakeSafe();
            
            $this->Name = $params['status_name'];
            $this->Status_type = $params['status_type'];
            $status_id = $params['status_id'];
            
            $sql="UPDATE asset_status SET status=\"$this->Name\", status_type=\"$this->Status_type\" WHERE id=".$status_id."";
            
            if(!$dbh->exec($sql)){
                    error_log( "SQL Error: " . $sql );
                    return false;
            } else {
                    $this->PortID=$dbh->lastInsertID();
                    (class_exists('LogActions'))?LogActions::LogThis($this):'';
                    $this->MakeDisplay();
                    return true;
            }
	}
        
        function DeleteStatus($TransferTo=null){
            $this->MakeSafe();

            $sql="UPDATE asset_status SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}
        
        function CreateSupplier($params = array()){
            global $dbh;

            $this->MakeSafe();
            
            $this->Name = $params['supplier_name'];
            
            $sql="INSERT INTO asset_supplier SET name=\"$this->Name\"";
            
            if(!$dbh->exec($sql)){
                error_log( "SQL Error: " . $sql );
                return false;
            } else {
                $this->PortID = $dbh->lastInsertID();
                (class_exists('LogActions'))?LogActions::LogThis($this):'';
                $this->MakeDisplay();
                return true;
            }
	}

	function DeleteObject($TransferTo=null){
            $this->MakeSafe();

            $sql="UPDATE assets SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            global $dbh;
            $this->MakeSafe();
            
            $sql="SELECT * FROM assets WHERE is_deleted='N' AND id={$this->PortID};";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                $ManufacturerList[]=Asset::RowToObject($row);
            } }
            
            if($this->Purchase_date !="")
            {
                $purchase_date = date('Y-m-d',strtotime($this->Purchase_date));
            }
            if($this->Maintenance !="")
            {
                //$first_main_date = date('Y-m-d',strtotime($this->First_main_date));
                $last_main_date = $ManufacturerList[0]->Last_main_date;
                $next_main_date = date('Y-m-d', strtotime("+".$this->Maintenance." months", strtotime($last_main_date)));
            }
            
            if($this->Requestable != "")
            {
                $requestable = $this->Requestable;
            } else {
                $requestable = "N";
            }
            $created_at = date('Y-m-d');
            
            $sql="UPDATE assets SET name=\"$this->Name\", asset_tag=\"$this->Asset_tag\", model_id=\"$this->Model\", status_id=\"$this->Status\", serial_no=\"$this->Serial_no\", purchase_date=\"$purchase_date\", next_main_date=\"$next_main_date\", last_main_date=\"$last_main_date\", supplier_id=\"$this->Supplier\", order_no=\"$this->Order_no\", purchase_cost=\"$this->Purchase_cost\", warranty=\"$this->Warranty\", maintenance=\"$this->Maintenance\", note=\"$this->Notes\", location_id=\"$this->Location\", room_id=\"$this->Room\", rack_id=\"$this->Rack\", requestable='".$requestable."', asset_image=\"$this->Asset_img\", last_updated='".$created_at."' WHERE id=$this->PortID;";
            
            $old=new Asset();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
        
        function UpdateAssetObject(){
            global $dbh;
            $error = array();
            $parm = array();
            $fields = array();
            $response = array();
            $message = "";

            parse_str($_REQUEST['form_customer'], $parm);
            
            $edit_data_id = $parm['edit_data_id'];

            $name = $parm['name'];
            $asset_tag = $parm['asset_tag'];
            $status = $parm['status'];
            $created_at = date('Y-m-d');
            
            $sql="SELECT * FROM assets WHERE is_deleted='N' AND is_simulation='N' AND id={$edit_data_id};";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                $ManufacturerList[]=Asset::RowToObject($row);
            } }
            $last_main_date = $ManufacturerList[0]->Last_main_date;
            $next_main_date = $ManufacturerList[0]->Next_main_date;
            if($status =="completed")
            {
                $last_main_date = $ManufacturerList[0]->Next_main_date;
                $next_main_date = date('Y-m-d', strtotime("+".$ManufacturerList[0]->Maintenance." months", strtotime($last_main_date)));
                $maintenance = $ManufacturerList[0]->Maintenance;
                $sql="INSERT INTO maintenance_history SET assets_id={$edit_data_id}, status=\"$status\", maintenance_date=\"$last_main_date\", maintenance_months=\"$maintenance\", last_updated='".$created_at."'";
                $this->query($sql);
                $status = "pendding";
            }
            
            try {
                if ($edit_data_id == '') {
                    $sql="INSERT INTO assets SET name=\"$name\", asset_tag=\"$asset_tag\", maintenance_status=\"$status\", last_main_date=\"$last_main_date\", next_main_date=\"$next_main_date\", last_updated='".$created_at."'";

                    $response['status'] = "success";
                } else {
                    $sql="UPDATE assets SET name=\"$name\", asset_tag=\"$asset_tag\", maintenance_status=\"$status\", last_main_date=\"$last_main_date\", next_main_date=\"$next_main_date\", last_updated='".$created_at."' WHERE id={$edit_data_id}";
                    
                    $this->query($sql);
                    
                    $response['status'] = "success";
                    $response['action'] = "update";
                }
            } catch (Exception $exc) {
                $message = $exc->getMessage();
            }

            $old=new Asset();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
         
            return $response;
	}
        
        static function GetMainHistoryRow($filter){
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
            if(isset($filter['assets'])){
                $incr .= " AND a.id =".$filter['assets'];
            }
            
            
            $sql="SELECT m.*, a.name, a.first_main_date
                FROM maintenance_history m
                JOIN assets a ON(a.id=m.assets_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Asset::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
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
            if(isset($filter['assets'])){
                $incr .= " AND a.id =".$filter['assets'];
            }
            if(isset($filter['status'])){
                $incr .= " AND a.status ='".$filter['status']."'";
            }
            if(isset($filter['rack'])){
                $incr .= " AND a.rack_id =".$filter['rack'];
            }
            if(isset($filter['device'])){
                $incr .= " AND a.device_id =".$filter['device'];
            }
            if(isset($filter['company'])){
                $incr .= " AND a.company ='".$filter['company']."'";
            }
            
            $sql="SELECT a.*, l.name as location_name, m.name as model_name, s.name as supplier_name, ss.status as status_name, ss.status_type as status_type, r1.name as room_name, r2.name as rack_name
                FROM assets a
                LEFT JOIN location l ON(l.id=a.location_id)
                LEFT JOIN room r1 ON(r1.id=a.room_id)
                LEFT JOIN rack r2 ON(r2.id=a.rack_id)
                LEFT JOIN asset_model m ON(m.id=a.model_id)
                LEFT JOIN asset_supplier s ON(s.id=a.supplier_id)
                LEFT JOIN asset_status ss ON(ss.id=a.status_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Asset::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Asset::RowToObject($row);
                    }
            } }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Assets_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Location".$tab_sep."Room".$tab_sep."Rack".$tab_sep."Asset Tag".$tab_sep."Model".$tab_sep."Supplier".$tab_sep."Status".$tab_sep."Purchase Cost".$tab_sep."Purchase Date".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Location_name'].$tab_sep.$row['Room_name'].$tab_sep.$row['Rack_name'].$tab_sep.$row['Asset_tag'].$tab_sep.$row['Model_name'].$tab_sep.$row['Supplier_name'].$tab_sep.$row['Status_name'].$tab_sep.$row['Purchase_cost'].$tab_sep.date("m/d/Y",strtotime($row['Purchase_date'])).$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>