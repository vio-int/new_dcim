<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class AssetCategory {
        
	var $PortID;
	var $Name;
        
        
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
            $m=new AssetCategory();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new AssetCategory();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            
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
                
		$sql="SELECT *
                FROM asset_category
                WHERE is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["PortID"]]=AssetCategory::RowToSearchObject($row);
                    }else{
                        $dcList[]=AssetCategory::RowToSearchObject($row);
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

            $sql="SELECT * FROM asset_category WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetCategory::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM asset_category WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetCategory::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
        static function GetCategoryList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM asset_category WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetCategory::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetCategory::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        // Function for list page
        static function GetCategoryListRows($filter){
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
            if(isset($filter['category'])){
                $incr .= " AND c.id =".$filter['category'];
            }

            $sql="SELECT c.* FROM asset_category c
                WHERE c.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= AssetCategory::RowToObject($row);
                }else{
                    $RowList[]= AssetCategory::RowToObject($row);
                }
            } }
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashCategoryList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_category FROM asset_category WHERE is_deleted='N'";
            
            $IpamGroupList = array();
            $result_IpamGroupList = $dbh->query($sql);
            $IpamGroupList = $result_IpamGroupList ? $result_IpamGroupList->fetch() : array();
            
            return $IpamGroupList;
	}
        
	function CreateObject($params = array()){
            global $dbh;

            $this->MakeSafe();
            $created_at = date("Y-m-d");
            
            if(!empty($params)){
                $this->Name = $params['category_name'];
            }
            
            $sql="INSERT INTO asset_category SET name=\"$this->Name\";";
            
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

            $sql="UPDATE asset_category SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $update_at = date("Y-m-d");
            
            $sql="UPDATE asset_category SET name=\"$this->Name\" WHERE id=$this->PortID;";
            
            $old=new AssetCategory();
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
            if(isset($filter['category'])){
                $incr .= " AND c.id =".$filter['category'];
            }

            $sql="SELECT c.* FROM asset_category
                WHERE c.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= AssetCategory::RowToObject($row);
                }else{
                    $RowList[]= AssetCategory::RowToObject($row);
                }
            } }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Category_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$line_sep;
                $flag = true;
              }
                $header_html .= $row['Name'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
