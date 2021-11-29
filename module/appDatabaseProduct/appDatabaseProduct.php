<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-27
 * Time: 10:19:53 AM
 * Filename: appDatabaseProduct.php
 */
$tablename = "product";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "p.id,p.productName,c.categoryName, p.productWeight,iF(p.productActive='on','<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') productActive";
        $result = getDataP($conn, $field, "product p LEFT JOIN category c ON p.productCategoryId=c.id", "productName LIKE '%$p_search%' ORDER by productDateTime DESC", $p_page, $p_perpage, true);
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

    case "load_category":
        $field = "id,categoryName";
        $data = getDataN($conn, $field, "category","categoryParentId!=''");
        foreach ($data['data'] as $val){
            $parent[] = array("id"=>$val['id'],"text"=>$val['categoryName']);
        }
        $result = array("data"=>$parent);
        echo json_encode($result);
        break;
    default:
        break;
}