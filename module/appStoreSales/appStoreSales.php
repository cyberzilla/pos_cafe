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
            $receipt = array(
                "content"=>array(
                    "storeStatus"=>$data['data']['orderStatus']==="success"?"LUNAS":($data['data']['orderStatus']==="process"?"PRE-ORDER":($data['data']['orderStatus']==="cancel"?"BATAL":"RETUR")),
                    "apiUrl"=>siteUrl()."/GetInvoice/".$data['data']['orderInvoice'],
                    "post"=>""
                )
            );
            //Set Antrian
            pushData($conn, "queue", "queueOrderInvoice='".$data['data']['orderInvoice']."'");
        }else{
            $receipt="";
        }
        $data['invoice'] = $data['data']['orderInvoice'];
        $result = array_merge($data,$receipt);
        echo json_encode($result);
        break;

    default:
        break;
}