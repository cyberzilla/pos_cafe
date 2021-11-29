<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-02-04
 * Time: 12:21:37 AM
 * Filename: appReportInventory.php
 */
$tablename = "viewproducts";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "categoryName,priceCapitalPrice,priceSellingPrice,productCode,productName,productWeight,CONCAT(stockQuantity,' ',productUnitName) stockQuantity";
        $result = getDataP($conn, $field, $tablename, "productCode LIKE '%$p_search%' OR productName LIKE '%$p_search%' OR categoryName LIKE '%$p_search%' ORDER by productName", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "downloadReport":
        require_once (sysclass."simplexlsxgen.class.php");
        $type = $p_pettycashType==="all"?"":$p_pettycashType;
        $inventory = getDataN($conn, "categoryName,priceCapitalPrice,priceSellingPrice,productCode,productName,productWeight,stockQuantity,productUnitName", $tablename,"productCode LIKE '%$p_search%' OR productName LIKE '%$p_search%' OR categoryName LIKE '%$p_search%' ORDER by productName");
        $xlsx_data = [
            ["NO",'KODE PRODUK', 'NAMA PRODUK','KATEGORI','HARGA BELI','HARGA JUAL','BERAT',"JUMLAH STOK","UNIT"]
        ];
        $i=2;
        foreach ($inventory['data'] as $pb){
            $xlsx_data[] = [($i-1),$pb['productCode'],$pb['productName'],$pb['categoryName'],$pb['priceCapitalPrice'],$pb['priceSellingPrice'],$pb['productWeight'],$pb['stockQuantity'],$pb['productUnitName']];
            $i++;
        }

        $xlsx = SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->downloadAs("ReportInventori.xlsx");
        break;
    default:
        break;
}