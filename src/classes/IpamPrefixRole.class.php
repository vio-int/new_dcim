<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamPrefixRole {
        
	var $PortID;
	var $Name;
        var $Slug;
        
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
            $m=new IpamPrefixRole();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            $m->Total_prefix = isset($row["total_prefix"])?$row["total_prefix"]:'';
            $m->Total_vlan = isset($row["total_vlan"])?$row["total_vlan"]:'';
            
            $m->MakeDisplay();

            return $m;
	}

        static function RowToSearchObject($row){
            $m=new IpamPrefixRole();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            $m->Total_prefix = $row["total_prefix"];
            $m->Total_vlan = $row["total_vlan"];
            
            $m->MakeDisplay();

            unset($m->PortID);
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
                
		$sql="SELECT r.*, (SELECT COUNT(*) as total_prefix FROM ipam_prefix p WHERE p.role_id=r.id) as total_prefix, (SELECT COUNT(*) as total_vlan FROM ipam_vlan v WHERE v.role_id=r.id) as total_vlan
                    FROM ipam_role r WHERE r.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["PrefixRoleID"]]=IpamPrefixRole::RowToSearchObject($row);
                    }else{
                        $dcList[]=IpamPrefixRole::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_role WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamPrefixRole::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_role WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                foreach(IpamPrefixRole::RowToObject($row) as $prop => $value){
                        $this->$prop=$value;
                }	
                return true;
            }else{
                return false;
            }
	}
	
        static function GetParentLocationList(){
            global $dbh;
           
            $sql="SELECT * FROM location WHERE is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Room::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Room::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
	static function GetIpamPrefixRoleList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_role WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamPrefixRole::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamPrefixRole::RowToObject($row);
                    }
            }
            return $ManufacturerList;
	}
        
        static function GetGroupLocationList($location_id){
            global $dbh;

            $sql="SELECT * FROM ipam_role WHERE is_deleted='N' AND site_id={$location_id} ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Rack::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Rack::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // Function for list page
        static function GetRoleListRows($filter){
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
            if(isset($filter['role'])){
                $incr .= " AND r.id =".$filter['role'];
            }
            if(isset($filter['slug'])){
                $incr .= " AND r.slug ='".$filter['slug']."'";
            }

            $sql="SELECT r.*, (SELECT COUNT(*) as total_prefix FROM ipam_prefix p WHERE p.role_id=r.id) as total_prefix, (SELECT COUNT(*) as total_vlan FROM ipam_vlan v WHERE v.role_id=r.id) as total_vlan
                    FROM ipam_role r WHERE r.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamPrefixRole::RowToObject($row);
                }else{
                    $RowList[]= IpamPrefixRole::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashIpamRoleList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_role FROM ipam_role WHERE is_deleted='N'";
            
            $IpamRoleList = array();
            $IpamRoleList = $dbh->query($sql)->fetch();
            
            return $IpamRoleList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created_at = date("Y-m-d");
            
            $sql="INSERT INTO ipam_role SET name=\"$this->Name\", slug=\"$this->Slug\", created=\"$created_at\";";
            
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

            $sql="UPDATE ipam_role SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $update_at = date("Y-m-d");
            
            $sql="UPDATE ipam_role SET name=\"$this->Name\", slug=\"$this->Slug\", last_updated=\"$update_at\" WHERE id=$this->PortID;";
            
            $old=new IpamPrefixRole();
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
            if(isset($filter['role'])){
                $incr .= " AND r.id =".$filter['role'];
            }
            if(isset($filter['slug'])){
                $incr .= " AND r.slug ='".$filter['slug']."'";
            }

            $sql="SELECT r.* FROM ipam_role r WHERE r.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamPrefixRole::RowToObject($row);
                }else{
                    $RowList[]= IpamPrefixRole::RowToObject($row);
                }
            }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Role_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Slug".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Slug'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
