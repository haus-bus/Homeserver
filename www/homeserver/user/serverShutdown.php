<?php
if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../../";
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

// Ausschalten nach 90 Sekunden
$data["duration"]=90;
callObjectMethodByName("1681724162", "on",$data);
?>