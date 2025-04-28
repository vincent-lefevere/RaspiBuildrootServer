<?php
 require_once('config.inc.php');
 
 session_start();
 if (!isset($_POST['project']))
    die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $p=(int) $_POST['project'];

 $res=$mysqli->query("SELECT * FROM graph WHERE id=$p OR id=0");
 echo '[';
 $nf=false;
 while ($row=$res->fetch_assoc()) {
  if ($nf) echo ','; else $nf=true;
  $time=$row['timestamp'];
  $cpu=is_null($row['cpu'])?-1:$row['cpu']/100;
  $lcpu=is_null($row['lcpu'])?-1:$row['lcpu']/100;
  $mem=is_null($row['mem'])?-1:$row['mem']/100;
  $lmem=is_null($row['lmem'])?-1:$row['lmem']/100;
  $swap=is_null($row['swap'])?-1:$row['swap']/100;
  if ($row['id']==0)
   echo "{\"time\":$time,\"cpu\":$cpu,\"mem\":$mem,\"swap\":$swap}";
  else
   echo "{\"time\":$time,\"lcpu\":$lcpu,\"lmem\":$lmem}";
 }
 echo ']';
?>