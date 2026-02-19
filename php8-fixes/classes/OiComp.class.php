<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class OiComp {
	var $PortID;
	var $Name;
        var $Type;
        var $Inter_name;
        var $Cable_type;
	
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
            $m=new OiComp();
            $m->PortID=$row["id"];
            if($row["Label"] != "")
            {
                $m->Name=$row["Label"];
            } else {
                $m->Name=$row["name"];
            }
            if($row["inter_name"] != ""){
                $m->Inter_name=$row["inter_name"];
            } else {
                $m->Inter_name=$row["oi_id"];
            }
            if($row["pctype"] != "")
            {
                $m->Cable_type=$row["pctype"];
            } else {
                $m->Cable_type=$row["cable_type_id"];
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

            $sql="SELECT * FROM PatchCableOIFCompat WHERE id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(OiComp::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM PatchCableOIFCompat WHERE ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(OiComp::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetOiCompList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM PatchCableOIFCompat ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=OiComp::RowToObject($row);
                    }else{
                            $ManufacturerList[]=OiComp::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetOInterfaceList(){
            global $dbh;

            $sql="SELECT *, name as inter_name FROM port_outer_interface ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=OiComp::RowToObject($row);
                    }else{
                            $ManufacturerList[]=OiComp::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        static function GetCableList(){
            global $dbh;

            $sql="SELECT * FROM PatchCableType ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=OiComp::RowToObject($row);
                    }else{
                            $ManufacturerList[]=OiComp::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
	
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            $sql="INSERT INTO PatchCableOIFCompat SET name=\"$this->Name\", cable_type_id=\"$this->Cable_type\", oi_id=\"$this->Outer_inter\";";
            
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

            $sql="DELETE FROM PatchCableOIFCompat WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();

            $sql="UPDATE PatchCableOIFCompat SET name=\"$this->Name\", cable_type_id=\"$this->Cable_type\", oi_id=\"$this->Outer_inter\" WHERE id=$this->PortID;";
            
            $old=new OiComp();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
