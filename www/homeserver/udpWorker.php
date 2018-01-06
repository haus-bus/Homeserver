<?php
error_reporting(7125);
$output=$argv[1];

$check = shell_exec("runlevel");
if (strpos($check,"0")!==FALSE) die("Runlevel 0 (shutdown) erkannt");
if (strpos($check,"6")!==FALSE) die("Runlevel 6 (reboot) erkannt");

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";

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

echo "Opening UDP Socket on port $UDP_PORT and sourceIp ";
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) echo 'Could not set option SO_REUSEADDR to socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
$sourceIp = getNetworkIp();
if( $sourceIp == '255.255.255.255' ) $sourceIp = 0;
echo $sourceIp . $lb;
socket_bind($sock, $sourceIp, $UDP_PORT) or die('Could not bind to address');

$controllerModuleIdFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "ModuleId");
$controllerRemoteObjectsFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "RemoteObjects");
$controllerConfigurationFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "Configuration");

echo "Server ready ";
if ($output==0) echo "in SILENT MODE !";
else echo "in VERBOSE MODE !";
echo $lb;





while(true)
{
  $data = socket_read($sock, 10000);
  
  $errorcode = socket_last_error();
  if ($errorcode>0)
  {
    $errormsg = socket_strerror($errorcode);
    echo "Fehler ".$errorCode.": ".$errormsg."<br>";
    MYSQL_QUERY("INSERT into udpCommandLog (time,  sender,  function,  params) values('".time()."','UdpWorker','SocketError $errorcode','$errormsg')") or die(MYSQL_ERROR());
  }

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
    MYSQL_QUERY("INSERT into udpDataLog (time, data) values('$time','$rawData')") or die(MYSQL_ERROR());
    $udpDataLogId = mysql_insert_id();
    if ($output==1) echo "Raw data: ".$rawData.$lb;


    // Kontroll-Byte
    $dataPos++;

    // Nachrichtenzähler
    $messageCounter = $datagramm[$dataPos++];

    // Sender-ID
    $sender = bytesToDword($datagramm,$dataPos);
    
    if ($output==1) echo "Sender: $sender, ClassId Sender: ".getClassId($sender).$lb;
    $senderSubscriberData = getBusSubscriberData($sender);
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
      else if ($functionId==202)
      {
      	// nach einem evStarted eines Controllers schicken wir automatisch ein getModuleId, getRemoteObjects und getConfiguration
      	unset($commandData);
      	$commandData[0]=2; // getModuleId;
      	$commandData[1]=0; // index = running
      	sendCommand($sender, $commandData);
      	
      	unset($commandData);
      	$commandData[0]=3; // getRemoteObjects;
      	sendCommand($sender, $commandData);

      	unset($commandData);
      	$commandData[0]=5; // getConfiguration;
      	sendCommand($sender, $commandData);
      	
      	// Zeit ist nicht genau, aber wird dann später über den Timesyncer genauer eingestellt
      	unset($commandData);
        $commandData["weekTime"]=toWeekTime(date("N")-1,date("H"),date("i"));
        callObjectMethodByName($sender, "setTime", $commandData);
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
      MYSQL_QUERY("UPDATE controller set online='1' where objectId='$correspondingControllerObjectId' limit 1") or die(MYSQL_ERROR());
      if ($output==1) echo "updating ".getFormatedObjectId($correspondingControllerObjectId)." affected = ".mysql_affected_rows().$lb;
    }
    
    // DEBUG Ausgabe
    if ($output==1) echo "Nachrichtenzähler: $messageCounter".$lb;
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
    
    $senderSubscriberDataDebugStr=mysql_real_escape_string($senderSubscriberData->debugStr);
    $receiverSubscriberDataDebugStr=mysql_real_escape_string($receiverSubscriberData->debugStr);
    $senderSubscriberData=mysql_real_escape_string(serialize($senderSubscriberData));
    $receiverSubscriberData=mysql_real_escape_string(serialize($receiverSubscriberData));
    $functionStr=mysql_real_escape_string($functionData->functionDebugStr);
    $paramsStr=mysql_real_escape_string($functionData->paramsDebugStr);
    $functionDataSql=mysql_real_escape_string(serialize($functionData));

    MYSQL_QUERY("delete from lastreceived where senderObj='$senderObj' and function='$functionStr' limit 1") or die(MYSQL_ERROR());
    MYSQL_QUERY("INSERT into lastreceived (time,type, function, functionData, senderObj) values('$time','$messageType','$functionStr','$functionDataSql','$senderObj')") or die(MYSQL_ERROR());
    
    MYSQL_QUERY("INSERT into udpCommandLog (time, type, messageCounter,  sender,  receiver,  function,  params, functionData, senderSubscriberData,  receiverSubscriberData, udpDataLogId,senderObj,fktId) values('$time','$messageType','$messageCounter','$senderSubscriberDataDebugStr','$receiverSubscriberDataDebugStr','$functionStr','$paramsStr','$functionDataSql', '$senderSubscriberData','$receiverSubscriberData','$udpDataLogId','$senderObj','$fktId')") or die(MYSQL_ERROR());
    $commandId = mysql_insert_id();
    
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
    $erg = MYSQL_QUERY("select SQL_CACHE id,name from controller where objectId='$parentObjectId' limit 1") or die(MYSQL_ERROR());
    if ($obj=MYSQL_FETCH_OBJECT($erg)) $controllerName=$obj->name."_Bootloader";
    else $controllerName = $controllerType."_Bootloader $deviceId";
     
    $erg = MYSQL_QUERY("select SQL_CACHE id from controller where objectId='$sender' limit 1") or die(MYSQL_ERROR());
    if ($row=MYSQL_FETCH_ROW($erg)) MYSQL_QUERY("update controller set name='$controllerName',bootloader='1' where objectId='$sender' limit 1") or die(MYSQL_ERROR());
    else MYSQL_QUERY("INSERT into controller (objectId,name,bootloader) values ('$sender','$controllerName','1')") or die(MYSQL_ERROR());
  }
  else
  {
    // Bootloader offline nehmen, sobald Firmware sich wieder meldet
    $bootloaderObjectId = getObjectId(getDeviceId($sender), getClassId($sender), $BOOTLOADER_INSTANCE_ID);
    MYSQL_QUERY("UPDATE controller set online='0' where objectId='$bootloaderObjectId' limit 1") or die(MYSQL_ERROR());
    
    $erg = MYSQL_QUERY("select SQL_CACHE id from controller where objectId='$sender' limit 1") or die(MYSQL_ERROR());
    if ($row=MYSQL_FETCH_ROW($erg)){}
    else
    {
      $controllerName = $controllerType." $deviceId";
      MYSQL_QUERY("INSERT into controller (objectId,name) values ('$sender','$controllerName')") or die(MYSQL_ERROR());
      readSonoffIds();
    }
  }

 
  $erg = MYSQL_QUERY("select SQL_CACHE * from controller where objectId='$sender' limit 1") or die(MYSQL_ERROR());
  if ($obj=MYSQL_FETCH_OBJECT($erg))
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
            $update.="booterMajor='".mysql_real_escape_string($paramObject->dataValue)."'";
      			continue;
      		}
      		if ($key=="booterMinor" && $paramObject->name=="minorRelease")
      		{
            if ($update!="") $update.=",";
            $update.="booterMinor='".mysql_real_escape_string($paramObject->dataValue)."'";
      			continue;
      		}
      	}
      	
        if ($paramObject->name==$key)
        {
          if ($update!="") $update.=",";
          $update.=$key."='".mysql_real_escape_string($paramObject->dataValue)."'";
        }
      }
    }

    $sql = "UPDATE controller set $update where objectId='$sender' limit 1";
    MYSQL_QUERY($sql) or die(MYSQL_ERROR());
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

      $erg = MYSQL_QUERY("select id from featureinstances where objectId='$objectId' order by id limit 1") or die(MYSQL_ERROR());
      if ($row=MYSQL_FETCH_ROW($erg))
      {
      	//echo "checked ".$row[0]."\n";
      	MYSQL_QUERY("update featureinstances set checked='1' where id='$row[0]'") or die(MYSQL_ERROR());
      }
      else
      {
        $featureClassesId = getFeatureClassesId($objectId);
        $featureName = $featureClasses[$featureClassesId]->name." ".getInstanceId($objectId);
        MYSQL_QUERY("INSERT into featureinstances (controllerId,featureClassesId,objectId,name,checked) values ('$controllerId','$featureClassesId','$objectId','$featureName','1')") or die(MYSQL_ERROR());
        echo "Neues Feature angelegt: ControllerId = $controllerId , FeatureClassId = $featureClassesId , ObjectId = $objectId , Name = $featureName \n";
        $featureInstanceId = mysql_insert_id();
        MYSQL_QUERY("INSERT into groups (single) values ('1')") or die(MYSQL_ERROR());
        $groupId = mysql_insert_id();
        MYSQL_QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values ('$groupId','$featureInstanceId')") or die(MYSQL_ERROR());
        
     		$basicStateNames = getBasicStateNames($featureClassesId);
		    $offName=$basicStateNames->offName;
		    $onName=$basicStateNames->onName;

        MYSQL_QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$offName','1','1')") or die(MYSQL_ERROR());
        MYSQL_QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$onName','2','2')") or die(MYSQL_ERROR());
      }
    }
  }

  // Controller selbst auch als Instanz eintragen
  $controllerId = getControllerId($sender);
  $objectId=$sender;
  $erg = MYSQL_QUERY("select id from featureinstances where objectId='$objectId' limit 1") or die(MYSQL_ERROR());
  if ($row=MYSQL_FETCH_ROW($erg)) MYSQL_QUERY("update featureinstances set checked='1' where id='$row[0]'") or die(MYSQL_ERROR());
  else
  {
    $featureClassesId = $CONTROLLER_CLASSES_ID;
    $featureName = $featureClasses[$featureClassesId]->name;
    MYSQL_QUERY("INSERT into featureinstances (controllerId,featureClassesId,objectId,name,checked) values ('$controllerId ','$featureClassesId','$objectId','$featureName','1')") or die(MYSQL_ERROR());
    echo "Neues Feature angelegt: ControllerId = $controllerId , FeatureClassId = $featureClassesId , ObjectId = $objectId , Name = $featureName \n";
    $featureInstanceId = mysql_insert_id();
    MYSQL_QUERY("INSERT into groups (single) values ('1')") or die(MYSQL_ERROR());
    $groupId = mysql_insert_id();
    MYSQL_QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values ('$groupId','$featureInstanceId')") or die(MYSQL_ERROR());
 		
 		$basicStateNames = getBasicStateNames($featureClassesId);
    $offName=$basicStateNames->offName;
	  $onName=$basicStateNames->onName;

    MYSQL_QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$offName','1','1')") or die(MYSQL_ERROR());
    MYSQL_QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$onName','2','2')") or die(MYSQL_ERROR());
  }
  

  /*
  $erg = MYSQL_QUERY("select * from featureinstances where controllerId='$controllerId' and checked='0'") or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
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