<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Service {
        
	var $PortID;
	var $Name;
        var $VM;
        var $Port;
        var $Port_type;
        var $IP_address;
        var $Description;
        var $Tag;
        var $Device;
	
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
            $m=new Service();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->VM=$row["virtual_machine_id"];
            $m->Port=$row["port"];
            $m->Port_type=$row["protocol"];
            $m->IP_address=$row["ip_address"];
            $m->Description=$row["description"];
            $m->Tag=$row["tag"];
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            $m->Machine_name = isset($row["machine_name"])?$row["machine_name"]:'';
            $m->Device = $row["device_id"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Service();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->VM=$row["virtual_machine_id"];
            $m->Port=$row["port"];
            $m->Port_type=$row["protocol"];
            $m->IP_address=$row["ip_address"];
            $m->Description=$row["description"];
            $m->Tag=$row["tag"];
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            $m->Machine_name = $row["machine_name"];
            $m->Device = $row["device_id"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->VM);
            unset($m->Device);
            
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
                
		$sql="SELECT s.*,v.name as machine_name
                FROM ipam_service s
                LEFT JOIN virtual_machine v ON(v.id=s.virtual_machine_id)
                WHERE s.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["serviceID"]]=Service::RowToSearchObject($row);
                    }else{
                        $dcList[]=Service::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_service WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(Service::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_service WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Service::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetServiceList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_service WHERE is_deleted='N' AND virtual_machine_id > 0 ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Service::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Service::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetDeviceServiceList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_service WHERE is_deleted='N' AND device_id > 0 ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Service::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Service::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // Function for list page
        static function GetServiceListRows($filter){
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
            if(isset($filter['name'])){
                $incr .= " AND s.id =".$filter['name'];
            }
            if(isset($filter['machine'])){
                $incr .= " AND s.virtual_machine_id =".$filter['machine'];
            }
            if(isset($filter['device'])){
                $incr .= " AND s.device_id =".$filter['device'];
            } else {
                $incr .= " AND s.virtual_machine_id > 0";
            }
            if(isset($filter['port_type'])){
                $incr .= " AND s.protocol ='".$filter['port_type']."'";
            }

            $sql="SELECT s.*,v.name as machine_name
                FROM ipam_service s
                LEFT JOIN virtual_machine v ON(v.id=s.virtual_machine_id)
                WHERE s.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Service::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Service::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashServiceList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_service
                FROM ipam_service s
                LEFT JOIN virtual_machine v ON(v.id=s.virtual_machine_id)
                WHERE s.is_deleted='N';";
            
            $ServiceList = array();
            $result_ServiceList = $dbh->query($sql);
            $ServiceList = $result_ServiceList ? $result_ServiceList->fetch() : array();
            
            return $ServiceList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created_at = date('Y-m-d');
            
            $sql="INSERT INTO ipam_service SET name=\"$this->Name\", virtual_machine_id=\"$this->VM\", protocol=\"$this->Port_type\", port=\"$this->Port\", ip_address=\"$this->IP_address\", description=\"$this->Description\", tag=\"$this->Tag\", created='".$created_at."';";
            
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
        
        function CreateDeviceServiceObject(){
            global $dbh;

            $this->MakeSafe();
            $created_at = date('Y-m-d');
            
            $sql="INSERT INTO ipam_service SET name=\"$this->Name\", device_id=\"$this->Device\", protocol=\"$this->Port_type\", port=\"$this->Port\", ip_address=\"$this->IP_address\", description=\"$this->Description\", tag=\"$this->Tag\", created='".$created_at."';";
            
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

            $sql="UPDATE ipam_service SET is_deleted='Y' WHERE id=$this->PortID;";
            
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            
            $last_updated = date('Y-m-d');
            
            $sql="UPDATE ipam_service SET name=\"$this->Name\", virtual_machine_id=\"$this->VM\", protocol=\"$this->Port_type\", port=\"$this->Port\", ip_address=\"$this->IP_address\", description=\"$this->Description\", tag=\"$this->Tag\", last_updated='".$last_updated."' WHERE id=$this->PortID;";
            
            $old=new Service();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
        
        function UpdateDeviceServiceObject(){
            $this->MakeSafe();
            
            $last_updated = date('Y-m-d');
            
            $sql="UPDATE ipam_service SET name=\"$this->Name\", device_id=\"$this->Device\", protocol=\"$this->Port_type\", port=\"$this->Port\", ip_address=\"$this->IP_address\", description=\"$this->Description\", tag=\"$this->Tag\", last_updated='".$last_updated."' WHERE id=$this->PortID;";
            
            $old=new Service();
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
            if(isset($filter['name'])){
                $incr .= " AND s.id =".$filter['name'];
            }
            if(isset($filter['machine'])){
                $incr .= " AND s.virtual_machine_id =".$filter['machine'];
            }
            if(isset($filter['port_type'])){
                $incr .= " AND s.protocol ='".$filter['port_type']."'";
            }

            $sql="SELECT s.*,v.name as machine_name
                FROM ipam_service s
                LEFT JOIN virtual_machine v ON(v.id=s.virtual_machine_id)
                WHERE s.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Service::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Service::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Services_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";
            $flag = false;
            foreach($result as $row) {
                
                if(!$flag) {
                    // display field/column names as first row
                    $header_html .= "Name".$tab_sep."Parent".$tab_sep."Protocol".$tab_sep."Port Number".$tab_sep."Description".$line_sep;
                    $flag = true;
                }
                    $header_html .= $row['Name'].$tab_sep.$row['Machine_name'].$tab_sep.$row['Port_type'].$tab_sep.$row['Port'].$tab_sep.$row['Description'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
