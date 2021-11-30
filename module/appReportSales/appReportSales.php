<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-20
 * Time: 10:08:08 AM
 * Filename: appReportSales.php
 */
$tablename = "`order`";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "*";
        $type = $p_orderType==="all"?"":$p_orderType;
        $status = $p_orderStatus==="all"?"":$p_orderStatus;
        $result = getDataP($conn, $field, $tablename, "orderStatus LIKE '%$status%' AND orderType LIKE '%$type%' AND orderInvoice LIKE '%$p_search%' AND (orderDateTime BETWEEN '$p_orderDateTimeStart 00:00:00' AND '$p_orderDateTimeEnd 23:59:59') ORDER by orderDateTime DESC", $p_page, $p_perpage, true);
        $customData = getDataN($conn, "od.orderInvoice,od.orderStatus,od.orderTotalPrice", "`vieworder` od", "od.orderStatus LIKE '%$status%' AND od.orderType LIKE '%$type%' AND od.orderInvoice LIKE '%$p_search%' AND (od.orderDateTime BETWEEN '$p_orderDateTimeStart 00:00:00' AND '$p_orderDateTimeEnd 23:59:59') GROUP BY od.id ORDER BY od.orderDateTime DESC",true);
        $result['custom'] = $customData['data'];
        echo json_encode($result);
        break;

    case "downloadReport":
        require_once (sysclass."simplexlsxgen.class.php");
        $type = $p_orderType==="all"?"":$p_orderType;
        $status = $p_orderStatus==="all"?"":$p_orderStatus;
        $sales = getDataN($conn, "*", $tablename,"orderStatus LIKE '%$status%' AND orderType LIKE '%$type%' AND orderInvoice LIKE '%$p_search%' AND (orderDateTime BETWEEN '$p_orderDateTimeStart 00:00:00' AND '$p_orderDateTimeEnd 23:59:59') ORDER by orderDateTime DESC");
        $colfinish = count($sales['data']);
        if($type==="preorder"){
            $xlsx_data = [
                ["TGL:",$p_orderDateTimeStart." ~ ".$p_orderDateTimeEnd],
                ["NO",'TANGGAL',"INVOICE","PANJAR",'DISKON','VOUCHER',"PAJAK","HARGA BELI","HARGA JUAL",'TOTAL HARGA',"LABA",'TIPE','STATUS']
            ];
            $i=2;
            foreach ($sales['data'] as $pb){
                $xlsx_data[] = [($i-1),$pb['orderDateTime'],$pb['orderInvoice'],$pb['orderDownPayment'],$pb['orderTotalDiscountValue'],$pb['orderVoucherValue'],$pb['orderTaxValue'],$pb['orderTotalCapitalPrice'],$pb['orderPrice'],$pb['orderTotalPrice'],(floatval($pb['orderTotalPrice'])-floatval($pb['orderTotalCapitalPrice'])),$pb['orderType'],$pb['orderStatus']];
                $i++;
            }
        }else{
            $xlsx_data = [
                ["TGL:",$p_orderDateTimeStart." ~ ".$p_orderDateTimeEnd],
                ["NO",'TANGGAL',"INVOICE", 'DISKON','VOUCHER',"PAJAK","HARGA BELI","HARGA JUAL",'TOTAL HARGA',"LABA",'TIPE','STATUS']
            ];
            $i=2;
            foreach ($sales['data'] as $pb){
                $xlsx_data[] = [($i-1),$pb['orderDateTime'],$pb['orderInvoice'],$pb['orderTotalDiscountValue'],$pb['orderVoucherValue'],$pb['orderTaxValue'],$pb['orderTotalCapitalPrice'],$pb['orderPrice'],$pb['orderTotalPrice'],(floatval($pb['orderTotalPrice'])-floatval($pb['orderTotalCapitalPrice'])),$pb['orderType'],$pb['orderStatus']];
                $i++;
            }
        }

        $xlsx = SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->downloadAs("ReportPenjualan_".slugConverter($p_orderDateTimeStart." sampai ".$p_orderDateTimeEnd).".xlsx");
        break;

    case "downloadOrderDetail":
        require_once (sysclass."simplexlsxgen.class.php");
        $detail = getDataN($conn, "*", "orderdetail od LEFT JOIN viewproducts vp ON od.orderdetailProductId=vp.id","orderdetailInvoice='$p_orderInvoice'");
        $xlsx_data = [
            ["INV:",$p_orderInvoice],
            ["NO","TANGGAL","INVOICE","NAMA PRODUK",'DISKON','HARGA SATUAN',"KUANTITAS",'SUBTOTAL']
        ];
        $i=2;
        foreach ($detail['data'] as $pb){
            $xlsx_data[] = [($i-1),$pb['orderdetailDateTime'],$pb['orderdetailInvoice'],$pb['productName'],$pb['orderdetailSubDiscountValue'],$pb['orderdetailPrice'],$pb['orderdetailQuantity']." ".$pb['productUnitName'],$pb['orderdetailSubTotalPrice']];
            $i++;
        }
        $xlsx = SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->downloadAs("ReportPenjualan_".$p_orderInvoice.".xlsx");
        break;

    case "showOrderDetail":
        $order = getData($conn,"*",$tablename,"orderInvoice='$p_orderInvoice'");
        $detail = getDataN($conn, "vp.productName,vp.productCode,vp.productUnitName,SUM(od.orderdetailQuantity) orderdetailQuantity,SUM(od.orderdetailSubPrice) orderdetailSubPrice", "orderdetail od LEFT JOIN viewproducts vp ON od.orderdetailProductId=vp.id","od.orderdetailInvoice='$p_orderInvoice' GROUP BY vp.id");
        $result = array_merge($detail, array("order"=>$order['data']));
        echo json_encode($result);
        break;

    default:
        break;
}