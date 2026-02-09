
<?php
$fpath = "rakesh.txt";

$fread = fopen($fpath, "r") or die("Unable to open file!");
echo fread($fread, filesize($fpath));
fclose($fread);

$fwrite = fopen($fpath, "w") or die("Unable to open file!");
$txt = "Rakesh Kumar\n";
fwrite($fwrite, $txt);
$txt = "Rakesh Kumar 2\n";
fwrite($fwrite, $txt);
fclose($fwrite);

$fappend = fopen($fpath, "a") or die("Unable to open file!");
$txt = "Rakesh Kumar 3\n";
fwrite($fappend, $txt);
fclose($fappend);
$fread = fopen($fpath, "r") or die("Unable to open file!");
echo fread($fread, filesize($fpath));
fclose($fread);

$fileaccesstime = fileatime($fpath);
echo "File Access Time: " . date("F d Y H:i:s.", $fileaccesstime);
$filemodificationtime = filemtime($fpath);
echo "File Modification Time: " . date("F d Y H:i:s.", $filemodificationtime);
$filecreationtime = filectime($fpath);
echo "File Creation Time: " . date("F d Y H:i:s.", $filecreationtime);

?>