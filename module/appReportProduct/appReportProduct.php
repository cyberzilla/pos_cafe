<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-03-16
 * Time: 5:16:18 PM
 * Filename: appReportProduct.php
 */
$tablename = "orderdetail";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "odn.id, odn.productName,odn.categoryName, CONCAT(SUM(odn.orderdetailQuantity),' ',odn.productUnitName) orderQuantity";
        $result = getDataP($conn, $field, "(SELECT odt.orderdetailInvoice,SUM(odt.orderdetailQuantity) orderdetailQuantity, odt.orderdetailDateTime, vp.productName, vp.categoryName,vp.productUnitName,vp.id FROM orderdetail odt LEFT JOIN viewproducts vp ON odt.orderdetailProductId = vp.id GROUP BY odt.orderdetailProductId,odt.orderdetailInvoice ORDER BY odt.orderdetailInvoice) odn LEFT JOIN `order` od on odn.orderdetailInvoice = od.orderInvoice", "od.orderStatus='success' AND odn.productName LIKE '%$p_search%' AND (odn.orderdetailDateTime BETWEEN '$p_orderdetailDateTimeStart 00:00:00' AND '$p_orderdetailDateTimeEnd 23:59:59')  GROUP BY odn.id  ORDER BY SUM(odn.orderdetailQuantity) DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "downloadReport":
        require_once (sysclass."simplexlsxgen.class.php");
        $sales = getDataN($conn, "vp.id, vp.productName,SUM(od.orderdetailQuantity) `orderQuantity`, vp.productUnitName , vp.categoryName", "vieworder vd LEFT JOIN orderdetail od ON vd.orderInvoice = od.orderdetailInvoice LEFT JOIN viewproducts vp ON od.orderdetailProductId = vp.id","vp.productName LIKE '%$p_search%' AND (orderdetailDateTime BETWEEN '$p_orderdetailDateTimeStart 00:00:00' AND '$p_orderdetailDateTimeEnd 23:59:59') GROUP BY vp.id  ORDER BY orderdetailQuantity DESC");
        $colfinish = count($sales['data']);

        $xlsx_data = [
            ["TGL:",$p_orderdetailDateTimeStart." ~ ".$p_orderdetailDateTimeEnd],
            ["NO",'NAMA PRODUK',"KATEGORI", 'PENJUALAN',"UNIT"]
        ];
        $i=2;
        foreach ($sales['data'] as $pb){
            $xlsx_data[] = [($i-1),$pb['productName'],$pb['categoryName'],$pb['orderQuantity'],$pb['productUnitName']];
            $i++;
        }

        $xlsx = SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->downloadAs("ReportPenjualanProduk_".slugConverter($p_orderdetailDateTimeStart." sampai ".$p_orderdetailDateTimeEnd).".xlsx");
        break;
    default:
        break;
}