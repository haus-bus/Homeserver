<?php
$output=$argv[1];

$check = shell_exec("runlevel");
if (strpos($check,"0")!==FALSE) die("Runlevel 0 (shutdown) erkannt");
if (strpos($check,"6")!==FALSE) die("Runlevel 6 (reboot) erkannt");

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";
$waitForDb=1;
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/userPlugin.php");

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

echo "Server ready ";
if ($output==0) echo "in SILENT MODE !";
else echo "in VERBOSE MODE !";
echo $lb;

while(true)
{
  $data = socket_read($sock, 1024);
  
  $errorcode = socket_last_error();
  if ($errorcode>0)
  {
    $errormsg = socket_strerror($errorcode);
    echo "Fehler ".$errorCode.": ".$errormsg."<br>";
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
    // Kontroll-Byte
    $dataPos++;

    // Nachrichtenzähler
    $messageCounter = $datagramm[$dataPos++];

    // Sender-ID
    $sender = bytesToDword($datagramm,$dataPos);
    
    if ($output==1) echo "Sender: $sender, ClassId Sender: ".getClassId($sender).$lb;
    $senderSubscriberData = getBusSubscriberData($sender);

    // Empfänger-ID
    $receiver = bytesToDword($datagramm,$dataPos);
    if ($output==1) echo "Receiver: $receiver, ClassId Receiver: ".getClassId($receiver).$lb;
    $receiverSubscriberData = getBusSubscriberData($receiver);

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

    // DEBUG Ausgabe
    if ($output==1) echo "Nachrichtenzähler: $messageCounter".$lb;
    if ($output==1) echo "Nachrichtentyp: $messageType".$lb;
    if ($output==1) echo $lb;

    if ($output==1) echo "Sender: ".$senderSubscriberData->debugStr.$lb;
    if ($output==1) echo "Empfänger: ".$receiverSubscriberData->debugStr.$lb;
    if ($output==1) echo $lb;
    if ($output==1) echo "Datenlänge: ".$length.$lb;
    if ($output==1) echo "Funktion: ".$functionData->functionDebugStr.$lb;
    if ($output==1) echo "Parameter: ".$functionData->paramsDebugStr.$lb;

    $mySenderData = new stdClass();
    $mySenderData->instanceObjectId = $senderSubscriberData->objectId;
    $mySenderData->instanceDbId = $senderSubscriberData->featureInstanceObject->id;
    $mySenderData->instanceName = $senderSubscriberData->featureInstanceObject->name;
    $mySenderData->classId = $senderSubscriberData->featureObj->classId;
    $mySenderData->classDbId = $senderSubscriberData->featureInstanceObject->featureClassesId;
    $mySenderData->className = $senderSubscriberData->featureObj->name;
    $mySenderData->controllerObjectId = $senderSubscriberData->controllerObj->objectId;
    $mySenderData->controllerDbId = $senderSubscriberData->featureInstanceObject->controllerId;
    $mySenderData->controllerName = $senderSubscriberData->controllerObj->name;
    $mySenderData->roomDbId = $senderSubscriberData->roomObj->id;
    $mySenderData->roomName = $senderSubscriberData->roomObj->name;

    $myReceiverData = new stdClass();
    $myReceiverData->instanceObjectId = $receiverSubscriberData->objectId;
    $myReceiverData->instanceDbId = $receiverSubscriberData->featureInstanceObject->id;
    $myReceiverData->instanceName = $receiverSubscriberData->featureInstanceObject->name;
    $myReceiverData->classId = $receiverSubscriberData->featureObj->classId;
    $myReceiverData->classDbId = $receiverSubscriberData->featureInstanceObject->featureClassesId;
    $myReceiverData->className = $receiverSubscriberData->featureObj->name;
    $myReceiverData->controllerObjectId = $receiverSubscriberData->controllerObj->objectId;
    $myReceiverData->controllerDbId = $receiverSubscriberData->featureInstanceObject->controllerId;
    $myReceiverData->controllerName = $receiverSubscriberData->controllerObj->name;
    $myReceiverData->roomDbId = $receiverSubscriberData->roomObj->id;
    $myReceiverData->roomName = $receiverSubscriberData->roomObj->name;
    
    $myFunctionData = new stdClass();
    $myFunctionData->functionId = $functionData->functionId;
    $myFunctionData->functionDbId = $functionData->id;
    $myFunctionData->classId = $functionData->classId;
    $myFunctionData->classDbId = $functionData->id;
    $myFunctionData->type = $functionData->type;
    $myFunctionData->name = $functionData->name;
    $myFunctionData->data = $functionData->paramData;
    
    eventOccured($mySenderData, $myReceiverData, $myFunctionData);

    flush();
    ob_flush();
  }
}
?>