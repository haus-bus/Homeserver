<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

setupTreeAndContent("graphSignalColor_design.html");

$erg = MYSQL_QUERY("select color from graphSignals where id='$signalId' limit 1") or die(MYSQL_ERROR());
$obj=MYSQL_FETCH_OBJECT($erg);
$html = str_replace("%COLOR%", $obj->color, $html);

$html = str_replace("%ID%", $id, $html);
$html = str_replace("%SIGNAL_ID%", $signalId, $html);

show();
?>