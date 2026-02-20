<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/
class ConnectorComp {
	var $PortID;
	var $Name;
        var $Type;
        var $Connector;
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
            $m=new ConnectorComp();
            $m->PortID=$row["id"];
            if($row["Label"] != "")
            {
                $m->Name=$row["Label"];
            } else {
                $m->Name=$row["name"];
            }
            if($row["connector"] != ""){
                $m->Connector=$row["connector"];
            } else {
                $m->Connector=$row["connector_id"];
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

            $sql="SELECT * FROM ConnectorComp WHERE id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConnectorComp::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM ConnectorComp WHERE ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(ConnectorComp::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetConnectorCompList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM ConnectorComp ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=ConnectorComp::RowToObject($row);
                    }else{
                            $ManufacturerList[]=ConnectorComp::RowToObject($row);
                    }
            } }

            return $ManufacturerList;
	}
        
        static function GetConnectorList(){
            global $dbh;

            $sql="SELECT * FROM patchcableconnector ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=ConnectorComp::RowToObject($row);
                    }else{
                            $ManufacturerList[]=ConnectorComp::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        static function GetCableList(){
            global $dbh;

            $sql="SELECT * FROM PatchCableType ORDER BY id ASC;";
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=ConnectorComp::RowToObject($row);
                    }else{
                            $ManufacturerList[]=ConnectorComp::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
	
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            $sql="INSERT INTO ConnectorComp SET name=\"$this->Name\", cable_type_id=\"$this->Cable_type\", connector_id=\"$this->Connector\";";
            
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

            $sql="DELETE FROM ConnectorComp WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();

            $sql="UPDATE ConnectorComp SET name=\"$this->Name\", cable_type_id=\"$this->Cable_type\", connector_id=\"$this->Connector\" WHERE id=$this->PortID;";
            
            $old=new ConnectorComp();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
}
?>
