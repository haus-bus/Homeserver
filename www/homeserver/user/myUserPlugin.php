<?php

if ($_GET["debug"]==1)
{
	include("../include/all.php");
}

/**
* Hier k�nnen eigene Implementierungen angelegt werden.
* Eine Beschreibung der Funktionen und aller Parameter befindet sich in der Klasse /homeserver/userPlugin.php
* Die Klasse userPlugin.php darf allerdings nicht ge�ndert werden, da diese bei jedem Update �berschrieben wird.
*/

function myEventOccured($senderData, $receiverData, $functionData)
{
	file_put_contents("test.txt","12345");
	/*if ($senderData->instanceName=="M-Taster Tina 6" && $functionData->name=="evHoldEnd")
  {
  	 sleep(1);
  	 executeCommand("1267602741", "off");
  	 executeCommand("65541429", "off");
  }*/
	//if ($senderData->instanceName=="Klatschschalter") executeCommand("541986852", "evClicked");
}

function myTimeTrigger()
{
}
?>