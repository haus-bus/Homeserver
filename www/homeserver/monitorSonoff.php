<?php

if ($_SERVER["DOCUMENT_ROOT"]=="") $_SERVER["DOCUMENT_ROOT"]="../";
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

error_reporting(7125);

$commandId = $argv[1];
$receiver = $argv[2];
$command = $argv[3];
$param = $argv[4];

if ($command=="on")
{
	$event="evOn";
	$paramArray["duration"]=$param;
}
else if ($command=="off") $event="evOff";


$result = waitForObjectEventByName($receiver, 1, $event, $commandId,"funtionDataParams", 0);
file_put_contents("sonoff.mon",$event." -> ".$result."\n", FILE_APPEND);

if ($result==-1)
{
	$last = filemtime("sonoff.fail");
  if (time()-$last<8)
  {
  	file_put_contents("sonoff.mon",$event." -> skip\n", FILE_APPEND);
  	exit;
  }

  executeCommand($receiver, $command, $paramArray, $event);
	file_put_contents("sonoff.fail",time());
}



?>