<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted==1)
{
  QUERY("DELETE from basicConfig where paramKey = 'ledStatusBrightness' limit 1");
  QUERY("INSERT into basicConfig (paramKey,paramValue) values('ledStatusBrightness','$brightness')");

  QUERY("DELETE from basicConfig where paramKey = 'ledLogicalButtonBrightness' limit 1");
  QUERY("INSERT into basicConfig (paramKey,paramValue) values('ledLogicalButtonBrightness','$logicalBrightness')");
  
  $message="Einstellung wurde gespeichert.<br>Änderungen sind erst nach Regelübermittlung aktiv.";
}

setupTreeAndContent("editLedBrightness_design.html", $message);

$ledStatusBrightness="100";
$erg = QUERY("select paramValue from basicConfig where paramKey = 'ledStatusBrightness' limit 1");
if($row = mysqli_fetch_ROW($erg)) $ledStatusBrightness=$row[0];
$html = str_replace("%BRIGHTNESS%", $ledStatusBrightness, $html);

$logicalBrightness="50";
$erg = QUERY("select paramValue from basicConfig where paramKey = 'ledLogicalButtonBrightness' limit 1");
if($row = mysqli_fetch_ROW($erg)) $ledStatusBrightness=$row[0];
$html = str_replace("%LOGICAL_BRIGHTNESS%", $ledStatusBrightness, $html);

show();

?>