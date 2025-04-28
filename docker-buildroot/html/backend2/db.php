<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 session_start();
 
 if (!isset($_SESSION['login'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 frontend(0);
?>