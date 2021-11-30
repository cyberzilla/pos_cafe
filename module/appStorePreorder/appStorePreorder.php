<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-25
 * Time: 4:24:16 AM
 * Filename: appStorePreorder.php
 */
$tablename = "`order`";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "id,orderDateTime,orderTotalPrice,orderInvoice,orderTable,orderCustomerName";
//        $result = getDataP($conn, $field, $tablename, "orderInvoice LIKE '%$p_search%' AND orderStatus='process' AND orderCashierId='$s_cashierId' ORDER by orderDateTime DESC", $p_page, $p_perpage, true);
        $result = getDataP($conn, $field, $tablename, "orderInvoice LIKE '%$p_search%' AND orderStatus='process' ORDER by orderDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $result = getData($conn, "id,orderTotalPrice,orderInvoice", $tablename, $contentPOST." AND orderStatus='process'");
        echo json_encode($result);
        break;

    case "showOrderDetail":
        $order = getData($conn,"*",$tablename,"orderInvoice='$p_orderInvoice'");
        $detail = getDataN($conn, "vp.productName,vp.productCode,vp.productUnitName,SUM(od.orderdetailQuantity) orderdetailQuantity,SUM(od.orderdetailSubPrice) orderdetailSubPrice", "orderdetail od LEFT JOIN viewproducts vp ON od.orderdetailProductId=vp.id","od.orderdetailInvoice='$p_orderInvoice' GROUP BY vp.id");
        $result = array_merge($detail, array("order"=>$order['data']));
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $data = updateData($conn, $tablename, "orderStatus='success',orderPricePayment='$p_orderPricePayment',orderDateTime='$f_datetimenow',orderCashierId='$s_cashierId',orderType='$p_orderType'", "id='$p_mainId'",true,"*","id='$p_mainId'");
        if($data['data']['status']==="success"){
            $orderDetails = getDataN($conn, "*", "orderdetail od LEFT JOIN viewproducts vp ON od.orderdetailProductId=vp.id","orderdetailInvoice='".$data['data']['data'][0]['orderInvoice']."'");
            foreach ($orderDetails['data'] as $detail){
                $details[] = array(
                    "code"=>$detail['productCode'],
                    "name"=>wrapWords($detail['productName'], 26),
                    "qty"=>intval($detail['orderdetailQuantity']),
                    "catroot"=>$detail['categoryRootName'],
                    "cat"=>$detail['categoryName'],
                    "price"=>floatval($detail['orderdetailPrice']),
                    "total"=>floatval($detail['orderdetailSubPrice']),
                );
                $cat[] = $detail['categoryRootName'];
            }
            $receipt = array(
                "content"=>array(
                    "storeTotalDiscount"=>is_null($data['data']['data'][0]['orderTotalDiscountValue'])?0:floatval($data['data']['data'][0]['orderTotalDiscountValue']),
                    "storeVoucherValue"=>is_null($data['data']['data'][0]['orderVoucherValue'])?0:floatval($data['data']['data'][0]['orderVoucherValue']),
                    "storeTotalPrice"=>is_null($data['data']['data'][0]['orderTotalPrice'])?0:floatval($data['data']['data'][0]['orderTotalPrice']),
                    "storePricePayment"=>is_null($data['data']['data'][0]['orderPricePayment'])?0:floatval($data['data']['data'][0]['orderPricePayment']),
                    "storeDate"=>"TGL: ".date("d/m/Y",strtotime($data['data']['data'][0]['orderDateTime']))." JAM: ".date("H:i:s",strtotime($data['data']['data'][0]['orderDateTime'])),
                    "storeDownPayment"=>is_null($data['data']['data'][0]['orderDownPayment'])?0:floatval($data['data']['data'][0]['orderDownPayment']),
                    "storeStatus"=>$data['data']['data'][0]['orderStatus']==="success"?"LUNAS":"PRE-ORDER",
                    "storeTax"=>is_null($data['data']['data'][0]['orderTaxValue'])?0:floatval($data['data']['data'][0]['orderTaxValue']),
                    "storeProduct"=>$details,
                    "storeQueue"=>intval(substr($data['data']['data'][0]['orderInvoice'],-3)),
                    "storeTable"=>$data['data']['data'][0]['orderTable'],
                    "storeAdditionalInfo"=>wrapWords($data['data']['data'][0]['orderAdditionalInfo'], 34),
                    "storeRootName"=>join(",",array_unique($cat)),
                    "storeCustomerName"=>is_null($data['data']['data'][0]['orderCustomerName'])?"":"PELANGGAN: ".$data['data']['data'][0]['orderCustomerName']
                )
            );
        }else{
            $receipt="";
        }
        $message = array("print"=>$p_receiptPrint,"messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message,$receipt);
        echo json_encode($result);
        break;

    default:
        break;
}