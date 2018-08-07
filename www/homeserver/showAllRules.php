<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

setupTreeAndContent("showAllRules_design.html", $message);

if ($showGeneratedGroups==0 && $_SESSION["ansicht"]!="Entwickler") $and=" and groups.generated='0'";

$table="rules";

$title="";

if ($bySensor==1)
{
	$title=" nach Tastern";
}
else if ($withTime==1)
{
	$title=" mit Zeitsteuerungen";
	$evDayFunctionId = getFunctionsIdByNameForClassName("controller","evDay");
	$evNightFunctionId = getFunctionsIdByNameForClassName("controller","evNight");
	$evTimeFunctionId = getFunctionsIdByNameForClassName("controller","evTime");
	$and.=" and (featureFunctionId='$evDayFunctionId' or featureFunctionId='$evNightFunctionId' or featureFunctionId='$evTimeFunctionId')";
	$joinSignal=" join ruleSignals on (ruleSignals.ruleId=rules.id)";
}
else if ($deactivated==1)
{
	$title=", die aktuell deaktiviert sind";
	$table="basicrules";
	$and.=" and basicrules.active='0'";
}
else
{
	$title=" nach Aktoren";
}

$html=str_replace("%TITLE%",$title,$html);

if ($byRoom==1) $title2.="Sortierung nach Räumen [<a href='showAllRules.php?withTime=$withTime&deactivated=$deactivated&bySensor=$bySensor&byRoom=0'>Nach Controller sortieren</a>]";
else $title2="Sortierung nach Controllerm [<a href='showAllRules.php?withTime=$withTime&deactivated=$deactivated&bySensor=$bySensor&byRoom=1'>Nach Räumen sortieren</a>]";

$html=str_replace("%TITLE2%",$title2,$html);


$elementsTag = getTag("%ELEMENTS%", $html);
$elements = "";


if ($byRoom==1)
{
	if ($bySensor==1)
	{
  	$lastRoom = "";
    
    $sql = "SELECT featureInstancesSignal.id as featureInstanceId, featureInstancesSignal.name as signalName, featureInstancesAktor.id as aktorId, featureInstancesAktor.name as aktorName, rooms.name AS roomName, rooms.id AS roomId, featureClasses.name AS featureClassName, groups.id as groupId
    FROM ruleSignals
    join rules on (ruleSignals.ruleId = rules.id)
    join ruleActions on (ruleActions.ruleId = rules.id)
    JOIN featureInstances as featureInstancesSignal ON ( featureInstancesSignal.id = ruleSignals.featureInstanceId )
    JOIN featureInstances as featureInstancesAktor ON ( featureInstancesAktor.id = ruleActions.featureInstanceId )
    JOIN featureClasses ON ( featureClasses.id = featureInstancesSignal.featureClassesId )
    join roomFeatures on (featureInstancesSignal.id=roomFeatures.featureInstanceId)
    JOIN rooms ON ( rooms.id = roomfeatures.roomId )
    join groupFeatures on (groupFeatures.featureInstanceId=featureInstancesAktor.id)
    join groups on (groupFeatures.groupId=groups.id)
    where featureClasses.id=1 and single=1
    ORDER BY roomName, featureClassName, signalName ";
    
    $erg = QUERY($sql);
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      if ($ready[$obj->roomId . "-" . $obj->featureInstanceId."-".$obj->aktorId] == 1) continue;
      $ready[$obj->roomId . "-" . $obj->featureInstanceId."-".$obj->aktorId] = 1;
      
      if ($obj->roomId != $lastRoom)
      {
        if ($lastRoom != "") $elements .= "<tr><td><br></td></tr>";
        $elements .= "<tr><td><b>".$obj->roomName . "</b></td></tr>";
        $lastRoom = $obj->roomId;
      }
      
      $actTag = $elementsTag;

      $actTag = str_replace("%LAST_OPEN%", $_SESSION["groupLinkNr" . $obj->groupId] - 1, $actTag);
      $actTag = str_replace("%GROUP_ID%", $obj->groupId, $actTag);
      $actTag = str_replace("%NAME%", $obj->signalName . " » " . $obj->aktorName, $actTag);
      $elements .= $actTag;
    }
  }
  else
  {
  	$lastRoom = "";
    
    $sql = "SELECT $table.groupId, single, groupFeatures.featureInstanceId, rooms.name AS roomName, rooms.id AS roomId, featureInstances.name AS featureInstanceName, featureClasses.name AS featureClassName
    FROM $table
    JOIN groups ON ( groups.id = $table.groupId )
    JOIN groupFeatures ON ( groupFeatures.groupId = groups.id )
    JOIN featureInstances ON ( featureInstances.id = groupFeatures.featureInstanceId )
    JOIN featureClasses ON ( featureClasses.id = featureInstances.featureClassesId )
    JOIN roomfeatures ON ( roomfeatures.featureInstanceid = featureInstances.id )
    JOIN rooms ON ( rooms.id = roomfeatures.roomId )
    $joinSignal
    where single=1 $and
    ORDER BY roomName, featureClassName, featureInstanceName ";
    
    $erg = QUERY($sql);
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      if ($ready[$obj->roomId . "-" . $obj->featureInstanceId] == 1) continue;
      $ready[$obj->roomId . "-" . $obj->featureInstanceId] = 1;
      
      if ($obj->roomId != $lastRoom)
      {
        if ($lastRoom != "") $elements .= "<tr><td><br></td></tr>";
        $elements .= "<tr><td><b>".$obj->roomName . "</b></td></tr>";
        $lastRoom = $obj->roomId;
      }
      
      $actTag = $elementsTag;
      $actTag = str_replace("%LAST_OPEN%", $_SESSION["groupLinkNr" . $obj->groupId] - 1, $actTag);
      $actTag = str_replace("%GROUP_ID%", $obj->groupId, $actTag);
      
      $actTag = str_replace("%NAME%", $obj->featureClassName . " » " . $obj->featureInstanceName, $actTag);
      $elements .= $actTag;
    }
  }
}
else
{
	if ($bySensor==1)
	{
  	$lastController = "";
    
    $sql = "SELECT featureInstancesSignal.id as featureInstanceId, featureInstancesSignal.name as signalName, featureInstancesAktor.id as aktorId, featureInstancesAktor.name as aktorName, rooms.name AS roomName, rooms.id AS roomId, featureClasses.name AS featureClassName, groups.id as groupId, controller.id as controllerId, controller.name as controllerName
    FROM ruleSignals
    join rules on (ruleSignals.ruleId = rules.id)
    join ruleActions on (ruleActions.ruleId = rules.id)
    JOIN featureInstances as featureInstancesSignal ON ( featureInstancesSignal.id = ruleSignals.featureInstanceId )
    JOIN featureInstances as featureInstancesAktor ON ( featureInstancesAktor.id = ruleActions.featureInstanceId )
    JOIN featureClasses ON ( featureClasses.id = featureInstancesSignal.featureClassesId )
    join roomFeatures on (featureInstancesSignal.id=roomFeatures.featureInstanceId)
    JOIN rooms ON ( rooms.id = roomfeatures.roomId )
    join groupFeatures on (groupFeatures.featureInstanceId=featureInstancesAktor.id)
    join groups on (groupFeatures.groupId=groups.id)
    JOIN controller ON ( featureInstancesSignal.controllerId = controller.id )

    where featureClasses.id=1 and single=1
    ORDER BY roomName, featureClassName, signalName ";
   
    $erg = QUERY($sql);
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      if ($ready[$obj->controllerId . "-" . $obj->featureInstanceId."-".$obj->aktorId] == 1) continue;
      $ready[$obj->controllerId . "-" . $obj->featureInstanceId."-".$obj->aktorId] = 1;
      
      if ($obj->controllerId != $lastController)
      {
        if ($lastController != "") $elements .= "<tr><td><br></td></tr>";
        $elements .= "<tr><td><b>Controller " . $obj->controllerName . "</b></td></tr>";
        $lastController = $obj->controllerId;
      }
      
      $actTag = $elementsTag;
      $actTag = str_replace("%LAST_OPEN%", $_SESSION["groupLinkNr" . $obj->groupId] - 1, $actTag);
      $actTag = str_replace("%GROUP_ID%", $obj->groupId, $actTag);
      
      $actTag = str_replace("%NAME%", $obj->signalName . " » " . $obj->aktorName, $actTag);
      $elements .= $actTag;
    }
  }
  else
  {
    $lastController = "";
    
    $sql = "SELECT $table.groupId, single, groupFeatures.featureInstanceId, controller.name AS controllerName, controller.id AS controllerId, featureInstances.name AS featureInstanceName, featureClasses.name AS featureClassName
    from $table
    JOIN groups ON ( groups.id = $table.groupId )
    JOIN groupFeatures ON ( groupFeatures.groupId = groups.id )
    JOIN featureInstances ON ( featureInstances.id = groupFeatures.featureInstanceId )
    JOIN featureClasses ON ( featureClasses.id = featureInstances.featureClassesId )
    JOIN controller ON ( featureInstances.controllerId = controller.id )
    $joinSignal
    where single=1 $and
    ORDER BY controllerId, featureClassName, featureInstanceName ";
    
    $erg = QUERY($sql);
    while ( $obj = mysqli_fetch_OBJECT($erg) )
    {
      if ($ready[$obj->controllerId . "-" . $obj->featureInstanceId] == 1) continue;
      $ready[$obj->controllerId . "-" . $obj->featureInstanceId] = 1;
      
      if ($obj->controllerId != $lastController)
      {
        if ($lastController != "") $elements .= "<tr><td><br></td></tr>";
        $elements .= "<tr><td><b>Controller " . $obj->controllerName . "</b></td></tr>";
        $lastController = $obj->controllerId;
      }
      
      $actTag = $elementsTag;
      $actTag = str_replace("%LAST_OPEN%", $_SESSION["groupLinkNr" . $obj->groupId] - 1, $actTag);
      $actTag = str_replace("%GROUP_ID%", $obj->groupId, $actTag);
      
      $actTag = str_replace("%NAME%", $obj->featureClassName . " » " . $obj->featureInstanceName, $actTag);
      $elements .= $actTag;
    }
  }
}

if ($withTime!=1 && $deactivated!=1)
{
  $elements .= "<tr><td><br><br><b>Gruppenregeln</b><br></td></tr>";

  $sql = "SELECT ruleId, rules.groupId, groups.name as groupName
  FROM ruleSignals
  JOIN rules ON ( rules.id = ruleSignals.ruleId )
  JOIN groups ON ( groups.id = rules.groupId )
  where single=0 $and
  group by rules.groupId
  ORDER BY name";
  $erg = QUERY($sql);
  while ( $obj = mysqli_fetch_OBJECT($erg) )
  {
    $actTag = $elementsTag;
    $actTag = str_replace("%LAST_OPEN%", $_SESSION["groupLinkNr" . $obj->groupId] - 1, $actTag);
    $actTag = str_replace("%GROUP_ID%", $obj->groupId, $actTag);
    $actTag = str_replace("%NAME%", $obj->groupName, $actTag);
    $elements .= $actTag;
  }
}

$html = str_replace("%ELEMENTS%", $elements, $html);

show();
?>

