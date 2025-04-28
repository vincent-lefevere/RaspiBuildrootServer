<?php
  session_start();
  die(isset($_SESSION['login'])?'true':'false');
?>