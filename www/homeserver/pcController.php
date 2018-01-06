<?php
$PC_SERVER_DEVICE_ID=$argv[1];
if ($PC_SERVER_DEVICE_ID==1) die("serverDeviceId 12222 ist für den PC-Server reserviert. Bitte eine andere Instanz vergeben.");
if ($PC_SERVER_DEVICE_ID=="") $PC_SERVER_DEVICE_ID=12223;
$output=$argv[2];


if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";

require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/APcServer.php");

checkAndSetTimeZone();

$lb="\n";
$line="----------------------------------------------------------------------------------------------------------".$lb;

set_time_limit(0);
ob_implicit_flush();

echo date("H:i:s").": Opening UDP Socket on port $UDP_PORT".$lb;
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) echo 'Could not set option SO_REUSEADDR to socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
socket_bind($sock, 0, $UDP_PORT) or die('Could not bind to address');
if ($output==0) echo "$lb SILENT MODE !$lb";

while(true)
{
  $data = socket_read($sock, 1024);
  
  $errorcode = socket_last_error();
  if ($errorcode>0)
  {
    $errormsg = socket_strerror($errorcode);
    echo "Fehler ".$errorCode.": ".$errormsg."<br>";
  }

  if ($output==1) echo $line;
  if ($output==1) echo date("H:i:s")." Received ".strlen($data)." bytes".$lb;

  unset($datagramm);
  for ($i = 0; $i < strlen($data); $i++)
  {
    $datagramm[$i]=ord($data[$i]);
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

    checkServerFunction($receiver, $sender, $functionData);
    
    flush();
    ob_flush();
  }
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
  $ethernetClassesId=getClassIdByName("Ethernet");

  $result="1,$pcServerClassesId;1,$ethernetClassesId";
  for ($i=1;$i<=50;$i++)
  {
    $result.=";$i,$tasterClassesId";
  }

  $paramData["objectList"]=$result;
  $mySender = $SERVER_OBJECT_ID;

  callInstanceMethodForObjectId($sender, $GET_REMOTE_OBJECTS_RESULT_INDEX, $paramData, $mySender);
  if ($output==1) echo "Server: ".$debug."<br>";
}

function checkServerFunction($receiver, $sender, $functionData)
{
  global $EXECUTOR_OBJECT_ID;
  global $output;
  global $SERVER_OBJECT_ID, $BROADCAST_OBJECT_ID;

  superCheckServerFunction($receiver, $sender, $functionData);
}
?>