<?php

$bridgeIp="192.168.178.34";
$data="1620000270.LED.2.10";


error_reporting(E_ERROR | E_WARNING | E_PARSE);

$ifConfig = shell_exec('/sbin/ifconfig eth0');
$pos = strpos($ifConfig,"inet Adr");
$pos = strpos($ifConfig,":",$pos);
$pos2 = strpos($ifConfig," ",$pos);
$address = substr($ifConfig,$pos+1,$pos2-$pos-1);
$addressParts = explode(".", $address);

$lb="\n";
set_time_limit(0);
ob_implicit_flush();

$UDP_PORT = 15557;

$repeatDone=false;

echo "Opening UDP Socket on port $UDP_PORT".$lb; 
if (($udpSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) die("UDP socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()));
if (!socket_set_option($udpSocket, SOL_SOCKET, SO_REUSEADDR, 1)) die("UDP Could not set option SO_REUSEADDR to socket: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (!socket_set_option($udpSocket, 1, 6, TRUE)) die("UDP Could not set broadcast option to socket: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (!socket_set_nonblock($udpSocket)) die("UDP Could not set socket to non blocking mode: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (socket_bind($udpSocket, 0, $UDP_PORT) === false) traceError("UDP socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
echo "UDP server ready \n";


ob_implicit_flush(true);

echo microtime(true)." send\n";
socket_sendto($udpSocket, $data, strlen($data), 0, $bridgeIp, $UDP_PORT);
echo microtime(true)." send ready\n";
exit;
/*
if ($argv[1]==1)
{
	echo microtime(true)." send\n";
	$data="1234567890";
	socket_sendto($udpSocket, $data, 10, 0, "192.168.178.255", 15555);
	echo microtime(true)." send ready\n";
}
*/

// Mainloop
while(true)
{
	if (checkAnySocketHasChanged())
	{
	  checkForUdpData();
  }
}

function checkAnySocketHasChanged()
{
	global $udpSocket;
  
	$null = NULL;
	$allSocketsArray[]=$udpSocket;
	
	$num_changed_sockets = socket_select($allSocketsArray, $null, $null, $null);
  if ($num_changed_sockets === false) echo "TCP socket_select hat Fehler gemeldet: ". socket_strerror(socket_last_error());
  else if ($num_changed_sockets > 0) return TRUE;
  return FALSE;
}

function checkForUdpData()
{
	global $udpSocket;
	global $argv;
	global $address;
	global $start;
	global $UDP_PORT;
	
	$null = NULL;
	$socketArray = array($udpSocket);
	$num_changed_sockets = socket_select($socketArray, $null, $null, 0);
  if ($num_changed_sockets === false) echo "UDP socket_select hat Fehler gemeldet: ". socket_strerror(socket_last_error());
  else if ($num_changed_sockets > 0)
  {
  	$from = '';
  	$data="";
    socket_recvfrom($udpSocket, $data, 1024, 0, $from, $UDP_PORT);
    $len = strlen($data);
    echo $data."\n";
    
    /*
  	echo microtime(true)." receive $len \n";
  	
    
    if ($data!=false && $data!='' && $from!=$address)
    {
    	echo microtime(true)." receive ready $len \n";
  	  echo "duration = ".(time()-$start)." ms\n";
  	  $start=time();

      //if ($argv[1]!=1)
      {
    	  $len = strlen($data);
        $opt_ret = socket_set_option($udpSocket, 1, 6, TRUE);
        if($opt_ret < 0) echo "setsockopt() failed, error: " . strerror($opt_ret) . "\n";
        echo microtime(true)." send \n";
        socket_sendto($udpSocket, $data, $len, 0, "192.168.178.255", 15555);
        echo microtime(true)." send ready \n";
      }
    }
    else "echo keine daten $lb";
    */
  }
}

function closeClient($client)
{
	  $client->active=false;
	  $client->deviceId=-1;
	  $client->ip="";
	  socket_close($client->socket);
}
?>