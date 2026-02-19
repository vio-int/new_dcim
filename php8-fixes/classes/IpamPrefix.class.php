<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamPrefix {
        
	var $PortID;
	var $Name;
        var $Status;
        var $Vrf;
        var $Role;
        var $Description;
        var $Site;
        var $Group;
        var $Vlan;
        var $Pool;
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
            $m=new IpamPrefix();
            $m->PortID=$row["id"];
            $m->Name=$row["prefix"];
            $m->Status=$row["status"];
            $m->Vrf=$row["vrf_id"];
            $m->Role=$row["role_id"];
            $m->Description=$row["description"];
            $m->Site=$row["site_id"];
            $m->Group=$row["group_id"];
            $m->Vlan=$row["vlan_id"];
            $m->Pool=$row["is_pool"];
            $m->Tag=$row["tag"];
            $m->GroupName=isset($row["group_name"])?$row["group_name"]:'';
            $m->VRFName=isset($row["vrf_name"])?$row["vrf_name"]:'';
            $m->VlanName=isset($row["vlan_name"])?$row["vlan_name"]:'';
            $m->LocationName=isset($row["location_name"])?$row["location_name"]:'';
            $m->RoleName=isset($row["role_name"])?$row["role_name"]:'';
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new IpamPrefix();
            $m->PortID=$row["id"];
            $m->Name=$row["prefix"];
            $m->Status=$row["status"];
            $m->Vrf=$row["vrf_id"];
            $m->Role=$row["role_id"];
            $m->Description=$row["description"];
            $m->Site=$row["site_id"];
            $m->Group=$row["group_id"];
            $m->Vlan=$row["vlan_id"];
            $m->Pool=$row["is_pool"];
            $m->Tag=$row["tag"];
            $m->GroupName=$row["group_name"];
            $m->VRFName=$row["vrf_name"];
            $m->VlanName=$row["vlan_name"];
            $m->LocationName=$row["location_name"];
            $m->RoleName=$row["role_name"];
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Vrf);
            unset($m->Role);
            unset($m->Site);
            unset($m->Group);
            unset($m->Vlan);
            
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
                
		$sql="SELECT p.*,v.name as vrf_name,pr.name as role_name, l.name as location_name, vg.name as group_name, vl.name as vlan_name 
                FROM ipam_prefix p 
                LEFT JOIN ipam_vrf v ON(v.id=p.vrf_id)
                LEFT JOIN ipam_role pr ON(pr.id=p.role_id)
                LEFT JOIN location l ON(l.id=p.site_id)
                LEFT JOIN ipam_vlangroup vg ON(vg.id=p.group_id)
                LEFT JOIN ipam_vlan vl ON(vl.id=p.vlan_id)
                WHERE p.is_deleted='N' $sqlextend ORDER BY v.Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["PrefixID"]]=IpamPrefix::RowToSearchObject($row);
                    }else{
                        $dcList[]=IpamPrefix::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_prefix WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamPrefix::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_prefix WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamPrefix::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIpamPrefixList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_prefix WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamPrefix::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamPrefix::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        static function GetLocationIpamPrefixList($location_id){
            global $dbh;

            $sql="SELECT * FROM ipam_prefix WHERE is_deleted='N' AND site_id={$location_id} ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamPrefix::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamPrefix::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetIpamPrefixListRows($filter){
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
            if(isset($filter['prefix'])){
                $incr .= " AND p.id =".$filter['prefix'];
            }
            if(isset($filter['vrf'])){
                $incr .= " AND p.vrf_id =".$filter['vrf'];
            }
            if(isset($filter['role'])){
                $incr .= " AND p.role_id =".$filter['role'];
            }
            if(isset($filter['location'])){
                $incr .= " AND p.site_id =".$filter['location'];
            }
            if(isset($filter['group'])){
                $incr .= " AND p.group_id ='".$filter['group']."'";
            }
            if(isset($filter['vlan'])){
                $incr .= " AND p.vlan_id ='".$filter['vlan']."'";
            }

            $sql="SELECT p.*,v.name as vrf_name,pr.name as role_name, l.name as location_name, vg.name as group_name, vl.name as vlan_name 
                FROM ipam_prefix p 
                LEFT JOIN ipam_vrf v ON(v.id=p.vrf_id)
                LEFT JOIN ipam_role pr ON(pr.id=p.role_id)
                LEFT JOIN location l ON(l.id=p.site_id)
                LEFT JOIN ipam_vlangroup vg ON(vg.id=p.group_id)
                LEFT JOIN ipam_vlan vl ON(vl.id=p.vlan_id)
                WHERE p.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamPrefix::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamPrefix::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetPrefixOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['prefix'])){
                $incr .= " AND p.id =".$filter['prefix'];
            }
            
            $sql="SELECT p.*,v.name as vrf_name,pr.name as role_name, l.name as location_name, vg.name as group_name, vl.name as vlan_name 
                FROM ipam_prefix p 
                LEFT JOIN ipam_vrf v ON(v.id=p.vrf_id)
                LEFT JOIN ipam_role pr ON(pr.id=p.role_id)
                LEFT JOIN location l ON(l.id=p.site_id)
                LEFT JOIN ipam_vlangroup vg ON(vg.id=p.group_id)
                LEFT JOIN ipam_vlan vl ON(vl.id=p.vlan_id)
                WHERE p.is_deleted='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamPrefix::RowToObject($row);
                }else{
                    $RowList[]= IpamPrefix::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashIpamPrefixList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_prefix
                FROM ipam_prefix p 
                LEFT JOIN ipam_vrf v ON(v.id=p.vrf_id)
                LEFT JOIN ipam_role pr ON(pr.id=p.role_id)
                LEFT JOIN location l ON(l.id=p.site_id)
                LEFT JOIN ipam_vlangroup vg ON(vg.id=p.group_id)
                LEFT JOIN ipam_vlan vl ON(vl.id=p.vlan_id) WHERE p.is_deleted='N'";
            
            $IpamPrefixList = array();
            $IpamPrefixList = $dbh->query($sql)->fetch();
            
            return $IpamPrefixList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            if($this->Pool!="")
            {
                $pool = $this->Pool;
            } else {
                $pool = "N";
            }
            $created_at = date('Y-m-d');
            
            $sql="INSERT INTO ipam_prefix SET prefix=\"$this->Name\", vrf_id=\"$this->Vrf\", site_id=\"$this->Site\", role_id=\"$this->Role\", status=\"$this->Status\", group_id=\"$this->Group\", is_pool='".$pool."', vlan_id=\"$this->Vlan\", description=\"$this->Description\", tag=\"$this->Tag\", created=\"$created_at\";";
            
            
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

            $sql="UPDATE ipam_prefix SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            
            $last_updated = date('Y-m-d');
            if($this->Pool!="")
            {
                $pool = $this->Pool;
            } else {
                $pool = "N";
            }
            
            $sql="UPDATE ipam_prefix SET prefix=\"$this->Name\", vrf_id=\"$this->Vrf\", site_id=\"$this->Site\", role_id=\"$this->Role\", status=\"$this->Status\", group_id=\"$this->Group\", is_pool='".$pool."', vlan_id=\"$this->Vlan\", description=\"$this->Description\", tag=\"$this->Tag\", last_updated=\"$last_updated\" WHERE id=$this->PortID;";
            
            $old=new IpamPrefix();
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
            if(isset($filter['prefix'])){
                $incr .= " AND p.id =".$filter['prefix'];
            }
            if(isset($filter['vrf'])){
                $incr .= " AND p.vrf_id =".$filter['vrf'];
            }
            if(isset($filter['role'])){
                $incr .= " AND p.role_id =".$filter['role'];
            }
            if(isset($filter['location'])){
                $incr .= " AND p.site_id =".$filter['location'];
            }
            if(isset($filter['group'])){
                $incr .= " AND p.group_id ='".$filter['group']."'";
            }
            if(isset($filter['vlan'])){
                $incr .= " AND p.vlan_id ='".$filter['vlan']."'";
            }

            $sql="SELECT p.*,v.name as vrf_name,pr.name as role_name, l.name as location_name, vg.name as group_name, vl.name as vlan_name 
                FROM ipam_prefix p 
                LEFT JOIN ipam_vrf v ON(v.id=p.vrf_id)
                LEFT JOIN ipam_role pr ON(pr.id=p.role_id)
                LEFT JOIN location l ON(l.id=p.site_id)
                LEFT JOIN ipam_vlangroup vg ON(vg.id=p.group_id)
                LEFT JOIN ipam_vlan vl ON(vl.id=p.vlan_id)
                WHERE p.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamPrefix::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamPrefix::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "prefix_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Status".$tab_sep."VRF".$tab_sep."Role".$tab_sep."Description".$tab_sep."Location".$tab_sep."Group".$tab_sep."VLAN".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Status'].$tab_sep.$row['VRFName'].$tab_sep.$row['RoleName'].$tab_sep.$row['Description'].$tab_sep.$row['LocationName'].$tab_sep.$row['GroupName'].$tab_sep.$row['VlanName'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
