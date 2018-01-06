<?php
include $_SERVER["DOCUMENT_ROOT"].'/homeserver/include/all.php';
include 'smoketest.inc.php';
error_reporting(E_ALL);

########################
$tests[0]="Gruppe AUS";
$tests[1]="Gruppe AN - AN - AUS - AUS";
showTests($tests);

//$triggerBySoftware = 1;

if ($test!="")
{
	if( $test == 0  || $test=="all" )
	{
		testAUS();
	}  
	if( $test == 1  || $test=="all" )
	{
		testAN();
		testAN();
		testAUS();
		testAUS();
	}  	

  	checkDauerlauf();
}

function testLastActorEvent( $objectId, $event, $logId )
{
 	$erg = QUERY("select name from featureinstances where objectId=$objectId limit 1");
  if ($obj = MYSQL_FETCH_ROW($erg))
  {
  	
  	if( -1 == waitForObjectEventByName( $objectId, 5, $event, $logId, "senderData", 0 ) )
	{
		echo "<br>" . $obj[0];
		if( $event == "evOn" )  echo " nicht angegangen";
		else echo " nicht ausgegangen";
		flushIt();
		return true;
	} 
	return false;
}
else die("fehler im test");
 
}

function testAN()
{
	global $triggerBySoftware;
	
  $logId = updateLastLogId();	
  if( $triggerBySoftware == 1 )
  {
  	echo "<br><b>Schalte Szene ein!</b>";
  	callObjectMethodByName( 0x204E1021 , "evClicked");
  }
  else 
  {	
    echo "<br><b>Bitte Szene per Taster einschalten!</b>";
  }	

  flushIt();
  waitForObjectEventByName( 0x204E1021 , 5, "evClicked", $logId );  
  $hasError = false;
  $hasError |= testLastActorEvent( 0x59001301, "evOn", $logId );
  $hasError |= testLastActorEvent( 0x59001108, "evOff", $logId );
  $hasError |= testLastActorEvent( 0x53581308, "evOn", $logId );
  $hasError |= testLastActorEvent( 0x204e1544, "evOn", $logId );
  $hasError |= testLastActorEvent( 0x59001103, "evOn", $logId );
  $hasError |= testLastActorEvent( 0x741d1103, "evOn", $logId );
  $hasError |= testLastActorEvent( 0x741d1104, "evOn", $logId );
  $hasError |= testLastActorEvent( 0x59001106, "evOn", $logId );
  $hasError |= testLastActorEvent( 0x204e1531, "evOn", $logId );
  
  if( !$hasError ) echo " -> OK!";	
}

function testAUS()
{
	global $triggerBySoftware;
	
  $logId = updateLastLogId();	
  if( $triggerBySoftware == 1 )
  {
  	echo "<br><b>Schalte Szene aus!</b>";
  	callObjectMethodByName( 0x204E1021, "evHoldStart");
  }
  else 
  {	
    echo "<br><b>Bitte Szene per Taster ausschalten!</b>";
  }	  
  
  flushIt();
  waitForObjectEventByName( 0x204E1021, 5, "evHoldStart", $logId );  

    $hasError = false;
  	$hasError |= testLastActorEvent( 0x01f71103, "evOn", $logId );
  	$hasError |= testLastActorEvent( 0x01f71301, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x01f71107, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x59001106, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x59001103, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x59001302, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x59001301, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x59001108, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x59001105, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x59001104, "evOff", $logId );  	
  	$hasError |= testLastActorEvent( 0x2d3d1305, "evOff", $logId );
	$hasError |= testLastActorEvent( 0x2d3d1102, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x643d1301, "evOn", $logId );
  	$hasError |= testLastActorEvent( 0x643d1367, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x643d1108, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x643d1107, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x53581308, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x53581307, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x53581306, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x741d1103, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x741d1104, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x204e1531, "evOff", $logId );
  	$hasError |= testLastActorEvent( 0x204e1544, "evOff", $logId );

  	if( !$hasError ) echo " -> OK!";	
}

?>
