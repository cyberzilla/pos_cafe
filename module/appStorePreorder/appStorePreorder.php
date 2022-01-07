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
        $field = "IF(orderCashierId IS NULL,CONCAT(id,':hide'),id) id,orderDateTime,orderTotalPrice,orderInvoice,IF(ISNULL(orderCashierId),'OrderWeb',orderTable) orderTable,IF(ISNULL(orderCashierId),CONCAT('<div class=\"readed text-info animate__animated animate__headShake animate__infinite\">',orderCustomerName,'</div>'),orderCustomerName) orderCustomerName";
        $result = getDataP($conn, $field, $tablename, "orderInvoice LIKE '%$p_search%' AND orderStatus='process' ORDER by orderDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $result = getData($conn, "id,orderTotalPrice,orderInvoice,IF(ISNULL(orderCashierId),'orderweb','preorder') orderType", $tablename, $contentPOST." AND orderStatus='process'");
        echo json_encode($result);
        break;

    case "showOrderDetail":
        $order = getData($conn,"*",$tablename,"orderInvoice='$p_orderInvoice'");
        $detail = getDataN($conn, "vp.productName,vp.productCode,vp.productUnitName,SUM(od.orderdetailQuantity) orderdetailQuantity,SUM(od.orderdetailSubPrice) orderdetailSubPrice", "orderdetail od LEFT JOIN viewproducts vp ON od.orderdetailProductId=vp.id","od.orderdetailInvoice='$p_orderInvoice' GROUP BY vp.id");
        $result = array_merge($detail, array("order"=>$order['data']));
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        if($p_requestType==="orderweb"){
            $data = updateData($conn, $tablename, "orderStatus='success',orderPricePayment='$p_orderPricePayment',orderTable='$p_orderTable',orderCashierId='$s_cashierId',orderType='$p_orderType',orderRequestType='$p_orderRequestType'", "id='$p_mainId'",true,"*","id='$p_mainId'");
            //Set Antrian
            pushData($conn, "queue", "queueOrderInvoice='".$data['data']['data'][0]['orderInvoice']."'");
        }else{
            $data = updateData($conn, $tablename, "orderStatus='success',orderPricePayment='$p_orderPricePayment',orderDateTime='$f_datetimenow',orderCashierId='$s_cashierId',orderType='$p_orderType'", "id='$p_mainId'",true,"*","id='$p_mainId'");
        }

        if($data['data']['status']==="success"){
            $receipt = array(
                "content"=>array(
                    "storeStatus"=>$data['data']['data'][0]['orderStatus']==="success"?"LUNAS":"PRE-ORDER",
                    "apiUrl"=>siteUrl()."/GetInvoice/".$data['data']['data'][0]['orderInvoice'],
                    "post"=>""
                )
            );
        }else{
            $receipt="";
        }
        $message = array("request"=>$p_requestType,"print"=>$p_receiptPrint,"messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        $result = array_merge($data, $message,$receipt);
        echo json_encode($result);
        break;

    case "load_meja":
        $field = "tableName,tableSlug,tableType";
        $data = getDataN($conn, $field, "`table`","tableActive='on'");
        foreach ($data['data'] as $val){
            $parent[] = array("id"=>$val['tableSlug'],"text"=>$val['tableName'],"data"=>array("tableroot"=>$val['tableType']));
        }
        $result = array("data"=>$parent);
        echo json_encode($result);
        break;

    case "cancelOrder":
        $checkOrderDetail = getDataN($conn, "*", "orderdetail","orderdetailInvoice='$p_orderInvoice'");
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_orderTable"));
        $data = updateData($conn, $tablename, $contentPOST.",orderLock='on'", "id='$p_mainId'");
        if($p_orderTable!=="OrderWeb"){
            if(count($checkOrderDetail['data'])>0){
                foreach ($checkOrderDetail['data'] as $stock){
                    updateData($conn, "stock", "stockQuantity=stockQuantity+'".$stock['orderdetailQuantity']."'","stockProductId='".$stock['orderdetailProductId']."'");
                }
            }
        }
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> Berhasil melakukan pembatalan order');
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    default:
        break;
}