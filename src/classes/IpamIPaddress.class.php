<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamIPaddress {
        
	var $PortID;
	var $Address;
        var $Status;
        var $Role;
        var $Vrf;
        var $Description;
	var $Site;
        var $Rack;
        var $Device;
        var $IPAddress;
        var $Tag;
        var $By_type;
	
	public function __construct($manufacturerid=false){
            if($manufacturerid){
                $this->PortID=$manufacturerid;
            }
            return $this;
	}

	function MakeSafe(){
            $this->PortID=intval($this->PortID);
            $this->Name=isset($this->Name)?sanitize($this->Name):'';
	}

	function MakeDisplay(){
            $this->Name=isset($this->Name)?stripslashes($this->Name):'';
        }

	static function RowToObject($row){
            $m=new IpamIPaddress();
            $m->PortID=$row["id"];
            $m->Address=$row["address"];
            $m->Status=$row["status"];
            $m->Role=$row["role_id"];
            $m->Vrf=$row["vrf_id"];
            $m->Description=$row["description"];
            $m->Site=$row["site_id"];
            $m->Rack=$row["rack_id"];
            $m->Device=$row["device_id"];
            $m->IPAddress=$row["ip_address"];
            $m->Tag=$row["tag"];
            $m->Location_name=isset($row["location_name"])?$row["location_name"]:'';
            $m->Rack_name=isset($row["rack_name"])?$row["rack_name"]:'';
            $m->Device_name=isset($row["device_name"])?$row["device_name"]:'';
            $m->VRF_name=isset($row["vrf_name"])?$row["vrf_name"]:'';
            $m->Role_name=isset($row["role_name"])?$row["role_name"]:'';
            $m->By_type=isset($row["by_type"])?$row["by_type"]:'';
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new IpamIPaddress();
            $m->PortID=$row["id"];
            $m->Address=$row["address"];
            $m->Status=$row["status"];
            $m->Role=$row["role_id"];
            $m->Vrf=$row["vrf_id"];
            $m->Description=$row["description"];
            $m->Site=$row["site_id"];
            $m->Rack=$row["rack_id"];
            $m->Device=$row["device_id"];
            $m->IPAddress=$row["ip_address"];
            $m->Tag=$row["tag"];
            $m->Location_name=$row["location_name"];
            $m->Rack_name=$row["rack_name"];
            $m->Device_name=$row["device_name"];
            $m->VRF_name=$row["vrf_name"];
            $m->Role_name=$row["role_name"];
            $m->By_type=$row["by_type"];
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Role);
            unset($m->Vrf);
            unset($m->Site);
            unset($m->Rack);
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
                
		$sql="SELECT a.*,l.name as location_name, r.name as rack_name, d.name as device_name, ir.name as role_name, v.name as vrf_name 
                FROM ipam_ipaddress a 
                LEFT JOIN location l ON(l.id=a.site_id)
                LEFT JOIN rack r ON(r.id=a.rack_id) 
                LEFT JOIN ipam_role ir ON(ir.id=a.role_id) 
                LEFT JOIN device d ON(d.id=a.device_id) 
                LEFT JOIN ipam_vrf v ON(v.id=a.vrf_id) 
                WHERE a.is_deleted='N' $sqlextend ORDER BY l.Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["IpaddressID"]]=IpamIPaddress::RowToSearchObject($row);
                    }else{
                        $dcList[]=IpamIPaddress::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_ipaddress WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamIPaddress::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_ipaddress WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamIPaddress::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIpamIPaddressList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_ipaddress WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamIPaddress::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamIPaddress::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetIPAddressOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['ipaddress'])){
                $incr .= " AND a.id =".$filter['ipaddress'];
            }
            
            $sql="SELECT a.*,l.name as location_name, r.name as rack_name, d.name as device_name, ir.name as role_name, v.name as vrf_name 
                FROM ipam_ipaddress a 
                LEFT JOIN location l ON(l.id=a.site_id)
                LEFT JOIN rack r ON(r.id=a.rack_id) 
                LEFT JOIN ipam_role ir ON(ir.id=a.role_id) 
                LEFT JOIN device d ON(d.id=a.device_id) 
                LEFT JOIN ipam_vrf v ON(v.id=a.vrf_id) 
                WHERE a.is_deleted='N' ".$incr."";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamIPaddress::RowToObject($row);
                }else{
                    $RowList[]= IpamIPaddress::RowToObject($row);
                }
            } }
            return $RowList;
	}
        
        // Function for list page
        static function GetIpaddressListRows($filter){
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
            if(isset($filter['address'])){
                $incr .= " AND a.id =".$filter['address'];
            }
            if(isset($filter['location'])){
                $incr .= " AND a.site_id =".$filter['location'];
            }
            if(isset($filter['rack'])){
                $incr .= " AND a.rack_id =".$filter['rack'];
            }
            if(isset($filter['device'])){
                $incr .= " AND a.device_id =".$filter['device'];
            }
            if(isset($filter['role'])){
                $incr .= " AND a.role_id =".$filter['role'];
            }
            if(isset($filter['vrf'])){
                $incr .= " AND a.vrf_id =".$filter['vrf'];
            }

            $sql="SELECT a.*,l.name as location_name, r.name as rack_name, d.name as device_name, ir.name as role_name, v.name as vrf_name 
                FROM ipam_ipaddress a 
                LEFT JOIN location l ON(l.id=a.site_id)
                LEFT JOIN rack r ON(r.id=a.rack_id) 
                LEFT JOIN ipam_role ir ON(ir.id=a.role_id) 
                LEFT JOIN device d ON(d.id=a.device_id) 
                LEFT JOIN ipam_vrf v ON(v.id=a.vrf_id) 
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]= IpamIPaddress::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamIPaddress::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashIpaddressList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_ipaddress 
                FROM ipam_ipaddress a 
                LEFT JOIN location l ON(l.id=a.site_id)
                LEFT JOIN rack r ON(r.id=a.rack_id) 
                LEFT JOIN ipam_role ir ON(ir.id=a.role_id) 
                LEFT JOIN device d ON(d.id=a.device_id) 
                LEFT JOIN ipam_vrf v ON(v.id=a.vrf_id) WHERE a.is_deleted='N'";
            
            $IpamIpaddressList = array();
            $result_IpamIpaddressList = $dbh->query($sql);
            $IpamIpaddressList = $result_IpamIpaddressList ? $result_IpamIpaddressList->fetch() : array();
            
            return $IpamIpaddressList;
	}
	
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            if($this->By_type=="by_ip"){
                $site_id = "''";
                $rack_id = "''";
                $device_id = "''";
            } else {
                $site_id = $this->Site;
                $rack_id = $this->Rack;
                $device_id = $this->Device;
            }
            $sql="INSERT INTO ipam_ipaddress SET address=\"$this->Address\", status=\"$this->Status\", role_id=\"$this->Role\", vrf_id=\"$this->Vrf\", description=\"$this->Description\", by_type=\"$this->By_type\", site_id=".$site_id.", rack_id=".$rack_id.", device_id=".$device_id.", ip_address=\"$this->IPAddress\", tag=\"$this->Tag\";";
            
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

            $sql="UPDATE ipam_ipaddress SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            
            if($this->By_type=="by_ip"){
                $site_id = "''";
                $rack_id = "''";
                $device_id = "''";
            } else {
                $site_id = $this->Site;
                $rack_id = $this->Rack;
                $device_id = $this->Device;
            }
            
            $sql="UPDATE ipam_ipaddress SET address=\"$this->Address\", status=\"$this->Status\", role_id=\"$this->Role\", vrf_id=\"$this->Vrf\", description=\"$this->Description\", by_type=\"$this->By_type\", site_id=".$site_id.", rack_id=".$rack_id.", device_id=".$device_id.", ip_address=\"$this->IPAddress\", tag=\"$this->Tag\" WHERE id=$this->PortID;";
            
            $old=new IpamIPaddress();
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
            if(isset($filter['address'])){
                $incr .= " AND a.id =".$filter['address'];
            }
            if(isset($filter['location'])){
                $incr .= " AND a.site_id =".$filter['location'];
            }
            if(isset($filter['rack'])){
                $incr .= " AND a.rack_id =".$filter['rack'];
            }
            if(isset($filter['device'])){
                $incr .= " AND a.device_id =".$filter['device'];
            }
            if(isset($filter['role'])){
                $incr .= " AND a.role_id =".$filter['role'];
            }
            if(isset($filter['vrf'])){
                $incr .= " AND a.vrf_id =".$filter['vrf'];
            }

            $sql="SELECT a.*,l.name as location_name, r.name as rack_name, d.name as device_name, ir.name as role_name, v.name as vrf_name 
                FROM ipam_ipaddress a 
                LEFT JOIN location l ON(l.id=a.site_id)
                LEFT JOIN rack r ON(r.id=a.rack_id) 
                LEFT JOIN ipam_role ir ON(ir.id=a.role_id) 
                LEFT JOIN device d ON(d.id=a.device_id) 
                LEFT JOIN ipam_vrf v ON(v.id=a.vrf_id) 
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]= IpamIPaddress::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamIPaddress::RowToObject($row);
                    }
            } }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Ipaddress_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Address".$tab_sep."Status".$tab_sep."Role".$tab_sep."VRF".$tab_sep."Location".$tab_sep."Rack".$tab_sep."Device".$tab_sep."Description".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Address'].$tab_sep.$row['Status'].$tab_sep.$row['Role_name'].$tab_sep.$row['VRF_name'].$tab_sep.$row['Location_name'].$tab_sep.$row['Rack_name'].$tab_sep.$row['Device_name'].$tab_sep.$row['Description'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
