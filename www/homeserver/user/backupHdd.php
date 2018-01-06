<?php

$mode = $argv[1];
if ($mode=="on") $modus="on";
else if ($mode=="off")
{
	$modus="on";
  $data["duration"]=30;
}
else die("Unbekannter modus $mode");

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../../";
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

callObjectMethodByName("1681724162", $modus,$data);
?>