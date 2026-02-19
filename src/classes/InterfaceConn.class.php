<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class InterfaceConn {
        
	var $PortID;
	var $Name;
        var $Device_a;
        var $Interface_a;
        var $Device_b;
        var $Interface_b;
	
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
            $m=new InterfaceConn();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Device_a=$row["device_a"];
            $m->Interface_a=$row["interface_a"];
            $m->Device_b=$row["device_b"];
            $m->Interface_b=$row["interface_b"];
            $m->Device_name_a=isset($row["device_name_a"])&&$row["device_name_a"]!=""?$row["device_name_a"]:'';
            $m->Device_name_b=isset($row["device_name_b"])&&$row["device_name_b"]!=""?$row["device_name_b"]:'';
            
            $m->MakeDisplay();

            return $m;
	}

        static function RowToSearchObject($row){
            $m=new InterfaceConn();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Device_a=$row["device_a"];
            $m->Interface_a=$row["interface_a"];
            $m->Device_b=$row["device_b"];
            $m->Interface_b=$row["interface_b"];
            $m->Device_name_a=$row["device_name_a"];
            $m->Device_name_b=$row["device_name_b"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Device_a);
            unset($m->Device_b);
            
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

		$sql="SELECT v.*, d.name as device_name_a, d2.name as device_name_b 
                FROM interface_conn v
                LEFT JOIN device d ON(d.id=v.device_a)
                LEFT JOIN device d2 ON(d2.id=v.device_b)
                WHERE v.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["InterfaceID"]]=InterfaceConn::RowToSearchObject($row);
			}else{
				$dcList[]=InterfaceConn::RowToSearchObject($row);
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

            $sql="SELECT * FROM interface_conn WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(InterfaceConn::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM interface_conn WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(InterfaceConn::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetInterfaceConnList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM interface_conn WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=InterfaceConn::RowToObject($row);
                    }else{
                            $ManufacturerList[]=InterfaceConn::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
	
        // Function for list page
        static function GetInterfaceConnListRows($filter){
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
            if(isset($filter['device_a'])){
                $incr .= " AND v.device_a =".$filter['device_a'];
            }
            if(isset($filter['device_b'])){
                $incr .= " AND v.device_b =".$filter['device_b'];
            }

            $sql="SELECT v.*, d.name as device_name_a, d2.name as device_name_b 
                FROM interface_conn v
                LEFT JOIN device d ON(d.id=v.device_a)
                LEFT JOIN device d2 ON(d2.id=v.device_b)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= InterfaceConn::RowToObject($row);
                }else{
                    $RowList[]= InterfaceConn::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashInterfaceList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_interface 
                FROM interface_conn v
                LEFT JOIN device d ON(d.id=v.device_a)
                LEFT JOIN device d2 ON(d2.id=v.device_b)
                WHERE v.is_deleted='N'";
            
            $InterfaceConnList = array();
            $result_InterfaceConnList = $dbh->query($sql);
            $InterfaceConnList = $result_InterfaceConnList ? $result_InterfaceConnList->fetch() : array();
            
            return $InterfaceConnList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="INSERT INTO interface_conn SET name=\"$this->Name\", device_a=\"$this->Device_a\", interface_a=\"$this->Interface_a\", device_b=\"$this->Device_b\", interface_b=\"$this->Interface_b\", created='".$created."';";
            
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

            $sql="UPDATE interface_conn SET is_deleted='Y' WHERE id=$this->PortID;";
            
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
            $sql="UPDATE interface_conn SET name=\"$this->Name\", device_a=\"$this->Device_a\", interface_a=\"$this->Interface_a\", device_b=\"$this->Device_b\", interface_b=\"$this->Interface_b\", last_updated='".$created."' WHERE id=$this->PortID;";
            
            $old=new InterfaceConn();
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
            if(isset($filter['device_a'])){
                $incr .= " AND v.device_a =".$filter['device_a'];
            }
            if(isset($filter['device_b'])){
                $incr .= " AND v.device_b =".$filter['device_b'];
            }

            $sql="SELECT v.*, d.name as device_name_a, d2.name as device_name_b 
                FROM interface_conn v
                LEFT JOIN device d ON(d.id=v.device_a)
                LEFT JOIN device d2 ON(d2.id=v.device_b)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= InterfaceConn::RowToObject($row);
                }else{
                    $RowList[]= InterfaceConn::RowToObject($row);
                }
            }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Interface_Connection_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Device A".$tab_sep."Interface A".$tab_sep."Device B".$tab_sep."Interface B".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Device_name_a'].$tab_sep.$row['Interface_a'].$tab_sep.$row['Device_name_b'].$tab_sep.$row['Interface_b'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
