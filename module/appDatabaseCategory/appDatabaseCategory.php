<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-26
 * Time: 10:01:59 PM
 * Filename: appStoreSettingCategory.php
 */

$tablename = "category";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "id,categoryName,categorySlug,IF(categoryParentId<>'','Child','Parent') categoryType";
        $result = getDataP($conn, $field, $tablename, "categoryName LIKE '%$p_search%' ORDER by categoryDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $result = getData($conn, "*,IF(ISNULL(categoryParentId),\"parent\",\"child\") categoryType", $tablename, $contentPOST);
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $extra = $p_categoryType!=="parent"?",categoryParentId='$p_categoryParentId'":",categoryParentId=NULL";
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_categoryType","p_categoryParentId"));
        $data = pushData($conn, $tablename, $contentPOST.$extra);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $extra = $p_categoryType!=="parent"?",categoryParentId='$p_categoryParentId'":",categoryParentId=NULL";
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_categoryType","p_categoryParentId"));
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

    case "load_categoryparent":
        $field = "id,categoryName";
        $data = getDataN($conn, $field, $tablename,"");
        foreach ($data['data'] as $val){
            $parent[] = array("id"=>$val['id'],"text"=>$val['categoryName']);
        }
        $result = array("data"=>$parent);
        echo json_encode($result);
        break;
    default:
        break;
}