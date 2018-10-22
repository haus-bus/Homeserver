<?php
require ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

$cutPos = strpos($id,"_");
if ($cutPos!==FALSE) $id = substr($id,0,$cutPos);

if (!isset($_SESSION["utf8Encoding"]))
{
   $erg = QUERY("select paramValue from basicConfig where paramKey = 'utf8Encoding' limit 1");
   if ($row=MYSQLi_FETCH_ROW($erg)) $utf8Encoding=$row[0];
   else $utf8Encoding=0;
   $_SESSION["utf8Encoding"]=$utf8Encoding;
}

if ($command=="ajaxObjects")
{
  print_r($_SESSION["ajaxObjects"]);
  exit;
}


$dimmerClassesId = 9;
$switchClassesId = 8;
$temperatureClassesId = 2;
$rolloClassesId = 14;
$ledClassesId = 18;
$tasterClassesId = 1;
$humidityClassesId = 23;
$currentReaderClassesId = getClassesIdByName("CurrentReader");

if ($command == "registerObjects")
{
  unset($_SESSION["ajaxObjects"]);
  $where = "1=2";
  for($i = 0; $i < $objects; $i++)
  {
    $act = "object$i";
    $objectId = $$act;
    $_SESSION["ajaxObjects"][$objectId]["status"] = - 1;
    $_SESSION["ajaxObjects"][$objectId]["text"] = - 1;
    $_SESSION["ajaxObjects"][$objectId]["running"] = 0;
    $_SESSION["ajaxObjects"][$objectId]["toDirection"] = "";
    $where .= " or objectId='$objectId'";
  }
  
  $erg = QUERY("select featureClassesId,objectId from featureInstances where $where");
  while ( $obj = MYSQLi_FETCH_OBJECT($erg) )
  {
    $_SESSION["ajaxObjects"][$obj->objectId]["featureClassesId"] = $obj->featureClassesId;
  }
  
  $_SESSION["ajaxLastId"] = - 1;
  $_SESSION["myWhere"]=$where;
  die("registered $where");
}
else if ($command == "readStatus")
{
	$where = $_SESSION["myWhere"];
  $erg = QUERY("select featureClassesId,objectId from featureInstances where $where");
  while ( $obj = MYSQLi_FETCH_OBJECT($erg) )
  {
  	if ($obj->featureClassesId!=$currentReaderClassesId) callObjectMethodByName($obj->objectId, "getStatus");
  	if ($obj->featureClassesId==$temperatureClassesId) callObjectMethodByName($obj->objectId, "getConfiguration");
  }
}
else if ($command == "dbId")
{
  die($_SESSION["ajaxLastId"]);
}
else if ($command == "showObjects")
{
  print_r($_SESSION["ajaxObjects"]);
  exit();
}
else if ($command == "updateMyStatus")
{
  updateStatus();
  
  //print_r($_SESSION["ajaxObjects"]);
  

  // TOdo hier fï¿½r nicht gefï¿½llt objecte eine Statusabfrage machen und dann Status neu einlesen
  

  $result = "";
  foreach ( (array)$_SESSION["ajaxObjects"] as $senderObj => $arr )
  {
    if ($_SESSION["ajaxObjects"][$senderObj]["changed"] == 1)
    {
      if ($result != "") $result .= ",";
      $result .= $senderObj . "=" . $arr["status"] . ";" . $arr["text"];
      
      if ($arr["toDirection"] != "") $result .= ";" . $arr["toDirection"];
      
      if ($_SESSION["ajaxObjects"][$senderObj]["featureClassesId"] != $currentReaderClassesId) $_SESSION["ajaxObjects"][$senderObj]["toDirection"] = "";
    }
  }
  
  die($result);
}
else if ($command == "click")
{
  if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $dimmerClassesId)
  {
  	if ($function!="" && $function!="undefined") callObjectMethodByName($id, $function, array (getParamNameForObjectFunction($id,$function,0) => $functionParam1,getParamNameForObjectFunction($id,$function,1) => $functionParam2 ));
    else
    {
      if ($_SESSION["ajaxObjects"][$id]["status"] == 1) callObjectMethodByName($id, "setBrightness", array ("brightness" => 0,"fadingTime" => 0));
      else callObjectMethodByName($id, "setBrightness", array ("brightness" => 100, "fadingTime" => 0 ));
    }
  }
  else if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $switchClassesId)
  {
   	if ($function!="" && $function!="undefined") callObjectMethodByName($id, $function, array (getParamNameForObjectFunction($id,$function,0) => $functionParam1,getParamNameForObjectFunction($id,$function,1) => $functionParam2 ));
   	else
   	{
      if ($_SESSION["ajaxObjects"][$id]["status"] == 1) callObjectMethodByName($id, "off");
      else
      {
        $duration = $functionParam1;
        if ($duration == "") $duration = "0";
        callObjectMethodByName($id, "on", array ("onTime" => $duration ));
      }
    }
  }
  else if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $ledClassesId)
  {
    if ($function!="" && $function!="undefined") callObjectMethodByName($id, $function, array (getParamNameForObjectFunction($id,$function,0) => $functionParam1,getParamNameForObjectFunction($id,$function,1) => $functionParam2 ));
    else
    {
    	if ($_SESSION["ajaxObjects"][$id]["status"] == 1) callObjectMethodByName($id, "off");
      else
    	{
        $duration = $functionParam1;
        if ($duration == "") $duration = "0";
        callObjectMethodByName($id, "on", array ("brightness" => 255,"duration" => $duration ));
      }
    }
  }
  else if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $rolloClassesId)
  {
   	if ($function!="" && $function!="undefined") callObjectMethodByName($id, $function, array (getParamNameForObjectFunction($id,$function,0) => $functionParam1,getParamNameForObjectFunction($id,$function,1) => $functionParam2 ));
   	else
   	{
      if ($_SESSION["ajaxObjects"][$id]["running"] == 1) callObjectMethodByName($id, "stop");
    	else
    	{
        callObjectMethodByName($id, "start", array ("direction" => "0" ));
      }
    }
  }
  else if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $tasterClassesId || $multiTaster==1)
  {
  	if ($function!="" && $function!="undefined") callObjectMethodByName($id, $function, array (getParamNameForObjectFunction($id,$function,0) => $functionParam1,getParamNameForObjectFunction($id,$function,1) => $functionParam2 ));
  	else
  	{
      callObjectMethodByName($id, "evCovered");
      callObjectMethodByName($id, "evClicked");
    }
  }
}
else if ($command == "clickup")
{
  if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $rolloClassesId)
  {
    //print_r($_SESSION["ajaxObjects"]);
    if ($_SESSION["ajaxObjects"][$id]["running"] == 1) callObjectMethodByName($id, "stop");
    else callObjectMethodByName($id, "start", array ("direction" => "255" ));
  }
  else if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $dimmerClassesId)
  {
    //print_r($_SESSION["ajaxObjects"]);
    callObjectMethodByName($id, "start", array ("direction" => 1));
  }
}
else if ($command == "clickdown")
{
  if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $rolloClassesId)
  {
    //print_r($_SESSION["ajaxObjects"]);
    if ($_SESSION["ajaxObjects"][$id]["running"] == 1) callObjectMethodByName($id, "stop");
    else callObjectMethodByName($id, "start", array ("direction" => "1" ));
  }
  else if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $dimmerClassesId)
  {
    //print_r($_SESSION["ajaxObjects"]);
    callObjectMethodByName($id, "start", array ("direction" => 255 ));
  }
}
else if ($command == "clickrelease")
{
  if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $dimmerClassesId)
  {
    //print_r($_SESSION["ajaxObjects"]);
    callObjectMethodByName($id, "stop");
  }
}
else if ($command == "setValue")
{
  if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $dimmerClassesId) // DIMMER
  {
    //print_r($_SESSION["ajaxObjects"]);
    callObjectMethodByName($id, "setBrightness", array ("brightness" => $newValue));
  }
  else if ($_SESSION["ajaxObjects"][$id]["featureClassesId"] == $rolloClassesId) // Rollos
  {
    //print_r($_SESSION["ajaxObjects"]);
    callObjectMethodByName($id, "moveToPosition", array ("position" => $newValue));
  }
}
else if ($command == "clickButton")
{
  callInstanceMethodByName($id, "evCovered");
  callInstanceMethodByName($id, "evClicked");
}

function updateStatus()
{
  global $dimmerClassesId;
  global $switchClassesId;
  global $temperatureClassesId;
  global $rolloClassesId;
  global $ledClassesId;
  global $humidityClassesId;
  global $tasterClassesId;
  global $currentReaderClassesId;
  
  if ($_SESSION["ajaxLastId"] == - 1) $firstRound = 1;
  
  $sql = "select * from lastreceived where id>'" . $_SESSION["ajaxLastId"] . "' and (1=2";
  
  foreach ( (array)$_SESSION["ajaxObjects"] as $senderObj => $arr )
  {
    $_SESSION["ajaxObjects"][$senderObj]["changed"] = 0;
    $sql .= " or senderObj='" . $senderObj . "'";
  }
  $sql .= ") order by id";
  //echo $sql."<br>";
  $erg = QUERY($sql);
  while ( $obj = MYSQLi_FETCH_OBJECT($erg) )
  {
    $_SESSION["ajaxLastId"] = $obj->id;
    
    $data = unserialize($obj->functionData);
    
    //echo $obj->senderObj.",".$data->featureClassesId."<br>";
    

    if ($data->featureClassesId == $dimmerClassesId)
    {
      if ($data->name == "evOff") setObjectStatus($obj->senderObj, 0, "0");
      else if ($data->name == "evOn") setObjectStatus($obj->senderObj, 1, $data->paramData[0]->dataValue);
      else if ($data->name == "Status")
      {
        if ($data->paramData[0]->dataValue == 0) $state = 0;
        else $state = 1;
        
        setObjectStatus($obj->senderObj, $state, $data->paramData[0]->dataValue);
      }
    }
    else if ($data->featureClassesId == $switchClassesId)
    {
      if ($data->name == "evOff") setObjectStatus($obj->senderObj, 0, "###");
      else if ($data->name == "evOn") setObjectStatus($obj->senderObj, 1, "###");
      else if ($data->name == "Status")
      {
        if ($data->paramData[0]->dataValue == 0) $state = 0;
        else $state = 1;
        
        setObjectStatus($obj->senderObj, $state, "###");
      }
    }
    else if ($data->featureClassesId == $ledClassesId)
    {
      if ($data->name == "evOff") setObjectStatus($obj->senderObj, 0, "0");
      else if ($data->name == "evOn") setObjectStatus($obj->senderObj, 1, $data->paramData[0]->dataValue);
      else if ($data->name == "Status")
      {
        if ($data->paramData[0]->dataValue == 0) $state = 0;
        else $state = 1;
        
        setObjectStatus($obj->senderObj, $state, $data->paramData[0]->dataValue);
      }
    }
    else if ($data->featureClassesId == $rolloClassesId)
    {
      if ($data->name == "evOpen") setObjectStatus($obj->senderObj, 0, 0);
      else if ($data->name == "evClosed") setObjectStatus($obj->senderObj, 1, $data->paramData[0]->dataValue);
      else if ($data->name == "Status") setObjectStatus($obj->senderObj, 1, $data->paramData[0]->dataValue);
      
      if ($data->name == "evStart")
      {
        $_SESSION["ajaxObjects"][$obj->senderObj]["running"] = 1;
        
        if ($firstRound != 1)
        {
          if ($data->paramData[0]->dataValue == 255) $_SESSION["ajaxObjects"][$obj->senderObj]["toDirection"] = "up";
          else $_SESSION["ajaxObjects"][$obj->senderObj]["toDirection"] = "down";
          $_SESSION["ajaxObjects"][$obj->senderObj]["changed"] = 1;
        }
      }
      else if ($data->name == "evClosed") $_SESSION["ajaxObjects"][$obj->senderObj]["running"] = 0;
      else if ($data->name == "evOpen") $_SESSION["ajaxObjects"][$obj->senderObj]["running"] = 0;
    }
    else if ($data->featureClassesId == $temperatureClassesId)
    {
    	$_SESSION["ajaxObjects"][$obj->senderObj]["changed"] = 1;
    	if ($data->name == "Status")
    	{
        $myStatus = $data->paramData[0]->dataValue . "." . $data->paramData[1]->dataValue;
        if ( $_SESSION["utf8Encoding"] == 1) $myStatus .= utf8_encode("°") . "C";
        else $myStatus .= "°C";
      	setObjectStatus($obj->senderObj, 1, $myStatus);
      	
      	$_SESSION["ajaxObjects"][$obj->senderObj]["toDirection"] = $data->paramData[2]->dataValueName;
      }
      else if ($data->name == "evCold") $_SESSION["ajaxObjects"][$obj->senderObj]["toDirection"] = "COLD";
      else if ($data->name == "evWarm") $_SESSION["ajaxObjects"][$obj->senderObj]["toDirection"] = "WARM";
      else if ($data->name == "evHot") $_SESSION["ajaxObjects"][$obj->senderObj]["toDirection"] = "HOT";
    }
    else if ($data->featureClassesId == $humidityClassesId)
    {
      if ($data->name == "Status") setObjectStatus($obj->senderObj, 1, $data->paramData[0]->dataValue . "%");
    }
    else if ($data->featureClassesId == $tasterClassesId)
    {
       $zeit = date("H:i",$obj->time);
       if ($data->name == "evCovered") setObjectStatus($obj->senderObj, 0, $zeit);
       else if ($data->name == "evFree") setObjectStatus($obj->senderObj, 1, $zeit);
    }
    else if ($data->featureClassesId == $currentReaderClassesId)
    {
       if ($data->name == "evCurrent")
       {
      	 $mysStatus = ((int)($data->paramData[0]->dataValue/1000)).".".($data->paramData[0]->dataValue%1000)." kWh";
       	 setObjectStatus($obj->senderObj, 0, $mysStatus);
       }
       else if ($data->name == "evSignal")
       {
      	 $_SESSION["ajaxObjects"][$obj->senderObj]["toDirection"]=$data->paramData[2]->dataValue." Watt";
      	 $_SESSION["ajaxObjects"][$obj->senderObj]["changed"] = 1;
       }
    }
  }
}

function setObjectStatus($objectId, $status, $text)
{
  //echo "setObjectStatus($objectId, $status, $text)<br>";
  if ($_SESSION["ajaxObjects"][$objectId]["status"] != $status || $_SESSION["ajaxObjects"][$objectId]["text"] != $text) $_SESSION["ajaxObjects"][$objectId]["changed"] = 1;
  
  $_SESSION["ajaxObjects"][$objectId]["status"] = $status;
  $_SESSION["ajaxObjects"][$objectId]["text"] = $text;
}

function getParamNameForObjectFunction($objectId,$functionName,$paramId)
{
  $erg = QUERY("select featureFunctionParams.name from featureFunctionParams join featureFunctions on (featureFunctions.id=featureFunctionParams.featureFunctionId) join  featureInstances on (featureInstances.featureClassesId = featureFunctions.featureClassesId) where  featurefunctions.name='$functionName' and featureInstances.objectId='$objectId' order by featureFunctionParams.id limit $paramId,1");
  $row=MYSQLi_FETCH_ROW($erg);
  return $row[0];
}
?>