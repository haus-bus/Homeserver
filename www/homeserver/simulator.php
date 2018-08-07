<?php

$SIMULATOR_OBJECT_ID = getObjectId(12345, $CONTROLLER_CLASS_ID, 1);

$bootloaderActive=0;

function checkSimulatorFunction($sender, $functionData)
{
  global $BROADCAST_OBJECT_ID;
  if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "getModuleId")) sendModuleId($sender);
  else if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "getConfiguration")) sendConfiguration($sender);
  else if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "getRemoteObjects")) sendRemoteObjects($sender);
  else if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "ping")) sendPong($sender);
  else if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "writeMemory")) sendMemoryStatus($sender);
  else if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "writeRules")) sendMemoryStatus($sender);
  else if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "readMemory")) readMemory($sender,$functionData->paramData[0]->dataValue,$functionData->paramData[1]->dataValue);
  else if ($functionData->functionId == getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "reset")) resetMe($sender);
}

function resetMe($sender)
{
  global $bootloaderActive;

  if ($bootloaderActive==0) $bootloaderActive=1;
  else $bootloaderActive=0;
  echo "Got Reset -> bootloaderActive = $bootloaderActive \n";
}

function readMemory($sender, $offset, $length)
{
  global $BROADCAST_OBJECT_ID;	
  global $SIMULATOR_OBJECT_ID;
  global $bootloaderActive;
  global $debug;

  $READ_MEMORY_RESULT_INDEX = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "MemoryData");

  $fd = fopen ("C:/AR8_Bootloader.bin", "r");
  $ready=0;
  while (!feof ($fd))
  {
    $buffer = fread($fd, $length);

    if ($ready==$offset)
    {
      $paramData["address"]=$offset;
      $paramData["data"]=$buffer;

      if ($bootloaderActive==1) $mySender = getBootloaderObjectId($SIMULATOR_OBJECT_ID);
      else $mySender = $SIMULATOR_OBJECT_ID;

      callInstanceMethodForObjectId($sender, $READ_MEMORY_RESULT_INDEX, $paramData, $mySender);
      echo "Simulator: ".$debug."<br>";
    }
    $ready+=strlen($buffer);
  }
  fclose($fd);
}

function sendMemoryStatus($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SIMULATOR_OBJECT_ID;
  global $bootloaderActive;
  global $debug;

  if ($bootloaderActive==1) $mySender = getBootloaderObjectId($SIMULATOR_OBJECT_ID);
  else $mySender = $SIMULATOR_OBJECT_ID;

  $GET_MEMORY_STATUS_RESULT_INDEX = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "MemoryStatus");

  callInstanceMethodForObjectId($sender, $GET_MEMORY_STATUS_RESULT_INDEX, $paramData, $mySender);
  echo "Simulator: ".$debug."<br>";
}


function sendModuleId($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SIMULATOR_OBJECT_ID;
  global $bootloaderActive;
  global $debug;

  $GET_MODULE_ID_RESULT_INDEX = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "ModuleId");

  if ($bootloaderActive==1) $paramData["name"]="Simulator_Bootloader";
  else $paramData["name"]="Simulator";
  $paramData["size"]="999";
  $paramData["majorRelease"]="0";
  $paramData["minorRelease"]="1";
  $paramData["firmwareId"]="1";

  if ($bootloaderActive==1) $mySender = getBootloaderObjectId($SIMULATOR_OBJECT_ID);
  else $mySender = $SIMULATOR_OBJECT_ID;

  callInstanceMethodForObjectId($sender, $GET_MODULE_ID_RESULT_INDEX, $paramData, $mySender);
  echo "Simulator: ".$debug."<br>";
}

function sendConfiguration($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SIMULATOR_OBJECT_ID;
  global $bootloaderActive;
  global $debug;

  $GET_CONFIGURATION_RESULT_INDEX = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "Configuration");

  $paramData["dataBlockSize"]="775";

  if ($bootloaderActive==1) $mySender = getBootloaderObjectId($SIMULATOR_OBJECT_ID);
  else $mySender = $SIMULATOR_OBJECT_ID;
  callInstanceMethodForObjectId($sender, $GET_CONFIGURATION_RESULT_INDEX, $paramData, $mySender);
  echo "Simulator: ".$debug."<br>";
}

function sendRemoteObjects($sender)
{
  global $BROADCAST_OBJECT_ID;
  global $SIMULATOR_OBJECT_ID;
  global $CONTROLLER_CLASS_ID;
  global $bootloaderActive;
  global $debug;

  $GET_REMOTE_OBJECTS_RESULT_INDEX =  getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "RemoteObjects");

  $result="";
  if ($bootloaderActive==0)
  {
    $erg = QUERY("select classId from featureClasses where classId!='$CONTROLLER_CLASS_ID' order by id");
    while($obj=mysqli_fetch_OBJECT($erg))
    {
      if ($result!="") $result.=";";
      $result.="1,".$obj->classId;

      // zweiter dimmer
      if ($obj->classId==17)
      $result.=";2,".$obj->classId;
    }
  }

  $paramData["objectList"]=$result;

  if ($bootloaderActive==1) $mySender = getBootloaderObjectId($SIMULATOR_OBJECT_ID);
  else $mySender = $SIMULATOR_OBJECT_ID;

  callInstanceMethodForObjectId($sender, $GET_REMOTE_OBJECTS_RESULT_INDEX, $paramData, $mySender);
  echo "Simulator: ".$debug."<br>";
}

function sendPong($sender)
{
  global $SIMULATOR_OBJECT_ID;
  global $bootloaderActive;
  global $debug;

  if ($bootloaderActive==1) $mySender = getBootloaderObjectId($SIMULATOR_OBJECT_ID);
  else $mySender = $SIMULATOR_OBJECT_ID;

  callObjectMethodByName($sender, "pong", $paramData, $mySender);
  echo "Simulator: ".$debug."<br>";
}

?>