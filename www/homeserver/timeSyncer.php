<?php
error_reporting(0);
$forceClean=$argv[1];

echo "forceClean = $forceClean \n";

$check = shell_exec("runlevel");
if (strpos($check,"0")!==FALSE) die("Runlevel 0 (shutdown) erkannt");
if (strpos($check,"6")!==FALSE) die("Runlevel 6 (reboot) erkannt");

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

$now = time();

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'timeSyncerPending' limit 1") or die(MYSQL_ERROR());
if ($row = MYSQL_FETCH_ROW($erg))
{
	if ($now-$row[0]<120 && $forceClean!=1)
	{
		echo "letzte runde <120 sec. abbruch \n";
		exit;
	}
	MYSQL_QUERY("UPDATE basicConfig set paramValue='$now' where paramKey = 'timeSyncerPending' limit 1") or die(MYSQL_ERROR());
}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('timeSyncerPending','$now')") or die(MYSQL_ERROR());

checkAndSetTimeZone();

$actSekunden = date("s");
$rest=60-$actSekunden;
echo "Warte $rest Sekunden auf volle Minute.\n";
sleep($rest);

$day = date("N")-1;
$data["weekTime"]=toWeekTime($day,date("H"),date("i"));
echo "Sende Zeit day = $day hour = ".date("H")." minute = ".date("i")." - time = ".$data["weekTime"]."\n";
callObjectMethodByName($BROADCAST_OBJECT_ID, "setTime", $data);


$latitude=-1;
$longitude=-1;
$erg = MYSQL_QUERY("select paramValue,paramKey from basicConfig where paramKey='latitude' or paramKey='longitude' limit 2") or die(MYSQL_ERROR());
while($row=MYSQL_FETCH_ROW($erg))
{
	 if ($row[1]=="latitude") $latitude=$row[0];
	 if ($row[1]=="longitude") $longitude=$row[0];
}

if ($latitude!=-1 && $longitude!=-1)
{
  $gmt = date('Z')/3600;
  $sunrise =  date_sunrise ( time(), SUNFUNCS_RET_STRING, $latitude, $longitude,ini_get("date.sunset_zenith"),$gmt);
  //echo $sunrise."\n";
  $parts = explode(":",$sunrise);
  $sunrise = mktime ($parts[0], $parts[1]);
  $sunset =  date_sunset ( time(), SUNFUNCS_RET_STRING, $latitude, $longitude,ini_get("date.sunset_zenith"),$gmt);
  //echo $sunset."\n";
  $parts = explode(":",$sunset);
  $sunset = mktime ($parts[0], $parts[1]);

  $erg = MYSQL_QUERY("select paramKey, paramValue from basicConfig where paramKey = 'offsetSunrise' or paramKey = 'offsetSunset' limit 2") or die(MYSQL_ERROR());
  while($row = MYSQL_FETCH_ROW($erg))
  {  
 	  if ($row[0]=="offsetSunrise") $sunrise+=$row[1]*60;
 	  else if ($row[0]=="offsetSunset") $sunset+=$row[1]*60;
  }
  
  //echo date("H:i",$sunrise)."\n";
  //echo date("H:i",$sunset)."\n";

  if ($sunrise!=$sunset)
  {
    echo "Sende Sonnenauf- und Untergang \n";
    $data["sunriseTime"]=toWeekTime($day, date("H",$sunrise), date("i",$sunrise));
    $data["sunsetTime"]=toWeekTime($day, date("H",$sunset), date("i",$sunset));
    callObjectMethodByName($BROADCAST_OBJECT_ID, "setSunTimes", $data);
  }
}

MYSQL_QUERY("UPDATE basicConfig set paramValue='0' where paramKey = 'timeSyncerPending' limit 1") or die(MYSQL_ERROR());

if ($noClean==1 || file_exists("/nocleanup.txt")) echo "no cleanup";
else
{
  if (date("H")==4 || $forceClean==1)
  {
  	cleanUp();
    echo "Räume Server auf... \n";
  }
}


require($_SERVER["DOCUMENT_ROOT"]."/homeserver/cronHourly.php");
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/user/cronHourly.php");
?>