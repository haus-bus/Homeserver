<?php
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($id=="")
{
	$erg = QUERY("select id from webapppages where pos='1' limit 1");
	if ($row=MYSQL_FETCH_ROW($erg)) $id=$row[0];
	else die("<html><body><br><br>Fehler!<br>Es wurde keine Startseite konfiguriert.<br>Bitte in der Administration unter Webapplikation Seiten anlegen und eine als Startseite konfigurieren");
}

if (file_exists($id.".webapp")) $html = file_get_contents($id.".webapp");
else $html="<html><body>kein svg<center><form><table width=90%><tr><td>%BUTTONS%</td></tr></table></form></html>";


$where="1=2";
$erg = QUERY("select id,name from webapppageszeilen where pageId='$id' order by pos");
while($row=MYSQL_FETCH_ROW($erg))
{
	$zeilen[$row[0]]=$row[1];
	$where.=" or zeilenId='$row[0]'";
}

$erg = QUERY("select id,zeilenId,name,featureInstanceId from webapppagesbuttons where $where order by zeilenId,pos");
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	$buttonName[$obj->zeilenId][$obj->id]=$obj->name;
	$buttonLink[$obj->zeilenId][$obj->id]=$obj->featureInstanceId;
}

$buttons="";
foreach((array)$zeilen as $zeilenId=>$zeilenName)
{
	$buttons.="<table><tr><td>$zeilenName</td></tr><tr>";

  foreach((array)$buttonName[$zeilenId] as $buttonId=>$name)
  {
    	 $link = $buttonLink[$zeilenId][$buttonId];
    	 if (substr($link,0,1)=="P")
    	 {
    	 	  $page = substr($link,1);
    	 	  $action="location='index.php?id=$page'";
    	 }
    	 else if (strpos($link,"http://")!==FALSE)
    	 {
    	 	  $action="window.open('$link')";
    	 }
    	 else
    	 {
    	 	  $action="send('ajaxServer.php?command=clickButton&id=$link', 'dummyCallback');";
    	 }
    	 $buttons.="<td><input type=button value='$name' onclick=\"$action\"></td><td width=10> </td>";
  }
	
	$buttons.="</tr></table>";
}

$html = str_replace("%BUTTONS%",$buttons,$html);

die($html);

?>