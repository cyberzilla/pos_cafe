<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-31
 * Time: 6:01:15 PM
 * Filename: appStorePurchase.php
 */
$tablename = "purchase";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "id,purchaseName,purchaseRealDate,purchaseType,purchasePrice,purchaseStatus";
        $result = getDataP($conn, $field, $tablename, "purchaseName LIKE '%$p_search%' ORDER by purchaseDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "*", "purchase", "id='$p_id'");
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_purchasePriceX","p_supplierName","p_purchaseTaxX","p_purchaseTaxValueX","p_purchaseShippingX","p_purchaseTotalPriceX"),true);
        $data = pushData($conn, $tablename, $contentPOST.",purchaseUsersId='$s_usersId'");
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_purchasePriceX","p_supplierName","p_purchaseTaxX","p_purchaseTaxValueX","p_purchaseShippingX","p_purchaseTotalPriceX"),true);
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

    default:
        break;
}