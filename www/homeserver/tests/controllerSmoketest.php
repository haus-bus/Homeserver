<?php
include $_SERVER["DOCUMENT_ROOT"].'/homeserver/include/all.php';
include 'smoketest.inc.php';

$erg = QUERY("select id, objectId, name from controller where id='$featureInstanceId' limit 1");
if ($obj=MYSQLi_FETCH_OBJECT($erg))
{
  echo "Smoketest - Controller - ".$obj->name." - ".getFormatedObjectId($obj->objectId)."<hr>";
  $receiverObjectId = $obj->objectId;
  $featureClassesId = $CONTROLLER_CLASSES_ID;
}
else die("FEHLER! Ung�ltige featureInstanceId $featureInstanceId");

########################
$tests[0]="Ping Pong ohne Delay";
$tests[1]="Ping Pong mit 1ms Delay";
$tests[2]="Ping Pong mit 4 ms Delay";
showTests($tests);


if ($test!="")
{
  if ($test==0 || $test==1 || $test==2 || $test=="all")
  {
  	$nrPings=100;
  	if ($test==0) $delay=0;
  	else if ($test==1) $delay=1;
  	else if ($test==2) $delay=4;
  	
  	$lastId = updateLastLogId();

    echo "Schicke $nr Pings mit einer Verz�gerung von $delay ms <br>";
    
    for ($i=0;$i<$nrPings;$i++)
    {
    	  callObjectMethodByName($receiverObjectId, "ping");
    	  if ($delay>0) sleepMS($delay);
    }

    sleep(2);
    
    $erg = QUERY("select count(*) from udpcommandlog where id>'$lastId' and senderObj='$receiverObjectId' and function='pong'");
    $row=MYSQLi_FETCH_row($erg);
    $receivedPongs = $row[0];
    
    if ($receivedPongs!=$nrPings) die("Fehler: Nur $receivedPongs von $nrPings empfangen");
    
    echo "Test bestanden ! <br>";
  }
}

?>
