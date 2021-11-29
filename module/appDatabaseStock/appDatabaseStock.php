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
        $field = "st.id,pd.productName,CONCAT(st.stockQuantity,' ',pd.productUnitName) stockQuantity";
        $result = getDataP($conn, $field, "stock st LEFT JOIN product pd ON st.stockProductId=pd.id", "pd.productName LIKE '%$p_search%' AND pd.productActive='on' ORDER by st.stockDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "st.id,pd.productName,st.stockProductId,st.stockQuantity", "stock st LEFT JOIN product pd ON st.stockProductId=pd.id", "st.id='$p_id' AND pd.productActive='on'");
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
        $result = getDataN($conn, "id,productName", "product", "id NOT IN (SELECT stockProductId FROM stock) AND productName LIKE '%$p_search%' AND productActive='on' LIMIT 10");
        echo json_encode($result);
        break;

    case "import_". $p_slug:
        $allowed = explode(",", $p_acceptFile);
        $filename = $_FILES['stockFile']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $result = array(
                "status"=>"error",
                "messageSuccess"=>"Ekstensi File Tidak Diizinkan"
            );
        }else{
            switch ($ext){
                case "xlsx":
                    require_once (sysclass."simplexlsx.class.php");
                    if($xlsx = SimpleXLSX::parse($_FILES['stockFile']['tmp_name'])) {
                        $rows = $xlsx->rows();
                        $status = "success";
                        $message = "Sukses Mengimpor Data Stok";
                    }else{
                        $status = "error";
                        $message = SimpleXLSX::parseError();
                    }
                    break;
                case "xls":
                    require_once (sysclass."simplexls.class.php");
                    if($xls = SimpleXLS::parse($_FILES['stockFile']['tmp_name'])) {
                        $rows = $xls->rows();
                        $status = "success";
                        $message = "Sukses Mengimpor Data Stok";

                    }else{
                        $status = "error";
                        $message = SimpleXLS::parseError();
                    }
                    break;
                case "csv":
                    require_once (sysclass."simplecsv.class.php");
                    if($csv = SimpleCSV::import($_FILES['stockFile']['tmp_name'])){
                        $rows = $csv;
                        $status = "success";
                        $message = "Sukses Mengimpor Data Stok";
                    }else{
                        $status = "error";
                        $message = "Tidak Dapat Memuat Data Stok";
                    }
                    break;
            }
        }
        if($status==="success"){
            if(floatval($rows[4][1])===floatval($p_purchaseTotalPrice) && intval($rows[0][1])===intval($p_purchasedetailPurchaseId)){
                // Process Rows disini
                for($i=6;$i<count($rows);$i++){
                    #Colom 0 = Id Produk #Colom 2 = Quantity
                    #Update Stok (Tambah Stok yang Lama)
                    updateData($conn, "stock", "stockQuantity=stockQuantity+'".$rows[$i][2]."'","stockProductId='".$rows[$i][0]."'");
                    #Save purchasedetail
                    pushData($conn, "purchasedetail", "purchasedetailPurchaseId='$p_purchasedetailPurchaseId',purchasedetailProductId='".$rows[$i][0]."',purchasedetailQuantity='".$rows[$i][2]."',purchasedetailSubTotalPrice='".$rows[$i][4]."'");
                }
                //Update Purchase
                updateData($conn, "purchase", "purchaseImport='on'","id='$p_purchasedetailPurchaseId'");
                $result = array(
                    "status"=>$status,
                    "messageSuccess"=>$message
                );
            }else{
                $result = array(
                    "status"=>"error",
                    "messageSuccess"=>"Data referensi pembelian tidak cocok dengan data yang diimpor!"
                );
            }
        }else{
            $result = array(
                "status"=>$status,
                "messageSuccess"=>$message
            );
        }
        echo json_encode($result);
        break;
    case "loadPurchase":
        $result = getDataN($conn, "id,purchaseName,CONCAT('(Rp. ',FORMAT(purchaseTotalPrice,0,'id_ID'),')') purchaseTotalPriceFormat,purchaseTotalPrice", "purchase", "purchaseName LIKE '%$p_search%' AND purchaseImport='' AND purchaseType='restock'");
        echo json_encode($result);
        break;
    default:
        break;
}