<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted==1)
{
  QUERY("DELETE from basicConfig where paramKey = 'utf8Encoding' limit 1");
  QUERY("INSERT into basicConfig (paramKey,paramValue) values('utf8Encoding','$utf8')");
  
  $message="Einstellung wurde gespeichert.";
}

setupTreeAndContent("editAdditionalSettings_design.html", $message);

$ledStatusBrightness="100";
$erg = QUERY("select paramValue from basicConfig where paramKey = 'utf8Encoding' limit 1");
if($row = mysqli_fetch_ROW($erg)) $utf8Encoding=$row[0];

if ($utf8Encoding==1) $html = str_replace("%UTF_CHECKED%", "checked", $html);
else $html = str_replace("%UTF_CHECKED%", "", $html);

show();

?>