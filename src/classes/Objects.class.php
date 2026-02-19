<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class Objects {
	var $ObjectID;
	var $Name;
	
	public function __construct($manufacturerid=false){
		if($manufacturerid){
			$this->ObjectID=$manufacturerid;
		}
		return $this;
	}

	function MakeSafe(){
		$this->ObjectID=intval($this->ObjectID);
		$this->Name=sanitize($this->Name);
	}

	function MakeDisplay(){
		$this->Name=stripslashes($this->Name);
	}

	static function RowToObject($row){
		$m=new Objects();
		$m->ObjectID=$row["id"];
		$m->Name=$row["name"];
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

		$sql="SELECT * FROM Object WHERE id=$this->ObjectID;";

		if($row=$this->query($sql)->fetch()){
			foreach(Objects::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM Object WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(Objects::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetObjectList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM Object ORDER BY name ASC;";

		$ManufacturerList=array();
		foreach($dbh->query($sql) as $row){
                    	if($indexbyid){
				$ManufacturerList[$row['ObjectID']]=Objects::RowToObject($row);
			}else{
				$ManufacturerList[]=Objects::RowToObject($row);
			}
		}
                
		return $ManufacturerList;
	}
	
	function CreateObject(){
		global $dbh;
		
		$this->MakeSafe();
                
		$sql="INSERT INTO Object SET Name=\"$this->Name\";";
                
		if(!$dbh->exec($sql)){
			error_log( "SQL Error: " . $sql );
			return false;
		}else{
			$this->ObjectID=$dbh->lastInsertID();
			(class_exists('LogActions'))?LogActions::LogThis($this):'';
			$this->MakeDisplay();
			return true;
		}
	}

	function DeleteObject($TransferTo=null){
		$this->MakeSafe();

		$sql="DELETE FROM Object WHERE id=$this->ObjectID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();

		$sql="UPDATE Object SET name=\"$this->Name\" WHERE id=$this->ObjectID;";

		$old=new Objects();
		$old->ObjectID=$this->ObjectID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
		return $this->query($sql);
	}
}
?>
