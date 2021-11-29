<?php
/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-11-15
 * Time: 5:45:17 AM
 * Filename: appDatabaseShortcutMenu.php
 */
$tablename = "shortcut";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "st.id,CONCAT(vp.productCode,' - ',vp.productName) productName,st.shortcutOrder, iF(st.shortcutActive='on','<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') shortcutActive";
        $result = getDataP($conn, $field, "shortcut st LEFT JOIN viewproducts vp ON st.shortcutProductId=vp.id", "productName LIKE '%$p_search%' ORDER by shortcutDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "st.*, pd.productName", "shortcut st LEFT JOIN product pd ON st.shortcutProductId=pd.id", "st.id='$p_id'");
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_productName"));
        $data = pushData($conn, $tablename, $contentPOST);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_productName"));
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

    case "loadProduct":
        $result = getDataN($conn, "id,productName", "viewproducts", "productName LIKE '%$p_search%' LIMIT 5");
        echo json_encode($result);
        break;

    default:
        break;
}