<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-02
 * Time: 4:16:11 AM
 * Filename: appStoreCashier.php
 */
$tablename = "cart";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $checkInvoice = getDataN($conn,"orderInvoice","`order`","ORDER BY orderInvoice DESC, orderDateTime DESC LIMIT 1",false);
        $field = "vp.id,vp.productCode,vp.productName,SUM(cr.cartQuantity) cartQty,CONCAT(SUM(cr.cartQuantity),' ',vp.productUnitName) cartQuantity,IF(SUM(cr.cartQuantity)>=vp.discountMinimalOrder,vp.discountByPercent,0) discountByPercent,vp.discountMinimalOrder,vp.priceSellingPrice,(SUM(cr.cartQuantity)*vp.priceSellingPrice) subTotalRaw,((SUM(cr.cartQuantity)*vp.priceSellingPrice)-(IF(SUM(cr.cartQuantity)>=vp.discountMinimalOrder,vp.discountByPercent,0)/100*(SUM(cr.cartQuantity)*vp.priceSellingPrice))) subSellingPrice, (IF(SUM(cr.cartQuantity)>=vp.discountMinimalOrder,vp.discountByPercent,0)/100*(SUM(cr.cartQuantity)*vp.priceSellingPrice)) subDiscount";
        $result = getDataP($conn, $field, "cart cr LEFT JOIN viewproducts vp ON cr.cartProductId=vp.id", "cr.cartCashierId='$s_cashierId' GROUP BY vp.id", $p_page, $p_perpage,true);
        $result['invoice'] = getInvoice($checkInvoice['data'][0]['orderInvoice']);
        $alldata = getDataN($conn,$field,"cart cr LEFT JOIN viewproducts vp ON cr.cartProductId=vp.id","cr.cartCashierId='$s_cashierId' GROUP BY vp.id");
        $result['alldata'] = $alldata['data'];
        echo json_encode($result);
        break;

    case "create_" . $p_slug:
        $check = getData($conn, "stockQuantity", "stock","stockProductId='$p_cartProductId' AND stockQuantity>='$p_cartQuantity'");
        if(count($check['data'])>0){
            $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_sellingType","p_customerName"));
            $data = pushData($conn, $tablename, $contentPOST);
            updateData($conn, "stock", "stockQuantity=stockQuantity-'$p_cartQuantity'","stockProductId='$p_cartProductId'");
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> Berhasil menambah produk');
            $result = array_merge($data, $message);
        }else{
            $data = array("status"=>"error");
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> Quantitas yang dimasukkan melebihi stok yang tersedia',"desc"=>$check);
            $result = array_merge($data, $message);
        }
        echo json_encode($result);
        break;

    case "remove_" . $p_slug:
        $check = getDataN($conn, "cartProductId, SUM(cartQuantity) cartQuantity", "cart","cartCashierId='$s_cashierId' AND cartProductId='$p_id' GROUP BY cartProductId LIMIT 1");
        if($check['data'][0]['cartQuantity']>1){
            $data = updateData($conn, $tablename, "cartQuantity=cartQuantity-1", "cartCashierId='$s_cashierId' AND cartProductId='$p_id' AND cartQuantity>0 LIMIT 1");
        }else{
            $data = removeData($conn, $tablename, "cartProductId='$p_id' AND cartCashierId='$s_cashierId'");
        }
        if ($data['status'] === 'success') {
            updateData($conn, "stock", "stockQuantity=stockQuantity+1","stockProductId='$p_id'");
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_removed);
            $result = array_merge($data, $message);
        } else {
            $result = $data;
        }
        echo json_encode($result);
        break;

    case "loadProduct":
        $result = getDataN($conn, "id,productCodeName, CONCAT('') cartRefCode, CONCAT('') customerName", "viewproducts", "(productName LIKE '%$p_search%' OR productCode LIKE '%$p_search%' OR productCodeName LIKE '%$p_search%') AND stockQuantity>0 LIMIT 10");
        echo json_encode($result);
        break;

    case "clearCart":
        $check = getDataN($conn, "cr.id,cr.cartQuantity,cr.cartProductId", "cart cr LEFT JOIN cashier cs ON cr.cartCashierId=cs.id","cs.cashierUsersId='$s_usersId'");
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug","p_act", "p_mainId", "p_app"));
        if(count($check['data'])>0){
            foreach ($check['data'] as $cart){
                updateData($conn, "stock", "stockQuantity=stockQuantity+'".$cart['cartQuantity']."'","stockProductId='".$cart['cartProductId']."'");
                $data =  removeData($conn, $tablename, "id='".$cart['id']."'");
            }
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_removed);
            $result = array_merge($data, $message);
        }else{
            $data = array("status"=>"error");
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> Tidak terdapat item produk dalam keranjang');
            $result = array_merge($data, $message);
        }
        echo json_encode($result);
        break;

    case "create_pettycash":
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_pettycashStartCashX","p_usersFullName","p_cashierUsersId"));
        $check = getData($conn, "*", "pettycash","pettycashCashierId='$p_pettycashCashierId' ORDER BY pettycashDateTime");
        if($check['data']['pettycashType']==="open"){
            $data = $check;
        }else{
            $push = pushData($conn, "pettycash", $contentPOST,true,"*","lastID");
            $data = $push['data'];
        }
        $getReceiptConfig = getDataN($conn,"configSlug,configContent","config","configActive='on' AND configType='receipt'");
        $redata = array(
            array("configSlug"=>"pettycashType", "configContent"=>$p_pettycashType),
            array("configSlug"=>"pettycashCashierId", "configContent"=>$p_pettycashCashierId),
            array("configSlug"=>"pettycashStartCash", "configContent"=>$data['data']['pettycashStartCash']),
            array("configSlug"=>"cashierName", "configContent"=>$p_usersFullName),
            array("configSlug"=>"cashierUsersId", "configContent"=>$p_cashierUsersId),
        );
        //New Method
        $message = array("config"=>array_merge($redata,$getReceiptConfig['data']),"messageSuccess" => '<i class="fa fa-info-circle"></i> ' . "Berhasil membuka kasir","type"=>"pushData");
        $result = array_merge($data, $message);
        $_SESSION['cashierId'] = $check['data']['pettycashCashierId'];
        echo json_encode($result);
        break;

    case "loadUsers":
        $result = getDataN($conn, "ch.id,us.usersFullName,ch.cashierShift,ch.cashierUsersId", "cashier ch LEFT JOIN `".iBase."`.users us ON ch.cashierUsersId=us.id", "us.usersFullName LIKE '%$p_search%' AND us.usersActive='on' AND ch.cashierUsersId='$s_usersId' AND ch.cashierActive='on' LIMIT 10");
        echo json_encode($result);
        break;

    case "checkVoucherCode":
        $code = getData($conn,"*","viewvoucher","voucherCode='$p_code'");
        echo json_encode($code);
        break;

    case "checkCashierId":
        $check = getData($conn,"pc.pettycashType,ch.id cashierId,pc.pettycashStartCash,us.usersFullName cashierName,pc.pettycashDateTime","cashier ch LEFT JOIN pettycash pc ON pc.pettycashCashierId = ch.id LEFT JOIN `".iBase."`.users us ON ch.cashierUsersId =us.id","ch.cashierUsersId='$p_cashierUsersId' ORDER BY pc.pettycashDateTime");
        $getReceiptConfig = getDataN($conn,"configSlug,configContent","config","configActive='on' AND configType='receipt'");
        $redata = array(
            array("configSlug"=>"pettycashType", "configContent"=>$check['data']['pettycashType']),
            array("configSlug"=>"pettycashCashierId", "configContent"=>$check['data']['cashierId']),
            array("configSlug"=>"pettycashStartCash", "configContent"=>$check['data']['pettycashStartCash']),
            array("configSlug"=>"cashierName", "configContent"=>$check['data']['cashierName']),
            array("configSlug"=>"cashierUsersId", "configContent"=>$p_cashierUsersId),
        );
        $result = array(
            "pettycashAccess"=>!($check['data'] === null),
            "pettycashType"=>$check['data']['pettycashType'],
            "pettycashDateTime"=>$check['data']['pettycashDateTime'],
            "config"=>array_merge($redata,$getReceiptConfig['data'])
        );
        $_SESSION['cashierId'] = $check['data']['cashierId'];
        $_SESSION['cashierName'] = $check['data']['cashierName'];
        $_SESSION['cashierUsersId'] = $p_cashierUsersId;
        echo json_encode($result);
        break;

    case "create_appStoreCashierOrder":
        $checkMerge = getData($conn,"orderInvoice","`order`","orderInvoice='$p_orderInvoice'");
        if(is_null($checkMerge['data'])){
            $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_orderPricePaymentX","p_orderDownPaymentX","p_directPrint","p_orderInvoice"),true);
            $checkInvoice = getDataN($conn,"orderInvoice","`order`","ORDER BY orderInvoice DESC, orderDateTime DESC LIMIT 1",false);
            if($checkInvoice['data'][0]['orderInvoice']===$p_orderInvoice){
                $invoice = getInvoice($checkInvoice['data'][0]['orderInvoice']);
            }else{
                $invoice = $p_orderInvoice;
            }

            $orderDetails = getDataN($conn,"*","viewcart","cartCashierId='$s_cashierId'");
            foreach ($orderDetails['data'] as $detail){
                $fieldForPush = "orderdetailCashierId='".$detail['cartCashierId']."',orderdetailInvoice='".$invoice."',orderdetailProductId='".$detail['cartProductId']."',orderdetailQuantity='".$detail['cartQuantity']."',orderdetailSubDiscountValue='".$detail['subDiscount']."',orderdetailSubPrice='".$detail['subTotalRaw']."',orderdetailSubTotalPrice='".$detail['subSellingPrice']."',orderdetailPrice='".$detail['priceSellingPrice']."',orderdetailCapitalPrice='".$detail['priceCapitalPrice']."'";
                //Add Order Detail
                pushData($conn,"orderdetail",$fieldForPush);
//                $details[] = array(
//                    "code"=>$detail['productCode'],
//                    "name"=>wrapWords($detail['productName'], 26),
//                    "qty"=>intval($detail['cartQuantity']),
//                    "catroot"=>$detail['categoryRootName'],
//                    "cat"=>$detail['categoryName'],
//                    "price"=>floatval($detail['priceSellingPrice']),
//                    "total"=>floatval($detail['subTotalRaw']),
//                );
//                $cat[] = $detail['categoryRootName'];
                $modal += intval($detail['cartQuantity'])*floatval($detail['priceCapitalPrice']);
            }
            //Update Voucher Used
            updateData($conn, "voucher","voucherUsed=voucherUsed+1","voucherCode='".$p_orderVoucherCode."' AND voucherActive='on'");

            //Remove Cart
            removeData($conn,"cart","cartCashierId='".$orderDetails['data'][0]['cartCashierId']."'");
            $data = pushData($conn, "`order`", $contentPOST.",orderInvoice='$invoice'".",orderTotalCapitalPrice='$modal'",true,"*","orderInvoice='$invoice'");

//            $receipt = array(
//                "content"=>array(
//                    "storeTotalDiscount"=>is_null($data['data']['data'][0]['orderTotalDiscountValue'])?0:floatval($data['data']['data'][0]['orderTotalDiscountValue']),
//                    "storeVoucherValue"=>is_null($data['data']['data'][0]['orderVoucherValue'])?0:floatval($data['data']['data'][0]['orderVoucherValue']),
//                    "storeTotalPrice"=>is_null($data['data']['data'][0]['orderTotalPrice'])?0:floatval($data['data']['data'][0]['orderTotalPrice']),
//                    "storePricePayment"=>is_null($data['data']['data'][0]['orderPricePayment'])?0:floatval($data['data']['data'][0]['orderPricePayment']),
//                    "storeDate"=>"TGL: ".date("d/m/Y",strtotime($data['data']['data'][0]['orderDateTime']))." JAM: ".date("H:i:s",strtotime($data['data']['data'][0]['orderDateTime'])),
//                    "storeDownPayment"=>is_null($data['data']['data'][0]['orderDownPayment'])?0:floatval($data['data']['data'][0]['orderDownPayment']),
//                    "storeStatus"=>$data['data']['data'][0]['orderStatus']==="success"?($data['data']['data'][0]['orderType']==="card"?"LUNAS (NON TUNAI)":"LUNAS"):"PRE-ORDER",
//                    "storeTax"=>is_null($data['data']['data'][0]['orderTaxValue'])?0:floatval($data['data']['data'][0]['orderTaxValue']),
//                    "storeProduct"=>$details,
//                    "storeQueue"=>intval(substr($invoice,-3)),
//                    "storeTable"=>$data['data']['data'][0]['orderTable'],
//                    "storeAdditionalInfo"=>wrapWords($data['data']['data'][0]['orderAdditionalInfo'], 34),
//                    "storeRootName"=>join(",",array_unique($cat)),
//                    "storeCustomerName"=>is_null($data['data']['data'][0]['orderCustomerName'])?"":"PELANGGAN: ".$data['data']['data'][0]['orderCustomerName']
//                )
//            );

            $receipt = array(
                "content"=>array(
                    "storeStatus"=>$data['data']['data'][0]['orderStatus']==="success"?($data['data']['data'][0]['orderType']==="card"?"LUNAS (NON TUNAI)":"LUNAS"):"PRE-ORDER",
                    "apiUrl"=>siteUrl()."/GetInvoice/".$invoice,
                    "post"=>""
                )
            );

        }else{
            $orderDetails = getDataN($conn,"*","viewcart","cartCashierId='$s_cashierId'");
            foreach ($orderDetails['data'] as $detail){
                $fieldForPush = "orderdetailCashierId='".$detail['cartCashierId']."',orderdetailInvoice='".$p_orderInvoice."',orderdetailProductId='".$detail['cartProductId']."',orderdetailQuantity='".$detail['cartQuantity']."',orderdetailSubDiscountValue='".$detail['subDiscount']."',orderdetailSubPrice='".$detail['subTotalRaw']."',orderdetailSubTotalPrice='".$detail['subSellingPrice']."',orderdetailPrice='".$detail['priceSellingPrice']."',orderdetailCapitalPrice='".$detail['priceCapitalPrice']."'";
                //Add Order Detail
                pushData($conn,"orderdetail",$fieldForPush);
                $details[] = $detail['cartProductId'].":".intval($detail['cartQuantity']);
                $modal += intval($detail['cartQuantity'])*floatval($detail['priceCapitalPrice']);
            }

            removeData($conn,"cart","cartCashierId='$s_cashierId'");
            $data = updateData($conn, "`order`", "orderTaxValue=orderTaxValue+'$p_orderTaxValue',orderTotalPrice=orderTotalPrice+'$p_orderTotalPrice',orderPrice=orderPrice+'$p_orderPrice',orderTotalCapitalPrice=orderTotalCapitalPrice+'$modal'","orderInvoice='$p_orderInvoice'",true,"*","orderInvoice='$p_orderInvoice'");

//            $receipt = array(
//                "content"=>array(
//                    "storeTotalDiscount"=>is_null($data['data']['data'][0]['orderTotalDiscountValue'])?0:floatval($data['data']['data'][0]['orderTotalDiscountValue']),
//                    "storeVoucherValue"=>is_null($data['data']['data'][0]['orderVoucherValue'])?0:floatval($data['data']['data'][0]['orderVoucherValue']),
//                    "storeTotalPrice"=>is_null($data['data']['data'][0]['orderTotalPrice'])?0:floatval($data['data']['data'][0]['orderTotalPrice']),
//                    "storePricePayment"=>is_null($data['data']['data'][0]['orderPricePayment'])?0:floatval($data['data']['data'][0]['orderPricePayment']),
//                    "storeDate"=>"TGL: ".date("d/m/Y",strtotime($data['data']['data'][0]['orderDateTime']))." JAM: ".date("H:i:s",strtotime($data['data']['data'][0]['orderDateTime'])),
//                    "storeDownPayment"=>is_null($data['data']['data'][0]['orderDownPayment'])?0:floatval($data['data']['data'][0]['orderDownPayment']),
//                    "storeStatus"=>$data['data']['data'][0]['orderStatus']==="success"?($data['data']['data'][0]['orderType']==="card"?"LUNAS (NON TUNAI)":"LUNAS"):"PRE-ORDER",
//                    "storeTax"=>is_null($data['data']['data'][0]['orderTaxValue'])?0:floatval($data['data']['data'][0]['orderTaxValue']),
//                    "storeProduct"=>$details,
//                    "storeQueue"=>intval(substr($p_orderInvoice,-3)),
//                    "storeTable"=>$data['data']['data'][0]['orderTable'],
//                    "storeAdditionalInfo"=>wrapWords($data['data']['data'][0]['orderAdditionalInfo'], 34),
//                    "storeRootName"=>join(",",array_unique($cat)),
//                    "storeCustomerName"=>is_null($data['data']['data'][0]['orderCustomerName'])?"":"PELANGGAN: ".$data['data']['data'][0]['orderCustomerName']
//                )
//            );

            $receipt = array(
                "content"=>array(
                    "storeStatus"=>$data['data']['data'][0]['orderStatus']==="success"?($data['data']['data'][0]['orderType']==="card"?"LUNAS (NON TUNAI)":"LUNAS"):"PRE-ORDER",
                    "apiUrl"=>siteUrl()."/GetInvoice/".$p_orderInvoice,
                    "post"=>join(",",$details)
                )
            );
        }

        $message = array("detail"=>$orderDetails,"messageSuccess" => '<i class="fa fa-info-circle"></i> Order Sukses');
        $result = array_merge($data, $message,$receipt);
        echo json_encode($result);
        break;

    case "precloseCashier":
            $allItemOrder = getDataN($conn,"vp.productName,SUM(odt.orderdetailQuantity) cartQuantity","`orderdetail` odt LEFT JOIN `order` od ON odt.orderdetailInvoice = od.orderInvoice LEFT JOIN viewproducts vp ON odt.orderdetailProductId = vp.id","(od.orderDateTime BETWEEN '$p_pettycashDateTime' AND NOW()) AND od.orderCashierId = '$p_cashierId' AND od.orderStatus='success' GROUP BY odt.orderdetailProductId");
            $totalOrderPrices = getDataN($conn,"SUM(od.orderTotalPrice) totalPrice,od.orderType","`order` od","(od.orderDateTime BETWEEN '$p_pettycashDateTime' AND NOW()) AND od.orderCashierId = '$p_cashierId' AND od.orderStatus='success' GROUP BY od.orderCashierId,od.orderType");
            $checkPreorder = getDataN($conn, "*", "`order`","orderType='preorder' AND orderStatus='process'");
            foreach ($allItemOrder['data'] as $detail){
                $details[] = array(
                    "name"=>wrapWords($detail['productName'], 26),
                    "qty"=>intval($detail['cartQuantity']),
                );
            }

            foreach ($totalOrderPrices['data'] as $totalOrderPrice){
                $total += doubleval($totalOrderPrice['totalPrice']);
            }

            $result = array(
                "content"=>array(
                    "allItem"=>$details,
                    "groupPrices"=>$totalOrderPrices['data'],
                    "totalPrices"=>$total,
                    "startCash"=>$p_pettycashStartCash
                ),
                "preorder"=>count($checkPreorder['data'])
            );

            echo json_encode($result);
    break;

    case "closeCashier":
        $getcash = getData($conn, "SUM(orderTotalDiscountValue) totalDiscount,SUM(orderPrice) totalRawPrice, SUM(orderVoucherValue) totalVoucher, SUM(IF(orderStatus='success',orderTotalPrice,0))+SUM(IF(orderStatus='process',orderDownPayment,0)) totalPrice", "`order`","(orderDateTime BETWEEN '$p_datetime' AND NOW()) AND orderCashierId='$p_cashierId' AND (orderStatus='success' OR orderStatus='process') GROUP BY orderCashierId");
        $field = "pettycashCashierId='$p_cashierId',pettycashType='close',pettycashCash='".($getcash['data']['totalPrice']===NULL?0:$getcash['data']['totalPrice'])."',pettycashLastCash='$p_pettycashLastCash',pettycashStartCash='$p_pettycashStartCash'";
        $data = pushData($conn, "pettycash", $field);
        $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> Berhasil menutup kasir');
        //Remove Cart
        $check = getDataN($conn, "*", $tablename,"cartCashierId='$s_cashierId'");
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug","p_act", "p_mainId", "p_app"));
        if(count($check['data'])>0){
            foreach ($check['data'] as $cart){
                updateData($conn, "stock", "stockQuantity=stockQuantity+'".$cart['cartQuantity']."'","stockProductId='".$cart['cartProductId']."'");
                removeData($conn, $tablename, "id='".$cart['id']."'");
            }
        }

        $receipt = array(
            "content"=>array(
                "apiUrl"=>siteUrl()."/CloseCashier/".$s_cashierId."/".strtotime($p_datetime)."/".$p_pettycashStartCash
            )
        );

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

    case "load_shortcutMenu":
        $data = getDataN($conn,"vp.id,vp.productCode,vp.productName,st.shortcutOrder,vp.categoryName","shortcut st LEFT JOIN viewproducts vp ON st.shortcutProductId=vp.id","ORDER By st.shortcutOrder",false);
        echo json_encode($data);
        break;

    case "loadAllMenu":
        $result = array("MODUL DALAM PENGERJAAN, STAY COOL :)");
        echo json_encode($result);
        break;
    default:
        break;
}