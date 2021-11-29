<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-30
 * Time: 2:00:07 AM
 * Filename: appStoreSettingVoucher.php
 */
$tablename = "voucher";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "id,voucherExpiredType,voucherName,voucherValue,voucherStatus,CONCAT(voucherUsed,' kali') voucherUsed,iF(voucherActive='on','<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') voucherActive";
        $result = getDataP($conn, $field, "viewvoucher", "voucherName LIKE '%$p_search%' ORDER by voucherDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $result = getData($conn, "*", $tablename, $contentPOST);
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_voucherValueX","p_voucherLimit","p_voucherExpired"));
        $extra = $p_voucherExpiredType==="byDate"?",voucherExpired='$p_voucherExpired',voucherLimit=NULL":",voucherExpired=NULL,voucherLimit='$p_voucherLimit'";
        $data = pushData($conn, $tablename, $contentPOST.$extra);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_voucherValueX","p_voucherLimit","p_voucherExpired"));
        $extra = $p_voucherExpiredType==="byDate"?",voucherExpired='$p_voucherExpired',voucherLimit=NULL":",voucherExpired=NULL,voucherLimit='$p_voucherLimit'";
        $data = updateData($conn, $tablename, $contentPOST.$extra, "id='$p_mainId'");
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

    case "getVoucherCode":
        $result = getData($conn, "*", $tablename, "id='$p_id'");
        echo json_encode($result);
        break;

    case "checkVoucherCode":
        $data = getDataN($conn, "*", $tablename, "voucherCode='$p_voucherCode'");
        echo count($data['data'])<1?"true":"false";
        break;
    default:
        break;
}