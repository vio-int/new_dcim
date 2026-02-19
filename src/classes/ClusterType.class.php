<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class ClusterType {
        
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
            $m=new ClusterType();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            $m->Total_cluster = isset($row["total_cluster"])?$row["total_cluster"]:'';
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new ClusterType();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            $m->Total_cluster = $row["total_cluster"];
            
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
                
		$sql="SELECT v.*, (SELECT COUNT(id) as total_cluster FROM cluster c WHERE v.id=c.type_id GROUP BY type_id) as total_cluster                
                    FROM cluster_type v 
                    WHERE v.is_deleted='N' GROUP BY v.id $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
                                $dcList[$row["ClusterTypeID"]]=ClusterType::RowToSearchObject($row);
			}else{
                            
                                $dcList[]=ClusterType::RowToSearchObject($row);
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

            $sql="SELECT * FROM cluster_type WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(ClusterType::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM cluster_type WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(ClusterType::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetClusterTypeList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM cluster_type WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]=ClusterType::RowToObject($row);
                }else{
                    $ManufacturerList[]=ClusterType::RowToObject($row);
                }
            }
            
            return $ManufacturerList;
	}
	
        // Function for list page
        static function GetClusterTypeListRows($filter){
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
            if(isset($filter['slug'])){
                $incr .= " AND v.slug ='".$filter['slug']."'";
            }

            $sql="SELECT v.*, (SELECT COUNT(id) as total_cluster FROM cluster c WHERE v.id=c.type_id GROUP BY type_id) as total_cluster                FROM cluster_type v 
                WHERE v.is_deleted='N' GROUP BY v.id ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= ClusterType::RowToObject($row);
                }else{
                    $RowList[]= ClusterType::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashClusterTypeList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_type FROM cluster_type WHERE is_deleted='N'";
            
            $ClusterTypeList = array();
            $ClusterTypeList = $dbh->query($sql)->fetch();
            
            return $ClusterTypeList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            /* if($this->Enforce != "")
            {
                $enforce = $this->Enforce;
            } else {
                $enforce = "N";
            } */
            $sql="INSERT INTO cluster_type SET name=\"$this->Name\", slug=\"$this->Slug\";";
            
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

            $sql="UPDATE cluster_type SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            /* if($this->Enforce != "")
            {
                $enforce = $this->Enforce;
            } else {
                $enforce = "N";
            } */
            $sql="UPDATE cluster_type SET name=\"$this->Name\", slug=\"$this->Slug\" WHERE id=$this->PortID;";
            
            $old=new ClusterType();
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
            if(isset($filter['slug'])){
                $incr .= " AND v.slug ='".$filter['slug']."'";
            }

            $sql="SELECT v.*, (SELECT COUNT(id) as total_cluster FROM cluster c WHERE v.id=c.type_id GROUP BY type_id) as total_cluster                FROM cluster_type v 
                WHERE v.is_deleted='N' GROUP BY v.id ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= ClusterType::RowToObject($row);
                }else{
                    $RowList[]= ClusterType::RowToObject($row);
                }
            }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Cluster_type_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Type Name".$tab_sep."Slug".$tab_sep."Total Cluster".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Slug'].$tab_sep.$row['Total_cluster'].$tab_sep.$row['Description'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
