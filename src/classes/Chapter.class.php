<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class Chapter {
	var $ChapterID;
	var $Name;
	
	public function __construct($manufacturerid=false){
		if($manufacturerid){
			$this->ChapterID=$manufacturerid;
		}
		return $this;
	}

	function MakeSafe(){
		$this->ChapterID=intval($this->ChapterID);
		$this->Name=sanitize($this->Name);
	}

	function MakeDisplay(){
		$this->Name=stripslashes($this->Name);      
	}

	static function RowToObject($row){
		$m=new Chapter();
		$m->ChapterID=$row["id"];
                if($row["Label"] != "")
                {
                    $m->Name=$row["Label"];
                } else {
                    $m->Name=$row["name"];
                }
                
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

		$sql="SELECT * FROM chapter WHERE id=$this->ChapterID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(Chapter::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM chapter WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(Chapter::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetChapterList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM chapter ORDER BY id ASC;";

		$ManufacturerList=array();
		$result = $dbh->query($sql); if ($result) { 		foreach($result as $row) {
                    if($indexbyid){
                        $ManufacturerList[$row['ChapterID']]=Chapter::RowToObject($row);
                    }else{
                        $ManufacturerList[]=Chapter::RowToObject($row);
                    }
		} }
                return $ManufacturerList;
	}
        
        static function GetPortList(){
		global $dbh;

		$sql="SELECT * FROM fac_ports ORDER BY DeviceID ASC;";

		$ManufacturerList=array();
		$result = $dbh->query($sql); if ($result) { 		foreach($result as $row) {
                    	if($indexbyid){
				$ManufacturerList[$row['ChapterID']]=Chapter::RowToObject($row);
			}else{
				$ManufacturerList[]=Chapter::RowToObject($row);
			}
		} }
                
                return $ManufacturerList;
	}
	
	function CreateObject(){
		global $dbh;
		
		$this->MakeSafe();
                
                $sql="INSERT INTO chapter SET name=\"$this->Name\";";
		
		if(!$dbh->exec($sql)){
			error_log( "SQL Error: " . $sql );
			return false;
		}else{
			$this->ChapterID=$dbh->lastInsertID();
			(class_exists('LogActions'))?LogActions::LogThis($this):'';
			$this->MakeDisplay();
			return true;
		}
	}

	function DeleteObject($TransferTo=null){
		$this->MakeSafe();

		$sql="DELETE FROM chapter WHERE id=$this->ChapterID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();

		$sql="UPDATE chapter SET name=\"$this->Name\" WHERE id=$this->ChapterID;";

		$old=new Chapter();
		$old->ChapterID=$this->ChapterID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
