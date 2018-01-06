<?php
include $_SERVER["DOCUMENT_ROOT"].'/homeserver/include/all.php';
include 'smoketest.inc.php';

$erg = MYSQL_QUERY("select id, featureClassesId, objectId, name, port from featureInstances where id='$featureInstanceId' limit 1") or die(MYSQL_ERROR());
if ($obj=MYSQL_FETCH_OBJECT($erg))
{
  $obj->objectId = $obj->objectId;
  echo "Smoketest - Dimmer - ".$obj->name." - ".getFormatedObjectId($obj->objectId)."<hr>";
  $receiverObjectId = $obj->objectId;
  $featureClassesId = $obj->featureClassesId;
}
else die("FEHLER! Ungültige featureInstanceId $featureInstanceId");

########################
$tests[0]="In 10 Stufen mit 50ms dimmen und dabei Status und Events prüfen";
$tests[1]="Start/Stop und dabei Status und Events prüfen";
$tests[2]="Zufallsstresstest und Status und Event vom letzten Ereignis prüfen";
//$tests[-1]="Dummytest";
showTests($tests);


if ($test!="")
{
  echo "<hr>Lese Konfiguration: ";
  // Einmal Konfiguration lesen
  if ($test!=-1)
  getConfiguration();

  echo "mode = $mode, fadingTime = $fadingTime, dimmingTime = $dimmingTime --> OK ? <br>";

  if ($test==0 || $test=="all")
  {
    prepareTest($test);

    for ($i=100;$i>=0;$i-=10)
    {
      echo "Dimme auf ".$i."% <br>";
      dimm($i,1);
    }
    echo "Test bestanden ! <br>";
  }

  if ($test==1 || $test=="all")
  {
    prepareTest($test);
    echo "Konfiguriere langsame Dimmingtime (60 = 3s) <br>";
    setConfiguration(0, 2, 60);

    echo "Start TO_LIGHT...  <br>";
    start(1,1);
    sleepMS(500);
    echo "Stop...  <br>";
    $brightness = stop("evOn");
    if ($brightness==0 || $brightness==100) trigger_error("Erwartet 0<brightness<100, ist aber $brightness", E_USER_ERROR);

    echo "Weiter TO_LIGHT...  <br>";
    start(1,1);
    sleepMS(4000); // Extra lange warten
    echo "Stop...  <br>";
    $brightness = stop("evOn");
    if ($brightness!=100) trigger_error("Erwartet brightness=100, ist aber $brightness", E_USER_ERROR);

    echo "Start TO_DARK...  <br>";
    start(255,255);
    sleepMS(500);
    echo "Stop...  <br>";
    $brightness = stop("evOn");
    if ($brightness==0 || $brightness==100) trigger_error("Erwartet 0<brightness<100, ist aber $brightness", E_USER_ERROR);

    echo "TOGGLE  <br>";
    start(0,1);
    sleepMS(4000); // Extra lange warten
    echo "Stop...  <br>";
    $brightness = stop("evOn");
    if ($brightness!=100) trigger_error("Erwartet brightness=100, ist aber $brightness", E_USER_ERROR);

    echo "Start TO_DARK...  <br>";
    start(255,255);
    sleepMS(500);
    echo "Stop...  <br>";
    $brightness = stop("evOn");
    if ($brightness==0 || $brightness==100) trigger_error("Erwartet 0<brightness<100, ist aber $brightness", E_USER_ERROR);

    echo "Weiter TO_DARK...  <br>";
    start(255,255);
    sleepMS(4000); // Extra lange warten
    echo "Stop...  <br>";
    $brightness = stop("evOff");
    if ($brightness!=0) trigger_error("Erwartet 0, ist aber $brightness", E_USER_ERROR);

    echo "Test bestanden ! <br>";
  }

  if ($test==2 || $test=="all")
  {
    prepareTest($test);
    echo "500 mal Stressdimmen mit Zufallswerten<br>";
    for($i=0;$i<500;$i++)
    {
      $brightness = rand(0, 100);
      $data["brightness"]=$brightness; // Helligkeit
      $speed = rand(0, 255);
      $data["fadingTime"]=$speed; // Dauer a 50 ms
      callInstanceMethodByName($featureInstanceId, "setBrightness", $data);
      callInstanceMethodByName($featureInstanceId, "getConfiguration");
      callInstanceMethodByName($featureInstanceId, "getStatus");
      callInstanceMethodByName($featureInstanceId, "start");
      callInstanceMethodByName($featureInstanceId, "stop");
      flushIt();
    }
    waitForIdle();
    echo "Setze definiert Helligkeit 100<br>";
    dimm(100, 1);
    echo "Setze definiert Helligkeit 0<br>";
    dimm(0, 1);
    echo "Test bestanden ! <br>";
  }

  if ($test==-1)
  {
    dimm(100, 3);
    flushIt();
    sleep(2);
    echo "Dummytest bestanden ! <br>";
  }

  checkDauerlauf();
}

function start($direction, $expected)
{
  global $featureInstanceId;
  global $lastLogId;

  callInstanceMethodByName($featureInstanceId, "start", array("direction"=>$direction));
  $result = waitForInstanceEventByName($featureInstanceId, 5, "evStart", $lastLogId);
  if ($result[0]->dataValue!=$expected) trigger_error("Erwartet direction = $expected, ist aber ".$result[0]->dataValue, E_USER_ERROR);
}

function stop($waitFor)
{
  global $featureInstanceId;
  global $lastLogId;

  callInstanceMethodByName($featureInstanceId, "stop");
  $result = waitForInstanceEventByName($featureInstanceId,5, $waitFor, $lastLogId);
  $brightness = $result[0]->dataValue;

  callInstanceMethodByName($featureInstanceId, "getStatus");
  $result = waitForInstanceResultByName($featureInstanceId, 5, "Status", $lastLogId);
  if ($result[0]->dataValue!=$brightness) trigger_error("Erwartet brightness = $brightness, ist aber ".$result[0]->dataValue, E_USER_ERROR);

  return $brightness;
}


function dimm($brightness, $speed)
{
  global $featureInstanceId;
  global $fadingTime;
  global $lastLogId;

  callInstanceMethodByName($featureInstanceId, "setBrightness", array("brightness"=>$brightness, "fadingTime"=>$speed));
  $result = waitForInstanceEventByName($featureInstanceId,5, "evStart", $lastLogId);
  if ($brightness>0)
  {
    $result = waitForInstanceEventByName($featureInstanceId,5, "evOn", $lastLogId);
    if ($result[0]->dataValue!=$brightness) trigger_error("Erwartet brightness = $brightness, ist aber ".$result[0]->dataValue." lastLogId = $lastLogId", E_USER_ERROR);
  }
  else
  {
    $result = waitForInstanceEventByName($featureInstanceId,5, "evOff", $lastLogId);
  }

  callInstanceMethodByName($featureInstanceId, "getStatus");
  $result = waitForInstanceResultByName($featureInstanceId,5, "Status", $lastLogId);
  if ($result[0]->dataValue!=$brightness) trigger_error("Erwartet brightness = $brightness, ist aber ".$result[0]->dataValue." lastLogId = $lastLogId", E_USER_ERROR);
}



function getConfiguration()
{
  global $featureInstanceId;
  global $lastLogId;

  global $fadingTime;
  global $dimmingTime;
  global $mode;
   
  callInstanceMethodByName($featureInstanceId, "getConfiguration");
  $result = waitForInstanceResultByName($featureInstanceId,5, "Configuration", $lastLogId);
  $mode = $result[0]->dataValue;
  $fadingTime = $result[1]->dataValue;
  $dimmingTime = $result[2]->dataValue;
}

function setConfiguration($modeIn, $fadingTimeIn, $dimmingTimeIn)
{
  global $featureInstanceId;
  global $fadingTime;
  global $dimmingTime;

  $data["mode"]=$modeIn;
  $data["fadingTime"]=$fadingTimeIn;
  $data["dimmingTime"]=$dimmingTimeIn;
  callInstanceMethodByName($featureInstanceId, "setConfiguration", $data);

  sleepMS(500);

  getConfiguration();
  if ($mode!=$modeIn) trigger_error("Erwartet mode = $modeIn aber ist $mode", E_USER_ERROR);
  if ($fadingTime!=$fadingTimeIn) trigger_error("Erwartet fadingTime = $fadingTimeIn aber ist $fadingTime", E_USER_ERROR);
  if ($dimmingTime!=$dimmingTimeIn) trigger_error("Erwartet dimmingTime = $dimmingTimeIn aber ist $dimmingTime", E_USER_ERROR);
}

function prepareTest($i)
{
  global $featureInstanceId;
  global $tests;
   
  echo "Starte Test: ".$tests[$i]."<br>";

  // Definiert ausschalten
  $data["brightness"]=0; // Helligkeit
  $data["fadingTime"]=1; // Dauer a 50 ms
  callInstanceMethodByName($featureInstanceId, "setBrightness", $data);
  
  sleepMS(500);
}


?>
