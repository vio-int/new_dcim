<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class PortConnectors {
	var $PortID;
	var $Connector;
        
	
	public function __construct($manufacturerid=false){
		if($manufacturerid){
			$this->PortID=$manufacturerid;
		}
		return $this;
	}

	function MakeSafe(){
		$this->PortID=intval($this->PortID);
		$this->Connector=sanitize($this->Connector);
	}

	function MakeDisplay(){
		$this->Connector=stripslashes($this->Connector);
               
                
	}

	static function RowToObject($row){
		$m=new PortConnectors();
		$m->PortID=$row["id"];
                $m->Connector=$row["connector"];
                
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

		$sql="SELECT * FROM patchcableconnector WHERE id=$this->PortID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(PortConnectors::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM patchcableconnector WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(PortConnectors::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetPortConnectorsList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM patchcableconnector ORDER BY id ASC;";

		$ManufacturerList=array();
		$result = $dbh->query($sql); if ($result) { 		foreach($result as $row) {
                    	if($indexbyid){
				$ManufacturerList[$row['PortID']]=PortConnectors::RowToObject($row);
			}else{
				$ManufacturerList[]=PortConnectors::RowToObject($row);
			}
		} }
                //print_r($ManufacturerList);exit;
		return $ManufacturerList;
	}
        
        function CreateObject(){
		global $dbh;
		
		$this->MakeSafe();
                
                $sql="INSERT INTO patchcableconnector SET connector=\"$this->Connector\";";
		
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

		$sql="DELETE FROM patchcableconnector WHERE id=$this->PortID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();

		$sql="UPDATE patchcableconnector SET connector=\"$this->Connector\" WHERE id=$this->PortID;";
                //echo $sql;exit;
		$old=new PortConnectors();
		$old->PortID=$this->PortID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
