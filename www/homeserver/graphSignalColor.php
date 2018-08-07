<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

setupTreeAndContent("graphSignalColor_design.html");

$erg = QUERY("select color from graphSignals where id='$signalId' limit 1");
$obj=mysqli_fetch_OBJECT($erg);
$html = str_replace("%COLOR%", $obj->color, $html);

$html = str_replace("%ID%", $id, $html);
$html = str_replace("%SIGNAL_ID%", $signalId, $html);

show();
?>