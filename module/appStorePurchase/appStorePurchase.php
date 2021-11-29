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
        $field = "IF(purchaseType='others',CONCAT(id,':hide'),id) id,purchaseName,purchaseRealDate,purchaseType,purchasePrice,purchaseStatus,iF(purchaseImport='on','<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') purchaseImport";
        $result = getDataP($conn, $field, $tablename, "purchaseName LIKE '%$p_search%' ORDER by purchaseDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $result = getData($conn, "pc.*,sp.supplierName", "purchase pc LEFT JOIN supplier sp ON pc.purchaseSupplierId=sp.id", "pc.id='$p_id'");
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
    case "loadSupplier":
        $result = getDataN($conn, "id,supplierName", "supplier", "supplierName LIKE '%$p_search%' LIMIT 10");
        echo json_encode($result);
        break;

    case "download_format_xls":
        require_once (sysclass."simplexlsxgen.class.php");
        $purchase = getData($conn, "purchaseName,purchaseTaxValue,purchaseShipping", "purchase","id='$p_id' AND purchaseType='restock'");
        $products = getDataN($conn, "pd.id,pd.productName,pc.priceCapitalPrice", "product pd LEFT JOIN price pc ON pc.priceProductId=pd.id");
        $colfinish = count($products['data']);
        $xlsx_data = [
                        ['ID_REFERENSI', $p_id],
                        ['PAJAK', $purchase['data']['purchaseTaxValue']],
                        ['PENGIRIMAN', $purchase['data']['purchaseShipping']],
                        ['TOTAL_HARGA', "=SUM(E7:E".(6+$colfinish).")"],
                        ['FINAL_HARGA', "=SUM(B2:B4)"],
                        ['ID_PRODUK', 'NAMA_PRODUK','QUANTITY','SATUAN_HARGA','SUB_TOTAL_HARGA']
                    ];
        $i=7;
        foreach ($products['data'] as $product){
            $xlsx_data[] = [$product['id'],$product['productName'],0,$product['priceCapitalPrice'],'=SUM(C'.$i.'*D'.$i.')'];
            $i++;
        }
        $xlsx = SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->downloadAs(slugConverter($purchase['data']['purchaseName']).".xlsx");
        break;
    default:
        break;
}