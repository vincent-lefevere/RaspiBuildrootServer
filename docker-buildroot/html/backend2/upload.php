<?php
$putdata = fopen("php://input", "r");
$fp = fopen("/tmp/tc.tar.gz", "w");
while ($data = fread($putdata, 65536)) {
  fwrite($fp, $data);	
}
fclose($putdata);
fclose($fp);
?>