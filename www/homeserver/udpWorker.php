<?php
error_reporting(7125);
$output=$argv[1];

$check = shell_exec("runlevel");
if (strpos($check,"0")!==FALSE) die("Runlevel 0 (shutdown) erkannt");
if (strpos($check,"6")!==FALSE) die("Runlevel 6 (reboot) erkannt");

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";
$waitForDb=1;
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/simulator.php");
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/serverInstance.php");

checkAndSetTimeZone();


initServer();

//print_r($serverRuleSignals);


$lb="\n";
$line="----------------------------------------------------------------------------------------------------------".$lb;

set_time_limit(0);
ob_implicit_flush();
setupNetwork();

echo "Opening UDP Socket on port $UDP_PORT and sourceIp ";
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) echo 'Could not set option SO_REUSEADDR to socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
if (!socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1)) echo 'Could not set option SO_BROADCAST to socket: '. socket_strerror(socket_last_error()) . PHP_EOL;

$sourceIp = getNetworkIp();
// in case of broadcast address connect to 0, that means to all available interfaces
if( $sourceIp == '255.255.255.255') $sourceIp = "0";
echo $sourceIp . $lb;
echo '  NetworkIP: ' . $UDP_NETWORK_IP . $lb;
echo '  NetworkMask: ' . $UDP_NETWORK_MASK . $lb;
echo '  BroadcastIP: ' . $UDP_BCAST_IP . $lb;
socket_bind($sock, $sourceIp, $UDP_PORT) or die('Could not bind to address');

$controllerModuleIdFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "ModuleId");
$controllerRemoteObjectsFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "RemoteObjects");
$controllerConfigurationFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "Configuration");

echo "Server ready ";
if ($output==0) echo "in SILENT MODE !";
else echo "in VERBOSE MODE !";
echo $lb;

$myMessageCounter=0;
while(true)
{
  $data = socket_read($sock, 10000);
  
  $errorcode = socket_last_error();
  if ($errorcode>0)
  {
    $errormsg = socket_strerror($errorcode);
    echo "Fehler ".$errorCode.": ".$errormsg."<br>";
    QUERY("INSERT into udpCommandLog (time,  sender,  function,  params) values('".time()."','UdpWorker','SocketError $errorcode','$errormsg')");
  }

  if (strlen($data)==0) continue;

  if ($output==1) echo $line;
  if ($output==1) echo date("H:i:s")." Received ".strlen($data)." bytes".$lb;
  
  
  $rawData="";
  unset($datagramm);
  for ($i = 0; $i < strlen($data); $i++)
  {
    $datagramm[$i]=ord($data[$i]);
    if ($rawData!="") $rawData.=",";
    $rawData.="0x".decHex(ord($data[$i]));
  }

  $dataPos=0;

  // UDP-Header (Ist es ein Paket von unseren Busteilnehmern ?)
  $headerOk=true;
  $i=0;
  foreach ($UDP_HEADER_BYTES as $value)
  {
    if ($datagramm[$i]!=$value)
    {
      $headerOk=false;
      break;
    }
    $i++;
    $dataPos++;
  }

  if (!$headerOk) echo "Header nicht von uns".$lb;
  else
  {
 	  // RAW Daten erstmal loggen
    $time=time();
    QUERY("INSERT into udpDataLog (time, data) values('$time','$rawData')");
    $udpDataLogId = query_insert_id();
    if ($output==1) echo "Raw data: ".$rawData.$lb;


    // Kontroll-Byte
    $dataPos++;

    // Nachrichtenzähler
    $messageCounter = $datagramm[$dataPos++];

    // Sender-ID
    $sender = bytesToDword($datagramm,$dataPos);
    
    if ($output==1) echo "Sender: $sender, ClassId Sender: ".getClassId($sender).$lb;
    $senderSubscriberData = getBusSubscriberData($sender);
    //print_r($senderSubscriberData);
    //$dataPos+=4;

    // Empfänger-ID
    $receiver = bytesToDword($datagramm,$dataPos);
    if ($output==1) echo "Receiver: $receiver, ClassId Receiver: ".getClassId($receiver).$lb;
    $receiverSubscriberData = getBusSubscriberData($receiver);
    //$dataPos+=4;

    // Nutzdaten
    $length = bytesToWord($datagramm, $dataPos);
    if ($output==1) echo "Datenlänge: ".$length.$lb;
    $functionId = $datagramm[$dataPos++];
    if ($output==1) echo "Function ID: ".$functionId.$lb;


    if ($receiver==$BROADCAST_OBJECT_ID)
    {
      // Beim Broadcast kann es eine Event des Senders sein, oder ein Broadcastfunktionsaufruf auf allen Controllern
      if ($functionId<$RESULT_START) $featureClassesId = $CONTROLLER_CLASSES_ID;
      else $featureClassesId = getFeatureClassesId($sender);
    }
    else
    {
      // Funktionsausruf
      if ($functionId<$RESULT_START) $featureClassesId = getFeatureClassesId($receiver);
      // Oder RESULT
      else $featureClassesId = getFeatureClassesId($sender);
    }
    $functionData = getFunctionData($featureClassesId, $functionId, $datagramm, $dataPos, $length-1);
    $messageType = $functionData->type;

    // Controllerdaten aktualisieren
    if (getClassId($sender)==$CONTROLLER_CLASS_ID)
    {
      if ($functionId==$controllerModuleIdFktId) updateControllerData($sender, $functionData);
      else if ($functionId==$controllerRemoteObjectsFktId) updateRemoteObjects($sender, $functionData);
      else if ($functionId==$controllerConfigurationFktId)
      {
        $controllerId = getControllerId($sender);
        if ($controllerId!=0) QUERY("update controller set receivedConfiguration='1' where id='$controllerId' limit 1");
      }
      else if ($functionId==202)
      {
      	// nach einem evStarted eines Controllers schicken wir automatisch ein getModuleId, getRemoteObjects und getConfiguration
      	unset($commandData);
      	$commandData[0]=2; // getModuleId;
      	$commandData[1]=0; // index = running
      	sendCommand($sender, $commandData);
      	sleepMs(100);
      	
      	unset($commandData);
      	$commandData[0]=3; // getRemoteObjects;
      	sendCommand($sender, $commandData);
      	sleepMs(100);

      	unset($commandData);
      	$commandData[0]=5; // getConfiguration;
      	sendCommand($sender, $commandData);
      	sleepMs(100);
      	
      	// Zeit ist nicht genau, aber wird dann später über den Timesyncer genauer eingestellt
      	unset($commandData);
        $commandData["weekTime"]=toWeekTime(date("N")-1,date("H"),date("i"));
        callObjectMethodByName($sender, "setTime", $commandData);
      	sleepMs(100);
      }
      
      //else if ($functionId == $controllerConfigurationFktId) updateControllerData($sender, $functionData,$noMessages, $profileStart, $profile, $debugBuffer);
    }


    // Simulator
    if ($SIMULATOR_ACTIVE==1 && getClassId($receiver)==$CONTROLLER_CLASS_ID && ($receiver == $SIMULATOR_OBJECT_ID || $receiver == getBootloaderObjectId($SIMULATOR_OBJECT_ID) || $receiver == $BROADCAST_OBJECT_ID))
      checkSimulatorFunction($sender, $functionData);

   
    $t=time();
    if (getClassId($sender)==$CONTROLLER_CLASS_ID) $correspondingControllerObjectId = $sender;
    else $correspondingControllerObjectId = getObjectId(getDeviceId($sender), $CONTROLLER_CLASS_ID, $FIRMWARE_INSTANCE_ID);

    if ($correspondingControllerObjectId!=$MY_OBJECT_ID && getClassId($sender)!=91) // 91 ist TCP client
    {
      QUERY("UPDATE controller set online='1' where objectId='$correspondingControllerObjectId' limit 1");
      if ($output==1) echo "updating ".getFormatedObjectId($correspondingControllerObjectId)." affected = ".mysqli_affected_rows().$lb;
    }
    
    // DEBUG Ausgabe
    $myMessageCounter++;
    if ($output==1) echo "Nachrichtenzähler: $messageCounter  [".$myMessageCounter."]".$lb;
    if ($output==1) echo "Nachrichtentyp: $messageType".$lb;
    if ($output==1) echo $lb;

    if ($output==1) echo "Sender: ".$senderSubscriberData->debugStr.$lb;
    if ($output==1) echo "Empfänger: ".$receiverSubscriberData->debugStr.$lb;
    if ($output==1) echo $lb;
    if ($output==1) echo "Datenlänge: ".$length.$lb;
    if ($output==1) echo "Function: ".$functionData->functionDebugStr.$lb;
    if ($output==1) echo "Parameter: ".$functionData->paramsDebugStr.$lb;

    $senderObj = $senderSubscriberData->objectId;
    $fktId = $functionData->functionId;

    $senderSubscriberDataDebugStr=query_real_escape_string($senderSubscriberData->debugStr);
    $receiverSubscriberDataDebugStr=query_real_escape_string($receiverSubscriberData->debugStr);
    $senderSubscriberData=query_real_escape_string(serialize($senderSubscriberData));
    $receiverSubscriberData=query_real_escape_string(serialize($receiverSubscriberData));
    $functionStr=query_real_escape_string($functionData->functionDebugStr);
    $paramsStr=query_real_escape_string($functionData->paramsDebugStr);
    $functionDataSql=query_real_escape_string(serialize($functionData));

    QUERY("delete from lastreceived where senderObj='$senderObj' and function='$functionStr' limit 1");
    QUERY("INSERT into lastreceived (time,type, function, functionData, senderObj) values('$time','$messageType','$functionStr','$functionDataSql','$senderObj')");
    
    QUERY("INSERT into udpCommandLog (time, type, messageCounter,  sender,  receiver,  function,  params, functionData, senderSubscriberData,  receiverSubscriberData, udpDataLogId,senderObj,fktId) values('$time','$messageType','$messageCounter','$senderSubscriberDataDebugStr','$receiverSubscriberDataDebugStr','$functionStr','$paramsStr','$functionDataSql', '$senderSubscriberData','$receiverSubscriberData','$udpDataLogId','$senderObj','$fktId')");
    $commandId = query_insert_id();
    
    // Server
    if ($SERVER_ACTIVE==1) checkServerFunction($receiver, $sender, $functionData, $commandId);

    flush();
    ob_flush();
  }
}

function updateControllerData($sender, $functionData)
{
  global $BOOTLOADER_INSTANCE_ID;
  global $FIRMWARE_INSTANCE_ID;

  $deviceId = getDeviceId($sender);
  
  $controllerType="Mastercontroller";
  foreach ((array)$functionData->paramData as $paramObject)
  {
    if ($paramObject->name=="name" && strpos($paramObject->dataValue,"Booter")!==FALSE) $isBooter=1;
    if ($paramObject->name=="firmwareId" && $paramObject->dataValue==2) $controllerType="Taster";
    if ($paramObject->name=="size" && $paramObject->dataValue==999) $controllerType="SW-Controller";
  }

  if (getInstanceId($sender) == $BOOTLOADER_INSTANCE_ID)
  {
    $parentObjectId = getObjectId(getDeviceId($sender), getClassId($sender), $FIRMWARE_INSTANCE_ID);
    $erg = QUERY("select SQL_CACHE id,name from controller where objectId='$parentObjectId' limit 1");
    if ($obj=mysqli_fetch_OBJECT($erg)) $controllerName=$obj->name."_Bootloader";
    else $controllerName = $controllerType."_Bootloader $deviceId";
     
    $erg = QUERY("select SQL_CACHE id from controller where objectId='$sender' limit 1");
    if ($row=mysqli_fetch_ROW($erg)) QUERY("update controller set name='$controllerName',bootloader='1' where objectId='$sender' limit 1");
    else QUERY("INSERT into controller (objectId,name,bootloader) values ('$sender','$controllerName','1')");
  }
  else
  {
    // Bootloader offline nehmen, sobald Firmware sich wieder meldet
    $bootloaderObjectId = getObjectId(getDeviceId($sender), getClassId($sender), $BOOTLOADER_INSTANCE_ID);
    QUERY("UPDATE controller set online='0' where objectId='$bootloaderObjectId' limit 1");
    
    $erg = QUERY("select SQL_CACHE id from controller where objectId='$sender' limit 1");
    if ($row=mysqli_fetch_ROW($erg)){}
    else
    {
      $controllerName = $controllerType." $deviceId";
      QUERY("INSERT into controller (objectId,name) values ('$sender','$controllerName')");
      readSonoffIds();
    }
  }

 
  $erg = QUERY("select SQL_CACHE * from controller where objectId='$sender' limit 1");
  if ($obj=mysqli_fetch_OBJECT($erg))
  {
    $update="online='1'";
    foreach ($obj as $key => $value)
    {
      if ($key=="name") continue;
      foreach ((array)$functionData->paramData as $paramObject)
      {
      	if ($isBooter==1)
      	{
      		if ($key=="majorRelease") continue;
      		if ($key=="minorRelease") continue;
      		if ($key=="booterMajor" && $paramObject->name=="majorRelease")
      		{
            if ($update!="") $update.=",";
            $update.="booterMajor='".query_real_escape_string($paramObject->dataValue)."'";
      			continue;
      		}
      		if ($key=="booterMinor" && $paramObject->name=="minorRelease")
      		{
            if ($update!="") $update.=",";
            $update.="booterMinor='".query_real_escape_string($paramObject->dataValue)."'";
      			continue;
      		}
      	}
      	
        if ($paramObject->name==$key)
        {
          if ($update!="") $update.=",";
          $update.=$key."='".query_real_escape_string($paramObject->dataValue)."'";
        }
      }
    }

    $update.=",receivedModuleId='1'";
    $sql = "UPDATE controller set $update where objectId='$sender' limit 1";
    QUERY($sql);
  }
}

/*
 ObjectId:
 Byte 0: deviceIdByte0
 Byte 1: deviceIdByte1
 Byte 2: CLASS_ID
 Byte 3: fimware-intern (meist Nummer der Instanz)
 */
function updateRemoteObjects($sender, $functionData)
{
	global $CONTROLLER_CLASSES_ID;
	
  $featureClasses = readFeatureClasses();

  $deviceId = getDeviceId($sender);
  $controllerId = getControllerId($sender);
  if ($controllerId==0) return;
  
  QUERY("update controller set receivedObjects='1' where id='$controllerId' limit 1");
  
  //echo "updateRemoteObjects $deviceId \n";

  $remoteObjects = $functionData->paramData[0]->dataValue;
  //echo $remoteObjects."\n";
  if ($remoteObjects!="")
  {
    $elements = explode(";",$remoteObjects);
    foreach ($elements as $value)
    {
      $parts = explode(",",$value);
      $objectId = getObjectId($deviceId, $parts[1], $parts[0]);

      $erg = QUERY("select id from featureinstances where objectId='$objectId' order by id limit 1");
      if ($row=mysqli_fetch_ROW($erg))
      {
      	//echo "checked ".$row[0]."\n";
      	QUERY("update featureinstances set checked='1' where id='$row[0]'");
      }
      else
      {
        $featureClassesId = getFeatureClassesId($objectId);
        $featureName = $featureClasses[$featureClassesId]->name." ".getInstanceId($objectId);
        QUERY("INSERT into featureinstances (controllerId,featureClassesId,objectId,name,checked) values ('$controllerId','$featureClassesId','$objectId','$featureName','1')");
        echo "Neues Feature angelegt: ControllerId = $controllerId , FeatureClassId = $featureClassesId , ObjectId = $objectId , Name = $featureName \n";
        $featureInstanceId = query_insert_id();
        QUERY("INSERT into groups (single) values ('1')");
        $groupId = query_insert_id();
        QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values ('$groupId','$featureInstanceId')");
        
     		$basicStateNames = getBasicStateNames($featureClassesId);
		    $offName=$basicStateNames->offName;
		    $onName=$basicStateNames->onName;

        QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$offName','1','1')");
        QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$onName','2','2')");
      }
    }
  }

  // Controller selbst auch als Instanz eintragen
  $controllerId = getControllerId($sender);
  $objectId=$sender;
  $erg = QUERY("select id from featureinstances where objectId='$objectId' limit 1");
  if ($row=mysqli_fetch_ROW($erg)) QUERY("update featureinstances set checked='1' where id='$row[0]'");
  else
  {
    $featureClassesId = $CONTROLLER_CLASSES_ID;
    $featureName = $featureClasses[$featureClassesId]->name;
    QUERY("INSERT into featureinstances (controllerId,featureClassesId,objectId,name,checked) values ('$controllerId ','$featureClassesId','$objectId','$featureName','1')");
    echo "Neues Feature angelegt: ControllerId = $controllerId , FeatureClassId = $featureClassesId , ObjectId = $objectId , Name = $featureName \n";
    $featureInstanceId = query_insert_id();
    QUERY("INSERT into groups (single) values ('1')");
    $groupId = query_insert_id();
    QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values ('$groupId','$featureInstanceId')");
 		
 		$basicStateNames = getBasicStateNames($featureClassesId);
    $offName=$basicStateNames->offName;
	  $onName=$basicStateNames->onName;

    QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$offName','1','1')");
    QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$onName','2','2')");
  }
  

  /*
  $erg = QUERY("select * from featureinstances where controllerId='$controllerId' and checked='0'");
  while($obj=mysqli_fetch_OBJECT($erg))
  {
    //echo "=================================================\n";
    echo "Achtung FEATURE ".$obj->name." wurde gel?scht von controllerId = $controllerId - objectId sender = $sender \n";
    //print_r($obj);
    //echo "DEAKTIVIERT !!!! \n";
    //echo "=================================================\n";
    deleteFeatureInstance($obj->id);
  }
  */
}

function udate($format, $utimestamp = null)
{
    if (is_null($utimestamp))
        $utimestamp = microtime(true);

    $timestamp = floor($utimestamp);
    $milliseconds = round(($utimestamp - $timestamp) * 1000000);

    return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}
?>