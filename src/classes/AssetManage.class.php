<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class AssetManage {
        
	var $PortID;
	var $Name;
        var $Status;
        var $Label;
        var $Serial_no;
        var $Asset_tag;
        var $Primary_ip;
        var $Manufacture_date;
        var $Install_date;
        var $Company;
        var $Expiration_date;
        var $Rack;
        var $Device;
        var $Height;
        var $Position;
        var $Half_depth;
        var $Back_side;
        var $Data_ports;
        var $Watts;
        var $Weight;
        var $Power_connection;
        var $Device_role;
        var $SNMP_version;
        var $SNMP_community;
        var $SNMP_failure;
        var $Department;
	
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
            $m=new AssetManage();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Status=$row["status"];
            $m->Label=$row["label"];
            $m->Serial_no=$row["serial_no"];
            $m->Asset_tag=$row["asset_tag"];
            $m->Primary_ip=$row["primary_ip"];
            $m->Manufacture_date=$row["manufacture_date"];
            $m->Install_date=$row["install_date"];
            $m->Company=$row["company"];
            $m->Expiration_date=$row["expire_date"];
            $m->Rack=$row["rack_id"];
            $m->Device=$row["device_id"];
            $m->Height=$row["height"];
            $m->Position=$row["position"];
            $m->Half_depth=$row["half_depth"];
            $m->Back_side=$row["back_side"];
            $m->Data_ports=$row["data_ports"];
            $m->Watts=$row["watts"];
            $m->Weight=$row["weight"];
            $m->Power_connection=$row["power_connection"];
            $m->Device_role=$row["device_role"];
            $m->SNMP_version=$row["snmp_version"];
            $m->SNMP_community=$row["snmp_community"];
            $m->SNMP_failure=$row["snmp_failure"];
            $m->Rack_name=$row["rack_name"];
            $m->Device_name=$row["device_name"];
            $m->Department=$row["department_id"];
            $m->Department_name=$row["department_name"];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new AssetManage();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Status=$row["status"];
            $m->Label=$row["label"];
            $m->Serial_no=$row["serial_no"];
            $m->Asset_tag=$row["asset_tag"];
            $m->Primary_ip=$row["primary_ip"];
            $m->Manufacture_date=$row["manufacture_date"];
            $m->Install_date=$row["install_date"];
            $m->Company=$row["company"];
            $m->Expiration_date=$row["expire_date"];
            $m->Rack=$row["rack_id"];
            $m->Device=$row["device_id"];
            $m->Height=$row["height"];
            $m->Position=$row["position"];
            $m->Half_depth=$row["half_depth"];
            $m->Back_side=$row["back_side"];
            $m->Data_ports=$row["data_ports"];
            $m->Watts=$row["watts"];
            $m->Weight=$row["weight"];
            $m->Power_connection=$row["power_connection"];
            $m->Device_role=$row["device_role"];
            $m->SNMP_version=$row["snmp_version"];
            $m->SNMP_community=$row["snmp_community"];
            $m->SNMP_failure=$row["snmp_failure"];
            $m->Rack_name=$row["rack_name"];
            $m->Device_name=$row["device_name"];
            $m->Department=$row["department_id"];
            $m->Department_name=$row["department_name"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Rack);
            unset($m->Device);
            unset($m->Department);
            
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
        
        function Search($indexedbyid=false,$loose=false){
		$o=new stdClass();
		// Store any values that have been added before we make them safe 
		foreach($this as $prop => $val){
			if(isset($val)){
				$o->$prop=$val;
			}
		}

		// Make everything safe for us to search with
		$this->MakeSafe();

		// This will store all our extended sql
		$sqlextend="";
		foreach($this as $prop => $val){
			if($val){
				extendsql($prop,$val,$sqlextend,$loose);
			}
		}

		$sql="SELECT a.*, r.name as rack_name, d.name as device_name, dp.name as department_name
                FROM assets a
                LEFT JOIN rack r ON(r.id=a.rack_id)
                LEFT JOIN device d ON(d.id=a.device_id)
                LEFT JOIN department dp ON(dp.id=a.department_id)
                WHERE a.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["AssetsID"]]=AssetManage::RowToSearchObject($row);
			}else{
				$dcList[]=AssetManage::RowToSearchObject($row);
			}
		}

		return $dcList;
	}
	
	// Wrapper to make this method like the other classes
	function GetObject(){
            return $this->GetOrderByID();
	}
	
	function GetOrderByID(){
            $this->MakeSafe();

            $sql="SELECT * FROM assets WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetManage::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM assets WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetManage::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetAssetManageList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM assets WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetManage::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetManage::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetDepartment(){
            global $dbh;

            $sql="SELECT * FROM department WHERE is_deleted='N' ORDER BY name ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                if($indexbyid){
                    $ManufacturerList[$row['PortID']]=AssetManage::RowToObject($row);
                }else{
                    $ManufacturerList[]=AssetManage::RowToObject($row);
                }
            }
            
            return $ManufacturerList;
	}
                    
        static function GetAssetManageListRow($filter){
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
            if(isset($filter['assets'])){
                $incr .= " AND a.id =".$filter['assets'];
            }
            if(isset($filter['status'])){
                $incr .= " AND a.status ='".$filter['status']."'";
            }
            if(isset($filter['rack'])){
                $incr .= " AND a.rack_id =".$filter['rack'];
            }
            if(isset($filter['device'])){
                $incr .= " AND a.device_id =".$filter['device'];
            }
            if(isset($filter['company'])){
                $incr .= " AND a.company ='".$filter['company']."'";
            }
            
            $sql="SELECT a.*, r.name as rack_name, d.name as device_name, dp.name as department_name
                FROM assets a
                LEFT JOIN rack r ON(r.id=a.rack_id)
                LEFT JOIN device d ON(d.id=a.device_id)
                LEFT JOIN department dp ON(dp.id=a.department_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetManage::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetManage::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashAssetManageList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_assets 
                FROM assets a
                LEFT JOIN rack r ON(r.id=a.rack_id)
                LEFT JOIN device d ON(d.id=a.device_id)
                LEFT JOIN department dp ON(dp.id=a.department_id) WHERE a.is_deleted='N'";
            
            $AssetManageList = array();
            $result_AssetManageList = $dbh->query($sql);
            $AssetManageList = $result_AssetManageList ? $result_AssetManageList->fetch() : array();
            
            return $AssetManageList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            if($this->Manufacture_date !="")
            {
                $manufacture_date = date('Y-m-d',strtotime($this->Manufacture_date));
            }
            if($this->Install_date !="")
            {
                $install_date = date('Y-m-d',strtotime($this->Install_date));
            } else {
                $install_date = "Null";
            }
            if($this->Expiration_date !="")
            {
                $expire_date = date('Y-m-d',strtotime($this->Expiration_date));
            }
            if($this->Half_depth != "")
            {
                $depth = $this->Half_depth;
            } else {
                $depth = "N";
            }
            if($this->Back_side != ""){
                $back_side = $this->Back_side;
            } else {
                $back_side = "N";
            }
            
            $sql="INSERT INTO assets SET name=\"$this->Name\", status=\"$this->Status\", department_id=\"$this->Department\", label=\"$this->Label\", serial_no=\"$this->Serial_no\", asset_tag=\"$this->Asset_tag\", primary_ip=\"$this->Primary_ip\", manufacture_date='".$manufacture_date."', install_date='".$install_date."', company=\"$this->Company\", expire_date='".$expire_date."', rack_id=\"$this->Rack\", device_id=\"$this->Device\", height=\"$this->Height\", position=\"$this->Position\", half_depth='".$depth."', back_side='".$back_side."', data_ports=\"$this->Data_ports\", watts=\"$this->Watts\", weight=\"$this->Weight\", power_connection=\"$this->Power_connection\", device_role=\"$this->Device_role\", snmp_version=\"$this->SNMP_version\", snmp_community=\"$this->SNMP_community\", snmp_failure=\"$this->SNMP_failure\", created='".date('Y-m-d')."'";
            
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

            $sql="UPDATE assets SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            if($this->Manufacture_date !="")
            {
                $manufacture_date = date('Y-m-d',strtotime($this->Manufacture_date));
            }
            if($this->Install_date !="")
            {
                $install_date = date('Y-m-d',strtotime($this->Install_date));
            }
            if($this->Expiration_date !="")
            {
                $expire_date = date('Y-m-d',strtotime($this->Expiration_date));
            }
            if($this->Half_depth != "")
            {
                $depth = $this->Half_depth;
            } else {
                $depth = "N";
            }
            if($this->Back_side != ""){
                $back_side = $this->Back_side;
            } else {
                $back_side = "N";
            }
            
            $sql="UPDATE assets SET name=\"$this->Name\", status=\"$this->Status\", department_id=\"$this->Department\", label=\"$this->Label\", serial_no=\"$this->Serial_no\", asset_tag=\"$this->Asset_tag\", primary_ip=\"$this->Primary_ip\", manufacture_date='".$manufacture_date."', install_date='".$install_date."', company=\"$this->Company\", expire_date='".$expire_date."', rack_id=\"$this->Rack\", device_id=\"$this->Device\", height=\"$this->Height\", position=\"$this->Position\", half_depth='".$depth."', back_side='".$back_side."', data_ports=\"$this->Data_ports\", watts=\"$this->Watts\", weight=\"$this->Weight\", power_connection=\"$this->Power_connection\", device_role=\"$this->Device_role\", snmp_version=\"$this->SNMP_version\", snmp_community=\"$this->SNMP_community\", snmp_failure=\"$this->SNMP_failure\", last_updated='".date('Y-m-d')."' WHERE id=$this->PortID;";
            
            $old=new AssetManage();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
        
        function ExportReport($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['sort_on'])){
                $sort_on = $filter['sort_on'];
            }
            if(isset($filter['sort_by'])){
                $sort_by = $filter['sort_by'];
            }
            if(isset($filter['assets'])){
                $incr .= " AND a.id =".$filter['assets'];
            }
            if(isset($filter['status'])){
                $incr .= " AND a.status ='".$filter['status']."'";
            }
            if(isset($filter['rack'])){
                $incr .= " AND a.rack_id =".$filter['rack'];
            }
            if(isset($filter['device'])){
                $incr .= " AND a.device_id =".$filter['device'];
            }
            if(isset($filter['company'])){
                $incr .= " AND a.company ='".$filter['company']."'";
            }
            
            $sql="SELECT a.*, r.name as rack_name, d.name as device_name, dp.name as department_name
                FROM assets a
                LEFT JOIN rack r ON(r.id=a.rack_id)
                LEFT JOIN device d ON(d.id=a.device_id)
                LEFT JOIN department dp ON(dp.id=a.department_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetManage::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetManage::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Assets_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Status".$tab_sep."Department".$tab_sep."Label".$tab_sep."Primary IP".$tab_sep."Serial Number".$tab_sep."Asset Tag".$tab_sep."Primary IP/ Host Name".$tab_sep."Manufacture Date".$tab_sep."Install Date".$tab_sep."Company".$tab_sep."Warranty Expration".$tab_sep."Rack".$tab_sep."Device".$tab_sep."Height".$tab_sep."Position".$tab_sep."Half Depth".$tab_sep."Back Side".$tab_sep."Number of data ports".$tab_sep."Nominal Draw(Watts)".$tab_sep."Weight".$tab_sep."Power Connection".$tab_sep."Device Role".$tab_sep."SNMP Version".$tab_sep."SNMP Read Only Community".$tab_sep."SNMP Failure".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Status'].$tab_sep.$row['Department_name'].$tab_sep.$row['Label'].$tab_sep.$row['Primary_ip'].$tab_sep.$row['Serial_no'].$tab_sep.$row['Asset_tag'].$tab_sep.$row['Primary_ip'].$tab_sep.date("m/d/Y",strtotime($row['Manufacture_date'])).$tab_sep.date("m/d/Y",strtotime($row['Install_date'])).$tab_sep.$row['Company'].$tab_sep.date("m/d/Y",strtotime($row['Expiration_date'])).$tab_sep.$row['Rack_name'].$tab_sep.$row['Device_name'].$tab_sep.$row['Height'].$tab_sep.$row['Position'].$tab_sep.$row['Half_depth'].$tab_sep.$row['Back_side'].$tab_sep.$row['Data_ports'].$tab_sep.$row['Watts'].$tab_sep.$row['Weight'].$tab_sep.$row['Power_connection'].$tab_sep.$row['Device_role'].$tab_sep.$row['SNMP_version'].$tab_sep.$row['SNMP_community'].$tab_sep.$row['SNMP_failure'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>