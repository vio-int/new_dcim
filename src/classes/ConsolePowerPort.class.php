<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class ConsolePowerPort {
        
	var $PortID;
	var $Name;
        var $Port_type;
        var $Device;
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
            $m=new ConsolePowerPort();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Port_type=$row["type"];
            $m->Device=$row["device_id"];
            $m->Tag=$row["tag"];
            $m->Device_name=$row["device_name"];
            
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
            return $this->GetOconsole_servererByID();
	}
	
	function GetOrderByID(){
            $this->MakeSafe();

            $sql="SELECT * FROM console_power_port WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConsolePowerPort::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM console_power_port WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConsolePowerPort::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetConsoleConnList($type){
            global $dbh;

            $sql="SELECT * FROM console_power_port WHERE is_deleted='N' AND type='{$type}' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=ConsolePowerPort::RowToObject($row);
                    }else{
                            $ManufacturerList[]=ConsolePowerPort::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        function GetPortList(){
            global $dbh;

            $sql="SELECT * FROM console_power_port WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=ConsolePowerPort::RowToObject($row);
                    }else{
                            $ManufacturerList[]=ConsolePowerPort::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
	
        // Function for list page
        static function GetConsolePowerPortListRows($filter){
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
            if(isset($filter['device'])){
                $incr .= " AND v.device_id =".$filter['device'];
            }

            $sql="SELECT v.*, d.name as device_name 
                FROM console_power_port v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= ConsolePowerPort::RowToObject($row);
                }else{
                    $RowList[]= ConsolePowerPort::RowToObject($row);
                }
            } }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOAPort_type COUNTER
	static function GetDashConsoleList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_console 
                FROM console_power_port v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N'";
            
            $ConsolePowerPortList = array();
            $result_ConsolePowerPortList = $dbh->query($sql);
            $ConsolePowerPortList = $result_ConsolePowerPortList ? $result_ConsolePowerPortList->fetch() : array();
            
            return $ConsolePowerPortList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="INSERT INTO console_power_port SET name=\"$this->Name\", type=\"$this->Port_type\", tag=\"$this->Tag\", device_id=\"$this->Device\", created='".$created."';";
            
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

            $sql="UPDATE console_power_port SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="UPDATE console_power_port SET name=\"$this->Name\", type=\"$this->Port_type\", tag=\"$this->Tag\", device_id=\"$this->Device\", last_updated='".$created."' WHERE id=$this->PortID;";
            
            $old=new ConsolePowerPort();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
