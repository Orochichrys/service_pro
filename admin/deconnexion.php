<?php
require_once('../includes/fonctions.php');
session_start();
session_unset();
session_destroy();
redirection("../auth/connexion.php");
?>
