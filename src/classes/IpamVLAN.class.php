<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamVLAN {
        
	var $PortID;
	var $Name;
        var $Site;
        var $Status;
        var $Group;
        var $Role;
        var $Description;
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
            $m=new IpamVLAN();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Site=$row["site_id"];
            $m->Status=$row["status"];
            $m->Group=$row["group_id"];
            $m->Role=$row["role_id"];
            $m->Description=$row["description"];
            $m->Tag=$row["tag"];
            $m->Role_name=isset($row["role_name"])?$row["role_name"]:'';
            $m->Location_name=isset($row["location_name"])?$row["location_name"]:'';
            $m->Group_name=isset($row["group_name"])?$row["group_name"]:'';
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new IpamVLAN();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Site=$row["site_id"];
            $m->Status=$row["status"];
            $m->Group=$row["group_id"];
            $m->Role=$row["role_id"];
            $m->Description=$row["description"];
            $m->Tag=$row["tag"];
            $m->Role_name=$row["role_name"];
            $m->Location_name=$row["location_name"];
            $m->Group_name=$row["group_name"];
            $m->Created_at=$row["created"];
            $m->Updated_at=$row["last_updated"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Site);
            unset($m->Group);
            unset($m->Role);
            
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
                
		$sql="SELECT v.*,l.name as location_name, g.name as group_name, r.name as role_name 
                FROM ipam_vlan v 
                LEFT JOIN location l ON(l.id=v.site_id)
                LEFT JOIN ipam_vlangroup g ON(g.id=v.group_id) 
                LEFT JOIN ipam_role r ON(r.id=v.role_id) 
                WHERE v.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["VlanID"]]=IpamVLAN::RowToSearchObject($row);
                    }else{
                        $dcList[]=IpamVLAN::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_vlan WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamVLAN::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_vlan WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamVLAN::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIpamVLANList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_vlan WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamVLAN::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamVLAN::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetVlanLocationList($group_id){
            global $dbh;

            $sql="SELECT * FROM ipam_vlan WHERE is_deleted='N' AND site_id={$group_id} ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]= IpamVLANGroup::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamVLANGroup::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetLocationIpamVLANList($location_id){
            global $dbh;

            $sql="SELECT * FROM ipam_vlan WHERE is_deleted='N' AND site_id={$location_id} ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamVLAN::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamVLAN::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // Function for list page
        static function GetIpamVLANListRows($filter){
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
                $incr .= " AND v.id =".$filter['name'];
            }
            if(isset($filter['location'])){
                $incr .= " AND v.site_id =".$filter['location'];
            }
            if(isset($filter['group'])){
                $incr .= " AND v.group_id =".$filter['group'];
            }
            if(isset($filter['role'])){
                $incr .= " AND v.role_id =".$filter['role'];
            }
            if(isset($filter['type'])){
                $incr .= " AND r.type ='".$filter['type']."'";
            }

            $sql="SELECT v.*,l.name as location_name, g.name as group_name, r.name as role_name 
                FROM ipam_vlan v 
                LEFT JOIN location l ON(l.id=v.site_id)
                LEFT JOIN ipam_vlangroup g ON(g.id=v.group_id) 
                LEFT JOIN ipam_role r ON(r.id=v.role_id) 
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamVLAN::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamVLAN::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetVLANOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['vlan'])){
                $incr .= " AND v.id =".$filter['vlan'];
            }
            
            $sql="SELECT v.*,l.name as location_name, g.name as group_name, r.name as role_name 
                FROM ipam_vlan v 
                LEFT JOIN location l ON(l.id=v.site_id)
                LEFT JOIN ipam_vlangroup g ON(g.id=v.group_id) 
                LEFT JOIN ipam_role r ON(r.id=v.role_id) 
                WHERE v.is_deleted='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamVLAN::RowToObject($row);
                }else{
                    $RowList[]= IpamVLAN::RowToObject($row);
                }
            }
            return $RowList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashIpamVLANList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_vlan 
                FROM ipam_vlan v 
                LEFT JOIN location l ON(l.id=v.site_id)
                LEFT JOIN ipam_vlangroup g ON(g.id=v.group_id) 
                LEFT JOIN ipam_role r ON(r.id=v.role_id) WHERE v.is_deleted='N';";
            
            $IpamVLANList = array();
            $result_IpamVLANList = $dbh->query($sql);
            $IpamVLANList = $result_IpamVLANList ? $result_IpamVLANList->fetch() : array();
            
            return $IpamVLANList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created_at = date('Y-m-d');
            
            $sql="INSERT INTO ipam_vlan SET name=\"$this->Name\", site_id=\"$this->Site\", role_id=\"$this->Role\", status=\"$this->Status\", group_id=\"$this->Group\", description=\"$this->Description\", tag=\"$this->Tag\", created=\"$created_at\";";
            
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

            $sql="UPDATE ipam_vlan SET is_deleted='Y' WHERE id=$this->PortID;";
            
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            
            $last_updated = date('Y-m-d');
            
            $sql="UPDATE ipam_vlan SET name=\"$this->Name\", site_id=\"$this->Site\", role_id=\"$this->Role\", status=\"$this->Status\", group_id=\"$this->Group\", description=\"$this->Description\", tag=\"$this->Tag\", last_updated=\"$last_updated\" WHERE id=$this->PortID;";
            
            $old=new IpamVLAN();
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
                $incr .= " AND v.id =".$filter['name'];
            }
            if(isset($filter['location'])){
                $incr .= " AND v.site_id =".$filter['location'];
            }
            if(isset($filter['group'])){
                $incr .= " AND v.group_id =".$filter['group'];
            }
            if(isset($filter['role'])){
                $incr .= " AND v.role_id =".$filter['role'];
            }
            if(isset($filter['type'])){
                $incr .= " AND r.type ='".$filter['type']."'";
            }

            $sql="SELECT v.name, v.status, l.name as location_name, g.name as group_name, r.name as role_name, v.description 
                FROM ipam_vlan v 
                LEFT JOIN location l ON(l.id=v.site_id)
                LEFT JOIN ipam_vlangroup g ON(g.id=v.group_id) 
                LEFT JOIN ipam_role r ON(r.id=v.role_id) 
                WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamVLAN::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamVLAN::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "VLANs_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";
            $flag = false;
            foreach($result as $row) {
                
                if(!$flag) {
                    // display field/column names as first row
                    $header_html .= "Name".$tab_sep."Status".$tab_sep."Location".$tab_sep."Group".$tab_sep."Role".$tab_sep."Description".$line_sep;
                    $flag = true;
                }
                    $header_html .= $row['Name'].$tab_sep.$row['Status'].$tab_sep.$row['Location_name'].$tab_sep.$row['Group_name'].$tab_sep.$row['Role_name'].$tab_sep.$row['Description'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
