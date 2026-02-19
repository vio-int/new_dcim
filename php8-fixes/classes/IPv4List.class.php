<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class IPv4List {
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
            $m=new IPv4List();
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
                foreach(IPv4List::RowToObject($row) as $prop => $value){
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
                foreach(IPv4List::RowToObject($row) as $prop => $value){
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
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]=IPv4List::RowToObject($row);
                }else{
                    $ManufacturerList[]=IPv4List::RowToObject($row);
                }
            }
            
            return $ManufacturerList;
	}
}
?>
