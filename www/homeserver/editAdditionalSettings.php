<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted==1)
{
  MYSQL_QUERY("DELETE from basicConfig where paramKey = 'utf8Encoding' limit 1") or die(MYSQL_ERROR());
  MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('utf8Encoding','$utf8')") or die(MYSQL_ERROR());
  
  $message="Einstellung wurde gespeichert.";
}

setupTreeAndContent("editAdditionalSettings_design.html", $message);

$ledStatusBrightness="100";
$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'utf8Encoding' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) $utf8Encoding=$row[0];

if ($utf8Encoding==1) $html = str_replace("%UTF_CHECKED%", "checked", $html);
else $html = str_replace("%UTF_CHECKED%", "", $html);

show();

?>