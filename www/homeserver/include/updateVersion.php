<?php
$versionFile = $argv[1];
echo "Updating $versionFile ... \n";

$in = file_get_contents($versionFile);
$pos = strpos($in,"-");
$version = trim(substr($in,0,$pos));
echo "Aktuelle Version $version \n";
$pos = strpos($version,".");
$front = trim(substr($version,0,$pos));
$back = trim(substr($version,$pos+1, strlen($version)-$pos-1));
echo "Major: $front \n";
echo "Minor: $back \n";
echo "\n";
$newVersion = $front.".".($back+1)." - ".date("d.m.Y");
echo "Neu: $newVersion \n";

file_put_contents($versionFile, $newVersion);
?>