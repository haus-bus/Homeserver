<?php
include ($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/include/all.php");
header ( 'Content-Encoding: none; ' ); // disable apache compressed

$ip="192.168.178.79";
$serverIp="192.168.178.48";
$firmwareName = "FourRelaySonoff.ino.generic";

echo "IP vom Modul: $ip <br>";
echo "IP vom Server: $serverIp <br>";

      
$updateUrl = "http://".$ip."/update?ip=".$serverIp."&file=firmware/".$firmwareName.".bin";
$result = file_get_contents($updateUrl);

die("ende ".$result);
?>

