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
        $field = "pd.id,pd.productName,COALESCE(pc.priceCapitalPrice,0) priceCapitalPrice,COALESCE(pc.priceSellingPrice,0) priceSellingPrice";
        $result = getDataP($conn, $field, "viewproducts pd LEFT JOIN price pc ON pc.priceProductId = pd.id", "pd.productName LIKE '%$p_search%' ORDER BY pd.productName", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "pd.id, pc.id priceId,pd.id priceProductId,pd.productName,COALESCE(pc.priceCapitalPrice,'0') priceCapitalPrice,COALESCE(pc.priceSellingPrice,'0') priceSellingPrice", "viewproducts pd LEFT JOIN price pc ON pc.priceProductId = pd.id", "pd.id='$p_id'");
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug", "p_appslug", "p_act", "p_mainId", "p_app","p_productName","p_priceCapitalPriceX","p_priceId","p_priceSellingPriceX","p_priceProductId"));
        if($p_priceId!==""){
            $data = updateData($conn, $tablename, $contentPOST, "id='$p_priceId'");
            pushData($conn, "price_log","price_logProductId='$p_priceProductId',price_logCapitalPrice='$p_priceCapitalPrice',price_logSellingPrice='$p_priceSellingPrice'");
        }else{
            $data = pushData($conn, $tablename, $contentPOST.",priceProductId='$p_priceProductId'");
            pushData($conn, "price_log","price_logProductId='$p_priceProductId',price_logCapitalPrice='$p_priceCapitalPrice',price_logSellingPrice='$p_priceSellingPrice'");
            $msg_updated =  $msg_created;
        }
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "showHistory":
        $preorder = getDataN($conn,"*","price_log","price_logProductId='$p_id'");
        echo json_encode($preorder);
        break;

        default:
        break;
}