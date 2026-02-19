<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamVLANGroup {
        
	var $PortID;
	var $Name;
        var $Slug;
        var $Location;
        
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
            $m=new IpamVLANGroup();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            $m->Location=$row["site_id"];
            $m->LocationName = isset($row["location_name"])?$row["location_name"]:'';
            $m->Created_at =$row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new IpamVLANGroup();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            $m->Location=$row["site_id"];
            $m->LocationName = $row["location_name"];
            $m->Created_at =$row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Location);
            
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
                
		$sql="SELECT g.*, l.name as location_name
                    FROM ipam_vlangroup g 
                    JOIN location l ON(l.id=g.site_id)
                    WHERE g.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["GroupID"]]=IpamVLANGroup::RowToSearchObject($row);
                    }else{
                        $dcList[]=IpamVLANGroup::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_vlangroup WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamVLANGroup::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_vlangroup WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamVLANGroup::RowToObject($row) as $prop => $value){
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
        
	static function GetIpamVLANGroupList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_vlangroup WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamVLANGroup::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamVLANGroup::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetGroupLocationList($location_id){
            global $dbh;

            $sql="SELECT * FROM ipam_vlangroup WHERE is_deleted='N' AND site_id={$location_id} ORDER BY id ASC;";

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
        static function GetGroupListRows($filter){
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
                $incr .= " AND g.id =".$filter['name'];
            }
            if(isset($filter['slug'])){
                $incr .= " AND g.slug ='".$filter['slug']."'";
            }

            $sql="SELECT g.* FROM ipam_vlangroup g WHERE g.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamVLANGroup::RowToObject($row);
                }else{
                    $RowList[]= IpamVLANGroup::RowToObject($row);
                }
            }
            return $RowList;
	}
        
        // Function for detail page
        static function GetVLANGROUPOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['group'])){
                $incr .= " AND g.id =".$filter['group'];
            }
            
            $sql="SELECT g.* FROM ipam_vlangroup g WHERE g.is_deleted='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamVLANGroup::RowToObject($row);
                }else{
                    $RowList[]= IpamVLANGroup::RowToObject($row);
                }
            }
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashIpamGroupList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_group FROM ipam_vlangroup WHERE is_deleted='N'";
            
            $IpamGroupList = array();
            $IpamGroupList = $dbh->query($sql)->fetch();
            
            return $IpamGroupList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created_at = date("Y-m-d");
            
            $sql="INSERT INTO ipam_vlangroup SET name=\"$this->Name\", slug=\"$this->Slug\", site_id=\"$this->Location\", created=\"$created_at\";";
            
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

            $sql="UPDATE ipam_vlangroup SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $update_at = date("Y-m-d");
            
            $sql="UPDATE ipam_vlangroup SET name=\"$this->Name\", slug=\"$this->Slug\", site_id=\"$this->Location\", last_updated=\"$update_at\" WHERE id=$this->PortID;";
            
            $old=new IpamVLANGroup();
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
                $incr .= " AND g.id =".$filter['name'];
            }
            if(isset($filter['slug'])){
                $incr .= " AND g.slug ='".$filter['slug']."'";
            }

            $sql="SELECT g.*, l.name as location_name
                FROM ipam_vlangroup g 
                LEFT JOIN location l ON (l.id=g.site_id) WHERE g.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']] = IpamVLANGroup::RowToObject($row);
                }else{
                    $RowList[] = IpamVLANGroup::RowToObject($row);
                }
            }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "VLAN_group_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Slug".$tab_sep."Location".$line_sep;
                $flag = true;
              }
                $header_html .= $row['Name'].$tab_sep.$row['Slug'].$tab_sep.$row['LocationName'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
