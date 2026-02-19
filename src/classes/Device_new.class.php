<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Device_new {
        
	var $PortID;
	var $Name;
        var $Device_role;
        var $Manufacture;
        var $Device_type;
        var $Serial_no;
        var $Asset_tag;
        var $Weight;
        var $Height;
        var $Wattage;
        var $No_power;
        var $No_port;
        var $Snmp_version;
        var $Community;
        var $Failures;
        var $Front_picture;
        var $Front_pic;
        var $Rear_picture;
        var $Rear_pic;
        var $Site;
        var $Rack;
        var $Rack_face;
        var $Position;
        var $Status;
        var $Platform;
        var $Tag;
        var $Comment;
	var $Location_name;
        var $Rack_name;
        
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
            $m=new Device_new();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Device_role=$row["device_role"];
            $m->Manufacture=$row["manufacture_id"];
            $m->Device_type=$row["device_type"];
            $m->Serial_no=$row["serial_no"];
            $m->Asset_tag=$row["asset_tag"];
            $m->Height=$row["height"];
            $m->Weight=$row["weight"];
            $m->Wattage=$row["wattage"];
            $m->No_power=$row["no_power"];
            $m->No_port=$row["no_port"];
            $m->Snmp_version=$row["snmp_version"];
            $m->Community=$row["community"];
            $m->Failures=$row["failures"];
            $m->Site=$row["site_id"];
            $m->Rack=$row["rack_id"];
            $m->Rack_face=$row["rack_face"];
            $m->Position=$row["position"];
            $m->Status=$row["status"];
            $m->Platform=$row["platform"];
            $m->Tag=$row["tag"];
            $m->Comment=$row["comment"];
            $m->Front_picture=isset($row["front_picture"])?$row["front_picture"]:"";
            $m->Front_pic=isset($row["front_picture"])?$row["front_picture"]:"";
            $m->Rear_picture=isset($row["rear_picture"])?$row["rear_picture"]:"";
            $m->Rear_pic=isset($row["rear_picture"])?$row["rear_picture"]:"";
            $m->Location_name=$row["location_name"];
            $m->Rack_name=$row["rack_name"];
            $m->Manufacture_name = $row["manufacture_name"];
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
            // FILE UPLOAD CODE START
            if(!empty($_FILES['front_picture'])){
                $frontfile_name = $_FILES['front_picture'];
                $fronttmp_name = $_FILES["front_picture"]["tmp_name"];
                $frontFileName = $_FILES["front_picture"]["name"];
                $fronttemp = explode(".", $frontFileName);
                //print_r($_FILES['front_picture'])."<br/>";
                // print_r($_POST['front_pic']);exit;
                $img = $_POST['front_pic_val'];
                $img = str_replace('data:image/jpeg;base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                //$frontnewfilename = $frontFileName . '.' . end($fronttemp);
                //$fronttarget_dir = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/';
                //$fronttarget_file = $fronttarget_dir . $frontFileName;
                $fronttarget_file = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/' . $frontFileName;

                if ($fronttmp_name !="" && file_put_contents($fronttarget_file, $data)) {
                    $m->Front_pic = $frontFileName;
                }
            }
            
            if(!empty($_FILES['rear_picture'])){
                $rearfile_name = $_FILES['rear_picture'];
                $reartmp_name = $_FILES["rear_picture"]["tmp_name"];
                $rearFileName = $_FILES["rear_picture"]["name"];
                $reartemp = explode(".", $rearFileName);
                //$rearnewfilename = $rearFileName . '.' . end($reartemp);
                //$reartarget_dir = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/';
                //$reartarget_file = $reartarget_dir . $rearFileName;
                $rearimg = $_POST['rear_pic_val'];
                $rearimg = str_replace('data:image/jpeg;base64,', '', $rearimg);
                $rearimg = str_replace(' ', '+', $rearimg);
                $reardata = base64_decode($rearimg);
                //$frontnewfilename = $frontFileName . '.' . end($fronttemp);
                //$fronttarget_dir = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/';
                //$fronttarget_file = $fronttarget_dir . $frontFileName;
                $reartarget_file = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/' . $rearFileName;
                if ($reartmp_name !="" && file_put_contents($reartarget_file, $reardata)) {
                    $m->Rear_picture = $reartmp_name;
                }
            }
            // FILE UPLOAD CODE END
            
            $m->MakeDisplay();
            
            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Device_new();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Device_role=$row["device_role"];
            $m->Manufacture=$row["manufacture_id"];
            $m->Device_type=$row["device_type"];
            $m->Serial_no=$row["serial_no"];
            $m->Asset_tag=$row["asset_tag"];
            $m->Height=$row["height"];
            $m->Weight=$row["weight"];
            $m->Wattage=$row["wattage"];
            $m->No_power=$row["no_power"];
            $m->No_port=$row["no_port"];
            $m->Snmp_version=$row["snmp_version"];
            $m->Community=$row["community"];
            $m->Failures=$row["failures"];
            $m->Site=$row["site_id"];
            $m->Rack=$row["rack_id"];
            $m->Rack_face=$row["rack_face"];
            $m->Position=$row["position"];
            $m->Status=$row["status"];
            $m->Platform=$row["platform"];
            $m->Tag=$row["tag"];
            $m->Comment=$row["comment"];
            $m->Front_picture=isset($row["front_picture"])?$row["front_picture"]:"";
            $m->Front_pic=isset($row["front_picture"])?$row["front_picture"]:"";
            $m->Rear_picture=isset($row["rear_picture"])?$row["rear_picture"]:"";
            $m->Rear_pic=isset($row["rear_picture"])?$row["rear_picture"]:"";
            $m->Location_name=$row["location_name"];
            $m->Rack_name=$row["rack_name"];
            $m->Manufacture_name = $row["manufacture_name"];
            $m->Created_at = $row["created"];
            $m->Updated_at = $row["last_updated"];
            
            $m->MakeDisplay();
            unset($m->PortID);
            unset($m->Site);
            unset($m->Rack);
            unset($m->Manufacture);
            
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

		$sql="SELECT d.*, l.name as location_name,r.name as rack_name , m.name as manufacture_name
                FROM device d 
                LEFT JOIN location l ON(l.id=d.site_id) 
                LEFT JOIN rack r ON(r.id=d.rack_id) 
                LEFT JOIN manufacture m ON(m.id = d.manufacture_id)
                WHERE d.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["DeviceID"]]=Device_new::RowToSearchObject($row);
			}else{
				$dcList[]=Device_new::RowToSearchObject($row);
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
            
            $sql="SELECT * FROM device WHERE is_deleted='N' AND is_simulation='N' AND id=$this->PortID;";
            
            if($row=$this->query($sql)->fetch()){
                    foreach(Device_new::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM device WHERE is_deleted='N' AND is_simulation='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Device_new::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetDevice_newList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM device WHERE is_deleted='N' AND is_simulation='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Device_new::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Device_new::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetParentLocationList(){
            global $dbh;
           
            $sql="SELECT * FROM location WHERE is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Room::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Room::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetRackDeviceList($rack_id){
            global $dbh;

            $sql="SELECT * FROM device WHERE is_deleted='N' AND is_simulation='N' AND rack_id={$rack_id} ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]= Device_new::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Device_new::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetRackList(){
            global $dbh;
           
            $sql="SELECT * FROM device WHERE is_deleted='N' AND is_simulation='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Room::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Room::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetDeviceListRows($filter){
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
            if(isset($filter['rack'])){
                $incr .= " AND d.rack_id =".$filter['rack'];
            }
            if(isset($filter['location'])){
                $incr .= " AND d.site_id =".$filter['location'];
            }
            if(isset($filter['height'])){
                $incr .= " AND d.height =".$filter['height'];
            }
            if(isset($filter['weight'])){
                $incr .= " AND d.weight =".$filter['weight'];
            }
            if(isset($filter['device_type'])){
                $incr .= " AND d.device_type ='".$filter['device_type']."'";
            }
            if(isset($filter['manufacture'])){
                $incr .= " AND d.manufacture_id ='".$filter['manufacture']."'";
            }
            if(isset($filter['device_role'])){
                $incr .= " AND d.device_role ='".$filter['device_role']."'";
            }

            $sql="SELECT d.*,l.name as location_name,r.name as rack_name , m.name as manufacture_name
                FROM device d 
                LEFT JOIN location l ON(l.id=d.site_id) 
                LEFT JOIN rack r ON(r.id=d.rack_id) 
                LEFT JOIN manufacture m ON(m.id = d.manufacture_id)
                WHERE d.is_deleted='N' AND d.is_simulation='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Device_new::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Device_new::RowToObject($row);
                    }
            }
            return $ManufacturerList;
	}
        
        // Function for detail page
        function GetDeviceOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['device'])){
                $incr .= " AND d.id =".$filter['device'];
            }
            
            $sql="SELECT d.*,l.name as location_name,r.name as rack_name , m.name as manufacture_name
                FROM device d 
                LEFT JOIN location l ON(l.id=d.site_id) 
                LEFT JOIN rack r ON(r.id=d.rack_id) 
                LEFT JOIN manufacture m ON(m.id = d.manufacture_id)
                WHERE d.is_deleted='N' AND d.is_simulation='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if(isset($indexbyid)){
                    $RowList[$row['PortID']]= Device_new::RowToObject($row);
                }else{
                    $RowList[]= Device_new::RowToObject($row);
                }
            }
            return $RowList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashDevice_newList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_device 
                FROM device d 
                LEFT JOIN location l ON(l.id=d.site_id) 
                LEFT JOIN rack r ON(r.id=d.rack_id) 
                LEFT JOIN manufacture m ON(m.id = d.manufacture_id) WHERE d.is_deleted='N' AND d.is_simulation='N'";
            
            $Device_newList = array('total_device' => 0);
            $result = $dbh->query($sql);
            if($result){
                $Device_newList = $result->fetch();
            }
            
            return $Device_newList;
	}
        function getRoomID($rack_id){
            global $dbh;
            
            $sql="SELECT room_id as roomID FROM rack WHERE id=".$rack_id;
            
            $RackList = array();
            $result_RackList = $dbh->query($sql);
            $RackList = $result_RackList ? $result_RackList->fetch() : array();
            
            return $RackList;
        }
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $ins_room_id = 0;
            $created = date('Y-m-d');
            if($this->Rack != "")
            {    
                $ins_room_id_tmp = $this->getRoomID($this->Rack);
                $ins_room_id = $ins_room_id_tmp['roomID']; 
            }
            $sql="INSERT INTO device SET name=\"$this->Name\", site_id=\"$this->Site\", room_id=".$ins_room_id.", rack_id=\"$this->Rack\", device_role=\"$this->Device_role\", manufacture_id=\"$this->Manufacture\", device_type=\"$this->Device_type\", serial_no=\"$this->Serial_no\", asset_tag=\"$this->Asset_tag\", height=\"$this->Height\", weight=\"$this->Weight\", wattage=\"$this->Wattage\", no_power=\"$this->No_power\", no_port=\"$this->No_port\", snmp_version=\"$this->Snmp_version\", community=\"$this->Community\", failures=\"$this->Failures\", front_picture=\"$this->Front_pic\", rear_picture=\"$this->Rear_pic\", rack_face=\"$this->Rack_face\", position=\"$this->Position\", status=\"$this->Status\", platform=\"$this->Platform\", tag=\"$this->Tag\", comment=\"$this->Comment\", created=\"$created\";";
            
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

            $sql="UPDATE device SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            //$this->MakeSafe();
            $last_updated = date('Y-m-d');
            $ins_room_id = 0;
            if($this->Rack != "")
            {    
                $ins_room_id_tmp = $this->getRoomID($this->Rack);
                $ins_room_id = $ins_room_id_tmp['roomID']; 
            }
            
            $sql="UPDATE device SET name=\"$this->Name\", site_id=\"$this->Site\", room_id=".$ins_room_id.", rack_id=\"$this->Rack\", device_role=\"$this->Device_role\", manufacture_id=\"$this->Manufacture\", device_type=\"$this->Device_type\", serial_no=\"$this->Serial_no\", asset_tag=\"$this->Asset_tag\", height=\"$this->Height\", weight=\"$this->Weight\", wattage=\"$this->Wattage\", no_power=\"$this->No_power\", no_port=\"$this->No_port\", snmp_version=\"$this->Snmp_version\", community=\"$this->Community\", failures=\"$this->Failures\", front_picture=\"$this->Front_pic\", rear_picture=\"$this->Rear_pic\", rack_face=\"$this->Rack_face\", position=\"$this->Position\", status=\"$this->Status\", platform=\"$this->Platform\", tag=\"$this->Tag\", comment=\"$this->Comment\", last_updated=\"$last_updated\" WHERE id=$this->PortID;";
            
            $old=new Device_new();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            $this->MakeDisplay();
            (class_exists('LogActions'))?LogActions::LogThis($this,$old):'';
            //echo $sql;exit;
            return $this->query($sql);
	}
        
        function UpdateDevicePosition($params){
            $this->MakeSafe();
            
            $sql="UPDATE device SET position={$params['position']} WHERE id={$params['device_id']};";
            
            (class_exists('LogActions'))?LogActions::LogThis($this):'';
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
            if(isset($filter['rack'])){
                $incr .= " AND d.rack_id =".$filter['rack'];
            }
            if(isset($filter['location'])){
                $incr .= " AND d.site_id =".$filter['location'];
            }
            if(isset($filter['height'])){
                $incr .= " AND d.height =".$filter['height'];
            }
            if(isset($filter['weight'])){
                $incr .= " AND d.weight =".$filter['weight'];
            }
            if(isset($filter['device_type'])){
                $incr .= " AND d.device_type ='".$filter['device_type']."'";
            }
            if(isset($filter['manufacture'])){
                $incr .= " AND d.manufacture_id ='".$filter['manufacture']."'";
            }
            if(isset($filter['device_role'])){
                $incr .= " AND d.device_role ='".$filter['device_role']."'";
            }

            $sql="SELECT d.*,l.name as location_name,r.name as rack_name , m.name as manufacture_name
                FROM device d 
                LEFT JOIN location l ON(l.id=d.site_id) 
                LEFT JOIN rack r ON(r.id=d.rack_id) 
                LEFT JOIN manufacture m ON(m.id = d.manufacture_id)
                WHERE d.is_deleted='N' AND d.is_simulation='N'".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Device_new::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Device_new::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Device_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Location".$tab_sep."Rack".$tab_sep."Role".$tab_sep."Manufacture".$tab_sep."Device Type".$tab_sep."Height".$tab_sep."Weight".$tab_sep."Wattage".$tab_sep."No. Power".$tab_sep."No. Port".$tab_sep."Rack Face".$tab_sep."Status".$tab_sep."Platform".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Location_name'].$tab_sep.$row['Rack_name'].$tab_sep.$row['Device_role'].$tab_sep.$row['Manufacture'].$tab_sep.$row['Device_type'].$tab_sep.$row['Height'].$tab_sep.$row['Weight'].$tab_sep.$row['Wattage'].$tab_sep.$row['No_power'].$tab_sep.$row['No_port'].$tab_sep.$row['Rack_face'].$tab_sep.$row['Status'].$tab_sep.$row['Platform'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
