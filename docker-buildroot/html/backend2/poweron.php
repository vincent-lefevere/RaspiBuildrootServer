<?php
 require_once('config.inc.php');
 require_once('frontend.inc.php');
 require_once('path.inc.php');

 function mycmp($item1,$item2) {
  $val1=explode("=",$item1)[0];
  $val2=explode("=",$item2)[0];
  return $val1 <=> $val2;
 }

 function init($vm,$p,$version,$expert) {
  $email=$_SESSION['login'];
  $name=$_SESSION['name'];
  system("mkdir -p /data/vm-{$vm}/external ; git clone --branch prj{$p} git://git/projets.git /data/vm-{$vm}/external",$retval);
  if ($retval!=0) {
   exec("git clone --depth 1 git://git/projets.git /data/vm-{$vm}/external");
   exec("tar -C /data/vm-{$vm}/external -xvzpf /data/external.tar.gz");
   exec("cd /data/vm-{$vm}/external ; git config user.email {$email} ; git config user.name \"{$name}\" ; git add --all ; git commit --all -m 'create project' ; git branch -c prj{$p} ; git switch prj{$p} ; git push origin prj{$p}");
  } else exec("cd /data/vm-{$vm}/external ; mkdir -p board configs custom-rootfs packages");
  if (file_exists($fname="/data/vm-{$vm}/external/configs/.idhardwareversion")) $previous=(int) file_get_contents($fname);
  else $previous=$version;
  if ($previous!=$version) {
    $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
    $val=$mysqli->query("SELECT versions.title, defconfs.defconfig FROM defconfs INNER JOIN (prop INNER JOIN (images INNER JOIN versions ON images.version=versions.id) ON prop.idtoolchain=images.toolchain) ON defconfs.id=prop.iddefconf WHERE images.id={$version}")->fetch_assoc();
    $versionlib=$val['defconfig'].' on '.$val['title'];
    $val=$mysqli->query("SELECT versions.title, defconfs.defconfig FROM defconfs INNER JOIN (prop INNER JOIN (images INNER JOIN versions ON images.version=versions.id) ON prop.idtoolchain=images.toolchain) ON defconfs.id=prop.iddefconf WHERE images.id={$previous}")->fetch_assoc();
    $previouslib=$val['defconfig'].' on '.$val['title'];
    $mysqli->close();
    $dcname="/data/vm-{$vm}/external/configs/manip_defconfig";
    $olddc="/data/vm-{$vm}/manip_defconfig.old";
    rename($dcname,$olddc);
    $fbegin=file_get_contents($olddc);
    $fdef1=file_get_contents("/data/br-{$previous}/usage_defconfig");
    $fdef2=file_get_contents("/data/br-{$version}/usage_defconfig");
    $def1=explode("\n",$fdef1);
    $def2=explode("\n",$fdef2);
    $tmp=explode("\n",$fbegin);
    $mod=array_diff($tmp,$def1);                        // mod  <-- tmp - def_previous
    $mod=array_uintersect($mod,$def1,'mycmp');          // mod  <-- mod ^ def_previous
    $tmp=array_udiff($tmp,$def1,$def2,'mycmp');         // tmp  <-- tmp ~ ( def_previous U def_version )
    $def2=array_udiff($def2,$mod,'mycmp');              // def2 <-- def_version ~ mod
    $fend=implode("\n",$tmp)."\n\n".implode("\n",$def2)."\n\n".implode("\n",$mod)."\n\n";
    file_put_contents($dcname,$fend);
    file_put_contents($fname, $version);
    exec("cd /data/vm-{$vm}/external ; git config user.email {$email} ; git config user.name \"{$name}\" ; git add --all ; git commit --all -m 'convert from {$previouslib} to {$versionlib}' ; git push origin prj{$p}");
  } else file_put_contents($fname, $version);
  $ip=10+$vm;
  $port=2200+$vm;
  $path=PATH_ROOT_INSTALL;
  $tmp=<<<EOT
services:
  vm-{$vm}:
    image: docker-buildroot-master-{$version}:latest
    restart: always
    ports:
      - "{$port}:2222"
    hostname: vm-{$vm}
    networks:
      web_net:
        ipv4_address: 172.31.255.{$ip}
    volumes:
      - {$path}/data/vm-{$vm}/conf.xml:/etc/wsssh/conf.xml
      - {$path}/conf/proftpd/custom:/etc/proftpd/custom
      - {$path}/data/vm-{$vm}/external:/home/buildroot/external
      - {$path}/data/vm-{$vm}/mysql.conf:/etc/proftpd/conf.d/mysql.conf
      - /var/tmp/.buildroot-ccache:/home/.buildroot-ccache
networks:
  web_net:
    ipam:
      driver: default
      config:
        - subnet: "172.31.255.0/24"

EOT;
  file_put_contents("/data/vm-{$vm}/vm.yml", $tmp);
  $bashcmd='';
  if ($expert) $bashcmd=<<<EOT
  <cmd name="bash" login="buildroot">export TERM=xterm ; cd /home/buildroot/output ; export BR2_EXTERNAL=/home/buildroot/external ; bash</cmd>
EOT;
  $tmp=<<<EOT
<?xml version="1.0" encoding="utf8" standalone="yes" ?>
<!DOCTYPE conf [
  <!ELEMENT conf (mysql?, auth?, cmd*)>
  <!ATTLIST conf port CDATA #REQUIRED> <!-- n° de port du serveur websocket -->
  <!ATTLIST conf uri CDATA #REQUIRED> <!-- début de l'URL de traitement -->
  <!ATTLIST conf log CDATA #REQUIRED> <!-- fichier de mémorisation console -->
  <!ATTLIST conf cert CDATA #REQUIRED> <!-- fichier du certificat -->
  <!ATTLIST conf key CDATA #REQUIRED> <!-- fichier de la clef privée -->
  <!ATTLIST conf max CDATA #REQUIRED> <!-- nombre maximum de client -->
  <!ATTLIST conf format (bin|txt) "bin"> <!-- format d'envoi dans le web socket -->
  <!ELEMENT mysql EMPTY>
  <!ATTLIST mysql host CDATA #REQUIRED> <!-- serveur MySQL -->
  <!ATTLIST mysql user CDATA #REQUIRED> <!-- utilisateur MySQL -->
  <!ATTLIST mysql pwd CDATA #REQUIRED> <!-- mot de passe MySQL -->
  <!ATTLIST mysql db CDATA #REQUIRED> <!-- Base de données -->
  <!ELEMENT auth (file|sql|(file,sql)|(sql,file))>
  <!ATTLIST auth cookie CDATA #REQUIRED> <!-- nom du cookie utilisé pour recevoir le token -->
  <!ELEMENT file (#PCDATA)> <!-- fichier des tokens valides -->
  <!ELEMENT sql (#PCDATA)> <!-- requête SQL de récupération des tokens valides -->
  <!ELEMENT cmd (#PCDATA)> <!-- Commande shell à exécuter -->
  <!ATTLIST cmd name CDATA #REQUIRED> <!-- fin de l'URL de traitement -->
  <!ATTLIST cmd login CDATA #REQUIRED> <!-- login exécutant la commande -->
  <!ATTLIST cmd sql CDATA #IMPLIED> <!-- requête pour mettre des valeurs dans des variables field -->
]>
<conf port="9000" uri="/BR2/" log="/tmp/cmd.log" cert="" key="" max="10" format="bin">
  <mysql host="mariadb" user="buildroot" pwd="buildroot" db="buildroot"/>
  <auth cookie="token">
    <sql>SELECT 1 FROM act WHERE token='%s' AND id={$p}</sql>
  </auth>
  <cmd name="savedefconfig" login="buildroot">cd /home/buildroot/output ; BR2_EXTERNAL=/home/buildroot/external make savedefconfig BR2_DEFCONFIG=/home/buildroot/external/configs/manip_defconfig</cmd>
  <cmd name="menuconfig" login="buildroot">export TERM=xterm ; cd /home/buildroot/output ; BR2_EXTERNAL=/home/buildroot/external make menuconfig</cmd>
  <cmd name="loaddefconfig" login="buildroot">cd /home/buildroot/output ; BR2_EXTERNAL=/home/buildroot/external make manip_defconfig</cmd>
  <cmd name="linux-menuconfig" login="buildroot">export TERM=xterm ; cd /home/buildroot/output ; BR2_EXTERNAL=/home/buildroot/external make linux-menuconfig</cmd>
  <cmd name="make" login="buildroot">cd /home/buildroot/output ; rm -f images/sdcard.img ; time make</cmd>
  <cmd name="du" login="buildroot">cd /home/buildroot/output ; du -s -m target</cmd>
  <cmd name="graph-depends" login="buildroot">cd /home/buildroot/output ; make graph-depends</cmd>
  <cmd name="build" login="buildroot">cd /home/buildroot/output ; echo "enter package to build" ; read pkg ; make \${pkg}-build</cmd>
  <cmd name="dirclean" login="buildroot">cd /home/buildroot/output ; echo "enter package to clean" ; read pkg ; make \${pkg}-dirclean</cmd>
  <cmd name="clean" login="buildroot">cd /home/buildroot/output ; make clean</cmd>
  {$bashcmd}
</conf>

EOT;
  file_put_contents("/data/vm-{$vm}/conf.xml", $tmp);
  $tmp=<<<EOT
LoadModule mod_sql.c
LoadModule mod_sql_mysql.c
LoadModule mod_sftp.c

<VirtualHost 0.0.0.0>
  SFTPEngine on
  Port 2222
  SFTPLog /var/log/proftpd/sftp.log

  SFTPHostKey /etc/proftpd/custom/ssh_host_rsa_key
  SFTPAuthMethods publickey password
  SFTPAuthorizedUserKeys file:/etc/proftpd/custom/authorized_keys/%u

  RequireValidShell off
  AuthOrder mod_sql.c

  AuthGroupFile /etc/proftpd/custom/ftp.group
  SQLBackend mysql
  SQLEngine on

  SQLConnectInfo buildroot@mariadb buildroot buildroot

  SQLAuthenticate users
  SQLAuthTypes Crypt
  SQLMinUserUID 33
  SQLMinUserGID 33
  SQLUserInfo custom:/get-user-by-name/get-user-by-id/get-user-names/get-all-users
  SQLNamedQuery get-user-by-name SELECT "users.email, users.pwd, 33, 33, '/home/buildroot', '/bin/false' FROM users INNER JOIN act ON users.email=act.email WHERE act.id={$p} AND users.email = '%U'"
  SQLNamedQuery get-user-by-id SELECT "'buildroot', '*', 33, 33, '/home/buildroot', '/bin/false'"
  SQLNamedQuery get-user-names SELECT "email FROM users"
  SQLNamedQuery get-all-users SELECT "users.email, users.pwd, 33, 33, '/home/buildroot', '/bin/false' FROM users INNER JOIN act ON users.email=act.email WHERE act.id={$p}"
  SQLNamedQuery get-user-key SELECT "users.email FROM users WHERE users.email = '%U'"
  SQLUserPrimaryKey custom:/get-user-key
  
  DefaultRoot ~

  RequireValidShell off

  SFTPCompression delayed
</VirtualHost>

<Global>
    <Directory />
      <Limit WRITE>
        DenyAll
      </Limit>
    </Directory>

    <Directory /home/buildroot/external>
      AllowOverwrite on
      <Limit WRITE>
        AllowAll
      </Limit>
    </Directory>
</Global>


EOT;
  file_put_contents("/data/vm-{$vm}/mysql.conf", $tmp);
  exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml up -d vm-{$vm}");
  exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml exec -T -w /home/buildroot/output -u buildroot vm-{$vm} make manip_defconfig BR2_EXTERNAL=/home/buildroot/external");
 }

 function poweron($vm,$p,$version,$expert) {
  if (file_exists("/data/vm-{$vm}/vm.yml")) {
   exec("sudo /usr/bin/docker compose -p docker-buildroot -f /data/vm-{$vm}/vm.yml start vm-{$vm}");
  } else init($vm,$p,$version,$expert);
 }

 session_start();
 if (!isset($_POST['projet'])||!isset($_SESSION['login'])) die('false');
 $mysqli = new mysqli(BDDSERVEUR,BDDLOGIN,BDDPASSWD,BDDBASE);
 $p=(int) $_POST['projet'];
 $mysqli->query("LOCK TABLES projects WRITE, act WRITE");
 $vm=1;
 while ($vm<=30) {
  if ($mysqli->query("SELECT 1 FROM projects WHERE power={$vm}")->fetch_assoc()) $vm++;
  else break;
 }
 if ($vm<=30) {
  $result=$mysqli->query("SELECT 1 FROM act WHERE id={$p} AND token IS NOT NULL AND email='".$_SESSION['login']."'");
  $val=$result->fetch_assoc();
  if ($val) {
   $val=$mysqli->query("SELECT image, expert FROM projects WHERE id={$p}")->fetch_assoc();
   poweron($vm,$p,(int) $val['image'],$val['expert']);
   $mysqli->query("UPDATE projects SET power={$vm}, allow=expert WHERE id={$p}");
   send_mqtt_msg("/all");
   $mysqli->query("UNLOCK TABLES");
   frontend($p);
   die();
  }
 }
 $mysqli->query("UNLOCK TABLES");
 echo 'false';
?>
