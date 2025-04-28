<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 require_once('install.inc.php');
 session_start();
 session_unset();

 function checklogin($login){
  global $mysqli;

  $result=$mysqli->query("SELECT prof, name, pwd FROM users WHERE email='$login'");
  $val=$result->fetch_assoc();
  if ($val && password_verify($_POST['pwd'],$val['pwd'])) {
	  $_SESSION['prof']=$val['prof'];
	  $_SESSION['name']=$val['name'];
  } else {
   if (defined('LDAPSERVER')) {
    $ds=ldap_connect('ldap://'.LDAPSERVER);
    if (!$ds) die('false');
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
    $r=@ldap_bind($ds, $_POST['login'], $_POST['pwd']);
    if (!$r) die('false');
    $filter='(|(mail='.$_POST['login'].'))';
    $justthese=array('dn','ou', 'sn', 'givenname', 'mail');
    $result=ldap_search($ds, LDAPPROF, $filter, $justthese );
	if (!($prof=ldap_count_entries($ds,$result))) {
		$result=ldap_search($ds, LDAPETUD, $filter, $justthese );
		$_SESSION['prof']=false;
	} else
		$_SESSION['prof']=true;
	$info = ldap_get_entries($ds, $result);
	$_SESSION['name']=$name=$info[0]['givenname'][0].' '.$info[0]['sn'][0];
    $pwd=crypt($_POST['pwd'], '$1$'.substr(base64_encode(random_bytes(6)),0,6).'$');
    $mysqli->query("INSERT INTO users VALUES ('$login', '$pwd', $prof, '$name') ON DUPLICATE KEY UPDATE pwd='$pwd', prof=$prof, name='$name'");
   } else die('false');
  }
  $_SESSION['login']=$login;
 }

 if (!isset($_POST['login'])||!isset($_POST['pwd'])) die('false');
 if ($_POST['login']==''||$_POST["pwd"]=='') die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,'');
 if (! $mysqli) die('false');
 $login=$mysqli->real_escape_string($_POST['login']);
 if ($login!=$_POST['login']) die('false');
 try {
  if (! $mysqli->select_db(BDDBASE)) {
   installdb();
   $mysqli->close();
   $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
  }
 } catch (mysqli_sql_exception $e) {
  installdb();
  $mysqli->close();
  $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 }
 checklogin($login);
 frontend(0);
?>