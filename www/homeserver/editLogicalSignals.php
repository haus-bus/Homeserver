<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action == "removeState")
{
  $activationState = ($nr - 1) * 10;
  $deactivationState = ($nr - 1) * 10 + 1;
  $erg = QUERY("select id from rules where groupId='$groupId' and activationStateId='$activationState' or activationStateId='$deactivationState' limit 2");
  while ( $row = MYSQL_FETCH_ROW($erg) )
  {
    deleteRule($row[0]);
  }
  
  $nr = 0;
  $erg = QUERY("select id,activationStateId from rules where groupId='$groupId' order by activationStateId");
  while ( $row = MYSQL_FETCH_ROW($erg) )
  {
    $activationStateId = $row[1];
    
    if ($activationStateId % 2 == 0)
    {
      $state = $nr * 10;
      QUERY("UPDATE rules set activationStateId='$state' where id='$row[0]' limit 1");
    }
    else
    {
      $state = $nr * 10 + 1;
      QUERY("UPDATE rules set activationStateId='$state' where id='$row[0]' limit 1");
      $nr++;
    }
  }
}

setupTreeAndContent("editLogicalSignals_design.html", $message);

$html = str_replace("%GROUP_ID%", $groupId, $html);

$allFeatureInstances = readFeatureInstances();
$allFeatureClasses = readFeatureClasses();
$allFeatureFunctions = readFeatureFunctions();

$statesTag = getTag("%STATES%", $html);
$activeTag = getTag("%ACTIVE%", $statesTag);

$theStates = "";
$erg = QUERY("select rules.id as ruleId, ruleSignals.id as signalId, activationStateId,featureInstanceId,featureFunctionId from rules left join ruleSignals on (ruleSignals.ruleId=rules.id) where groupId='$groupId'");
while ( $obj = MYSQL_FETCH_OBJECT($erg) )
{
  $nr = (int)($obj->activationStateId / 10);
  
  $actTag = $activeTag;
  
  $roomName = getRoomForFeatureInstance($obj->featureInstanceId)->name;
  $actTag = str_replace("%SIGNAL_ROOM%", $roomName, $actTag);
  
  $actFeatureInstance = $allFeatureInstances[$obj->featureInstanceId];
  $actTag = str_replace("%SIGNAL_NAME%", $actFeatureInstance->name, $actTag);
  
  $actClass = $allFeatureClasses[$actFeatureInstance->featureClassesId];
  $actTag = str_replace("%SIGNAL_CLASS%", $actClass->name, $actTag);
  
  $actTag = str_replace("%SIGNAL_FUNCTION%", i18n($allFeatureFunctions[$obj->featureFunctionId]->name), $actTag);
  $actTag = str_replace("%RULE_ID%", $obj->ruleId, $actTag);
  $actTag = str_replace("%SIGNAL_ID%", $obj->signalId, $actTag);
  $actTag = str_replace("%FEATURE_FUNCTION_ID%", $obj->featureFunctionId, $actTag);
  
  if ($obj->activationStateId % 2 == 0)
  {
    if ($obj->featureInstanceId != null)
      $theStates[$nr]["actives"] .= $actTag;
    $theStates[$nr]["activeRuleId"] = $obj->ruleId;
  }
  else
  {
    if ($obj->featureInstanceId != null)
      $theStates[$nr]["deactives"] .= $actTag;
    $theStates[$nr]["deactiveRuleId"] = $obj->ruleId;
  }
}

$last = - 1;
$foundEmpty = 0;
if ($theStates != "")
{
  ksort($theStates);
  foreach ( $theStates as $nr => $content )
  {
    $last = $nr;
    $actTag = $statesTag;
    
    if ($content["actives"] == "" && $content["deactives"] == "")
    {
      $foundEmpty = 1;
      removeTag("%OPT_DELETE%", $actTag);
    }
    else
      chooseTag("%OPT_DELETE%", $actTag);
    
    $actTag = str_replace("%NR%", $nr + 1, $actTag);
    $actTag = str_replace("%ACTIVE_RULE_ID%", $content["activeRuleId"], $actTag);
    $actTag = str_replace("%DEACTIVE_RULE_ID%", $content["deactiveRuleId"], $actTag);
    $actTag = str_replace("%ACTIVE%", $content["actives"], $actTag);
    $actTag = str_replace("%DEACTIVE%", $content["deactives"], $actTag);
    
    $states .= $actTag;
  }
}

if ($foundEmpty == 0)
{
  $last++;
  $activationState = $last * 10;
  QUERY("INSERT into rules (groupId, activationStateId,startDay,startHour,startMinute,endDay,endHour,endMinute) values('$groupId','$activationState','7','31','255','7','31','255')");
  $acticationRuleId = mysql_insert_id();
  $deactivationState = ($last * 10) + 1;
  QUERY("INSERT into rules (groupId, activationStateId,startDay,startHour,startMinute,endDay,endHour,endMinute) values('$groupId','$deactivationState','7','31','255','7','31','255')");
  $deacticationRuleId = mysql_insert_id();
  
  $actTag = $statesTag;
  removeTag("%OPT_DELETE%", $actTag);
  $actTag = str_replace("%NR%", $last + 1, $actTag);
  $actTag = str_replace("%ACTIVE_RULE_ID%", $acticationRuleId, $actTag);
  $actTag = str_replace("%DEACTIVE_RULE_ID%", $deacticationRuleId, $actTag);
  $actTag = str_replace("%ACTIVE%", "", $actTag);
  $actTag = str_replace("%DEACTIVE%", "", $actTag);
  $states .= $actTag;
}

$html = str_replace("%STATES%", $states, $html);

show();

show();
?>