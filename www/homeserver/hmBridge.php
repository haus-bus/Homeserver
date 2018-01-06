<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$check = shell_exec("runlevel");
if (strpos($check,"0")!==FALSE) die("Runlevel 0 (shutdown) erkannt");
if (strpos($check,"6")!==FALSE) die("Runlevel 6 (reboot) erkannt");

$ifConfig = shell_exec('/sbin/ifconfig eth0');
$pos = strpos($ifConfig,"inet Adr");
$pos = strpos($ifConfig,":",$pos);
$pos2 = strpos($ifConfig," ",$pos);
$address = substr($ifConfig,$pos+1,$pos2-$pos-1);
$addressParts = explode(".", $address);

$lb="\n";
$line="--------------------------------------------------------".$lb;

set_time_limit(0);
ob_implicit_flush();

$bidcosPort = 2000;
$keepalivePort = 2001;
$resetPort = 18;


if (($g_serverBidcosFd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) traceError("TCP1 socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()));
if (!socket_set_option($g_serverBidcosFd, SOL_SOCKET, SO_REUSEADDR, 1)) traceError("TCP1 Kann SO_REUSEADDR nicht setzen für Socket: ". socket_strerror(socket_last_error()));
if (!socket_set_nonblock($g_serverBidcosFd)) traceError("TCP1 Could not set socket to non blocking mode: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (socket_bind($g_serverBidcosFd, $address, $bidcosPort) === false) traceError("TCP1 socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
if (socket_listen($g_serverBidcosFd, 5) === false) traceError ("TCP1 socket_listen() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
logMe( "TCP1 server ready \n");

if (($g_serverKeepAliveFd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) traceError("TCP2 socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()));
if (!socket_set_option($g_serverKeepAliveFd, SOL_SOCKET, SO_REUSEADDR, 1)) traceError("TCP2 Kann SO_REUSEADDR nicht setzen für Socket: ". socket_strerror(socket_last_error()));
if (!socket_set_nonblock($g_serverKeepAliveFd)) traceError("TCP2 Could not set socket to non blocking mode: ". socket_strerror(socket_last_error()) . PHP_EOL);
if (socket_bind($g_serverKeepAliveFd, $address, $keepalivePort) === false) traceError("TCP2 socket_bind() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
if (socket_listen($g_serverKeepAliveFd, 5) === false) traceError ("TCP2 socket_listen() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error($sock)));
logMe( "TCP2 server ready \n");

ob_implicit_flush(true);

// Mainloop
while(true)
{
  checkForClients($g_serverBidcosFd, "CCU");
  checkForClients($g_serverKeepAliveFd, "KEEP ALIVE");
}


function checkForClients($socket, $name)
{
	$null = NULL;
	$socketArray=array($socket);
	$num_changed_sockets = socket_select($socketArray, $null, $null, 0);
  if ($num_changed_sockets === false) logMe( "$name socket_select hat Fehler gemeldet: ". socket_strerror(socket_last_error())."\n");
  else if ($num_changed_sockets > 0)
  {
	  $newClient = @socket_accept($tcpSocket);
    if ($newClient != FALSE) echo "Neuer Client auf $name! \n";
  }
}

function logMe($message, $withTimeStamp=true)
{
	 if ($withTimeStamp) echo date("d.m.y H:i:s").": ";
	 echo $message;
}

function traceError($message)
{
	 echo date("d.m.y H:i:s").": error: ";
	 echo $message;
}

?>