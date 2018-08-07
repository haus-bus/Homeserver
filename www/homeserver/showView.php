<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($submitted==1)
{
   QUERY("UPDATE basicconfig set paramValue='$view' where paramKey='view' limit 1");
   triggerTreeUpdate();
}

setupTreeAndContent("showView_design.html", $message);

$erg = QUERY("select paramValue from basicconfig where paramKey='view' limit 1");
if ($row=mysqli_fetch_ROW($erg)) $ansicht=$row[0];
else
{
  QUERY("INSERT into basicconfig (paramKey,paramValue) values('view','Standard')");
  $ansicht="Standard";
}
$html = str_replace("%ANSICHT%",$ansicht,$html);

if ($ansicht=="Standard")
{
   $html = str_replace("%standardChecked%","checked",$html);
   $html = str_replace("%experteChecked%","",$html);
   $html = str_replace("%entwicklerChecked%","",$html);
}
else  if ($ansicht=="Experte")
{
   $html = str_replace("%standardChecked%","",$html);
   $html = str_replace("%experteChecked%","checked",$html);
   $html = str_replace("%entwicklerChecked%","",$html);
}
else  if ($ansicht=="Entwickler")
{
   $html = str_replace("%standardChecked%","",$html);
   $html = str_replace("%experteChecked%","",$html);
   $html = str_replace("%entwicklerChecked%","checked",$html);
}
  
show();
?>

