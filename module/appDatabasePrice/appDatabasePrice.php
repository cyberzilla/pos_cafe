<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-29
 * Time: 1:29:01 PM
 * Filename: appDatabasePrice.php
 */
$tablename = "price";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "pc.id,pd.productName,pc.priceCapitalPrice,pc.priceSellingPrice";
        $result = getDataP($conn, $field, "price pc LEFT JOIN product pd ON pc.priceProductId=pd.id", "pd.productName LIKE '%$p_search%' AND pd.productActive='on' ORDER by pc.priceDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "pc.id,pd.productName,pc.priceProductId,pc.priceCapitalPrice,pc.priceSellingPrice", "price pc LEFT JOIN product pd ON pc.priceProductId=pd.id", "pc.id='$p_id' AND pd.productActive='on'");
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_productName","p_priceCapitalPriceX","p_priceSellingPriceX"));
        $data = pushData($conn, $tablename, $contentPOST);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug", "p_appslug", "p_act", "p_mainId", "p_app","p_productName","p_priceCapitalPriceX","p_priceSellingPriceX"));
        $data = updateData($conn, $tablename, $contentPOST, "id='$p_mainId'");
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "remove_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug","p_act", "p_mainId", "p_app"));
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
        $result = getDataN($conn, "id,productName", "product", "id NOT IN (SELECT priceProductId FROM price) AND productName LIKE '%$p_search%' AND productActive='on' LIMIT 5");
        echo json_encode($result);
        break;
    default:
        break;
}