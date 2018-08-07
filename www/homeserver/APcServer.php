<?php

$SERVER_OBJECT_ID = getObjectId($PC_SERVER_DEVICE_ID, $CONTROLLER_CLASS_ID, 1);
$EXECUTOR_OBJECT_ID = getObjectId($PC_SERVER_DEVICE_ID, getClassIdByName("PC-Server"), 1);
$ETHERNET_OBJECT_ID = getObjectId($PC_SERVER_DEVICE_ID, getClassIdByName("Ethernet"), 1);

$getModuleIdFunctionId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "getModuleId");
$controllerModuleIdFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "ModuleId");
$getConfigurationFunctionId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "getConfiguration");
$controllerConfigurationFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "Configuration");
$getRemoteObjectsFunctionId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "getRemoteObjects");
$controllerRemoteObjectsFktId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "RemoteObjects");
$pingFunctionId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "ping");
$writeRulesFunctionId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "writeRules");
$resetFunctionId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "reset");
$execFunctionId = getObjectFunctionIdByName($EXECUTOR_OBJECT_ID, "exec");
$setVariableFunctionId = getObjectFunctionIdByName($EXECUTOR_OBJECT_ID, "setVariable");
$standbyFunctionId = getObjectFunctionIdByName($EXECUTOR_OBJECT_ID, "standby");
$shutdownFunctionId = getObjectFunctionIdByName($EXECUTOR_OBJECT_ID, "shutdown");
$restartFunctionId = getObjectFunctionIdByName($EXECUTOR_OBJECT_ID, "restart");
$quitFunctionId = getObjectFunctionIdByName($EXECUTOR_OBJECT_ID, "quit");
$evOfflineFunctionId = getObjectFunctionsIdByName($EXECUTOR_OBJECT_ID, "evOffline");
$evOnlineFunctionId = getObjectFunctionsIdByName($EXECUTOR_OBJECT_ID, "evOnline");

$schalterClassId=getClassIdByName("Schalter");
$schalterOnFunctionId = getFunctionIdByNameForClassName("Schalter", "on");
$schalterOffFunctionId = getFunctionIdByNameForClassName("Schalter", "off");
$schalterGetStatusFunctionId = getFunctionIdByNameForClassName("Schalter", "getStatus");
$schalterEvOnFunctionId = getFunctionsIdByNameForClassName("Schalter", "evOn");
$schalterEvOffFunctionId = getFunctionsIdByNameForClassName("Schalter", "evOff");
$schalterStatusFunctionId = getFunctionsIdByNameForClassName("Schalter", "Status");

$ethernetGetConfigurationFunctionId = getObjectFunctionIdByName($ETHERNET_OBJECT_ID, "getConfiguration");
$ethernetWakeUpDeviceFunctionId = getObjectFunctionIdByName($ETHERNET_OBJECT_ID, "wakeUpDevice");

sendOnlineEvent();

function superCheckServerFunction($receiver, $sender, $functionData)
{
  global $EXECUTOR_OBJECT_ID;
  global $getModuleIdFunctionId;
  global $getConfigurationFunctionId;
  global $getRemoteObjectsFunctionId;
  global $pingFunctionId;
  global $writeRulesFunctionId;
  global $resetFunctionId;
  global $getConfigurationFunctionId;
  global $execFunctionId;
  global $setUnitGroupStateFunctionId;
  global $setVariableFunctionId;
  global $getWeatherFunctionId;
  global $output;
  global $ETHERNET_OBJECT_ID;
  global $ethernetGetConfigurationFunctionId;
  global $ethernetWakeUpDeviceFunctionId;
  global $SERVER_OBJECT_ID, $BROADCAST_OBJECT_ID;
  global $standbyFunctionId,$shutdownFunctionId,$restartFunctionId,$quitFunctionId;
  global $schalterOnFunctionId,$schalterOffFunctionId,$schalterGetStatusFunctionId,$schalterClassId;

  if ($receiver==$EXECUTOR_OBJECT_ID)
  {
    if ($functionData->functionId == $execFunctionId) executeSystemCommand($functionData);
    else if ($functionData->functionId == $standbyFunctionId) execStandby();
    else if ($functionData->functionId == $shutdownFunctionId) execShutdown();
    else if ($functionData->functionId == $restartFunctionId) execRestart();
    else if ($functionData->functionId == $quitFunctionId) exitServer();
    else if ($functionData->functionId == $setVariableFunctionId) setVariable($functionData);
  }
  else if ($receiver==$ETHERNET_OBJECT_ID)
  {
    if ($functionData->functionId == $ethernetGetConfigurationFunctionId) ethernetGetConfiguration($sender);
    else if ($functionData->functionId == $ethernetWakeUpDeviceFunctionId) ethernetWakeUpDevice($functionData);
  }
  else if ($receiver == $SERVER_OBJECT_ID || $receiver == $BROADCAST_OBJECT_ID)
  {
    if ($functionData->functionId == $getModuleIdFunctionId) sendServerModuleId($sender);
    else if ($functionData->functionId == $getConfigurationFunctionId) sendServerConfiguration($sender);
    else if ($functionData->functionId == $getRemoteObjectsFunctionId) sendServerRemoteObjects($sender);
    else if ($functionData->functionId == $pingFunctionId) sendServerPong($sender);
    else if ($functionData->functionId == $writeRulesFunctionId) sendServerMemoryStatus($sender);
    else if ($functionData->functionId == $resetFunctionId) resetServer($sender);
  }
  else if (getClassId($receiver) == $schalterClassId && getDeviceId($receiver)==$PC_SERVER_DEVICE_ID)
  {
    if ($functionData->functionId == $schalterOnFunctionId || $functionData->functionId == $schalterOffFunctionId) sendSchalterEvent($receiver, $functionData);
    else if ($functionData->functionId == $schalterGetStatusFunctionId) sendSchalterStatus($receiver, $sender);
  }
}

function sendSchalterStatus($receiver, $sender)
{
	global $schalterStatus;
	global $schalterStatusFunctionId;
	
	if ($schalterStatus[$receiver]==1) $paramData["state"]=1;
	else $paramData["state"]=0;
	
  callInstanceMethodForObjectId($sender, $schalterStatusFunctionId, $paramData, $receiver);
}

function sendSchalterEvent($receiver, $functionData)
{
	global $schalterOnFunctionId,$schalterOffFunctionId,$schalterEvOnFunctionId,$schalterEvOffFunctionId;
	global $schalterStatus;
	
	if ($functionData->functionId == $schalterOnFunctionId)
	{
		callInstanceMethodForObjectId($receiver, $schalterEvOnFunctionId);
		$schalterStatus[$receiver]=1;
	}
	else
	{
		callInstanceMethodForObjectId($receiver, $schalterEvOffFunctionId);
		$schalterStatus[$receiver]=0;
	}
}

function sendOnlineEvent()
{
  global $EXECUTOR_OBJECT_ID;
  global $evOnlineFunctionId;
  if ($evOfflineFunctionId>0) callInstanceMethodForObjectId($EXECUTOR_OBJECT_ID, $evOnlineFunctionId);
}


function exitServer()
{
  global $evOfflineFunctionId;
  global $EXECUTOR_OBJECT_ID;

  callInstanceMethodForObjectId($EXECUTOR_OBJECT_ID, $evOfflineFunctionId);
  
  if (file_exists($_SERVER["DOCUMENT_ROOT"]."/homeserver/my.cnf"))
  {
  	$erg = shell_exec("diff /etc/mysql/my.cnf ".$_SERVER["DOCUMENT_ROOT"]."/homeserver/my.cnf");
    if (strlen($erg)>2)
    {
    	exec("cp ".$_SERVER["DOCUMENT_ROOT"]."/homeserver/my.cnf /etc/mysql/");
  	  exec("/etc/init.d/mysql restart");
  	}
 	  exec("rm ".$_SERVER["DOCUMENT_ROOT"]."/homeserver/my.cnf");
  }

  if (file_exists($_SERVER["DOCUMENT_ROOT"]."/homeserver/jpgraph")) exec("rm -R ".$_SERVER["DOCUMENT_ROOT"]."/homeserver/jpgraph");
  checkUpdatePhpMyAdminRights();

  die("exiting...");
}

function checkUpdatePhpMyAdminRights()
{
	 $file = "/etc/phpmyadmin/apache.conf";
	 if (file_exists($file))
	 {
	   $text = file_get_contents($file);
	   if (strpos($text,"allow from 127.0.0.1")===FALSE)
	   {
	   	  $pos = strpos($text, "<Directory /usr/share/phpmyadmin>");
	   	  if ($pos!==FALSE) $pos = strpos($text, "</Directory>",$pos);
	   	  if ($pos!==FALSE)
	   	  {
	   	  	 $text = substr($text,0,$pos)."order deny,allow\ndeny from all\nallow from 127.0.0.1\nallow from 192.\n".substr($text,$pos);
	   	  	 file_put_contents($file, $text);
	   	  	 return;
	   	  }
	   }
	 }
}

function execStandby()
{
  global $evOfflineFunctionId;
  global $EXECUTOR_OBJECT_ID;

  callInstanceMethodForObjectId($EXECUTOR_OBJECT_ID, $evOfflineFunctionId);

  exec("shutdown /h");
}

function execShutdown()
{
  global $evOfflineFunctionId;
  global $EXECUTOR_OBJECT_ID;

  callInstanceMethodForObjectId($EXECUTOR_OBJECT_ID, $evOfflineFunctionId);

  exec("shutdown /s /t 0");
}

function execRestart()
{
  global $evOfflineFunctionId;
  global $EXECUTOR_OBJECT_ID;

  callInstanceMethodForObjectId($EXECUTOR_OBJECT_ID, $evOfflineFunctionId);

  exec("shutdown /r");
}

function executeSystemCommand($functionData)
{
  global $pcServerGroupId;
  global $serverVariables;
  global $output;
  global $EXECUTOR_OBJECT_ID;

  foreach((array)$functionData->paramData as $id3=>$obj3)
  {
    $nr = $obj3->dataValue;
    if ($nr==-1) restoreHomeserver();
    else if ($nr==-2) createSnapshot();
    else
    {
   	  if ($pcServerGroupId>0) {}
      else
      {
        $erg = QUERY("select groups.id from groups join groupFeatures on (groupFeatures.groupId=groups.id) join featureInstances on (featureInstances.id = groupFeatures.featureInstanceId) where featureInstances.objectId='$EXECUTOR_OBJECT_ID' limit 1");
        if ($row=mysqli_fetch_ROW($erg)) $pcServerGroupId=$row[0];
        else
        {
  	       echo "Gruppe vom PC-Server nicht gefunden!\n";
  	       return;
        }
      }

      $erg=QUERY("select ruleActionParams.id,ruleActionParams.paramValue from ruleActionParams join ruleActions on
(ruleActions.id=ruleActionParams.ruleActionId) join rules on (rules.id = ruleActions.ruleId) where groupId='$pcServerGroupId' order by ruleActionParams.id
limit $nr,1");
      $row=mysqli_fetch_ROW($erg);
      if ($output==1) echo "exec: ".$row[1]." - ".$row[0]."\n";

      //$cmd = 'nohup nice -n 10 /usr/bin/php -c /path/to/php.ini -f /path/to/php/file.php action=generate var1_id=23 var2_id=35 gen_id=535 >>
	  //path/to/log/file.log';
      //$pid = shell_exec($cmd);
 
      if (strpos($row[1],"etherwake")!==FALSE) exec($row[1]);
      else
      {
      	if ($output==1) echo $row[1]." > /dev/null 2>/dev/null & \n";
      	exec($row[1]." > /dev/null 2>/dev/null &");
      }
      //else pclose(popen("wget -q --delete-after -b -o wgetout.txt http://localhost/homeserver/executor.php?id=".$row[0], "r"));
      if ($output==1) echo "exec ende \n";
    }
  }
}

function restoreHomeserver()
{
	echo "restoring homeserver \n";
	
	exec("/var/www/homeserver/homeserverRestore.sh");
	
	echo "restoring finished... restarting \n";
	exit;
}

function createSnapshot()
{
	echo "creating snapshot \n";
	
	exec("/var/www/homeserver/homeserverBackup.sh 1");
	
	echo "snapshot finished...\n";
}

function setVariable($functionData)
{
  global $serverVariables;
  global $output;
  global $PC_SERVER_DEVICE_ID;

  $paramData = $functionData->paramData;

  $name =  $paramData[0]->dataValue;
  $value =  $paramData[1]->dataValue;

  QUERY("delete from serverVariables where name='$name' and instance='$PC_SERVER_DEVICE_ID' limit 1");
  QUERY("INSERT into serverVariables (name,value,instance) values ('$name','$value','$PC_SERVER_DEVICE_ID')");

  traceToJournal("$name => $value","setVariable");

  if ($output==1) echo "---> Variable $name = $value \n";
}

function ethernetWakeUpDevice($functionData)
{
  global $UDP_PORT;
  global $UDP_BCAST_IP;

  $paramData = $functionData->paramData;

  $addr_byte[0] =  $paramData[0]->dataValue;
  $addr_byte[1] =  $paramData[1]->dataValue;
  $addr_byte[2] =  $paramData[2]->dataValue;
  $addr_byte[3] =  $paramData[3]->dataValue;
  $addr_byte[4] =  $paramData[4]->dataValue;
  $addr_byte[5] =  $paramData[5]->dataValue;

  $hw_addr = '';
  for ($a=0; $a < 6; $a++) $hw_addr .= chr($addr_byte[$a]);

  $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);

  for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;

  if (isWindows())
  {
    $fp = fsockopen("udp://" . $UDP_BCAST_IP, $UDP_PORT, $errno, $errstr);
    fwrite($fp, $msg, strlen($msg));
  }
  else
  {
    $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($s == false)
    {
      echo "Error creating socket!\n";
      echo "Error code is '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
    }
    else
    {
      // setting a broadcast option to socket:
      $opt_ret = socket_set_option($s, 1, 6, TRUE);
      if($opt_ret < 0)
      {
        echo "setsockopt() failed, error: " . strerror($opt_ret) . "\n";
      }
      $e = socket_sendto($s, $msg, strlen($msg), 0, $UDP_BCAST_IP, $UDP_PORT);
      socket_close($s);
    }
  }
}

function ethernetGetConfiguration($sender)
{
  global $ETHERNET_OBJECT_ID;

  global $debug;
  global $output;

  $GET_CONFIGURATION_RESULT_INDEX = getObjectFunctionsIdByName($ETHERNET_OBJECT_ID, "Configuration");

  $serverIp = getHostByName(getHostName());
  $parts = explode(".",$serverIp);

  $paramData["IP3"]=$parts[0];
  $paramData["IP2"]=$parts[1];
  $paramData["IP1"]=$parts[2];
  $paramData["IP0"]=$parts[3];

  callInstanceMethodForObjectId($sender, $GET_CONFIGURATION_RESULT_INDEX, $paramData, $ETHERNET_OBJECT_ID);
  if ($output==1) echo "Server: ".$debug."<br>";
}

function resetServer($sender)
{
  echo "Got Reset\n";
}

function sendServerMemoryStatus($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SERVER_OBJECT_ID;
  global $output;

  $mySender = $SERVER_OBJECT_ID;
  $GET_MEMORY_STATUS_RESULT_INDEX = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "MemoryStatus");
  callInstanceMethodForObjectId($sender, $GET_MEMORY_STATUS_RESULT_INDEX, $paramData, $mySender);
  if ($output==1) echo "Server: ".$debug."<br>";
}

function sendServerModuleId($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SERVER_OBJECT_ID;
  global $SERVER_CLASS_ID;
  global $debug;
  global $output;

  $GET_MODULE_ID_RESULT_INDEX = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "ModuleId");

  $paramData["name"]="Server";
  $paramData["size"]="999";
  $paramData["majorRelease"]="1";
  $paramData["minorRelease"]="0";
  $paramData["firmwareId"]="1";

  $mySender = $SERVER_OBJECT_ID;

  callInstanceMethodForObjectId($sender, $GET_MODULE_ID_RESULT_INDEX, $paramData, $mySender);
  if ($output==1) echo "Server: ".$debug."<br>";
}

function sendServerConfiguration($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SERVER_OBJECT_ID;
  global $SERVER_CLASS_ID;
  global $debug;
  global $output;

  $GET_CONFIGURATION_RESULT_INDEX = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "Configuration");

  $paramData["dataBlockSize"]="775";

  $mySender = $SERVER_OBJECT_ID;
  callInstanceMethodForObjectId($sender, $GET_CONFIGURATION_RESULT_INDEX, $paramData, $mySender);
  if ($output==1) echo "Server: ".$debug."<br>";
}

function sendServerPong($sender)
{
  global $SERVER_OBJECT_ID;
  global $SERVER_CLASS_ID;
  global $debug;
  global $output;

  $mySender = $SERVER_OBJECT_ID;

  callObjectMethodByName($sender, "pong", $paramData, $mySender);
  if ($output==1) echo "Server: ".$debug."<br>";
}

function traceToJournal($message, $functionStr="")
{
  $time=time();
  $messageType="FUNCTION";
  $messageCounter="0";
  $senderSubscriberDataDebugStr="PC-Server";
  $receiverSubscriberDataDebugStr="PC-Server";
  $paramsStr=query_real_escape_string($message);

  QUERY("INSERT into udpCommandLog (time, type, messageCounter,  sender,  receiver,  function,  params, functionData, senderSubscriberData,
receiverSubscriberData, udpDataLogId,senderObj,fktId)

values('$time','$messageType','$messageCounter','$senderSubscriberDataDebugStr','$receiverSubscriberDataDebugStr','$functionStr','$paramsStr','$functionData'
, '$senderSubscriberData','$receiverSubscriberData','$udpDataLogId','$senderObj','$fktId')");
}
?>