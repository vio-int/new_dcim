<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Capacity {
        
	var $PortID;
	var $Name;
        var $Capacity_type;
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
            $m=new Capacity();
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
            $m->Total_weight=$row["total_weight"];
            $m->Total_used_weight=$row["total_used_weight"];
            $m->Total_free_weight= ($row["total_weight"] - $row["total_used_weight"]);
            $m->Total_device=$row["total_device"];
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
                    foreach(Capacity::RowToObject($row) as $prop => $value){
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
                    foreach(Capacity::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetCapacityList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM capacity WHERE is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Capacity::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Capacity::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetCapacityOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['name'])){
                $incr .= " AND a.id =".$filter['name'];
            }
            
            $sql="SELECT a.*,l.name as location_name, r.name as rack_name, r2.name as room_name
                FROM capacity a 
                LEFT JOIN location l ON(l.id=a.site_id)
                LEFT JOIN rack r ON(r.id=a.rack_id) 
                LEFT JOIN room r2 ON(r2.id=a.room_id)  
                WHERE a.is_deleted='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= Capacity::RowToObject($row);
                }else{
                    $RowList[]= Capacity::RowToObject($row);
                }
            }
            return $RowList;
	}
        
        // Function for list page
        static function GetLocationCapacityRows($filter){
            global $dbh;
            
            $limit = 15; 
            if (isset($_GET["page"])) {
                $page = $_GET["page"]; 
            } else { 
                $page=1;
            } 
            $start_from = ($page-1) * $limit; 
            
            $incr = "";
            $sort_on = "created";
            $sort_by = "Asc";
            
            if(isset($filter['location'])){
                $incr .= " AND l.id =".$filter['location'];
            }

            $sql="SELECT l.*, 
                (SELECT COUNT(*) FROM room r WHERE r.location_id=l.id) as total_room, 
                (SELECT SUM(IFNULL(height, 0)) FROM device d WHERE d.site_id=l.id AND d.is_deleted='N' AND d.is_simulation='N') as total_used_space, 
                (SELECT SUM(IFNULL(height, 0)) FROM rack r WHERE r.site_id=l.id AND r.is_deleted='N' AND r.is_simulation='N') as total_space, 
                (SELECT SUM(IFNULL(wattage, 0)) FROM device d2 WHERE d2.site_id=l.id AND d2.is_deleted='N' AND d2.is_simulation='N') as total_used_power,
                (SELECT SUM(IFNULL(max_kw, 0)) FROM rack r2 WHERE r2.site_id=l.id AND r2.is_deleted='N' AND r2.is_simulation='N') as total_power
                FROM location l 
                WHERE l.is_deleted='N' ".$incr." GROUP BY l.id ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]= Capacity::RowToObject($row);
                }else{
                    $ManufacturerList[]=Capacity::RowToObject($row);
                }
            }
            
            return $ManufacturerList;
	}
        
        static function GetRoomCapacityRows($filter){
            global $dbh;
            
            $limit = 15; 
            if (isset($_GET["page"])) {
                $page = $_GET["page"]; 
            } else { 
                $page=1;
            } 
            $start_from = ($page-1) * $limit; 
            
            $incr = "";
            $sort_on = "created";
            $sort_by = "Asc";
            if(isset($filter['location'])){
                $incr .= " AND r.location_id =".$filter['location'];
            }

            $sql="SELECT r.id, r.name as room_name, 
                (SELECT COUNT(*) FROM rack r2 WHERE r2.room_id=r.id AND r2.is_deleted='N' AND r2.is_simulation='N') as total_rack, 
                (SELECT SUM(IFNULL(d.height, 0)) FROM device d WHERE d.room_id=r.id AND d.is_deleted='N' AND d.is_simulation='N') as total_used_space,
                (SELECT SUM(IFNULL(r3.height, 0)) FROM rack r3 WHERE r3.room_id=r.id AND r3.is_deleted='N' AND r3.is_simulation='N') as total_space,
                (SELECT SUM(IFNULL(d2.wattage, 0)) FROM device d2 WHERE d2.room_id=r.id AND d2.is_deleted='N' AND d2.is_simulation='N') as total_used_power,
                (SELECT SUM(IFNULL(r4.max_kw, 0)) FROM rack r4 WHERE r4.room_id=r.id AND r4.is_deleted='N' AND r4.is_simulation='N') as total_power
                FROM room r 
                WHERE r.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]= Capacity::RowToObject($row);
                }else{
                    $ManufacturerList[]=Capacity::RowToObject($row);
                }
            }
            return $ManufacturerList;
	}
        
        static function GetRackCapacityRowOne($filter){
            global $dbh;
            
            $limit = 15; 
            if (isset($_GET["page"])) {
                $page = $_GET["page"]; 
            } else { 
                $page=1;
            } 
            $start_from = ($page-1) * $limit; 
            
            $incr = "";
            $sort_on = "created";
            $sort_by = "Asc";
            if(isset($filter['room_id'])){
                $incr .= " AND r.id =".$filter['room_id'];
            }

            $sql="SELECT r.id, r.name as rack_name,
                (SELECT COUNT(*) FROM device d2 WHERE d2.rack_id=r.id AND d2.is_deleted='N' AND d2.is_simulation='N') as total_device, 
                (SELECT SUM(IFNULL(d.height, 0)) FROM device d WHERE d.rack_id=r.id AND d.is_deleted='N' AND d.is_simulation='N') as total_used_space,
                r.height as total_space,
                (SELECT SUM(IFNULL(d.wattage, 0)) FROM device d WHERE d.rack_id=r.id AND d.is_deleted='N' AND d.is_simulation='N') as total_used_power,
                r.max_kw as total_power,
                (SELECT SUM(IFNULL(d.weight, 0)) FROM device d WHERE d.rack_id=r.id AND d.is_deleted='N' AND d.is_simulation='N') as total_used_weight,
                r.max_weight as total_weight
                FROM rack r
                WHERE r.is_deleted='N' AND r.is_simulation='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]= Capacity::RowToObject($row);
                }else{
                    $ManufacturerList[]=Capacity::RowToObject($row);
                }
            }
            return $ManufacturerList;
	}
        
        static function GetRackCapacityRows($filter){
            global $dbh;
            
            $limit = 15; 
            if (isset($_GET["page"])) {
                $page = $_GET["page"]; 
            } else { 
                $page=1;
            } 
            $start_from = ($page-1) * $limit; 
            
            $incr = "";
            $sort_on = "created";
            $sort_by = "Asc";
            if(isset($filter['room'])){
                $incr .= " AND r.room_id =".$filter['room'];
            }

            $sql="SELECT r.id, r.name as rack_name,
                (SELECT COUNT(*) FROM device d2 WHERE d2.rack_id=r.id AND d2.is_deleted='N' AND d2.is_simulation='N') as total_device, 
                (SELECT SUM(IFNULL(d.height, 0)) FROM device d WHERE d.rack_id=r.id AND d.is_deleted='N' AND d.is_simulation='N') as total_used_space,
                r.height as total_space,
                (SELECT SUM(IFNULL(d.wattage, 0)) FROM device d WHERE d.rack_id=r.id AND d.is_deleted='N' AND d.is_simulation='N') as total_used_power,
                r.max_kw as total_power
                FROM rack r
                WHERE r.is_deleted='N' AND r.is_simulation='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]= Capacity::RowToObject($row);
                }else{
                    $ManufacturerList[]=Capacity::RowToObject($row);
                }
            }
            return $ManufacturerList;
	}
        
        static function GetDeviceCapacityRows($filter){
            global $dbh;
            
            $limit = 15; 
            if (isset($_GET["page"])) {
                $page = $_GET["page"]; 
            } else { 
                $page=1;
            } 
            $start_from = ($page-1) * $limit; 
            
            $incr = "";
            $sort_on = "created";
            $sort_by = "Asc";
            if(isset($filter['rack'])){
                $incr .= " AND d.rack_id =".$filter['rack'];
            }

            $sql="SELECT d.id, d.name as device_name, height as total_used_space, d.wattage as total_used_power
                FROM device d 
                WHERE d.is_deleted='N' AND d.is_simulation='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]= Capacity::RowToObject($row);
                }else{
                    $ManufacturerList[]=Capacity::RowToObject($row);
                }
            }
            
            return $ManufacturerList;
	}
        // QUERY FOR BREADCRUMBS 
        function GetPropertyName($type, $net_id){
            global $dbh;
            if($type == 'location'){
                $sql="SELECT name FROM location WHERE id=".$net_id;
            } else if($type == 'room'){
                $sql="SELECT name FROM room WHERE id=".$net_id;
            } else if($type == 'rack'){
                $sql="SELECT name FROM rack WHERE id=".$net_id;
            } else if($type == 'device'){
                $sql="SELECT name FROM device WHERE id=".$net_id;
            }
            
            $GenrList = array();
            $GenrList = $dbh->query($sql)->fetch();
            
            return $GenrList;
        }
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashCapacityList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_capacity
                FROM capacity a 
                LEFT JOIN location l ON(l.id=a.site_id)
                LEFT JOIN rack r ON(r.id=a.rack_id)
                LEFT JOIN room r2 ON(r2.id=a.room_id) 
                WHERE a.is_deleted='N'";
            
            $IpamIpaddressList = array();
            $IpamIpaddressList = $dbh->query($sql)->fetch();
            
            return $IpamIpaddressList;
	}
	
        // QUERY FOR PAGINATION COUNTER
	static function GetPageLocationCapacityList($fielter){
            global $dbh;
            $incr = "";
            
            if(isset($filter['location'])){
                $incr .= " AND l.id =".$filter['location'];
            }
            $sql="SELECT COUNT(*) as total_location
                FROM location l 
                WHERE l.is_deleted='N' ".$incr."";
            
            $LocationList = array();
            $LocationList = $dbh->query($sql)->fetch();
            
            return $LocationList;
	}
        static function GetPageRoomCapacityList($fielter){
            global $dbh;
            $incr = "";
            
            if(isset($filter['location'])){
                $incr .= " AND r.location_id =".$filter['location'];
            }
            $sql="SELECT COUNT(*) as total_room
                FROM room r 
                WHERE r.is_deleted='N' ".$incr."";
            
            $LocationList = array();
            $LocationList = $dbh->query($sql)->fetch();
            
            return $LocationList;
	}
        static function GetPageRackCapacityList($fielter){
            global $dbh;
            $incr = "";
            
            if(isset($filter['room'])){
                $incr .= " AND r.room_id =".$filter['room'];
            }
            $sql="SELECT COUNT(*) as total_rack
                FROM rack r
                WHERE r.is_deleted='N' AND r.is_simulation='N' ".$incr."";
            
            $LocationList = array();
            $LocationList = $dbh->query($sql)->fetch();
            
            return $LocationList;
	}
        static function GetPageDeviceCapacityList($fielter){
            global $dbh;
            $incr = "";
            
            if(isset($filter['rack'])){
                $incr .= " AND d.rack_id =".$filter['rack'];
            }
            $sql="SELECT COUNT(*) as total_device
                FROM device d 
                WHERE d.is_deleted='N' AND d.is_simulation='N' ".$incr."";
            
            $LocationList = array();
            $LocationList = $dbh->query($sql)->fetch();
            
            return $LocationList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="INSERT INTO capacity SET name=\"$this->Name\", capacity_type=\"$this->Capacity_type\", site_id=\"$this->Site\", room_id=\"$this->Room\", rack_id=\"$this->Rack\", space=\"$this->Space\", power=\"$this->Power\", tag=\"$this->Tag\", created='".$created."';";
            
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

            $sql="UPDATE capacity SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
            $rack_id = $this->Rack;
            $room_id = $this->Room;
            if($this->Capacity_type=="is_location"){
                $rack_id = 0;
                $room_id = 0;
            } else if($this->Capacity_type=="is_room"){
                $rack_id = 0;
            }
            $sql="UPDATE capacity SET name=\"$this->Name\", capacity_type=\"$this->Capacity_type\", site_id=\"$this->Site\", room_id=".$room_id.", rack_id=".$rack_id.", space=\"$this->Space\", power=\"$this->Power\", tag=\"$this->Tag\", last_updated='".$created."' WHERE id=$this->PortID;";
            
            $old=new Capacity();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
        
}
?>
