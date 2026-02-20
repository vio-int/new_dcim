<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class PowerConn {
        
	var $PortID;
	var $Name;
        var $PDU;
        var $Outlet;
        var $Device;
        var $Power_port;
	
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
            $m=new PowerConn();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->PDU=$row["pdu"];
            $m->Outlet=$row["outlet"];
            $m->Device=$row["device_id"];
            $m->Power_port=$row["power_port"];
            $m->Device_name=isset($row["device_name"])?$row["device_name"]:'';
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new PowerConn();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->PDU=$row["pdu"];
            $m->Outlet=$row["outlet"];
            $m->Device=$row["device_id"];
            $m->Power_port=$row["power_port"];
            $m->Device_name=$row["device_name"];
            
            $m->MakeDisplay();

            unset($m->PortID);
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
                
		$sql="SELECT v.*, d.name as device_name 
                FROM power_conn v 
                LEFT JOIN device d ON(d.id=v.device_id) 
                WHERE v.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
                                $dcList[$row["LocationID"]]=PowerConn::RowToSearchObject($row);
			}else{
                            
                                $dcList[]=PowerConn::RowToSearchObject($row);
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

            $sql="SELECT * FROM power_conn WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(PowerConn::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM power_conn WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(PowerConn::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetPowerConnList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM power_conn WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=PowerConn::RowToObject($row);
                    }else{
                            $ManufacturerList[]=PowerConn::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
	
        // Function for list page
        static function GetPowerConnListRows($filter){
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
                FROM power_conn v 
                LEFT JOIN device d ON(d.id=v.device_id) WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= PowerConn::RowToObject($row);
                }else{
                    $RowList[]= PowerConn::RowToObject($row);
                }
            } }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashPowerList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_powers
                FROM power_conn v 
                LEFT JOIN device d ON(d.id=v.device_id) WHERE v.is_deleted='N'";
            
            $PowerConnList = array();
            $result_PowerConnList = $dbh->query($sql);
            $PowerConnList = $result_PowerConnList ? $result_PowerConnList->fetch() : array();
            
            return $PowerConnList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created= date("Y-m-d");
            
            $sql="INSERT INTO power_conn SET name=\"$this->Name\", pdu=\"$this->PDU\", power_port=\"$this->Power_port\", device_id=\"$this->Device\", outlet=\"$this->Outlet\", created='".$created."';";
            
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

            $sql="UPDATE power_conn SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
                    
            $sql="UPDATE power_conn SET name=\"$this->Name\", pdu=\"$this->PDU\", power_port=\"$this->Power_port\", device_id=\"$this->Device\", outlet=\"$this->Outlet\", last_updated='".$created."' WHERE id=$this->PortID;";
            
            $old=new PowerConn();
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
                FROM power_conn v 
                LEFT JOIN device d ON(d.id=v.device_id) WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= PowerConn::RowToObject($row);
                }else{
                    $RowList[]= PowerConn::RowToObject($row);
                }
            } }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Power_Connection_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."PDU".$tab_sep."Outlet".$tab_sep."Device".$tab_sep."Power Port".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['PDU'].$tab_sep.$row['Outlet'].$tab_sep.$row['Device_name'].$tab_sep.$row['Power_port'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
