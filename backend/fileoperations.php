
<?php
$fpath = "rakesh.txt";
$fread = fopen($fpath, "r") or die("Unable to open file!");
echo fread($fread, filesize($fpath));
fclose($fread);
?>