<?php 
session_start();
require_once("../includes/fonctions.php");
session_destroy();
redirection("../index.php");
