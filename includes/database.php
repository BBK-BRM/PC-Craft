<?php
function getDB(){
    $db_host = "localhost";
    $db_user = "bibek";
    $db_pass = "Baram@123";
    $db_name = "pccraft";
    
    $conn = mysqli_connect($db_host,$db_user,$db_pass,$db_name);
    
    if(mysqli_connect_error()){
        echo mysqli_connect_error();
        exit;
    }else{
        return $conn;
    }
}