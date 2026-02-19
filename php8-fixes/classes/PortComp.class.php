<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class PortComp {
	var $PortID;
	var $Name;
        var $FromInterfaceID;
        var $ToInterfaceID;
	
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
		$m=new PortComp();
		$m->PortID=$row["id"];
                if($row["Label"] != "")
                {
                    $m->Name=$row["Label"];
                } else {
                    $m->Name=$row["name"];
                }
                $m->FromInterfaceID=$row["type1"];
                $m->ToInterfaceID=$row["type2"];
                
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

		$sql="SELECT * FROM PortCompat WHERE id=$this->PortID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(PortComp::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM PortCompat WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(PortComp::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetPortCompList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM PortCompat ORDER BY id ASC;";

		$ManufacturerList=array();
		foreach($dbh->query($sql) as $row){
                    	if($indexbyid){
				$ManufacturerList[$row['PortID']]=PortComp::RowToObject($row);
			}else{
				$ManufacturerList[]=PortComp::RowToObject($row);
			}
		}
                
		return $ManufacturerList;
	}
        
        static function GetPortList(){
		global $dbh;

		$sql="SELECT * FROM fac_ports ORDER BY DeviceID ASC;";

		$ManufacturerList=array();
		foreach($dbh->query($sql) as $row){
                    	if($indexbyid){
				$ManufacturerList[$row['PortID']]=PortComp::RowToObject($row);
			}else{
				$ManufacturerList[]=PortComp::RowToObject($row);
			}
		}
                
                return $ManufacturerList;
	}
	
	function CreateObject(){
		global $dbh;
		
		$this->MakeSafe();
                
                $sql="INSERT INTO PortCompat SET name=\"$this->Name\", type1=$this->FormInterfaceID,type2=$this->ToInterfaceID;";
		
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

		$sql="DELETE FROM PortCompat WHERE id=$this->PortID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();

		$sql="UPDATE PortCompat SET name=\"$this->Name\", type1=$this->FormInterfaceID,
		type2=$this->ToInterfaceID WHERE id=$this->PortID;";

		$old=new PortComp();
		$old->PortID=$this->PortID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
