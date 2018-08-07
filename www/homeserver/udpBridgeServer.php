<?php
error_reporting((E_ERROR | E_WARNING | E_PARSE) &~E_NOTICE);

$udpOutput=$argv[1];
$tcpOutput=$argv[2];
$bridgeOutput=$argv[3];

$TCP_PORT=9500;
$MAX_CLIENTS=10;

$check = shell_exec("runlevel");
if (strpos($check,"0")!==FALSE) die("Runlevel 0 (shutdown) erkannt");
if (strpos($check,"6")!==FALSE) die("Runlevel 6 (reboot) erkannt");

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";
$waitForDb=1;
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/userPlugin.php");

$announceServerFunctionId = getFunctionsIdByNameForClassName("TcpClient", "announceServer");

$ifConfig = shell_exec('/sbin/ifconfig eth0');
$pos = strpos($ifConfig,"inet");
$pos = strpos($ifConfig," ",$pos);
$pos2 = strpos($ifConfig," ",$pos+1);
$address = substr($ifConfig,$pos+1,$pos2-$pos-1);
$addressParts = explode(".", $address);

$networkIp = getNetworkIp();

$lb="\n";
$line="--------------------------------------------------------".$lb;

set_time_limit(0);
ob_implicit_flush();

logMe("Opening UDP Socket on port $UDP_PORT"); 
if (($udpSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) die("UDP socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()));
if (!socket_set_option($udpSocket, SOL_SOCKET, SO_REUSEADDR, 1)) die("UDP Could not set option SO_REUSEADDR to socket: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (!socket_set_option($udpSocket, 1, 6, TRUE)) die("UDP Could not set broadcast option to socket: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (!socket_set_nonblock($udpSocket)) die("UDP Could not set socket to non blocking mode: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (socket_bind($udpSocket, 0, $UDP_PORT) === false) traceError("UDP socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
logMe( "UDP server ready ");
if ($udpOutput==0) logMe( "in SILENT MODE ! \n");
else logMe( "in VERBOSE MODE ! \n");

logMe( "Opening TCP Socket on port $TCP_PORT and ip $address \n");
if (($tcpSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) traceError("TCP socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()));
if (!socket_set_option($tcpSocket, SOL_SOCKET, SO_REUSEADDR, 1)) traceError("TCP Kann SO_REUSEADDR nicht setzen für Socket: ". socket_strerror(socket_last_error()));
if (!socket_set_option($tcpSocket, SOL_SOCKET, SO_REUSEPORT, 1)) traceError("TCP Kann SO_REUSEPORT nicht setzen für Socket: ". socket_strerror(socket_last_error()));
if (!socket_set_nonblock($tcpSocket)) die("UDP Could not set socket to non blocking mode: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (socket_bind($tcpSocket, $address, $TCP_PORT) === false) traceError("TCP socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
if (socket_listen($tcpSocket, 5) === false) traceError ("TCP socket_listen() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
logMe( "TCP server ready ");
if ($tcpOutput==0) logMe( "in SILENT MODE ! \n");
else logMe( "in VERBOSE MODE ! \n");

ob_implicit_flush(true);

// Clients hält die aktiven Sockets
for ($i=0;$i<$MAX_CLIENTS;$i++)
{
	$newClient = new stdClass();
	$newClient->active=false;
	$newClient->ip="";
	$newClient->socket="";
	$newClient->index=$i;
	$newClient->deviceId=-1;
	$clients[$i] = $newClient;
}

// Mainloop
while(true)
{
	if (checkAnySocketHasChanged())
	{
	  checkForUdpData();
	  checkForTcpClients();
	  checkForTcpData();
  }
}

function checkForTcpData()
{
	global $clients;
	
	$allClientsArray = array();
	$nrActiveClients=0;
	foreach ($clients as $actClient)
  {
    if ($actClient->active==true) $allClientsArray[$nrActiveClients++]=$actClient->socket;
  }
  
  if ($nrActiveClients>0)
  {
  	$null = NULL;
  	$num_changed_sockets = socket_select($allClientsArray, $null, $null, 0);
    if ($num_changed_sockets === false) logMe("TCP client socket_select hat Fehler gemeldet: ". socket_strerror(socket_last_error()));
    else if ($num_changed_sockets > 0)
    {
    	foreach($allClientsArray as $actChangedSocket)
    	{
    		 $found=false;
    		 foreach ($clients as $actClient)
         {
           if ($actClient->active==true && $actClient->socket == $actChangedSocket)
           {
           	 $found=true;
           	 handleTcpClient($actClient);
           	 break;
           }
         }
         if (!found) logMe( "Client socket nicht gefunden \n");
    	}
    }
  }
}

function handleTcpClient($client)
{
	 global $networkIp;
	 global $UDP_PORT;
	 global $udpSocket;
	 global $clients;
	 global $bridgeOutput;
	 
	 $mySocket=$client->socket;
	 $myIp = $client->ip;
	 $myIndex = $client->index;
	 
	 $data = @socket_read($mySocket, 1024);
   if ($data===false)
   {
   	 if ($bridgeOutput==1) logMe("Neue Daten von TCP Client $myIndex ($myIp): Keine Daten empfangen ! \n");
   }
   else if ($data!='')
   {
   	  $nrBytes = strlen($data);
   	  if ($bridgeOutput==1)
   	  {
   	  	  logMe("$nrBytes Bytes von von TCP Client $myIndex ($myIp): ");
   	      for ($i=0;$i<$nrBytes;$i++)
     	    {
   	  	    logMe( ord($data[$i])." ",false);
   	      }
   	      logMe( " \n", false);
   	  }
   	  
   	  if ($client->deviceId==-1)
   	  {
   	  	$client->deviceId=ord($data[0])+256*ord($data[1]);
   	  	logMe("DeviceID vom TCP Client $myIndex ($myIp): ".$client->deviceId."\n");
   	  	
   	  	// Slot mit gleicher DeviceId platt machen
        foreach ($clients as $actClient)
        {
        	if ($actClient->active==true && $actClient->index!=$myIndex && $actClient->deviceId==$client->deviceId)
      	  {
        		logMe( "Beende Slot mit gleicher DeviceId  ".$actClient->index."\n");
        		closeClient($actClient);
            break;
          }
        }
   	  }
   	  else
   	  {
   	  	if ($bridgeOutput==1) logMe( "Sende weiter and $networkIp und port $UDP_PORT \n");
        $send = socket_sendto($udpSocket, $data, $nrBytes, 0, $networkIp, $UDP_PORT);
        if ($send==-1) logMe( "Fehler beim Senden auf UDP Socket". socket_strerror(socket_last_error())."\n");
        else if ($send==-1) logMe( "Fehler beim Senden auf UDP Socket". socket_strerror(socket_last_error())."\n");
   	  }
   }
   else
   {
   	 logMe( "Client ".$client->index." ".$client->ip." disconnected \n");
   	 closeClient($client);
   }
}

function checkAnySocketHasChanged()
{
	global $udpSocket, $tcpSocket;
	global $clients;
  
	$null = NULL;
	$allSocketsArray[]=$udpSocket;
	$allSocketsArray[]=$tcpSocket;
	foreach ($clients as $actClient)
  {
    if ($actClient->active==true) $allSocketsArray[]=$actClient->socket;
  }
	
	$num_changed_sockets = socket_select($allSocketsArray, $null, $null, $null);
  if ($num_changed_sockets === false) logMe( "TCP socket_select hat Fehler gemeldet: ". socket_strerror(socket_last_error())."\n");
  else if ($num_changed_sockets > 0) return TRUE;
  return FALSE;
}

function checkForTcpClients()
{
	global $clients;
	global $tcpSocket;
	
	$null = NULL;
	$socketArray=array($tcpSocket);
	$num_changed_sockets = socket_select($socketArray, $null, $null, 0);
  if ($num_changed_sockets === false) logMe( "TCP socket_select hat Fehler gemeldet: ". socket_strerror(socket_last_error())."\n");
  else if ($num_changed_sockets > 0)
  {
	  $newClient = @socket_accept($tcpSocket);
    if ($newClient != FALSE)
    {
    	if (!socket_set_nonblock($newClient)) die("TCP Client Could not set socket to non blocking mode: ". socket_strerror(socket_last_error()) . PHP_EOL);
    	
   	  $clientIp="";
      $result = socket_getpeername ( $newClient , $clientIp );
      logMe( "Neuer Client von $clientIp \n");
      
      // Slot mit gleicher IP platt machen
      foreach ($clients as $actClient)
      {
      	if ($actClient->active==true && $actClient->ip==$clientIp)
      	{
      		logMe( "Beende Slot mit gleicher IP  ".$actClient->index."\n");
      		closeClient($actClient);
          break;
        }
      }
      
      // Neuen Slot suchen
      $found=false;
      foreach ($clients as $actClient)
      {
      	if ($actClient->active==false)
      	{
      		$found=true;
      		logMe( "Benutze Slot ".$actClient->index."\n");
          $actClient->active = true;
          $actClient->socket = $newClient;
          $actClient->ip = $clientIp;
          break;
        }
      }
      
      if (!found)
      {
      	logMe( "Fehler: Kein Clientslot mehr frei \n");
      	socket_close($newClient);
      }
    }
    else logMe( "Kein Client trotz changed \n");
  }
}


function checkForUdpData()
{
	global $udpSocket;
	
	$null = NULL;
	$socketArray = array($udpSocket);
	$num_changed_sockets = socket_select($socketArray, $null, $null, 0);
  if ($num_changed_sockets === false) logMe( "UDP socket_select hat Fehler gemeldet: ". socket_strerror(socket_last_error())."\n");
  else if ($num_changed_sockets > 0)
  {
    $data = @socket_read($udpSocket, 1024);
    if ($data!=false && $data!='') handleUdpData($data);
    else logMe(" keine daten $lb");
  }
}

function handleUdpData($data)
{
	global $udpOutput;
	global $lb;
	global $line;
	global $announceServerFunctionId;
	global $UDP_HEADER_BYTES;
	global $addressParts;
	global $clients;
	global $TCP_PORT;
	global $debug;

  if ($udpOutput==1)
  {
  	//logMe( $line);
    logMe("Received ".strlen($data)." bytes".$lb);
  }
  
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

  if (!$headerOk)
  {
  	 if ($udpOutput==1) logMe( "Header nicht von uns".$lb);
  }
  else
  {
    // Kontroll-Byte
    $dataPos++;

    // Nachrichtenzähler
    $messageCounter = $datagramm[$dataPos++];

    // Sender-ID
    $sender = bytesToDword($datagramm,$dataPos);
    
    if ($udpOutput==1) logMe( "Sender: $sender, ClassId Sender: ".getClassId($sender).$lb);
    $senderSubscriberData = getBusSubscriberData($sender);

    // Empfänger-ID
    $receiver = bytesToDword($datagramm,$dataPos);
    if ($udpOutput==1) logMe( "Receiver: $receiver, ClassId Receiver: ".getClassId($receiver).$lb);
    $receiverSubscriberData = getBusSubscriberData($receiver);

    // Nutzdaten
    $length = bytesToWord($datagramm, $dataPos);
    if ($udpOutput==1) logMe( "Datenlänge: ".$length.$lb);
    
    $functionId = $datagramm[$dataPos++];
    if ($udpOutput==1) logMe( "Function ID: ".$functionId.$lb);

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
    if ($udpOutput==1)
    {
    	logMe( "Nachrichtenzähler: $messageCounter".$lb);
      logMe( "Nachrichtentyp: $messageType".$lb);
      logMe( $lb);
      logMe( "Sender: ".$senderSubscriberData->debugStr.$lb);
      logMe( "Empfänger: ".$receiverSubscriberData->debugStr.$lb);
      logMe( $lb);
      logMe( "Datenlänge: ".$length.$lb);
      logMe( "Funktion: ".$functionData->functionDebugStr.$lb);
      logMe( "Parameter: ".$functionData->paramsDebugStr.$lb);
    }
    
    if ($functionData->classId == 91 && $functionData->functionId == 200) // evWhoIsServer
    {
    	logMe( "Got who is server from $sender ".$lb);
    	
    	//exec("php udpBridgeClientHandler.php > /dev/null 2>/dev/null &");
    	 
    	$paramData["IP0"]=$addressParts[0];
      $paramData["IP1"]=$addressParts[1];
      $paramData["IP2"]=$addressParts[2];
      $paramData["IP3"]=$addressParts[3];
      $paramData["port"]=$TCP_PORT;
      callInstanceMethodForObjectId($sender, $announceServerFunctionId, $paramData);
    }
    else
    {
    	  $receiverDeviceId=getDeviceId($receiver);
    	  
    	  foreach($clients as $actClient)
    	  {
    	  	 if ($actClient->active==false) continue;
    	  	
    	  	 if ($receiver==0 || $receiverDeviceId == $actClient->deviceId) sendUdpDataToClient($actClient, $data);
    	  }
    }

    flush();
    ob_flush();
  }
}

function sendUdpDataToClient($client, $data)
{
	 global $bridgeOutput;
	 
	 $mySocket=$client->socket;
	 $myIp = $client->ip;
	 $myIndex = $client->index;

   if ($bridgeOutput==1) logMe( "Schicke ".strlen($data)." an $myIp :");
   
	 $write = socket_write($mySocket, $data, strlen($data));
	 
	 if ($bridgeOutput==1) logMe( $write."\n");
	 
	 if ($write===false || $write===0)
	 {
	 	 logMe( "Send an TCP Client $myIndex ($myIp) fehlgeschlagen: ".socket_strerror(socket_last_error())."\n");
	 	 closeClient($client);
	 }
}

function closeClient($client)
{
	  $client->active=false;
	  $client->deviceId=-1;
	  $client->ip="";
	  socket_close($client->socket);
}

function logMe($message, $withTimeStamp=true)
{
	 if ($withTimeStamp) echo date("d.m.y H:i:s").": ";
	 echo $message;
}


?>
