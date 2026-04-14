<?php

function securisation($temp){
    $temp = trim($temp);
    $temp = strip_tags($temp);
    $temp = stripslashes($temp);
    return $temp;
}

function redirection($chemin){
    header("Location:$chemin");
    exit();
}

?>