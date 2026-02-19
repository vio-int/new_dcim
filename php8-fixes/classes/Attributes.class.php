<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class Attributes {
	var $PortID;
	var $Name;
        var $Type;
        var $Dictionary;
	
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
		$m=new Attributes();
		$m->PortID=$row["id"];
                if($row["Label"] != "")
                {
                    $m->Name=$row["Label"];
                } else {
                    $m->Name=$row["name"];
                }
                $m->Type=$row["type"];
                $m->Dictionary=$row["applies_to"];
                
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

		$sql="SELECT * FROM attributes WHERE id=$this->PortID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(Attributes::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM attributes WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(Attributes::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetAttributesList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM attributes ORDER BY id ASC;";

		$ManufacturerList=array();
		foreach($dbh->query($sql) as $row){
                    	if($indexbyid){
				$ManufacturerList[$row['PortID']]=Attributes::RowToObject($row);
			}else{
				$ManufacturerList[]=Attributes::RowToObject($row);
			}
		}
                
		return $ManufacturerList;
	}
        
        static function GetDictionaryList(){
		global $dbh;

		$sql="SELECT * FROM dictionary ORDER BY id ASC;";

		$ManufacturerList=array();
		foreach($dbh->query($sql) as $row){
                    	if($indexbyid){
				$ManufacturerList[$row['PortID']]=Attributes::RowToObject($row);
			}else{
				$ManufacturerList[]=Attributes::RowToObject($row);
			}
		}
                
                return $ManufacturerList;
	}
	
	function CreateObject(){
		global $dbh;
		
		$this->MakeSafe();
                
                $sql="INSERT INTO attributes SET name=\"$this->Name\", type=\"$this->Type\", applies_to=\"$this->Dictionary\";";
		
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

		$sql="DELETE FROM attributes WHERE id=$this->PortID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();

		$sql="UPDATE attributes SET name=\"$this->Name\", type=\"$this->Type\", applies_to=\"$this->Dictionary\" WHERE id=$this->PortID;";
                //echo $sql;exit;
		$old=new Attributes();
		$old->PortID=$this->PortID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
