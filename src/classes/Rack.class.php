<?php
/*
	VIO Intelligence DCIM

	This is the main class library for the VIO Intelligence DCIM application, which
	is a PHP/Web based data center infrastructure management system.

	This application was originally written by PT VIO Intelligence
*/

class Rack {
        
	var $PortID;
	var $Name;
        var $Site;
        var $Group_no;
        var $Row_position;
        var $Facility;
        var $Serial_no;
        var $Descending;
        var $Type;
        var $Width;
        var $Height;
        var $Position;
        var $Model;
        var $Key_info;
        var $Max_kw;
        var $Max_weight;
        var $Installed_at;
        var $Assign_to;
        var $Tag;
        var $Comment;
        var $X1;
        var $X2;
        var $Y1;
        var $Y2;
        var $MapX1;
        var $MapX2;
        var $MapY1;
        var $MapY2;
        var $Mapzoom;
	
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
            $m=new Rack();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Site=$row["room_id"];
            $m->Group_no=$row["group_no"];
            $m->Row_position=$row["row_position"];
            $m->Facility=$row["facility"];
            $m->Serial_no=$row["serial_no"];
            $m->Descending=$row["is_descending"];
            $m->Type=$row["type"];
            $m->Width=$row["width"];
            $m->Height=$row["height"];
            $m->Position=$row["position"];
            $m->Model=$row["model"];
            $m->Key_info=$row["key_info"];
            $m->Max_kw=$row["max_kw"];
            $m->Max_weight=$row["max_weight"];
            $m->Installed_at=$row["installed_at"];
            $m->Assign_to=$row["assign_to"];
            $m->Tag=$row["tag"];
            $m->Comment=$row["comment"];
            $m->X1=$row["x1"];
            $m->X2=$row["x2"];
            $m->Y1=$row["y1"];
            $m->Y2=$row["y2"];
            $m->MapX1=$row["x1"];
            $m->MapX2=$row["x2"];
            $m->MapY1=$row["y1"];
            $m->MapY2=$row["y2"];
            $m->Mapzoom=$row["mapzoom"];
            $m->RoomName=isset($row['room_name'])?$row['room_name']:'';
            $m->Location_Name=isset($row['location_name'])?$row['location_name']:'';
            $m->Created_at=$row["created"];
            $m->Updated_at=$row['last_updated'];
            
            $m->MakeDisplay();

            return $m;
	}
        
        static function RowToSearchObject($row){
            $m=new Rack();
            $m->PortID=$row["id"];
            $m->Name=$row["name"];
            $m->Site=$row["room_id"];
            $m->Group_no=$row["group_no"];
            $m->Row_position=$row["row_position"];
            $m->Facility=$row["facility"];
            $m->Serial_no=$row["serial_no"];
            $m->Descending=$row["is_descending"];
            $m->Type=$row["type"];
            $m->Width=$row["width"];
            $m->Height=$row["height"];
            $m->Position=$row["position"];
            $m->Model=$row["model"];
            $m->Key_info=$row["key_info"];
            $m->Max_kw=$row["max_kw"];
            $m->Max_weight=$row["max_weight"];
            $m->Installed_at=$row["installed_at"];
            $m->Assign_to=$row["assign_to"];
            $m->Tag=$row["tag"];
            $m->Comment=$row["comment"];
            $m->X1=$row["x1"];
            $m->X2=$row["x2"];
            $m->Y1=$row["y1"];
            $m->Y2=$row["y2"];
            $m->MapX1=$row["x1"];
            $m->MapX2=$row["x2"];
            $m->MapY1=$row["y1"];
            $m->MapY2=$row["y2"];
            $m->Mapzoom=$row["mapzoom"];
            $m->RoomName=$row['room_name'];
            $m->Location_Name=$row['location_name'];
            $m->Created_at=$row["created"];
            $m->Updated_at=$row['last_updated'];
            
            $m->MakeDisplay();
            
            unset($m->PortID);
            unset($m->Site);
            
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

		$sql="SELECT r.*, r1.name as room_name, l.name as location_name 
                FROM rack r 
                LEFT JOIN room r1 ON(r1.id=r.room_id)
                LEFT JOIN location l on(l.id=r.site_id) 
                WHERE r.is_deleted='N' $sqlextend ORDER BY Name ASC;";

		$dcList=array();

		foreach($this->query($sql) as $row){
                    if($indexedbyid){
                        $dcList[$row["RackID"]]=Rack::RowToSearchObject($row);
                    }else{
                        $dcList[]=Rack::RowToSearchObject($row);
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

            $sql="SELECT * FROM rack WHERE is_deleted='N' AND is_simulation='N' AND id=$this->PortID;";
            
            if($row=$this->query($sql)->fetch()){
                    foreach(Rack::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }
                    return true;
            }else{
                    return false;
            }
	}
	
	function GetObjectByName(){
            $this->MakeSafe();

            $sql="SELECT * FROM rack WHERE is_deleted='N' AND is_simulation='N' AND ucase(Name)=ucase('".$this->Name."');";

            if($row=$this->query($sql)->fetch()){
                    foreach(Rack::RowToObject($row) as $prop => $value){
                            $this->$prop=$value;
                    }	
                    return true;
            }else{
                    return false;
            }
	}
	
	static function GetRackList($indexbyid=false){
            global $dbh;

            $sql="SELECT * FROM rack WHERE is_deleted='N' AND is_simulation='N' ORDER BY id ASC;";

            $ManufacturerList=array();
            $result = $dbh->query($sql);
            if ($result) {
                foreach($result as $row){
                        if($indexbyid){
                                $ManufacturerList[$row['PortID']]=Rack::RowToObject($row);
                        }else{
                                $ManufacturerList[]=Rack::RowToObject($row);
                        }
                }
            }
            
            return $ManufacturerList;
	}
        static function GetLocationRackList($location_id){
            global $dbh;

            $sql="SELECT r2.* FROM room r 
                JOIN rack r2 ON (r2.room_id=r.id) 
                WHERE r2.is_deleted='N' AND r2.is_simulation='N' AND r.location_id={$location_id} ORDER BY id ASC;";
                
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
        
        static function GetLocationRoomList($room_id){
            global $dbh;
            
            $sql="SELECT *
                FROM rack 
                WHERE is_deleted='N' AND is_simulation='N' AND room_id={$room_id} ORDER BY id ASC;";
                
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
        
        static function GetRackListRows($filter){
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
                $incr .= " AND r.id =".$filter['rack'];
            }
            if(isset($filter['room'])){
                $incr .= " AND r.room_id =".$filter['room'];
            }
            if(isset($filter['height'])){
                $incr .= " AND r.height =".$filter['height'];
            }
            if(isset($filter['width'])){
                $incr .= " AND r.width =".$filter['width'];
            }
            if(isset($filter['type'])){
                $incr .= " AND r.type ='".$filter['type']."'";
            }

            $sql="SELECT r.*,l.name as room_name 
                FROM rack r 
                LEFT JOIN room l ON(l.id=r.room_id) 
                WHERE r.is_deleted='N' AND r.is_simulation='N' ".$incr." ORDER BY {$sort_on} {$sort_by} LIMIT {$start_from} , {$limit};";
            
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
        // Function for detail page
        static function GetRackOne($filter){
            global $dbh;
            
            $incr = "";
            if(isset($filter['rack'])){
                $incr .= " AND r.id =".$filter['rack'];
            }
            
            $sql="SELECT r.*, l.name as location_name 
                FROM rack r 
                LEFT JOIN location l ON(l.id=r.site_id) 
                WHERE r.is_deleted='N' AND r.is_simulation='N' ".$incr."";
            
            $RowList=array();
            foreach($dbh->query($sql) as $row){
                if(isset($indexbyid)){
                    $RowList[$row['PortID']]= Rack::RowToObject($row);
                }else{
                    $RowList[]= Rack::RowToObject($row);
                }
            }
            
            return $RowList;
	}
        
        function GetMapOverview($room_id){
                
		$this->MakeSafe();
		$statusarray=array();	
                $filter = array();
                
                if($room_id != "")
                {
                    $filter['room'] = $room_id;
                }
                $room_arr = Room::GetRoomOne($filter);
                $room_res = json_decode(json_encode($room_arr), true);
                
		// check to see if map was set
		if(strlen($room_res[0]['Front_picture'])){
			$mapfile="uploads/room".DIRECTORY_SEPARATOR.$room_res[0]['Front_picture'];

			$overview=array();
			$space=array();
			$weight=array();
			$power=array();
			$temperature=array();
			$humidity=array();
			$realpower=array();
			$colors=array();
			// map was set in config check to ensure a file exists before we attempt to use it
			if(file_exists($mapfile)){
                                
				$this->dcconfig=new Config();
				$dev=new Device();
				$templ=new DeviceTemplate();
				$cab=new Cabinet();
                                $rack=new Rack();
				
				// get all color codes and limits for use with loop below
				$CriticalColor=html2rgb($this->dcconfig->ParameterArray["CriticalColor"]);
				$CautionColor=html2rgb($this->dcconfig->ParameterArray["CautionColor"]);
				$GoodColor=html2rgb($this->dcconfig->ParameterArray["GoodColor"]);
				$SpaceRed=intval($this->dcconfig->ParameterArray["SpaceRed"]);
				$SpaceYellow=intval($this->dcconfig->ParameterArray["SpaceYellow"]);
				$WeightRed=intval($this->dcconfig->ParameterArray["WeightRed"]);
				$WeightYellow=intval($this->dcconfig->ParameterArray["WeightYellow"]);
				$PowerRed=intval($this->dcconfig->ParameterArray["PowerRed"]);
				$PowerYellow=intval($this->dcconfig->ParameterArray["PowerYellow"]);
				$unknown=html2rgb('FFFFFF');

				// Copy all colors into an array to export
				$color['unk']=array('r' => $unknown[0], 'g' => $unknown[1], 'b' => $unknown[2]);
				$color['bad']=array('r' => $CriticalColor[0], 'g' => $CriticalColor[1], 'b' => $CriticalColor[2]);
				$color['med']=array('r' => $CautionColor[0], 'g' => $CautionColor[1], 'b' => $CautionColor[2]);
				$color['low']=array('r' => $GoodColor[0], 'g' => $GoodColor[1], 'b' => $GoodColor[2]);
				$colors=$color;
                                
				// Assign color variables 
				$CriticalColor='bad';
				$CautionColor='med';
				$GoodColor='low';
				$unknownColor='unk';

				// Temperature
				$TemperatureYellow=intval($this->dcconfig->ParameterArray["TemperatureYellow"]);
				$TemperatureRed=intval($this->dcconfig->ParameterArray["TemperatureRed"]);
				
				// Humidity
				$HumidityMin=intval($this->dcconfig->ParameterArray["HumidityRedLow"]);
				$HumidityMedMin=intval($this->dcconfig->ParameterArray["HumidityYellowLow"]);			
				$HumidityMedMax=intval($this->dcconfig->ParameterArray["HumidityYellowHigh"]);				
				$HumidityMax=intval($this->dcconfig->ParameterArray["HumidityRedHigh"]);
				
				//Real Power
				$RealPowerRed=intval($this->dcconfig->ParameterArray["PowerRed"]);
				$RealPowerYellow=intval($this->dcconfig->ParameterArray["PowerYellow"]);
				
				// get image file attributes and type
				if(mime_content_type($mapfile)=='image/svg+xml'){
					$svgfile = simplexml_load_file($mapfile);
					$width = substr($svgfile['width'],0,4);
					$height = substr($svgfile['height'],0,4);
				}else{
					list($width, $height, $type, $attr)=getimagesize($mapfile);
				}
				$cdus=array();
					
				$sql = "select * from rack 
					where room_id=".intval($this->PortID);

				$rpvalues=$this->query($sql);
                                
				foreach($rpvalues as $cduRow){
                                        
					$cabid=$cduRow['id'];
					$voltamp=1;
					$rp=1;
					$bs=1;

					if($bs==1){
						$maxDraw=$voltamp / 1.732;
					}elseif($bs==2){
						$maxDraw=$voltamp;
					}else{
						$maxDraw=$voltamp * 1.732;
					}

					// De-rate all breakers to 80% sustained load
					$maxDraw*=0.8;

					// Only keep the highest percentage of any single CDU in a cabinet
					if ( $maxDraw > 0 ) {
						$pp=intval($rp / $maxDraw * 100);
					} else {
						$pp = 0;
					}
					$cdus[$cabid]=(isset($cdus[$cabid]) && $cdus[$cabid]>$pp)?$cdus[$cabid]:$pp;
				}
                                
				$rack->PortID = $this->PortID;
				$cabList_arr = $rack->GetLocationRoomList($rack->PortID);
				$cabList = json_decode(json_encode($cabList_arr), true);
                                
				$titletemp=0;
				$titlerp=0;
				// read all cabinets and calculate the color to display on the cabinet

				foreach($cabList as $cabRow){
                                        
					if ($cabRow['MapX1']==$cabRow['MapX2'] || $cabRow['MapY1']==$cabRow['MapY2']){
						continue;
					}
					$currentHeight=$cabRow['Height'];
					
					$metrics = CabinetMetrics::getMetrics( $cabRow['PortID'] );
					
					$currentTemperature=$metrics->IntakeTemperature;
					$currentHumidity=$metrics->IntakeHumidity;
					$currentRealPower=$metrics->MeasuredPower;

					$used=$metrics->SpaceUsed;
					// check to make sure the cabinet height is set to keep errors out of the logs
					if(!isset($cabRow['Height'])||$cabRow['Height']==0){$SpacePercent=100;}else{$SpacePercent=number_format($metrics->SpaceUsed /$cabRow['Height'] *100,0);}
					// check to make sure there is a weight limit set to keep errors out of logs
					if(!isset($cabRow['Max_weight'])||$cabRow['Max_weight']==0){$WeightPercent=0;}else{$WeightPercent=number_format($metrics->CalculatedWeight /$cabRow['Max_weight'] *100,0);}
					// check to make sure there is a kilowatt limit set to keep errors out of logs
					if(!isset($cabRow['Max_kw'])||$cabRow['Max_kw']==0){$PowerPercent=0;}else{$PowerPercent=number_format(($metrics->CalculatedPower /1000 ) /$cabRow['Max_kw'] *100,0);}
					if(!isset($cabRow['Max_kw'])||$cabRow['Max_kw']==0){$RealPowerPercent=0;}else{$RealPowerPercent=number_format(($metrics->MeasuredPower /1000 ) /$cabRow['Max_kw'] *100,0, ",", ".");}

					// check for individual cdu's being weird
					if(isset($cdus[$rack->PortID])){$RealPowerPercent=($RealPowerPercent>$cdus[$rack->PortID])?$RealPowerPercent:$cdus[$rack->PortID];}
				
					//Decide which color to paint on the canvas depending on the thresholds
					if($SpacePercent>$SpaceRed){$scolor=$CriticalColor;}elseif($SpacePercent>$SpaceYellow){$scolor=$CautionColor;}else{$scolor=$GoodColor;}
					if($WeightPercent>$WeightRed){$wcolor=$CriticalColor;}elseif($WeightPercent>$WeightYellow){$wcolor=$CautionColor;}else{$wcolor=$GoodColor;}
					if($PowerPercent>$PowerRed){$pcolor=$CriticalColor;}elseif($PowerPercent>$PowerYellow){$pcolor=$CautionColor;}else{$pcolor=$GoodColor;}
					if($RealPowerPercent>$RealPowerRed){$rpcolor=$CriticalColor;}elseif($RealPowerPercent>$RealPowerYellow){$rpcolor=$CautionColor;}else{$rpcolor=$GoodColor;}
					
					if($currentTemperature==0){$tcolor=$unknownColor;}
						elseif($currentTemperature>$TemperatureRed){$tcolor=$CriticalColor;}
						elseif($currentTemperature>$TemperatureYellow){$tcolor=$CautionColor;}
						else{$tcolor=$GoodColor;}
					
					if($currentHumidity==0){$hcolor=$unknownColor;}
						elseif($currentHumidity>$HumidityMax || $currentHumidity<$HumidityMin){$hcolor=$CriticalColor;}
						elseif($currentHumidity>$HumidityMedMax || $currentHumidity<$HumidityMedMin) {$hcolor=$CautionColor;}
						else{$hcolor=$GoodColor;}
										
					foreach(array($scolor,$wcolor,$pcolor,$tcolor,$hcolor,$rpcolor) as $cc){
						if($cc=='bad'){
							$color='low';break;
						}elseif($cc=='med'){
							$color='med';break;
						}else{
							$color='low';
						}
					}
					
					$overview[$cabRow['PortID']]=$color;
					$space[$cabRow['PortID']]=$scolor;
					$weight[$cabRow['PortID']]=$wcolor;
					$power[$cabRow['PortID']]=$pcolor;
					$temperature[$cabRow['PortID']]=$tcolor;
					$humidity[$cabRow['PortID']]=$hcolor;
					$realpower[$cabRow['PortID']]=$rpcolor;
					$airflow[$cabRow['PortID']]= "Bottom"; //$cabRow->FrontEdge
				}
			}
			
			$tempSQL = "select max(LastRead) as ReadingTime from fac_sensorreadings where DeviceID in (select DeviceID from fac_Device where DeviceType='Sensor' and Cabinet in (select CabinetID from fac_cabinet where DataCenterID=" . $this->PortID . "))";
			$tempRes = $this->query( $tempSQL );
			$tempRow = $tempRes->fetch();
			
			$pwrSQL = "select max(LastRead) as ReadingTime from fac_pdustats where PDUID in (select DeviceID from fac_device where DeviceType='CDU' and Cabinet in (select CabinetID from fac_cabinet where DataCenterID=" . $this->PortID . "))";
			$pwrRes = $this->query( $pwrSQL );
			$pwrRow = $pwrRes->fetch();
			
			//Key
			$overview['title']=__("Composite View of Rack");
			$space['title']=__("Occupied Space");
			$weight['title']=__("Calculated Weight");
			$power['title']=__("Calculated Power Usage");
			$temperature['title']=($tempRow["ReadingTime"]>0)?__("Measured on")." ".date( 'c', strtotime( $tempRow["ReadingTime"])):__("no data");
			$humidity['title']=($tempRow["ReadingTime"]>0)?__("Measured on")." ".date( 'c', strtotime( $tempRow["ReadingTime"])):__("no data");
			$realpower['title']=($pwrRow["ReadingTime"]>0)?__("Measured on")." ".date( 'c', strtotime( $pwrRow["ReadingTime"])):__("no data");
			$airflow['title']=__("Air Flow");

			$statusarray=array('overview' => $overview,
								'space' => $space,
								'weight' => $weight,
								'power' => $power,
								'humidity' => $humidity,
								'temperature' => $temperature,
								'realpower' => $realpower,
								'airflow' => $airflow,
								'colors' => $colors
							);
                        
		}
		return $statusarray;
	}
        
        function GetDeviceAlocationList($filter){
            global $dbh;
            $incr = "";
            if(isset($filter['rack'])){
                $incr .= " AND rack_id =".$filter['rack'];
            }
            $sql="SELECT *
                FROM device  
                WHERE is_deleted='N' AND is_simulation='N' {$incr}";
            
            $RackList = array();
            foreach($dbh->query($sql) as $row){
                $RackList[]= $row;
            }
            
            return $RackList;
	}
        
	// QUERY FOR DASHBOARD COUNTER
	static function GetDashRackList(){
            global $dbh;
            
            $sql="SELECT count(*) as total_rack 
                FROM rack r 
                LEFT JOIN room l ON(l.id=r.room_id) WHERE r.is_deleted='N' AND r.is_simulation='N'";
            
            $RackList = array('total_rack' => 0);
            $result = $dbh->query($sql);
            if($result){
                $RackList = $result->fetch();
            }
            
            return $RackList;
	}
        function getLocationID($room_id){
            global $dbh;
            
            $sql="SELECT location_id as locationID FROM room WHERE id=".$room_id;
            
            $RoomList = array();
            $result_RoomList = $dbh->query($sql);
            $RoomList = $result_RoomList ? $result_RoomList->fetch() : array();
            
            return $RoomList;
        }
	function CreateObject(){
            global $dbh;

            $this->MakeSafe();
            if($this->Descending != "")
            {
                $descending = $this->Descending;
            } else {
                $descending = "N";
            }
            $installed_at = date('Y-m-d',strtotime($this->Installed_at));
            $ins_location_id = $this->getLocationID($this->Site);
            
            $sql="INSERT INTO rack SET name=\"$this->Name\", site_id=".$ins_location_id['locationID'].", room_id=\"$this->Site\", group_no=\"$this->Group_no\", row_position=\"$this->Row_position\", facility=\"$this->Facility\", serial_no=\"$this->Serial_no\", is_descending=\"$descending\", type=\"$this->Type\", width=\"$this->Width\", height=\"$this->Height\", position=\"$this->Position\", model=\"$this->Model\", key_info=\"$this->Key_info\", max_kw=\"$this->Max_kw\", max_weight=\"$this->Max_weight\", installed_at=\"$installed_at\", assign_to=\"$this->Assign_to\", tag=\"$this->Tag\", comment=\"$this->Comment\", x1=\"$this->X1\", x2=\"$this->X2\", y1=\"$this->Y1\", y2=\"$this->Y2\", mapzoom=\"$this->Mapzoom\", created='".date('Y-m-d')."'";
            
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

            $sql="UPDATE rack SET is_deleted='Y' AND is_simulation='N' WHERE id=$this->PortID;";

            (class_exists('LogActions'))?LogActions::LogThis($this):'';
            return $this->query($sql);
	}

	function UpdateObject(){
            global $dbh;
            $this->MakeSafe();
            if($this->Descending != "")
            {
                $descending = $this->Descending;
            } else {
                $descending = "N";
            }
            $installed_at = date('Y-m-d',strtotime($this->Installed_at));
            $ins_location_id = $this->getLocationID($this->Site);
            
            $sql="UPDATE rack SET name=\"$this->Name\", site_id=".$ins_location_id['locationID'].", room_id=\"$this->Site\", group_no=\"$this->Group_no\", row_position=\"$this->Row_position\", facility=\"$this->Facility\", serial_no=\"$this->Serial_no\", is_descending=\"$descending\", type=\"$this->Type\", width=\"$this->Width\", height=\"$this->Height\", position=\"$this->Position\", model=\"$this->Model\", key_info=\"$this->Key_info\", max_kw=\"$this->Max_kw\", max_weight=\"$this->Max_weight\", installed_at=\"$installed_at\", assign_to=\"$this->Assign_to\", tag=\"$this->Tag\", comment=\"$this->Comment\", x1=\"$this->X1\", x2=\"$this->X2\", y1=\"$this->Y1\", y2=\"$this->Y2\", mapzoom=\"$this->Mapzoom\", last_updated='".date('Y-m-d')."' WHERE id=$this->PortID;";
            
            $old=new Rack();
            $old->PortID=$this->PortID;
            $old->GetOrderByID();

            // UPDATE DEVICE POSITION IF RACK CHANGE SETTING FOR ASCEDING / DESCENDING 
            $device_sql="SELECT * FROM device WHERE is_deleted='N' AND is_simulation='N' AND rack_id=$this->PortID";
            
            $DeviceList= array();
            $DeviceList = $dbh->query($device_sql)->fetchAll();
            
            $this->MakeDisplay();
            
            if($descending == "N"){
                
                if(count($DeviceList) > 0){
                    foreach($DeviceList as $val){
                        if($val['height'] > 1)
                        {
                            $new_position = ($val['position'] - $val['height']) + 1; 
                            $device_up_sql="UPDATE device SET position=".$new_position." WHERE id=".$val['id'].";";
                            $this->query($device_up_sql);
                        }
                    }
                }
            } else {
                if(count($DeviceList) > 0){
                    foreach($DeviceList as $val){
                        if($val['height'] > 1)
                        {
                            $new_position = ($val['position'] + $val['height']) - 1; 
                            $device_up_sql="UPDATE device SET position=".$new_position." WHERE id=".$val['id'].";";
                            $this->query($device_up_sql);
                        }
                    }
                }
            }
            
            
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
            if(isset($filter['rack'])){
                $incr .= " AND r.id =".$filter['rack'];
            }
            if(isset($filter['room'])){
                $incr .= " AND r.room_id =".$filter['room'];
            }
            if(isset($filter['height'])){
                $incr .= " AND r.height =".$filter['height'];
            }
            if(isset($filter['width'])){
                $incr .= " AND r.width =".$filter['width'];
            }
            if(isset($filter['type'])){
                $incr .= " AND r.type ='".$filter['type']."'";
            }

            $sql="SELECT r.*,l.name as room_name 
                FROM rack r 
                LEFT JOIN room l ON(l.id=r.room_id) 
                WHERE r.is_deleted='N' AND r.is_simulation='N' ".$incr." ORDER BY {$sort_on} {$sort_by};";
            
            $ManufacturerList=array();
            foreach($dbh->query($sql) as $row){
                    if($indexbyid){
                            $ManufacturerList[$row['PortID']]=Rack::RowToObject($row);
                    }else{
                            $ManufacturerList[]=Rack::RowToObject($row);
                    }
            }
            $result = json_decode(json_encode($ManufacturerList), true);
            
            // XLS CODE START
            // filename for download
            $filename = "Rack_" . time() . ".xls";

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $tab_sep = "\t";
            $line_sep = "\r\n";
            $header_html = "";

            $flag = false;
            foreach($result as $row) {
              if(!$flag) {
                // display field/column names as first row
                $header_html .= "Name".$tab_sep."Room".$tab_sep."Model".$tab_sep."Facility".$tab_sep."Height".$tab_sep."Width".$tab_sep."Type".$tab_sep."Serial Numner".$tab_sep."Is Descending".$tab_sep."Tag".$line_sep;
                $flag = true;
              }
              $header_html .= $row['Name'].$tab_sep.$row['RoomName'].$tab_sep.$row['Model'].$tab_sep.$row['Facility'].$tab_sep.$row['Height'].$tab_sep.$row['Width'].$tab_sep.$row['Type'].$tab_sep.$row['Serial_no'].$tab_sep.$row['Descending'].$tab_sep.$row['Tag'].$line_sep;
            }
            echo $header_html;exit;
            // XLS CODE END
        }
}
?>
