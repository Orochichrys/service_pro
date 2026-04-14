<?php

//fonction de securisation des données
function securisation($temp){
    $temp = trim($temp);
    $temp = strip_tags($temp);
    $temp = stripslashes($temp);
    return $temp;
}

//fonction de redirection
function redirection($chemin){
    header("Location:$chemin");
    exit();
}

?>