<?php
require_once('../includes/function.php');
session_start();
session_unset();
session_destroy();
redirection("../auth/login.php");
?>
