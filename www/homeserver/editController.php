<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

header( 'Content-Encoding: none; ' );//disable apache compressed
ini_set('max_execution_time', 300);

// Methodenaufrufe
if ($action == "callMethod")
{
  $erg = QUERY("select max(id) from udpCommandLog");
  $row = MYSQL_FETCH_ROW($erg);
  $minId = $row[0];
  
  $erg = QUERY("select objectId from controller where id='$id' limit 1");
  if ($obj = MYSQL_FETCH_OBJECT($erg))
  {
    $obj->objectId = $obj->objectId;
    $message = "<script>document.write(\"<iframe style='position:relative;left:0px;top:0px' src='specificJournal.php?objectId=$obj->objectId&minId=$minId' width='97%' height='55' frameborder=0 border=0></iframe>\");</script>";
  }
  
  $erg = QUERY("select id,name,type from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
    $param = "param" . $obj->id;
    
    if ($obj->type == "WEEKTIME")
    {
      $value = "param" . $obj->id . "Day";
      $day = $$value;
      $value = "param" . $obj->id . "Hour";
      $hour = $$value;
      $value = "param" . $obj->id . "Minute";
      $minute = $$value;
      $paramData[trim($obj->name)] = toWeekTime($day, $hour, $minute);
    }
    else
      $paramData[trim($obj->name)] = $$param;
    if ($obj->name == "deviceId" && ($$param > 32767))
      showMessage("Fehler: Die DeviceID darf nicht größer als 32767 sein");
  }
  
  callInstanceMethodForObjectId($objectId, $featureFunctionId, $paramData);
  $waitForId = $minId + 2;
  
  // Nach dem Setzen der Konfiguration lesen wir direkt die Konfiguration neu aus, damit sie in lastReceived steht
  $erg = QUERY("select name from featureFunctions where id='$featureFunctionId' limit 1");
  $row = MYSQL_FETCH_ROW($erg);
  if ($row[0] == "setConfiguration")
  {
    sleepMs(300);
    callObjectMethodByName($objectId, "getConfiguration");
    waitForObjectResultByName($objectId, 5, "Configuration", $lastLogId);
    $waitForId += 2;
  }
  
  if ($featureFunctionId == getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "reset"))
  {
    /*showMessageNonExit("Reset wird durchgeführt und Controllerstatus aktualisiert....");
    
    flushIt();
    sleep(3);
    updateControllerStatus();
    echo "<script>location='editController.php?id=$id'</script>";
    exit();
    */
  }
  else if (substr($row[0], 0, 3) == "get")
    waitForCommandId($waitForId);
}
else if ($action == "recover")
{
  $debugMe = 1;
  
  $erg = QUERY("select id from featureFunctions where featureClassesId='$CONTROLLER_CLASSES_ID' and name='getConfiguration' limit 1");
  $row = MYSQL_FETCH_ROW($erg);
  $controllerConfigFktId = $row[0];
  
  $digitalPortClassesId = getClassesIdByName("DigitalPort");
  
  $erg = QUERY("select objectId from controller where id='$id' limit 1");
  if ($row = MYSQL_FETCH_ROW($erg))
  {
    $controllerObjectId = $row[0];
    
    $erg = QUERY("select configuration from recovery where objectId='$controllerObjectId' limit 1");
    if ($row = MYSQL_FETCH_ROW($erg))
    {
      $obj = unserialize($row[0]);
      
      // Zuerst schauen ob sich an der Controllerconfiguration was geändert hat
      $resetRelevantChanges = 0;
      
      callObjectMethodByName($controllerObjectId, "getConfiguration");
      $result = waitForObjectResultByName($controllerObjectId, 5, "Configuration", $lastLogId, "funtionDataParams", 0);
      if ($result != - 1)
      {
        $changes = 0;
        
        unset($actData);
        foreach ( $result as $key => $valueObj )
        {
          $actData[$valueObj->name] = $valueObj->dataValue;
        }
        
        unset($data);
        foreach ( $obj as $key => $valueObj )
        {
          $data[$valueObj->name] = $valueObj->dataValue;
          if ($actData[$valueObj->name] != $valueObj->dataValue)
          {
            if ($debugMe == 1)
              echo $valueObj->name . " von " . $actData[$valueObj->name] . " -> " . $valueObj->dataValue . "<br>";
            $resetRelevantChanges = 1;
            $changes = 1;
          }
        }
        
        if ($changes == 1)
          callObjectMethodByName($controllerObjectId, "setConfiguration", $data);
      }
      else
        $message = "getConfiguration nicht erfolgreich";
      
      $erg = QUERY("select * from featureInstances where controllerId='$id' and featureClassesId='$digitalPortClassesId'") or die(MYSQL_ERROR());
      while ( $obj = MYSQL_FETCH_OBJECT($erg) )
      {
        $erg2 = QUERY("select configuration from recovery where objectId='$obj->objectId' limit 1");
        if ($row2 = MYSQL_FETCH_ROW($erg2))
        {
          $configObj = unserialize($row2[0]);
          
          callObjectMethodByName($obj->objectId, "getConfiguration");
          $result = waitForObjectResultByName($obj->objectId, 5, "Configuration", $lastLogId, "funtionDataParams", 0);
          if ($result != - 1)
          {
            $changes = 0;
            
            unset($actData);
            foreach ( $result as $key => $valueObj )
            {
              $actData[$valueObj->name] = $valueObj->dataValue;
            }
            
            unset($data);
            foreach ( $configObj as $key => $valueObj )
            {
              $data[$valueObj->name] = $valueObj->dataValue;
              if ($actData[$valueObj->name] != $valueObj->dataValue)
              {
                if ($debugMe == 1)
                  echo $valueObj->name . " von " . $actData[$valueObj->name] . " -> " . $valueObj->dataValue . "<br>";
                $resetRelevantChanges = 1;
                $changes = 1;
              }
            }
            
            if ($changes == 1)
              callObjectMethodByName($obj->objectId, "setConfiguration", $data);
          }
          else
            $message = "getConfiguration nicht erfolgreich";
        }
        else if ($debugMe == 1)
          "Keine gespeicherte Konfiguration zu " . $obj->name . "<br>";
      }
      
      // bei zuvoriger Änderung -> Reset 
      if ($resetRelevantChanges == 1)
      {
        callObjectMethodByName($controllerObjectId, "reset");
        sleep(5);
        callObjectMethodByName($controllerObjectId, "ping");
        $result = waitForObjectResultByName($controllerObjectId, 5, "pong", $lastLogId, "funtionDataParams", 0);
        if ($result == - 1)
        {
          callObjectMethodByName($controllerObjectId, "ping");
          $result = waitForObjectResultByName($controllerObjectId, 5, "pong", $lastLogId);
        }
      }
      
      // Dann der Rest
      $erg = QUERY("select * from featureInstances where controllerId='$id' and featureClassesId!='$digitalPortClassesId'") or die(MYSQL_ERROR());
      while ( $obj = MYSQL_FETCH_OBJECT($erg) )
      {
        $erg2 = QUERY("select configuration from recovery where objectId='$obj->objectId' limit 1");
        if ($row2 = MYSQL_FETCH_ROW($erg2))
        {
          $configObj = unserialize($row2[0]);
          
          callObjectMethodByName($obj->objectId, "getConfiguration");
          $result = waitForObjectResultByName($obj->objectId, 5, "Configuration", $lastLogId, "funtionDataParams", 0);
          if ($result != - 1)
          {
            $changes = 0;
            
            unset($actData);
            foreach ( $result as $key => $valueObj )
            {
              $actData[$valueObj->name] = $valueObj->dataValue;
            }
            
            unset($data);
            foreach ( $configObj as $key => $valueObj )
            {
              $data[$valueObj->name] = $valueObj->dataValue;
              
              if ($actData[$valueObj->name] != $valueObj->dataValue)
              {
                if ($debugMe == 1)
                  echo $valueObj->name . " von " . $actData[$valueObj->name] . " -> " . $valueObj->dataValue . "<br>";
                $changes = 1;
              }
            }
            
            if ($changes == 1)
              callObjectMethodByName($obj->objectId, "setConfiguration", $data);
          }
          else
            $message = "getConfiguration nicht erfolgreich";
        }
        else if ($debugMe == 1)
          "Keine gespeicherte Konfiguration zu " . $obj->name . "<br>";
      }
    }
    else
      $message = "Zu diesem Controller wurde bisher keine Konfiguration gespeichert";
  }
  else
    $message = "Fehler ObjectID nicht gefunden";
  
  if ($callback == 1)
  {
    sleep(3);
    echo "<script>location='recovery.php?action=recover&confirm=1&lastId=$id';</script>";
    exit();
  }
}
else if ($action == "restoreFactorySettings")
{
  if ($confirm == 1)
  {
    $tasterClassId = getClassIdByName("Taster");
    $ledClassId = getClassIdByName("Led");
    
    $UNUSED = 255;
    $erg = QUERY("select objectId from controller where id='$id' limit 1");
    if ($row = MYSQL_FETCH_ROW($erg))
    {
      $controllerObjectId = $row[0];
      callObjectMethodByName($controllerObjectId, "getModuleId");
      $result = waitForObjectResultByName($controllerObjectId, 5, "ModuleId", $lastLogId, "funtionDataParams", 0);
      if ($result != - 1)
      {
        foreach ( $result as $key => $valueObj )
        {
          $moduleId[$valueObj->name] = $valueObj->dataValue;
        }
        callObjectMethodByName($controllerObjectId, "getConfiguration");
        $result = waitForObjectResultByName($controllerObjectId, 5, "Configuration", $lastLogId, "funtionDataParams", 0);
        if ($result != - 1)
        {
          foreach ( $result as $key => $valueObj )
          {
            $configuration[$valueObj->name] = $valueObj->dataValue;
          }
          
          if ($moduleId["firmwareId"] == getFunctionParamEnumValueByName($controllerObjectId, "ModuleId", "firmwareId", "AR8"))
          {
            $message = "AR8";
            $erg = QUERY("select * from featureInstances where controllerId='$id'") or die(MYSQL_ERROR());
            while ( $obj = MYSQL_FETCH_OBJECT($erg) )
            {
              if ($obj->featureClassesId == getClassesIdByName("DigitalPort"))
              {
                if (getInstanceId($obj->objectId) == 96) // PortF
                {
                  $data["pin0"] = $tasterClassId;
                  $data["pin1"] = $tasterClassId;
                  $data["pin2"] = $tasterClassId;
                  $data["pin3"] = $tasterClassId;
                  $data["pin4"] = $tasterClassId;
                  $data["pin5"] = $tasterClassId;
                  $data["pin6"] = $tasterClassId;
                  $data["pin7"] = $tasterClassId;
                  callObjectMethodByName($obj->objectId, "setConfiguration", $data);
                }
                else
                {
                  $data["pin0"] = $UNUSED;
                  $data["pin1"] = $UNUSED;
                  $data["pin2"] = $UNUSED;
                  $data["pin3"] = $UNUSED;
                  $data["pin4"] = $UNUSED;
                  $data["pin5"] = $UNUSED;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                  callObjectMethodByName($obj->objectId, "setConfiguration", $data);
                }
              }
              else if ($obj->featureClassesId == getClassesIdByName("Dimmer"))
              {
                $data = array (
                    "mode" => 0,
                    "fadingTime" => 12,
                    "dimmingTime" => 60 
                );
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
                $slot[getInstanceId($obj->objectId)] = $obj->objectId;
              }
              else if ($obj->featureClassesId == getClassesIdByName("Rollladen"))
              {
                $data = array (
                    "closeTime" => 10,
                    "openTime" => 12 
                );
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
                $slot[getInstanceId($obj->objectId)] = $obj->objectId;
              }
              else if ($obj->featureClassesId == getClassesIdByName("Schalter"))
              {
                $slot[getInstanceId($obj->objectId)] = $obj->objectId;
              }
            }
            callObjectMethodByName($controllerObjectId, "reset");
            waitForObjectEventByName($controllerObjectId, 5, "evStarted", $lastLogId);
            //updateControllerStatus();
			      callObjectMethodByName($controllerObjectId, "getRemoteObjects");
            waitForObjectResultByName($controllerObjectId, 5, "RemoteObjects", $lastLogId);
			
            $erg = QUERY("select * from featureInstances where controllerId='$id'") or die(MYSQL_ERROR());
            while ( $obj = MYSQL_FETCH_OBJECT($erg) )
            {
              if ($obj->featureClassesId == getClassesIdByName("Taster"))
              {
                $button[getInstanceId($obj->objectId) & 0xF] = $obj->objectId;
                $data["holdTimeout"] = 100;
                $data["waitForDoubleClickTimeout"] = 50;
                $data["eventMask"] = 129;
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
              }
            }
            // standard Regeln setzten
            $dataPos = 0;
            $rulesData[$dataPos++] = count($button); // Anzahl aller Regeln
            for($i = 1; $i <= count($button); $i++)
            {
              addDefaultGroupRuleData($button[$i], "evCovered", $slot[$i]);
            }
            $rulesData[$dataPos++] = 0; // 0 Terminierung
            

            unset($buffer);
            for($i = 0; $i < $dataPos; $i++)
            {
              $buffer .= chr($rulesData[$i]);
            }
            
            $data["offset"] = 0;
            $data["data"] = $buffer;
            callObjectMethodByName($controllerObjectId, "writeRules", $data);
            $result = waitForObjectResultByName($controllerObjectId, 5, "MemoryStatus", $lastLogId);
            $memoryStatus = getResultDataValueByName("status", $result);
            $memoryStatusOk = getFunctionParamEnumValueByName($controllerObjectId, "MemoryStatus", "status", "OK");
            if ($memoryStatus != $memoryStatusOk)
              die("Controller hat Fehler gemeldet im MemoryStatus -> " . $memoryStatus);
          }
          else if ($moduleId["firmwareId"] == getFunctionParamEnumValueByName($controllerObjectId, "ModuleId", "firmwareId", "MS6"))
          {
            $message = "MS6";
            $configuration["logicalButtonMask"] = 1;
            callObjectMethodByName($controllerObjectId, "setConfiguration", $configuration);
            
            $erg = QUERY("select * from featureInstances where controllerId='$id'") or die(MYSQL_ERROR());
            while ( $obj = MYSQL_FETCH_OBJECT($erg) )
            {
              if ($obj->featureClassesId == getClassesIdByName("DigitalPort"))
              {
                if (getInstanceId($obj->objectId) == 16) // PortA
                {
                  $data["pin0"] = $UNUSED;
                  $data["pin1"] = $UNUSED;
                  $data["pin2"] = $UNUSED;
                  $data["pin3"] = $UNUSED;
                  $data["pin4"] = $UNUSED;
                  $data["pin5"] = $UNUSED;
                  $data["pin6"] = $tasterClassId;
                  $data["pin7"] = $tasterClassId;
                }
                if (getInstanceId($obj->objectId) == 32) // PortB
                {
                  $data["pin0"] = $tasterClassId;
                  $data["pin1"] = $tasterClassId;
                  $data["pin2"] = $tasterClassId;
                  $data["pin3"] = $tasterClassId;
                  $data["pin4"] = $UNUSED;
                  $data["pin5"] = $UNUSED;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                }
                if (getInstanceId($obj->objectId) == 48) // PortC
                {
                  $data["pin0"] = $ledClassId;
                  $data["pin1"] = $ledClassId;
                  $data["pin2"] = $ledClassId;
                  $data["pin3"] = $ledClassId;
                  $data["pin4"] = $ledClassId;
                  $data["pin5"] = $ledClassId;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                }
                if (getInstanceId($obj->objectId) == 64) // PortD
                {
                  $data["pin0"] = $UNUSED;
                  $data["pin1"] = $UNUSED;
                  $data["pin2"] = $UNUSED;
                  $data["pin3"] = $UNUSED;
                  $data["pin4"] = $UNUSED;
                  $data["pin5"] = $UNUSED;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                }
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
              }
            }
            callObjectMethodByName($controllerObjectId, "reset");
            waitForObjectEventByName($controllerObjectId, 5, "evStarted", $lastLogId);
            //updateControllerStatus();
			callObjectMethodByName($controllerObjectId, "getRemoteObjects");
            waitForObjectResultByName($controllerObjectId, 5, "RemoteObjects", $lastLogId);
            
            $erg = QUERY("select * from featureInstances where controllerId='$id'") or die(MYSQL_ERROR());
            while ( $obj = MYSQL_FETCH_OBJECT($erg) )
            {
              if ($obj->featureClassesId == getClassesIdByName("LogicalButton"))
              {
                $logicalButtonInstanceId = $obj->id;
                
                $logicalButtonConfiguration = array (
                    "button1" => 36,
                    "button2" => 23,
                    "button3" => 35,
                    "button4" => 24,
                    "button5" => 34,
                    "button6" => 33,
                    "button7" => 0,
                    "button8" => 0,
                    "led1" => 53,
                    "led2" => 54,
                    "led3" => 52,
                    "led4" => 49,
                    "led5" => 51,
                    "led6" => 50,
                    "led7" => 0,
                    "led8" => 0 
                );
                callObjectMethodByName($obj->objectId, "setConfiguration", $logicalButtonConfiguration);
              }
              else if ($obj->featureClassesId == getClassesIdByName("Taster"))
              {
                $data["holdTimeout"] = 100;
                $data["waitForDoubleClickTimeout"] = 50;
                $data["eventMask"] = 129;
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
              }
              else if ($obj->featureClassesId == getClassesIdByName("Led"))
              {
                $data["dimmOffset"] = 0;
				$data["minBrightness"] = 0;
				$data["timeBase"] = 1000;
				$data["options"] = 6;
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
              }
            }
            
            /*
            $ruleData["offset"] = 0;
            $ruleData["data"] = "\0";
            callObjectMethodByName($controllerObjectId, "writeRules", $ruleData);
            $result = waitForObjectResultByName($controllerObjectId, 5, "MemoryStatus", $lastLogId);
            $memoryStatus = getResultDataValueByName("status", $result);
            $memoryStatusOk = getFunctionParamEnumValueByName($controllerObjectId, "MemoryStatus", "status", "OK");
            if ($memoryStatus != $memoryStatusOk)
              die("Controller hat Fehler gemeldet im MemoryStatus -> " . $memoryStatus);
              */
            
            QUERY("UPDATE featureInstances set name='Taster 1' where controllerId='$id' and name='Taster 36' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 2' where controllerId='$id' and name='Taster 23' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 3' where controllerId='$id' and name='Taster 35' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 4' where controllerId='$id' and name='Taster 24' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 5' where controllerId='$id' and name='Taster 34' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 6' where controllerId='$id' and name='Taster 33' limit 1");
            QUERY("UPDATE featureInstances set name='Led 1' where controllerId='$id' and name='Led 53' limit 1");
            QUERY("UPDATE featureInstances set name='Led 2' where controllerId='$id' and name='Led 54' limit 1");
            QUERY("UPDATE featureInstances set name='Led 3' where controllerId='$id' and name='Led 52' limit 1");
            QUERY("UPDATE featureInstances set name='Led 4' where controllerId='$id' and name='Led 49' limit 1");
            QUERY("UPDATE featureInstances set name='Led 5' where controllerId='$id' and name='Led 51' limit 1");
            QUERY("UPDATE featureInstances set name='Led 6' where controllerId='$id' and name='Led 50' limit 1");
            QUERY("UPDATE featureInstances set name='LogicalButton' where controllerId='$id' and name='LogicalButton 1' limit 1");
            
            if ($logicalButtonInstanceId > 0)
            {
              $erg = MYSQL_QUERY("select classId,name from featureClasses where name='Taster' or name='Led' limit 2") or die(MYSQL_ERROR());
              while ( $row = MYSQL_FETCH_ROW($erg) )
              {
                if ($row[1] == "Taster")
                  $tasterClassId = $row[0];
                else if ($row[1] == "Led")
                  $ledClassId = $row[0];
              }
              
              // zugehörige featureInstances neufinden
              MYSQL_QUERY("update featureInstances set parentInstanceId='0' where parentInstanceId='$logicalButtonInstanceId'") or die(MYSQL_ERROR());
              
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 36);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 23);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 35);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 24);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 34);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 33);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 53);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 54);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 52);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 49);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 51);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 50);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
            }
          }
		  else if ($moduleId["firmwareId"] == getFunctionParamEnumValueByName($controllerObjectId, "ModuleId", "firmwareId", "SD6"))
          {
            $message = "SD6";
            $configuration["logicalButtonMask"] = 1;
            callObjectMethodByName($controllerObjectId, "setConfiguration", $configuration);
            
            $erg = QUERY("select * from featureInstances where controllerId='$id'") or die(MYSQL_ERROR());
            while ( $obj = MYSQL_FETCH_OBJECT($erg) )
            {
              if ($obj->featureClassesId == getClassesIdByName("DigitalPort"))
              {
                if (getInstanceId($obj->objectId) == 16) // PortA
                {
                  $data["pin0"] = $tasterClassId;
                  $data["pin1"] = $tasterClassId;
                  $data["pin2"] = $tasterClassId;
                  $data["pin3"] = $tasterClassId;
                  $data["pin4"] = $tasterClassId;
                  $data["pin5"] = $tasterClassId;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                }
                if (getInstanceId($obj->objectId) == 32) // PortB
                {
                  $data["pin0"] = $UNUSED;
                  $data["pin1"] = $UNUSED;
                  $data["pin2"] = $UNUSED;
                  $data["pin3"] = $UNUSED;
                  $data["pin4"] = $UNUSED;
                  $data["pin5"] = $UNUSED;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                }
                if (getInstanceId($obj->objectId) == 48) // PortC
                {
                  $data["pin0"] = $ledClassId;
                  $data["pin1"] = $ledClassId;
                  $data["pin2"] = $ledClassId;
                  $data["pin3"] = $ledClassId;
                  $data["pin4"] = $ledClassId;
                  $data["pin5"] = $ledClassId;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                }
                if (getInstanceId($obj->objectId) == 64) // PortD
                {
                  $data["pin0"] = $UNUSED;
                  $data["pin1"] = $UNUSED;
                  $data["pin2"] = $UNUSED;
                  $data["pin3"] = $UNUSED;
                  $data["pin4"] = $UNUSED;
                  $data["pin5"] = $UNUSED;
                  $data["pin6"] = $UNUSED;
                  $data["pin7"] = $UNUSED;
                }
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
              }
            }
            callObjectMethodByName($controllerObjectId, "reset");
            waitForObjectEventByName($controllerObjectId, 5, "evStarted", $lastLogId);
            //updateControllerStatus();
			callObjectMethodByName($controllerObjectId, "getRemoteObjects");
            waitForObjectResultByName($controllerObjectId, 5, "RemoteObjects", $lastLogId);
            
            $erg = QUERY("select * from featureInstances where controllerId='$id'") or die(MYSQL_ERROR());
            while ( $obj = MYSQL_FETCH_OBJECT($erg) )
            {
              if ($obj->featureClassesId == getClassesIdByName("LogicalButton"))
              {
                $logicalButtonInstanceId = $obj->id;
                
                $logicalButtonConfiguration = array (
                    "button1" => 17,
                    "button2" => 18,
                    "button3" => 19,
                    "button4" => 20,
                    "button5" => 21,
                    "button6" => 22,
                    "button7" => 0,
                    "button8" => 0,
                    "led1" => 49,
                    "led2" => 50,
                    "led3" => 51,
                    "led4" => 52,
                    "led5" => 53,
                    "led6" => 54,
                    "led7" => 0,
                    "led8" => 0 
                );
                callObjectMethodByName($obj->objectId, "setConfiguration", $logicalButtonConfiguration);
              }
              else if ($obj->featureClassesId == getClassesIdByName("Taster"))
              {
                $data["holdTimeout"] = 100;
                $data["waitForDoubleClickTimeout"] = 50;
                $data["eventMask"] = 129;
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
              }
              else if ($obj->featureClassesId == getClassesIdByName("Led"))
              {
                $data["dimmOffset"] = 0;
				$data["minBrightness"] = 0;
				$data["timeBase"] = 1000;
				$data["options"] = 7;
                callObjectMethodByName($obj->objectId, "setConfiguration", $data);
              }
            }
            $ruleData["offset"] = 0;
            $ruleData["data"] = "\0";
            callObjectMethodByName($controllerObjectId, "writeRules", $ruleData);
            $result = waitForObjectResultByName($controllerObjectId, 5, "MemoryStatus", $lastLogId);
            $memoryStatus = getResultDataValueByName("status", $result);
            $memoryStatusOk = getFunctionParamEnumValueByName($controllerObjectId, "MemoryStatus", "status", "OK");
            if ($memoryStatus != $memoryStatusOk)
              die("Controller hat Fehler gemeldet im MemoryStatus -> " . $memoryStatus);
            
            QUERY("UPDATE featureInstances set name='Taster 1' where controllerId='$id' and name='Taster 17' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 2' where controllerId='$id' and name='Taster 18' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 3' where controllerId='$id' and name='Taster 19' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 4' where controllerId='$id' and name='Taster 20' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 5' where controllerId='$id' and name='Taster 21' limit 1");
            QUERY("UPDATE featureInstances set name='Taster 6' where controllerId='$id' and name='Taster 22' limit 1");
            QUERY("UPDATE featureInstances set name='Led 1' where controllerId='$id' and name='Led 49' limit 1");
            QUERY("UPDATE featureInstances set name='Led 2' where controllerId='$id' and name='Led 50' limit 1");
            QUERY("UPDATE featureInstances set name='Led 3' where controllerId='$id' and name='Led 51' limit 1");
            QUERY("UPDATE featureInstances set name='Led 4' where controllerId='$id' and name='Led 52' limit 1");
            QUERY("UPDATE featureInstances set name='Led 5' where controllerId='$id' and name='Led 53' limit 1");
            QUERY("UPDATE featureInstances set name='Led 6' where controllerId='$id' and name='Led 54' limit 1");
            QUERY("UPDATE featureInstances set name='LogicalButton' where controllerId='$id' and name='LogicalButton 1' limit 1");
            
            if ($logicalButtonInstanceId > 0)
            {
              $erg = MYSQL_QUERY("select classId,name from featureClasses where name='Taster' or name='Led' limit 2") or die(MYSQL_ERROR());
              while ( $row = MYSQL_FETCH_ROW($erg) )
              {
                if ($row[1] == "Taster")
                  $tasterClassId = $row[0];
                else if ($row[1] == "Led")
                  $ledClassId = $row[0];
              }
              
              // zugehörige featureInstances neufinden
              MYSQL_QUERY("update featureInstances set parentInstanceId='0' where parentInstanceId='$logicalButtonInstanceId'") or die(MYSQL_ERROR());
              
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 17);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 18);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 19);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 20);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 21);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $tasterClassId, 22);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 49);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 50);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 51);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 52);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 53);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
              $childObjectId = getObjectId(getDeviceId($controllerObjectId), $ledClassId, 54);
              MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$logicalButtonInstanceId' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
            }
          }
          else
            $message = "FW-ID unbekannt";
        }
        else
          $message = "getConfiguration nicht erfolgreich";
      }
      else
        $message = "getModuleId nicht erfolgreich";
    }
    else
      $message = "Fehler ObjectID nicht gefunden";
  }
  else
    showMessage("Alle Einstellungen und Regeln werden auf den Auslieferzustand gesetzt.<br>Die Aktion kann nur über einen Wiederherstellungspunkt rückgängig gemacht werden !", "Achtung!", "editController.php?id=$id&action=restoreFactorySettings&confirm=1", "Zurücksetzen", "editController.php?id=$id", "Abbruch");
}
else if ($action == "loadConfigTemplate")
{
  if ($confirm == 1)
  {
    $erg = QUERY("select objectId from controller where id='$id' limit 1");
    $row = MYSQL_FETCH_ROW($erg);
    $controllerObjectId = $row[0];
    $deviceId = getDeviceId($controllerObjectId);
    $digitalPortClassId = getClassIdByName("DigitalPort");
    
    callObjectMethodByName($controllerObjectId, "getModuleId");
    $result = waitForObjectResultByName($controllerObjectId, 5, "ModuleId", $lastLogId, "funtionDataParams");
    foreach ( $result as $key => $valueObj )
    {
      $moduleId[$valueObj->name] = $valueObj->dataValue;
    }
    
    if ($moduleId["firmwareId"] == getFunctionParamEnumValueByName($controllerObjectId, "ModuleId", "firmwareId", "AR8"))
    {
      $myType = "AR8";
      $digitalPorts = array (
          "80",
          "96" 
      );
    }
    else if ($moduleId["firmwareId"] == getFunctionParamEnumValueByName($controllerObjectId, "ModuleId", "firmwareId", "MS6"))
    {
      $myType = "MS6";
      $digitalPorts = array (
          "16",
          "32",
          "48",
          "64" 
      );
    }
    else
      die("Fehler. Unbekannter Typ");
    
    $configFile = parse_ini_file($_FILES['userfile']['tmp_name'], TRUE);
    
    // Zuerst Controller konfigurieren
    $configData = $configFile["controller"];
    if (count($configData) > 0)
    {
      $objectId = $controllerObjectId;
      callObjectMethodByName($objectId, "getConfiguration");
      $lastConfig = paramDataToArray(waitForObjectResultByName($objectId, 5, "Configuration", $lastLogId));
      
      $paramData = $lastConfig;
      foreach ( $lastConfig as $key => $value )
      {
        if ($configData[$key] != "")
          $paramData[$key] = $configData[$key];
      }
      callObjectMethodByName($objectId, "setConfiguration", $paramData);
    }
    
    // Dann Digitalports
    foreach ( $digitalPorts as $port )
    {
      $configData = (array)$configFile["DigitalPort " . $port];
      if (count($configData) > 0)
      {
        $objectId = getObjectId($deviceId, $digitalPortClassId, $port);
        
        callObjectMethodByName($objectId, "getConfiguration");
        $lastConfig = paramDataToArray(waitForObjectResultByName($objectId, 5, "Configuration", $lastLogId));
        
        $paramData = $lastConfig;
        foreach ( $lastConfig as $key => $value )
        {
          if ($configData[$key] != "")
            $paramData[$key] = $configData[$key];
        }
        callObjectMethodByName($objectId, "setConfiguration", $paramData);
      }
    }
    
    callObjectMethodByName($controllerObjectId, "reset");
  }
  else
  {
    setupTreeAndContent("loadConfigTemplate_design.html", $message);
    $html = str_replace("%ID%", $id, $html);
    show();
  }
}

function addDefaultGroupRuleData($triggerObjectId, $triggerEvent, $actorObjectId)
{
  global $rulesData;
  global $dataPos;
  
  $startPos = $dataPos;
  
  $rulesData[$dataPos++] = 1; // numOfSignals
  $rulesData[$dataPos++] = 1; // numOfActions
  $rulesData[$dataPos++] = 255; // Aktivierungsstate egal
  $rulesData[$dataPos++] = 255; // ResultingState gleich
  $rulesData[$dataPos++] = 255; // Zeitraum immer
  $rulesData[$dataPos++] = 255; // Zeitraum immer
  $rulesData[$dataPos++] = 255; // Zeitraum immer
  $rulesData[$dataPos++] = 255; // Zeitraum immer
  

  // triggerId einfügen
  $bytes = dWordToBytes($triggerObjectId);
  foreach ( (array)$bytes as $value )
  {
    $rulesData[$dataPos++] = $value;
  }
  
  // triggerEvent setzen
  $rulesData[$dataPos++] = getFunctionIdByName($triggerObjectId, $triggerEvent);
  
  // trigger auffüllen      
  for($i = 0; $i < 5; $i++)
  {
    $rulesData[$dataPos++] = 0;
  }
  
  // actorId einfügen
  $bytes = dWordToBytes($actorObjectId);
  foreach ( (array)$bytes as $value )
  {
    $rulesData[$dataPos++] = $value;
  }
  
  $function = 0;
  $param1 = 0;
  $param2 = 0;
  $param3 = 0;
  $param4 = 0;
  $length = 2;
  // actionData setzen
  if (getClassId($actorObjectId) == getClassIdByName("Dimmer"))
  {
    $function = getFunctionIdByName($actorObjectId, "start");
  }
  else if (getClassId($actorObjectId) == getClassIdByName("Rollladen"))
  {
    $function = getFunctionIdByName($actorObjectId, "start");
  }
  else if (getClassId($actorObjectId) == getClassIdByName("Schalter"))
  {
    $function = getFunctionIdByName($actorObjectId, "toggle");
	$param3 = 1;
	$length = 4;
  }
  
  $rulesData[$dataPos++] = $length;
  $rulesData[$dataPos++] = $function;
  $rulesData[$dataPos++] = $param1;
  $rulesData[$dataPos++] = $param2;
  $rulesData[$dataPos++] = $param3;
  $rulesData[$dataPos++] = $param4;
  
  $rulesData[$dataPos++] = 0; // 0 Terminierung
}

function statusOut($bytes, $fileSize, $blockSize)
{
  $percent = (int)($bytes * 100 / $fileSize);
  echo "<script>document.getElementById(\"status\").innerHTML=\"Updatestatus: $bytes/$fileSize Bytes - $percent%\";</script>";
}

if ($action == "activateBootloader" || $action == "fwUpdate")
{
  ob_end_flush();
  ob_start();
  
  $erg = QUERY("select objectId from controller where id='$id' limit 1");
  if ($obj = MYSQL_FETCH_OBJECT($erg))
  {
    $objectId = $obj->objectId;
    
    setupTreeAndContent("fwUpdate.html");
    show(0);
    
    if (getInstanceId($objectId) != $BOOTLOADER_INSTANCE_ID)
    {
      liveOut("<b>Bootloader wird aktiviert ...</b>");
      callObjectMethodByName($objectId, "reset");
      //sleep(2);
      $receiverObjectId = getObjectId(getDeviceId($objectId), getClassId($objectId), $BOOTLOADER_INSTANCE_ID);
      
      for ($i=0;$i<5;$i++)
      {
         sleepMs(100);
         callObjectMethodByName($receiverObjectId, "ping");
      }
      
      $result = callObjectMethodByNameAndRecover($receiverObjectId, "ping","","pong",1,5,1);
      $objectId = $receiverObjectId;
      liveOut("Bootloader gestartet. ObjectID: " . getFormatedObjectId($objectId));
      liveOut('');
      //updateControllerStatus();
    }
    
    if ($action == "activateBootloader")
    {
      flushIt();
      //sleep(5);
      //updateControllerStatus();
      //sleep(2);
      //echo "<script>location='controller.php'</script>";
      exit();
    }
    
      $erg = QUERY("select firmwareId from controller where id='$id' limit 1");
      if ($row = MYSQL_FETCH_ROW($erg))
      {
        $firmwareId = $row[0];
        $firmwareIdFunctionId = getObjectFunctionsIdByName($BROADCAST_OBJECT_ID, "ModuleId");
        $erg = QUERY("select id from featureFunctionParams where featureFunctionId='$firmwareIdFunctionId' and name='firmwareId' limit 1");
        if ($row = MYSQL_FETCH_ROW($erg))
        {
          $paramId = $row[0];
          $erg = QUERY("select name from featureFunctionEnums where paramId='$paramId' and value='$firmwareId' limit 1");
          if ($row = MYSQL_FETCH_ROW($erg))
            $neededFirmware = $row[0]; //.".bin";
          else
            die("Fehler, unbekannte FirmwareID");
        }
        else
          die("Fehler 2");
      }
      else
        die("Fehler 3");
    
    $orig = $_FILES['userfile']['name'];
    if ($force != 1 && $orig != "" && strpos($orig, $neededFirmware)  === FALSE)
        die("Fehler: Nicht kompatible Firmware. Erwartet wird: " . $row[0]);
    if ($orig == "")
    {
      $newestFirmware = "";
      $newestFirmwareTime = 0;
      $handle = opendir("../firmware/");
      while ( false !== ($file = readdir($handle)) )
      {
        if (strpos($file, $neededFirmware) !== FALSE && strpos($file, ".bin") !== FALSE && strpos($file, "BOOTER") === FALSE && filemtime("../firmware/" . $file) > $newestFirmwareTime)
        {
          $newestFirmwareTime = filemtime("../firmware/" . $file);
          $newestFirmware = $file;
        }
      }
      closedir($handle);
      if ($newestFirmware == "")
        die("Fehler: Keine Datei gewählt und keine Defaultfirmware vorhanden -> $neededFirmware");
      $neededFirmware = $newestFirmware;
    }
    
    @mkdir("uploads");
    
    if ($orig != "")
    {
      $fwfile = $_FILES['userfile']['tmp_name'];
      $show = $_FILES['userfile']['name']; //substr($fwfile,strrpos($fwfile,"\\"));
    }
    else
    {
      $fwfile = "../firmware/" . $neededFirmware;
      $show = $neededFirmware;
    }
    
    if (strpos($show, "BOOTER") !== FALSE)
      $isBooter = 1;
    else
      $isBooter = 0;
    
    liveOut("<b>Firmware Update ...($show)</b>");
    liveOut("Während des Updates den Controller und den PC NICHT AUSSCHALTEN!");
    liveOut('');
    $fileSize = filesize($fwfile);
    liveOut("Dateigröße: $fileSize Bytes");
    
    $result = callObjectMethodByNameAndRecover($objectId, "getConfiguration","","Configuration",3,5,1);
    $blockSize = getResultDataValueByName("dataBlockSize", $result);
    
    liveOut("Daten Blockgröße: " . $blockSize . " Bytes");
    liveOut('');
    liveOut("<div id=\"status\">Updatestatus: 0/$fileSize Bytes - 0%</div>", 0);
    
    $fd = fopen($fwfile, "r");
    $ready = 0;
    $firstWriteId = - 1;
    
    $memoryStatusOk = getFunctionParamEnumValueByName($objectId, "MemoryStatus", "status", "OK");
	$memoryStatusAborted = getFunctionParamEnumValueByName($objectId, "MemoryStatus", "status", "ABORTED");
    
    $rounds = 0;
    while ( ! feof($fd) )
    {
      $buffer = fread($fd, $blockSize);
      $data["address"] = $ready;
      $data["data"] = $buffer;
      if ($firstWriteId == - 1)
        $firstWriteId = $lastLogId;
      
      $result = callObjectMethodByNameAndRecover($objectId, "writeMemory", $data, "MemoryStatus", 2, 3,0);
      if ($result==-1)
      {
      	updateControllerStatus ();
      	die("Fehler: Controller antwortet nicht");
      }
      
      $memoryStatus = getResultDataValueByName("status", $result);
      
      if ($memoryStatus != $memoryStatusOk)
      {
      	updateControllerStatus ();
		if($memoryStatus == $memoryStatusAborted) 
		{
		  liveOut("Bootloader hat die FW nicht akzeptiert!  ");
		  liveOut("Mögliche Ursachen: ");
          liveOut("- FW ist nicht für dieses Modul ");
		  liveOut("- FW Major-Release-Kennung ist vom Bootloader verschieden ");
		  liveOut("- FW Minor-Kennung ist nicht größer als bereits installiert ");
		  liveOut("- FW ist korrupt oder modifiziert ");
		}
		else
		{
		  liveOut("Bootloader hat fehlerhaften MemoryStatus gemeldet: " . $result[0]->dataValue);
		}
        exit();
      }
      $ready += strlen($buffer);
      if ($round % 5 == 0)
        statusOut($ready, $fileSize, $blockSize);
      $i++;
      $rounds++;
      flushIt();
      //sleepMS(50); // TODO warum hilft das ?
    }
    fclose($fd);
    
    liveOut("Übertragung erfolgreich beendet");
    liveOut('');
    
    if ($verify == 1)
    {
      liveOut("<b>Firmware wird verifiziert...</b>");
      $erg = QUERY("select functionData,receiverSubscriberData from udpCommandLog where function='writeMemory' and id>'$firstWriteId' order by id");
      while ( $row = MYSQL_FETCH_ROW($erg) )
      {
        if (unserialize($row[1])->objectId != $objectId)
          continue;
        
        $fkt = unserialize($row[0]);
        $offset = $fkp->paramData[0]->dataValue;
        $crc = $fkp->paramData[1]->dataValue;
        
        $data["address"] = $offset;
        $data["length"] = $blockSize;
        callObjectMethodByName($objectId, "readMemory", $data);
        $result = waitForObjectResultByName($objectId, 5, "MemoryData", $lastLogId);
        $compareCrc = getResultDataValueByName("data", $result);
        if ($compareCrc != $crc)
        {
          liveOut("Fehler bei offset: $offset -> " . $compareCrc . " != " . $crc);
          exit();
        }
      }
      liveOut('OK!');
      liveOut('');
    }
    
    liveOut("<b>Starte Controller neu...</b>");
    callObjectMethodByName($objectId, "reset");
    flush();
    
    sleep(3);
    
    for($i = 0; $i < 3; $i++)
    {
      if ($isBooter != 1)
        $receiverObjectId = getObjectId(getDeviceId($objectId), getClassId($objectId), $FIRMWARE_INSTANCE_ID);
      else
        $receiverObjectId = getObjectId(getDeviceId($objectId), getClassId($objectId), $BOOTLOADER_INSTANCE_ID);
      callObjectMethodByName($receiverObjectId, "ping");
      $result = waitForObjectResultByName($receiverObjectId, 3, "pong", $lastLogId, "funtionDataParams", 0);
      if ($result != - 1)
        break;
      sleep(1);
      if ($i == 9)
      {
        updateControllerStatus();
        liveOut("Fehler! Controller antwortet nicht");
        exit();
      }
    }
    
    liveOut("Firmwareupdate erfolgreich beendet");
    exit();
  }
  else
    die("Fehlerhafte ID $id");
}

if ($submitted != "")
{
  if ($delete == 1)
  {
    deleteController($id);
    triggerTreeUpdate();
    header("Location: controller.php");
    exit();
  }
  else
  {
    QUERY("UPDATE controller set name='$name' where id='$id' limit 1");
    $message = "Einstellungen gespeichert";
  }
}

setupTreeAndContent("editController_design.html", $message);

$html = str_replace("%ID%", $id, $html);

$allController = readControllers();
foreach ( $allController as $obj )
{
  if ($obj->id == $id)
  {
    $objectId = $obj->objectId;
    $html = str_replace("%OBJECT_ID_FORMATED%", getFormatedObjectId($obj->objectId), $html);
    $html = str_replace("%OBJECT_ID%", $obj->objectId, $html);
    $html = str_replace("%CONTROLLER_NAME%", $obj->name, $html);
    $status = "online";
    if ($obj->online == 999)
    {
      $status = "offline";
      removeTag("%OPT_FW_UPDATE%", $html);
      removeTag("%OPT_ACTIVATE_BOOTLOADER%", $html);
    }
    else
    {
      chooseTag("%OPT_FW_UPDATE%", $html);
      
      // Bootloader aktiv
      if (getInstanceId($obj->objectId) == $BOOTLOADER_INSTANCE_ID)
        removeTag("%OPT_ACTIVATE_BOOTLOADER%", $html);
        // Normaler Controller -> Bootloader aktierbar
      else
        chooseTag("%OPT_ACTIVATE_BOOTLOADER%", $html);
    }
    $html = str_replace("%STATUS%", $status, $html);
    break;
  }
}
if ($objectId == "")
  die("FEHLER! Ungültige ID $id");

$typeRound[0] = "EVENT";
$typeRound[1] = "ACTION";
$typeRound[2] = "FUNCTION";
$typeRound[3] = "RESULT";

$functionTag = getTag("%FUNCTION%", $html);
$html = str_replace("%FUNCTION%", "", $html);

$allFeatureFunctions = readFeatureFunctions();
$allFeatureFunctionParams = readFeatureFunctionParams();
$allFeatureFunctionEnums = readFeatureFunctionEnums();

// Zuletzt empfangene Daten von diesem Sender
$erg = QUERY("select function,functionData,id from lastReceived  where senderObj='$objectId' order by id desc limit 50");
while ( $row = MYSQL_FETCH_ROW($erg) )
{
  if (! isset($lastReceived[$row[0]]))
  {
    $lastReceived[$row[0]] = $row[1];
    $lastReceivedId[$row[0]] = $row[2];
  }
}

debugScript("test");

$ansicht = $_SESSION["ansicht"];

foreach ( $typeRound as $actType )
{
  $content = "";
  foreach ( $allFeatureFunctions as $obj )
  {
    if ($obj->featureClassesId == $CONTROLLER_CLASSES_ID and $obj->type == $actType)
    {
      if ($ansicht == "Experte" && $obj->view == "Entwickler")
        continue;
      if ($ansicht == "Standard" && ($obj->view == "Experte" || $obj->view == "Entwickler"))
        continue;
      
      $actFunctionData = getLastFunctionData($obj->name, $lastReceived, $lastReceivedId);
      
      $actTag = $functionTag;
      $actTag = str_replace("%FUNCTION%", i18n($obj->name), $actTag);
      $actTag = str_replace("%FEATURE_FUNCTION_ID%", $obj->id, $actTag);
      
      $paramTag = getTag("%PARAM%", $actTag);
      $params = "";
      foreach ( $allFeatureFunctionParams as $obj2 )
      {
        if ($obj2->featureFunctionId == $obj->id)
        {
          
          if ($ansicht == "Experte" && $obj2->view == "Entwickler")
            continue;
          if ($ansicht == "Standard" && ($obj2->view == "Experte" || $obj2->view == "Entwickler"))
            continue;
          
          $actParamsTag = $paramTag;
          
          $actParamValue = "";
          
          if ($actFunctionData != "")
          {
            foreach ( $actFunctionData->paramData as $actSearchParam )
            {
              if ($actSearchParam->name == $obj2->name)
              {
                $actParamValue = $actSearchParam->dataValue;
                break;
              }
            }
          }
          
          $actParamsTag = str_replace("%PARAM_NAME%", $obj2->name, $actParamsTag);
          if ($obj2->type == "ENUM")
          {
            $type = "<select name='param" . $obj2->id . "'>";
            foreach ( $allFeatureFunctionEnums as $obj3 )
            {
              if ($obj3->featureFunctionId == $obj->id and $obj3->paramId == $obj2->id)
              {
                if ($obj3->value == $actParamValue)
                  $selected = "selected";
                else
                  $selected = "";
                $type .= "<option value='$obj3->value' $selected>$obj3->name";
              }
            }
            $type .= "</select>";
          }
          else if ($obj2->type == "BITMASK")
          {
            if ($actParamValue == "")
              $actParamValue = 0;
            $type = getBitMask("param" . $obj2->id, $actParamValue, readFeatureFunctionBitmaskNames($obj->id, $obj2->id));
          }
          else if ($obj2->type == "WEEKTIME")
          {
            //if ($actParamValue=="") $actParamValue=0;
            $type = getWeekTime("param" . $obj2->id, $actParamValue);
            //$type=getBitMask("param".$obj2->id,$actParamValue, readFeatureFunctionBitmaskNames($obj->id, $obj2->id));
          }
          else
          {
            $size = strlen($actParamValue);
            if ($size < 5)
              $size = 5;
            if ($size > 50)
              $size = 50;
            $type = "<input name='param" . $obj2->id . "' value='$actParamValue' type='text' size='$size'>";
          }
          
          $actParamsTag = str_replace("%PARAM_ENTRY%", $type, $actParamsTag);
          $actParamsTag = str_replace("%COMMENT%", $obj2->comment, $actParamsTag);
          $params .= $actParamsTag;
        }
      }
      
      $actTag = str_replace("%PARAM%", $params, $actTag);
      $content .= $actTag;
    }
  }
  $html = str_replace("%" . $actType . "S%", $content, $html);
}

show();

function getLastFunctionData($name, $lastReceived, $lastReceivedId)
{
  $lastReceivedNormal = $lastReceivedId[$name];
  $setName = str_replace("set", "", $name);
  $lastReceivedSet = $lastReceivedId[$setName];
  
  if ($lastReceivedNormal == "" && $lastReceivedSet == "")
    return "";
  if ($lastReceivedNormal == "" && $lastReceivedSet != "")
    return unserialize($lastReceived[$setName]);
  if ($lastReceivedNormal != "" && $lastReceivedSet == "")
    return unserialize($lastReceived[$name]);
  if ($lastReceivedNormal > $lastReceivedSet)
    return unserialize($lastReceived[$name]);
  return unserialize($lastReceived[$setName]);
}

function paramDataToArray($paramData)
{
  $result = "";
  foreach ( $paramData as $act )
  {
    $result[$act->name] = $act->dataValue;
  }
  return $result;
}

?>