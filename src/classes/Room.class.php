<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Room {
        
	var $PortID;
	var $Name;
        var $RoomNo;
        var $Location;
        var $Rows;
        var $Columns;
        var $Rows_per_rack;
        var $Group_columns;
        var $Group_rows;
        var $Front_picture;
        var $Front_pic;
        
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
            $m=new Room();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->RoomNo=$row["room_no"];
            $m->Location=$row["location_id"];
            $m->LocationName=isset($row["location_name"])?$row["location_name"]:'';
            $m->Rows=$row["rows"];
            $m->Columns=$row["columns"];
            $m->Rows_per_rack=$row["rows_per_rack"];
            $m->Group_columns=$row["group_columns"];
            $m->Group_rows=$row["group_rows"];
            $m->Front_picture=isset($row["picture"])?$row["picture"]:'';
            $m->Front_pic=isset($row["picture"])?$row["picture"]:'';
            
            // FILE UPLOAD CODE START
            if(!empty($_FILES)){
                $frontfile_name = $_FILES['front_picture'];
                $fronttmp_name = $_FILES["front_picture"]["tmp_name"];
                $frontFileName = str_replace(" ", "_", $_FILES["front_picture"]["name"]);
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
                $fronttarget_file = _PATH.DIRECTORY_SEPARATOR.'uploads/room/' . $frontFileName;

                if ($fronttmp_name !="" && file_put_contents($fronttarget_file, $data)) {
                    $m->Front_pic = $frontFileName;
                }
            }
            
            $m->MakeDisplay();

            return $m;
	}

        static function RowToSearchObject($row){
            $m=new Room();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->RoomNo=$row["room_no"];
            $m->Location=$row["location_id"];
            $m->LocationName=isset($row["location_name"])?$row["location_name"]:'';
            $m->Rows=$row["rows"];
            $m->Columns=$row["columns"];
            $m->Rows_per_rack=$row["rows_per_rack"];
            $m->Group_columns=$row["group_columns"];
            $m->Group_rows=$row["group_rows"];
            $m->Front_picture=isset($row["picture"])?$row["picture"]:'';
            $m->Front_pic=isset($row["picture"])?$row["picture"]:'';
            
            $m->MakeDisplay();
            
            unset($m->PortID);
            unset($m->Location);
            
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

		$sql="SELECT r.*, l.name as location_name 
                    FROM room r 
                    LEFT JOIN location l ON(r.location_id=l.id) 
                    WHERE r.is_deleted='N' $sqlextend ORDER BY id ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
			if($indexedbyid){
				$dcList[$row["RoomID"]]=Room::RowToSearchObject($row);
			}else{
				$dcList[]=Room::RowToSearchObject($row);
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
            
            $sql="SELECT * FROM room WHERE is_deleted='N' AND id=$this->PortID;";

            if($row=$this->query($sql)->fetch()){
                    foreach(Room::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM room WHERE is_deleted='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Room::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetRoomList($indexbyid=false){
            global $dbh;
            
            $sql="SELECT * FROM room WHERE is_deleted='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql);
            if ($result) {
                foreach($result as $row){
                        if($indexbyid){
                                $ManufacturerList[$row['PortID']]=Room::RowToObject($row);
                        }else{
                                $ManufacturerList[]=Room::RowToObject($row);
                        }
                }
            }
            
            return $ManufacturerList;
	}
        
        static function GetRoomListRows($filter){
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
            if(isset($filter['location'])){
                $incr .= " AND r.location_id =".$filter['location'];
            }
            if(isset($filter['room'])){
                $incr .= " AND r.id =".$filter['room'];
            }
            if(isset($filter['room_no'])){
                $incr .= " AND r.room_no =".$filter['room_no'];
            }
            $sql="SELECT r.*,l.name as location_name 
                FROM room r 
                LEFT JOIN location l ON(r.location_id=l.id) 
                WHERE r.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
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
        
        // Function for detail page
        static function GetRoomOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['room'])){
                $incr .= " AND r.id =".$filter['room'];
            }
            
            $sql="SELECT r.*
                FROM room r 
                WHERE r.is_deleted='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if(isset($indexbyid)){
                    $RowList[$row['PortID']]= Room::RowToObject($row);
                }else{
                    $RowList[]= Room::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        static function GetLocationRoomList($location_id){
            global $dbh;
            
            $sql="SELECT *
                FROM room 
                WHERE is_deleted='N' AND location_id={$location_id} ORDER BY id ASC;";
             
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Rack::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Rack::RowToObject($row);
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
        
        // QUERY FOR DASHBOARD COUNTER
	static function GetDashRoomList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_room FROM room r 
                LEFT JOIN location l ON(r.location_id=l.id) WHERE r.is_deleted='N'";
            
            $RoomList = array('total_room' => 0);
            $result = $dbh->query($sql);
            if($result){
                $RoomList = $result->fetch();
            }
            
            return $RoomList;
	}
        
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            $picture_name = str_replace(" ", "_", $this->Front_pic);
            $sql="INSERT INTO room SET name=\"$this->Name\", room_no=\"$this->RoomNo\", location_id=\"$this->Location\", rows=\"$this->Rows\", columns=\"$this->Columns\", rows_per_rack=\"$this->Rows_per_rack\", group_columns=\"$this->Group_columns\", group_rows=\"$this->Group_rows\", picture=\"$picture_name\", created='".date('Y-m-d')."';";
            
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

            $sql="UPDATE room SET is_deleted='Y' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            $this->MakeSafe();
            $picture_name = str_replace(" ", "_", $this->Front_pic);
            $sql="UPDATE room SET name=\"$this->Name\", room_no=\"$this->RoomNo\", location_id=\"$this->Location\", rows=\"$this->Rows\", columns=\"$this->Columns\", rows_per_rack=\"$this->Rows_per_rack\", group_columns=\"$this->Group_columns\", group_rows=\"$this->Group_rows\", picture=\"$picture_name\", last_updated='".date('Y-m-d')."' WHERE id=$this->PortID;";
            
            $old=new Room();
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
            if(isset($filter['location'])){
                $incr .= " AND r.location_id =".$filter['location'];
            }
            if(isset($filter['room'])){
                $incr .= " AND r.id =".$filter['room'];
            }
            if(isset($filter['room_no'])){
                $incr .= " AND r.room_no =".$filter['room_no'];
            }
            $sql="SELECT r.*,l.name as location_name 
                FROM room r 
                LEFT JOIN location l ON(r.location_id=l.id) 
                WHERE r.is_deleted='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Room::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Room::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Room_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Location".$tab_sep."Room Number".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['LocationName'].$tab_sep.$row['RoomNo'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
