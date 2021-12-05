<?php
/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-12-05
 * Time: 8:07:07 AM
 * Filename: appDatabaseGallery.php
 */
$tablename = "gallery";
$msg_created = "Berhasil Menambah Data";
$msg_updated = "Berhasil Mengubah Data";
$msg_removed = "Berhasil Menghapus Data";

switch ($p_act) {
    case "load_" . $p_slug:
        $field = "vp.id,vp.productName,iF(ISNULL(gl.galleryImages),'<i class=\"fa fa-times-circle red\"></i>','<i class=\"fa fa-check-circle green\"></i>') galleryImages,iF(gl.galleryActive='on','<i class=\"fa fa-check-circle green\"></i>','<i class=\"fa fa-times-circle red\"></i>') galleryActive";
        $result = getDataP($conn, $field, "viewproducts vp LEFT JOIN gallery gl ON gl.galleryProductId=vp.id", "vp.productName LIKE '%$p_search%' ORDER by vp.productName", $p_page, $p_perpage, true);
        echo json_encode($result);
        break;

    case "select_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app"));
        $result = getData($conn, "vp.id id,vp.id galleryProductId,gl.id galleryId, vp.productName productName,COALESCE(gl.galleryImages,'null') galleryImages,gl.galleryActive", "viewproducts vp LEFT JOIN gallery gl ON gl.galleryProductId=vp.id", "vp.id='$p_id'");
        echo json_encode($result);
        break;

    case "update_" . $p_slug:
        $contentPOST = postExtractor($_POST, array("p_method", "p_slug","p_appslug", "p_act", "p_mainId", "p_app","p_galleryId","p_productName","p_galleryActive"));
        $filePath = $p_app."/".$p_appslug."/upload/";
        $p_galleryActive = !ISSET($p_galleryActive)?",galleryActive=''":",galleryActive='".$p_galleryActive."'";
        if(file_exists($_FILES['galleryImages']['tmp_name'][0])) {
            for ($i = 0; $i < count($_FILES['galleryImages']['name']); $i++) {
                $ext = pathinfo($_FILES['galleryImages']['name'][$i], PATHINFO_EXTENSION);
                $name = pathinfo($_FILES['galleryImages']['name'][$i], PATHINFO_FILENAME);
                $mimeType = mime_content_type($_FILES['galleryImages']['tmp_name'][$i]);
                $fileType = explode('/', $mimeType)[0];
                $filename = slugConverter($name) . "." . $ext;
                if (move_uploaded_file($_FILES['galleryImages']['tmp_name'][$i], $filePath . $filename)) {
                    $statusUpload[] = "ok";
                    $arrFile[] = array("mime" => $mimeType,
                        "type" => $fileType,
                        "name" => $filename,
                        "path" => $filePath . $filename);
                } else {
                    $statusUpload[] = "failed";
                }
            }
        }else{
            $arrFile = null;
        }

        if($arrFile===null){
            $galleryImages = ",galleryImages=NULL";
        }else{
            $galleryImages = ",galleryImages='".json_encode($arrFile)."'";
        }

        if($p_galleryId===""){
            $data = pushData($conn, $tablename, $contentPOST.$galleryImages.$p_galleryActive);
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_created);
        }else{
            $data = updateData($conn, $tablename, $contentPOST.$galleryImages.$p_galleryActive,"id='$p_galleryId'");
            $message = array("messageSuccess" => '<i class="fa fa-info-circle"></i> ' . $msg_updated);
        }
        $result = array_merge($data, $message);
        echo json_encode($result);
        break;

    default:
        break;
}