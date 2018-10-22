<?php
// Register Globals FIX
foreach (array('_GET', '_POST', '_COOKIE', '_SERVER') as $_SG) 
{
    foreach ($$_SG as $_SGK => $_SGV) 
    {
        $$_SGK = $_SGV;
    }
}

$scriptStart = microtime(TRUE);
$debugTime=0;

debugScript("started");

include_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/dbconnect.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/global.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/config.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/templates.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/tree.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/communication.php");

function debugScript($message, $exit=0)
{
  global $scriptStart;
  global $debugTime;
  
  if ($debugTime!=1) return;
  
  $scriptDuration = (microtime(TRUE)-$scriptStart)*1000;

  echo $scriptDuration."ms : ".$message."<br>";

  if ($exit==1)
  {
  	 die("exit");
  	 exit;
  }
}
?>