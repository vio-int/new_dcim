<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Location {
        
	var $PortID;
	var $Name;
        var $Slug;
        var $Parent;
        
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
            $m=new Location();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Slug=$row["slug"];
            $m->Parent=$row["parent_id"];
            
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

            $sql="SELECT * FROM location WHERE id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(Location::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM location WHERE ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Location::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetLocationList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM location ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Location::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Location::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetParentLocationList(){
            global $dbh;
            $incr = "";
            
            if($_GET['PortID'] != "")
            {
                $incr = " AND id != {$_GET['PortID']}";
            }
            
            $sql="SELECT * FROM location WHERE id>0 ".$incr." ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Location::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Location::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashLocationList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_location FROM location";
            
            $LocationList= array();
            $result_LocationList = $dbh->query($sql);
            $LocationList = $result_LocationList ? $result_LocationList->fetch() : array();
            
            return $LocationList;
	}
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $level = 1;
            if($this->Parent != ""){
                $sql="SELECT * FROM location WHERE id=$this->Parent;";
                $row = $this->query($sql)->fetch();
                
                $level = $row['level'] + 1;  
            }
            $sql="INSERT INTO location SET name=\"$this->Name\", slug=\"$this->Slug\", parent_id=\"$this->Parent\", level=\"$level\", created='".date('Y-m-d')."';";
            
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

            $sql="DELETE FROM location WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $level = 1;
            if($this->Parent != ""){
                $sql="SELECT * FROM location WHERE id=$this->Parent;";
                $row = $this->query($sql)->fetch();
                
                $level = $row['level'] + 1;  
            }
            $sql="UPDATE location SET name=\"$this->Name\", slug=\"$this->Slug\", parent_id=\"$this->Parent\", level=\"$level\", last_updated='".date('Y-m-d')."' WHERE id=$this->PortID;";
            
            $old=new Location();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
