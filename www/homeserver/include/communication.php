<?php

/**
 * Liefert den Funktions-Index nach Klassenindex und Funktionsnamen
 */
function getClassesIdFunctionsIdByName($classesId, $functionName)
{
    global $CONTROLLER_CLASSES_ID;
    $erg = QUERY("select featureFunctions.id from featureFunctions join featureInstances on (featureInstances.featureClassesId = featureFunctions.featureClassesId) where featureInstances.featureClassesId='$classesId' and featureFunctions.name='$functionName' limit 1");
    $row = MYSQLi_FETCH_ROW($erg);
    return $row[0];
}

/**
 * Liefert den Funktions-Param-Index nach Klassenindex, Funktionsnamen und PArameternamen
 */
function getClassesIdFunctionParamIdByName($classesId, $functionName, $paramName)
{
    global $CONTROLLER_CLASSES_ID;
    $erg = QUERY("select featureFunctionParams.id from featureFunctionParams join featurefunctions on (featureFunctionParams.featurefunctionId=featurefunctions.id) where featureFunctions.featureClassesId='$classesId' and featureFunctions.name='$functionName' and featureFunctionParams.name='$paramName' limit 1");
    $row = MYSQLi_FETCH_ROW($erg);
    return $row[0];
}

/**
 * Liefert den Funktions-Index nach ObjektID und Funktionsnamen
 */
function getObjectFunctionsIdByName($objectId, $functionName)
{
    global $CONTROLLER_CLASSES_ID;
    global $CONTROLLER_CLASS_ID;

    // Controller Broadcast
    if (getClassId($objectId) == $CONTROLLER_CLASS_ID)
        $erg = QUERY("select SQL_CACHE id from featureFunctions where featureFunctions.featureClassesId='$CONTROLLER_CLASSES_ID' and featureFunctions.name='$functionName' limit 1");
    else
        $erg = QUERY("select SQL_CACHE featureFunctions.id from featureFunctions join featureInstances on (featureInstances.featureClassesId = featureFunctions.featureClassesId) where featureInstances.objectId='$objectId' and featureFunctions.name='$functionName' limit 1");
    $row = MYSQLi_FETCH_ROW($erg);
    return $row[0];
}

/**
 * Liefert den Funktion-ID nach ObjektID und Funktionsnamen
 */
function getObjectFunctionIdByName($objectId, $functionName)
{
    global $CONTROLLER_CLASSES_ID;
    global $CONTROLLER_CLASS_ID;

    // Controller Broadcast
    if (getClassId($objectId) == $CONTROLLER_CLASS_ID)
        $erg = QUERY("select SQL_CACHE functionId from featureFunctions where featureFunctions.featureClassesId='$CONTROLLER_CLASSES_ID' and featureFunctions.name='$functionName' limit 1");
    else
        $erg = QUERY("select SQL_CACHE functionId from featureFunctions join featureInstances on (featureInstances.featureClassesId = featureFunctions.featureClassesId) where featureInstances.objectId='$objectId' and featureFunctions.name='$functionName' limit 1");
    $row = MYSQLi_FETCH_ROW($erg);
    return $row[0];
}

/**
 * Liefert den Enumwert nach ObjektID,Funktionsnamen,Parameternamen und Enumnamen
 */
function getFunctionParamEnumValueByName($objectId, $functionName, $paramName, $enumName)
{
    global $CONTROLLER_CLASSES_ID;
    global $CONTROLLER_CLASS_ID;

    if (getClassId($objectId) == $CONTROLLER_CLASS_ID)
    {
    	 $sql = "select featureFunctionEnums.value  
                      from featureFunctions
                      LEFT join featureFunctionParams on (featureFunctionParams.featureFunctionId=featureFunctions.id)
                      LEFT join featureFunctionEnums on (featureFunctionEnums.featureFunctionId=featureFunctions.id)
                      where featureFunctions.featureClassesId='$CONTROLLER_CLASSES_ID' and featureFunctions.name='$functionName' and featureFunctionParams.name = '$paramName' and featureFunctionEnums.name='$enumName' limit 1";
    } else
    {
       $sql = "select featureFunctionEnums.value  
                      from featureFunctions
                      JOIN featureInstances on (featureInstances.featureClassesId = featureFunctions.featureClassesId)		
                      LEFT join featureFunctionParams on (featureFunctionParams.featureFunctionId=featureFunctions.id)
                      LEFT join featureFunctionEnums on (featureFunctionEnums.featureFunctionId=featureFunctions.id)
                      where featureInstances.objectId='$objectId' and featureFunctions.name='$functionName' and featureFunctionParams.name = '$paramName' and featureFunctionEnums.name='$enumName' limit 1";
    }
    
    $erg = QUERY($sql);
    if ($row = MYSQLi_FETCH_ROW($erg)) return $row[0];
    else die("getFunctionParamEnumValueByName: $sql");
}

/**
 * Liefert den Enumwert nach ObjektID,Funktionsnamen,Parameternamen und Enumnamen
 */
function getFunctionParamEnumValueForClassesIdByName($featureClassesId, $functionName, $paramName, $enumName)
{
 	 $sql = "select featureFunctionEnums.value  
                  from featureFunctions
                  LEFT join featureFunctionParams on (featureFunctionParams.featureFunctionId=featureFunctions.id)
                  LEFT join featureFunctionEnums on (featureFunctionEnums.featureFunctionId=featureFunctions.id)
                  where featureFunctions.featureClassesId='$featureClassesId' and featureFunctions.name='$functionName' and featureFunctionParams.name = '$paramName' and featureFunctionEnums.name='$enumName' limit 1";
    
    $erg = QUERY($sql);
    if ($row = MYSQLi_FETCH_ROW($erg)) return $row[0];
    else die("getFunctionParamEnumValueForClassesIdByName: $sql");
}

/**
 * Liefert den Enumwert nach ClassesId,Funktionsnamen,Parameternamen und Enumnamen
 */
function getFunctionParamBitValueByNameForClassesId($classesId, $functionName, $paramName, $bitName)
{
  	 $sql = "select featurefunctionbitmasks.bit  
             from featureFunctions
             LEFT join featureFunctionParams on (featureFunctionParams.featureFunctionId=featureFunctions.id)
             LEFT join featurefunctionbitmasks on (featurefunctionbitmasks.featureFunctionId=featureFunctions.id)
             where featureFunctions.featureClassesId='$classesId' and featureFunctions.name='$functionName' and featureFunctionParams.name = '$paramName' and featurefunctionbitmasks.name='$bitName' limit 1";

    $erg = QUERY($sql);
    if ($row = MYSQLi_FETCH_ROW($erg)) return $row[0];
    else die("getFunctionParamBitValueByNameForClassesId: $sql");
}

/**
 * Wartet auf einen Sender-Index f� angegebende Dauer auf das angegebene Ergebnis (RESULT) 
 */
function waitForInstanceResultByName($senderInstanceId, $waitSeconds, $functionName, $lastLogId, $resultType = "funtionDataParams", $fail = 1)
{
	  $sql = "select objectId, featureFunctions.functionId as featureFunctionsId
                                           from featureInstances
                                           join featureFunctions on (featureFunctions.featureClassesId = featureInstances.featureClassesId)
                                           where featureInstances.id='$senderInstanceId' and featureFunctions.name='$functionName' limit 1";
    $erg = QUERY($sql);
    if ($row = MYSQLi_FETCH_ROW($erg))
    {
        $senderObjectId = $row[0];
        $functionId = $row[1];
    } else
        die("waitForInstanceResultByName: $sql");

    return _waitForObjectResult($senderObjectId, $waitSeconds, "RESULT", $functionId, $lastLogId, $resultType, $fail);
}

/**
 * Wartet auf eine Sender-Object-ID f� angegebende Dauer auf das angegebene Ergebnis (RESULT) 
 */
function waitForObjectResultByName($senderObjectId, $waitSeconds, $functionName, $lastLogId, $resultType = "funtionDataParams", $fail = 1, $wait=1)
{
    global $CONTROLLER_CLASS_ID;
    global $CONTROLLER_CLASSES_ID;

    // Controller
    if (getClassId($senderObjectId) == $CONTROLLER_CLASS_ID)
    {
       $sql="select featureFunctions.functionId from featureFunctions where featureClassesId = '$CONTROLLER_CLASSES_ID' and featureFunctions.name='$functionName' limit 1";
    }
    else
    {
       $sql="select featureFunctions.functionId
                    from featureInstances
                    join featureFunctions on (featureFunctions.featureClassesId = featureInstances.featureClassesId)
                    where featureInstances.objectId='$senderObjectId' and featureFunctions.name='$functionName' limit 1";
    }
    
    $erg = QUERY($sql);
    if ($row = MYSQLi_FETCH_ROW($erg))
    {
        $functionId = $row[0];
    } 
    else die("waitForObjectResultByName: $sql");

    return _waitForObjectResult($senderObjectId, $waitSeconds, "RESULT", $functionId, $lastLogId, $resultType, $fail, $wait);
}

/**
 * Wartet auf einen Sender-Index f� angegebende Dauer auf das angegebene Event
 */
function waitForInstanceEventByName($senderInstanceId, $waitSeconds, $functionName, $lastLogId, $resultType = "funtionDataParams", $fail = 1)
{
	  $sql = "select objectId, featureFunctions.functionId as featureFunctionsId
                                           from featureInstances
                                           join featureFunctions on (featureFunctions.featureClassesId = featureInstances.featureClassesId)
                                           where featureInstances.id='$senderInstanceId' and featureFunctions.name='$functionName' limit 1";
    $erg = QUERY($sql);
    if ($row = MYSQLi_FETCH_ROW($erg))
    {
        $senderObjectId = $row[0];
        $functionId = $row[1];
    } else
        die("waitForInstanceEventByName: $sql"); 

    return _waitForObjectResult($senderObjectId, $waitSeconds, "EVENT", $functionId, $lastLogId, $resultType, $fail);
}

/**
 * Wartet auf eine Sender-Object-ID f� angegebende Dauer auf das angegebene Ergebnis (EVENT)
 */
function waitForObjectEventByName($senderObjectId, $waitSeconds, $functionName, $lastLogId, $resultType = "funtionDataParams", $fail = 1)
{
    global $CONTROLLER_CLASS_ID;
    global $CONTROLLER_CLASSES_ID;

    // Controller
    if (getClassId($senderObjectId) == $CONTROLLER_CLASS_ID)
    {
       $sql="select featureFunctions.functionId from featureFunctions where featureClassesId = '$CONTROLLER_CLASSES_ID' and featureFunctions.name='$functionName' limit 1";
    }
    else
    {

       $sql= "select featureFunctions.functionId as featureFunctionsId
              from featureInstances
              join featureFunctions on (featureFunctions.featureClassesId = featureInstances.featureClassesId)
              where featureInstances.objectId='$senderObjectId' and featureFunctions.name='$functionName' limit 1";
    }
    $erg = QUERY($sql);
    if ($row = MYSQLi_FETCH_ROW($erg))
    {
        $functionId = $row[0];
    } else
        die("waitForObjectEventByName: $sql");

    return _waitForObjectResult($senderObjectId, $waitSeconds, "EVENT", $functionId, $lastLogId, $resultType, $fail);
}

function _waitForObjectResult($senderObjectId, $waitSeconds, $type, $functionId, $lastLogId, $resultType = "funtionDataParams", $fail = 1, $wait=1)
{
    $start = time();

    while (1)
    {
   	    if ($wait==1) sleepMS(20);

        if ($type!="") $andType=" and type='$type'";
        $sql = "select functionData,senderSubscriberData from udpcommandlog where id>'$lastLogId' and senderObj='$senderObjectId' and fktId='$functionId' $andType limit 1";
        $erg = QUERY($sql);
        while ($obj = MYSQLi_FETCH_object($erg))
        {
            if ($resultType == "funtionDataParams")
            {
            	$myObj = unserialize($obj->functionData);
            	return $myObj->paramData;
            }
            else if ($resultType == "senderData")
            {
            	$senderObj = unserialize($obj->senderSubscriberData);
            	return $senderObj;
            }
            else return;
        }

        if (time() - $start > $waitSeconds)
        {
            if ($fail == 1) die("Antwort nicht empfangen $type: functionId=$functionId,  waitSeconds=$waitSeconds, lastLogId=$lastLogId, senderObjectId=$senderObjectId");
            else
            {
            	echo "Antwort nicht empfangen $type: functionId=$functionId,  waitSeconds=$waitSeconds, lastLogId=$lastLogId, senderObjectId=$senderObjectId <br>";
            	return -1;
            }
        }
        if ($wait!=1) sleepMS(20);
    }
}

//executeCommand("1681719297", "ping", "", "pong");
function executeCommand($objectId, $functionName, $paramArray="", $resultName="")
{
   global $lastLogId;
   
   for ($i=0;$i<2;$i++)
   {
     callObjectMethodByName($objectId, $functionName, $paramArray);
     if ($resultName!="")
     {
       $result = waitForCommandResultByName($objectId, $resultName, $lastLogId);
       if ($result!=-1)
       {
       	 if ($result=="") return "";
       	 $elCount = count((array)$result);
       	 
         for ($i=0;$i<$elCount;$i++)
         {
         	 if ($result[$i]->type=="ENUM") $resultArray[$result[$i]->name]=$result[$i]->dataValueName;
         	 else $resultArray[$result[$i]->name]=$result[$i]->dataValue;
         }

         return $resultArray;
       }
       echo "Recovery bei $objectId $methodName <br>";
       flushIt();
     }
     else return;
   }
   
   return -1;
}

function waitForCommandResultByName($senderObjectId, $resultName, $lastLogId)
{
  global $CONTROLLER_CLASS_ID;
  global $CONTROLLER_CLASSES_ID;

  if (getClassId($senderObjectId) == $CONTROLLER_CLASS_ID)
  {
    $sql="select functionId from featureFunctions where featureClassesId = '$CONTROLLER_CLASSES_ID' and featureFunctions.name='$resultName' limit 1";
  }
  else
  {
    $sql="select featureFunctions.functionId
                 from featureInstances
                 join featureFunctions on (featureFunctions.featureClassesId = featureInstances.featureClassesId)
                 where featureInstances.objectId='$senderObjectId' and featureFunctions.name='$resultName' limit 1";
  }
    
  $erg = QUERY($sql);
  if ($row = MYSQLi_FETCH_ROW($erg)) $functionId = $row[0];
  else die("waitForCommandResultByName: $sql");

  return _waitForObjectResult($senderObjectId, 3, "", $functionId, $lastLogId, "funtionDataParams", 0, 1);
}

function callObjectMethodByNameAndRecover($objectId, $methodName, $params, $resultName, $waitSeconds=3, $repetitions=1,$fail=1,$resultType="funtionDataParams")
{
   global $lastLogId;
   
   for ($i=0;$i<$repetitions+1;$i++)
   {
     //echo "BBBB $i <br>";
     callObjectMethodByName($objectId, $methodName, $params);
     $result = waitForObjectResultByName($objectId, $waitSeconds, $resultName, $lastLogId,$resultType,0);
     //echo "A".$result."<br>";
      if ($result!=-1)
        return $result;
      echo "Recovery bei $objectId $methodName ... (waitSeconds = $waitSeconds, repetitions=".($i+1)."/".$repetitions.", fail=$fail)<br>";
      flushIt();
   }

   if ($fail==1)
      die("Antwort nicht empfangen $objectId $methodName");
   else
     return -1;
}

function callObjectMethodByNameForEventAndRecover($objectId, $methodName, $params, $eventName, $waitSeconds=3, $repetitions=1,$fail=1)
{
  global $lastLogId;
   
  for ($i=0;$i<$repetitions+1;$i++)
  {
  //echo "BBBB $i <br>";
  callObjectMethodByName($objectId, $methodName, $params);
  $result = waitForObjectEventByName($objectId, $waitSeconds, $eventName, $lastLogId,"funtionDataParams",0);
      //echo "A".$result."<br>";
  if ($result!=-1)
  return $result;
  echo "Recovery bei $objectId $methodName <br>";
  flushIt();
  }
   
  if ($fail==1)
    die("Antwort nicht empfangen $objectId $methodName");
    else
    return -1;
}

function waitForCommandId($commandId, $timeout=1000)
{
	  $timeout=time()+2;
    while (time()<$timeout)
    {
    	  sleepMS(40);

        $erg = QUERY("select max(id) from udpCommandLog");
        $row=MYSQLi_FETCH_ROW($erg);
        if ($row[0]>=$commandId) return;
    }
}

/**
 * Liefert einen Wert zu einem Datenbankergebnis zum angegebenen Namen
 */
function getResultDataValueByName($paramName, $result)
{
    foreach ((array) $result as $obj)
    {
    	foreach($obj as $name => $value)
    	{
      	 if ($name == "name" && $value=="$paramName")
      	    return $obj->dataValue;
      }
    }
    return "-1";
}

// Ruft eine durch functionName angegebene Funktion auf einer Objektinstanz auf
// Enumnamen werden dabei durch den zugeh�en Enumwert ersetzt
function callInstanceMethodByName($featureInstanceId, $functionName, $paramData = "", $senderObjectId = "")
{
    $limit = "limit 1";
    if ($paramData != "")
        $limit = "";

    $erg = QUERY("select objectId,
                                                     featureFunctions.id as featureFunctionsId,
                                                     featureFunctionParams.name as featureFunctionParamName,
                                                     featureFunctionEnums.name as featureFunctionEnumName, featureFunctionEnums.value as featureFunctionEnumValue  
                                                     from featureInstances
                                                     join featureFunctions on (featureFunctions.featureClassesId = featureInstances.featureClassesId)
                                                     LEFT join featureFunctionParams on (featureFunctionParams.featureFunctionId=featureFunctions.id)
                                                     LEFT join featureFunctionEnums on (featureFunctionEnums.featureFunctionId=featureFunctions.id)
                                                     where featureInstances.id='$featureInstanceId' and featureFunctions.name='$functionName' $limit");
    while ($obj = MYSQLi_FETCH_OBJECT($erg))
    {
        $featureFunctionId = $obj->featureFunctionsId;
        $receiverObjectId = $obj->objectId;

        foreach ((array)$paramData as $key => $value)
        {
            if ($key == $obj->featureFunctionParamName && $value == $obj->featureFunctionEnumName)
            {
                $paramData[$key] = $obj->featureFunctionEnumValue;
                break;
            }
        }
    }
    
    callInstanceMethodForObjectId($receiverObjectId, $featureFunctionId, $paramData, $senderObjectId);
}

// Ruft eine durch functionName angegebene Funktion auf einer Objektinstanz auf
// Enumnamen werden dabei durch den zugeh�en Enumwert ersetzt
function callObjectMethodByName($receiverObjectId, $functionName, $paramData = "", $senderObjectId = "")
{
    global $CONTROLLER_CLASSES_ID;
    global $CONTROLLER_CLASS_ID;

    $limit = "limit 1";
    if ($paramData != "") $limit = "";

    if (getClassId($receiverObjectId) == $CONTROLLER_CLASS_ID) // Controller Broadcast
    {
      $erg = QUERY("select featureFunctions.id as featureFunctionsId,
                           featureFunctionParams.name as featureFunctionParamName,
                           featureFunctionEnums.name as featureFunctionEnumName, featureFunctionEnums.value as featureFunctionEnumValue  
                           from featureFunctions
                           LEFT join featureFunctionParams on (featureFunctionParams.featureFunctionId=featureFunctions.id)
                           LEFT join featureFunctionEnums on (featureFunctionEnums.featureFunctionId=featureFunctions.id)
                           where featureFunctions.featureClassesId='$CONTROLLER_CLASSES_ID' and featureFunctions.name='$functionName' $limit");
    } 
    else
    {
      $erg = QUERY("select objectId,
                           featureFunctions.id as featureFunctionsId,
                           featureFunctionParams.name as featureFunctionParamName,
                           featureFunctionEnums.name as featureFunctionEnumName, featureFunctionEnums.value as featureFunctionEnumValue  
                           from featureInstances
                           join featureFunctions on (featureFunctions.featureClassesId = featureInstances.featureClassesId)
                           LEFT join featureFunctionParams on (featureFunctionParams.featureFunctionId=featureFunctions.id)
                           LEFT join featureFunctionEnums on (featureFunctionEnums.featureFunctionId=featureFunctions.id)
                           where featureInstances.objectId='$receiverObjectId' and featureFunctions.name='$functionName' $limit");
    }
    while ($obj = MYSQLi_FETCH_OBJECT($erg))
    {
        $featureFunctionId = $obj->featureFunctionsId;

        foreach ((array) $paramData as $key => $value)
        {
            if ($key == $obj->featureFunctionParamName && $value == $obj->featureFunctionEnumName)
            {
                $paramData[$key] = $obj->featureFunctionEnumValue;
                break;
            }
        }
    }

    //die($receiverObjectId."-".$featureFunctionId."-".$paramData."-".$senderObjectId);
    callInstanceMethodForObjectId($receiverObjectId, $featureFunctionId, $paramData, $senderObjectId);
}

function callInstanceMethod($featureInstanceId, $featureFunctionId, $paramData = "", $senderObjectId = "")
{
    $erg = QUERY("select SQL_CACHE objectId from featureInstances where id='$featureInstanceId' limit 1");
    if ($row = MYSQLi_FETCH_ROW($erg))
        $receiverObjectId = $row[0];
    else
        die("Ung� featureInstanceId: " . $featureInstanceId);

    callInstanceMethodForObjectId($receiverObjectId, $featureFunctionId, $paramData, $senderObjectId);
}

function callInstanceMethodForObjectId($receiverObjectId, $featureFunctionId, $paramData = "", $senderObjectId = "")
{
    global $debug;
    global $MY_OBJECT_ID;
    global $BROADCAST_OBJECT_ID;

    $debugStr = "";
    $dataPos = 0;
    $erg = QUERY("select type,name,functionId from featureFunctions where id='$featureFunctionId' limit 1");
    if ($obj = MYSQLi_FETCH_OBJECT($erg))
    {
      $debugStr .= i18n($obj->name). "(" . $obj->functionId . ")";

      // Spezialmode um ein Event aus der Webapplikation zu versenden
      if ($obj->type == "EVENT" && $senderObjectId == "")
      {
        $senderObjectId = $receiverObjectId;
        $receiverObjectId = $BROADCAST_OBJECT_ID;
      } 
      else if ($obj->type == "RESULT" && $senderObjectId == "")
      {
        $senderObjectId = $receiverObjectId;
        $receiverObjectId = $MY_OBJECT_ID;
      }

      $data[$dataPos++] = $obj->functionId;

      $binaryStartPos = 0;

      $erg2 = QUERY("select SQL_CACHE id,name,type from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
      while ($obj2 = MYSQLi_FETCH_OBJECT($erg2))
      {
        $param = $paramData[trim($obj2->name)];
        if ($param == "")
        {
          $param = 0;
          $wasEmpty = 1;
        } 
        else $wasEmpty = 0;

        if ($obj2->type == "BYTE" || $obj2->type == "ENUM" || $obj2->type == "BITMASK") $data[$dataPos++] = $param;
        else if ($obj2->type == "BLOB")
        {
          if ($wasEmpty != 1)
          {
            $binaryStartPos = $dataPos;
            for ($i = 0; $i < strlen($param); $i++)
            {
              $data[$dataPos++] = substr($param, $i, 1);
            }
          }
        } 
        else
        {
          if ($obj2->type == "WORD" || $obj2->type == "WEEKTIME") $bytes = wordToBytes($param);
          else if ($obj2->type == "DWORD") $bytes = dWordToBytes($param);
          else if ($obj2->type == "STRING")$bytes = stringToBytes($param);
          else if ($obj2->type == "WORDLIST") $bytes = wordListToBytes($param);
          else die("Unbekannter Parametertyp: " . $obj2->type);
       
          foreach ((array) $bytes as $value)
          {
            $data[$dataPos++] = $value;
          }
        }

        if ($obj2->type == "BLOB") $debugStr .= ", " . i18n($obj2->name) . "(" . $obj2->type . ", " . strlen($param) . " bytes)";
        else $debugStr .= ", " . i18n($obj2->name) . "(" . $obj2->type . ", " . $param . ")";
      }
    } 
    else die("Ungültige methodId b -> $featureFunctionId");

    sendCommand($receiverObjectId, $data, $senderObjectId, $binaryStartPos);

    $debug = "Funktion wurde aufgerufen (" . date("H:i:s") . " Uhr)<br>$debugStr <br>" . $debug;
}

function sendCommand($receiverObjectId, $data, $senderObjectId = "", $binaryStartPos = 0)
{
	//echo $receiverObjectId."-".$data."-".$senderObjectId."-".$binaryStartPos."<br>";
    global $UDP_PORT;
    global $MY_OBJECT_ID;
    global $UDP_HEADER_BYTES;
    global $debug;

    if ($senderObjectId == "")
        $senderObjectId = $MY_OBJECT_ID;

   
    $datagrammPos = 0;

    // UDP-Header
    foreach ($UDP_HEADER_BYTES as $value)
    {
        $datagramm[$datagrammPos++] = $value;
    }

    //Kontroll-Byte
    $datagramm[$datagrammPos++] = 0x00;

    // Nachrichtenz㧬er
    QUERY("INSERT into udpHelper (dummy) values('1')");
    $id = query_insert_id() % 255;
    $datagramm[$datagrammPos++] = $id;

    // Sender-ID
    $myObjectIdBytes = dWordToBytes($senderObjectId);
    foreach ($myObjectIdBytes as $value)
    {
        $datagramm[$datagrammPos++] = $value;
    }

    // Empf㭧er-ID
    $receiverIdBytes = dWordToBytes($receiverObjectId);
    foreach ($receiverIdBytes as $value)
    {
        $datagramm[$datagrammPos++] = $value;
    }

    // Datenl㭧e
    $dataLength = count($data);
    $dataLengthBytes = wordToBytes($dataLength);
    foreach ($dataLengthBytes as $value)
    {
        $datagramm[$datagrammPos++] = $value;
    }

    $dataStartPos = $datagrammPos;

    // Daten
    for ($i = 0; $i < $dataLength; $i++)
    {
        $datagramm[$datagrammPos++] = $data[$i];
    }

    $binary_msg = "";
    for ($i = 0; $i < $datagrammPos; $i++)
    {
        if ($binaryStartPos == 0 || $i < $binaryStartPos + $dataStartPos)
            $binary_msg .= chr($datagramm[$i]);
        else
            $binary_msg .= $datagramm[$i];
    }

    updateLastLogId();

    if (isWindows())
    {
      $fp = fsockopen("udp://" . getNetworkIp(), $UDP_PORT, $errno, $errstr);
      fwrite($fp, $binary_msg, $datagrammPos);
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
        $e = socket_sendto($s, $binary_msg, $datagrammPos, 0, getNetworkIp(), $UDP_PORT);
        socket_close($s);
      }
    }    

    $debug = bytesToDebugString($datagramm);
}

function paramToBytes($param, $dataType, $convertWeektimeToThreeBytes=0)
{
    if ($dataType == "BYTE" || $dataType == "ENUM" || $dataType == "BITMASK") $bytes = $param;
    else if ($dataType == "WORD") $bytes = wordToBytes($param);
    else if ($dataType == "DWORD") $bytes = dWordToBytes($param);
    else if ($dataType == "WEEKTIME")
    {
    	if ($convertWeektimeToThreeBytes==0) $bytes = wordToBytes($param);
    	else
    	{
    		$obj = parseWeekTime($param);
    		$bytes[0]=$obj->minute;
    		$bytes[1]=$obj->hour;
    		if ($obj->hour=="31") $bytes[1]=255;
    		$bytes[2]=$obj->day;
    		if ($obj->day=="7") $bytes[2]=255;
    	}
    }
    else if ($dataType == "STRING") $bytes = stringToBytes($param);
    else if ($dataType == "WORDLIST") $bytes = wordListToBytes($param);
    else die("Unbekannter Parametertyp: " . $dataType);
    return $bytes;
}

function updateControllerStatus($short=0)
{
    global $BROADCAST_OBJECT_ID;
    global $debug;
    $pingActive=1;

    echo "<script>top.frames[0].document.getElementById('treeUpdateControl').innerHTML='.';</script>";

    treeStatusOut("Controllerstatus wird aktualisiert ...");

    QUERY("UPDATE controller set online='0',receivedModuleId='0',receivedConfiguration='0',receivedObjects='0'");

    $message = "";
    callObjectMethodByName($BROADCAST_OBJECT_ID, "getModuleId", array("index"=>"0"));
    $message = $debug . "<br>";
    sleepMS(1000);
    callObjectMethodByName($BROADCAST_OBJECT_ID, "getModuleId", array("index"=>"1"));
    $message = $debug . "<br>";
    sleepMS(1000);
    callObjectMethodByName($BROADCAST_OBJECT_ID, "getConfiguration");
    $message .= $debug . "<br>";
    sleepMS(1000);
    callObjectMethodByName($BROADCAST_OBJECT_ID, "getRemoteObjects");
    $message .= $debug . "<br>";
    
    if ($short!=1)
    {
      sleepMS(1000);
      
      $oldOnline=-1;
      $newOnline=0;
      $pinged=0;
      while($oldOnline!=$newOnline || $pinged==1)
      {
        $oldOnline=$newOnline;
        sleepMS(1000);
        $erg = QUERY("select count(*) from controller where bootloader='0' and online='1'");
        $row=MYSQLi_FETCH_ROW($erg);
        $newOnline=$row[0];
  
        treeStatusOut("Online Controller: ".$newOnline);
        
        if ($oldOnline==$newOnline)
        {
           if ($pinged!=1 && $pingActive==1)
           {
             $erg = QUERY("select objectId from controller where bootloader='0' and online='0'");
             while ($row = MYSQLi_FETCH_ROW($erg))
             {
               callObjectMethodByName($row[0], "ping");
               $pinged=1;
             }
           
             if ($pinged==1)
             {
               treeStatusOut("Online Controller: ".$newOnline.". Ping an offliner...");
               sleepMS(1500);
             }
           }
           else
             break;
        }
      }
      
      $repeated=0;
      $erg = QUERY("select objectId, receivedModuleId, receivedConfiguration, receivedObjects from controller where bootloader='0' and online='1' and (receivedModuleId='0' or receivedConfiguration='0' or receivedObjects='0')");
      while ($row = MYSQLi_FETCH_ROW($erg))
      {
      	 if ($row[1]==0)
      	 {
      	 	 $repeated=1;
      	 	 treeStatusOut("Wiederhole ModuleId");
      	 	 callObjectMethodByName($row[0], "getModuleId", array("index"=>"0"));
      	 	 sleepMS(100);
      	 }
      	 
      	 if ($row[2]==0)
      	 {
      	 	 $repeated=1;
      	 	 treeStatusOut("Wiederhole Configuration");
      	 	 callObjectMethodByName($row[0], "getConfiguration");
      	 	 sleepMS(100);
      	 }
      	 
      	 if ($row[3]==0)
      	 {
      	 	 $repeated=1;
      	 	 treeStatusOut("Wiederhole RemoteObjects");
      	 	 callObjectMethodByName($row[0], "getRemoteObjects");
      	 	 sleepMS(100);
      	 }
      }
      
      if ($repeated==1)
      {
      	sleepMS(1000);
      	$erg = QUERY("select objectId, receivedModuleId, receivedConfiguration, receivedObjects from controller where bootloader='0' and online='1' and (receivedModuleId='0' or receivedConfiguration='0' or receivedObjects='0') limit 1");
        if ($row = MYSQLi_FETCH_ROW($erg))
        {
        	 treeStatusOut("Nicht alle Daten empfangen!");
        	 sleepMS(1000);
        }
      }
    }
    
    for ($i=0;$i<10;$i++)
    {
    	$minDist = time()-2;
    	$erg = QUERY("select id from lastreceived where time>'$minDist' limit 1");
    	if ($obj=MYSQLi_FETCH_OBJECT($erg))
    	{
    		treeStatusOut("Fertigstellung $i ...");
    		sleep(1);
    	}
    	else break;
    }
    
    // Gibts es Controller mit unbekannten Features, die mittlerweile bekannt sind ?
    $erg = QUERY("select id, objectId from featureInstances where featureClassesId='-1' or name like 'Unbekanntes Feature%'");
    while($obj=MYSQLi_FETCH_OBJECT($erg))
    {
    	  $actClassId = getClassId($obj->objectId);
    	  
        $erg2 = QUERY("select id,name from featureClasses where classId='$actClassId' limit 1");
        if ($obj2=MYSQLi_FETCH_OBJECT($erg2))
        {
        	 treeStatusOut("Aktualisiere ".$obj->id);
        	 QUERY("UPDATE featureInstances set featureClassesId='$obj2->id',name='$obj2->name' where id='$obj->id' limit 1");
        }
    }
    

    treeStatusOut("");
    echo "<script>top.frames[0].document.getElementById('treeUpdateControl').innerHTML='..';</script>";

    return $message;
}

function cleanUp()
{
	global $MAX_LOG_ENTRIES;
	global $MAX_TRACE_ENTRIES;
	
	trace("cleanUp start",1);
	
	$start = time();
	
	// Journale aufr㴭en
  $erg = QUERY("select count(*) from udpcommandlog");
  $row=MYSQLi_FETCH_ROW($erg);
  $diff = $row[0]-$MAX_LOG_ENTRIES;
  if ($diff>0)
  {
  	 $sql = "DELETE from udpcommandlog order by id limit $diff";
  	 echo $sql."\n";
  	 QUERY($sql);
  }

  $erg = QUERY("select count(*) from udpdatalog");
  $row=MYSQLi_FETCH_ROW($erg);
  $diff = $row[0]-$MAX_LOG_ENTRIES;
  if ($diff>0)
  {
  	 $sql = "DELETE from udpdatalog  order by id limit $diff";
  	 echo $sql."\n";
  	 QUERY($sql);
  }

  $erg = QUERY("select count(*) from udphelper");
  $row=MYSQLi_FETCH_ROW($erg);
  $diff = $row[0]-$MAX_LOG_ENTRIES;
  if ($diff>0)
  {
  	 $sql = "DELETE from udphelper order by id limit $diff";
  	 echo $sql."\n";
  	 QUERY($sql);
  }

  // Trace aufr㴭en
  $erg = QUERY("select count(*) from trace");
  $row=MYSQLi_FETCH_ROW($erg);
  $diff = $row[0]-$MAX_TRACE_ENTRIES;
  if ($diff>0)
  {
  	 $sql = "DELETE from trace order by id limit $diff";
  	 echo $sql."\n";
  	 QUERY($sql);
  }
  
  exec("rm /var/lib/php5/sess_0*");
  exec("rm /var/lib/php5/sess_1*");
  exec("rm /var/lib/php5/sess_2*");
  exec("rm /var/lib/php5/sess_3*");
  exec("rm /var/lib/php5/sess_4*");
  exec("rm /var/lib/php5/sess_5*");
  exec("rm /var/lib/php5/sess_6*");
  exec("rm /var/lib/php5/sess_7*");
  exec("rm /var/lib/php5/sess_8*");
  exec("rm /var/lib/php5/sess_9*");
  exec("rm /var/lib/php5/sess_a*");
  exec("rm /var/lib/php5/sess_b*");
  exec("rm /var/lib/php5/sess_c*");
  exec("rm /var/lib/php5/sess_d*");
  exec("rm /var/lib/php5/sess_e*");
  exec("rm /var/lib/php5/sess_f*");
  exec("rm /var/lib/php5/sess_g*");
  exec("rm /var/lib/php5/sess_h*");
  exec("rm /var/lib/php5/sess_i*");
  exec("rm /var/lib/php5/sess_j*");
  exec("rm /var/lib/php5/sess_k*");
  exec("rm /var/lib/php5/sess_l*");
  exec("rm /var/lib/php5/sess_m*");
  exec("rm /var/lib/php5/sess_n*");
  exec("rm /var/lib/php5/sess_o*");
  exec("rm /var/lib/php5/sess_p*");
  exec("rm /var/lib/php5/sess_q*");
  exec("rm /var/lib/php5/sess_r*");
  exec("rm /var/lib/php5/sess_s*");
  exec("rm /var/lib/php5/sess_t*");
  exec("rm /var/lib/php5/sess_u*");
  exec("rm /var/lib/php5/sess_v*");
  exec("rm /var/lib/php5/sess_w*");
  exec("rm /var/lib/php5/sess_x*");
  exec("rm /var/lib/php5/sess_y*");
  exec("rm /var/lib/php5/sess_z*");
  exec("rm /var/lib/php5/sess_*");
  
  exec("find /var/log/ -name \"*.gz\"|xargs rm");
  exec("find /var/log/ -name \"*.1\"|xargs rm");
  exec("find /var/log/ -name \"*.old\"|xargs rm");


  /*trace("repair and optimize tables",1);

  $alltables = QUERY("SHOW TABLES");
  while ($table = MYSQLi_FETCH_assoc($alltables))
  {
     foreach ($table as $db => $tablename)
     {
     	   trace("repair table $tablename",1);  
         QUERY("REPAIR TABLE $tablename QUICK");
     	   trace("optimize table $tablename",1);  
         QUERY("OPTIMIZE TABLE $tablename");
     }
  }*/

	trace("cleanUp ende",1);
}

function wordToBytes($in)
{
    $result[0] = $in & 0xff;
    $result[1] = ($in >> 8) & 0xff;
    return $result;
}

function bytesToWord($dataArray, &$startPos)
{
    $result = 0;
    $result += $dataArray[$startPos];
    $result += $dataArray[$startPos +1] * 256;
    $startPos += 2;
    return $result;
}

function dWordToBytes($in)
{
	  $in = (float)$in;
    $result[0] = $in & 0xff;
    $result[1] = ($in >> 8) & 0xff;
    $result[2] = ($in >> 16) & 0xff;
    $result[3] = ($in >> 24) & 0xff;
    return $result;
}

function bytesToDword($dataArray, &$startPos)
{
    $result = 0;
    $result += $dataArray[$startPos];
    $result += $dataArray[$startPos +1] * 256;
    $result += $dataArray[$startPos +2] * 65536;
    $result += $dataArray[$startPos +3] * 16777216;
    $startPos += 4;
    return $result;
}

function stringToBytes($string)
{
    $i = 0;
    for (; $i < strlen($string); $i++)
    {
        $result[$i] = ord(substr($string, $i, 1));
    }
    $result[$i] = 0;
    return $result;
}

function wordListToBytes($wordList)
{
    if ($wordList == "")
        return "";

    $i = 0;
    $parts = explode(";", $wordList);
    foreach ($parts as $value)
    {
        $elements = explode(",", $value);
        $result[$i++] = $elements[0];
        $result[$i++] = $elements[1];
    }
    return $result;
}

function bytesToString($dataArray, &$startPos, $dataLength)
{
    $result = "";
    $start = $startPos;
    $end = $startPos + $dataLength;
    for ($i = $start; $i < $end; $i++)
    {
        $startPos++;

        if ($dataArray[$i] == 0)
            break;
        else
            $result .= chr($dataArray[$i]);
    }
    return $result;
}

function blobToCrc32($dataArray, &$startPos, $dataLength)
{
    $pos = 0;
    $start = $startPos;
    $end = $startPos + $dataLength;
    for ($i = $start; $i < $end; $i++)
    {
        $result[$pos++] = $dataArray[$i];
    }

    $result = implode('', $result);
    return crc32($result);
}

function bytesToWordList($dataArray, &$startPos, $dataLength)
{
    $result = "";
    $start = $startPos;
    $end = $startPos + $dataLength;
    for ($i = $start; $i < $end; $i += 2)
    {
        $startPos += 2;
        if ($result != "")
            $result .= ";";
        $result .= $dataArray[$i] . "," . $dataArray[$i +1];
    }
    return $result;
}

function bytesToDebugString($data)
{
    $result = "";
    foreach ((array) $data as $value)
    {
        if ($result != "")
            $result .= ",";
        $result .= "0x" . decHex((float)$value);
    }
    return $result;
}

// deprecated -> verbessert durch join und weglassen einiger Daten im Ergebnis
function getBusSubscriberDataOld($objectId)
{
    global $MY_OBJECT_ID;
    global $BROADCAST_OBJECT_ID;
    global $CONTROLLER_CLASS_ID;

    $result = new stdClass();
    $result->objectId = $objectId;

    if ($objectId == $MY_OBJECT_ID)
    {
        $result->featureClassName = "Homeserver";
        $result->featureInstanceName = "Webcontrol";
        $result->debugStr = "Homeserver Webcontrol";
    } 
    elseif ($objectId == $BROADCAST_OBJECT_ID)
    {
        $result->featureClassName = "BROADCAST";
        $result->debugStr = "BROADCAST";
    } 
    else
            if (getClassId($objectId) == $CONTROLLER_CLASS_ID)
            {
                $erg = QUERY("select SQL_CACHE name from controller where objectId='$objectId' limit 1");
                if ($obj = MYSQLi_FETCH_OBJECT($erg))
                {
                    $result->featureClassName = "Controller";
                    $result->featureInstanceName = $obj->name;
                    $result->debugStr = "Controller " . $obj->name;
                } else
                {
                    $result->featureClassName = "Controller";
                    $result->featureInstanceName = "Unbekannt";
                    $result->debugStr = "Controller unbekannt";
                }
            } else
            {
                $erg = QUERY("select SQL_CACHE * from featureInstances where objectId='$objectId' limit 1");
                if ($obj = MYSQLi_FETCH_OBJECT($erg))
                {
                    $result->featureInstanceObject = $obj;
                    $featureInstanceName = $obj->name;

                    $erg2 = QUERY("select SQL_CACHE * from featureClasses where id='$obj->featureClassesId' limit 1");
                    if ($obj2 = MYSQLi_FETCH_OBJECT($erg2))
                    {
                        $result->featureObj = $obj2;
                        $featureClassName = $obj2->name;
                    } else
                        $featureClassName = "Unbekannte FeatureClass classesId =  " . $obj->featureClassesId;

                    $erg2 = QUERY("select SQL_CACHE * from controller where id='$obj->controllerId' limit 1");
                    if ($obj2 = MYSQLi_FETCH_OBJECT($erg2))
                    {
                        $result->controllerObj = $obj2;
                        $controllerName = $obj2->name;
                    } else
                        $controllerName = "Unbekannter Controller. controllerId = " . $obj->controllerId;

                    $erg2 = QUERY("select SQL_CACHE * from roomFeatures where featureInstanceId='$obj->id' limit 1");
                    if ($obj2 = MYSQLi_FETCH_OBJECT($erg2))
                    {
                        $erg3 = QUERY("select SQL_CACHE id, name from rooms where id='$obj2->roomId' limit 1");
                        if ($obj3 = MYSQLi_FETCH_OBJECT($erg3))
                        {
                            $result->roomObj = $obj3;
                            $roomName = $obj3->name;
                        } else
                            $roomName = "Fehler: Raum nicht gefunden. ID = " . $obj2->roomId;
                    } else
                        $roomName = "Keine Raumzuordnung. featureInstanceId = " . $obj->id;
                } else
                    $featureInstanceName = "Unbekannte Featureinstanz objectId =  " . $objectId;

                $result->debugStr = "Raum: " . $roomName. " -> Controller: " . $controllerName . " -> Feature: " . i18n($featureClassName) . " " . i18n($featureInstanceName);
            }

    return $result;
}

function getBusSubscriberData($objectId)
{
    global $MY_OBJECT_ID;
    global $BROADCAST_OBJECT_ID;
    global $CONTROLLER_CLASS_ID;

    $result = new stdClass();
    $result->objectId = $objectId;

    if ($objectId == $MY_OBJECT_ID)
    {
      $result->featureClassName = "Homeserver";
      $result->featureInstanceName = "Webcontrol";
      $result->debugStr = "Homeserver Webcontrol";
    } 
    else if ($objectId == $BROADCAST_OBJECT_ID)
    {
      $result->featureClassName = "BROADCAST";
      $result->debugStr = "BROADCAST";
    } 
    else if (getClassId($objectId) == $CONTROLLER_CLASS_ID)
    {
      $erg = QUERY("select SQL_CACHE name from controller where objectId='$objectId' limit 1");
      if ($obj = MYSQLi_FETCH_OBJECT($erg))
      {
        $result->featureClassName = "Controller";
        $result->featureInstanceName = $obj->name;
        $result->debugStr = "Controller " . $obj->name;
      } 
      else
      {
        $result->featureClassName = "Controller";
        $result->featureInstanceName = "Unbekannt";
        $result->debugStr = "Controller unbekannt";
      }
    } 
    else
    {
       $erg = QUERY("select 
       featureInstances.id as featureInstanceId, featureInstances.controllerId as featureInstanceControllerId, featureInstances.name as featureInstanceName,featureInstances.featureClassesId as featureInstanceClassesId,
       featureClasses.id as featureClassesId, featureClasses.classId as featureClassId, featureClasses.name as featureClassesName,
       controller.id as controllerId, controller.objectId as controllerObjectId, controller.name as controllerName,
       rooms.id as roomId, rooms.name as roomName
       from featureInstances 
       left join featureClasses on (featureClasses.id=featureInstances.featureClassesId)
       left join controller on (controller.id=featureInstances.controllerId)
       left join roomFeatures on (roomFeatures.featureInstanceId = featureInstances.id)
       left join rooms on (rooms.id = roomFeatures.roomId)
       where featureInstances.objectId='$objectId' limit 1");
       if ($obj = MYSQLi_FETCH_OBJECT($erg))
       {
         $featureInstanceObject = new stdClass();
         $featureInstanceObject->id=$obj->featureInstanceId;
         $featureInstanceObject->controllerId=$obj->featureInstanceControllerId;
         $featureInstanceObject->featureClassesId=$obj->featureInstanceClassesId;
         $featureInstanceObject->objectId=$objectId;
         $featureInstanceObject->name=$obj->featureInstanceName;
         $result->featureInstanceObject = $featureInstanceObject;
         
         $featureInstanceName = $obj->featureInstanceName;

         $featureObj = new stdClass();
         $featureObj->id = $obj->featureClassesId;
         $featureObj->classId = $obj->featureClassId;
         $featureObj->name = $obj->featureClassesName;
         $result->featureObj = $featureObj;
         
         $featureClassName = $obj->featureClassesName;
         if ($featureClassName=="NULL") $featureClassName = "Unbekannte FeatureClass classesId =  " . $obj->featureInstanceClassesId;

         $controllerObj = new stdClass();
         $controllerObj->id = $obj->controllerId;
         $controllerObj->objectId = $obj->controllerObjectId;
         $controllerObj->name = $obj->controllerName;
         $result->controllerObj = $controllerObj;
         $controllerName = $obj->controllerName;
         if ($controllerName=="NULL") $controllerName = "Unbekannter Controller. controllerId = " . $obj->featureInstanceControllerId;
         
         $roomObj = new stdClass();
         $roomObj->id = $obj->roomId;
         $roomObj->name = $obj->roomName;
         $result->roomObj = $roomObj;
         if ($obj->roomName=="NULL") $roomName = "";
         else $roomName=$obj->roomName;
       }
       else $featureInstanceName = "Unbekannte Featureinstanz objectId =  " . $objectId;

       $result->debugStr = "Raum: " . $roomName. " -> Controller: " . $controllerName . " -> Feature: " . i18n($featureClassName) . " " . i18n($featureInstanceName);
    }

    return $result;
}

function getFunctionData($featureClassesId, $functionId, $datagramm, $dataPos, $dataLength)
{
    global $CONTROLLER_CLASSES_ID;
    global $BROADCAST_OBJECT_ID;

    $RULE_DATA_FUNCTION_ID = 134;

    $erg = QUERY("select featureFunctions.id,featureFunctions.featureClassesId,featureFunctions.type,featureFunctions.name,featureFunctions.functionId,
                         featureClasses.classId 
                  from featureFunctions 
                  join featureClasses on(featureClasses.id = featureFunctions.featureClassesId) 
                  where featureClassesId='$featureClassesId' and functionId='$functionId' limit 1");
    if ($obj = MYSQLi_FETCH_OBJECT($erg))
    {
        $obj->functionDebugStr = $obj->name;

        $paramCount = 0;
        $erg2 = QUERY("select id,name,type from featureFunctionParams where featureFunctionId='$obj->id' order by id");
        while ($obj2 = MYSQLi_FETCH_OBJECT($erg2))
        {
            if ($paramsStr != "") $paramsStr .= ", ";
            $paramsStr .= $obj2->name . " = ";

            if ($obj2->type == "BYTE" || $obj2->type == "BITMASK") $dataValue = $datagramm[$dataPos++];
            else if ($obj2->type == "WORD" || $obj2->type == "WEEKTIME") $dataValue = bytesToWord($datagramm, $dataPos);
            else if ($obj2->type == "DWORD") $dataValue = bytesToDword($datagramm, $dataPos);
            else if ($obj2->type == "STRING") $dataValue = bytesToString($datagramm, $dataPos, $dataLength);
            else if ($obj2->type == "WORDLIST") $dataValue = bytesToWordList($datagramm, $dataPos, $dataLength);
            else if ($obj2->type == "BLOB")
            {
               if ($featureClassesId == $CONTROLLER_CLASSES_ID && $functionId == $RULE_DATA_FUNCTION_ID) $dataValue = bytesToByteList($datagramm, $dataPos, $dataLength -2);
               else $dataValue = blobToCrc32($datagramm, $dataPos, $dataLength);
            } 
            else if ($obj2->type == "ENUM")
            {
              $dataValue = $datagramm[$dataPos++];
              $erg3 = QUERY("select id,paramId,name,value from featureFunctionEnums where featureFunctionId='$obj->id' and value='$dataValue' limit 1");
              if ($obj3 = MYSQLi_FETCH_OBJECT($erg3))
              {
                //$obj2->featureFunctionEnumObj = $obj3;
                $paramsStr .= $obj3->name . " / ";
                $obj2->dataValueName=$obj3->name;
              } 
              else $paramsStr .= "Unbekannter Enumwert ($dataValue) ";
            } 
            else die("Unbekannter Datentyp $obj2->type");

            $obj2->dataValue = $dataValue;
            $paramsStr .= $dataValue;
            $featureFunctionParamsObj[$paramCount++] = $obj2;
        }

        $obj->paramData = $featureFunctionParamsObj;
        $obj->paramsDebugStr = $paramsStr;
    } else $obj->functionDebugStr = "Unbekannte Funktion featureClassesId = $featureClassesId, functionId = $functionId";
    
    return $obj;
}

function getFeatureClassesId($objectId)
{
    $classId = getClassId($objectId);

    $erg = QUERY("select SQL_CACHE id from featureClasses where classId='$classId' limit 1");
    if ($row = MYSQLi_FETCH_ROW($erg))
        return $row[0];
    
    echo "getFeatureClassesId: Unbekannte classId von objectId $objectId. ClassId = " . $classId." <br>";
    return -1;
}

function getControllerId($objectId)
{
    $erg = QUERY("select SQL_CACHE id from controller where objectId='$objectId' limit 1");
    if ($row = MYSQLi_FETCH_ROW($erg))
        return $row[0];
        
    echo "getControllerId: Unbekannte objectId $objectId! <br>";
    return 0;
}

function getController($objectId)
{
    $erg = QUERY("select SQL_CACHE * from controller where objectId='$objectId' limit 1");
    if ($obj = MYSQLi_FETCH_OBJECT($erg)) return $obj;
        
    echo "getControllerId: Unbekannte objectId $objectId! <br>";
    return 0;
}

/*
 ObjectId:
 Byte0=instanceId,
 Byte1=classId,
 Byte2=address,
 Byte3=group
 */

function getFormatedObjectId($objectId)
{
    return "0x" . decHex((float)$objectId) . " (deviceId: " . getDeviceId($objectId) . " classId: " . getClassId($objectId) . " instanceId: " .getInstanceId($objectId).") Dezimal: $objectId";
}

function getObjectId($deviceId, $classId, $instanceId)
{
    return ($deviceId << 16) + ($classId << 8) + $instanceId;
}

function getBootloaderObjectId($objectId)
{
    global $BOOTLOADER_INSTANCE_ID;
    return getObjectId(getDeviceId($objectId), getClassId($objectId), $BOOTLOADER_INSTANCE_ID);
}

function getObjectIdForInstanceId($instanceId)
{
  $erg = QUERY("select objectId from featureInstances where id='$instanceId' limit 1");
  if ($row=MYSQLi_FETCH_ROW($erg)) return $row[0];
}

function getClassId($objectId)
{
	  $objectId = (float)$objectId;
    return ($objectId >> 8) & 0xff;
}

function getInstanceId($objectId)
{
	 $objectId = (float)$objectId;
    return ($objectId) & 0xff;
}

function getDeviceId($objectId)
{
	  $objectId = (float)$objectId;
    return (($objectId >> 24) & 0xff) * 256 + (($objectId >> 16) & 0xff);
}

function bytesToByteList($dataArray, $startPos, $dataLength)
{
    $result = "";
    for ($i = $startPos; $i < $startPos + $dataLength; $i++)
    {
        if ($result != "")
            $result .= ",";
        $result .= $dataArray[$i];
    }
    return $result;
}

function setUserData($key, $value)
{
	$value=query_real_escape_string($value);
	QUERY("REPLACE into userData (userKey,userValue) values('$key','$value')");
}


function getUserData($key)
{
	$erg = QUERY("select userValue from userData where userKey='$key' limit 1");
	if ($row=MYSQLi_FETCH_ROW($erg)) return $row[0];
	return NULL;
}

function userJournal($function, $parameter="")
{
  $time=time();
  $function=query_real_escape_string($function);
  $parameter=query_real_escape_string($parameter);

  QUERY("INSERT into udpCommandLog (time,  sender,  function,  params)
         values('$time','UserPlugin','$function','$parameter')");
}

function sendEmail($receiver, $subject, $message, $from="reply@domain.com")
{
   $message = "<html><body>".$message."</body></html>";
   $header = "From:$from \r\n";
   $header .= "MIME-Version: 1.0\r\n";
   $header .= "Content-type: text/html\r\n";
   
   $result = mail ($receiver,$subject,$message,$header);
   return $result;
}

function getLastReceivedData($objectId, $functionName)
{
	// Zuletzt empfangene Daten von diesem Sender
  $erg = QUERY("select functionData from lastReceived  where senderObj='$objectId' and function='$functionName' order by id desc limit 1");
  if ($obj=MYSQLi_FETCH_OBJECT($erg))
  {
  	$result=unserialize($obj->functionData)->paramData;
  	$elCount = count((array)$result);
       	 
    for ($i=0;$i<$elCount;$i++)
    {
    	 if ($result[$i]->type=="ENUM") $resultArray[$result[$i]->name]=$result[$i]->dataValueName;
     	 else $resultArray[$result[$i]->name]=$result[$i]->dataValue;
    }

    return $resultArray;
  }
  return "";
}

function whichIsLastReceivedEvent($objectId, $eventNames)
{
	$parts = explode(",",$eventNames);
	
	$or="1=2 ";
	foreach((array)$parts as $events)
	{
		$or.="or function='$events' ";
	}
	
	// Zuletzt empfangene Daten von diesem Sender
  $erg = QUERY("select function from lastReceived  where senderObj='$objectId' and ($or) order by id desc limit 1");
  if ($obj=MYSQLi_FETCH_OBJECT($erg)) return $obj->function;
  return "";
}
?>
