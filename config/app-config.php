<?php
/**
 * Created By iCMS
 * Author: Admin iCMS
 * Date: 2021-11-08
 * Time: 3:15:25 PM
 * Filename: config.php
 */
//Main Connection
$main_conn = openConn(iHost, iUser, iPass, iBase);
//Select New Database
mysqli_select_db($conn, "pos_cafe"); //in same host
//Reinit Host and Database
#$conn =  openConn("host", "user", "password", "databasename");