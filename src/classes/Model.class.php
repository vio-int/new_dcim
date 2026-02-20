<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Model {
        
	var $PortID;
	var $Name;
        var $Manufacture_id;
        var $Manufacture_name;
        var $Manufacture;
        var $Category_id;
        var $Category;
        var $Fieldset;
        var $Model_no;
        var $EOL;
        var $Note;
        var $Is_user_request;
        var $Model_image;
        var $Model_img;


	
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
            $m=new Model();
            $m->PortID=$row["id"];
            $m->Name=isset($row["name"])?$row["name"]:'';
            $m->Manufacture=isset($row["manufacture_id"])?$row["manufacture_id"]:'';
            $m->Manufacture_name=isset($row["manufacture_name"])?$row["manufacture_name"]:'';
            $m->Category=isset($row["category_id"])?$row["category_id"]:'';
            $m->Category_name=isset($row["category_name"])?$row["category_name"]:'';
            $m->Model_no=isset($row["model_no"])?$row["model_no"]:'';
            $m->EOL=isset($row["eol"])?$row["eol"]:'';
            $m->Note=isset($row["note"])?$row["note"]:'';
            $m->Is_user_request=isset($row["is_user_request"])?$row["is_user_request"]:'';
            $m->Model_image=isset($row["model_image"])?$row["model_image"]:'';
            $m->Model_img=isset($row["model_image"])?$row["model_image"]:'';
            $m->Fieldset=isset($row["fieldset"])?$row["fieldset"]:'';
            $m->Total_assets=isset($row["total_assets"])?$row["total_assets"]:'';
            
            // FILE UPLOAD CODE START
            if(!empty($_FILES['model_image'])){
                $frontfile_name = $_FILES['model_image'];
                $fronttmp_name = $_FILES["model_image"]["tmp_name"];
                $frontFileName = $_FILES["model_image"]["name"];
                $fronttemp = explode(".", $frontFileName);
                
                $img = $_POST['model_img_val'];
                $img = str_replace('data:image/jpeg;base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                $fronttarget_file = _PATH.DIRECTORY_SEPARATOR.'uploads/assets_model/' . $frontFileName;
                
                if ($fronttmp_name !="" && file_put_contents($fronttarget_file, $data)) {
                    $m->Model_img = $frontFileName;
                }
            }
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Model();
            $m->PortID=$row["id"];
            $m->Name=isset($row["name"])?$row["name"]:'';
            $m->Manufacture=isset($row["manufacture_id"])?$row["manufacture_id"]:'';
            $m->Category=isset($row["category_id"])?$row["category_id"]:'';
            $m->Manufacture_name=isset($row["manufacture_name"])?$row["manufacture_name"]:'';
            $m->Category_name=isset($row["category_name"])?$row["category_name"]:'';
            $m->Model_no=isset($row["model_no"])?$row["model_no"]:'';
            $m->EOL=isset($row["eol"])?$row["eol"]:'';
            $m->Note=isset($row["note"])?$row["note"]:'';
            $m->Is_user_request=isset($row["is_user_request"])?$row["is_user_request"]:'';
            $m->Model_image=isset($row["model_image"])?$row["model_image"]:'';
            $m->Model_img=isset($row["model_image"])?$row["model_image"]:'';
            $m->Fieldset=isset($row["fieldset"])?$row["fieldset"]:'';
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Category);
            unset($m->Manufacture);
            unset($m->Category_id);
            unset($m->Manufacture_id);
            unset($m->Model_img);
            
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

		$sql="SELECT m.*, ac.name as category_name, ma.name as manufacture_name
                    FROM asset_model m 
                    LEFT JOIN asset_category ac ON(m.category_id=ac.id)
                    LEFT JOIN manufacture ma ON(ma.id=m.manufacture_id)
                    WHERE m.is_deleted='N' $sqlextend ORDER BY Name ASC;";
                
		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["ModelID"]]=Model::RowToSearchObject($row);
			}else{
				$dcList[]=Model::RowToSearchObject($row);
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

            $sql="SELECT * FROM asset_model WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(Model::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM asset_model WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Model::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetAssetModelList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM asset_model WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Model::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Model::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
        static function GetAssetModelListRow($filter){
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
            if(isset($filter['model'])){
                $incr .= " AND a.id =".$filter['model'];
            }
            if(isset($filter['category'])){
                $incr .= " AND a.category_id ='".$filter['category']."'";
            }
            
            
            $sql="SELECT a.*, m.name as manufacture_name, c.name as category_name, (SELECT count(id) as total_assets FROM assets a2 WHERE a2.is_deleted ='N' AND a2.model_id=a.id) as total_assets
                FROM asset_model a
                LEFT JOIN manufacture m ON(m.id=a.manufacture_id)
                LEFT JOIN asset_category c ON(c.id=a.category_id)
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            
            $ManufacturerList=array();
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Model::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Model::RowToObject($row);
                    }
            } }
            
            return $ManufacturerList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashModelList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_models 
                FROM asset_model a
                WHERE a.is_deleted='N'";
            
            $ModelList = array();
            $result_ModelList = $dbh->query($sql);
            $ModelList = $result_ModelList ? $result_ModelList->fetch() : array();
            
            return $ModelList;
	}
        
	function CreateObject($params = array()){
            global $dbh;

            $this->MakeSafe();
            if(!empty($params)){
                $this->Name = $params['name'];
                $this->Manufacture = $params['manufacture'];
                $this->Category = $params['category_name'];
                $this->Model_no = $params['model_no'];
                $this->Fieldset = $params['fieldset'];
            }
            
            if($this->Is_user_request != "")
            {
                $is_user_request = $this->Is_user_request;
            } else {
                $is_user_request = "N";
            }
            if($this->Model_image != "")
            {
                $img_name = $this->Model_image;
            } else {
                $img_name = $this->Model_img;
            }
            
            $sql="INSERT INTO asset_model SET name=\"$this->Name\", manufacture_id=\"$this->Manufacture\", model_no=\"$this->Model_no\", fieldset=\"$this->Fieldset\", category_id=\"$this->Category\", eol=\"$this->EOL\", note=\"$this->Note\", is_user_request='".$is_user_request."', model_image='".$img_name."'";
            
            if(!$dbh->exec($sql)){
                    error_log( "SQL Error: " . $sql );
                    return false;
            } else {
                    $this->PortID=$dbh->lastInsertID();
                    (class_exists('LogActions'))?LogActions::LogThis($this):'';
                    $this->MakeDisplay();
                    return true;
            }
	}
        
        function DeleteObject($TransferTo=null){
            $this->MakeSafe();

            $sql="UPDATE asset_model SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            
            $this->MakeSafe();
            
            if($this->Is_user_request != "")
            {
                $is_user_request = $this->Is_user_request;
            } else {
                $is_user_request = "N";
            }
            
            if($this->Model_image != "")
            {
                $img_name = $this->Model_image;
            } else {
                $img_name = $this->Model_img;
            }
            
            $sql="UPDATE asset_model SET name=\"$this->Name\", manufacture_id=\"$this->Manufacture\", model_no=\"$this->Model_no\",  fieldset=\"$this->Fieldset\", category_id=\"$this->Category\", eol=\"$this->EOL\", note=\"$this->Note\", is_user_request='".$is_user_request."', model_image='".$img_name."' WHERE id=$this->PortID;";
            
            $old=new Model();
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
            $result = $dbh->query($sql); if ($result) {             foreach($result as $row) {
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Model::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Model::RowToObject($row);
                    }
            } }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Models_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Status".$tab_sep."Department".$tab_sep."Label".$tab_sep."Primary IP".$tab_sep."Serial Number".$tab_sep."Model Tag".$tab_sep."Primary IP/ Host Name".$tab_sep."Manufacture Date".$tab_sep."Install Date".$tab_sep."Company".$tab_sep."Warranty Expration".$tab_sep."Rack".$tab_sep."Device".$tab_sep."Height".$tab_sep."Position".$tab_sep."Half Depth".$tab_sep."Back Side".$tab_sep."Number of data ports".$tab_sep."Nominal Draw(Watts)".$tab_sep."Weight".$tab_sep."Power Connection".$tab_sep."Device Role".$tab_sep."SNMP Version".$tab_sep."SNMP Read Only Community".$tab_sep."SNMP Failure".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Status'].$tab_sep.$row['Department_name'].$tab_sep.$row['Label'].$tab_sep.$row['Primary_ip'].$tab_sep.$row['Serial_no'].$tab_sep.$row['Model_tag'].$tab_sep.$row['Primary_ip'].$tab_sep.date("m/d/Y",strtotime($row['Manufacture_date'])).$tab_sep.date("m/d/Y",strtotime($row['Install_date'])).$tab_sep.$row['Company'].$tab_sep.date("m/d/Y",strtotime($row['Expiration_date'])).$tab_sep.$row['Rack_name'].$tab_sep.$row['Device_name'].$tab_sep.$row['Height'].$tab_sep.$row['Position'].$tab_sep.$row['Half_depth'].$tab_sep.$row['Back_side'].$tab_sep.$row['Data_ports'].$tab_sep.$row['Watts'].$tab_sep.$row['Weight'].$tab_sep.$row['Power_connection'].$tab_sep.$row['Device_role'].$tab_sep.$row['SNMP_version'].$tab_sep.$row['SNMP_community'].$tab_sep.$row['SNMP_failure'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>