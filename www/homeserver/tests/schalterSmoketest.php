<?php
include $_SERVER["DOCUMENT_ROOT"].'/homeserver/include/all.php';
include 'smoketest.inc.php';

$erg = QUERY("select id, featureClassesId, objectId, name, port from featureInstances where id='$featureInstanceId' limit 1");
if ($obj=MYSQLi_FETCH_OBJECT($erg))
{
  $obj->objectId = $obj->objectId;
  echo "Smoketest - Schalter - ".$obj->name." - ".getFormatedObjectId($obj->objectId)."<hr>";
  $receiverObjectId = $obj->objectId;
  $featureClassesId = $obj->featureClassesId;
}
else die("FEHLER! Ung�ltige featureInstanceId $featureInstanceId");


########################
$tests[0]="Schalten mit Status�berpr�fung";
//$tests[-1]="Dummytest";
showTests($tests);


if ($test!="")
{
  echo "<hr>Lese Konfiguration: ";
  // Einmal Konfiguration lesen
  if ($test!=-1) getConfiguration(1);

  if ($test==0 || $test=="all")
  {
    prepareTest($test);
    die("noch nicht implementiert");
    echo "Start TO_OPEN...  <br>";
    start(255,255);
    sleepMS(2000);
    echo "Stop...  <br>";
    $position = stop("evStop");
    if ($position==0 || $position==100) trigger_error("Erwartet 0<position<100, ist aber $position", E_USER_ERROR);


    echo "Weiter TO_OPEN...  <br>";
    start(255,255);
    sleepMS(10000); // Extra lange warten
    echo "Stop...  <br>";
    $position = stop("evStop");
    if ($position!=100) trigger_error("Erwartet position=100, ist aber $position", E_USER_ERROR);

    echo "Start TO_CLOSE...  <br>";
    start(1,1);
    sleepMS(2000);
    echo "Stop...  <br>";
    $position = stop("evStop");
    if ($position==0 || $position==100) trigger_error("Erwartet 0<position<100, ist aber $position", E_USER_ERROR);

    echo "TOGGLE  <br>";
    start(0,255);
    sleepMS(4000); // Extra lange warten
    echo "Stop...  <br>";
    $position = stop("evStop");
    if ($position!=100) trigger_error("Erwartet position=100, ist aber $position", E_USER_ERROR);

    echo "Start TO_CLOSE...  <br>";
    start(1,1);
    sleepMS(2000);
    echo "Stop...  <br>";
    $position = stop("evStop");
    if ($position==0 || $position==100) trigger_error("Erwartet 0<position<100, ist aber $position", E_USER_ERROR);

    echo "Weiter TO_CLOSE...  <br>";
    start(1,1);
    sleepMS(10000); // Extra lange warten
    echo "Stop...  <br>";
    $position = stop("evStop");
    if ($position!=0) trigger_error("Erwartet 0, ist aber $position", E_USER_ERROR);

    echo "Test bestanden ! <br>";
  }

  checkDauerlauf();
}

function start($direction, $expected)
{
  global $featureInstanceId;
  global $lastLogId;

  callInstanceMethodByName($featureInstanceId, "start", array("direction"=>$direction));

  $result = waitForInstanceEventByName($featureInstanceId, 5, evStart, $lastLogId);
  if ($result[0]->dataValue!=$expected) trigger_error("Erwartet direction = $expected, ist aber ".$result[0]->dataValue, E_USER_ERROR);
}

function stop($waitFor)
{
  global $featureInstanceId;
  global $lastLogId;

  callInstanceMethodByName($featureInstanceId, "stop");
  
  $result = waitForInstanceEventByName($featureInstanceId,5, $waitFor, $lastLogId);
  $position = $result[0]->dataValue;

  callInstanceMethodByName($featureInstanceId, "getStatus");
  $result = waitForInstanceResultByName($featureInstanceId,5, "Status", $lastLogId);
  if ($result[0]->dataValue!=$position) trigger_error("Erwartet position = $position, ist aber ".$result[0]->dataValue, E_USER_ERROR);

  return $position;
}

function moveToPosition($position, $expected)
{
  global $featureInstanceId;
  global $lastLogId;

  callInstanceMethodByName($featureInstanceId, "moveToPosition", array("position"=>$position));
  $result = waitForInstanceEventByName($featureInstanceId, 5, evStart, $lastLogId);
  if ($result[0]->dataValue!=$expected) trigger_error("Erwartet direction = $expected, ist aber ".$result[0]->dataValue, E_USER_ERROR);

  $result = waitForInstanceEventByName($featureInstanceId, 20, evStop, $lastLogId);
  if ($result[0]->dataValue!=$position) trigger_error("Erwartet position = $position, ist aber ".$result[0]->dataValue, E_USER_ERROR);

  callInstanceMethodByName($featureInstanceId, "getStatus");
  $result = waitForInstanceEventByName($featureInstanceId, 3, Status, $lastLogId);
  if ($result[0]->dataValue!=$position) trigger_error("Erwartet position = $position, ist aber ".$result[0]->dataValue, E_USER_ERROR);
}

function getConfiguration($debug=0)
{
  global $featureInstanceId;
  global $lastLogId;

  global $closeTime;
  global $openTime;
  global $userPosition;

  echo "deaktiviert <br>";
  return;

  callInstanceMethodByName($featureInstanceId, "getConfiguration");
  
  $result = waitForInstanceResultByName($featureInstanceId, 5, Configuration, $lastLogId);
  $closeTime = $result[0]->dataValue;
  $openTime = $result[1]->dataValue;
  $userPosition = $result[2]->dataValue;

  if ($debug==1) echo "closeTime = $closeTime, openTime = $openTime, userPosition = $userPosition --> OK ? <br>";

}

function setConfiguration($closeTimeIn, $openTimeIn, $userPositionIn)
{
  global $featureInstanceId;
  global $fadingTime;
  global $dimmingTime;

  $data["closeTime"]=$closeTimeIn;
  $data["openTime"]=$openTimeIn;
  $data["userPosition"]=$userPositionIn;
  callInstanceMethodByName($featureInstanceId, "setConfiguration", data);

  sleepMS(500);

  getConfiguration();
  if ($closeTime!=$closeTimeIn) trigger_error("Erwartet closeTime = $closeTimeIn aber ist $closeTime", E_USER_ERROR);
  if ($openTime!=$openTimeIn) trigger_error("Erwartet openTime = $openTimeIn aber ist $openTime", E_USER_ERROR);
  if ($userPosition!=$userPositionIn) trigger_error("Erwartet userPositionIn = $userPositionIn aber ist $userPosition", E_USER_ERROR);
}

function prepareTest($i)
{
  global $featureInstanceId;
  global $lastLogId;
  global $tests;
   
  echo "Starte Test: ".$tests[$i]."<br>";
  flushIt();

  callInstanceMethodByName($featureInstanceId, "getStatus");
  
  $result = waitForInstanceResultByName($featureInstanceId, 5, Status, $lastLogId, "functionDataParams", 1, $featureInstanceId);

  if ($result[0]->dataValue!=0)
  {
  	callInstanceMethodByName($featureInstanceId, "off");
  	callInstanceMethodByName($featureInstanceId, "getStatus");
    $result = waitForInstanceResultByName($featureInstanceId, 5, "Status", $lastLogId);
    if ($result[0]->dataValue!=0) die("Fehler! Status 0 nicht erreicht");
  }
}


  ?>
