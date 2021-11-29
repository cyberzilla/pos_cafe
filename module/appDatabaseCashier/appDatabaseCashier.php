<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-31
 * Time: 3:14:20 PM
 * Filename: appStoreSettingCashier.php
 */
$tablename = "cashier";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "ch.id,us.usersFullName cashierName,us.usersPhone cashierPhone,us.usersUserName cashierUsername,cashierShift,iF(cashierActive='on','<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') cashierActive";
        $result = getDataP($conn, $field, "cashier ch LEFT JOIN `".iBase."`.users us ON ch.cashierUsersId=us.id", "us.usersFullName LIKE '%$p_search%' ORDER by ch.cashierDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "ch.id,ch.cashierUsersId,ch.cashierShift,us.usersFullName,ch.cashierActive", "cashier ch LEFT JOIN `".iBase."`.users us ON ch.cashierUsersId=us.id", "ch.id='$p_id' AND us.usersActive='on'");
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_usersFullName"));
        $data = pushData($conn, $tablename, $contentPOST);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_usersFullName"));
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
    case "loadUsers":
        $result = getDataN($conn, "us.id,us.usersFullName", "`".iBase."`.users us LEFT JOIN cashier ch  ON ch.cashierUsersId=us.id", "us.id NOT IN (SELECT cashierUsersId FROM cashier) AND us.usersFullName LIKE '%$p_search%' AND us.usersActive='on' LIMIT 10");
        echo json_encode($result);
        break;
    default:
        break;
}