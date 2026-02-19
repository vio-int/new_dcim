<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class AssetSimulation {
        
	var $PortID;
	var $Name;
        var $Simulation_type;
        var $Site;
        var $Room;
        var $Rack;
        var $Space;
	var $Power;
        var $Tag;
	
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
            $m=new AssetSimulation();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Room_name=$row["room_name"];
            $m->Rack_name=$row["rack_name"];
            $m->Model_name=$row["model_name"];
            $m->Supplier_name=$row["supplier_name"];
            $m->Serail_no=$row["serial_no"];
            $m->Asset_tag=$row["asset_tag"];
            $m->Purchase_date=$row["purchase_date"];
            $m->Order_no=$row["order_no"];
            $m->Asset_image= $row["asset_image"]; 
            $m->Is_simulation = $row["is_simulation"]; 
            
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            
            $m->MakeDisplay();

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
	
	// Wrapper to make this method like the other classes
	function GetObject(){
            return $this->GetOrderByID();
	}
	
	function GetOrderByID(){
            $this->MakeSafe();

            $sql="SELECT * FROM capacity WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetSimulation::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM capacity WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetSimulation::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetAssetSimulationList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM capacity WHERE is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSimulation::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSimulation::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        function GetCapacityDetails($filter){
            global $dbh;
            $incr1 = "";
            $incr2 = "";
            $incr3 = "";
            $capacity_arr = array();
            if($filter["location_id"] !=""){
                $incr1 .= " AND l.id=".$filter['location_id'];
            }
            if($filter["room_id"] !=""){
                $incr2 .= " AND r.id=".$filter['room_id'];
            }
            if($filter["rack_id"] !=""){
                $incr3 .= " AND r.id=".$filter['rack_id'];
            }
            $location_sql = "SELECT  
                (SELECT SUM(IFNULL(height, 0)) FROM device d WHERE d.site_id=l.id AND d.is_deleted='N') as total_used_space, 
                (SELECT SUM(IFNULL(height, 0)) FROM rack r WHERE r.site_id=l.id) as total_space, 
                (SELECT SUM(IFNULL(wattage, 0)) FROM device d2 WHERE d2.site_id=l.id AND d2.is_deleted='N') as total_used_power,
                (SELECT SUM(IFNULL(max_kw, 0)) FROM rack r2 WHERE r2.site_id=l.id) as total_power
                FROM location l 
                WHERE l.is_deleted='N' ".$incr1."";
            $SiteList = array();
            if($incr1 != "")
            {
                $result_SiteList = $dbh->query($location_sql);
                $SiteList = $result_SiteList ? $result_SiteList->fetch() : array();
            }
            
            $room_sql = "SELECT 
                (SELECT SUM(IFNULL(d.height, 0)) FROM device d WHERE d.room_id=r.id AND d.is_deleted='N') as total_used_space,
                (SELECT SUM(IFNULL(r3.height, 0)) FROM rack r3 WHERE r3.room_id=r.id) as total_space,
                (SELECT SUM(IFNULL(d2.wattage, 0)) FROM device d2 WHERE d2.room_id=r.id AND d2.is_deleted='N') as total_used_power,
                (SELECT SUM(IFNULL(r4.max_kw, 0)) FROM rack r4 WHERE r4.room_id=r.id) as total_power
                FROM room r 
                WHERE r.is_deleted='N' ".$incr2."";
            $RoomList = array();
            if($incr2 != "")
            {
                $result_RoomList = $dbh->query($room_sql);
                $RoomList = $result_RoomList ? $result_RoomList->fetch() : array();
            }
            $rack_sql = "SELECT 
                (SELECT SUM(IFNULL(d.height, 0)) FROM device d WHERE d.rack_id=r.id AND d.is_deleted='N') as total_used_space,
                r.height as total_space,
                (SELECT SUM(IFNULL(d.wattage, 0)) FROM device d WHERE d.rack_id=r.id AND d.is_deleted='N') as total_used_power,
                r.max_kw as total_power
                FROM rack r
                WHERE r.is_deleted='N' ".$incr3."";
            
            $RackList = array();
            if($incr3 != "")
            {
                $result_RackList = $dbh->query($rack_sql);
                $RackList = $result_RackList ? $result_RackList->fetch() : array();
            }
            
            $capacity_arr['site'] = $SiteList['total_space'] - $SiteList['total_used_space'];
            $capacity_arr['room'] = $RoomList['total_space'] - $RoomList['total_used_space'];
            $capacity_arr['rack'] = $RackList['total_space'] - $RackList['total_used_space'];
            $capacity_arr['site_power'] = $SiteList['total_power'] - $SiteList['total_used_power'];
            $capacity_arr['room_power'] = $RoomList['total_power'] - $RoomList['total_used_power'];
            $capacity_arr['rack_power'] = $RackList['total_power'] - $RackList['total_used_power'];
            
            return $capacity_arr;
	}
        function GetAssetList($rack_id){
            global $dbh;
           
            $sql="SELECT a.*, r.name as room_name, r2.name as rack_name, m.name as model_name, s.name as supplier_name
                FROM assets a
                LEFT JOIN room r ON (r.id=a.room_id)
                LEFT JOIN rack r2 ON (r2.id=a.rack_id)
                LEFT JOIN asset_model m ON (m.id=a.model_id)
                LEFT JOIN asset_supplier s ON (s.id=a.supplier_id)
                WHERE a.is_deleted='N' AND a.is_simulation='N' AND a.rack_id={$rack_id}";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSimulation::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSimulation::RowToObject($row);
                    }
            }
            
            return $ManufacturerList; 
        }
        
        function GetRackAssetList($rack_id){
            global $dbh;
           
            $sql="SELECT a.*, r.name as room_name, r2.name as rack_name, m.name as model_name, s.name as supplier_name
                FROM assets a
                LEFT JOIN room r ON (r.id=a.room_id)
                LEFT JOIN rack r2 ON (a.rack_id=r2.id)
                LEFT JOIN asset_model m ON (a.model_id=m.id)
                LEFT JOIN asset_supplier s ON (a.supplier_id=s.id)
                WHERE a.is_deleted='N' AND a.rack_id={$rack_id}";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSimulation::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSimulation::RowToObject($row);
                    }
            }
            
            return $ManufacturerList; 
        }
        
        // QUERY FOR DASHBOARD COUNTER
	function GetDashAssetSimulation(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_simulation 
                FROM device WHERE is_simulation='Y'";
            
            $ConsoleConnList = array();
            $result_ConsoleConnList = $dbh->query($sql);
            $ConsoleConnList = $result_ConsoleConnList ? $result_ConsoleConnList->fetch() : array();
            
            return $ConsoleConnList;
	}
        
        function GetRackDetails($rack_id){
            global $dbh;
            
            $sql="SELECT * FROM rack WHERE id={$rack_id}";
            
            $GenrList = array();
            $result_GenrList = $dbh->query($sql);
            $GenrList = $result_GenrList ? $result_GenrList->fetch() : array();
            
            return $GenrList; 
        }
        function GetAssetDetails($device_id){
            global $dbh;
            
            $sql="SELECT * FROM assets WHERE id='{$device_id}'";
            
            $GenrList = array();
            $result_GenrList = $dbh->query($sql);
            $GenrList = $result_GenrList ? $result_GenrList->fetch() : array();
            
            return $GenrList; 
        }
        
        function CreateObject($params){
            global $dbh;
            
            if(!empty($params['new_rack_ids'])){
                
                foreach($params['new_rack_ids'] as $key=>$val){
                    if($val != "")
                    {   
                        $created = date("Y-m-d");
                        $site_id = $params['site'];
                        $room_id = $params['room'];
                        $rack_id = $params['rack'];
                        $position = $params['new_rack_pos'][$key];
                        $device_arr = $this->GetAssetDetails($val);
                        $sql="INSERT INTO assets SET name='".$device_arr['name']."', location_id=".$site_id.", room_id=".$room_id.", rack_id=".$rack_id.", model_id='".$device_arr['model_id']."', supplier_id=".$device_arr['supplier_id'].", status_id='".$device_arr['status_id']."', serial_no='".$device_arr['serial_no']."', asset_tag='".$device_arr['asset_tag']."', purchase_date='".$device_arr['purchase_date']."', order_no=".$device_arr['order_no'].", purchase_cost=".$device_arr['purchase_cost'].", warranty='".$device_arr['warranty']."', note='".$device_arr['note']."', requestable='".$device_arr['requestable']."', asset_image='".$device_arr['asset_image']."', is_simulation='Y', is_deleted='N', created='".$created."';";
                        
                        if(!$dbh->exec($sql)){
                            error_log( "SQL Error: " . $sql );
                            //return false;
                        }else{
                            $this->PortID=$dbh->lastInsertID();
                            (class_exists('LogActions'))?LogActions::LogThis($this):'';
                            //$this->MakeDisplay();
                            //return true;
                        }
                    }    
                }
            }
	}
        
        function CreateDeviceObject($params){
            $this->MakeSafe();
            
            $sql="UPDATE assets SET is_simulation='N' WHERE is_deleted='N' AND rack_id={$params['rack']};";
            
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}
        
	function DeleteObject($params){
            $this->MakeSafe();
            
            $sql="UPDATE assets SET is_deleted='Y' WHERE id={$params['simulation_id']};";
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}
}
?>
