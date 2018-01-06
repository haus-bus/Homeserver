<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($submitted != "")
{
	MYSQL_QUERY("update basicConfig set paramValue='$stufe1' where paramKey='webDimmer1' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("update basicConfig set paramValue='$stufe2' where paramKey='webDimmer2' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("update basicConfig set paramValue='$stufe3' where paramKey='webDimmer3' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("update basicConfig set paramValue='$stufe4' where paramKey='webDimmer4' limit 1") or die(MYSQL_ERROR());
	
	MYSQL_QUERY("update basicConfig set paramValue='$rolloStufe1' where paramKey='webRollo1' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("update basicConfig set paramValue='$rolloStufe2' where paramKey='webRollo2' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("update basicConfig set paramValue='$rolloStufe3' where paramKey='webRollo3' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("update basicConfig set paramValue='$rolloStufe4' where paramKey='webRollo4' limit 1") or die(MYSQL_ERROR());
	
	MYSQL_QUERY("update basicConfig set paramValue='$roomTemp' where paramKey='webRoomTemp' limit 1") or die(MYSQL_ERROR());
	MYSQL_QUERY("update basicConfig set paramValue='$roomHumidity' where paramKey='webRoomHumidity' limit 1") or die(MYSQL_ERROR());
	
}

setupTreeAndContent("editWebPage_design.html");

$html = str_replace("%TITLE%", "Tabellarische Oberfläche", $html);
$html = str_replace("%TITLE2%", "URL der Webapplikation: <a href='web/' target='_blank'>http://".$_SERVER["HTTP_HOST"]."/homeserver/web</a>", $html);

$erg = MYSQL_QUERY("select paramKey,paramValue from basicConfig where paramKey='webDimmer1' or paramKey='webDimmer2' or paramKey='webDimmer3' or paramKey='webDimmer4' limit 4") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	if ($obj->paramKey=="webDimmer1") $html = str_replace("%STUFE1%", $obj->paramValue, $html);
	else if ($obj->paramKey=="webDimmer2") $html = str_replace("%STUFE2%", $obj->paramValue, $html);
	else if ($obj->paramKey=="webDimmer3") $html = str_replace("%STUFE3%", $obj->paramValue, $html);
	else if ($obj->paramKey=="webDimmer4") $html = str_replace("%STUFE4%", $obj->paramValue, $html);
}

$erg = MYSQL_QUERY("select paramKey,paramValue from basicConfig where paramKey='webRollo1' or paramKey='webRollo2' or paramKey='webRollo3' or paramKey='webRollo4' limit 4") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	if ($obj->paramKey=="webRollo1") $html = str_replace("%ROLLO_STUFE1%", $obj->paramValue, $html);
	else if ($obj->paramKey=="webRollo2") $html = str_replace("%ROLLO_STUFE2%", $obj->paramValue, $html);
	else if ($obj->paramKey=="webRollo3") $html = str_replace("%ROLLO_STUFE3%", $obj->paramValue, $html);
	else if ($obj->paramKey=="webRollo4") $html = str_replace("%ROLLO_STUFE4%", $obj->paramValue, $html);
}

$erg = MYSQL_QUERY("select paramKey,paramValue from basicConfig where paramKey='webRoomTemp' or paramKey='webRoomHumidity' limit 2") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	if ($obj->paramKey=="webRoomTemp" && $obj->paramValue==1) $roomTempChecked="checked";
	else if ($obj->paramKey=="webRoomHumidity" && $obj->paramValue==1) $roomHumidityChecked="checked";
}

$html = str_replace("%TEMP_CHECKED%", $roomTempChecked, $html);
$html = str_replace("%HUMIDITY_CHECKED%", $roomHumidityChecked, $html);


show(); 
?>
