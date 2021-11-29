<?php
/**
 * Created By iCMS
 * Author: Abu Dzakiyyah
 * Date: 2021-01-30
 * Time: 2:16:05 PM
 * Filename: appReportCashier.php
 */
switch ($p_act) {
    case "load_" . $p_slug:
        $field = "pc.id,pettycashDateTime,usersFullName,pettycashType,pettycashStartCash,pettycashCash,pettycashLastCash,IF((pettycashStartCash+pettycashCash)=pettycashLastCash,'<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') pettycashMatch";
        $type = $p_pettycashType==="all"?"":$p_pettycashType;
        $result = getDataP($conn, $field, "pettycash pc LEFT JOIN cashier cr ON pc.pettycashCashierId = cr.id LEFT JOIN `".iBase."`.users usr ON cr.cashierUsersId=usr.id", "pc.pettycashType LIKE '%$type%' AND usr.usersFullName LIKE '%$p_search%' AND (pc.pettycashDateTime BETWEEN '$p_pettycashDateTimeStart 00:00:00' AND '$p_pettycashDateTimeEnd 23:59:59') ORDER by pc.pettycashDateTime DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "downloadReport":
        require_once (sysclass."simplexlsxgen.class.php");
        $type = $p_pettycashType==="all"?"":$p_pettycashType;
        $pettyCash = getDataN($conn, "*", "pettycash pc LEFT JOIN cashier cr ON pc.pettycashCashierId = cr.id LEFT JOIN `".iBase."`.users usr ON cr.cashierUsersId=usr.id","pc.pettycashType LIKE '%$type%' AND usr.usersFullName LIKE '%$p_search%' AND (pc.pettycashDateTime BETWEEN '$p_pettycashDateTimeStart 00:00:00' AND '$p_pettycashDateTimeEnd 23:59:59') ORDER by pc.pettycashDateTime DESC");
        $xlsx_data = [
            ["TGL:",$p_pettycashDateTimeStart." ~ ".$p_pettycashDateTimeEnd],
            ["NO",'TANGGAL', 'NAMA KASIR','TIPE','KAS AWAL','PEMASUKAN','KAS AKHIR']
        ];
        $i=2;
        foreach ($pettyCash['data'] as $pb){
            $xlsx_data[] = [($i-1),$pb['pettycashDateTime'],$pb['usersFullName'],($pb['pettycashType']==="open"?"Buka Kasir":"Tutup Kasir"),$pb['pettycashStartCash'],$pb['pettycashCash'],$pb['pettycashLastCash']];
            $i++;
        }

        $xlsx = SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->downloadAs("ReportKasir_".slugConverter($p_pettycashDateTimeStart." sampai ".$p_pettycashDateTimeEnd).".xlsx");
        break;
    default:
        break;
}