<?php
/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-12-14
 * Time: 1:07:48 PM
 * Filename: appDatabaseCustomerReview.php
 */
$tablename = "review";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "id,IF(reviewReaded='',CONCAT('<div class=\"readed red animate__animated animate__headShake animate__infinite\">',reviewName,'</div>'),reviewName) reviewName,reviewDateTime,IF(reviewShow='on','<i class=\"fa fa-eye green\"></i>','<i class=\"fa fa-eye-slash red\"></i>') reviewShow,CONCAT('<div class=\"rate\" data-hits=\"',reviewHits,'\"></div>') reviewHits";
        $result = getDataP($conn, $field, $tablename, "reviewName LIKE '%$p_search%' ORDER by reviewDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $result = getData($conn, "*", $tablename, $contentPOST);
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $data = pushData($conn, $tablename, $contentPOST);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $data = updateData($conn, $tablename, $contentPOST, "id='$p_mainId'");
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "remove_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $data = removeData($conn, $tablename, $contentPOST);
        if ($data['status'] === 'success') {
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_removed);
            $result = array_merge($data, $message);
        } else {
            $result = $data;
        }
        echo json_encode($result);
        break;

    case "showReview":
        $review = getData($conn,"*",$tablename,"id='$p_id'");
        updateData($conn, $tablename, "reviewReaded='on'","id='$p_id'");
        echo json_encode($review);
        break;

        default:
        break;
}