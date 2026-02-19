<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Simulation {
        
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
            $m=new Simulation();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Room_name=$row["room_name"];
            $m->Rack_name=$row["rack_name"];
            $m->Device_name=$row["device_name"];
            $m->Total_room=$row["total_room"];
            $m->Total_rack=$row["total_rack"];
            $m->Total_space= $row["total_space"]; 
            $m->Total_used_space= $row["total_used_space"]; 
            $m->Total_free_space= ($row["total_space"] - $row["total_used_space"]); 
            $m->Total_power=$row["total_power"];
            $m->Total_used_power=$row["total_used_power"];
            $m->Total_free_power= ($row["total_power"] - $row["total_used_power"]);
            $m->Total_device=$row["total_device"];
            $m->Device_height=$row["device_height"];
            $m->Device_power=$row["device_wattage"];
            $m->Is_simulation=$row["is_simulation"];
            $m->Position=$row["position"];
            $m->Front_picture=$row["front_picture"];
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
                    foreach(Simulation::RowToObject($row) as $prop => $value){
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
                    foreach(Simulation::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetSimulationList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM capacity WHERE is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Simulation::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Simulation::RowToObject($row);
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
        function GetDeviceList($rack_id){
            global $dbh;
           
            $sql="SELECT id, name as device_name,height as device_height, wattage as device_wattage, is_simulation, position, front_picture
                FROM device WHERE is_deleted='N' AND rack_id={$rack_id}";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Simulation::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Simulation::RowToObject($row);
                    }
            }
            
            return $ManufacturerList; 
        }
        
        // QUERY FOR DASHBOARD COUNTER
	function GetDashSimulation(){
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
        function GetDeviceDetails($device_name){
            global $dbh;
            
            $sql="SELECT * FROM device WHERE name='{$device_name}'";
            
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
                        $device_arr = $this->GetDeviceDetails($val);
                        $sql="INSERT INTO device SET name='".$device_arr['name']."', site_id=".$site_id.", room_id=".$room_id.", rack_id=".$rack_id.", device_role='".$device_arr['device_role']."', manufacture_id=".$device_arr['manufacture_id'].", device_type='".$device_arr['device_type']."', serial_no='".$device_arr['serial_no']."', asset_tag='".$device_arr['asset_tag']."', height=".$device_arr['height'].", weight=".$device_arr['weight'].", wattage=".$device_arr['wattage'].", no_power=".$device_arr['no_power'].", no_port=".$device_arr['no_port'].", snmp_version=".$device_arr['snmp_version'].", front_picture='".$device_arr['front_picture']."', rear_picture='".$device_arr['rear_picture']."', rack_face='".$device_arr['rack_face']."', position=".$position.", status='".$device_arr['status']."', platform='".$device_arr['platform']."', tag='".$device_arr['tag']."', comment='".$device_arr['comment']."', is_simulation='Y', created='".$created."';";
                        
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
            
            $sql="UPDATE device SET is_simulation='N' WHERE is_deleted='N' AND rack_id={$params['rack']};";
            
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}
        
	function DeleteObject($params){
            $this->MakeSafe();
            
            $sql="UPDATE device SET is_deleted='Y' WHERE id={$params['simulation_id']};";
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}
}
?>
