<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class IPv4 {
	var $PortID;
	var $Name;
        var $Prefix;
        var $Vlan;
        var $Tag;
	
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
            $m=new IPv4();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Prefix=$row["ip"];
            $m->Vlan=$row["vlan"];
            $m->Tag=$row["tags"];
            
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

            $sql="SELECT * FROM ipv4network WHERE id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IPv4::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipv4network WHERE ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IPv4::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIPv4List($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipv4network ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IPv4::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IPv4::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
	
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            $sql="INSERT INTO ipv4network SET name=\"$this->Name\", ip=\"$this->Prefix\", vlan=\"$this->Vlan\", tags=\"$this->Tag\";";
            
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

            $sql="DELETE FROM ipv4network WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();

            $sql="UPDATE ipv4network SET name=\"$this->Name\", ip=\"$this->Prefix\", vlan=\"$this->Vlan\", tags=\"$this->Tag\" WHERE id=$this->PortID;";
            
            $old=new IPv4();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
