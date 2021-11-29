<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-30
 * Time: 2:00:01 AM
 * Filename: appStoreSettingDiscount.php
 */
$tablename = "discount";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "dc.id,pd.productName,dc.discountByPercent,dc.discountByPrice,IFNULL(dc.discountExpired,'Permanent') discountExpired,iF(dc.discountActive='on','<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') discountActive, CONCAT(IF(dc.discountByMinimalOrder='on',dc.discountMinimalOrder,0),' ',pd.productUnitName) discountMinimalOrder";
        $result = getDataP($conn, $field, "discount dc LEFT JOIN product pd ON dc.discountProductId=pd.id", "pd.productName LIKE '%$p_search%' AND pd.productActive='on' ORDER by dc.discountDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "dc.discountType,dc.id,pd.productName,dc.discountProductId,dc.discountByPercent,dc.discountByPrice,dc.discountExpired,pc.priceSellingPrice,dc.discountActive,dc.discountByMinimalOrder,dc.discountMinimalOrder", "discount dc LEFT JOIN product pd ON dc.discountProductId=pd.id LEFT JOIN price pc ON pc.priceProductId=pd.id", "dc.id='$p_id' AND pd.productActive='on'");
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_productName","p_priceSellingPrice","p_priceSellingPriceX","p_discountByPercentX","p_discountByPriceX","p_discountExpired","p_discountMinimalOrder"));
        $extra = $p_discountType==="permanent"?",discountExpired=NULL":",discountExpired='$p_discountExpired'";
        $extra2 = $p_discountByMinimalOrder!=="on"?",discountMinimalOrder=NULL":",discountMinimalOrder='$p_discountMinimalOrder'";
        $data = pushData($conn, $tablename, $contentPOST.$extra.$extra2);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_productName","p_priceSellingPrice","p_priceSellingPriceX","p_discountByPercentX","p_discountByPriceX","p_discountExpired","p_discountMinimalOrder"));
        $extra = $p_discountType==="permanent"?",discountExpired=NULL":",discountExpired='$p_discountExpired'";
        $extra2 = $p_discountByMinimalOrder!=="on"?",discountMinimalOrder=NULL":",discountMinimalOrder='$p_discountMinimalOrder'";
        $data = updateData($conn, $tablename, $contentPOST.$extra.$extra2, "id='$p_mainId'");
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
    case "loadProduct":
        $result = getDataN($conn, "pd.id,pd.productName,pc.priceSellingPrice", "price pc LEFT JOIN product pd ON pc.priceProductId=pd.id", "pd.id NOT IN (SELECT discountProductId FROM discount) AND pd.productName LIKE '%$p_search%' AND pd.productActive='on' LIMIT 10");
        echo json_encode($result);
        break;
    default:
        break;
}