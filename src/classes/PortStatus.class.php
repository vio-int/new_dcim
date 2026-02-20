<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class PortStatus {
	var $PortID;
	var $Name;
        var $Status;
        
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
		$m=new PortStatus();
		$m->PortID=$row["id"];
                if($row["Label"] != "")
                {
                    $m->Name=$row["Label"];
                } else {
                    $m->Name=$row["name"];
                }
                $m->Status=$row["status"];
                
                
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

		$sql="SELECT * FROM fac_ports WHERE id=$this->PortID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(PortStatus::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM fac_ports WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(PortStatus::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetPortList(){
		global $dbh;

		$sql="SELECT * FROM fac_ports ORDER BY DeviceID ASC;";

		$ManufacturerList=array();
		$result = $dbh->query($sql); if ($result) { 		foreach($result as $row) {
                    	if($indexbyid){
				$ManufacturerList[$row['PortID']]=PortStatus::RowToObject($row);
			}else{
				$ManufacturerList[]=PortStatus::RowToObject($row);
			}
		} }
                
                return $ManufacturerList;
	}
	
	function UpdateObject(){
            	$this->MakeSafe();
                
		$sql="UPDATE fac_ports SET status='$this->Status' WHERE id=$this->PortID;";
                //echo $sql;exit;
		$old=new PortStatus();
		$old->PortID=$this->PortID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
