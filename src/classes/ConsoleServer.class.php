<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class ConsoleServer {
        
	var $PortID;
	var $Name;
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
            $m=new ConsoleServer();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
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

            $sql="SELECT * FROM console_server WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConsoleServer::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM console_server WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConsoleServer::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetConsoleServerList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM console_server WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=ConsoleServer::RowToObject($row);
                    }else{
                            $ManufacturerList[]=ConsoleServer::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
	
        // Function for list page
        static function GetConsoleServerListRows($filter){
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
                FROM console_server v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= ConsoleServer::RowToObject($row);
                }else{
                    $RowList[]= ConsoleServer::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOAConsole_server COUNTER
	static function GetDashConsoleList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_console 
                FROM console_server v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N'";
            
            $ConsoleServerList = array();
            $result_ConsoleServerList = $dbh->query($sql);
            $ConsoleServerList = $result_ConsoleServerList ? $result_ConsoleServerList->fetch() : array();
            
            return $ConsoleServerList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="INSERT INTO console_server SET name=\"$this->Name\", tag=\"$this->Tag\", device_id=\"$this->Device\", created='".$created."';";
            
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

            $sql="UPDATE console_server SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="UPDATE console_server SET name=\"$this->Name\", tag=\"$this->Tag\", device_id=\"$this->Device\", last_updated='".$created."' WHERE id=$this->PortID;";
            
            $old=new ConsoleServer();
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
            if(isset($filter['device'])){
                $incr .= " AND v.device_id =".$filter['device'];
            }

            $sql="SELECT v.*, d.name as device_name 
                FROM console_server v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= ConsoleServer::RowToObject($row);
                }else{
                    $RowList[]= ConsoleServer::RowToObject($row);
                }
            }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Console_Connection_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Console Server".$tab_sep."Port".$tab_sep."Device".$tab_sep."Console Port".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Console_server'].$tab_sep.$row['Port'].$tab_sep.$row['Device_name'].$tab_sep.$row['Tag'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
