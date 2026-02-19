<?php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$log = new LogActions();

$page = $_POST['page'];
$filter_2 = array();
$filter_2['sort_on'] = "Time";
$filter_2['sort_by'] = "DESC";
$filter_2['class'] = $_POST["class"];
$filter_2['object'] = $_POST["object"];
$filter_2['page'] = $page;
$log_arr = $log->GetClassLog($filter_2);
$log_res = json_decode(json_encode($log_arr), true);

$html_content = "";
$respone = array();

if(count($log_res) > 0)
{
    foreach($log_res as $val){
        $html_content .= "<tr>
                            <td>".$val['UserID']."</td>
                            <td>".$val['Class']."</td>
                            <td>".$val['Property']."</td>
                            <td>".$val['OldVal']."</td>
                            <td>".$val['NewVal']."</td>
                            <td>".$val['Time']."</td>
                        </tr>";
    }
}
$respone["status"] = "success";
$respone["res"] = $html_content;

echo json_encode($respone);
exit;
?>