<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class IpamIPaddressBulk {
        
	var $PortID;
	var $Address;
        var $Status;
        var $Role;
        var $Vrf;
        var $Description;
	
	
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
            $m=new IpamIPaddressBulk();
            $m->PortID=$row["id"];
            $m->Address=$row["address"];
            $m->Status=$row["status"];
            $m->Role=$row["role_id"];
            $m->Vrf=$row["vrf_id"];
            $m->Description=$row["description"];
            
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

            $sql="SELECT * FROM ipam_ipaddressbulk WHERE id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamIPaddressBulk::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ipam_ipaddressbulk WHERE ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(IpamIPaddressBulk::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetIpamIPaddressBulkList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ipam_ipaddressbulk ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=IpamIPaddressBulk::RowToObject($row);
                    }else{
                            $ManufacturerList[]=IpamIPaddressBulk::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
	
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            $sql="INSERT INTO ipam_ipaddressbulk SET address=\"$this->Address\", status=\"$this->Status\", role_id=\"$this->Role\", vrf_id=\"$this->Vrf\", description=\"$this->Description\";";
            
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

            $sql="DELETE FROM ipam_ipaddressbulk WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();

            $sql="UPDATE ipam_ipaddressbulk SET address=\"$this->Address\", status=\"$this->Status\", role_id=\"$this->Role\", vrf_id=\"$this->Vrf\", description=\"$this->Description\" WHERE id=$this->PortID;";
            
            $old=new IpamIPaddressBulk();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
