<?php
$output=$argv[1];

$check = shell_exec("runlevel");
if (strpos($check,"0")!==FALSE) die("Runlevel 0 (shutdown) erkannt");
if (strpos($check,"6")!==FALSE) die("Runlevel 6 (reboot) erkannt");

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";
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
  	  
      $url = 'http://192.168.178.80/test';
      $postData["data"]=$data;

      $options = array(
        'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'timeout'  => '10',
        'content' => http_build_query($postData)
        )
      );
      $context  = stream_context_create($options);
      $result = file_get_contents($url, false, $context);
      if ($result === FALSE)  echo "error";
      var_dump($result);
  }
}
?>