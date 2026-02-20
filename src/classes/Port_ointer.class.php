<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class PortOinter {
	var $PortID;
	var $Name;
        var $Poi_key;
        var $Refcnt;
	
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
		$m=new PortOinter();
		$m->PortID=$row["id"];
                $m->Name=$row["name"];
                $m->Poi_key=$row["poi_key"];
                $m->Refcnt=$row["refcnt"];
                
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

		$sql="SELECT * FROM port_outer_interface WHERE id=$this->PortID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(PortOinter::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM port_outer_interface WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(PortOinter::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetPortOinterList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM port_outer_interface ORDER BY id ASC;";

		$ManufacturerList=array();
		$result = $dbh->query($sql); if ($result) { 		foreach($result as $row) {
                    	if($indexbyid){
				$ManufacturerList[$row['PortID']]=PortOinter::RowToObject($row);
			}else{
				$ManufacturerList[]=PortOinter::RowToObject($row);
			}
		} }
                //print_r($ManufacturerList);exit;
		return $ManufacturerList;
	}
        
        function CreateObject(){
		global $dbh;
		
		$this->MakeSafe();
                
                $sql="INSERT INTO port_outer_interface SET name=\"$this->Name\", poi_key=\"$this->Key\", refcnt=$this->Refcnt;";
		
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

		$sql="DELETE FROM port_outer_interface WHERE id=$this->PortID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();

		$sql="UPDATE port_outer_interface SET name=\"$this->Name\", poi_key=\"$this->Key\", refcnt=$this->Refcnt WHERE id=$this->PortID;";
                //echo $sql;exit;
		$old=new PortOinter();
		$old->PortID=$this->PortID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
