<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Cluster {
        
	var $PortID;
	var $Name;
        var $Type;
        var $Group;
        var $Location;
        var $Tag;
        var $Comment;
        
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
            $m=new Cluster();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Type=$row["type_id"];
            $m->Group=$row["group_id"];
            $m->Location=$row["site_id"];
            $m->Tag=$row["tag"];
            $m->Comment=$row["comment"];
            $m->Location_name = isset($row["location_name"])?$row["location_name"]:'';
            $m->Group_name = isset($row["group_name"])?$row["group_name"]:'';
            $m->Type_name = isset($row["type_name"])?$row["type_name"]:'';
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Cluster();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Type=$row["type_id"];
            $m->Group=$row["group_id"];
            $m->Location=$row["site_id"];
            $m->Tag=$row["tag"];
            $m->Comment=$row["comment"];
            $m->Location_name = $row["location_name"];
            $m->Group_name = $row["group_name"];
            $m->Type_name = $row["type_name"];
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();
            
            unset($m->PortID);
            unset($m->Type);
            unset($m->Group);
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
                
		$sql="SELECT c.*, l.name as location_name, g.name as group_name, t.name as type_name
                FROM cluster c 
                LEFT JOIN cluster_group g ON(g.id=c.group_id)
                LEFT JOIN cluster_type t ON(t.id=c.type_id)
                LEFT JOIN location l ON(l.id=c.site_id)
                WHERE c.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["ClusterID"]]=Cluster::RowToSearchObject($row);
                    }else{
                        $dcList[]=Cluster::RowToSearchObject($row);
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

            $sql="SELECT * FROM cluster WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(Cluster::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM cluster WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Cluster::RowToObject($row) as $prop => $value){
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
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Room::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Room::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
	static function GetClusterList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM cluster WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Cluster::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Cluster::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        static function GetGroupLocationList($location_id){
            global $dbh;

            $sql="SELECT * FROM cluster WHERE is_deleted='N' AND site_id={$location_id} ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Rack::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Rack::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        static function GetGroupClusterList($group_id){
            global $dbh;

            $sql="SELECT id, name FROM cluster WHERE is_deleted='N' AND group_id={$group_id} ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Rack::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Rack::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetClusterOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['aggreget'])){
                $incr .= " AND a.id =".$filter['aggreget'];
            }
            
            $sql="SELECT c.*, l.name as location_name, g.name as group_name, t.name as type_name
                FROM cluster c 
                LEFT JOIN cluster_group g ON(g.id=c.group_id)
                LEFT JOIN cluster_type t ON(t.id=c.type_id)
                LEFT JOIN location l ON(l.id=c.site_id)
                WHERE c.is_deleted='N' ".$incr."";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= Cluster::RowToObject($row);
                }else{
                    $RowList[]= Cluster::RowToObject($row);
                }
            } }
            return $RowList;
	}
        
        // Function for list page
        static function GetClusterListRows($filter){
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
            if(isset($filter['location'])){
                $incr .= " AND c.site_id =".$filter['location'];
            }
            if(isset($filter['group'])){
                $incr .= " AND c.group_id =".$filter['group'];
            }
            if(isset($filter['type'])){
                $incr .= " AND c.type_id =".$filter['type'];
            }
            if(isset($filter['cluster'])){
                $incr .= " AND c.id =".$filter['cluster'];
            }

            $sql="SELECT c.*, l.name as location_name, g.name as group_name, t.name as type_name
                FROM cluster c 
                LEFT JOIN cluster_group g ON(g.id=c.group_id)
                LEFT JOIN cluster_type t ON(t.id=c.type_id)
                LEFT JOIN location l ON(l.id=c.site_id)
                WHERE c.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= Cluster::RowToObject($row);
                }else{
                    $RowList[]= Cluster::RowToObject($row);
                }
            } }
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashClusterList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_cluster FROM cluster WHERE is_deleted='N'";
            
            $IpamGroupList = array();
            $result_IpamGroupList = $dbh->query($sql);
            $IpamGroupList = $result_IpamGroupList ? $result_IpamGroupList->fetch() : array();
            
            return $IpamGroupList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $created_at = date("Y-m-d");
            
            $sql="INSERT INTO cluster SET name=\"$this->Name\", site_id=\"$this->Location\", group_id=\"$this->Group\", type_id=\"$this->Type\", tag=\"$this->Tag\", comment=\"$this->Comment\", created=\"$created_at\";";
            
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

            $sql="UPDATE cluster SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $update_at = date("Y-m-d");
            
            $sql="UPDATE cluster SET name=\"$this->Name\", site_id=\"$this->Location\", group_id=\"$this->Group\", type_id=\"$this->Type\", tag=\"$this->Tag\", comment=\"$this->Comment\", last_updated=\"$update_at\" WHERE id=$this->PortID;";
            
            $old=new Cluster();
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
            if(isset($filter['location'])){
                $incr .= " AND c.site_id =".$filter['location'];
            }
            if(isset($filter['group'])){
                $incr .= " AND c.group_id =".$filter['group'];
            }
            if(isset($filter['type'])){
                $incr .= " AND c.type_id =".$filter['type'];
            }

            $sql="SELECT c.*, l.name as location_name, g.name as group_name, t.name as type_name
                FROM cluster c 
                LEFT JOIN cluster_group g ON(g.id=c.group_id)
                LEFT JOIN cluster_type t ON(t.id=c.type_id)
                LEFT JOIN location l ON(l.id=c.site_id)
                WHERE c.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= Cluster::RowToObject($row);
                }else{
                    $RowList[]= Cluster::RowToObject($row);
                }
            } }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Cluster_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Location".$tab_sep."Group".$tab_sep."Type".$tab_sep."Tag".$line_sep;
                $flag = true;
              }
                $header_html .= $row['Name'].$tab_sep.$row['Location_name'].$tab_sep.$row['Group_name'].$tab_sep.$row['Type_name'].$tab_sep.$row['Tag'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
