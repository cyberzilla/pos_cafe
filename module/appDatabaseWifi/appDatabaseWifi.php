<?php
/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2022-05-27
 * Time: 2:46:33 PM
 * Filename: appDatabaseWifi.php
 */
$tablename = "wifi";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

require_once sysclass."routerosAPI.class.php";
$mikrotik = new routerosAPI();
$localIP = "192.168.1.1";
$localUser = "admin";
$localPass = "RL@3221";

//$mikrotik->write("/ip/hotspot/user/print");
//$mikrotik->write("/ip/hotspot/user/profile/print");
//$mikrotik->write("/user/group/print");
//$result = $mikrotik->read();

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "*";
        $result = getDataP($conn, $field, $tablename, "wifiUser LIKE '%$p_search%' ORDER by wifiDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        if (!$mikrotik->connect($localIP,$localUser,$localPass)){
            $result = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_removed);
        }else{
            $comment = date("Y-m-d H:i:s");
            $mikrotik->write("/ip/hotspot/user/add", false);
            $mikrotik->write("=name=" . $p_wifiUser, false);
            $mikrotik->write("=password=" . $p_wifiPassword, false);
            $mikrotik->write("=profile=" . $p_wifiProfile, false);
            $mikrotik->write("=comment=" . $comment);
            $mikrotik->read();

            $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
            $data = pushData($conn, $tablename, $contentPOST.",wifiComment='$comment'");
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
            $result = array_merge($data, $message);
        }

        echo json_encode($result);
        break;


    case "remove_" . $p_slug:
//        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
//        $data = removeData($conn, $tablename, $contentPOST);
//        if ($data['status'] === 'success') {
//            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_removed);
//            $result = array_merge($data, $message);
//        } else {
//            $result = $data;
//        }

        //Penghapusan di dalam mikrotik

        if (!$mikrotik->connect($localIP,$localUser,$localPass)){
            $result = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_removed);
        }else{
            $mikrotik->write("/ip/hotspot/user/remove",false);
            $mikrotik->write("=.id=*A",true);
//            $mikrotik->write("=name=wl5dpk0",true);
            $mikrotik->read();
        }

        echo json_encode($result);
        break;


    case "loadProfileMikrotik":
        if (!$mikrotik->connect($localIP,$localUser,$localPass)){
            $result = array("status"=>"error", "desc" => "(".$mikrotik->error_no.") ".$mikrotik->error_str);
        }else{
            $mikrotik->write("/ip/hotspot/user/profile/print");
            $results = $mikrotik->read();
        }
        foreach ($results as $val){
            $parent[] = array("id"=>$val['name'],"text"=>$val['name']);
        }
        $result = array("data"=>$parent);
        echo json_encode($result);
        break;

    case "checkWifiUser":
        $data = getDataN($conn, "*", $tablename, "wifiUser='$p_wifiUser'");
        echo count($data['data'])<1?"true":"false";
        break;

    case "loadParam":
        if (!$mikrotik->connect($localIP,$localUser,$localPass)){
            $result = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_removed);
        }else{
            $mikrotik->write("/ip/hotspot/user/print");
            //$mikrotik->write("=name=wl5dpk0");
//            $mikrotik->write("/ip/hotspot/user/getall");
            //$mikrotik->write("=.proplist=name=.proplist=password=.proplist=mac-address=.proplist=comment?.id=*5");
//            $mikrotik->write("=.proplist=.id?name==wl5dpk0",true);
            $result = $mikrotik->read();
        }

        echo json_encode($result);
        break;

    default:
        break;
}