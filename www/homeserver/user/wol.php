<?php
if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../../";
error_reporting(E_ALL);
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
//00-25-22-A5-74-48
$data["mac5"] = 0;
$data["mac4"] = 37;
$data["mac3"] = 34;
$data["mac2"] = 165;
$data["mac1"] = 116;
$data["mac0"] = 72;
callObjectMethodByName("1681760769", "wakeUpDevice",$data);

exit;
	$addr_byte[0] =  0;
	$addr_byte[1] =  25;
	$addr_byte[2] =  22;
	$addr_byte[3] =  0xa5;
	$addr_byte[4] =  74;
	$addr_byte[5] =  48;
	
  $hw_addr = '';
  for ($a=0; $a < 6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));

  $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);

  for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;

  $fp = fsockopen("udp://255.255.255.255", 9, $errno, $errstr);
  fwrite($fp, $msg, strlen($msg));
  
  echo "A:".$errno." - ".$errstr;

?>