<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class DeviceInterface {
        
    var $PortID;
    var $Name;
    var $Form_factor;
    var $Parent_lag;
    var $MTU;
    var $Mac_address;
    var $Description;
    var $Tag;
    var $Mode;
    var $Enable;
    var $Is_oob;
    var $Device;

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
        $m=new Service();
        $m->PortID=$row["id"];
        $m->Name=$row["name"];
        $m->Form_factor=$row["form_factor"];
        $m->Parent_lag=$row["parent_lag"];
        $m->MTU=$row["mtu"];
        $m->Mac_address=$row["mac_address"];
        $m->Description=$row["description"];
        $m->Tag=$row["tag"];
        $m->Mode = $row["mode"];
        $m->Enable=$row["is_enable"];
        $m->Is_oob=$row["is_oob"];
        $m->Device=$row["device_id"];
        $m->Created_at=$row["created"];
        $m->Updated_at=$row["last_updated"];

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

        $sql="SELECT * FROM device_interface WHERE is_deleted='N' AND id=$this->PortID;";

        if($row=$this->query($sql)->fetch()){
                foreach(DeviceInterface::RowToObject($row) as $prop => $value){
                        $this->$prop=$value;
                }
                return true;
        }else{
                return false;
        }
    }

    function GetObjectByName(){
        $this->MakeSafe();

        $sql="SELECT * FROM device_interface WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

        if($row=$this->query($sql)->fetch()){
                foreach(DeviceInterface::RowToObject($row) as $prop => $value){
                        $this->$prop=$value;
                }	
                return true;
        }else{
                return false;
        }
    }

    static function GetDeviceInterfaceList($indexbyid=false){
        global $dbh;

        $sql="SELECT * FROM device_interface WHERE is_deleted='N' ORDER BY id ASC;";

        $ManufacturerList=array();
        $result = $dbh->query($sql); if ($result) {         foreach($result as $row) {
                if($indexbyid){
                        $ManufacturerList[$row['PortID']]=DeviceInterface::RowToObject($row);
                }else{
                        $ManufacturerList[]=DeviceInterface::RowToObject($row);
                }
        } }

        return $ManufacturerList;
    }

    // Function for list page
    static function GetDeviceInterfaceListRows($filter){
        global $dbh;

        $limit = 15; 
        if (isset($_GET["page"])) {
            $page = $_GET["page"]; 
        } else { 
            $page=1;
        }
        $start_from = ($page-1) * $limit; 

        $incr = "";
        if(isset($filter['sort_on'])){
            $sort_on = $filter['sort_on'];
        }
        if(isset($filter['sort_by'])){
            $sort_by = $filter['sort_by'];
        }
        if(isset($filter['name'])){
            $incr .= " AND s.id =".$filter['name'];
        }
        if(isset($filter['machine'])){
            $incr .= " AND s.virtual_machine_id =".$filter['machine'];
        }
        if(isset($filter['device'])){
            $incr .= " AND s.device_id =".$filter['device'];
        } else {
            $incr .= " AND s.virtual_machine_id > 0";
        }
        if(isset($filter['port_type'])){
            $incr .= " AND s.protocol ='".$filter['port_type']."'";
        }

        $sql="SELECT s.*,v.name as machine_name
            FROM device_interface s
            LEFT JOIN virtual_machine v ON(v.id=s.virtual_machine_id)
            WHERE s.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";

        $ManufacturerList=array();
        $result = $dbh->query($sql); if ($result) {         foreach($result as $row) {
                if($indexbyid){
                        $ManufacturerList[$row['PortID']]=DeviceInterface::RowToObject($row);
                }else{
                        $ManufacturerList[]=DeviceInterface::RowToObject($row);
                }
        } }

        return $ManufacturerList;
    }

    // QUERY FOR DASHBOARD COUNTER
    static function GetDashDeviceInterfaceList(){
        global $dbh;

        $sql="SELECT COUNT(*) as total_interface
            FROM device_interface WHERE is_deleted='N';";

        $ServiceList = array();
        $result_ServiceList = $dbh->query($sql);
        $ServiceList = $result_ServiceList ? $result_ServiceList->fetch() : array();

        return $ServiceList;
    }

    function CreateObject(){
        global $dbh;

        $this->MakeSafe();
        $created_at = date('Y-m-d');
        
        if($this->Enable=="Y")
        {
            $is_enable = "Y";
        } else {
            $is_enable = "N";
        }
        if($this->Is_oob=="Y")
        {
            $is_oob = "Y";
        } else {
            $is_oob = "N";
        }
        
        $sql="INSERT INTO device_interface SET name=\"$this->Name\", device_id=\"$this->Device\", form_factor=\"$this->Form_factor\", parent_lag=\"$this->Parent_lag\", mtu=\"$this->MTU\", mac_address=\"$this->Mac_address\", description=\"$this->Description\", tag=\"$this->Tag\", mode=\"$this->Mode\", is_enable='".$is_enable."', is_oob='".$is_oob."', created='".$created_at."';";
        
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

        $sql="UPDATE device_interface SET is_deleted='Y' WHERE id=$this->PortID;";

        (class_exists('LogActions'))?LogActions::LogThis($this):'';
        return $this->query($sql);
    }

    function UpdateObject(){
        $this->MakeSafe();

        $last_updated = date('Y-m-d');
        
        if($this->Enable=="Y")
        {
            $is_enable = "Y";
        } else {
            $is_enable = "N";
        }
        if($this->Is_oob=="Y")
        {
            $is_oob = "Y";
        } else {
            $is_oob = "N";
        }
        
        $sql="UPDATE device_interface SET name=\"$this->Name\", device_id=\"$this->Device\", form_factor=\"$this->Form_factor\", parent_lag=\"$this->Parent_lag\", mtu=\"$this->MTU\", mac_address=\"$this->Mac_address\", description=\"$this->Description\", tag=\"$this->Tag\", mode=\"$this->Mode\", is_enable='".$is_enable."', is_oob='".$is_oob."', last_updated='".$last_updated."' WHERE id=$this->PortID;";

        $old=new Service();
        $old->PortID=$this->PortID;
        $old->GetOrderByID();

        $this->MakeDisplay();
        (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
        //echo $sql;exit;
        return $this->query($sql);
    }
}
?>
