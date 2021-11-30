<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-30
 * Time: 11:07:34 AM
 * Filename: appStoreSales.php
 */
$tablename = "`order`";
$msg_updated = "Sukses Merubah Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "IF(orderStatus='return' OR orderStatus='cancel',CONCAT(id,':hide'),id) id, orderInvoice,orderDateTime,orderType,orderTotalPrice,orderStatus";
        $result = getDataP($conn, $field, $tablename, "orderInvoice LIKE '%$p_search%' AND orderCashierId='$s_cashierId' ORDER by orderDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $result = getData($conn, "*", $tablename, $contentPOST);
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $checkOrderDetail = getDataN($conn, "*", "orderdetail","orderdetailInvoice='$p_orderInvoice'");
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $data = updateData($conn, $tablename, $contentPOST.",orderLock='on'", "id='$p_mainId'");
        if(count($checkOrderDetail['data'])>0){
            foreach ($checkOrderDetail['data'] as $stock){
                updateData($conn, "stock", "stockQuantity=stockQuantity+'".$stock['orderdetailQuantity']."'","stockProductId='".$stock['orderdetailProductId']."'");
            }
        }
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    case "showOrderDetail":
        $order = getData($conn,"*",$tablename,"orderInvoice='$p_orderInvoice'");
        $detail = getDataN($conn, "vp.productName,vp.productCode,vp.productUnitName,SUM(od.orderdetailQuantity) orderdetailQuantity,SUM(od.orderdetailSubPrice) orderdetailSubPrice", "orderdetail od LEFT JOIN viewproducts vp ON od.orderdetailProductId=vp.id","od.orderdetailInvoice='$p_orderInvoice' GROUP BY vp.id");
        $result = array_merge($detail, array("order"=>$order['data']));
        echo json_encode($result);
        break;

    case "rePrint":
        $data = getData($conn, "*",$tablename, "id='$p_id'");
        if($data['data']!==null){
            $orderDetails = getDataN($conn, "*", "orderdetail od LEFT JOIN viewproducts vp ON od.orderdetailProductId=vp.id","orderdetailInvoice='".$data['data']['orderInvoice']."'");
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
                    "storeTotalDiscount"=>is_null($data['data']['orderTotalDiscountValue'])?0:floatval($data['data']['orderTotalDiscountValue']),
                    "storeVoucherValue"=>is_null($data['data']['orderVoucherValue'])?0:floatval($data['data']['orderVoucherValue']),
                    "storeTotalPrice"=>is_null($data['data']['orderTotalPrice'])?0:floatval($data['data']['orderTotalPrice']),
                    "storePricePayment"=>is_null($data['data']['orderPricePayment'])?0:floatval($data['data']['orderPricePayment']),
                    "storeDate"=>"TGL: ".date("d/m/Y",strtotime($data['data']['orderDateTime']))." JAM: ".date("H:i:s",strtotime($data['data']['orderDateTime'])),
                    "storeDownPayment"=>is_null($data['data']['orderDownPayment'])?0:floatval($data['data']['orderDownPayment']),
                    "storeStatus"=>$data['data']['orderStatus']==="success"?"LUNAS":($data['data']['orderStatus']==="process"?"PRE-ORDER":($data['data']['orderStatus']==="cancel"?"BATAL":"RETUR")),
                    "storeTax"=>is_null($data['data']['orderTaxValue'])?0:floatval($data['data']['orderTaxValue']),
                    "storeProduct"=>$details,
                    "storeQueue"=>intval(substr($data['data']['orderInvoice'],-3)),
                    "storeTable"=>$data['data']['orderTable'],
                    "storeAdditionalInfo"=>wrapWords($data['data']['orderAdditionalInfo'], 34),
                    "storeRootName"=>join(",",array_unique($cat)),
                    "storeCustomerName"=>is_null($data['data']['orderCustomerName'])?"":"PELANGGAN: ".$data['data']['orderCustomerName']
                )
            );
        }else{
            $receipt="";
        }
        $data['invoice'] = $data['data']['orderInvoice'];
        $result = array_merge($data,$receipt);
        echo json_encode($result);
        break;

    case "eyeInfoPrint":
        $result = getData($conn, "*", "eyeinfo ey LEFT JOIN customer cs ON ey.eyeinfoCustomerId=cs.id", "ey.eyeinfoRefCode='$p_refCode'");
        $data = getData($conn, "*",$tablename, "id='$p_id'");
        $left = json_decode($result['data']['eyeinfoLeft'],true);
        $right = json_decode($result['data']['eyeinfoRight'],true);
        if($result['data']!==null){
            $receipt = array(
                "content"=>array(
                    "eyeinfoFrame"=>$result['data']['eyeinfoFrame'],
                    "eyeinfoLens"=>$result['data']['eyeinfoLens'],
                    "customerName"=> $result['data']['customerName'],
                    "customerPhone"=>$result['data']['customerPhone'],
                    "orderInvoice"=> $data['data']['orderInvoice'],
                    "refCode"=>$p_refCode,
                    "storeTotalPrice"=>is_null($data['data']['orderTotalPrice'])?0:floatval($data['data']['orderTotalPrice']),
                    "storeStatus"=>$data['data']['orderStatus']==="success"?"LUNAS":($data['data']['orderStatus']==="process"?"PRE-ORDER":($data['data']['orderStatus']==="cancel"?"BATAL":"RETUR")),
                    "eyeinfoDateTime"=>"TGL: ".date("d/m/Y",strtotime($result['data']['eyeinfoDateTime']))." JAM: ".date("H:i:s",strtotime($result['data']['eyeinfoDateTime'])),
                    "eyeinfoLeft"=>$left,
                    "eyeinfoRight"=>$right
                )
            );
        }else{
            $receipt = array(
                "content"=>null
            );
        }
        echo json_encode($receipt);
        break;
    default:
        break;
}