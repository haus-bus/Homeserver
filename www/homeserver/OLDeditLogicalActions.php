<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

setupTreeAndContent("editLogicalActions_design.html", $message);

$html = str_replace("%GROUP_ID%", $groupId, $html);

$erg = QUERY("select groupType from groups where id='$groupId' limit 1");
$row = MYSQL_FETCH_ROW($erg);
if ($row[0] == "AND")
  $html = str_replace("%TYPE%", "UND", $html);
else if ($row[0] == "OR")
  $html = str_replace("%TYPE%", "ODER", $html);
else
  die("Fehler: Unbekannter Gruppentyp: " . $row[0]);

if ($logical == "enter")
{
  $html = str_replace("%STATE%", "Erreichen", $html);
  $html = str_replace("%LOGICAL_GROUP_MODE%", "2", $html);
  $myActivationStateId = 0;
}
else if ($logical == "leave")
{
  $html = str_replace("%STATE%", "Verlassen", $html);
  $html = str_replace("%LOGICAL_GROUP_MODE%", "3", $html);
  $myActivationStateId = 1;
}
  
else
  die("Unbekannter modus " . $logical);

//if ($mode)

$ruleId = 0;
$erg = QUERY("select * from rules where groupId='$groupId' and activationStateId='$myActivationStateId' limit 1");
if ($obj99 = MYSQL_FETCH_OBJECT($erg))
{
}
else
{
	QUERY("INSERT into rules (groupId,baseRule,activationStateId, startDay,startHour,startMinute,endDay,endHour,endMinute) values('$groupId','1','$myActivationStateId','7','31','255','7','31','255')");
  $obj99->id = mysql_insert_id();
}

$allFeatureInstances = readFeatureInstances();
$allFeatureClasses = readFeatureClasses();
$allFeatureFunctions = readFeatureFunctions();
$allRooms = readRooms();
$allRoomFeatures = readRoomFeatures();
$allGroupStates = readGroupStates();

$allRuleActions = readRuleActions();

$actionsTag = getTag("%ACTIONS%", $html);

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
$html = str_replace("%ACTIONS%", $actions, $html);
$html = str_replace("%RULE_ID%", $obj99->id, $html);

show();
?>