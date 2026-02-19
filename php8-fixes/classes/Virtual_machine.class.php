<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Virtual_machine {
        
	var $PortID;
	var $Name;
        var $Role;
        var $Group;
        var $Cluster;
        var $Status;
        var $Platform;
        var $Ipv_4;
        var $Ipv_6;
        var $Vcpus;
        var $Memory;
        var $Disk;
        var $Context;
        var $Comment;
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
            $m=new Virtual_machine();
            $m->PortID = $row["id"];
            $m->Name = $row["name"];
            $m->Role = $row["role_id"];
            $m->Group = $row["cluster_group_id"];
            $m->Cluster = $row["cluster_id"];
            $m->Status = $row["status"];
            $m->Platform = $row["platform"];
            $m->Ipv_4 = $row["ipv_4"];
            $m->Ipv_6 = $row["ipv_6"];
            $m->Vcpus = $row["vcpus"];
            $m->Memory = $row["memory"];
            $m->Disk = $row["disk"];
            $m->Context = $row["context"];
            $m->Comment = $row["comment"];
            $m->Tag = $row["tag"];
            $m->Role_name = isset($row["role_name"])?$row["role_name"]:'';
            $m->Group_name = isset($row["group_name"])?$row["group_name"]:'';
            $m->Cluster_name = isset($row["cluster_name"])?$row["cluster_name"]:'';
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Virtual_machine();
            $m->PortID = $row["id"];
            $m->Name = $row["name"];
            $m->Role = $row["role_id"];
            $m->Group = $row["cluster_group_id"];
            $m->Cluster = $row["cluster_id"];
            $m->Status = $row["status"];
            $m->Platform = $row["platform"];
            $m->Ipv_4 = $row["ipv_4"];
            $m->Ipv_6 = $row["ipv_6"];
            $m->Vcpus = $row["vcpus"];
            $m->Memory = $row["memory"];
            $m->Disk = $row["disk"];
            $m->Context = $row["context"];
            $m->Comment = $row["comment"];
            $m->Tag = $row["tag"];
            $m->Role_name = $row["role_name"];
            $m->Group_name = $row["group_name"];
            $m->Cluster_name = $row["cluster_name"];
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Role);
            unset($m->Group);
            unset($m->Cluster);
            
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
                
		$sql="SELECT a.*,r.name as role_name, c.name as cluster_name, g.name as group_name
                FROM virtual_machine a
                LEFT JOIN ipam_role r ON(r.id=a.role_id)
                LEFT JOIN cluster c ON(c.id=cluster_id)
                LEFT JOIN cluster_group g ON(g.id=cluster_group_id)
                WHERE a.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
                                $dcList[$row["MachineID"]]=Virtual_machine::RowToSearchObject($row);
			}else{
                            
                                $dcList[]=Virtual_machine::RowToSearchObject($row);
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

            $sql="SELECT * FROM virtual_machine WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                foreach(Virtual_machine::RowToObject($row) as $prop => $value){
                        $this->$prop=$value;
                }
                return true;
            } else {
                return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM virtual_machine WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Virtual_machine::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetVirtual_machineList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM virtual_machine WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Virtual_machine::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Virtual_machine::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        // Function for detail page
        static function GetVirtual_machineOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['virtual_machine'])){
                $incr .= " AND a.id =".$filter['virtual_machine'];
            }
            
            $sql="SELECT a.*,r.name as role_name, c.name as cluster_name, g.name as group_name
                FROM virtual_machine a
                LEFT JOIN ipam_role r ON(r.id=a.role_id)
                LEFT JOIN cluster c ON(c.id=cluster_id)
                LEFT JOIN cluster_group g ON(g.id=cluster_group_id)
                WHERE a.is_deleted='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= Virtual_machine::RowToObject($row);
                }else{
                    $RowList[]= Virtual_machine::RowToObject($row);
                }
            }
            return $RowList;
	}
        
        // Function for list page
        static function GetVirtualMachineListRows($filter){
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
            if(isset($filter['machine'])){
                $incr .= " AND a.id =".$filter['machine'];
            }
            if(isset($filter['role'])){
                $incr .= " AND a.role_id =".$filter['role'];
            }
            if(isset($filter['group'])){
                $incr .= " AND a.cluster_group_id =".$filter['group'];
            }
            if(isset($filter['cluster'])){
                $incr .= " AND a.cluster_id =".$filter['cluster'];
            }
            if(isset($filter['status'])){
                $incr .= " AND a.status ='".$filter['status']."'";
            }
            if(isset($filter['platform'])){
                $incr .= " AND a.platform ='".$filter['platform']."'";
            }

            $sql="SELECT a.*,r.name as role_name, c.name as cluster_name, g.name as group_name
                FROM virtual_machine a
                LEFT JOIN ipam_role r ON(r.id=a.role_id)
                LEFT JOIN cluster c ON(c.id=cluster_id)
                LEFT JOIN cluster_group g ON(g.id=cluster_group_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
              
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= Virtual_machine::RowToObject($row);
                }else{
                    $RowList[]= Virtual_machine::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashVirtual_machineList(){
            global $dbh;
            
            $sql="SELECT COUNT(*) as total_machine
                FROM virtual_machine a
                LEFT JOIN ipam_role r ON(r.id=a.role_id)
                LEFT JOIN cluster c ON(c.id=cluster_id)
                LEFT JOIN cluster_group g ON(g.id=cluster_group_id)
                WHERE a.is_deleted='N'";
            
            $AggreegetList = array();
            $AggreegetList = $dbh->query($sql)->fetch();
            
            return $AggreegetList;
	}
        
	function CreateObject(){
            
            global $dbh;

            $this->MakeSafe();
            
            $created = date("Y-m-d");
            
            $sql="INSERT INTO virtual_machine SET name=\"$this->Name\", role_id=\"$this->Role\", cluster_group_id=\"$this->Group\", cluster_id=\"$this->Cluster\", status=\"$this->Status\", platform=\"$this->Platform\", ipv_4=\"$this->Ipv_4\", ipv_6=\"$this->Ipv_6\", vcpus=\"$this->Vcpus\", memory=\"$this->Memory\", disk=\"$this->Disk\", context=\"$this->Context\", comment=\"$this->Comment\", tag=\"$this->Tag\", created='".$created."';";
            
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

            $sql="UPDATE virtual_machine SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $created = date("Y-m-d");
            
            $sql="UPDATE virtual_machine SET name=\"$this->Name\", role_id=\"$this->Role\", cluster_group_id=\"$this->Group\", cluster_id=\"$this->Cluster\", status=\"$this->Status\", platform=\"$this->Platform\", ipv_4=\"$this->Ipv_4\", ipv_6=\"$this->Ipv_6\", vcpus=\"$this->Vcpus\", memory=\"$this->Memory\", disk=\"$this->Disk\", context=\"$this->Context\", comment=\"$this->Comment\", tag=\"$this->Tag\", last_updated='".$created."' WHERE id=$this->PortID;";
            
            $old=new Virtual_machine();
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
            if(isset($filter['machine'])){
                $incr .= " AND a.id =".$filter['machine'];
            }
            if(isset($filter['role'])){
                $incr .= " AND a.role_id =".$filter['role'];
            }
            if(isset($filter['group'])){
                $incr .= " AND a.cluster_group_id =".$filter['group'];
            }
            if(isset($filter['cluster'])){
                $incr .= " AND a.cluster_id =".$filter['cluster'];
            }
            if(isset($filter['status'])){
                $incr .= " AND a.status ='".$filter['status']."'";
            }
            if(isset($filter['platform'])){
                $incr .= " AND a.platform ='".$filter['platform']."'";
            }

            $sql="SELECT a.*,r.name as role_name, c.name as cluster_name, g.name as group_name
                FROM virtual_machine a
                LEFT JOIN ipam_role r ON(r.id=a.role_id)
                LEFT JOIN cluster c ON(c.id=cluster_id)
                LEFT JOIN cluster_group g ON(g.id=cluster_group_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by}";
              
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $RowList[$row['PortID']]= Virtual_machine::RowToObject($row);
                }else{
                    $RowList[]= Virtual_machine::RowToObject($row);
                }
            }
            
            $result = json_decode(json_encode($RowList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Virtual_machine_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Role".$tab_sep."Cluster Group".$tab_sep."Cluster".$tab_sep."Status".$tab_sep."Platform".$tab_sep."VCPUs".$tab_sep."Memory".$tab_sep."Disk".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Role_name'].$tab_sep.$row['Group_name'].$tab_sep.$row['Cluster_name'].$tab_sep.$row['Status'].$tab_sep.$row['Platform'].$tab_sep.$row['Vcpus'].$tab_sep.$row['Memory'].$tab_sep.$row['Disk'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
