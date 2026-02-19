<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class AssetSupplier {
        
	var $PortID;
	var $Name;
        var $Address;
        var $City;
        var $State;
        var $Country;
        var $Zip;
        var $Contact_name;
        var $Phone;
        var $Fax;
        var $Email;
        var $Url;
        var $Note;
        var $Supplier_image;
        var $Supplier_img;
        
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
            $m=new AssetSupplier();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Address=$row["address"];
            $m->City=$row["city"];
            $m->State=$row["state"];
            $m->Country=$row["country"];
            $m->Zip=$row["zip"];
            $m->Contact_name=$row["contact_name"];
            $m->Phone=$row["phone"];
            $m->Fax=$row["fax"];
            $m->Email=$row["email"];
            $m->Url=$row["url"];
            $m->Note=$row["note"];
            $m->Supplier_image=$row["supplier_image"];
            $m->Supplier_img=$row["supplier_image"];
            
            // FILE UPLOAD CODE START
            if(!empty($_FILES['supplier_image'])){
                $frontfile_name = $_FILES['supplier_image'];
                $fronttmp_name = $_FILES["supplier_image"]["tmp_name"];
                $frontFileName = $_FILES["supplier_image"]["name"];
                $fronttemp = explode(".", $frontFileName);
                
                $img = $_POST['supplier_img_val'];
                $img = str_replace('data:image/jpeg;base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                //$frontnewfilename = $frontFileName . '.' . end($fronttemp);
                //$fronttarget_dir = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/';
                //$fronttarget_file = $fronttarget_dir . $frontFileName;
                $fronttarget_file = _PATH.DIRECTORY_SEPARATOR.'uploads/assets_supplier/' . $frontFileName;

                if ($fronttmp_name !="" && file_put_contents($fronttarget_file, $data)) {
                    $m->Supplier_img = $frontFileName;
                }
            }
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new AssetSupplier();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Address=$row["address"];
            $m->City=$row["city"];
            $m->State=$row["state"];
            $m->Country=$row["country"];
            $m->Zip=$row["zip"];
            $m->Contact_name=$row["contact_name"];
            $m->Phone=$row["phone"];
            $m->Fax=$row["fax"];
            $m->Email=$row["email"];
            $m->Url=$row["url"];
            $m->Note=$row["note"];
            $m->Supplier_image=$row["supplier_image"];
            $m->Supplier_img=$row["supplier_image"];
            
            $m->MakeDisplay();

            unset($m->PortID);
            unset($m->Supplier_img);
            
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

		$sql="SELECT * FROM asset_supplier WHERE is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["AssetsID"]]=AssetSupplier::RowToSearchObject($row);
			}else{
				$dcList[]=AssetSupplier::RowToSearchObject($row);
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

            $sql="SELECT * FROM asset_supplier WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetSupplier::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM asset_supplier WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(AssetSupplier::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetAssetList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM asset_supplier WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSupplier::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSupplier::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
                    
        static function GetAssetListRow($filter){
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
                $incr .= " AND a.status_id ='".$filter['status']."'";
            }
            
            if(isset($filter['company'])){
                $incr .= " AND a.company ='".$filter['company']."'";
            }
            
            $sql="SELECT a.*
                FROM asset_supplier a
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSupplier::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSupplier::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashAssetSupplierList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_supplier
                FROM asset_supplier a 
                WHERE a.is_deleted='N'";
            
            $AssetList = array();
            $result_AssetList = $dbh->query($sql);
            $AssetList = $result_AssetList ? $result_AssetList->fetch() : array();
            
            return $AssetList;
	}
        
        static function GetStatusList($indexbyid=false){
            global $dbh;

            $sql="SELECT *, status as status_name FROM asset_status WHERE is_deleted='N' ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSupplier::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSupplier::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        static function GetStatusListRows($indexbyid=false){
            global $dbh;

            $sql="SELECT *, status as status_name, (SELECT count(id) as total_assets FROM assets a WHERE a.status_id=id) as total_assets FROM asset_status WHERE is_deleted='N' GROUP BY id ORDER BY id ASC;";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSupplier::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSupplier::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
        function GetStatusByID(){
            $this->MakeSafe();

            $sql="SELECT *, status as status_name FROM asset_status WHERE is_deleted='N' AND id=$this->PortID;";
            
            if($row=$this->query($sql)->fetch()){
                    $ManufacturerList[0]=AssetSupplier::RowToObject($row);
            }else{
                    $ManufacturerList[0]=AssetSupplier::RowToObject($row);
            }
            return $ManufacturerList;
	}
        
        static function GetSupplierList($indexbyid=false){
            global $dbh;

            $sql="SELECT *, name as supplier_name FROM asset_supplier ORDER BY id ASC;";

            $ManufacturerList = array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSupplier::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSupplier::RowToObject($row);
                    }
            }
            
            return $ManufacturerList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            
            $created_at = date('Y-m-d');
            
            $sql="INSERT INTO asset_supplier SET name=\"$this->Name\", address=\"$this->Address\", city=\"$this->City\", state=\"$this->State\", country=\"$this->Country\", zip=\"$this->Zip\", contact_name=\"$this->Contact_name\", phone=\"$this->Phone\", fax=\"$this->Fax\", email=\"$this->Email\", url=\"$this->Url\", note=\"$this->Note\", supplier_image=\"$this->Supplier_img\", created='".$created_at."';"; 
            
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

            $sql="UPDATE assets SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            
            $created_at = date('Y-m-d');
            
            $sql="UPDATE asset_supplier SET name=\"$this->Name\", address=\"$this->Address\", city=\"$this->City\", state=\"$this->State\", country=\"$this->Country\", zip=\"$this->Zip\", contact_name=\"$this->Contact_name\", phone=\"$this->Phone\", fax=\"$this->Fax\", email=\"$this->Email\", url=\"$this->Url\", note=\"$this->Note\", supplier_image=\"$this->Supplier_img\", last_updated='".$created_at."' WHERE id=$this->PortID;";
            
            $old=new AssetSupplier();
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
            
            $sql="SELECT a.*
                FROM asset_supplier a
                WHERE a.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=AssetSupplier::RowToObject($row);
                    }else{
                            $ManufacturerList[]=AssetSupplier::RowToObject($row);
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
                $header_html .= "Name".$tab_sep."Address".$tab_sep."City".$tab_sep."State".$tab_sep."Country".$tab_sep."Email".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['Address'].$tab_sep.$row['City'].$tab_sep.$row['State'].$tab_sep.$row['Country'].$tab_sep.$row['Email'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>