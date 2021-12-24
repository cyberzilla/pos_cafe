<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2020-12-02
 * Time: 7:22:02 PM
 * Filename: appHome.php
 */

switch ($p_act) {
    case "loadChart":
        $purchaseNow = getData($conn, "count(id) purchaseNow", "purchase pc","YEAR(pc.purchaseRealDate) = YEAR(NOW()) AND MONTH(pc.purchaseRealDate)=MONTH(NOW()) GROUP BY MONTH(NOW())",true);
        $orderNow = getData($conn, "count(id) orderNow", "`order` od","YEAR(od.orderDateTime) = YEAR(NOW()) AND MONTH(od.orderDateTime)=MONTH(NOW()) AND od.orderStatus='success' GROUP BY MONTH(NOW())",true);
        $incomeNow = getData($conn, "SUM(IF(orderStatus='success',orderTotalPrice,0)) incomeNow", "`order` od","YEAR(od.orderDateTime) = YEAR(NOW()) AND MONTH(od.orderDateTime)=MONTH(NOW()) AND od.orderStatus='success' GROUP BY MONTH(NOW())",true);
        $spendNow = getData($conn, "SUM(pc.purchasePrice) spendNow", "purchase pc","YEAR(pc.purchaseRealDate) = YEAR(NOW()) AND MONTH(pc.purchaseRealDate)=MONTH(NOW()) GROUP BY MONTH(NOW())",true);

        $spendData = getDataN($conn, "COALESCE(SUM(pc.purchasePrice),0) spendMonth", "(SELECT 1 AS bulan UNION SELECT 2 AS bulan UNION SELECT 3 AS bulan UNION SELECT 4 AS bulan UNION SELECT 5 AS bulan UNION SELECT 6 AS bulan UNION SELECT 7 AS bulan UNION SELECT 8 AS bulan UNION SELECT 9 AS bulan UNION SELECT 10 AS bulan UNION SELECT 11 AS bulan UNION SELECT 12 AS bulan) bl LEFT JOIN purchase pc ON MONTH(pc.purchaseRealDate) = bl.bulan AND YEAR(pc.purchaseRealDate)='$p_year'","GROUP BY bl.bulan",false);
        $spendMonth[] = "Pengeluaran";
        foreach ($spendData['data'] as $spend){$spendMonth[] = floatval($spend['spendMonth']);}

        $incomeData = getDataN($conn, "SUM(IF(orderStatus='success',orderTotalPrice,0)) incomeMonth", "(SELECT 1 AS bulan UNION SELECT 2 AS bulan UNION SELECT 3 AS bulan UNION SELECT 4 AS bulan UNION SELECT 5 AS bulan UNION SELECT 6 AS bulan UNION SELECT 7 AS bulan UNION SELECT 8 AS bulan UNION SELECT 9 AS bulan UNION SELECT 10 AS bulan UNION SELECT 11 AS bulan UNION SELECT 12 AS bulan) bl LEFT JOIN `order` od ON MONTH(od.orderDateTime) = bl.bulan AND YEAR(od.orderDateTime)='$p_year' AND od.orderStatus='success'","GROUP BY bl.bulan",false);
        $incomeMonth[] = "Pemasukan";
        foreach ($incomeData['data'] as $income){$incomeMonth[] = floatval($income['incomeMonth']);}

        $profitData = getDataN($conn, "SUM(IF(orderStatus='success',orderTotalPrice,0)) - COALESCE(SUM(od.orderTotalCapitalPrice),0) profitMonth", "(SELECT 1 AS bulan UNION SELECT 2 AS bulan UNION SELECT 3 AS bulan UNION SELECT 4 AS bulan UNION SELECT 5 AS bulan UNION SELECT 6 AS bulan UNION SELECT 7 AS bulan UNION SELECT 8 AS bulan UNION SELECT 9 AS bulan UNION SELECT 10 AS bulan UNION SELECT 11 AS bulan UNION SELECT 12 AS bulan) bl LEFT JOIN `order` od ON MONTH(od.orderDateTime) = bl.bulan AND YEAR(od.orderDateTime)='$p_year' AND od.orderStatus='success'","GROUP BY bl.bulan",false);
        $profitMonth[] = "Laba";
        foreach ($profitData['data'] as $profit){$profitMonth[] = floatval($profit['profitMonth']);}

        $result = array(
            "purchaseNow"=>intval($purchaseNow['data']['purchaseNow']),
            "orderNow"=>intval($orderNow['data']['orderNow']),
            "incomeNow"=>floatval($incomeNow['data']['incomeNow']),
            "spendNow"=>floatval($spendNow['data']['spendNow']),
            "chart"=>array(array('kolom', "JAN", "FEB", "MAR", "APR", "MEI", "JUN","JUL","AGU","SEP","OKT","NOV","DES"), $incomeMonth, $spendMonth, $profitMonth)
        );
        echo json_encode($result);
        break;

    case "loadPopularProduct":
        $field = "odn.productName,odn.categoryName, CONCAT(SUM(odn.orderdetailQuantity),' ',odn.productUnitName) productMostSales";
        $result = getDataP($conn, $field, "(SELECT odt.orderdetailInvoice,SUM(odt.orderdetailQuantity) orderdetailQuantity, vp.productName, vp.categoryName,vp.productUnitName,vp.id FROM orderdetail odt LEFT JOIN viewproducts vp ON odt.orderdetailProductId = vp.id GROUP BY odt.orderdetailProductId,odt.orderdetailInvoice ORDER BY odt.orderdetailInvoice) odn LEFT JOIN `order` od on odn.orderdetailInvoice = od.orderInvoice","od.orderStatus='success' AND odn.id IS NOT NULL GROUP BY odn.id ORDER BY SUM(odn.orderdetailQuantity) DESC", $p_page, $p_perpage,true);
        echo json_encode($result);
        break;

    default:
        break;
}