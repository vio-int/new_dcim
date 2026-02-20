<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class Dictionary {
	var $DictionaryID;
	var $Name;
        var $ChapterID;
        var $Word;
        var $Refcnt;
        var $Key;
        
	public function __construct($manufacturerid=false){
		if($manufacturerid){
			$this->DictionaryID=$manufacturerid;
		}
		return $this;
	}

	function MakeSafe(){
		$this->DictionaryID=intval($this->DictionaryID);
		$this->Name=sanitize($this->Name);
	}

	function MakeDisplay(){
		$this->Name=stripslashes($this->Name);       
	}

	static function RowToObject($row){
		$m=new Dictionary();
		$m->DictionaryID=$row["id"];
                if($row["Label"] != "")
                {
                    $m->Name=$row["Label"];
                } else {
                    $m->Name=$row["name"];
                }
                $m->ChapterID=$row["chapter_id"];
                $m->Word=$row["word"];
                $m->Key=$row["dictionary_key"];
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

		$sql="SELECT * FROM dictionary WHERE id=$this->DictionaryID;";
                
		if($row=$this->query($sql)->fetch()){
			foreach(Dictionary::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	function GetObjectByName(){
		$this->MakeSafe();

		$sql="SELECT * FROM dictionary WHERE ucase(Name)=ucase('".$this->Name."');";

		if($row=$this->query($sql)->fetch()){
			foreach(Dictionary::RowToObject($row) as $prop => $value){
				$this->$prop=$value;
			}	
			return true;
		}else{
			return false;
		}
	}
	
	static function GetDictionaryList($indexbyid=false){
		global $dbh;

		$sql="SELECT * FROM dictionary ORDER BY id ASC;";

		$ManufacturerList=array();
		$result = $dbh->query($sql); if ($result) { 		foreach($result as $row) {
                    if($indexbyid){
                        $ManufacturerList[$row['DictionaryID']]=Dictionary::RowToObject($row);
                    }else{
                        $ManufacturerList[]=Dictionary::RowToObject($row);
                    }
		} }
                return $ManufacturerList;
	}
        
        static function GetChapterList(){
		global $dbh;

		$sql="SELECT * FROM Chapter ORDER BY id ASC;";

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
                
                $sql="INSERT INTO dictionary SET name=\"$this->Name\", chapter_id=\"$this->ChapterID\", word=\"$this->Word\", dictionary_key=\"$this->Key\", refcnt=\"$this->Refcnt\";";
		
		if(!$dbh->exec($sql)){
			error_log( "SQL Error: " . $sql );
			return false;
		}else{
			$this->DictionaryID=$dbh->lastInsertID();
			(class_exists('LogActions'))?LogActions::LogThis($this):'';
			$this->MakeDisplay();
			return true;
		}
	}

	function DeleteObject($TransferTo=null){
		$this->MakeSafe();

		$sql="DELETE FROM dictionary WHERE id=$this->DictionaryID;";

		(class_exists('LogActions'))?LogActions::LogThis($this):'';
		return $this->query($sql);
	}

	function UpdateObject(){
            	$this->MakeSafe();
                
		$sql="UPDATE dictionary SET name=\"$this->Name\", chapter_id=\"$this->ChapterID\", word=\"$this->Word\", dictionary_key=\"$this->Key\", refcnt=\"$this->Refcnt\" WHERE id=$this->DictionaryID;";

		$old=new Dictionary();
		$old->DictionaryID=$this->DictionaryID;
		$old->GetOrderByID();

		$this->MakeDisplay();
		(class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
                //echo $sql;exit;
		return $this->query($sql);
	}
}
?>
