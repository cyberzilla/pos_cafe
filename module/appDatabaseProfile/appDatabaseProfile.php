<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-27
 * Time: 6:19:37 AM
 * Filename: appSettings.php
 */

$tablename = "users";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug", "p_act", "p_mainId", "p_app","p_appslug"));
        $result = getData($main_conn, "*", $tablename, $contentPOST);
        $rebuild = array(
            "data"=>array(
                "id"=>$result['data']['id'],
                "usersFullName"=>$result['data']['usersFullName'],
                "usersUserName"=>$result['data']['usersUserName'],
                "usersPhone"=>$result['data']['usersPhone'],
                "usersEmail"=>$result['data']['usersEmail'],
                "usersActive"=>$result['data']['usersActive'],
                "usersAvatar"=>"[".json_encode(array(
                        "type"=>"image",
                        "path"=>$result['data']['usersAvatar'],
                        "name"=>basename($result['data']['usersAvatar']),
                        "mime"=>mime_content_type($result['data']['usersAvatar'])
                    ))."]"
            ),
            "status"=>"success"
        );
        echo json_encode($rebuild);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug", "p_act", "p_mainId", "p_app","p_appslug","p_usersPassword"));
        $encodePass = $p_usersPassword===""?"":",usersPassword='".md5($p_usersPassword)."'";
        $filePath = images."avatars/";
        if(file_exists($_FILES['usersAvatar']['tmp_name'])) {
            $ext = pathinfo($_FILES['usersAvatar']['name'], PATHINFO_EXTENSION);
            $name = pathinfo($_FILES['usersAvatar']['name'],PATHINFO_FILENAME);
            $mimeType = mime_content_type($_FILES['usersAvatar']['tmp_name']);
            $fileType = explode('/', $mimeType)[0];
            $filename = slugConverter($name).".".$ext;
            if(move_uploaded_file($_FILES['usersAvatar']['tmp_name'], $filePath.$filename)) {
                $statusUpload[] = "ok";
                $usersAvatar = ",usersAvatar='".$filePath.$filename."'";
            }else{
                $statusUpload[] = "failed";
                $usersAvatar="";
            }
        }else{
            $usersAvatar="";
        }
        $data = updateData($main_conn, $tablename, $contentPOST.$encodePass.$usersAvatar, "id='$p_mainId'");
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    default:
        break;
}