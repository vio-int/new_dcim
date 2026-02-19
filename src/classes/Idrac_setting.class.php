<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class IdracSetting {
        
	var $PortID;
	var $Name;
        var $Device;
        var $Nic_selection;
        var $Is_enable_ipv4;
        var $Is_enable_dhcp;
        var $Mac_address;
        var $Static_ip_address;
        var $Static_gateway;
        var $Static_subnet_mask;
        var $Device_name;
	
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
            $m=new IdracSetting();
            $m->PortID=$row["id"];
            $m->Device=$row["device_id"];
            $m->Nic_selection=$row["nic_selection"];
            $m->Is_enable_ipv4=$row["is_enable_ipv4"];
            $m->Is_enable_dhcp=$row["is_enable_dhcp"];
            $m->Mac_address=$row["mac_address"];
            $m->Static_ip_address=$row["ip_address"];
            $m->Static_gateway=$row["gateway"];
            $m->Static_subnet_mask=$row["subnet_mask"];
            $m->Device_name=$row["device_name"];
            $m->Name= "Setting ";
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new IdracSetting();
            $m->PortID=$row["id"];
            $m->Device=$row["device_id"];
            $m->Nic_selection=$row["nic_selection"];
            $m->Is_enable_ipv4=$row["is_enable_ipv4"];
            $m->Is_enable_dhcp=$row["is_enable_dhcp"];
            $m->Mac_address=$row["mac_address"];
            $m->Static_ip_address=$row["ip_address"];
            $m->Static_gateway=$row["gateway"];
            $m->Static_subnet_mask=$row["subnet_mask"];
            $m->Device_name=$row["device_name"];
            
            unset($m->PortID);
            unset($m->Device);
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

		$sql="SELECT i.*, d.name as device_name
                FROM idrac_setting i
                JOIN device d ON(d.id = i.device_id)
                WHERE i.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["DeviceID"]]=IdracSetting::RowToSearchObject($row);
			}else{
				$dcList[]=IdracSetting::RowToSearchObject($row);
			}
		}

		return $dcList;
	}
	
	// Wrapper to make this method like the other classes
	function GetObject(){
            return $this->GetIdrac_settingByID();
	}
	
	function GetOrderByID(){
            $this->MakeSafe();

            $sql="SELECT i.*, d.name as device_name 
                FROM idrac_setting i
                JOIN device d ON (d.id = i.device_id)
                WHERE i.is_deleted='N' AND i.id=$this->PortID;";
            
            if($row=$this->query($sql)->fetch()){
                    foreach(IdracSetting::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM idrac_setting WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IdracSetting::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
        static function GetIdracSettingDeviceList($device_id){
            global $dbh;
            $incr = "";
            if($device_id!= ""){
                $incr .=" AND i.device_id={$device_id}";
            }
            
            $sql="SELECT i.*, d.name as device_name
                FROM idrac_setting i 
                LEFT JOIN device d ON (d.id=i.device_id)
                WHERE i.is_deleted='N' {$incr} ORDER BY i.id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IdracSetting::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IdracSetting::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
	static function GetIdracSettingList($indexbyid=false){
            global $dbh;
            
            $sql="SELECT i.*, d.name as device_name
                FROM idrac_setting i 
                LEFT JOIN device d ON (d.id=i.device_id)
                WHERE i.is_deleted='N' ORDER BY i.id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IdracSetting::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IdracSetting::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
	
        // Function for list page
        static function GetIdracSettingListRows($filter){
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
                FROM idrac_setting v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IdracSetting::RowToObject($row);
                }else{
                    $RowList[]= IdracSetting::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOAConsole_server COUNTER
	static function GetDashConsoleList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_console 
                FROM idrac_setting v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N'";
            
            $IdracSettingList = array();
            $IdracSettingList = $dbh->query($sql)->fetch();
            
            return $IdracSettingList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created = date("Y-m-d");
            
            if($this->Is_enable_dhcp == "Y"){
                $Static_ip_address = "";
                $Static_gateway = "";
                $Static_subnet_mask = "";
            } else {
                $Static_ip_address = $this->Static_ip_address;
                $Static_gateway = $this->Static_gateway;
                $Static_subnet_mask = $this->Static_subnet_mask;
            }
            
            $sql="INSERT INTO idrac_setting SET nic_selection=\"$this->Nic_selection\", mac_address=\"$this->Mac_address\", is_enable_dhcp=\"$this->Is_enable_dhcp\", ip_address=\"$Static_ip_address\", gateway=\"$Static_gateway\", subnet_mask=\"$Static_subnet_mask\", device_id=\"$this->Device\", created='".$created."';";
            
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

            $sql="UPDATE idrac_setting SET is_deleted='Y' WHERE id=$this->PortID;";
            
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
            
            if($this->Is_enable_dhcp == "Y"){
                $Static_ip_address = "";
                $Static_gateway = "";
                $Static_subnet_mask = "";
            } else {
                $Static_ip_address = $this->Static_ip_address;
                $Static_gateway = $this->Static_gateway;
                $Static_subnet_mask = $this->Static_subnet_mask;
            }
            
            $sql="UPDATE idrac_setting SET nic_selection=\"$this->Nic_selection\", mac_address=\"$this->Mac_address\", is_enable_ipv4=\"$this->Is_enable_ipv4\", is_enable_dhcp=\"$this->Is_enable_dhcp\", ip_address=\"$Static_ip_address\", gateway=\"$Static_gateway\", subnet_mask=\"$Static_subnet_mask\", device_id=\"$this->Device\", last_updated='".$created."' WHERE id=$this->PortID;";
            $old=new IdracSetting();
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
                FROM idrac_setting v 
                LEFT JOIN device d ON(d.id=v.device_id)
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IdracSetting::RowToObject($row);
                }else{
                    $RowList[]= IdracSetting::RowToObject($row);
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
              $header_html .= $row['Name'].$tab_sep.$row['idrac_setting'].$tab_sep.$row['Port'].$tab_sep.$row['Device_name'].$tab_sep.$row['Tag'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
