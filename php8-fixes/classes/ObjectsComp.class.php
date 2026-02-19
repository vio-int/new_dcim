<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class ObjectsComp {
	var $ObjectID;
	var $Name;
        var $parent_objtype_id;
        var $child_objtype_id;
	
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
		$m=new ObjectsComp();
		$m->ObjectID=$row["id"];
		$m->Name=$row["name"];
                $m->parent_objtype_id=$row["parent_objtype_id"];
                $m->child_objtype_id=$row["child_objtype_id"];
                
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

		$sql="SELECT * FROM ObjectParentCompat WHERE id=$this->ObjectID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(ObjectsComp::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM ObjectParentCompat WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(ObjectsComp::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetObjecCompList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM ObjectParentCompat ORDER BY name ASC;";

		$ManufacturerList=array();
		foreach($dbh->query($sql) as $row){
                    	if($indexbyid){
				$ManufacturerList[$row['ObjectID']]=ObjectsComp::RowToObject($row);
			}else{
				$ManufacturerList[]=ObjectsComp::RowToObject($row);
			}
		}
                
		return $ManufacturerList;
	}
        
        static function GetObjectList(){
		global $dbh;

		$sql="SELECT * FROM Object ORDER BY name ASC;";

		$ManufacturerList=array();
		foreach($dbh->query($sql) as $row){
                    	if($indexbyid){
				$ManufacturerList[$row['ObjectID']]=ObjectsComp::RowToObject($row);
			}else{
				$ManufacturerList[]=ObjectsComp::RowToObject($row);
			}
		}
                
		return $ManufacturerList;
	}
	
	function CreateObject(){
		global $dbh;
		
		$this->MakeSafe();
                
                $sql="INSERT INTO ObjectParentCompat SET name=\"$this->Name\", parent_objtype_id=$this->ParentObjectID,
		child_objtype_id=$this->ChildObjectID;";
		
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

		$sql="DELETE FROM ObjectParentCompat WHERE id=$this->ObjectID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();

		$sql="UPDATE ObjectParentCompat SET name=\"$this->Name\", parent_objtype_id=$this->ParentObjectID,
		child_objtype_id=$this->ChildObjectID WHERE id=$this->ObjectID;";

		$old=new ObjectsComp();
		$old->ObjectID=$this->ObjectID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
