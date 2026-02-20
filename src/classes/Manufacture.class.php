<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Manufacture {
        
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
            $m=new Manufacture();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            
            $m->MakeDisplay();

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
	
	// Wrapper to make this method like the other classes
	function GetObject(){
            return $this->GetOrderByID();
	}
	
	function GetOrderByID(){
            $this->MakeSafe();

            $sql="SELECT * FROM manufacture WHERE id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(Manufacture::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM manufacture WHERE ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                foreach(Manufacture::RowToObject($row) as $prop => $value){
                        $this->$prop=$value;
                }	
                return true;
            }else{
                return false;
            }
	}
	
        static function GetManufactureList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM manufacture ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql);
            if ($result) {
                foreach($result as $row){
                        if($indexbyid){
                                $ManufacturerList[$row['PortID']]=Manufacture::RowToObject($row);
                        }else{
                                $ManufacturerList[]=Manufacture::RowToObject($row);
                        }
                }
            }
            return $ManufacturerList;
	}
        
        // Function for list page
        static function GetRoleListRows($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['sort_on'])){
                $sort_on = $filter['sort_on'];
            }
            if(isset($filter['sort_by'])){
                $sort_by = $filter['sort_by'];
            }
            if(isset($filter['name'])){
                $incr .= " AND r.id =".$filter['name'];
            }
            if(isset($filter['slug'])){
                $incr .= " AND r.slug ='".$filter['slug']."'";
            }

            $sql="SELECT r.* FROM manufacture r WHERE r.id>0 ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $RowList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                if($indexbyid){
                    $RowList[$row['PortID']]= Manufacture::RowToObject($row);
                }else{
                    $RowList[]= Manufacture::RowToObject($row);
                }
            } }
            return $RowList;
	}
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashIpamRoleList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_role FROM manufacture";
            
            $IpamRoleList = array();
            $result_IpamRoleList = $dbh->query($sql);
            $IpamRoleList = $result_IpamRoleList ? $result_IpamRoleList->fetch() : array();
            
            return $IpamRoleList;
	}
        
	function CreateObject($params = array()){
            global $dbh;
            
            if(!empty($params)){
                $this->Name = $params['manufacture_name'];
            }
            
            $this->MakeSafe();
            $created_at = date("Y-m-d");
            
            $sql="INSERT INTO manufacture SET name=\"$this->Name\", slug=\"$this->Slug\", created=\"$created_at\";";
            
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

            $sql="DELETE FROM manufacture WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $update_at = date("Y-m-d");
            
            $sql="UPDATE manufacture SET name=\"$this->Name\", slug=\"$this->Slug\", last_updated=\"$update_at\" WHERE id=$this->PortID;";
            
            $old=new Manufacture();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
