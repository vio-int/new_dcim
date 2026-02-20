<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamVRF {
        
	var $PortID;
	var $Name;
        var $RD;
        var $Enforce;
        var $Tag;
        var $Description;
	
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
            $m=new IpamVRF();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->RD=$row["rd"];
            $m->Description=$row["description"];
            $m->Enforce=$row["enforce_unique"];
            $m->Tag=$row["tag"];
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new IpamVRF();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->RD=$row["rd"];
            $m->Description=$row["description"];
            $m->Enforce=$row["enforce_unique"];
            $m->Tag=$row["tag"];
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
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
                
		$sql="SELECT v.* FROM ipam_vrf v WHERE v.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["vrfID"]]=IpamVRF::RowToSearchObject($row);
                    }else{
                        $dcList[]=IpamVRF::RowToSearchObject($row);
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

            $sql="SELECT * FROM ipam_vrf WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamVRF::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_vrf WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamVRF::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIpamVRFList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_vrf WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamVRF::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamVRF::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetVRFOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['vrf'])){
                $incr .= " AND v.id =".$filter['vrf'];
            }
            
            $sql="SELECT v.* FROM ipam_vrf v WHERE v.is_deleted='N' ".$incr."";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamVRF::RowToObject($row);
                }else{
                    $RowList[]= IpamVRF::RowToObject($row);
                }
            } }
            return $RowList;
	}
	
        // Function for list page
        static function GetIpamVRFListRows($filter){
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
            if(isset($filter['rd'])){
                $incr .= " AND v.rd ='".$filter['rd']."'";
            }

            $sql="SELECT v.* FROM ipam_vrf v WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamVRF::RowToObject($row);
                }else{
                    $RowList[]= IpamVRF::RowToObject($row);
                }
            } }
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashVRFList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_vrf FROM ipam_vrf WHERE is_deleted='N'";
            
            $IpamVRFList = array();
            $result_IpamVRFList = $dbh->query($sql);
            $IpamVRFList = $result_IpamVRFList ? $result_IpamVRFList->fetch() : array();
            
            return $IpamVRFList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            if($this->Enforce != "")
            {
                $enforce = $this->Enforce;
            } else {
                $enforce = "N";
            }
            $sql="INSERT INTO ipam_vrf SET name=\"$this->Name\", rd=\"$this->RD\", description=\"$this->Description\", tag=\"$this->Tag\", enforce_unique=\"$enforce\";";
            
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

            $sql="UPDATE ipam_vrf SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            if($this->Enforce != "")
            {
                $enforce = $this->Enforce;
            } else {
                $enforce = "N";
            }
            $sql="UPDATE ipam_vrf SET name=\"$this->Name\", rd=\"$this->RD\", enforce_unique=\"$enforce\", description=\"$this->Description\", tag=\"$this->Tag\" WHERE id=$this->PortID;";
            
            $old=new IpamVRF();
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
            if(isset($filter['rd'])){
                $incr .= " AND v.rd ='".$filter['rd']."'";
            }

            $sql="SELECT v.id, v.name, v.rd, v.description, v.enforce_unique, v.tag
                FROM ipam_vrf v WHERE v.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= IpamVRF::RowToObject($row);
                }else{
                    $RowList[]= IpamVRF::RowToObject($row);
                }
            } }
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "VRFs_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."RD".$tab_sep."Enforce unique space ?".$tab_sep."Description".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['RD'].$tab_sep.$row['Enforce'].$tab_sep.$row['Description'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
