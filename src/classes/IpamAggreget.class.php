<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamAggreget {
        
	var $PortID;
	var $Prefix;
        var $RIR;
        var $DateAdded;
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
            $this->Name= isset($this->Name)?sanitize($this->Name):'';
	}

	function MakeDisplay(){
            $this->Name= isset($this->Name)?stripslashes($this->Name):'';
        }

	static function RowToObject($row){
            $m=new IpamAggreget();
            $m->PortID=$row["id"];
            $m->Prefix=$row["prefix"];
            $m->RIR=$row["rir_id"];
            $m->DateAdded=$row["date_added"];
            $m->Description=$row["description"];
            $m->Tag=$row["tag"];
            $m->RIRname = isset($row["rir_name"])?$row["rir_name"]:'';
            $m->Created_at=$row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();
            
            return $m;
	}

        static function RowToSearchObject($row){
            $m=new IpamAggreget();
            $m->PortID=$row["id"];
            $m->Prefix=$row["prefix"];
            $m->RIR=$row["rir_id"];
            $m->DateAdded=$row["date_added"];
            $m->Description=$row["description"];
            $m->Tag=$row["tag"];
            $m->RIRname = $row["rir_name"];
            $m->Created_at=$row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();
            
            unset($m->PortID);
            unset($m->RIR);
            
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
                
		$sql="SELECT a.*,r.name as rir_name 
                FROM ipam_aggregate a 
                LEFT JOIN ipam_rir r ON(r.id=a.rir_id) 
                WHERE a.is_deleted='N' $sqlextend ORDER BY a.id ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["AggregateID"]]=IpamAggreget::RowToSearchObject($row);
                    }else{
                        $dcList[]=IpamAggreget::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_aggregate WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamAggreget::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_aggregate WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamAggreget::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIpamAggregetList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_aggregate WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamAggreget::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamAggreget::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        // Function for list page
        static function GetAggreegetListRows($filter){
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
            if(isset($filter['aggreget'])){
                $incr .= " AND a.id =".$filter['aggreget'];
            }
            if(isset($filter['rir'])){
                $incr .= " AND a.rir_id =".$filter['rir'];
            }

            $sql="SELECT a.*,r.name as rir_name 
                FROM ipam_aggregate a 
                LEFT JOIN ipam_rir r ON(r.id=a.rir_id) 
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamAggreget::RowToObject($row);
                }else{
                    $RowList[]= IpamAggreget::RowToObject($row);
                }
            } }
            
            return $RowList;
	}
        
        // Function for detail page
        static function GetAggreegetOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['aggreget'])){
                $incr .= " AND a.id =".$filter['aggreget'];
            }
            
            $sql="SELECT a.*,r.name as rir_name 
                FROM ipam_aggregate a 
                LEFT JOIN ipam_rir r ON(r.id=a.rir_id) 
                WHERE a.is_deleted='N' ".$incr."";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamAggreget::RowToObject($row);
                }else{
                    $RowList[]= IpamAggreget::RowToObject($row);
                }
            } }
            return $RowList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashIpamAggregetList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_aggreeget
                FROM ipam_aggregate a 
                LEFT JOIN ipam_rir r ON(r.id=a.rir_id) WHERE a.is_deleted='N';";
            
            $AggreegetList = array();
            $result_AggreegetList = $dbh->query($sql);
            $AggreegetList = $result_AggreegetList ? $result_AggreegetList->fetch() : array();
            
            return $AggreegetList;
	}
        
	function CreateObject(){
            
            global $dbh;

            $this->MakeSafe();
            
            $dateAdded = date("Y-m-d",strtotime($this->DateAdded));
            
            $sql="INSERT INTO ipam_aggregate SET prefix=\"$this->Prefix\", rir_id=\"$this->RIR\", date_added='".$dateAdded."', description=\"$this->Description\", tag=\"$this->Tag\";";
            
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

            $sql="UPDATE ipam_aggregate SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $dateAdded = date("Y-m-d",strtotime($this->DateAdded));
            
            $sql="UPDATE ipam_aggregate SET prefix=\"$this->Prefix\", rir_id=\"$this->RIR\", date_added='".$dateAdded."', description=\"$this->Description\", tag=\"$this->Tag\" WHERE id=$this->PortID;";
            
            $old=new IpamAggreget();
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
            if(isset($filter['aggreget'])){
                $incr .= " AND a.id =".$filter['aggreget'];
            }
            if(isset($filter['rir'])){
                $incr .= " AND a.rir_id =".$filter['rir'];
            }

            $sql="SELECT a.*,r.name as rir_name 
                FROM ipam_aggregate a 
                LEFT JOIN ipam_rir r ON(r.id=a.rir_id) 
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
                
            $RowList=array();
            $RowListObj=array();
            
            $result = $dbh->query($sql); if ($result) {             
            foreach($result as $row) {
                if($indexbyid){
                    $RowListObj[$row['PortID']]= IpamAggreget::RowToObject($row);
                }else{
                    $RowListObj[]= IpamAggreget::RowToObject($row);
                }
            } }
            $result = json_decode(json_encode($RowListObj), true);
            
            // XLS CODE START
            // filename for download
            $filename = "aggregates_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Aggregate".$tab_sep."RIR".$tab_sep."Prefixes".$tab_sep."Added".$tab_sep."Description".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Prefix'].$tab_sep.$row['RIRname'].$tab_sep.$row['Prefix'].$tab_sep.date("m/d/Y",strtotime($row['DateAdded'])).$tab_sep.$row['Description'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
