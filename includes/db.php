<?php

define("DB_HOST","0.0.0.0:3306");
define("DB_NAME","servicepro_db");
define("DB_USER","root");
define("DB_PASS","root");
define("DB_CHAR","utf8");

try{
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHAR,DB_USER,DB_PASS);
    $conn -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 
}
catch(PDOException $e){
    die("Erreur: ".$e->getMessage());
}

?>