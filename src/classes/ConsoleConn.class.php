<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class ConsoleConn {
        
	var $PortID;
	var $Name;
        var $Console_server;
        var $Device;
        var $Port;
        var $Console_port;
	
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
            $m=new ConsoleConn();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Console_server=$row["console_server"];
            $m->Device=$row["device_id"];
            $m->Port=$row["port"];
            $m->Console_port=$row["console_port"];
            $m->Device_name=isset($row["device_name"])?$row["device_name"]:'';
            $m->Console_server_name=isset($row["console_server_name"])?$row["console_server_name"]:'';
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new ConsoleConn();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Console_server=$row["console_server"];
            $m->Device=$row["device_id"];
            $m->Port=$row["port"];
            $m->Console_port=$row["console_port"];
            $m->Device_name=$row["device_name"];
            $m->Console_server_name=$row["console_server_name"];
            
            $m->MakeDisplay();
            
            unset($m->PortID);
            unset($m->Device);
            unset($m->Console_server);
            
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

		$sql="SELECT v.*, d.name as device_name, cs.name as console_server_name
                FROM console_conn v 
                LEFT JOIN device d ON(d.id=v.device_id)
                LEFT JOIN console_server cs ON(v.console_server=cs.id)
                WHERE v.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["consoleID"]]=ConsoleConn::RowToSearchObject($row);
			}else{
				$dcList[]=ConsoleConn::RowToSearchObject($row);
			}
		}

		return $dcList;
	}
        
	// Wrapper to make this method like the other classes
	function GetObject(){
            return $this->GetOconsole_servererByID();
	}
	
	function GetOrderByID(){
            $this->MakeSafe();

            $sql="SELECT * FROM console_conn WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConsoleConn::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM console_conn WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConsoleConn::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetConsoleConnList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM console_conn WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=ConsoleConn::RowToObject($row);
                    }else{
                            $ManufacturerList[]=ConsoleConn::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        function GetConsoleDeviceList($server_id){
            global $dbh;

            $incr = "";
            if($server_id!=""){
                $incr .= " AND c.console_server=".$server_id;
            }
            $sql="SELECT c.*, d.name as device_name 
                FROM console_conn c 
                LEFT JOIN device d ON(c.device_id = d.id) 
                WHERE c.is_deleted='N' {$incr} ORDER BY id ASC;";
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]=ConsoleConn::RowToObject($row);
                }else{
                    $ManufacturerList[]=ConsoleConn::RowToObject($row);
                }
            }
            
            return $ManufacturerList;
	}
	
        // Function for list page
        static function GetConsoleConnListRows($filter){
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

            $sql="SELECT v.*, d.name as device_name, cs.name as console_server_name 
                FROM console_conn v 
                LEFT JOIN device d ON(d.id=v.device_id)
                LEFT JOIN console_server cs ON(v.console_server=cs.id)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= ConsoleConn::RowToObject($row);
                }else{
                    $RowList[]= ConsoleConn::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOAConsole_server COUNTER
	static function GetDashConsoleList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_console 
                FROM console_conn v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N'";
            
            $ConsoleConnList = array();
            $result_ConsoleConnList = $dbh->query($sql);
            $ConsoleConnList = $result_ConsoleConnList ? $result_ConsoleConnList->fetch() : array();
            
            return $ConsoleConnList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="INSERT INTO console_conn SET name=\"$this->Name\", console_server=\"$this->Console_server\", console_port=\"$this->Console_port\", port=\"$this->Port\", device_id=\"$this->Device\", created='".$created."';";
            
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

            $sql="UPDATE console_conn SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="UPDATE console_conn SET name=\"$this->Name\", console_server=\"$this->Console_server\", console_port=\"$this->Console_port\", port=\"$this->Port\", device_id=\"$this->Device\", last_updated='".$created."' WHERE id=$this->PortID;";
            
            $old=new ConsoleConn();
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
                FROM console_conn v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= ConsoleConn::RowToObject($row);
                }else{
                    $RowList[]= ConsoleConn::RowToObject($row);
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
              $header_html .= $row['Name'].$tab_sep.$row['Console_server'].$tab_sep.$row['Port'].$tab_sep.$row['Device_name'].$tab_sep.$row['Console_port'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
