<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-30
 * Time: 1:24:18 AM
 * Filename: appDatabaseStock.php
 */
$tablename = "stock";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "pd.id,pd.productName,COALESCE(st.stockQuantity,0) stockQuantity";
        $result = getDataP($conn, $field, "viewproducts pd LEFT JOIN stock st ON st.stockProductId=pd.id", "pd.productName LIKE '%$p_search%' ORDER by pd.productName DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "pd.id,pd.id stockProductId,st.id stockId,pd.productName,COALESCE(st.stockQuantity,0) stockQuantity", "viewproducts pd LEFT JOIN stock st ON st.stockProductId=pd.id", "pd.id='$p_id'");
        echo json_encode($result);
        break;


    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_productName","p_stockId"));
        if($p_stockId!==""){
            $data = updateData($conn, $tablename, $contentPOST, "id='$p_stockId'");
        }else{
            $data = pushData($conn, $tablename, $contentPOST);
            $msg_updated =  $msg_created;
        }
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    default:
        break;
}