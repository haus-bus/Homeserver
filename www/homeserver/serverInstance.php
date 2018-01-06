<?php
$PC_SERVER_DEVICE_ID=12222;

require($_SERVER["DOCUMENT_ROOT"]."/homeserver/APcServer.php");

$WETTER_OBJECT_ID = getObjectId($PC_SERVER_DEVICE_ID, getClassIdByName("Wetter"), 1);
$POWER_OBJECT_ID = getObjectId($PC_SERVER_DEVICE_ID, getClassIdByName("StromzählServer"), 1);
$setUnitGroupStateFunctionId = getObjectFunctionIdByName($SERVER_OBJECT_ID, "setUnitGroupState");
$reloadUserPluginFunctionId = getObjectFunctionIdByName($EXECUTOR_OBJECT_ID, "reloadUserPlugin");
$getWeatherFunctionId = getObjectFunctionIdByName($WETTER_OBJECT_ID, "getWeather");
$schalterClassId = getClassIdByName("Schalter");
readSonoffIds();

function initServer()
{
	global $groupStates;
	
	for ($i=0;$i<8;$i++)
	{
  	  for ($a=0;$a<8;$a++)
	  {
	    $groupStates[$i][$a]=0;
	  }
	}
	
	QUERY("delete from serverVariables where instance<2");
}

function readSonoffIds()
{
	global $sonoffs;
	$erg = MYSQL_QUERY("select objectId from controller where firmwareId='5'") or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
	  $sonoffs[getDeviceId($obj->objectId)]=1;
  }
}

function checkServerFunction($receiver, $sender, $functionData, $commandId=0)
{
  global $output;
  global $setUnitGroupStateFunctionId, $reloadUserPluginFunctionId;
  global $getWeatherFunctionId;
  global $WETTER_OBJECT_ID;
  global $SERVER_OBJECT_ID, $BROADCAST_OBJECT_ID;
  global $EXECUTOR_OBJECT_ID;
  global $POWER_OBJECT_ID;
  global $currentReaderClassId;
  global $sonoffs;
  
  superCheckServerFunction($receiver, $sender, $functionData);

	$receiverClassId=getClassId($receiver);
	$receiverDeviceId = getDeviceId($receiver);
	
  if ($receiver==$WETTER_OBJECT_ID)
  {
    if ($functionData->functionId == $getWeatherFunctionId) sendWeather($sender);
  	else echo "Unbekannte wetter Serverfunktion ".$functionData->functionId."\n";
  }
  else if ($receiver == $SERVER_OBJECT_ID)
  {
    if ($functionData->functionId == $setUnitGroupStateFunctionId) setUnitGroupState($functionData);
  }
  else if ($receiver == $BROADCAST_OBJECT_ID)
  {
  	$senderClassId=getClassId($sender);
    if ($senderClassId == 0 &&$functionData->functionId == 202) sendTime(); //evStarted von einem Controller
  }
  else if ($receiver == $EXECUTOR_OBJECT_ID)
  {
  	if ($functionData->functionId == $reloadUserPluginFunctionId) reloadUdpClientServer();
  }
  else if ($sonoffs[$receiverDeviceId]==1) monitorSonoffCommands($receiver, $sender, $functionData, $commandId);
}

function monitorSonoffCommands($receiver, $sender, $functionData, $commandId)
{
	 $command="php monitorSonoff.php $commandId $receiver ".$functionData->name." ".$functionData->paramData[0]->dataValue." > /dev/null 2>/dev/null &";
	 $erg = exec($command);
}

function reloadUdpClientServer()
{
	 echo "reloading client server \n";
	 exec("/etc/init.d/udpClientServer restart");
}

// @deprecated
function sendWeather($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SERVER_OBJECT_ID;
  global $WETTER_OBJECT_ID;
  
  $weather = getWeather(getWoeID());
	
  $sunrise=strtotime($weather["sunrise"]);
  $sunset=strtotime($weather["sunset"]);

  $sunriseWeektime = toWeekTime(date("N",$sunrise)-1, date("H",$sunrise), date("i",$sunrise));
  $sunsetWeektime = toWeekTime(date("N",$sunset)-1, date("H",$sunset), date("i",$sunset));
  

  $fktId = getObjectFunctionsIdByName($WETTER_OBJECT_ID, "weather");

  $paramData["humidity"]=$weather["humidity"];
  $paramData["pressure"]=$weather["pressure"];
  $paramData["temp"]=$weather["temp"];
  $paramData["sunrise"]=$sunriseWeektime;
  $paramData["sunset"]=$sunsetWeektime;
  $paramData["text"]=$weather["text"];

  echo "sendWeather \n";
  print_r($paramData);
  
  callInstanceMethodForObjectId($sender, $fktId, $paramData, $WETTER_OBJECT_ID);
  if ($output==1) echo "Server: ".$debug."<br>";
}

function setUnitGroupState($functionData)
{
	global $groupStates;
	global $SERVER_OBJECT_ID;
	global $output;
	
	$paramData = $functionData->paramData;
	
	$groupId =  $paramData[0]->dataValue;
	$member =  $paramData[1]->dataValue;
	$memberState =  $paramData[2]->dataValue;
	$triggerThreshold =  $paramData[3]->dataValue;

	$onBefore=0;
	foreach($groupStates[$groupId] as $state)
	{
		 if ($state==1) $onBefore++;
	}
	
	$groupStates[$groupId][$member]=$memberState;
	
	$on=0;
	foreach($groupStates[$groupId] as $state)
	{
		 if ($state==1) $on++;
	}
	
	if ($output==1) echo $groupId." - ".$member." - ".$memberState." - onBefore = ".$onBefore.", on = ".$on.", trigger = ".$triggerThreshold."\n";
	
	if($onBefore>0 && $on==0) callObjectMethodByName($SERVER_OBJECT_ID, "evGroupOff", array("index" => $groupId));
	else if ($onBefore<$triggerThreshold && $on==$triggerThreshold) callObjectMethodByName($SERVER_OBJECT_ID, "evGroupOn", array("index" => $groupId));
	else if ($onBefore==0 && $on>0) callObjectMethodByName($SERVER_OBJECT_ID, "evGroupUndefined", array("index" => $groupId));
	else if ($onBefore==$triggerThreshold && $on<$triggerThreshold) callObjectMethodByName($SERVER_OBJECT_ID, "evGroupUndefined", array("index" => $groupId));
}

function sendTime()
{
  global $output;
  
  if ($output==1) echo "sendTime";
  
  pclose(popen("wget -q --delete-after -b -o wgetout.txt http://localhost/homeserver/timeSyncer.php?noClean=1", "r"));
  if ($output==1) echo "sendTime ende \n";
}

function sendServerRemoteObjects($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SERVER_OBJECT_ID;
  global $CONTROLLER_CLASS_ID;
  global $SERVER_CLASS_ID;
  global $debug;
  global $output;

  $GET_REMOTE_OBJECTS_RESULT_INDEX =  getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "RemoteObjects");
  $pcServerClassesId=getClassIdByName("PC-Server");
  $tasterClassesId=getClassIdByName("Taster");
  $schalterClassesId=getClassIdByName("Schalter");
  $wetterClassesId=getClassIdByName("Wetter");
  $ethernetClassesId=getClassIdByName("Ethernet");
  
  $result="1,$pcServerClassesId;1,$ethernetClassesId";
  for ($i=1;$i<=50;$i++)
  {
  	$result.=";$i,$tasterClassesId";
  }
  for ($i=1;$i<=50;$i++)
  {
  	$result.=";$i,$schalterClassesId";
  }
  
  $paramData["objectList"]=$result;
  $mySender = $SERVER_OBJECT_ID;

  callInstanceMethodForObjectId($sender, $GET_REMOTE_OBJECTS_RESULT_INDEX, $paramData, $mySender);
  if ($output==1) echo "Server: ".$debug."<br>";
}


function powerSetClientId($sender, $functionData)
{
}

function powerGetConnectionStatus($sender, $functionData)
{
}

function powerGetActualPower($sender, $functionData)
{
}

function powerGetConsumption($sender, $functionData)
{
}

function powerSetConfiguration($sender, $functionData)
{
}

?>