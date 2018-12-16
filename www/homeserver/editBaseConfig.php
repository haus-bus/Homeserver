<?php
include_once ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($generate == 1)
{
  generateMultiGroups();
  exit();
}

// Regel muss erst noch erstellt werden
if ($ruleId == - 1)
{
  QUERY("INSERT into basicRules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute) values('$groupId','7','31','255','7','31','255')");
  $ruleId = query_insert_id();
}

if ($action == "deleteRule") deleteBaseRule($ruleId);
else if ($action == "changeStartDay") QUERY("UPDATE basicRules set startDay='$startDay' where id='$ruleId' limit 1");
else if ($action == "changeExtras") QUERY("UPDATE basicRules set extras='$extra' where id='$ruleId' limit 1");
else if ($action == "changeTemplate")
{
  if ($template == "Default")
    $template = "";
  QUERY("UPDATE basicRules set template='$template' where id='$ruleId' limit 1");
}
else if ($action == "changeEndDay") QUERY("UPDATE basicRules set endDay='$endDay' where id='$ruleId' limit 1");
else if ($action == "changeStartTime") QUERY("UPDATE basicRules set startHour='$startTime' where id='$ruleId' limit 1");
else if ($action == "changeStartTimeMinute") QUERY("UPDATE basicRules set startMinute='$startTimeMinute' where id='$ruleId' limit 1");
else if ($action == "changeEndTime") QUERY("UPDATE basicRules set endHour='$endTime' where id='$ruleId' limit 1");
else if ($action == "changeEndTimeMinute") QUERY("UPDATE basicRules set endMinute='$endTimeMinute' where id='$ruleId' limit 1");
else if ($action == "addSignal")
{
  if ($submitted == 1)
  {
    if ($signalGroupId > 0)
    {
      QUERY("INSERT into basicRuleGroupSignals (ruleId, groupId, eventType) values('$ruleId','$signalGroupId','$signal')");
      $groupSignalId = query_insert_id();
      QUERY("INSERT into basicRuleSignals (ruleId,featureInstanceId) values('$ruleId','-$groupSignalId')");
      header("Location: editBaseConfig.php?groupId=$groupId&action=dummy");
    }
    else
    {
      $erg = QUERY("select id from basicRuleSignals where ruleId='$ruleId' and featureInstanceId='$featureInstanceId' limit 1");
      if ($row = mysqli_fetch_row($erg))
        $ruleSignalId = $row[0];
      else
      {
        QUERY("INSERT into basicRuleSignals (ruleId,featureInstanceId) values('$ruleId','$featureInstanceId')");
        $ruleSignalId = query_insert_id();
      }
      
      $irClassesId = getClassesIdByName("IR-Sensor");
      $actClassesId = getClassesIdByFeatureInstanceId($featureInstanceId);
      
      if ($actClassesId == $irClassesId)
      {
        if ($param1Value == "")
          header("Location: editBaseConfig.php?groupId=$groupId&action=editSignalParams&ruleSignalId=$ruleSignalId&featureFunctionId=111");
        else
          header("Location: editBaseConfig.php?groupId=$groupId&action=editSignalParams&ruleSignalId=$ruleSignalId&featureFunctionId=111&submitted=1&param173=$param1Value&param174=$param2Value");
      }
      else
        header("Location: editBaseConfig.php?groupId=$groupId&action=dummy");
    }
    exit();
  }
  else
  {
    if ($liveMode == 1)
    {
      setupTreeAndContent("signalLiveMode_design.html");
      $html = str_replace("%LINK%", urlencode("editBaseConfig.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId"), $html);
      show();
    }
    else
    {
      $tasterClassesId = getClassesIdByName("Taster");
      $irClassesId = getClassesIdByName("IR-Sensor");
      $tempClassesId = getClassesIdByName("Temperatursensor");

      
      setupTreeAndContent("addRuleSignal_design.html");
      removeTag("%OPT_ADD_OTHERS%",$html);
      
      $html = str_replace("signalLiveMode.php", "signalLiveModeBaseRules.php", $html);
      
      $closeTreeFolder = "</ul></li> \n";
      
      $treeElements = "";
      $treeElements .= addToTree("<a href='editBaseConfig.php?groupId=$groupId'>Neues Trigger-Signal für diese Regel auswählen</a>", 1);
      $html = str_replace("%INITIAL_ELEMENT2%", "expandToItem('tree2','$treeElementCount');", $html);
      
      $allMyRuleSignals = readMyBasicRuleSignals($ruleId);
      $logicalButtonClass = getClassesIdByName("LogicalButton");
      
      $erg = QUERY("select * from basicRuleGroupSignals where ruleId='$ruleId'");
      while ( $obj = mysqli_fetch_OBJECT($erg) )
        $myGroupSignals[$obj->groupId] = 1;
      
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
                                 where (featureInstances.featureClassesId='$tasterClassesId' or featureInstances.featureClassesId='$irClassesId' or featureInstances.featureClassesId='$tempClassesId' )
                                 order by roomName,featureClassName,featureInstanceName");
      while ( $obj = mysqli_fetch_object($erg) )
      {
        if ($ready[$obj->featureInstanceId] == 1) continue;
        
        $ready[$obj->featureInstanceId] = 1;
        
        if ($allMyRuleSignals[$obj->featureInstanceId] != 1)
        {
          if ($obj->roomId != $lastRoom)
          {
            if ($lastRoom != "") $treeElements .= $closeTreeFolder; // letzter raum
            $lastRoom = $obj->roomId;
            $treeElements .= addToTree($obj->roomName, 1);
          }
          
          $treeElements .= addToTree("<a href='editBaseConfig.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&featureInstanceId=" . $obj->featureInstanceId . "'>" . $obj->featureInstanceName . "</a>", 0);
        }
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
                                 where (featureInstances.featureClassesId='$tasterClassesId'  or featureInstances.featureClassesId='$irClassesId'  or featureInstances.featureClassesId='$tempClassesId')
                                 order by controllerName, featureClassName,featureInstanceName");
      while ( $obj = mysqli_fetch_object($erg) )
      {
        if ($ready[$obj->featureInstanceId] == 1) continue;
        
        if ($allMyRuleSignals[$obj->featureInstanceId] != 1)
        {
          if ($lastRoom == "")
          {
            $lastRoom = "dummy";
            $treeElements .= addToTree("Keinem Raum zugeordnet", 1);
          }
          
          if ($obj->controllerId != $lastController)
          {
            if ($lastController != "") $treeElements .= $closeTreeFolder; // letzter controller

            $lastController = $obj->controllerId;
            $treeElements .= addToTree($obj->controllerName, 1);
          }
          
          $treeElements .= addToTree("<a href='editBaseConfig.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&featureInstanceId=" . $obj->featureInstanceId . "'>" . $obj->featureInstanceName . "</a>", 0);
        }
      }
      
      $treeElements .= $closeTreeFolder; // letzter controller
      $treeElements .= $closeTreeFolder; // letzter raum

      $foundGroups = 0;
      $erg = QUERY("select id,name,groupType from groups where single!='1' and subOf='0' and groupType!='' order by name");
      while ( $obj = mysqli_fetch_OBJECT($erg) )
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
          $treeElements .= addToTree("<a href='editBaseConfig.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&groupSignal=1&signalGroupId=$obj->id&signal=ACTIVE'>Beim Erreichen vom logischen $groupType Zustand</a>", 0);
          $treeElements .= addToTree("<a href='editBaseConfig.php?action=$action&submitted=1&groupId=$groupId&ruleId=$ruleId&groupSignal=1&signalGroupId=$obj->id&signal=DEACTIVE'>Beim Verlassen vom logischen $groupType Zustand</a>", 0);
          $treeElements .= $closeTreeFolder;
        }
      }
      if ($foundGroups == 1) $treeElements .= $closeTreeFolder;
      
      $html = str_replace("%TREE_ELEMENTS%", $treeElements, $html);
      $html = str_replace("%GROUP_ID%", $groupId, $html);
      $html = str_replace("%RULE_ID%", $ruleId, $html);
      $html = str_replace("%ACTION%", $action, $html);
      show();
    }
  }
}
else if ($action == "removeSignal") deleteBaseRuleSignal($signalId);
else if ($action == "addSignalCopy")
{
  $erg = QUERY("select id from basicRules where groupId='$groupId' and id<'$ruleId' order by id desc limit 1");
  if ($obj = mysqli_fetch_OBJECT($erg))
  {
    $parentId = $obj->id;
    
    $erg2 = QUERY("select id,featureInstanceId from basicRuleSignals where ruleId='$parentId' order by id");
    while ( $obj2 = mysqli_fetch_OBJECT($erg2) )
    {
      QUERY("INSERT into basicRuleSignals (ruleId,featureInstanceId) values('$ruleId','$obj2->featureInstanceId')");
      $newRuleSignalId = query_insert_id();
      
      $erg3 = QUERY("select featureFunctionParamsId,paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id'");
      while ( $obj3 = mysqli_fetch_OBJECT($erg3) )
      {
        QUERY("INSERT into basicRuleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
     	                               values('$newRuleSignalId','$obj3->featureFunctionParamsId','$obj3->paramValue')");
      }
    }
  }
}
else if ($action == "changeFkt1")
{
  QUERY("update basicRules set fkt1='$value' where groupId='$groupId' and id='$ruleId' limit 1");
  debugScript("changeFkt1");
}
else if ($action == "changeFkt1Dauer") QUERY("update basicRules set fkt1Dauer='$value' where groupId='$groupId' and id='$ruleId' limit 1");
else if ($action == "changeFkt2") QUERY("update basicRules set fkt2='$value' where groupId='$groupId' and id='$ruleId' limit 1");
else if ($action == "changeFkt3") QUERY("update basicRules set fkt3='$value' where groupId='$groupId' and id='$ruleId' limit 1");
else if ($action == "changeFkt4") QUERY("update basicRules set fkt4='$value' where groupId='$groupId' and id='$ruleId' limit 1");
else if ($action == "changeFkt4Dauer") QUERY("update basicRules set fkt4Dauer='$value' where groupId='$groupId' and id='$ruleId' limit 1");
else if ($action == "changeLedStatus") QUERY("update basicRules set ledStatus='$value' where groupId='$groupId' and id='$ruleId' limit 1");
else if ($action == "chooseTimeSignal")
{
  setupTreeAndContent("chooseTimeSignal_design.html", $message);
  $html = str_replace("%GROUP_ID%", $groupId, $html);
  show();
  exit();
}
else if ($action == "changeLock")
{
  if ($lockState == "true") $value = 1;
  else $value = 0;
  QUERY("UPDATE basicRules set groupLock='$value' where id='$ruleId' limit 1");
}
else if ($action == "changeIntraday")
{
  if ($intradayState == "true") $value = 1;
  else $value = 0;
  QUERY("UPDATE basicRules set intraDay='$value' where id='$ruleId' limit 1");
}
else if ($action == "addTimeSignal")
{
  $erg = QUERY("select featureInstanceId from groupFeatures where groupId='$groupId' order by id limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $firstAktor = $row[0];
  else die("Kein Aktor gefunden in Gruppe $groupId");
  
  $erg = QUERY("select controllerId from featureInstances where id='$firstAktor' limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $controllerId = $row[0];
  else die("Controller zu featureId $firstAktor nicht gefunden");
  
  $erg = QUERY("select id from featureInstances where controllerId='$controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $controllerInstanceId = $row[0];
  else die("controllerInstanceId zu controllerId $controllerId nicht gefunden");
  
  QUERY("INSERT into basicRuleSignals (ruleId,featureInstanceId) values('$ruleId','$controllerInstanceId')");
  $signalId = query_insert_id();
  
  header("Location: editBaseConfig.php?action=editSignalParams&groupId=$groupId&ruleId=$ruleId&ruleSignalId=$signalId&featureFunctionId=129");
  exit();
}
else if ($action == "addTimeSignalSunrise")
{
  $erg = QUERY("select featureInstanceId from groupFeatures where groupId='$groupId' order by id limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $firstAktor = $row[0];
  else die("Kein Aktor gefunden in Gruppe $groupId");
  
  $erg = QUERY("select controllerId from featureInstances where id='$firstAktor' limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $controllerId = $row[0];
  else die("Controller zu featureId $firstAktor nicht gefunden");
  
  $erg = QUERY("select id from featureInstances where controllerId='$controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $controllerInstanceId = $row[0];
  else die("controllerInstanceId zu controllerId $controllerId nicht gefunden");
  
  QUERY("INSERT into basicRuleSignals (ruleId,featureInstanceId) values('$ruleId','$controllerInstanceId')");
  $signalId = query_insert_id();
  
  QUERY("INSERT into basicrulesignalparams (ruleSignalId,paramValue) values('$signalId','-1')");
}
else if ($action == "addTimeSignalSunset")
{
  $erg = QUERY("select featureInstanceId from groupFeatures where groupId='$groupId' order by id limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $firstAktor = $row[0];
  else die("Kein Aktor gefunden in Gruppe $groupId");
  
  $erg = QUERY("select controllerId from featureInstances where id='$firstAktor' limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $controllerId = $row[0];
  else die("Controller zu featureId $firstAktor nicht gefunden");
  
  $erg = QUERY("select id from featureInstances where controllerId='$controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1");
  if ($row = mysqli_fetch_ROW($erg)) $controllerInstanceId = $row[0];
  else die("controllerInstanceId zu controllerId $controllerId nicht gefunden");
  
  QUERY("INSERT into basicRuleSignals (ruleId,featureInstanceId) values('$ruleId','$controllerInstanceId')");
  $signalId = query_insert_id();
  
  QUERY("INSERT into basicrulesignalparams (ruleSignalId,paramValue) values('$signalId','-2')");
}
else if ($action == "editSignalParams")
{
  if ($submitted == 1)
  {
    QUERY("delete from basicRuleSignalParams where ruleSignalId='$ruleSignalId'");
    trace("basicRuleSignalParam mit ruleSignalId $ruleSignalId glöscht");
    
    $erg = QUERY("select id,type from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
    while ( $row = mysqli_fetch_ROW($erg) )
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
      QUERY("INSERT into basicRuleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) values('$ruleSignalId','$row[0]','$value')");
    }
    header("Location: editBaseConfig.php?groupId=$groupId&action=dummy");
    exit();
  }
  else
  {
    setupTreeAndContent("editBaseRuleSignalParams_design.html");
    
    $erg = QUERY("select featureFunctionParamsId,paramValue from basicRuleSignalParams where ruleSignalId='$ruleSignalId'");
    while ( $obj = mysqli_fetch_object($erg) )
    {
      $myValues[$obj->featureFunctionParamsId] = $obj->paramValue;
    }
    
    $html = str_replace("%GROUP_ID%", $groupId, $html);
    $html = str_replace("%RULE_SIGNAL_ID%", $ruleSignalId, $html);
    
    $ansicht = $_SESSION["ansicht"];
    $paramTag = getTag("%PARAM%", $html);
    $params = "";
    $erg2 = QUERY("select id,name,type,comment,view  from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id");
    while ( $obj2 = mysqli_fetch_OBJECT($erg2) )
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
        while ( $obj3 = mysqli_fetch_OBJECT($erg3) )
        {
          if ($myValue == $obj3->value) $selected = "selected";
          else $selected = "";
          
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
}
else if ($action == "copyToClipboard" || $action == "copyRule")
{
  $_SESSION["copyBaseRules"] = $groupId;
  if ($action == "copyRule")
    $_SESSION["copyBaseRulesSingle"] = $ruleId;
  else
    $_SESSION["copyBaseRulesSingle"] = "";
  
  $message = "Regeln wurden zum Kopieren markiert";
}
else if ($action=="changeActive") QUERY("UPDATE basicRules set active='$active' where groupId='$groupId' and id='$ruleId' limit 1");

if ($ajax==1) exit;




//if ($action!="") generateBaseRulesForGroup($groupId);


$dimmerClassesId = getClassesIdByName("Dimmer");
$rolloClassesId = getClassesIdByName("Rollladen");
$ledClassesId = getClassesIdByName("Led");
$schalterClassesId = getClassesIdByName("Schalter");
$irClassesId = getClassesIdByName("IR-Sensor");
$logicalButtonClassesId = getClassesIdByName("LogicalButton");
$tasterClassesId = getClassesIdByName("Taster");

$mixGroup = 0;
unset($diffFeatureClasses);
$erg = QUERY("select featureInstanceId,featureClassesId,name from groupFeatures join featureInstances on (featureInstances.id=featureInstanceId) where groupId='$groupId'");
while ( $obj = mysqli_fetch_OBJECT($erg) )
{
  $myClassesId = $obj->featureClassesId;
  $myTitle = $obj->name;
  $diffFeatureClasses[$myClassesId] = 1;
  
  if (count($diffFeatureClasses) > 1) // Multigruppe
  {
    $myClassesId = $schalterClassesId;
    $mixGroup = 1;
    break;
  }
}


if ($action == "insertFromClipboard")
{
  if (! isset($_SESSION["copyBaseRules"]))
    $message = "Keine Regeln zum Einfügen vorhanden";
  else
  {
    $copyGroupId = $_SESSION["copyBaseRules"];
    
    if ($_SESSION["copyBaseRulesSingle"] != "")
      $and = "and id='" . $_SESSION["copyBaseRulesSingle"] . "'";
    
    $erg = QUERY("select * from basicRules where groupId='$copyGroupId' $and order by id");
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      if ($myClassesId == $schalterClassesId)
      {
        if ($obj->fkt1 != "true" && $obj->fkt1 != "false")
        {
          if ($obj->fkt1 > 0) $obj->fkt1 = "true";
          else $obj->fkt1 = "false";
        }
        
        if ($obj->fkt2 != "true" && $obj->fkt2 != "false")
        {
          if ($obj->fkt2 > 0) $obj->fkt2 = "true";
          else $obj->fkt2 = "false";
        }
      }
      else if ($myClassesId == $dimmerClassesId)
      {
        if ($obj->fkt1 == "true") $obj->fkt1 = "100";
        else if ($obj->fkt1 == "false") $obj->fkt1 = "0";
        if ($obj->fkt4 > 100) $obj->fkt4 = "-";
      }
      
      QUERY("INSERT into basicRules(groupId,fkt1,fkt1Dauer,fkt2,fkt3,fkt4,fkt4Dauer,ledStatus,startDay,startHour,startMinute,endDay,endHour,endMinute,extras,template,intraDay)
	 	  	                         values('$groupId','$obj->fkt1','$obj->fkt1Dauer','$obj->fkt2','$obj->fkt3','$obj->fkt4','$obj->fkt4Dauer','$obj->ledStatus','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$obj->extras','$obj->template','$obj->intraDay')");
      $newRuleId = query_insert_id();
      
      $erg2 = QUERY("select * from basicRuleSignals where ruleId='$obj->id' order by id");
      while ( $obj2 = mysqli_fetch_OBJECT($erg2) )
      {
        QUERY("INSERT into basicRuleSignals (ruleId, featureInstanceId) values('$newRuleId','$obj2->featureInstanceId')");
        $newSignalId = query_insert_id();
        
        $erg3 = QUERY("select * from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id");
        while ( $obj3 = mysqli_fetch_OBJECT($erg3) )
        {
          QUERY("INSERT into basicRuleSignalParams (ruleSignalId, featureFunctionParamsId,paramValue) values('$newSignalId','$obj3->featureFunctionParamsId','$obj3->paramValue')");
        }
      }
    }
    $message = "Regeln wurden eingefügt";
  }
}

if ($myClassesId == "") showMessage("Bitte zunächst Gruppenelemente hinzufügen");

if ($myClassesId == "1" || $myClassesId == "24") showMessage("Für diesen Aktor, können keine Basisregeln konfiguriert werden. <a href='editRules.php?groupId=$groupId'>Hier klicken</a>, um die Zusatzregeln dieser Gruppe anzuzeigen.");

setupTreeAndContent("editBaseConfig_design.html", $message);
$html = str_replace("%GROUP_ID%", $groupId, $html);

if ($mixGroup!=1) $html = str_replace("%TITLE%",$myTitle, $html);
else $html = str_replace("%TITLE%","", $html);

if ($myClassesId == $dimmerClassesId)
{
  chooseTag("%OPT_FKT3%", $html);
  chooseTag("%OPT_FKT3_SPALTE%", $html);
  chooseTag("%OPT_FKT4%", $html);
  chooseTag("%OPT_FKT4_SPALTE%", $html);
  chooseTag("%OPT_LED_STATUS%", $html);
  
  $html = str_replace("%FKT1%", "AN<br>+ Dauer", $html);
  $html = str_replace("%FKT2%", "AUS<br><br>", $html);
  $html = str_replace("%FKT3%", "DIMM<br><br>", $html);
  $html = str_replace("%FKT4%", "PRESET<br>+ Dauer", $html);
}
else if ($myClassesId == $rolloClassesId)
{
  chooseTag("%OPT_FKT3%", $html);
  chooseTag("%OPT_FKT3_SPALTE%", $html);
  chooseTag("%OPT_FKT4%", $html);
  chooseTag("%OPT_FKT4_SPALTE%", $html);
  chooseTag("%OPT_LED_STATUS%", $html);
  
  $html = str_replace("%FKT1%", "HOCH", $html);
  $html = str_replace("%FKT2%", "RUNTER", $html);
  $html = str_replace("%FKT3%", "STOP'N'GO", $html);
  $html = str_replace("%FKT4%", "PRESET", $html);
}
else if ($myClassesId == $ledClassesId)
{
  removeTag("%OPT_FKT3%", $html);
  removeTag("%OPT_FKT3_SPALTE%", $html);
  chooseTag("%OPT_FKT4%", $html);
  chooseTag("%OPT_FKT4_SPALTE%", $html);
  chooseTag("%OPT_LED_STATUS%", $html);
  
  $html = str_replace("%FKT1%", "AN<br>+ Dauer", $html);
  $html = str_replace("%FKT2%", "AUS", $html);
  $html = str_replace("%FKT3%", "DIMM", $html);
  $html = str_replace("%FKT4%", "PRESET<br>+ Dauer", $html);
}
else if ($myClassesId == $logicalButtonClassesId)
{
  removeTag("%OPT_FKT3%", $html);
  removeTag("%OPT_FKT3_SPALTE%", $html);
  chooseTag("%OPT_FKT4%", $html);
  chooseTag("%OPT_FKT4_SPALTE%", $html);
  chooseTag("%OPT_LED_STATUS%", $html);
  
  $html = str_replace("%FKT1%", "AN<br>+ Dauer", $html);
  $html = str_replace("%FKT2%", "AUS", $html);
  $html = str_replace("%FKT4%", "PRESET<br>+ Dauer", $html);
}
else if ($myClassesId == $schalterClassesId)
{
  removeTag("%OPT_FKT3%", $html);
  removeTag("%OPT_FKT3_SPALTE%", $html);
  chooseTag("%OPT_FKT4%", $html);
  chooseTag("%OPT_FKT4_SPALTE%", $html);
  chooseTag("%OPT_LED_STATUS%", $html);
  
  $html = str_replace("%FKT1%", "AN", $html);
  $html = str_replace("%FKT2%", "AUS", $html);
  $html = str_replace("%FKT4%", "PRESET", $html);
}
else if ($myClassesId == $tasterClassesId)
{
  removeTag("%OPT_FKT3%", $html);
  removeTag("%OPT_FKT3_SPALTE%", $html);
  removeTag("%OPT_FKT4%", $html);
  removeTag("%OPT_FKT4_SPALTE%", $html);
  removeTag("%OPT_LED_STATUS%", $html);
  
  $html = str_replace("%FKT1%", "AN", $html);
  $html = str_replace("%FKT2%", "AUS", $html);
}

$templateNames = "Default";
$erg = QUERY("select distinct(name) from functionTemplates where (classesId='$myClassesId' or classesId=-1) and name!='' order by id");
while ( $row = mysqli_fetch_ROW($erg) )
{
  $templateNames .= "," . $row[0];
}

debugScript("before");

$allFeatureInstances = readFeatureInstances("featureClassesId,name");
$allFeatureClasses = readFeatureClasses();
//$allRooms = readRooms();
//$allRoomFeatures = readRoomFeatures();
$allRuleSignals = readBasicRuleSignals();

debugScript("after");

$rulesTag = getTag("%RULES%", $html);
$rules = "";
$first = 1;
$emptyRuleFound = 0;
$erg99 = QUERY("select * from basicRules where groupId='$groupId' order by id");
while ( $obj99 = GetNext($erg99, $emptyRuleFound) )
{
	debugScript("in");
  $actTag = $rulesTag;

  if ($ansicht == "Standard") removeTag("%OPT_LOCK%", $actTag);
  else
  {
    chooseTag("%OPT_LOCK%", $actTag);
    if ($obj99->groupLock == 1) $locked = "checked";
    else $locked = "";
    $actTag = str_replace("%LOCK_CHECKED%", $locked, $actTag);
  }
  
  if ($obj99->intraDay == 1) $checked = "checked";
  else $checked = "";
  $actTag = str_replace("%INTRADAY_CHECKED%", $checked, $actTag);

  
  if ($obj99->active==1)
  {
  	$actTag = str_replace("%ACTIVITY_IMAGE%","img/online2.gif",$actTag);
  	$actTag = str_replace("%ACTIVE_STATUS%","0",$actTag);
  	$actTag = str_replace("%ACTIVE_TITLE%","Regel ist aktiv. Klicken, um sie zu deaktivieren.",$actTag);
  }
  else
  {
  	$actTag = str_replace("%ACTIVITY_IMAGE%","img/offline2.gif",$actTag);
  	$actTag = str_replace("%ACTIVE_STATUS%","1",$actTag);
  	$actTag = str_replace("%ACTIVE_TITLE%","Regel ist inaktiv. Klicken, um sie zu aktivieren.",$actTag);
  }
  
  if (! isset($obj99->id))
  {
    $obj99 = "";
    $obj99->id = - 1;
  }
  
  $actTag = str_replace("%RULE_ID%", $obj99->id, $actTag);
  
  $fourthLine = "sadfjkhsadjkfhasdf2w32";
  if ($myClassesId == $dimmerClassesId)
  {
    $vals = "-";
    $names = "----";
    for($i = 100; $i >= 1; $i--)
    {
      $vals .= "," . $i;
      $names .= "," . $i . " %";
    }
    
    $valsDauer = "0";
    $namesDauer = "daueran";
    for($i = 1; $i < 60; $i++)
    {
      $valsDauer .= "," . $i;
      $namesDauer .= "," . $i . " Sek";
    }
    for($i = 1; $i < 60; $i++)
    {
      $valsDauer .= "," . ($i * 60);
      $namesDauer .= "," . $i . " Min";
    }
    for($i = 1; $i <= 18; $i++)
    {
      $valsDauer .= "," . ($i * 60 * 60);
      $namesDauer .= "," . $i . " Std";
    }
    
    $actTag = str_replace("%FIRST%", "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt1, $vals, $names) . "</select><br>
                                     <select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1Dauer&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt1Dauer, $valsDauer, $namesDauer) . "</select>", $actTag);
    
    if ($obj99->fkt2 == "true") $checked = "checked";
    else $checked = "";
    $actTag = str_replace("%SECOND%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt2&value='+this.checked);\">", $actTag);
    
    if ($obj99->fkt3 == "true") $checked = "checked";
    else $checked = "";
    $thirdLine = "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt3&value='+this.checked);\">";
    $actTag = str_replace("%THIRD%", $thirdLine, $actTag);
    
    $fourthLine = "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4, $vals, $names) . "</select><br>
                 <select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4Dauer&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4Dauer, $valsDauer, $namesDauer) . "</select>";
    $actTag = str_replace("%FOURTH%", $fourthLine, $actTag);
    
    if ($mixGroup == 1) $actTag = str_replace("%LED_STATUS%", "<select style='text-align:right;width:100px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeLedStatus&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->ledStatus, "0,2,3", "Kein,Teilszene,Komplettszene") . "</select>", $actTag);
    else $actTag = str_replace("%LED_STATUS%", "<select style='text-align:right;width:100px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeLedStatus&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->ledStatus, "0,1,2,3", "Kein,Aktor Einzeln,Teilszene,Komplettszene") . "</select>", $actTag);
  }
  else if ($myClassesId == $ledClassesId)
  {
    $vals = "-";
    $names = "----";
    for($i = 100; $i >= 1; $i--)
    {
      $vals .= "," . $i;
      $names .= "," . $i . " %";
    }
    
    $valsDauer = "0";
    $namesDauer = "daueran";
    for($i = 1; $i < 60; $i++)
    {
      $valsDauer .= "," . $i;
      $namesDauer .= "," . $i . " Sek";
    }
    for($i = 1; $i < 60; $i++)
    {
      $valsDauer .= "," . ($i * 60);
      $namesDauer .= "," . $i . " Min";
    }
    for($i = 1; $i <= 18; $i++)
    {
      $valsDauer .= "," . ($i * 60 * 60);
      $namesDauer .= "," . $i . " Std";
    }
    
    if ($obj99->fkt1 == "true")
      $obj99->fkt1 = 0;
    
    $actTag = str_replace("%FIRST%", "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt1, $vals, $names) . "</select><br>
                                     <select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1Dauer&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt1Dauer, $valsDauer, $namesDauer) . "</select>", $actTag);
    
    if ($obj99->fkt2 == "true") $checked = "checked";
    else $checked = "";
    $actTag = str_replace("%SECOND%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt2&value='+this.checked);\">", $actTag);
    
    $fourthLine = "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4, $vals, $names) . "</select><br>
                 <select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4Dauer&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4Dauer, $valsDauer, $namesDauer) . "</select>";
    $actTag = str_replace("%FOURTH%", $fourthLine, $actTag);
    
    $actTag = str_replace("%LED_STATUS%", "<select style='text-align:right;width:100px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeLedStatus&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->ledStatus, "0,1,2,3", "Kein,Aktor Einzeln,Teilszene,Komplettszene") . "</select>", $actTag);
  }
  else if ($myClassesId == $rolloClassesId)
  {
  	debugScript("in2");
    $vals = "-";
    $names = "----";
    for($i = 1; $i <= 100; $i++)
    {
      $vals .= "," . $i;
      $names .= "," . $i . " % zu";
    }
    
    if ($obj99->fkt1 == "true")
      $checked = "checked";
    else
      $checked = "";
    $actTag = str_replace("%FIRST%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1&value='+this.checked);\">", $actTag);
    
    if ($obj99->fkt2 == "true")
      $checked = "checked";
    else
      $checked = "";
    $actTag = str_replace("%SECOND%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt2&value='+this.checked);\">", $actTag);
    
    if ($obj99->fkt3 == "true")
      $checked = "checked";
    else
      $checked = "";
    $thirdLine = "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt3&value='+this.checked);\">";
    $actTag = str_replace("%THIRD%", $thirdLine, $actTag);
    
    $fourthLine = "<select style='text-align:right' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4, $vals, $names) . "</select>";
    $actTag = str_replace("%FOURTH%", $fourthLine, $actTag);
    
    $actTag = str_replace("%LED_STATUS%", "<select style='text-align:right;width:100px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeLedStatus&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->ledStatus, "0,1,2,3", "Kein,Aktor Einzeln,Teilszene,Komplettszene") . "</select>", $actTag);
  }
  else if ($myClassesId == $logicalButtonClassesId)
  {
    $vals = "-,C";
    $names = "----,Konfig";
    for($i = 100; $i >= 1; $i--)
    {
      $vals .= "," . $i;
      $names .= "," . $i . " %";
    }
    
    $valsDauer = "0";
    $namesDauer = "daueran";
    for($i = 1; $i < 60; $i++)
    {
      $valsDauer .= "," . $i;
      $namesDauer .= "," . $i . " Sek";
    }
    for($i = 1; $i < 60; $i++)
    {
      $valsDauer .= "," . ($i * 60);
      $namesDauer .= "," . $i . " Min";
    }
    for($i = 1; $i <= 18; $i++)
    {
      $valsDauer .= "," . ($i * 60 * 60);
      $namesDauer .= "," . $i . " Std";
    }
    
    if ($obj99->fkt1 == "true")
      $obj99->fkt1 = "C";
    
    $actTag = str_replace("%FIRST%", "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt1, $vals, $names) . "</select><br>
                                     <select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1Dauer&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt1Dauer, $valsDauer, $namesDauer) . "</select>", $actTag);
    
    if ($obj99->fkt2 == "true")
      $checked = "checked";
    else
      $checked = "";
    $actTag = str_replace("%SECOND%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt2&value='+this.checked);\">", $actTag);
    
    $actTag = str_replace("%FOURTH%", "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4, $vals, $names) . "</select><br>
                                      <select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4Dauer&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4Dauer, $valsDauer, $namesDauer) . "</select>", $actTag);
    
    $actTag = str_replace("%LED_STATUS%", "<select style='text-align:right;width:100px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeLedStatus&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->ledStatus, "0,1,2,3", "Kein,Aktor Einzeln,Teilszene,Komplettszene") . "</select>", $actTag);
  }
  else if ($myClassesId == $schalterClassesId)
  {
    $vals = "-,0";
    $names = "----,daueran";
    for($i = 1; $i < 60; $i++)
    {
      $vals .= "," . $i;
      $names .= "," . $i . " Sek";
    }
    for($i = 1; $i < 60; $i++)
    {
      $vals .= "," . ($i * 60);
      $names .= "," . $i . " Min";
    }
    for($i = 1; $i <= 18; $i++)
    {
      $vals .= "," . ($i * 60 * 60);
      $names .= "," . $i . " Std";
    }
    
    if ($obj99->fkt1 == "true") $obj99->fkt1 = 0;
    $actTag = str_replace("%FIRST%", "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt1, $vals, $names) . "</select>", $actTag);
    
    if ($obj99->fkt2 == "true") $checked = "checked";
    else $checked = "";
    $actTag = str_replace("%SECOND%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt2&value='+this.checked);\">", $actTag);
    
    $actTag = str_replace("%FOURTH%", "<select style='text-align:right;width:90px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt4&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->fkt4, $vals, $names) . "</select>", $actTag);
    
    if ($mixGroup == 1) $actTag = str_replace("%LED_STATUS%", "<select style='text-align:right;width:100px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeLedStatus&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->ledStatus, "0,2,3", "Kein,Teilszene,Komplettszene") . "</select>", $actTag);
    else $actTag = str_replace("%LED_STATUS%", "<select style='text-align:right;width:100px' onChange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeLedStatus&value='+this.options[this.selectedIndex].value);\">" . getSelect($obj99->ledStatus, "0,1,2,3", "Kein,Aktor Einzeln,Teilszene,Komplettszene") . "</select>", $actTag);
  }
  else if ($myClassesId == $tasterClassesId)
  {
    if ($obj99->fkt1 == "true") $checked = "checked";
    else $checked = "";
    $actTag = str_replace("%FIRST%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt1&value='+this.checked);\">", $actTag);
    
    if ($obj99->fkt2 == "true") $checked = "checked";
    else $checked = "";
    $actTag = str_replace("%SECOND%", "<input $checked type=checkbox onchange=\"sendAjax('editBaseConfig.php?groupId=$groupId&ruleId=$obj99->id&action=changeFkt2&value='+this.checked);\">", $actTag);
  }
  
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

 
  if ($obj99->template == "") $obj99->template = "Default";
  $actTag = str_replace("%TEMPLATE_OPTIONS%", getSelect($obj99->template, $templateNames, $templateNames), $actTag);
  
  $signalsTag = getTag("%SIGNALS%", $actTag);
  debugScript("in4");
  $signals = "";
  foreach ((array)$allRuleSignals as $obj)
  {
    if ($obj->ruleId == $obj99->id)
    {
      $actSignalTag = $signalsTag;
      $actSignalTag = str_replace("%SIGNAL_ID%", $obj->id, $actSignalTag);
      
      if ($obj->featureInstanceId < 0) //gruppenSignale
      {
        removeTag("%OPT_EDIT_SIGNAL%", $actSignalTag);
        
        $signalGroupId = $obj->featureInstanceId * - 1;
        
        $erg34 = QUERY("select eventType,name,groupType from  basicRuleGroupSignals join groups on (groups.id=basicRuleGroupSignals.groupId) where basicRuleGroupSignals.id='$signalGroupId' limit 1");
        $obj34 = mysqli_fetch_OBJECT($erg34);
        
        if ($obj34->groupType == "SIGNALS-AND") $groupType = "UND";
        else if ($obj34->groupType == "SIGNALS-OR") $groupType = "ODER";
        else $groupType = "UNBEKANNT";
        
        $actSignalTag = str_replace("%SIGNAL_ROOM%", "Gruppe " . $obj34->name, $actSignalTag);
        if ($obj34->eventType == "ACTIVE") $actSignalTag = str_replace("%SIGNAL_NAME%", "Erreichen logischer $groupType Zustand", $actSignalTag);
        else if ($obj34->eventType == "DEACTIVE") $actSignalTag = str_replace("%SIGNAL_NAME%", "Verlassen  logischer $groupType Zustand", $actSignalTag);
        else $actSignalTag = str_replace("%SIGNAL_NAME%", "unbekannt", $actSignalTag);
      }
      else
      {
        $actClassesId = getClassesIdByFeatureInstanceId($obj->featureInstanceId);
        
        if ($actClassesId == $irClassesId)
        {
          chooseTag("%OPT_EDIT_SIGNAL%", $actSignalTag);
          $actSignalTag = str_replace("%FEATURE_FUNCTION_ID%", "111", $actSignalTag);
        }
        else if ($actClassesId == $CONTROLLER_CLASSES_ID)
        {
          chooseTag("%OPT_EDIT_SIGNAL%", $actSignalTag);
          $actSignalTag = str_replace("%FEATURE_FUNCTION_ID%", "129", $actSignalTag);
        }
        else
          removeTag("%OPT_EDIT_SIGNAL%", $actSignalTag);
          
          // evTime oder evDay oder evNight vom controller
        if ($actClassesId == $CONTROLLER_CLASSES_ID)
        {
          $actSignalTag = str_replace("%SIGNAL_ROOM%", "", $actSignalTag);
          
          $erg = QUERY("select paramValue from basicRuleSignalParams where ruleSignalId='$obj->id' limit 1");
          if ($row = mysqli_fetch_ROW($erg))
          {
            if ($row[0] == - 1) $signalName = "Zeitgesteuert: Bei Sonnenaufgang";
            else if ($row[0] == - 2) $signalName = "Zeitgesteuert: Bei Sonnenuntergang";
            else
            {
              $weekTime = parseWeekTimeToObject($row[0]);
              $signalName = "Zeitgesteuert: " . $weekTime->tag . " " . $weekTime->stunde . ":" . $weekTime->minute;
            }
          }
          
          $actSignalTag = str_replace("%SIGNAL_CLASS%", "", $actSignalTag);
          $actSignalTag = str_replace("%SIGNAL_NAME%", $signalName, $actSignalTag);
          removeTag("%OPT_ADD_SIGNAL%", $actTag);
          removeTag("%OPT_COPY_SIGNAL%", $actTag);
          $actTag = str_replace($thirdLine, "", $actTag);
          $actTag = str_replace($fourthLine, "", $actTag);
          
          getTag("%OPT_TEMPLATES%", $actTag);
          $actTag = str_replace("%OPT_TEMPLATES%", "<td></td>", $actTag);
          /*getTag("%OPT_EXTRAS%", $actTag);
          $actTag = str_replace("%OPT_EXTRAS%", "<td></td>", $actTag);
          */
          
          $extras = ",Zeitzufall-1,Zeitzufall-2,Zeitzufall-3";
          $actTag = str_replace("%EXTRAS_OPTIONS%", getSelect($obj99->extras, $extras, $extras), $actTag);

          getTag("%OPT_LED_STATUS_SPALTE%", $actTag);
          $actTag = str_replace("%OPT_LED_STATUS_SPALTE%", "<td></td>", $actTag);
        }
        else
        {
          $myFeatureInstance = $allFeatureInstances[$obj->featureInstanceId];
          $myRoom = getRoomForFeatureInstance($obj->featureInstanceId);
          $actSignalTag = str_replace("%SIGNAL_ROOM%", $myRoom->name, $actSignalTag);
          $actSignalTag = str_replace("%SIGNAL_CLASS%", i18n($allFeatureClasses[$myFeatureInstance->featureClassesId]->name), $actSignalTag);
          $actSignalTag = str_replace("%SIGNAL_NAME%", $myFeatureInstance->name, $actSignalTag);
        }
      }
      
      $signals .= $actSignalTag;
    }
  }
  debugScript("in5");
  chooseTag("%OPT_ADD_SIGNAL%", $actTag);
  chooseTag("%OPT_TIMES%", $actTag);
  chooseTag("%OPT_TEMPLATES%", $actTag);
  
  // Wird ggf. oben schon anders überschrieben. Das hier ist Default
  $extras = ",Rotation,Bewegungsmelder,Heizungssteuerung";
  $actTag = str_replace("%EXTRAS_OPTIONS%", getSelect($obj99->extras, $extras, $extras), $actTag);

  chooseTag("%OPT_EXTRAS%", $actTag);
  chooseTag("%OPT_LED_STATUS_SPALTE%", $actTag);
  debugScript("in5b");
  if ($first == 1)
  {
    $first = 0;
    getTag("%OPT_COPY_SIGNAL%", $actTag);
    $actTag = str_replace("%OPT_COPY_SIGNAL%", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $actTag);
  }
  else
    chooseTag("%OPT_COPY_SIGNAL%", $actTag);
  
  $actTag = str_replace("%SIGNALS%", $signals, $actTag);
  if ($signals == "" && $emptyRuleFound != 1)
  {
    $emptyRuleFound = 1;
    removeTag("%OPT_DELETE%", $actTag);
    $myEmptyRule = $actTag;
  }
  else
  {
    chooseTag("%OPT_DELETE%", $actTag);
    $rules .= $actTag;
  }
  debugScript("in6");
}

if ($emptyRuleFound == 1)
  $rules .= $myEmptyRule;

$html = str_replace("%RULES%", $rules, $html);

debugScript("end");
show();

function getNext($erg, $emptySignalFound)
{
  if ($obj99 = mysqli_fetch_OBJECT($erg))
    return $obj99;
  if ($emptySignalFound == 1)
    return FALSE;
  return TRUE;
}

function parseWeekTimeToObject($value)
{
  $result = "";
  $weekTime = getWeekTime("dummy", $value);
  //selected value='1'>Di.
  

  $pos = strpos($weekTime, "selected");
  if ($pos === FALSE)
    return $result;
  $pos2 = strpos($weekTime, ">", $pos);
  if ($pos2 === FALSE)
    return $result;
  $pos3 = strpos($weekTime, "<", $pos2 + 1);
  if ($pos3 === FALSE)
    return $result;
  $result->tag = substr($weekTime, $pos2 + 1, $pos3 - $pos2 - 1);
  
  $pos = strpos($weekTime, "selected", $pos3);
  if ($pos === FALSE)
    return $result;
  $pos2 = strpos($weekTime, ">", $pos);
  if ($pos2 === FALSE)
    return $result;
  $pos3 = strpos($weekTime, "<", $pos2 + 1);
  if ($pos3 === FALSE)
    return $result;
  $result->stunde = substr($weekTime, $pos2 + 1, $pos3 - $pos2 - 1);
  
  $pos = strpos($weekTime, "selected", $pos3);
  if ($pos === FALSE)
    return $result;
  $pos2 = strpos($weekTime, ">", $pos);
  if ($pos2 === FALSE)
    return $result;
  $pos3 = strpos($weekTime, "<", $pos2 + 1);
  if ($pos3 === FALSE)
    return $result;
  $result->minute = substr($weekTime, $pos2 + 1, $pos3 - $pos2 - 1);
  
  return $result;
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