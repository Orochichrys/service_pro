<?php
require_once("../includes/function.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté ET s'il est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['est_admin']) || $_SESSION['est_admin'] != 1) {
    // Redirection vers le login public s'il n'est pas autorisé
    redirection("../auth/login.php");
}
?>