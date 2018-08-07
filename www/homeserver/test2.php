<?php
error_reporting(E_ALL);

echo "<h2>TCP/IP-Verbindung</h2>\n";

$service_port = 9500;

$ifConfig = shell_exec('/sbin/ifconfig eth0');
$pos = strpos($ifConfig,"inet Adr");
$pos = strpos($ifConfig,":",$pos);
$pos2 = strpos($ifConfig," ",$pos);
$address = substr($ifConfig,$pos+1,$pos2-$pos-1);

/* Einen TCP/IP-Socket erzeugen. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "OK.\n";
}

echo "Versuche, zu '$address' auf Port '$service_port' zu verbinden ...";
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() fehlgeschlagen.\nGrund: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "OK.\n";
}

$out = '';
$in="12345";

echo "HTTP HEAD request senden ...";
socket_write($socket, $in, strlen($in));
echo "OK.\n";

echo "Serverantwort lesen:\n\n";
while ($out = socket_read($socket, 2048)) 
{
    echo $out;
    
    socket_write($socket, $in, strlen($in));
}

echo "Socket schließen ...";
socket_close($socket);
echo "OK.\n\n";
?>
