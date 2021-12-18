<?php
/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-12-14
 * Time: 4:04:02 PM
 * Filename: appDatabaseProductReview.php
 */
$msg_created = "Successfully Adding Data";
$msg_updated = "Successfully Changing Data";
$msg_removed = "Successfully Remove Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "vp.id,COUNT(ht.hitsRate) ratedBy,CONCAT('<div class=\"rate\" data-hits=\"',COALESCE(AVG(ht.hitsRate),0),'\"></div>') hitsRate,vp.productName";
        $result = getDataP($conn, $field, "viewproducts vp LEFT JOIN hits ht ON hitsProductId = vp.id", "vp.productName LIKE '%$p_search%' GROUP BY vp.id ORDER BY hitsRate DESC", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    default:
        break;
}