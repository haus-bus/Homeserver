<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action == "readRules")
{
  callObjectMethodByName($objectId, "getConfiguration");
  
  $result = waitForObjectResultByName($objectId, 5, "Configuration", $lastLogId);
  $blockSize = getResultDataValueByName("dataBlockSize", $result);
  
  $rulesBytes = "";
  $rulesBytesPos = 0;
  while ( $rulesBytesPos < 8*1024 ) // 8k is max. for AR8
  {
    $data["offset"] = $rulesBytesPos;
    $data["length"] = $blockSize;
    callObjectMethodByName($objectId, "readRules", $data);
    
    $result = waitForObjectResultByName($objectId, 5, "RulesData", $lastLogId);
    $parts = explode(",", getResultDataValueByName("data", $result));
    foreach ( (array)$parts as $value )
    {
      $rulesBytes[$rulesBytesPos++] = $value;
    }
    
    $regeln = "";
    if (parseGroup($rulesBytes, $rulesBytesPos, $regeln))
      break;
  }
  die("<hr>" . $regeln);
}
else if ($action == "checkRules")
{
  echo "<br><div style='padding-left:20px'>";
  echo "<u>Generiere und prüfe Regeln</u><br>";
  echo "<div style='width:98%;height:500px;font-face:verdana;font-size:11px' id='updateArea'></div>";
  flushIt();
  
  generateAndCheckRules();
  exit();
}
else if ($action == "addTimeSignal" || $action == "addTimeSignalSunrise" || $action == "addTimeSignalSunset")
{
	if ($submitted!=1)
	{
	  setupTreeAndContent("chooseTimeSignal_design.html", $message);
    $html = str_replace("%GROUP_ID%", $groupId, $html);
    $html = str_replace("editBaseConfig.php", "editRules.php", $html);
    show();
    exit();
  }
  else
  {
    $erg = QUERY("select featureInstanceId from groupFeatures where groupId='$groupId' order by id limit 1");
    if ($row = MYSQL_FETCH_ROW($erg)) $firstAktor = $row[0];
    else die("Kein Aktor gefunden in Gruppe $groupId");
  
    $erg = QUERY("select controllerId from featureInstances where id='$firstAktor' limit 1");
    if ($row = MYSQL_FETCH_ROW($erg)) $controllerId = $row[0];
    else die("Controller zu featureId $firstAktor nicht gefunden");
  
    $erg = QUERY("select id from featureInstances where controllerId='$controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1");
    if ($row = MYSQL_FETCH_ROW($erg)) $controllerInstanceId = $row[0];
    else die("controllerInstanceId zu controllerId $controllerId nicht gefunden");
  
    QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId) 
                        values('$groupId','7','31','255','7','31','255','0','0')");
    $ruleId = mysql_insert_id();
  
    if ($action == "addTimeSignalSunrise") $myEvent = getClassesIdFunctionsIdByName($CONTROLLER_CLASSES_ID, "evDay");
    else if ($action == "addTimeSignalSunset") $myEvent = getClassesIdFunctionsIdByName($CONTROLLER_CLASSES_ID, "evNight");
    else $myEvent = getClassesIdFunctionsIdByName($CONTROLLER_CLASSES_ID, "evTime");
  
    QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                              values('$ruleId','$controllerInstanceId','$myEvent')");
    $ruleSignalId = mysql_insert_id();
  
    if ($action == "addTimeSignalSunrise" || $action == "addTimeSignalSunset")
    {
    	header("Location: editRules.php?groupId=$groupId");
    	exit;
    }
    else
    {
      $erg = QUERY("select id from featureFunctionParams where featureFunctionId='$evTimeFunctionId' and name='weekTime' limit 1");
      $row = MYSQL_FETCH_ROW($erg);
      $paramsId = $row[0];
      QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                 values('$ruleSignalId','$paramsId','0')");
 
      header("Location: editRules.php?action=editSignalParams&groupId=$groupId&ruleId=$ruleId&ruleSignalId=$ruleSignalId&logicalGroupMode=$logicalGroupMode");
      exit;
    }
  }
}
else if ($action == "submitRules")
{
  echo "<br><div style='padding-left:20px'><table width=98%><tr><td colspan=3 valign=top><font face=verdana size=2>";
  echo "<u>Generiere und püfe Regeln</u> <br>";
  echo "<div style='width:98%;height:170px;overflow-x:hidden;overflow-y:scroll;font-face:verdana;font-size:11px' id='updateArea'></div></td></tr>";
  echo "<tr><td valign=top width=50%><font face=verdana size=2>";
  
  flushIt();
  generateAndCheckRules();
  echo "<u>Konfiguriere Regeln</u><br>";
  flushIt();
  
  // Gruppenstates durchnummerierte Values geben
  $erg = QUERY("select distinct(groupId) from groupstates");
  while ( $row = MYSQL_FETCH_ROW($erg) )
  {
    $i = 0;
    $erg2 = QUERY("select id from groupstates where groupId='$row[0]' order by id");
    while ( $row2 = MYSQL_FETCH_ROW($erg2) )
    {
      QUERY("UPDATE groupstates set value='$i' where id='$row2[0]' limit 1");
      $i++;
    }
  }
  
  $pcInstances = "";
  $erg = QUERY("select featureInstances.id from featureInstances join controller on (featureInstances.controllerId=controller.id) where size='999'");
  while ( $row = MYSQL_FETCH_ROW($erg) )
  {
    $pcInstances .= " and featureInstanceId!='" . $row[0] . "'";
  }

  // Zu allen Features von online Controllern Gruppenregeln suchen
  $filterDone = "";
  if ($singleControllerId!="") $andSingleController=" and objectId='$singleControllerId'";
  $erg = QUERY("select id,objectId,name,firmwareId,size from controller where bootloader!='1' and online='1' $andSingleController");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
  	//echo "Controller ".$obj->name."<br>";
    // PC-Server überspringen
    if ($obj->size == "999")
      continue;
      
      // Position 0 für Anzahl der Gruppen freihalten
    $dataPos = 1;
    $nrGroups = 0;
    $objectId = $obj->objectId;
    
    $groups = "";
    $erg2 = QUERY("select id,featureClassesId from featureInstances where controllerId='$obj->id'");
    while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
    {
      $erg3 = QUERY("select groupId from groupFeatures where featureInstanceId='$obj2->id'");
      while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
      {
        // Prüfen obs das erste bzw einzige Feature in dieser Gruppe ist. Ansonsten ist wer anderes zuständig
        $erg4 = QUERY("select featureInstanceId from groupFeatures where groupId='$obj3->groupId' $pcInstances order by id limit 1");
        $row4 = MYSQL_FETCH_ROW($erg4);
        if ($row4[0] == $obj2->id)
        {
        	//echo $obj3->groupId;
        	
          $erg5 = QUERY("select subOf,groupType,active from groups where id='$obj3->groupId' limit 1");
          $row5 = MYSQL_FETCH_ROW($erg5);
          
          if ($row5[1] != "")
          {
          	//echo "filter groupType <br>";
            continue; // Hier wird die dazu generierte Gruppe konfiguriert
          }
          
          if ($row5[2] ==0)
          {
          	//echo "überspringe inaktive gruppe  $obj3->groupId <br>";
            continue; 
          }
          

          if ($row5[0] > 0)
          {
            $obj3->groupId = $row5[0]; // Zu Subgruppen wird die Vatergruppe generiert
          	//echo "parent ".$obj3->groupId." <br>";
          }
          

          if ($filterDone[$obj3->groupId] == 1)
          {
          	//echo "filter done <br>";
            continue;
          }
          $filterDone[$obj3->groupId] = 1;
          
          $erg4 = QUERY("select count(*) from rules where groupId='$obj3->groupId'");
          $row4 = MYSQL_FETCH_ROW($erg4);
          if ($row4[0] > 0)
          {
            
            // Prüfen, ob die Regel mindestens einen Trigger und eine Aktion hat
            $ruleIsOk = 1;
            $groupHasValidRules = addGroupRuleData($obj3->groupId);
            if ($groupHasValidRules)
            {
              $nrGroups++;
              $groups .= $obj3->groupId . ",";
              //echo "ok <br>";
            }
            //else echo "no valid rule data <br>";
          }
          //else echo "no rules <br>";
        }
      }
    }
    
    // Der erste AR8 Controller muss noch die PC Regeln mit hosten
    if ($obj->firmwareId == 1 && $pcServerDone == 0)
    {
      $pcServerDone = 1;
      
      $erg6 = QUERY("select groups.id from groups join groupFeatures on (groupFeatures.groupId=groups.id) join featureInstances on (featureInstances.id = groupFeatures.featureInstanceId) join controller on (controller.id=featureInstances.controllerId) where controller.size=999 and single='1'");
      while ($row6 = MYSQL_FETCH_ROW($erg6))
      {
        $pcServerGroupId=$row6[0];
        $groupHasValidRules = addGroupRuleData($pcServerGroupId, 1, $obj->objectId);
        if ($groupHasValidRules)
        {
          $nrGroups++;
          $groups .= $pcServerGroupId . ",";
        }
      }
    }
    
    $rulesData[$dataPos++] = 0; // 0 Terminierung
    

    //if ($nrGroups>0)
    {
      $rulesData[0] = $nrGroups;
      ksort($rulesData);
      
      $dataChanged = checkAndTraceDataDifferences($groups, $obj->id, $rulesData, $dataPos);
      if ($dataChanged || $nocache == 1 || $singleControllerId!="")
      {
        echo "<nobr> $nrGroups Regeln an Controller " . $obj->name . " ($dataPos Bytes)<br>";
        flushIt();
      }
      else
      {
        //liveOut("<i>Ungeändert $nrGroups Regeln an Controller ".$obj->name."</i>");
        continue;
      }
      
      $result = callObjectMethodByNameAndRecover($objectId, "getConfiguration","","Configuration",3,5,0);
      if ($result==-1)
      {
      	echo "<b>Controller antwortet nicht... Überspringe Regeln....!</b><br>";
      	continue;
      }
      
      $blockSize = getResultDataValueByName("dataBlockSize", $result);
      
      $memoryStatusOk = getFunctionParamEnumValueByName($objectId, "MemoryStatus", "status", "OK");
      unset($fileBuffer);
      $ready = 0;
      $firstWriteId = - 1;
      while ( 1 )
      {
        $rest = $dataPos - $ready;
        if ($rest >= $blockSize - 1)
          $actBlockSize = $blockSize;
        else
          $actBlockSize = $rest;
        
        unset($buffer);
        for($i = 0; $i < $actBlockSize; $i++)
        {
          $buffer .= chr($rulesData[$ready + $i]);
          $fileBuffer .= chr($rulesData[$ready + $i]);
        }
        
        $data["offset"] = $ready;
        $data["data"] = $buffer;
        if ($firstWriteId == - 1)
          $firstWriteId = updateLastLogId();
        
        $result = callObjectMethodByNameAndRecover($objectId, "writeRules", $data, "MemoryStatus", 3, 5,0);
        if ($result == - 1)
        {
          QUERY("delete from ruleCache where controllerId='$obj->id' limit 1");
          echo "<font color=#bb0000><b>FEHLER: Controller antwortet nicht </font><br>";
          break;
        }
        $memoryStatus = getResultDataValueByName("status", $result);
        
        if ($memoryStatus != $memoryStatusOk)
        {
          echo "<font color=#bb0000><b>Fehler: A) Controller hat Fehler gemeldet im MemoryStatus -> " . $memoryStatus . "</font><br>";
          break;
        }
        $ready += strlen($buffer);
        $rest = $dataPos - $ready;
        if ($rest < 1)
        {
          // Am ende noch ein 0 Package schicken, falls der letzte Block genau blockSize groß war
          if ($actBlockSize == $blockSize)
          {
            $data["offset"] = $ready;
            $data["data"] = ""; // leere daten
            $result = callObjectMethodByNameAndRecover($objectId, "writeRules", $data, "MemoryStatus", 3, 5,0);
            $memoryStatus = getResultDataValueByName("status", $result);
            if ($memoryStatus != $memoryStatusOk)
            {
              echo "<font color=#bb0000><b>Fehler: B) Controller hat Fehler gemeldet im MemoryStatus -> " . $memoryStatus . "</font><br>";
              break;
            }
          }
          break;
        }
      }
    }
    file_put_contents("0x".decHex($objectId).".bin", $fileBuffer);
  }
  
  if ($singleControllerId=="")
  {
    echo "</td><td width=20>&nbsp;</td><td valign=top><font face=verdana size=2>";
    echo "<u>Konfiguriere Taster</u><br>";
    flushIt();
    
    $tasterClassesId = getClassesIdByName("Taster");
    
    $erg = QUERY("select name, functionId,id from featureFunctions where type='EVENT' and featureClassesId='$tasterClassesId'");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      $tasterEvents[$obj->functionId] = $obj->name;
      $tasterEventsId[$obj->id] = $obj->name;
    }
    
    $notifyOnCovered = getFunctionParamBitValueByNameForClassesId($tasterClassesId, "setConfiguration", "eventMask", "notifyOnCovered");
    $notifyOnClicked = getFunctionParamBitValueByNameForClassesId($tasterClassesId, "setConfiguration", "eventMask", "notifyOnClicked");
    $notifyOnDoubleClicked = getFunctionParamBitValueByNameForClassesId($tasterClassesId, "setConfiguration", "eventMask", "notifyOnDoubleClicked");
    $notifyOnStartHold = getFunctionParamBitValueByNameForClassesId($tasterClassesId, "setConfiguration", "eventMask", "notifyOnStartHold");
    $notifyOnEndHold = getFunctionParamBitValueByNameForClassesId($tasterClassesId, "setConfiguration", "eventMask", "notifyOnEndHold");
    $notifyOnFree = getFunctionParamBitValueByNameForClassesId($tasterClassesId, "setConfiguration", "eventMask", "notifyOnFree");
    
    $bitmask = "";
    $erg = QUERY("SELECT distinct featureInstanceId, functionId, controller.id from rulesignals join featureInstances on (featureInstances.id = rulesignals.featureInstanceId) join featureClasses on (featureClasses.id = featureInstances.featureClassesId) join featureFunctions on (featureFunctions.id=featureFunctionId) join controller on (controller.id = featureInstances.controllerId) where featureClasses.id='$tasterClassesId'  and online='1' and groupAlias='0' order by featureInstanceId");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      if ($bitmask[$obj->featureInstanceId] == "") $bitmask[$obj->featureInstanceId] = 0;
      
      if ($tasterEvents[$obj->functionId] == "evCovered") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnCovered);
      else if ($tasterEvents[$obj->functionId] == "evClicked") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnClicked);
      else if ($tasterEvents[$obj->functionId] == "evDoubleClick") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnDoubleClicked);
      else if ($tasterEvents[$obj->functionId] == "evHoldStart") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnStartHold);
      else if ($tasterEvents[$obj->functionId] == "evHoldEnd") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnEndHold);
      else if ($tasterEvents[$obj->functionId] == "evFree") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnFree);
    }
    
    
    // Und noch Signale aus den Diagrammen berücksichtigen
    $erg = QUERY("SELECT DISTINCT featureInstanceId, graphsignalevents.functionId
  FROM graphsignalevents
  JOIN featureInstances ON ( featureInstances.id = graphsignalevents.featureInstanceId )
  JOIN featureClasses ON ( featureClasses.id = featureInstances.featureClassesId )
  JOIN featureFunctions ON ( featureFunctions.id = graphsignalevents.functionId )
  JOIN controller ON ( controller.id = featureInstances.controllerId )
  WHERE featureClasses.id = '1'
  AND online = '1'
  ORDER BY featureInstanceId");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      if ($bitmask[$obj->featureInstanceId] == "") $bitmask[$obj->featureInstanceId] = 0;
      
      if ($tasterEventsId[$obj->functionId] == "evCovered") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnCovered);
      else if ($tasterEventsId[$obj->functionId] == "evClicked") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnClicked);
      else if ($tasterEventsId[$obj->functionId] == "evDoubleClick") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnDoubleClicked);
      else if ($tasterEventsId[$obj->functionId] == "evHoldStart") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnStartHold);
      else if ($tasterEventsId[$obj->functionId] == "evHoldEnd") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnEndHold);
      else if ($tasterEventsId[$obj->functionId] == "evFree") $bitmask[$obj->featureInstanceId] |= pow(2, $notifyOnFree);
    }
    
    foreach ( $bitmask as $featureInstanceId => $mask )
    {
      $erg3 = MYSQL_QUERY("select controller.size as controllerSize,controller.name as controllerName,controller.objectId as controllerObjectId, featureInstances.name as featurInstanceName from featureInstances join controller on (controller.id=featureInstances.controllerId) where featureInstances.id='$featureInstanceId' limit 1") or die(MYSQL_ERROR());
      $obj3 = MYSQL_FETCH_OBJECT($erg3);
      if ($obj3->controllerSize == "999") continue; // PC rauslassen
      $tasterName = $obj3->controllerName . "-" . $obj3->featurInstanceName;
      
      callInstanceMethodByName($featureInstanceId, "getConfiguration");
      $result = waitForInstanceResultByName($featureInstanceId, 2, "Configuration", $lastLogId, "funtionDataParams", 0);
      if ($result == - 1)
      {
        echo "Wiederholung bei $tasterName <br>";
        flushIt();
        callInstanceMethodByName($featureInstanceId, "getConfiguration");
        $result = waitForInstanceResultByName($featureInstanceId, 2, "Configuration", $lastLogId, "funtionDataParams", 0);
        if ($result == - 1)
        {
          echo "Fehler bei $tasterName <br>";
          flushIt();
          continue;
        }
      }
      
      $holdTimeout = getResultDataValueByName("holdTimeout", $result);
      $eventMask = getResultDataValueByName("eventMask", $result);
      if ($eventMask > 127) $mask += 128;
      $waitForDoubleClickTimeout = getResultDataValueByName("waitForDoubleClickTimeout", $result);
      
      $configArray = array (
          "holdTimeout" => $holdTimeout,
          "waitForDoubleClickTimeout" => $waitForDoubleClickTimeout,
          "eventMask" => $mask 
      );
      $dataChanged = checkAndTraceConfigData($featureInstanceId, $configArray);
      
      if ($dataChanged || $nocache == 1)
      {
        echo "<nobr>$tasterName: hold = $holdTimeout, doubleClick = $waitForDoubleClickTimeout, mask = $mask <br>";
        flushIt();
        callInstanceMethodByName($featureInstanceId, "setConfiguration", $configArray);
      }
      //else liveOut("<i>Ungeändert: $tasterName: hold = $holdTimeout, doubleClick = $waitForDoubleClickTimeout, mask = $mask</i>");
    }
  }
  
  echo "</td></tr></table></div>";
  flushIt();
  triggerTreeUpdate();
  //echo "<script>setTimeout('location=\"controller.php\";',5000);</script>";
  exit();
  if ($groupId == "")
  {
    setupTreeAndContent("", $message);
    show();
  }
}

function addGroupRuleData($groupId, $pcRules = 0, $hostControllerObjectId = 0)
{
  global $rulesData;
  global $dataPos;
  global $CONTROLLER_CLASS_ID;
  global $FIRMWARE_INSTANCE_ID;
  
  $startPos = $dataPos;
  
  $erg9 = QUERY("select * from rules where groupId='$groupId' order by id");
  while ( $obj9 = MYSQL_FETCH_OBJECT($erg9) )
  {
    $erg = QUERY("select count(*) from ruleSignals where ruleId='$obj9->id' and groupAlias='0' and featureInstanceId>0");
    $row = MYSQL_FETCH_ROW($erg);
    $nrSignals = $row[0];
    if ($nrSignals == 0)
      continue;
    
    $erg = QUERY("select count(*) from ruleActions where ruleId='$obj9->id'");
    $row = MYSQL_FETCH_ROW($erg);
    $nrActions = $row[0];
    //if ($nrActions==0) continue;
    /*if ($nrActions>16)
    {
    	echo "Überspringe Regel mit $nrActions Actions <br>";
    	break;
    }*/
    
    $rulesData[$dataPos++] = $nrSignals;
    $rulesData[$dataPos++] = $nrActions;
    
    if ($obj9->activationStateId == 0) $rulesData[$dataPos++] = 255; // Aktivierungsstate egal
    else
    {
      $erg = QUERY("select value from groupStates where id='$obj9->activationStateId' limit 1");
      $row = MYSQL_FETCH_ROW($erg);
      $rulesData[$dataPos++] = $row[0]; // Aktivierungsstate
    }
    
    if ($obj9->resultingStateId == 0) $rulesData[$dataPos++] = 255; // ResultingState gleich
    else
    {
      $erg = QUERY("select value from groupStates where id='$obj9->resultingStateId' limit 1");
      $row = MYSQL_FETCH_ROW($erg);
      $rulesData[$dataPos++] = $row[0]; // ResultingState
    }
    
    if ($obj9->startHour == "25")
    {
      $obj9->startHour = 0;
      $obj9->startMinute = 60;
    }
    else if ($obj9->startHour == "26")
    {
      $obj9->startHour = 0;
      $obj9->startMinute = 61;
    }
    
    if ($obj9->endHour == "25")
    {
      $obj9->endHour = 0;
      $obj9->endMinute = 60;
    }
    else if ($obj9->endHour == "26")
    {
      $obj9->endHour = 0;
      $obj9->endMinute = 61;
    }
    
    //if ($obj9->startMinute==255) $obj9->startMinute=63; // die unteren 6 bits gesetzt
    if ($obj9->intraDay==1)
    {
    	 if ($obj9->startMinute==255) $obj9->startMinute=0;
    	 $obj9->startMinute|=0x40;
    }
    
    $rulesData[$dataPos++] = $obj9->startMinute;
    
    $rulesData[$dataPos++] = $obj9->startHour + ($obj9->startDay << 5);
    $rulesData[$dataPos++] = $obj9->endMinute;
    $rulesData[$dataPos++] = $obj9->endHour + ($obj9->endDay << 5);
    
    $erg = QUERY("select id, featureInstanceId, featureFunctionId from ruleSignals where ruleId='$obj9->id' and groupAlias='0' and featureInstanceId>0 order by id");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
    	// Bei EvTime müssen wir noch den Absender auf den Hostenden Controller abändern
    	if ($pcRules == 1 && $obj->featureFunctionId==129)
    	{
    		$bytes = dWordToBytes($hostControllerObjectId); // Sender
    	}
    	else
    	{
        $erg2 = QUERY("select objectId from featureInstances where id='$obj->featureInstanceId' limit 1");
        if ($obj2 = MYSQL_FETCH_OBJECT($erg2)) $bytes = dWordToBytes($obj2->objectId); // Sender
        else
        {
        	showRuleError("Unbekannter Sender in Gruppe $groupId ruleId $obj9->id featureInstanceId = $obj->featureInstanceId", $groupId);
          $rulesData[$startPos] = 0;
          $dataPos = $startPos;
          return false; 
        }
      }
      
      foreach ( (array)$bytes as $value )
      {
        $rulesData[$dataPos++] = $value;
      }
      
      $erg2 = QUERY("select functionId from featureFunctions where id='$obj->featureFunctionId' limit 1");
      $obj2 = MYSQL_FETCH_OBJECT($erg2);
      $rulesData[$dataPos++] = $obj2->functionId; // FunctionId
      

      $usedParamLength = 0;
      $erg2 = QUERY("select id,type from featureFunctionParams where featureFunctionId='$obj->featureFunctionId' order by id");
      while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
      {
        $erg3 = QUERY("select paramValue from ruleSignalParams where ruleSignalId='$obj->id' and featureFunctionParamsId='$obj2->id' limit 1");
        if ($obj3 = MYSQL_FETCH_OBJECT($erg3))
          $value = $obj3->paramValue;
        else
          $value = 0;
        
        $bytes = paramToBytes($value, $obj2->type, 1);
        foreach ( (array)$bytes as $value )
        {
          $rulesData[$dataPos++] = $value;
          $usedParamLength++;
        }
      }
      for($i = $usedParamLength; $i < 5; $i++)
      {
        $rulesData[$dataPos++] = 0;
      }
    }
    
    $erg = QUERY("select id, featureInstanceId, featureFunctionId from ruleActions where ruleId='$obj9->id' order by id");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      $erg2 = QUERY("select objectId from featureInstances where id='$obj->featureInstanceId' limit 1");
      $obj2 = MYSQL_FETCH_OBJECT($erg2);
      $bytes = dWordToBytes($obj2->objectId); // Empfänger
      foreach ( (array)$bytes as $value )
      {
        $rulesData[$dataPos++] = $value;
      }
      
      $actionLengthPos = $dataPos;
      $dataPos++;
      
      $erg2 = QUERY("select functionId from featureFunctions where id='$obj->featureFunctionId' limit 1");
      $obj2 = MYSQL_FETCH_OBJECT($erg2);
      $rulesData[$dataPos++] = $obj2->functionId; // FunctionId
      

      $usedParamLength = 1;
      $erg2 = QUERY("select id,type from featureFunctionParams where featureFunctionId='$obj->featureFunctionId' order by id");
      while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
      {
        $erg3 = QUERY("select paramValue,id from ruleActionParams where ruleActionId='$obj->id' and featureFunctionParamsId='$obj2->id' limit 1");
        if ($obj3 = MYSQL_FETCH_OBJECT($erg3))
        {
          if ($pcRules == 1 && $obj->featureFunctionId == "189")
          {
            $myNr = 0;
            $erg4 = QUERY("select ruleActionParams.id from ruleActionParams join ruleActions on (ruleActions.id=ruleActionParams.ruleActionId) join rules on (rules.id = ruleActions.ruleId) where groupId='$groupId' order by ruleActionParams.id");
            while ( $row4 = MYSQL_FETCH_ROW($erg4) )
            {
              if ($row4[0] == $obj3->id) break;
              $myNr++;
            }
            $value = $myNr;
            
          }
          else $value = $obj3->paramValue;
        }
        else $value = 0;
        
        $bytes = paramToBytes($value, $obj2->type);
        foreach ( (array)$bytes as $value )
        {
          $rulesData[$dataPos++] = $value;
          $usedParamLength++;
        }
      }
      
      $rulesData[$actionLengthPos] = $usedParamLength;
      for($i = $usedParamLength; $i < 5; $i++)
      {
        $rulesData[$dataPos++] = 0;
      }
    }
  }
  
  if ($dataPos > $startPos)
  {
    $rulesData[$dataPos++] = 0; // 0 Terminierung
    return true;
  }
  else
    return false;
}

if ($action == "addSignal")
{
  if ($submitted == 1)
  {
    if ($signalGroupId > 0) // Aufruf aus Signalgruppe
    {
      QUERY("INSERT into basicRuleGroupSignals (ruleId, groupId, eventType) values('-1','$signalGroupId','$signal')");
      $groupSignalId = mysql_insert_id();
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                               values('$ruleId','-$groupSignalId','0','0')");
      header("Location: editRules.php?groupId=$groupId&action=dummy");
    }
    else if ($graphId > 0) // Aufruf aus Diagramm
    {
      $singleParamName = "";
      $erg = MYSQL_QUERY("select name from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id limit 2") or die(MYSQL_ERROR());
      while ( $row = MYSQL_FETCH_ROW($erg) )
      {
        if ($singleParamName != "")
        {
          if ($singleParamName == "celsius" && $row[0] == "centiCelsius")
            $singleParamName = "celsius+centiCelsius/100";
          else if ($singleParamName == "relativeHumidity" && $row[0] == "lastEvent")
            $singleParamName = "relativeHumidity";
          else
            $singleParamName = "";
          break;
        }
        $singleParamName = $row[0];
      }
      
      QUERY("TRUNCATE graphData");
      
      if ($signalId > 0)
      {
      	 if ( $signalEventId > 0)
         {
         	QUERY("UPDATE graphSignalEvents set featureInstanceId='$featureInstanceId', functionId='$featureFunctionId',fkt='$singleParamName' where id='$signalEventId' and graphSignalsId='$graphId' limit 1");
         }
         else 
         {
         	QUERY("INSERT into graphSignalEvents (graphSignalsId, featureInstanceId, functionId,fkt) values('$signalId','$featureInstanceId','$featureFunctionId','$singleParamName')");
         }        
      }
      else
      {
        QUERY("INSERT into graphSignals (graphId, color ) values('$graphId','red')");
        $signalId = mysql_insert_id();
        QUERY("INSERT into graphSignalEvents (graphSignalsId, featureInstanceId, functionId,fkt) values('$signalId','$featureInstanceId','$featureFunctionId','$singleParamName')");
      }
      header("Location: editGraphs.php?id=$graphId");
    }
    else
    {
      $erg = QUERY("select id from ruleSignals where ruleId='$ruleId' and featureInstanceId='$featureInstanceId' and featureFunctionId='$featureFunctionId' limit 1");
      if ($row = mysql_fetch_row($erg))
        $ruleSignalId = $row[0];
      else
      {
        QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','$featureFunctionId')");
        $ruleSignalId = mysql_insert_id();
      }
      
      $erg = QUERY("select count(*) from featureFunctionParams where featureFunctionId='$featureFunctionId'");
      $row = mysql_fetch_row($erg);
      if ($row[0] > 0)
      {
        $erg = QUERY("select featureClassesId from featureInstances where id='$featureInstanceId' limit 1");
        $row = MYSQL_FETCH_ROW($erg);
        $featureClassesId = $row[0];
        
        $irClassesId = getClassesIdByName("IR-Sensor");
        if ($featureClassesId == $irClassesId)
        {
          $i = 0;
          $erg = QUERY("select id from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
          while ( $row = MYSQL_FETCH_ROW($erg) )
          {
            $i++;
            $value = "param" . $i . "Value";
            $value = $$value;
            QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                                 values('$ruleSignalId','$row[0]','$value')");
          }
          header("Location: editRules.php?groupId=$groupId&logicalGroupMode=$logicalGroupMode");
          exit();
        }
        
        header("Location: editRules.php?groupId=$groupId&action=editSignalParams&ruleSignalId=$ruleSignalId&featureFunctionId=$featureFunctionId&logicalGroupMode=$logicalGroupMode&logicalGroupMode=$logicalGroupMode");
      }
      else
        header("Location: editRules.php?groupId=$groupId&logicalGroupMode=$logicalGroupMode");
    }
    exit();
  }
  else
  {
    if ($liveMode == 1)
    {
      setupTreeAndContent("signalLiveMode_design.html");
      $html = str_replace("%LINK%", urlencode("editRules.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&logicalGroupMode=$logicalGroupMode&graphId=$graphId"), $html);
      show();
    }
    else
    {
      setupTreeAndContent("addRuleSignal_design.html");
      
      $withResults=1;
      if ($withResults==1) removeTag("%OPT_ADD_OTHERS%",$html);
      else
      {
      	chooseTag("%OPT_ADD_OTHERS%",$html);
        $html = str_replace("%MY_URI%",$REQUEST_URI,$html);
      }
    
      $html = str_replace("%LOGICAL_GROUP_MODE%", $logicalGroupMode, $html);
      $html = str_replace("%GRAPH_ID%", $graphId, $html);
      $html = str_replace("%SIGNAL_ID%", $signalId, $html);
      $html = str_replace("%SIGNAL_EVENT_ID%", $signalEventId, $html);
      
      $closeTreeFolder = "</ul></li> \n";
      
      $treeElements = "";
      if ($graphId > 0) $treeElements .= addToTree("<a href='editGraph.php?id=$graphId'>Neues Trigger-Signal für diese Regel auswählen</a>", 1);
      else $treeElements .= addToTree("<a href='editRules.php?groupId=$groupId&logicalGroupMode=$logicalGroupMode'>Neues Trigger-Signal für diese Regel auswählen</a>", 1);
      $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
      
      $allMyRuleSignals = readMyRuleSignals($ruleId);
      $logicalButtonClass = getClassesIdByName("LogicalButton");
      
      if ($graphId > 0 || $withResults==1) $or = "or featureFunctions.type='RESULT'";
      
      unset($ready);
      $lastRoom = "";
      $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName, featureFunctions.view as featureFunctionView
          
                                 
                                 from rooms
                                 join roomFeatures on (roomFeatures.roomId = rooms.id)
                                 join featureInstances on (featureInstances.id = roomFeatures.featureInstanceId)
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 
                                 where (featureFunctions.type='EVENT' $or) and featureInstances.featureClassesId!='$logicalButtonClass'
                                 order by roomName,featureClassName,featureInstanceName,featureFunctionName"); //and parentInstanceId='0' 
      while ( $obj = mysql_fetch_object($erg) )
      {
        $ready[$obj->featureInstanceId] = 1;
        
        if ($allMyRuleSignals[$obj->featureInstanceId . "-" . $obj->featureFunctionId] != 1)
        {
          if ($ansicht == "Experte" && $obj->featureFunctionView == "Entwickler")
            continue;
          if ($ansicht == "Standard" && ($obj->featureFunctionView == "Experte" || $obj->featureFunctionView == "Entwickler"))
            continue;
          
          if ($obj->roomId != $lastRoom)
          {
            if ($lastRoom != "")
            {
              $treeElements .= $closeTreeFolder; // letzte instance
              $treeElements .= $closeTreeFolder; // letzte featureclass
              $treeElements .= $closeTreeFolder; // letzter raum
            }
            $lastRoom = $obj->roomId;
            $treeElements .= addToTree($obj->roomName, 1);
            $lastClass = "";
          }
          
          if ($obj->featureClassesId != $lastClass)
          {
            if ($lastClass != "")
            {
              $treeElements .= $closeTreeFolder; // letzte instance
              $treeElements .= $closeTreeFolder; // letzte featureclass
            }
            
            $lastClass = $obj->featureClassesId;
            $treeElements .= addToTree($obj->featureClassName, 1);
            $lastInstance = "";
          }
          
          if ($obj->featureInstanceId != $lastInstance)
          {
            if ($lastInstance != "")
              $treeElements .= $closeTreeFolder; // letzte instance
            $lastInstance = $obj->featureInstanceId;
            $treeElements .= addToTree($obj->featureInstanceName, 1);
          }
          
          $treeElements .= addToTree("<a href='editRules.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&featureInstanceId=" . $obj->featureInstanceId . "&featureFunctionId=" . $obj->featureFunctionId . "&logicalGroupMode=$logicalGroupMode&graphId=$graphId&signalId=$signalId&signalEventId=$signalEventId'>" . i18n($obj->featureFunctionName) . "</a>", 0);
        }
      }
      
      $treeElements .= $closeTreeFolder; // letzte instance
      $treeElements .= $closeTreeFolder; // letzte featureclass
      $treeElements .= $closeTreeFolder; // letzter raum
      

      $lastRoom = "";
      $lastController = "";
      $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 controller.id as controllerId, controller.name as controllerName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName, featureFunctions.view as featureFunctionView
                                 
                                 from featureInstances
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join controller on (featureInstances.controllerId = controller.id)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 
                                 where (featureFunctions.type='EVENT' $or) and featureInstances.featureClassesId!='$logicalButtonClass'
                                 order by controllerName, featureClassName,featureInstanceName,featureFunctionName"); // and parentInstanceId='0' 
      while ( $obj = mysql_fetch_object($erg) )
      {
        if ($ready[$obj->featureInstanceId] == 1)
          continue;
        
        if ($allMyRuleSignals[$obj->featureInstanceId . "-" . $obj->featureFunctionId] != 1)
        {
          if ($ansicht == "Experte" && $obj->featureFunctionView == "Entwickler")
            continue;
          if ($ansicht == "Standard" && ($obj->featureFunctionView == "Experte" || $obj->featureFunctionView == "Entwickler"))
            continue;
          
          if ($lastRoom == "")
          {
            $lastRoom = "dummy";
            $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
          }
          
          if ($obj->controllerId != $lastController)
          {
            if ($lastController != "")
            {
              $treeElements .= $closeTreeFolder; // letztes instance
              $treeElements .= $closeTreeFolder; // letzte class
              $treeElements .= $closeTreeFolder; // letzter controller
            }
            $lastController = $obj->controllerId;
            $treeElements .= addToTree($obj->controllerName, 1);
            $lastClass = "";
          }
          
          if ($obj->featureClassesId != $lastClass)
          {
            if ($lastClass != "")
            {
              $treeElements .= $closeTreeFolder; // letzte instance
              $treeElements .= $closeTreeFolder; // letzte featureclass
            }
            
            $lastClass = $obj->featureClassesId;
            $treeElements .= addToTree($obj->featureClassName, 1);
            $lastInstance = "";
          }
          
          if ($obj->featureInstanceId != $lastInstance)
          {
            if ($lastInstance != "")
              $treeElements .= $closeTreeFolder; // letzte instance
            $lastInstance = $obj->featureInstanceId;
            $treeElements .= addToTree($obj->featureInstanceName, 1);
          }
          
          $treeElements .= addToTree("<a href='editRules.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&featureInstanceId=" . $obj->featureInstanceId . "&featureFunctionId=" . $obj->featureFunctionId . "&logicalGroupMode=$logicalGroupMode&graphId=$graphId&signalId=$signalId&signalEventId=$signalEventId'>" . i18n($obj->featureFunctionName) . "</a>", 0);
        }
      }
      
      $treeElements .= $closeTreeFolder; // letzte instance
      $treeElements .= $closeTreeFolder; // letzte featureclass
      $treeElements .= $closeTreeFolder; // letzter controller
      $treeElements .= $closeTreeFolder; // letzter raum
      

      $foundGroups = 0;
      $erg = QUERY("select id,name,groupType from groups where single!='1' and subOf='0' and groupType!='' order by name");
      while ( $obj = MYSQL_FETCH_OBJECT($erg) )
      {
        if ($foundGroups == 0)
        {
          $foundGroups = 1;
          $treeElements .= addToTree("Signalgruppen Events", 1);
        }
        if ($myGroupSignals[$obj->id] != 1)
        {
          if ($obj->groupType == "SIGNALS-AND")
            $groupType = "UND";
          else if ($obj->groupType == "SIGNALS-OR")
            $groupType = "ODER";
          else
            $groupType = "UNBEKANNTER TYP";
          $treeElements .= addToTree($obj->name . " (Logische " . $groupType . " Verknüpfung)", 1);
          $treeElements .= addToTree("<a href='editRules.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&groupSignal=1&signalGroupId=$obj->id&signal=ACTIVE'>Beim Erreichen vom logischen $groupType Zustand</a>", 0);
          $treeElements .= addToTree("<a href='editRules.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&groupSignal=1&signalGroupId=$obj->id&signal=DEACTIVE'>Beim Verlassen vom logischen $groupType Zustand</a>", 0);
          $treeElements .= $closeTreeFolder;
        }
      }
      if ($foundGroups == 1)
        $treeElements .= $closeTreeFolder;
      
      $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
      $html = str_replace("%GROUP_ID%", $groupId, $html);
      $html = str_replace("%RULE_ID%", $ruleId, $html);
      $html = str_replace("%ACTION%", $action, $html);
      show();
    }
  }
}
else if ($action == "addAction")
{
  if ($submitted == 1)
  {
    $erg = QUERY("select id from ruleActions where ruleId='$ruleId' and featureInstanceId='$featureInstanceId' and featureFunctionId='$featureFunctionId' limit 1");
    if ($row = MYSQL_FETCH_ROW($erg))
      $ruleActionId = $row[0];
    else
    {
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','$featureFunctionId')");
      $ruleActionId = mysql_insert_id();
    }
    
    $erg = QUERY("select count(*) from featureFunctionParams where featureFunctionId='$featureFunctionId'");
    $row = mysql_fetch_row($erg);
    if ($row[0] > 0)
      header("Location: editRules.php?groupId=$groupId&action=editActionParams&ruleActionId=$ruleActionId&featureFunctionId=$featureFunctionId");
    else
      header("Location: editRules.php?groupId=$groupId");
    exit();
  }
  else
  {
    $myGroupFeatures = readGroupFeatures($groupId);
    foreach ( $myGroupFeatures as $featureInstanceId => $dummy )
    {
      if (getClassesIdByFeatureInstanceId($featureInstanceId) == 24)
      {
        $isPc = 1;
        $erg = QUERY("select controllerId from featureInstances where id='$featureInstanceId' limit 1");
        $row = MYSQL_FETCH_ROW($erg);
        $erg = QUERY("select id from featureInstances where controllerId='$row[0]' and featureClassesId='12' limit 1");
        $row = MYSQL_FETCH_ROW($erg);
        $myGroupFeatures[$row[0]] = 1;
        break;
      }
    }
    
    setupTreeAndContent("addRuleAction_design.html");
    
    if ($withFunctions==1)
    {
    	 removeTag("%OPT_ADD_OTHERS%",$html);
    	 $orFunction=" or featureFunctions.type='FUNCTION'";
    }
    else
    {
    	chooseTag("%OPT_ADD_OTHERS%",$html);
      $html = str_replace("%MY_URI%",$REQUEST_URI,$html);
    }
    
    $ansicht = $_SESSION["ansicht"];
    
    $closeTreeFolder = "</ul></li> \n";
    
    $treeElements = "";
    $treeElements .= addToTree("<a href='editRules.php?groupId=$groupId'>Neue Aktion für diese Regel auswählen</a>", 1);
    //$html=str_replace("%INITIAL_ELEMENT2%","expandToItem('tree2','$treeElementCount');",$html);
    $html = str_replace("%INITIAL_ELEMENT2%", "expandTree('tree2');", $html);
    
    if ($isPc != 1)
      $allMyRuleActions = readMyRuleActions($ruleId);
    
    unset($ready);
    $lastRoom = "";
    $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName, featureFunctions.view as featureFunctionView
                                 
                                 from rooms
                                 join roomFeatures on (roomFeatures.roomId = rooms.id)
                                 join featureInstances on (featureInstances.id = roomFeatures.featureInstanceId)
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 
                                 where featureFunctions.type='ACTION' $orFunction
                                 order by roomName,featureClassName,featureInstanceName,featureFunctionName");
    while ( $obj = mysql_fetch_object($erg) )
    {
      if ($myGroupFeatures[$obj->featureInstanceId] == "" && $withFunctions!=1)
        continue;
      if ($allMyRuleActions[$obj->featureInstanceId] != "")
        continue;
      
      if ($ansicht == "Experte" && $obj2->featureFunctionView == "Entwickler")
        continue;
      if ($ansicht == "Standard" && ($obj2->featureFunctionView == "Experte" || $obj2->featureFunctionView == "Entwickler"))
        continue;
      
      $ready[$obj->featureInstanceId] = 1;
      
      if ($obj->roomId != $lastRoom)
      {
        if ($lastRoom != "")
        {
          $treeElements .= $closeTreeFolder; // letzte instance
          $treeElements .= $closeTreeFolder; // letzte featureclass
          $treeElements .= $closeTreeFolder; // letzter raum
        }
        $lastRoom = $obj->roomId;
        $treeElements .= addToTree($obj->roomName, 1);
        $lastClass = "";
      }
      
      if ($obj->featureClassesId != $lastClass)
      {
        if ($lastClass != "")
        {
          $treeElements .= $closeTreeFolder; // letzte instance
          $treeElements .= $closeTreeFolder; // letzte featureclass
        }
        
        $lastClass = $obj->featureClassesId;
        $treeElements .= addToTree($obj->featureClassName, 1);
        $lastInstance = "";
      }
      
      if ($obj->featureInstanceId != $lastInstance)
      {
        if ($lastInstance != "")
          $treeElements .= $closeTreeFolder; // letzte instance
        $lastInstance = $obj->featureInstanceId;
        $treeElements .= addToTree($obj->featureInstanceName, 1);
      }
      
      $treeElements .= addToTree("<a href='editRules.php?action=addAction&submitted=1&groupId=$groupId&ruleId=$ruleId&featureInstanceId=" . $obj->featureInstanceId . "&featureFunctionId=" . $obj->featureFunctionId . "'>" . i18n($obj->featureFunctionName) . "</a>", 0);
    }
    
    $treeElements .= $closeTreeFolder; // letzte instance
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter raum
    

    $lastRoom = "";
    $lastController = "";
    $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 controller.id as controllerId, controller.name as controllerName,
                                 featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName
                                 
                                 from featureInstances
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join controller on (featureInstances.controllerId = controller.id)
                                 join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                                 
                                 where featureFunctions.type='ACTION' $orFunction
                                 order by controllerName, featureClassName,featureInstanceName,featureFunctionName");
    while ( $obj = mysql_fetch_object($erg) )
    {
      if ($myGroupFeatures[$obj->featureInstanceId] == "" && $withFunctions!=1)
        continue;
      if ($ready[$obj->featureInstanceId] == 1)
        continue;
      if ($allMyRuleActions[$obj->featureInstanceId] != "")
        continue;
      
      if ($lastRoom == "")
      {
        $lastRoom = "dummy";
        $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
      }
      
      if ($obj->controllerId != $lastController)
      {
        if ($lastController != "")
        {
          $treeElements .= $closeTreeFolder; // letztes instance
          $treeElements .= $closeTreeFolder; // letzte class
          $treeElements .= $closeTreeFolder; // letzter controller
        }
        $lastController = $obj->controllerId;
        $treeElements .= addToTree($obj->controllerName, 1);
        $lastClass = "";
      }
      
      if ($obj->featureClassesId != $lastClass)
      {
        if ($lastClass != "")
        {
          $treeElements .= $closeTreeFolder; // letzte instance
          $treeElements .= $closeTreeFolder; // letzte featureclass
        }
        
        $lastClass = $obj->featureClassesId;
        $treeElements .= addToTree($obj->featureClassName, 1);
        $lastInstance = "";
      }
      
      if ($obj->featureInstanceId != $lastInstance)
      {
        if ($lastInstance != "")
          $treeElements .= $closeTreeFolder; // letzte instance
        $lastInstance = $obj->featureInstanceId;
        $treeElements .= addToTree($obj->featureInstanceName, 1);
      }
      
      $treeElements .= addToTree("<a href='editRules.php?action=addAction&submitted=1&groupId=$groupId&ruleId=$ruleId&featureInstanceId=" . $obj->featureInstanceId . "&featureFunctionId=" . $obj->featureFunctionId . "'>" . i18n($obj->featureFunctionName) . "</a>", 0);
    }
    
    $treeElements .= $closeTreeFolder; // letzte instance
    $treeElements .= $closeTreeFolder; // letzte featureclass
    $treeElements .= $closeTreeFolder; // letzter controller
    $treeElements .= $closeTreeFolder; // letzter raum
    

    $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
    show();
  }
}
/*else if ($action == "chooseSignalAlias")
{
  $tasterClassesId = getClassesIdByName("Taster");
  $irClassesId = getClassesIdByName("IR-Sensor");
  
  setupTreeAndContent("addRuleSignal_design.html");
  removeTag("%OPT_ADD_OTHERS%",$html);
  
  $ruleId = $id;
  
  $html = str_replace("signalLiveMode.php", "signalLiveModeSensorAlias.php", $html);
  
  $closeTreeFolder = "</ul></li> \n";
  
  $treeElements = "";
  $treeElements .= addToTree("<a href='showSensorRules.php?id=$id'>Sensoralias wählen, dessen Regeln übernommen werden sollen</a>", 1);
  $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
  
  $logicalButtonClass = getClassesIdByName("LogicalButton");
  
  unset($ready);
  $lastRoom = "";
  $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName
                                 from rooms
                                 join roomFeatures on (roomFeatures.roomId = rooms.id)
                                 join featureInstances on (featureInstances.id = roomFeatures.featureInstanceId)
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 where (featureInstances.featureClassesId='$tasterClassesId' or featureInstances.featureClassesId='$irClassesId')
                                 order by roomName,featureClassName,featureInstanceName");
  while ( $obj = mysql_fetch_object($erg) )
  {
    if ($ready[$obj->featureInstanceId] == 1)
      continue;
    
    $ready[$obj->featureInstanceId] = 1;
    
    if ($obj->roomId != $lastRoom)
    {
      if ($lastRoom != "")
        $treeElements .= $closeTreeFolder; // letzter raum
      

      $lastRoom = $obj->roomId;
      $treeElements .= addToTree($obj->roomName, 1);
    }
    
    $treeElements .= addToTree("<a href='showSensorRules.php?action=$action&id=$id&submitted=1&featureInstanceId=" . $obj->featureInstanceId . "'>" . $obj->featureInstanceName . "</a>", 0);
  }
  
  $treeElements .= $closeTreeFolder; // letzter raum
  

  $lastRoom = "";
  $lastController = "";
  $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 controller.id as controllerId, controller.name as controllerName
                                 from featureInstances
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join controller on (featureInstances.controllerId = controller.id)
                                 where (featureInstances.featureClassesId='$tasterClassesId'  or featureInstances.featureClassesId='$irClassesId')
                                 order by controllerName, featureClassName,featureInstanceName");
  while ( $obj = mysql_fetch_object($erg) )
  {
    if ($ready[$obj->featureInstanceId] == 1)
      continue;
    
    if ($lastRoom == "")
    {
      $lastRoom = "dummy";
      $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
    }
    
    if ($obj->controllerId != $lastController)
    {
      if ($lastController != "")
        $treeElements .= $closeTreeFolder; // letzter controller
      

      $lastController = $obj->controllerId;
      $treeElements .= addToTree($obj->controllerName, 1);
    }
    
    $treeElements .= addToTree("<a href='showSensorRules.php?action=$action&id=$id&submitted=1&featureInstanceId=" . $obj->featureInstanceId . "'>" . $obj->featureInstanceName . "</a>", 0);
  }
  
  $treeElements .= $closeTreeFolder; // letzter controller
  $treeElements .= $closeTreeFolder; // letzter raum
  

  $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
  $html = str_replace("%GROUP_ID%", $groupId, $html);
  $html = str_replace("%RULE_ID%", $ruleId, $html);
  $html = str_replace("%ACTION%", $action, $html);
  show();
}*/
else if ($action == "editButton")
{
  $tasterClassesId = getClassesIdByName("Taster");
  $irClassesId = getClassesIdByName("IR-Sensor");
  
  setupTreeAndContent("addRuleSignal_design.html");
  removeTag("%OPT_ADD_OTHERS%",$html);
  
  $ruleId = $id;
  
  $html = str_replace("signalLiveMode.php?", "signalLiveModeButtonSignal.php?pageId=$pageId&buttonId=$buttonId&", $html);
  
  $closeTreeFolder = "</ul></li> \n";
  
  $treeElements = "";
  $treeElements .= addToTree("<a href='editButtonPage.php?pageId=$pageId'>Signal für den Button auswählen</a>", 1);
  $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
  
  $logicalButtonClass = getClassesIdByName("LogicalButton");
  
  unset($ready);
  $lastRoom = "";
  $erg = QUERY("select rooms.id as roomId, rooms.name as roomName,
                                 roomFeatures.featureInstanceId,
                                 featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName
                                 from rooms
                                 join roomFeatures on (roomFeatures.roomId = rooms.id)
                                 join featureInstances on (featureInstances.id = roomFeatures.featureInstanceId)
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 where (featureInstances.featureClassesId='$tasterClassesId' or featureInstances.featureClassesId='$irClassesId')
                                 order by roomName,featureClassName,featureInstanceName");
  while ( $obj = mysql_fetch_object($erg) )
  {
    if ($ready[$obj->featureInstanceId] == 1)
      continue;
    
    $ready[$obj->featureInstanceId] = 1;
    
    if ($obj->roomId != $lastRoom)
    {
      if ($lastRoom != "")
        $treeElements .= $closeTreeFolder; // letzter raum
      

      $lastRoom = $obj->roomId;
      $treeElements .= addToTree($obj->roomName, 1);
    }
    
    $treeElements .= addToTree("<a href='editButtonPage.php?action=$action&pageId=$pageId&buttonId=$buttonId&featureInstanceId=" . $obj->featureInstanceId . "'>" . $obj->featureInstanceName . "</a>", 0);
  }
  
  $treeElements .= $closeTreeFolder; // letzter raum
  

  $lastRoom = "";
  $lastController = "";
  $erg = QUERY("select featureInstances.id as featureInstanceId, featureInstances.name as featureInstanceName, featureInstances.featureClassesId,
                                 featureClasses.name as featureClassName,
                                 controller.id as controllerId, controller.name as controllerName
                                 from featureInstances
                                 join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                                 join controller on (featureInstances.controllerId = controller.id)
                                 where (featureInstances.featureClassesId='$tasterClassesId'  or featureInstances.featureClassesId='$irClassesId')
                                 order by controllerName, featureClassName,featureInstanceName");
  while ( $obj = mysql_fetch_object($erg) )
  {
    if ($ready[$obj->featureInstanceId] == 1)
      continue;
    
    if ($lastRoom == "")
    {
      $lastRoom = "dummy";
      $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
    }
    
    if ($obj->controllerId != $lastController)
    {
      if ($lastController != "")
        $treeElements .= $closeTreeFolder; // letzter controller
      

      $lastController = $obj->controllerId;
      $treeElements .= addToTree($obj->controllerName, 1);
    }
    
    $treeElements .= addToTree("<a href='editButtonPage.php?action=$action&pageId=$pageId&buttonId=$buttonId&featureInstanceId=" . $obj->featureInstanceId . "'>" . $obj->featureInstanceName . "</a>", 0);
  }
  
  $treeElements .= $closeTreeFolder; // letzter controller
  $treeElements .= $closeTreeFolder; // letzter raum
  

  $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
  $html = str_replace("%GROUP_ID%", $groupId, $html);
  $html = str_replace("%RULE_ID%", $ruleId, $html);
  $html = str_replace("%ACTION%", $action, $html);
  show();
}
else if ($action == "removeSignal") deleteRuleSignal($signalId);
else if ($action == "removeAction") deleteRuleAction($actionId);
else if ($action == "addRule") QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute) values('$groupId','7','31','255','7','31','255')");
else if ($action == "addTwoRules")
{
  $allGroupStates = readGroupStates();
  $myStatesNo = 0;
  
  foreach ( (array)$allGroupStates as $obj )
  {
    if ($obj->groupId == $groupId)
    {
      $myStates[$myStatesNo++] = $obj->id;
      if ($myStatesNo == 2)
        break;
    }
  }
  
  $first = $myStates[0];
  $second = $myStates[1];
  
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId) values('$groupId','7','31','255','7','31','255','$first','$second')");
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId) values('$groupId','7','31','255','7','31','255','$second','$first')");
}
else if ($action == "deleteRule") deleteRule($ruleId);
else if ($action == "changeActivationState") QUERY("UPDATE rules set activationStateId='$activationStateId' where id='$ruleId' limit 1");
else if ($action == "changeStartDay") QUERY("UPDATE rules set startDay='$startDay' where id='$ruleId' limit 1");
else if ($action == "changeEndDay") QUERY("UPDATE rules set endDay='$endDay' where id='$ruleId' limit 1");
else if ($action == "changeStartTime") QUERY("UPDATE rules set startHour='$startTime' where id='$ruleId' limit 1");
else if ($action == "changeStartTimeMinute") QUERY("UPDATE rules set startMinute='$startTimeMinute' where id='$ruleId' limit 1");
else if ($action == "changeEndTime") QUERY("UPDATE rules set endHour='$endTime' where id='$ruleId' limit 1");
else if ($action == "changeEndTimeMinute") QUERY("UPDATE rules set endMinute='$endTimeMinute' where id='$ruleId' limit 1");
else if ($action=="changeLock")
{
  if ($lockState=="true") $value=1;
  else $value=0;
  QUERY("UPDATE rules set groupLock='$value' where id='$ruleId' limit 1");
}
else if ($action=="changeIntraday")
{
  if ($intradayState=="true") $value=1;
  else $value=0;
  QUERY("UPDATE rules set intraDay='$value' where id='$ruleId' limit 1");
}
else if ($action == "changeResultingState") QUERY("UPDATE rules set resultingStateId='$resultingStateId' where id='$ruleId' limit 1");
else if ($action == "editActionParams")
{
  if ($submitted == 1)
  {
    QUERY("delete from ruleActionParams where ruleActionId='$ruleActionId'");
    trace("ruleActionParam mit ruleActionId $ruleActionId glöscht");
    
    $erg = QUERY("select id from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
    while ( $row = MYSQL_FETCH_ROW($erg) )
    {
      $value = "param" . $row[0];
      $value = $$value;
      $value = addslashes($value);
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) values('$ruleActionId','$row[0]','$value')");
    }
    
    header("Location: editRules.php?groupId=$groupId");
    exit();
  }
  else
  {
    setupTreeAndContent("editRuleActionParams_design.html");
    
    $erg = QUERY("select featureFunctionParamsId,paramValue,id from ruleActionParams where ruleActionId='$ruleActionId'");
    while ( $obj = mysql_fetch_object($erg) )
    {
      $myValues[$obj->featureFunctionParamsId] = $obj->paramValue;
      $ruleActionIds[$obj->featureFunctionParamsId] = $obj->id;
    }
    
    $html = str_replace("%GROUP_ID%", $groupId, $html);
    $html = str_replace("%RULE_ACTION_ID%", $ruleActionId, $html);
    $html = str_replace("%FEATURE_FUNCTION_ID%", $featureFunctionId, $html);
    
    $ansicht = $_SESSION["ansicht"];
    
    $erg = QUERY("select featureFunctionId from ruleActions where id='$ruleActionId' limit 1");
    if ($row = MYSQL_FETCH_ROW($erg))
    {
      $featureFunctionId = $row[0];
      
      $paramTag = getTag("%PARAM%", $html);
      $params = "";
      $erg2 = QUERY("select id,name,type,comment,view from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
      while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
      {
        if ($ansicht == "Experte" && $obj2->view == "Entwickler") continue;
        if ($ansicht == "Standard" && ($obj2->view == "Experte" || $obj2->view == "Entwickler")) continue;
        
        $actParamsTag = $paramTag;
        $actParamsTag = str_replace("%PARAM_NAME%", i18n($obj2->name), $actParamsTag);
        
        $myValue = $myValues[$obj2->id];
        
        if ($obj2->type == "ENUM")
        {
          $type = "<select name='param" . $obj2->id . "'>";
          $erg3 = QUERY("select id,name,value from featureFunctionEnums where featureFunctionId='$featureFunctionId' and paramId='$obj2->id' order by id");
          while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
          {
            if ($myValue == $obj3->value) $selected = "selected";
            else $selected = "";
            
            $type .= "<option value='$obj3->value' $selected>" . i18n($obj3->name);
          }
          $type .= "</select>";
        }
        else if ($obj2->name == "command")
        {
        	 $type = "<textarea name='param" . $obj2->id . "' rows=6 cols=80>$myValue</textarea></tr>
        	 <tr><td align=right colspan=2><a href='executor.php?id=".$ruleActionIds[$obj2->id]."' target='_blank'>Diesen Befehl aufrufen</a></tr>
        	 <tr><td>";
        }
        else $type = "<input name='param" . $obj2->id . "' type='text' size=9 value='$myValue'>";
        
        $actParamsTag = str_replace("%PARAM_ENTRY%", $type, $actParamsTag);
        $actParamsTag = str_replace("%COMMENT%", $obj2->comment, $actParamsTag);
        $params .= $actParamsTag;
      }
      
      $html = str_replace("%PARAM%", $params, $html);
      
      if ($params == "")
      {
        getTag("%OPT_SUBMIT%", $html);
        $html = str_replace("%OPT_SUBMIT%", "Keine Parameter vorhanden", $html);
      }
      else chooseTag("%OPT_SUBMIT%", $html);
      
      show();
    }
    else die("Fehlerhafte ruleActionId $ruleActionId");
  }
}
else if ($action == "editSignalParams")
{
  if ($submitted == 1)
  {
    QUERY("delete from ruleSignalParams where ruleSignalId='$ruleSignalId'");
    trace("ruleSignalParam mit ruleSignalId $ruleSignalId glöscht");
    
    $erg = QUERY("select id,type from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
    while ( $row = MYSQL_FETCH_ROW($erg) )
    {
      if ($row[1] == "WEEKTIME")
      {
        $value = "param" . $row[0] . "Day";
        $day = $$value;
        $value = "param" . $row[0] . "Hour";
        $hour = $$value;
        $value = "param" . $row[0] . "Minute";
        $minute = $$value;
        $value = toWeekTime($day, $hour, $minute);
      }
      else
      {
        $value = "param" . $row[0];
        $value = $$value;
      }
      QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) values('$ruleSignalId','$row[0]','$value')");
    }
    
    if ($logicalGroupMode == 1)
      header("Location: editLogicalSignals.php?groupId=$groupId");
    else
      header("Location: editRules.php?groupId=$groupId");
    exit();
  }
  else
  {
    setupTreeAndContent("editRuleSignalParams_design.html");
    
    $erg = QUERY("select featureFunctionParamsId,paramValue from ruleSignalParams where ruleSignalId='$ruleSignalId'");
    while ( $obj = mysql_fetch_object($erg) )
    {
      $myValues[$obj->featureFunctionParamsId] = $obj->paramValue;
    }
    $html = str_replace("%GROUP_ID%", $groupId, $html);
    $html = str_replace("%RULE_SIGNAL_ID%", $ruleSignalId, $html);
    $html = str_replace("%LOGICAL_GROUP_MODE%", $logicalGroupMode, $html);
    
    $erg = QUERY("select featureFunctionId from ruleSignals where id='$ruleSignalId' limit 1");
    if ($row = MYSQL_FETCH_ROW($erg))
    {
      $featureFunctionId = $row[0];
      
      /*if ($featureFunctionId == 0) // evTime vom Controller
      {
      	$paramsId = getClassesIdFunctionParamIdByName(getClassesIdByName("controller"),"evTime","weektime");
      	$erg = query("select featureFunctionId from featureFunctionParams where id='$paramsId' limit 1");
      	$row = MYSQL_FETCH_ROW($erg);
      	$featureFunctionId = $row[0];
      }*/
      
      $ansicht = $_SESSION["ansicht"];
      $paramTag = getTag("%PARAM%", $html);
      $params = "";
      $erg2 = QUERY("select id,name,type,comment,view from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
      while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
      {
        if ($ansicht == "Experte" && $obj2->view == "Entwickler")
          continue;
        if ($ansicht == "Standard" && ($obj2->view == "Experte" || $obj2->view == "Entwickler"))
          continue;
        
        $actParamsTag = $paramTag;
        $actParamsTag = str_replace("%PARAM_NAME%", i18n($obj2->name), $actParamsTag);
        
        $myValue = $myValues[$obj2->id];
        
        if ($obj2->type == "ENUM")
        {
          $type = "<select name='param" . $obj2->id . "'>";
          $erg3 = QUERY("select id,name,value from featureFunctionEnums where featureFunctionId='$featureFunctionId' and paramId='$obj2->id' order by id");
          while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
          {
            if ($myValue == $obj3->value)
              $selected = "selected";
            else
              $selected = "";
            
            $type .= "<option value='$obj3->value' $selected>" . i18n($obj3->name);
          }
          $type .= "</select>";
        }
        else if ($obj2->type == "WEEKTIME")
        {
          $type = getWeekTime("param" . $obj2->id, $myValue);
        }
        else
        {
          $type = "<input name='param" . $obj2->id . "' type='text' size=9 value='$myValue'>";
        }
        
        $actParamsTag = str_replace("%PARAM_ENTRY%", $type, $actParamsTag);
        $actParamsTag = str_replace("%COMMENT%", $obj2->comment, $actParamsTag);
        $params .= $actParamsTag;
      }
      
      $html = str_replace("%PARAM%", $params, $html);
      
      if ($params == "")
      {
        getTag("%OPT_SUBMIT%", $html);
        $html = str_replace("%OPT_SUBMIT%", "Keine Parameter vorhanden", $html);
      }
      else
        chooseTag("%OPT_SUBMIT%", $html);
      
      $html = str_replace("%FEATURE_FUNCTION_ID%", $featureFunctionId, $html);
      
      show();
    }
    else
      die("Fehlerhafte ruleSignalId $ruleSignalId");
  }
}
else if ($action == "addSignalCopy")
{
  $erg = QUERY("select id from rules where groupId='$groupId' and id<'$ruleId' and generated!=1 order by id desc limit 1");
  if ($obj = MYSQL_FETCH_OBJECT($erg))
  {
    $parentId = $obj->id;
    
    $erg2 = QUERY("select id,featureInstanceId,featureFunctionId from ruleSignals where ruleId='$parentId' order by id");
    while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
    {
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
  		                               values('$ruleId','$obj2->featureInstanceId','$obj2->featureFunctionId')");
      $newRuleSignalId = mysql_insert_id();
      
      $erg3 = QUERY("select featureFunctionParamsId,paramValue from ruleSignalParams where ruleSignalId='$obj2->id'");
      while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
      {
        QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
     		                                    values('$newRuleSignalId','$obj3->featureFunctionParamsId','$obj3->paramValue')");
      }
    }
  }
}
else if ($action == "addActionCopy")
{
  $erg = QUERY("select id from rules where groupId='$groupId' and id<'$ruleId' and generated!=1 order by id desc limit 1");
  if ($obj = MYSQL_FETCH_OBJECT($erg))
  {
    $parentId = $obj->id;
    
    $erg2 = QUERY("select id,featureInstanceId,featureFunctionId from ruleActions where ruleId='$parentId' order by id");
    while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
    {
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
  		                               values('$ruleId','$obj2->featureInstanceId','$obj2->featureFunctionId')");
      $newRuleActionId = mysql_insert_id();
      
      $erg3 = QUERY("select featureFunctionParamsId,paramValue from ruleActionParams where ruleActionId='$obj2->id'");
      while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
      {
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                    values('$newRuleActionId','$obj3->featureFunctionParamsId','$obj3->paramValue')");
      }
    }
  }
}
else if ($action == "copyToClipboard")
{
  $_SESSION["copyRules"] = $groupId;
  $message = "Regeln wurden zum Kopieren markiert";
}
else if ($action == "insertFromClipboard")
{
	$myDebug=0;
	
  if (! isset($_SESSION["copyRules"]))
    $message = "Keine Regeln zum Einfügen vorhanden";
  else
  {
    $copyGroupId = $_SESSION["copyRules"];
    
    // States nach Name anlegen, falls nicht vorhanden
    $erg = QUERY("select * from rules where groupId='$copyGroupId' and generated=0 order by id");
    while ( $obj = MYSQL_FETCH_OBJECT($erg) )
    {
      if ($obj->activationStateId == "0") $myActivationStateId = 0;
      else
      {
        $erg2 = QUERY("select name,basics from groupStates where id='$obj->activationStateId' limit 1");
        if ($row2 = MYSQL_FETCH_ROW($erg2))
        {
          $activationStateName = $row2[0];
          $activationBasics = $row2[1];
          
          if ($myDebug==1) echo $activationStateName." - ".$activationBasics."<br>";
          
          $myActivationStateId = - 1;
          $erg2 = QUERY("select id from groupStates where groupId='$groupId' and name='$activationStateName' limit 1");
          if ($row2 = MYSQL_FETCH_ROW($erg2))
          {
          	$myActivationStateId = $row2[0];
          	if ($myDebug==1) echo "gefunden mit id ".$myActivationStateId."<br>";
          }
          else
          {
          	if ($myDebug==1) echo "nicht gefunden <br>";
            $erg2 = QUERY("Select max(value) from groupStates where groupId='$groupId'");
            if ($row2 = MYSQL_FETCH_ROW($erg2)) $nextValue = $row2[0] + 1;
            else $nextValue = 0;
            
            $sql = "INSERT into groupStates (groupId, name, value,basics) values('$groupId','" . mysql_real_escape_string($activationStateName) . "','$nextValue','$activationBasics')";
            if ($myDebug==1) echo $sql."<br>";
            else QUERY($sql);
            $myActivationStateId = mysql_insert_id();
          }
        }
      }
      
      if ($obj->resultingStateId == "0") $myResultingStateId = 0;
      else
      {
        $erg2 = QUERY("select name,basics from groupStates where id='$obj->resultingStateId' limit 1");
        if ($row2 = MYSQL_FETCH_ROW($erg2))
        {
          $resultingStateName = $row2[0];
          $resultingBasics = $row2[1];
          
          if ($myDebug==1) echo $resultingStateName." - ".$resultingBasics."<br>";
          
          $myResultingStateId = - 1;
          $erg2 = QUERY("select id from groupStates where groupId='$groupId' and name='$resultingStateName' limit 1");
          if ($row2 = MYSQL_FETCH_ROW($erg2))
          {
          	$myResultingStateId = $row2[0];
          	if ($myDebug==1) echo "gefunden mit id ".$myResultingStateId."<br>";
          }
          else
          {
          	if ($myDebug==1) echo "nicht gefunden <br>";
          	
            $erg2 = QUERY("Select max(value) from groupStates where groupId='$groupId'");
            if ($row2 = MYSQL_FETCH_ROW($erg2)) $nextValue = $row2[0] + 1;
            else $nextValue = 0;
            
            $sql = "INSERT into groupStates (groupId, name, value,basics) values('$groupId','" . mysql_real_escape_string($resultingStateName) . "','$nextValue','$resultingBasics')"; 
            if ($myDebug==1) echo $sql."<br>";
            else QUERY($sql);
            $myResultingStateId = mysql_insert_id();
          }
        }
      }
      
      $sql = "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,intraDay) values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$myActivationStateId','$myResultingStateId','$obj->intraDay')";

      if ($myDebug==1) echo $sql."<br>";                                  
      else QUERY($sql);
      $ruleId = mysql_insert_id();
      
      // Signale Kopieren
      $erg2 = QUERY("select id,featureInstanceId,featureFunctionId from ruleSignals where ruleId='$obj->id' order by id desc");
      while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
      {
      	$sql = "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$obj2->featureInstanceId','$obj2->featureFunctionId')";
      	if ($myDebug==1) echo $sql."<br>"; 
        else QUERY($sql);
        $newRuleSignalId = mysql_insert_id();
        
        $erg3 = QUERY("select featureFunctionParamsId,paramValue from ruleSignalParams where ruleSignalId='$obj2->id'");
        while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
        {
        	$sql="INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) values('$newRuleSignalId','$obj3->featureFunctionParamsId','$obj3->paramValue')";
        	if ($myDebug==1) echo $sql."<br>"; 
          else QUERY($sql);
        }
      }
      
      unset($done);
      // Actions kopieren und umbauen
      $erg2 = QUERY("select id,featureInstanceId,featureFunctionId from ruleActions where ruleId='$obj->id' order by id desc");
      while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
      {
        // Wenn es die gleiche Funktion in der neuen Gruppen gibt, übernehmen wir wie und tauschen die InstanzID aus
        $erg3 = QUERY("select featureClassesId from featureInstances where id='$obj2->featureInstanceId' limit 1");
        $row3 = MYSQL_FETCH_ROW($erg3);
        $actClassesId = $row3[0];
        
        $foundInstanceId = - 1;
        $erg3 = QUERY("select featureInstanceId from groupFeatures where groupId='$groupId'");
        while ( $row3 = MYSQL_FETCH_ROW($erg3) )
        {
          $erg4 = QUERY("select featureClassesId from featureInstances where id='$row3[0]' limit 1");
          if ($row4 = MYSQL_FETCH_ROW($erg4))
          {
            if ($row4[0] == $actClassesId && $done[$row3[0]] != 1)
            {
              $foundInstanceId = $row3[0];
              $done[$row3[0]] = 1;
              break;
            }
          }
        }
        
        if ($foundInstanceId != - 1)
        {
        	$sql = "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$foundInstanceId','$obj2->featureFunctionId')";
        	if ($myDebug==1) echo $sql."<br>"; 
          else QUERY($sql);
          
          $newRuleActionId = mysql_insert_id();
          
          $erg3 = QUERY("select featureFunctionParamsId,paramValue from ruleActionParams where ruleActionId='$obj2->id'");
          while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
          {
          	$sql="INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) values('$newRuleActionId','$obj3->featureFunctionParamsId','$obj3->paramValue')";
          	if ($myDebug==1) echo $sql."<br>"; 
            else QUERY($sql);
          }
        }
      }
    }
  }
}
else if ($action == "doubleActions")
{
  $erg = QUERY("select * from rules where groupId='$groupId' order by id");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
    QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,intraDay) 
                        values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$obj->activationStateId','$obj->resultingStateId','$obj->intraDay')");
    $ruleId = mysql_insert_id();
    
    // Actions kopieren
    $erg2 = QUERY("select id,featureInstanceId,featureFunctionId from ruleActions where ruleId='$obj->id' order by id");
    while ( $obj2 = MYSQL_FETCH_OBJECT($erg2) )
    {
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
	                              values('$ruleId','$obj2->featureInstanceId','$obj2->featureFunctionId')");
      $newRuleActionId = mysql_insert_id();
      
      $erg3 = QUERY("select featureFunctionParamsId,paramValue from ruleActionParams where ruleActionId='$obj2->id'");
      while ( $obj3 = MYSQL_FETCH_OBJECT($erg3) )
      {
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
 		                                   values('$newRuleActionId','$obj3->featureFunctionParamsId','$obj3->paramValue')");
      }
    }
  }
}
else if ($action == "addNewRules")
{
  $rolloClassesId = getClassesIdByName("Rollladen");
  $dimmerClassesId = getClassesIdByName("Dimmer");
  $schalterClassesId = getClassesIdByName("Schalter");
  
  // Standardstates anlegen
  $erg = MYSQL_QUERY("select id from groupStates where groupId='$groupId' and name='1' limit 1") or die(MYSQL_ERROR());
  $row = MYSQL_FETCH_ROW($erg);
  $firstState = $row[0];
  $erg = MYSQL_QUERY("select id from groupStates where groupId='$groupId' and name='2' limit 1") or die(MYSQL_ERROR());
  $row = MYSQL_FETCH_ROW($erg);
  $secondState = $row[0];
  
  // evOn
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$firstState','$secondState','evOn')");
  $myRuleIds[0] = mysql_insert_id();
  // evOff
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$secondState','$firstState','evOff')");
  $myRuleIds[1] = mysql_insert_id();
  
  $erg = QUERY("select featureInstanceId,featureClassesId
	                    from groupFeatures 
	                    join featureInstances on (featureInstances.id=featureInstanceId) 
	                    where groupId='$groupId'");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
    $featureInstanceId = $obj->featureInstanceId;
    
    // Rollos
    if ($obj->featureClassesId == $rolloClassesId)
    {
      $startFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "start");
      $stopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "stop");
      $paramToOpen = getFunctionParamEnumValueForClassesIdByName($rolloClassesId, "start", "direction", "TO_OPEN");
      $paramToClose = getFunctionParamEnumValueForClassesIdByName($rolloClassesId, "start", "direction", "TO_CLOSE");
      $paramToToggle = getFunctionParamEnumValueForClassesIdByName($rolloClassesId, "start", "direction", "TOGGLE");
      
      // DUMMY bei evOn
      $ruleId = $myRuleIds[0];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evStopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "evOpen");
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evStopFunctionId')");
      /*$signalId=mysql_insert_id();
  
         $rolloParamPositionId = getClassesIdFunctionParamIdByName($rolloClassesId,"evStop","position");
         QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                       values('$signalId','$rolloParamPositionId','0')");
                                       */
      
      // DUMMY bei evOff
      $ruleId = $myRuleIds[1];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evStopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "evClosed");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evStopFunctionId')");
      /*$signalId=mysql_insert_id();

         $rolloParamPositionId = getClassesIdFunctionParamIdByName($rolloClassesId,"evStop","position");
 		     QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                       values('$signalId','$rolloParamPositionId','255')");
                                       */
    }
    // Dimmer
    else if ($obj->featureClassesId == $dimmerClassesId)
    {
      // Dummy für evOn                              
      $ruleId = $myRuleIds[0];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOnFunctionId = getClassesIdFunctionsIdByName($dimmerClassesId, "evOn");
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evOnFunctionId')");
      $signalId = mysql_insert_id();
      
      $dimmerParamBrightnessId = getClassesIdFunctionParamIdByName($dimmerClassesId, "evOn", "brightness");
      QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                       values('$signalId','$dimmerParamBrightnessId','$signalParamWildcard')");
      
      // DUMMY für evOff
      $ruleId = $myRuleIds[1];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOffFunctionId = getClassesIdFunctionsIdByName($dimmerClassesId, "evOff");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
 	                                values('$ruleId','$featureInstanceId','$evOffFunctionId')");
    }
    // Schalter
    else if ($obj->featureClassesId == $schalterClassesId)
    {
      // DUMMY bei evOn
      $ruleId = $myRuleIds[0];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOnFunctionId = getClassesIdFunctionsIdByName($schalterClassesId, "evOn");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evOnFunctionId')");
      
      // DUMMY bei evOff
      $ruleId = $myRuleIds[1];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOffFunctionId = getClassesIdFunctionsIdByName($schalterClassesId, "evOff");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evOffFunctionId')");
    }
  } //while
}
else if ($action == "defaultActions")
{
  // deprecated
  

  if ($confirm != 1)
  {
    $message = "Achtung!<br><br>Wenn Sie die Standardaktionen wiederherstellen, werden alle aktuellen Regeln zu diesem Aktor gelöscht und dann der Standard erstellt.<br><br><a href='editRules.php?groupId=$groupId&confirm=1&action=defaultActions&template=$template'>Ja, Standardregeln herstellen</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='editRules.php?groupId=$groupId'>Nein, zurück</a>";
    setupTreeAndContent("", $message);
    show();
  }
  
  // Alles löschen  		 	   
  QUERY("delete from groupStates where groupId='$groupId'");
  $erg = QUERY("select id from rules where groupId='$groupId'");
  while ( $row = MYSQL_FETCH_ROW($erg) )
  {
    deleteRule($row[0]);
  }
  
  // Standardstates anlegen
  QUERY("INSERT into groupStates (groupId, name) values('$groupId','1')");
  $firstState = mysql_insert_id();
  QUERY("INSERT into groupStates (groupId, name) values('$groupId','2')");
  $secondState = mysql_insert_id();
  
  $rolloClassesId = getClassesIdByName("Rollladen");
  $dimmerClassesId = getClassesIdByName("Dimmer");
  $schalterClassesId = getClassesIdByName("Schalter");
  
  // Standardregeln 
      // click
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$firstState','$secondState','click')");
  $myRuleIds[0] = mysql_insert_id();
  // click
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$secondState','$firstState','click')");
  $myRuleIds[1] = mysql_insert_id();
  
  // hold start
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','0','0','holdStart')");
  $myRuleIds[2] = mysql_insert_id();
  
  // hold end
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','0','0','holdEnd')");
  $myRuleIds[3] = mysql_insert_id();
  
  // doubleClick
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','0','0','doubleClick')");
  $myRuleIds[4] = mysql_insert_id();
  
  // covered
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$firstState','$secondState','covered')");
  $myRuleIds[5] = mysql_insert_id();
  // covered
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$secondState','$firstState','covered')");
  $myRuleIds[6] = mysql_insert_id();
  
  // evOn
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$firstState','$secondState','evOn')");
  $myRuleIds[7] = mysql_insert_id();
  // evOff
  QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType) 
                     values('$groupId','7','31','255','7','31','255','$secondState','$firstState','evOff')");
  $myRuleIds[8] = mysql_insert_id();
  
  $isHomogenous = 1;
  //$mySingleClassesId=-1;
  $last = - 1;
  $erg = QUERY("select featureInstanceId,featureClassesId
	                    from groupFeatures 
	                    join featureInstances on (featureInstances.id=featureInstanceId) 
	                    where groupId='$groupId'");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
    //if($mySingleClassesId!=-1) $mySingleClassesId=-2;
      //else $mySingleClassesId=$obj->featureClassesId;
    

    if ($last == - 1)
      $last = $obj->featureClassesId;
    else if ($last != $obj->featureClassesId)
    {
      $isHomogenous = 0;
      break;
    }
  }
  
  $erg = QUERY("select featureInstanceId,featureClassesId
	                    from groupFeatures 
	                    join featureInstances on (featureInstances.id=featureInstanceId) 
	                    where groupId='$groupId'");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
    $featureInstanceId = $obj->featureInstanceId;
    
    // Rollos
    if ($obj->featureClassesId == $rolloClassesId)
    {
      $startFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "start");
      $stopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "stop");
      $paramToOpen = getFunctionParamEnumValueForClassesIdByName($rolloClassesId, "start", "direction", "TO_OPEN");
      $paramToClose = getFunctionParamEnumValueForClassesIdByName($rolloClassesId, "start", "direction", "TO_CLOSE");
      $paramToToggle = getFunctionParamEnumValueForClassesIdByName($rolloClassesId, "start", "direction", "TOGGLE");
      
      if ($template == "1")
      {
        // Hochfahren bei Click (beide Regeln gleich belegen)
        $ruleId = $myRuleIds[0];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
     		                                  values('$ruleId','$featureInstanceId','$startFunctionId')");
        $newRuleActionId = mysql_insert_id();
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                       values('$newRuleActionId','142','$paramToOpen')");
        
        // Hochfahren bei Click (beide Regeln gleich belegen)
        $ruleId = $myRuleIds[1];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
     		                                  values('$ruleId','$featureInstanceId','$startFunctionId')");
        $newRuleActionId = mysql_insert_id();
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                       values('$newRuleActionId','142','$paramToOpen')");
        
        // Runterfahren bei holdStart
        $ruleId = $myRuleIds[2];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
      		                               values('$ruleId','$featureInstanceId','$startFunctionId')");
        $newRuleActionId = mysql_insert_id();
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                       values('$newRuleActionId','142','$paramToClose')");
        
        // Nichts zun bei HoldEnd
      

        // STOP bei doppelclick
        $ruleId = $myRuleIds[4];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
       		                                values('$ruleId','$featureInstanceId','$stopFunctionId')");
      }
      else if ($template == "2")
      {
        // Hochfahren (beide regeln gleich belegen)
        $ruleId = $myRuleIds[0];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
     		                                  values('$ruleId','$featureInstanceId','$startFunctionId')");
        $newRuleActionId = mysql_insert_id();
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                       values('$newRuleActionId','142','$paramToOpen')");
        
        // Hochfahren (beide regeln gleich belegen)
        $ruleId = $myRuleIds[1];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
     		                                  values('$ruleId','$featureInstanceId','$startFunctionId')");
        $newRuleActionId = mysql_insert_id();
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                       values('$newRuleActionId','142','$paramToOpen')");
        
        // TOGGLE bei HoldStart
        $ruleId = $myRuleIds[2];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
     		                                  values('$ruleId','$featureInstanceId','$startFunctionId')");
        $newRuleActionId = mysql_insert_id();
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                       values('$newRuleActionId','142','$paramToToggle')");
        
        // STOP bei HoldEnd
        $ruleId = $myRuleIds[3];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
       		                                values('$ruleId','$featureInstanceId','$stopFunctionId')");
        
        // Runterfahren bei Doppelclick
        $ruleId = $myRuleIds[4];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
      		                               values('$ruleId','$featureInstanceId','$startFunctionId')");
        $newRuleActionId = mysql_insert_id();
        QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                       values('$newRuleActionId','142','$paramToClose')");
      }
      else
        die("Unbekanntes Template $template");
        
        // DUMMY bei evOn
      $ruleId = $myRuleIds[7];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evStopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "evOpen");
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                 values('$ruleId','$featureInstanceId','$evStopFunctionId')");
      /*$signalId=mysql_insert_id();
  
        $rolloParamPositionId = getClassesIdFunctionParamIdByName($rolloClassesId,"evStop","position");
        QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                      values('$signalId','$rolloParamPositionId','0')");
                                      */
      
      // DUMMY bei evOff
      $ruleId = $myRuleIds[8];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evStopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId, "evClosed");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                 values('$ruleId','$featureInstanceId','$evStopFunctionId')");
      /*$signalId=mysql_insert_id();

        $rolloParamPositionId = getClassesIdFunctionParamIdByName($rolloClassesId,"evStop","position");
 		    QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                      values('$signalId','$rolloParamPositionId','255')");
                                      */
    }
    // Dimmer
    else if ($obj->featureClassesId == $dimmerClassesId)
    {
      // 100% hell bei Click
      $ruleId = $myRuleIds[0];
      
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
      		                               values('$ruleId','$featureInstanceId','25')");
      $newRuleActionId = mysql_insert_id();
      
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                    values('$newRuleActionId','90','100')");
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                    values('$newRuleActionId','91','0')");
      
      // 0% hell bei Click
      $ruleId = $myRuleIds[1];
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
     		                               values('$ruleId','$featureInstanceId','25')");
      $newRuleActionId = mysql_insert_id();
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                    values('$newRuleActionId','90','0')");
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                    values('$newRuleActionId','91','0')");
      
      // DIMMEN Start bei HoldStart
      $ruleId = $myRuleIds[2];
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
     		                          values('$ruleId','$featureInstanceId','64')");
      $newRuleActionId = mysql_insert_id();
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                                    values('$newRuleActionId','115','0')");
      
      // DIMMEN Ende bei HoldEnd
      $ruleId = $myRuleIds[3];
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
      		                        values('$ruleId','$featureInstanceId','65')");
      
      // 50% Helligkeit bei Doppelclick
      $ruleId = $myRuleIds[4];
      QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId)
      	                          values('$ruleId','$featureInstanceId','25')");
      $newRuleActionId = mysql_insert_id();
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                               values('$newRuleActionId','90','50')");
      QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue) 
     		                               values('$newRuleActionId','91','0')");
      
      // Dummy für evOn                              
      $ruleId = $myRuleIds[7];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOnFunctionId = getClassesIdFunctionsIdByName($dimmerClassesId, "evOn");
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evOnFunctionId')");
      $signalId = mysql_insert_id();
      
      $dimmerParamBrightnessId = getClassesIdFunctionParamIdByName($dimmerClassesId, "evOn", "brightness");
      QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                       values('$signalId','$dimmerParamBrightnessId','$signalParamWildcard')");
      
      // DUMMY für evOff
      $ruleId = $myRuleIds[8];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOffFunctionId = getClassesIdFunctionsIdByName($dimmerClassesId, "evOff");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
 	                                values('$ruleId','$featureInstanceId','$evOffFunctionId')");
    }
    // Schalter
    else if ($obj->featureClassesId == $schalterClassesId)
    {
      if ($isHomogenous == 1)
      {
        // Einschalten bei Covered
        $ruleId = $myRuleIds[5];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','60')");
        
        // Ausschalten bei Covered
        $ruleId = $myRuleIds[6];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','61')");
      }
      else
      {
        // Einschalten bei Clicked
        $ruleId = $myRuleIds[0];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','60')");
        
        // Ausschalten bei Clicked
        $ruleId = $myRuleIds[1];
        QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','61')");
      }
      
      // DUMMY bei evOn
      $ruleId = $myRuleIds[7];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOnFunctionId = getClassesIdFunctionsIdByName($schalterClassesId, "evOn");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evOnFunctionId')");
      
      // DUMMY bei evOff
      $ruleId = $myRuleIds[8];
      //QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
      

      $evOffFunctionId = getClassesIdFunctionsIdByName($schalterClassesId, "evOff");
      
      QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                  values('$ruleId','$featureInstanceId','$evOffFunctionId')");
    }
  } //while
  

  /*
  if ($mySingleClassesId>0)
  {
  // DUMMY PING bei evOn
  $ruleId=$myRuleIds[7];
  QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");
    
	if ($mySingleClassesId==$dimmerClassesId)
  {
    $evOnFunctionId = getClassesIdFunctionsIdByName($dimmerClassesId,"evOn");
         
 	  QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                             values('$ruleId','$featureInstanceId','$evOnFunctionId')");
    $signalId=mysql_insert_id();

    $dimmerParamBrightnessId = getClassesIdFunctionParamIdByName($dimmerClassesId,"evOn","brightness");
    QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                  values('$signalId','$dimmerParamBrightnessId','100')");
  }
  else if ($mySingleClassesId==$schalterClassesId)
  {
    $evOnFunctionId = getClassesIdFunctionsIdByName($schalterClassesId,"evOn");
         
 		 QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                              values('$ruleId','$featureInstanceId','$evOnFunctionId')");
  }
  else if ($mySingleClassesId==$rolloClassesId)
  {
    $evStopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId,"evStop");
       
 	 QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                            values('$ruleId','$featureInstanceId','$evStopFunctionId')");
   $signalId=mysql_insert_id();
  
   $rolloParamPositionId = getClassesIdFunctionParamIdByName($rolloClassesId,"evStop","position");
   QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                 values('$signalId','$rolloParamPositionId','0')");
  }
  
  // DUMMY PING bei evOff
  $ruleId=$myRuleIds[8];
  QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$featureInstanceId','-1')");

 if ($mySingleClassesId==$dimmerClassesId)
	 {
 	 	  $evOffFunctionId = getClassesIdFunctionsIdByName($dimmerClassesId,"evOff");
         
	   	 QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
 	                                values('$ruleId','$featureInstanceId','$evOffFunctionId')");
   }
   else if ($mySingleClassesId==$schalterClassesId)
	 {
	 	  $evOffFunctionId = getClassesIdFunctionsIdByName($schalterClassesId,"evOff");
         
	   	 QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                                values('$ruleId','$featureInstanceId','$evOffFunctionId')");
   }
  else if ($mySingleClassesId==$rolloClassesId)
	 {
	 	  $evStopFunctionId = getClassesIdFunctionsIdByName($rolloClassesId,"evStop");
         
   	 QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId)
                               values('$ruleId','$featureInstanceId','$evStopFunctionId')");
     $signalId=mysql_insert_id();

     $rolloParamPositionId = getClassesIdFunctionParamIdByName($rolloClassesId,"evStop","position");
 		 QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                   values('$signalId','$rolloParamPositionId','255')");
   }
  }
  */
  
  // Regeln löschen, die keine Aktions bekommen haben
  for($i = 0; $i < count($myRuleIds); $i++)
  {
    $ruleId = $myRuleIds[$i];
    $erg = QUERY("select count(*) from ruleActions where ruleId='$ruleId'");
    while ( $row = MYSQL_FETCH_ROW($erg) )
    {
      if ($row[0] == 0)
        deleteRule($ruleId);
    }
  }
  
  // Optimierung für clicked regeln
  unset($firstActions);
  $i = 0;
  $erg = QUERY("select featureInstanceId, featureFunctionId,paramValue from ruleActions left join ruleActionParams on(ruleActionParams.ruleActionId=ruleActions.id) where ruleId='$myRuleIds[0]'");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
    $firstActions[$i++] = $obj;
  }
  
  //print_r($firstActions);
  

  $isEqual = 1;
  $i = 0;
  $erg = QUERY("select featureInstanceId, featureFunctionId,paramValue from ruleActions left join ruleActionParams on(ruleActionParams.ruleActionId=ruleActions.id) where ruleId='$myRuleIds[1]'");
  while ( $obj = MYSQL_FETCH_OBJECT($erg) )
  {
    if ($firstActions[$i]->featureInstanceId != $obj->featureInstanceId)
      $isEqual = 0;
    if ($firstActions[$i]->featureFunctionId != $obj->featureFunctionId)
      $isEqual = 0;
    if ($firstActions[$i]->paramValue != $obj->paramValue)
      $isEqual = 0;
    
    if ($isEqual == 0)
    {
      //print_r($obj);
      break;
    }
    $i++;
  }
  
  //echo "A".$isEqual."<br>";  
  

  if ($isEqual == 1)
  {
    // States bei Regel 0 auf all stellen
    QUERY("UPDATE rules set activationStateId='0', resultingStateId='0' where id='$myRuleIds[0]' limit 1");
    
    // Regel 1 löschen
    deleteRule($myRuleIds[1]);
  }
}

if ($logicalGroupMode == 1)
{
  header("Location: editLogicalSignals.php?groupId=$groupId");
  exit();
}

if ($ajax==1) exit;

setupTreeAndContent("editRules_design.html", $message);

$html = str_replace("%GROUP_ID%", $groupId, $html);

$last = "";
$hasLed = 0;
$hasRollo = 0;
$rolloClassesId = getClassesIdByName("Rollladen");
$ledClassesId = getClassesIdByName("Led");

$erg = QUERY("select featureInstanceId,
	                         featureClassesId
 	                    from groupFeatures 
	                    join featureInstances on (featureInstances.id=featureInstanceId) 
	                    where groupId='$groupId'");
while ( $obj = MYSQL_FETCH_OBJECT($erg) )
{
  if ($obj->featureClassesId == $rolloClassesId)
    $hasRollo = 1;
  if ($obj->featureClassesId == $ledClassesId)
    $hasLed = 1;
}

$allFeatureInstances = readFeatureInstances("featureClassesId,name");
$allFeatureClasses = readFeatureClasses();
$allFeatureFunctions = readFeatureFunctions();
$allRooms = readRooms();
$allRoomFeatures = readRoomFeatures();
$allGroupStates = readGroupStates();

foreach ( (array)$allGroupStates as $obj )
{
  if ($obj->groupId == $groupId)
  {
    if ($possibleValues != "")
    {
      $possibleValues .= ",";
      $possibleNames .= ",";
    }
    $possibleValues .= $obj->id;
    $possibleNames .= $obj->name;
  }
}

$allRuleActions = readRuleActions();
$allRuleSignals = readRuleSignals();

$rulesTag = getTag("%RULES%", $html);

$evTimeFunctionId = getClassesIdFunctionsIdByName($CONTROLLER_CLASSES_ID, "evTime");
$evDayFunctionId = getClassesIdFunctionsIdByName($CONTROLLER_CLASSES_ID, "evDay");
$evNightFunctionId = getClassesIdFunctionsIdByName($CONTROLLER_CLASSES_ID, "evNight");

$rules = "";
$first = 1;
$erg99 = QUERY("select * from rules where groupId='$groupId' order by baseRule,id");
while ( $obj99 = MYSQL_FETCH_OBJECT($erg99) )
{
  $actTag = $rulesTag;
  
  if ($obj99->baseRule == 1)
  {
    getTag("%OPT_DELETE%", $actTag);
    $actTag = str_replace("%OPT_DELETE%", "<font size=1>BASIS-<br>REGEL", $actTag);
  }
  else
    chooseTag("%OPT_DELETE%", $actTag);
  
  if ($first == 1)
  {
    $first = 0;
    getTag("%OPT_COPY_SIGNAL%", $actTag);
    $actTag = str_replace("%OPT_COPY_SIGNAL%", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $actTag);
    getTag("%OPT_COPY_ACTION%", $actTag);
    $actTag = str_replace("%OPT_COPY_ACTION%", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $actTag);
  }
  else
  {
    chooseTag("%OPT_COPY_SIGNAL%", $actTag);
    chooseTag("%OPT_COPY_ACTION%", $actTag);
  }
  
  $actTag = str_replace("%RULE_ID%", $obj99->id, $actTag);
  $actTag = str_replace("%ACTIVATION_STATE_OPTIONS%", getSelect($obj99->activationStateId, "," . $possibleValues, "alle," . $possibleNames), $actTag);
  $actTag = str_replace("%RESULTING_STATE_OPTIONS%", getSelect($obj99->resultingStateId, "," . $possibleValues, "gleich," . $possibleNames), $actTag);
  
  if ($obj99->startDay >= 10)
  {
    $obj99->endDay = 7;
    $obj99->startHour = 31;
    $obj99->startMinute = 255;
    $obj99->endHour = 31;
    $obj99->endMinute = 255;
  }
  
  $actTag = str_replace("%TIME_START_DAY_OPTIONS%", getSelect($obj99->startDay, "7,0,1,2,3,4,5,6", "Immer,Mo.,Di.,Mi.,Do.,Fr.,Sa.,So."), $actTag);
  $actTag = str_replace("%TIME_END_DAY_OPTIONS%", getSelect($obj99->endDay, "7,0,1,2,3,4,5,6", "Immer,Mo.,Di.,Mi.,Do.,Fr.,Sa.,So."), $actTag);
  
  $options = getTimeOptionsHour($obj99->startHour);
  $actTag = str_replace("%TIME_START_TIME_OPTIONS%", $options, $actTag);

  $options = getTimeOptionsMinute($obj99->startMinute);
  $actTag = str_replace("%TIME_START_TIME_MINUTE_OPTIONS%", $options, $actTag);
  
  $options = getTimeOptionsHour($obj99->endHour);
  $actTag = str_replace("%TIME_END_TIME_OPTIONS%", $options, $actTag);

  $options = getTimeOptionsMinute($obj99->endMinute);
  $actTag = str_replace("%TIME_END_TIME_MINUTE_OPTIONS%", $options, $actTag);
  
  if ($obj99->groupLock==1) $locked="checked";
  else $locked="";
  $actTag = str_replace("%LOCK_CHECKED%", $locked, $actTag);

  if ($obj99->intraDay==1) $checked="checked";
  else $checked="";
  $actTag = str_replace("%INTRADAY_CHECKED%", $checked, $actTag);
  
  $signalsTag = getTag("%SIGNALS%", $actTag);
  
  $signals = "";
  foreach ( (array)$allRuleSignals as $obj )
  {
    if ($obj->ruleId == $obj99->id)
    {
      $actSignalTag = $signalsTag;
      $actSignalTag = str_replace("%SIGNAL_ID%", $obj->id, $actSignalTag);
      
      $actClassesId = getClassesIdByFeatureInstanceId($obj->featureInstanceId);
      
      if ($obj->featureInstanceId < 0) //gruppenSignale
      {
        $signalGroupId = $obj->featureInstanceId * - 1;
        
        $erg34 = QUERY("select eventType,name,groupType from  basicRuleGroupSignals join groups on (groups.id=basicRuleGroupSignals.groupId) where basicRuleGroupSignals.id='$signalGroupId' limit 1");
        $obj34 = MYSQL_FETCH_OBJECT($erg34);
        
        if ($obj34->groupType == "SIGNALS-AND")
          $groupType = "UND";
        else if ($obj34->groupType == "SIGNALS-OR")
          $groupType = "ODER";
        else
          $groupType = "UNBEKANNT";
        
        if ($obj34->eventType == "ACTIVE")
          $action = "Erreichen";
        else if ($obj34->eventType == "DEACTIVE")
          $action = "Verlassen";
        else
          $action = "Unbekannt";
        
        $actSignalTag = str_replace("%SIGNAL_ROOM%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_CLASS%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_NAME%", "Logische Grupppe " . $obj34->name, $actSignalTag);
        $actSignalTag = str_replace("%FEATURE_FUNCTION_ID%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_FUNCTION%", $action . " des logischen $groupType Zustandes", $actSignalTag);
      }
      else if ($actClassesId == $CONTROLLER_CLASSES_ID && $obj->featureFunctionId == $evTimeFunctionId) // evTime vom controller
      {
        $actSignalTag = str_replace("%SIGNAL_ROOM%", "", $actSignalTag);
        
        $erg = QUERY("select paramValue from ruleSignalParams where ruleSignalId='$obj->id' limit 1");
        if ($row = MYSQL_FETCH_ROW($erg))
          $weekTime = parseWeekTimeToObject($row[0]);
        
        $actSignalTag = str_replace("%SIGNAL_CLASS%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_NAME%", "Zeitgesteuert", $actSignalTag);
        $actSignalTag = str_replace("%FEATURE_FUNCTION_ID%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_FUNCTION%", $weekTime->tag . " " . $weekTime->stunde . ":" . $weekTime->minute, $actSignalTag);
      }
      else if ($actClassesId == $CONTROLLER_CLASSES_ID && $obj->featureFunctionId == $evDayFunctionId)
      {
        $actSignalTag = str_replace("%SIGNAL_ROOM%", "", $actSignalTag);
        
        $actSignalTag = str_replace("%SIGNAL_CLASS%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_NAME%", "Zeitgesteuert", $actSignalTag);
        $actSignalTag = str_replace("%FEATURE_FUNCTION_ID%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_FUNCTION%", "Bei Sonnenaufgang", $actSignalTag);
      }
      else if ($actClassesId == $CONTROLLER_CLASSES_ID && $obj->featureFunctionId == $evNightFunctionId)
      {
        $actSignalTag = str_replace("%SIGNAL_ROOM%", "", $actSignalTag);
        
        $actSignalTag = str_replace("%SIGNAL_CLASS%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_NAME%", "Zeitgesteuert", $actSignalTag);
        $actSignalTag = str_replace("%FEATURE_FUNCTION_ID%", "", $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_FUNCTION%", "Bei Sonnenuntergang", $actSignalTag);
      }
      else
      {
        $myFeatureInstance = $allFeatureInstances[$obj->featureInstanceId];
        $myRoom = getRoomForFeatureInstance($obj->featureInstanceId);
        $add = "";
        if ($obj->groupAlias > 0)
          $add = "R ".$obj->groupAlias." ";
        $actSignalTag = str_replace("%SIGNAL_ROOM%", $add . $myRoom->name, $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_CLASS%", i18n($allFeatureClasses[$myFeatureInstance->featureClassesId]->name), $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_NAME%", $myFeatureInstance->name, $actSignalTag);
        $actSignalTag = str_replace("%FEATURE_FUNCTION_ID%", $obj->featureFunctionId, $actSignalTag);
        $actSignalTag = str_replace("%SIGNAL_FUNCTION%", i18n($allFeatureFunctions[$obj->featureFunctionId]->name), $actSignalTag);
      }
      
      $signals .= $actSignalTag;
    }
  }
  $actTag = str_replace("%SIGNALS%", $signals, $actTag);
  
  $actionsTag = getTag("%ACTIONS%", $actTag);
  
  $actions = "";
  foreach ( $allRuleActions as $obj )
  {
    if ($obj->ruleId == $obj99->id)
    {
      $actActionTag = $actionsTag;
      $actActionTag = str_replace("%ACTION_ID%", $obj->id, $actActionTag);
      
      $myFeatureInstance = $allFeatureInstances[$obj->featureInstanceId];
      $myRoom = getRoomForFeatureInstance($obj->featureInstanceId);
      
      $actActionTag = str_replace("%ACTION_ROOM%", $myRoom->name, $actActionTag);
      $actActionTag = str_replace("%ACTION_CLASS%", $allFeatureClasses[$myFeatureInstance->featureClassesId]->name, $actActionTag);
      $actActionTag = str_replace("%ACTION_NAME%", $myFeatureInstance->name, $actActionTag);
      $actActionTag = str_replace("%FEATURE_FUNCTION_ID%", $obj->featureFunctionId, $actActionTag);
      $actActionTag = str_replace("%ACTION_FUNCTION%", i18n($allFeatureFunctions[$obj->featureFunctionId]->name), $actActionTag);
      
      $actions .= $actActionTag;
    }
  }
  $actTag = str_replace("%ACTIONS%", $actions, $actTag);
  
  $rules .= $actTag;
}

$html = str_replace("%RULES%", $rules, $html);

show();

function parseGroup($rulesBytes, $rulesBytesPos, &$regeln)
{
  $myPos = 0;
  
  $numOfRules = $rulesBytes[$myPos++];
  $regeln .= "Anzahl Regeln: $numOfRules<hr>";
  
  for($i = 0; $i < $numOfRules; $i++)
  {
    $regeln .= "Regel $i <br>";
    $ok = parseRule($rulesBytes, $myPos, $rulesBytesPos, $regeln);
    if (! $ok) return FALSE;
  }
  
  return TRUE;
}

function parseRule($rulesBytes, &$myPos, $rulesBytesPos, &$result)
{
  $errorCounter = 0;
  while ( 1 && $errorCounter < 100 )
  {
    $errorCounter++;
    $ok = parseRuleElement($rulesBytes, $myPos, $rulesBytesPos, $result);
    if (! $ok) return FALSE;
    if ($myPos >= $rulesBytesPos) return FALSE;
    
    if ($rulesBytes[$myPos] == 0)
    {
      $myPos++;
      return TRUE;
    }
  }
}

function parseRuleElement($rulesBytes, &$myPos, $rulesBytesPos, &$result)
{
  if ($myPos >= $rulesBytesPos) return FALSE;
  $numOfConditions = $rulesBytes[$myPos++];
  $result .= "numOfConditions = $numOfConditions <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $numOfActions = $rulesBytes[$myPos++];
  $result .= "numOfActions = $numOfActions <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $triggerState = $rulesBytes[$myPos++];
  $result .= "triggerState = $triggerState <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $nextState = $rulesBytes[$myPos++];
  $result .= "nextState = $nextState <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  
  $startMinute = $rulesBytes[$myPos++];
  $result .= "startMinute = $startMinute <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $startHour = $rulesBytes[$myPos++];
  $startDay = ($startHour & 0xE0) >> 5;
  $startHour = $startHour & 0x1F;
  $result .= "startHour = $startHour <br>";
  $result .= "startDay = $startDay <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  
  $endMinute = $rulesBytes[$myPos++];
  $result .= "endMinute = $endMinute <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $endHour = $rulesBytes[$myPos++];
  $endDay = ($endHour & 0xE0) >> 5;
  $endHour = $endHour & 0x1F;
  $result .= "endHour = $endHour <br>";
  $result .= "endDay = $endDay <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  
  for($i = 0; $i < $numOfConditions; $i++)
  {
    $ok = parseCondition($rulesBytes, $myPos, $rulesBytesPos, $result);
    if (! $ok) return FALSE;
  }
  
  for($i = 0; $i < $numOfActions; $i++)
  {
    $ok = parseAction($rulesBytes, $myPos, $rulesBytesPos, $result);
    if (! $ok) return FALSE;
  }
  
  $result .= "<hr>";
  return true;
}

function parseCondition($rulesBytes, &$myPos, $rulesBytesPos, &$result)
{
  if ($myPos > $rulesBytesPos - 4) return FALSE;
  $sender = bytesToDword($rulesBytes, $myPos);
  $result .= "Sender = " . dechex($sender) . " <br>";
  //$myPos+=4;
  if ($myPos >= $rulesBytesPos) return FALSE;
  $eventId = $rulesBytes[$myPos++];
  
  $result .= "eventId = $eventId <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  return TRUE;
}

function parseAction($rulesBytes, &$myPos, $rulesBytesPos, &$result)
{
  if ($myPos > $rulesBytesPos - 4) return FALSE;
  $receiver = bytesToDword($rulesBytes, $myPos);
  $result .= "Empfänger = " . dechex($receiver) . " <br>";
  //$myPos+=4;
  if ($myPos >= $rulesBytesPos) return FALSE;
  $length = $rulesBytes[$myPos++];
  
  $result .= "Parameterlänge = $length <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $functionId = $rulesBytes[$myPos++];
  
  $result .= "functionId = $functionId <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  if ($myPos >= $rulesBytesPos) return FALSE;
  $param = $rulesBytes[$myPos++];
  
  $result .= "param = $param <br>";
  return TRUE;
}

function parseWeekTimeToObject($value)
{
  $result = "";
  $weekTime = getWeekTime("dummy", $value);
  //selected value='1'>Di.
  

  $pos = strpos($weekTime, "selected");
  if ($pos === FALSE) return $result;
  $pos2 = strpos($weekTime, ">", $pos);
  if ($pos2 === FALSE) return $result;
  $pos3 = strpos($weekTime, "<", $pos2 + 1);
  if ($pos3 === FALSE) return $result;
  $result->tag = substr($weekTime, $pos2 + 1, $pos3 - $pos2 - 1);
  
  $pos = strpos($weekTime, "selected", $pos3);
  if ($pos === FALSE) return $result;
  $pos2 = strpos($weekTime, ">", $pos);
  if ($pos2 === FALSE) return $result;
  $pos3 = strpos($weekTime, "<", $pos2 + 1);
  if ($pos3 === FALSE) return $result;
  $result->stunde = substr($weekTime, $pos2 + 1, $pos3 - $pos2 - 1);
  
  $pos = strpos($weekTime, "selected", $pos3);
  if ($pos === FALSE) return $result;
  $pos2 = strpos($weekTime, ">", $pos);
  if ($pos2 === FALSE) return $result;
  $pos3 = strpos($weekTime, "<", $pos2 + 1);
  if ($pos3 === FALSE) return $result;
  $result->minute = substr($weekTime, $pos2 + 1, $pos3 - $pos2 - 1);
  
  return $result;
}

function checkAndTraceDataDifferences($groups, $controllerId, $rulesData, $dataPos)
{
  $wurst = "";
  for($i = 0; $i < $dataPos; $i++)
  {
    $wurst .= $rulesData[$i] . ",";
  }
  
  $result = TRUE;
  $erg = QUERY("select data from ruleCache where controllerId='$controllerId' limit 1");
  if ($row = MYSQL_FETCH_ROW($erg))
  {
    if ($row[0] == $wurst)
      $result = FALSE;
    /*else
    {
    	 for ($i=0;$i<strlen($wurst);$i++)
    	 {
    	 	   if (substr($wurst,$i,1)!=substr($row[0],$i,1)) die("Unterschied an Position $i ".substr($wurst,$i,1)." != ".substr($row[0],$i,1));
    	 }
    }*/
    /*
	 	  $string1 = $row[0];
      $string2 = $wurst;
      $pos = strspn($string1 ^ $string2, "\0");
      printf('First difference at position %d: "%s" vs "%s"',$pos, $string1[$pos], $string2[$pos]);
  	  echo "Vorher:<br>$row[0]<br>Nachher:<br>$wurst <br>";
  	  //exit;
  	  */
  }
  
  QUERY("DELETE from ruleCache where controllerId='$controllerId' limit 1");
  QUERY("INSERT into ruleCache (groups, controllerId, data) values('$groups','$controllerId','$wurst')");
  
  return $result;
}

function checkAndTraceConfigData($featureInstanceId, $configArray)
{
  $wurst = "";
  foreach ( $configArray as $key => $value )
  {
    $wurst .= $key . "=" . $value . ",";
  }
  
  $erg = QUERY("select configData from configCache where featureInstanceId='$featureInstanceId' limit 1");
  if ($row = MYSQL_FETCH_ROW($erg))
  {
    if ($row[0] == $wurst)
      return FALSE;
  }
  
  QUERY("DELETE from configCache where featureInstanceId='$featureInstanceId' limit 1");
  QUERY("INSERT into configCache (featureInstanceId, configData) values('$featureInstanceId','$wurst')");
  return TRUE;
}


function getTimeOptionsHour($myValue)
{
	$myOptions = "<option value='31'>Immer";
	$myOptions .= "<option value='25'>Tagsüber";
 	$myOptions .= "<option value='26'>Nachts";

  for($hour = 0; $hour < 24; $hour++)
  {
    $myHour = $hour;
    if (strlen($myHour) == 1) $myHour = "0" . $myHour;
    $myOptions .= "<option value='$hour'>$myHour Uhr";
  }
      
  if ($myValue == "31") $myOptions = str_replace("value='31'","value='31' selected",$myOptions);
  else if ($myValue == "25") $myOptions = str_replace("value='25'","value='25' selected",$myOptions);
  else if ($myValue == "26") $myOptions = str_replace("value='26'","value='26' selected",$myOptions);

  $myOptions = str_replace("value='$myValue'","value='$myValue' selected",$myOptions);
  	
	return $myOptions;
}

function getTimeOptionsMinute($myValue)
{
	$myOptions = "<option value='255'>Immer";

  for($minute = 0; $minute < 60; $minute++)
  {
    $myMinute = $minute;
    if (strlen($myMinute) == 1) $myMinute = "0" . $myMinute;
    $myOptions .= "<option value='$minute'>$myMinute Min";
  }
      
  if ($myValue == "255") $myOptions = str_replace("value='255'","value='255' selected",$myOptions);

  $myOptions = str_replace("value='$myValue'","value='$myValue' selected",$myOptions);
  	
	return $myOptions;
}

?>