<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted==1)
{
  MYSQL_QUERY("DELETE from basicConfig where paramKey = 'ledStatusBrightness' limit 1") or die(MYSQL_ERROR());
  MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('ledStatusBrightness','$brightness')") or die(MYSQL_ERROR());

  MYSQL_QUERY("DELETE from basicConfig where paramKey = 'ledLogicalButtonBrightness' limit 1") or die(MYSQL_ERROR());
  MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('ledLogicalButtonBrightness','$logicalBrightness')") or die(MYSQL_ERROR());
  
  $message="Einstellung wurde gespeichert.<br>Änderungen sind erst nach Regelübermittlung aktiv.";
}

setupTreeAndContent("editLedBrightness_design.html", $message);

$ledStatusBrightness="100";
$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'ledStatusBrightness' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) $ledStatusBrightness=$row[0];
$html = str_replace("%BRIGHTNESS%", $ledStatusBrightness, $html);

$logicalBrightness="50";
$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'ledLogicalButtonBrightness' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) $ledStatusBrightness=$row[0];
$html = str_replace("%LOGICAL_BRIGHTNESS%", $ledStatusBrightness, $html);

show();

?>