<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-20
 * Time: 10:08:11 AM
 * Filename: appReportPurchase.php
 */
$tablename = "purchase";
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "*";
        $type = $p_purchaseType==="all"?"":$p_purchaseType;
        $status = $p_purchaseStatus==="all"?"":$p_purchaseStatus;
        $result = getDataP($conn, $field, $tablename, "purchaseStatus LIKE '%$status%' AND purchaseType LIKE '%$type%' AND purchaseName LIKE '%$p_search%' AND (purchaseRealDate BETWEEN '$p_purchaseDateTimeStart' AND '$p_purchaseDateTimeEnd') ORDER by purchaseDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "downloadReport":
        require_once (sysclass."simplexlsxgen.class.php");
        $type = $p_purchaseType==="all"?"":$p_purchaseType;
        $status = $p_purchaseStatus==="all"?"":$p_purchaseStatus;
        $purchase = getDataN($conn, "*", "purchase","purchaseStatus LIKE '%$status%' AND purchaseType LIKE '%$type%' AND purchaseName LIKE '%$p_search%' AND (purchaseRealDate BETWEEN '$p_purchaseDateTimeStart' AND '$p_purchaseDateTimeEnd') ORDER by purchaseDateTime DESC");
        $colfinish = count($purchase['data']);
        if($type==="restock"){
            $xlsx_data = [
                ["TGL:",$p_purchaseDateTimeStart." ~ ".$p_purchaseDateTimeEnd],
                ["NO",'TANGGAL',"FAKTUR", 'PEMBELIAN','TIPE',"PAJAK","ONGKIR","HARGA DASAR",'TOTAL HARGA','STATUS']
            ];
            $i=2;
            foreach ($purchase['data'] as $pb){
                $xlsx_data[] = [($i-1),$pb['purchaseRealDate'],$pb['purchaseNumber'],$pb['purchaseName'],$pb['purchaseType'],$pb['purchaseTaxValue'],$pb['purchaseShipping'],$pb['purchasePrice'],$pb['purchaseTotalPrice'],($pb['purchaseStatus']==="paid"?"Lunas":"Hutang")];
                $i++;
            }
        }else{
            $xlsx_data = [
                ["TGL:",$p_purchaseDateTimeStart." ~ ".$p_purchaseDateTimeEnd],
                ["NO",'TANGGAL', 'PEMBELIAN','TIPE','HARGA','STATUS']
            ];
            $i=2;
            foreach ($purchase['data'] as $pb){
                $xlsx_data[] = [($i-1),$pb['purchaseRealDate'],$pb['purchaseName'],$pb['purchaseType'],$pb['purchasePrice'],($pb['purchaseStatus']==="paid"?"Lunas":"Hutang")];
                $i++;
            }
        }

        $xlsx = SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->downloadAs("ReportPembelian_".slugConverter($p_purchaseDateTimeStart." sampai ".$p_purchaseDateTimeEnd).".xlsx");
        break;
    default:
        break;
}