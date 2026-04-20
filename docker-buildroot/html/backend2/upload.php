<?php
$id=0;
$putdata = fopen("php://input", "r");
$fp = fopen("/data/tcdl/tc{$id}.tar.gz", "w");
while ($data = fread($putdata, 65536)) {
  fwrite($fp, $data);	
}
fclose($putdata);
fclose($fp);
?>
