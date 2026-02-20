<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class IPv4Allocation {
        
	var $MainID;
	var $Name;
        var $IpID;
        var $ObjectID;
	
	public function __construct($manufacturerid=false){
            if($manufacturerid){
                $this->MainID=$manufacturerid;
            }
            return $this;
	}

	function MakeSafe(){
            $this->MainID=intval($this->MainID);
            $this->Name=sanitize($this->Name);
	}

	function MakeDisplay(){
            $this->Name=stripslashes($this->Name);
        }

	static function RowToObject($row){
            $m=new IPv4Allocation();
            $m->MainID=$row["id"];
            $m->Name=$row["name"];
            $m->IpID=$row["ipv4_id"];
            $m->ObjectID=$row["object_id"];
            
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

            $sql="SELECT * FROM ipv4allocation WHERE id=$this->MainID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IPv4Allocation::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipv4allocation WHERE ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IPv4Allocation::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIPv4AllocationList($indexbyid=false){
            global $dbh;
           
            $sql="SELECT * FROM ipv4allocation ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['MainID']]=IPv4Allocation::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IPv4Allocation::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        static function GetObjectList($indexbyid=false){
            global $dbh;
           
            $sql="SELECT * FROM Object ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['ObjID']]=Objects::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Objects::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        static function GetIPv4ID($ip_id=''){
            global $dbh;
            $incr = "";
            if($ip_id != ""){
                $incr = "id=".$ip_id."";
            } else {
                $incr = "1";
            }
            $sql="SELECT * FROM ipv4network WHERE {$incr} ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['IPVID']]=IPv4::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IPv4::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
	
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            $sql="INSERT INTO ipv4allocation SET name=\"$this->Name\", ipv4_id=\"$this->IpID\", object_id=\"$this->ObjectID\";";
            
            if(!$dbh->exec($sql)){
                    error_log( "SQL Error: " . $sql );
                    return false;
            }else{
                    $this->MainID=$dbh->lastInsertID();
                    (class_exists('LogActions'))?LogActions::LogThis($this):'';
                    $this->MakeDisplay();
                    return true;
            }
	}

	function DeleteObject($TransferTo=null){
            $this->MakeSafe();

            $sql="DELETE FROM ipv4allocation WHERE id=$this->MainID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();

            $sql="UPDATE ipv4allocation SET name=\"$this->Name\", ipv4_id=\"$this->IpID\", object_id=\"$this->ObjectID\" WHERE id=$this->MainID;";
            
            $old=new IPv4Allocation();
            $old->MainID=$this->MainID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
