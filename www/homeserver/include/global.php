<?php
ini_set ( 'max_execution_time', 300 );
ini_set ( "memory_limit", '512M' );
//error_reporting ( E_ERROR | E_PARSE );

$res = @session_start ();
if (! $res) session_start ();

if (! isset ( $_SESSION ["actLanguage"] )) switchLanguage ( "Deutsch" );
  
function readControllers()
{
  $erg = QUERY ( "select SQL_CACHE * from controller" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $controllers [$obj->id] = $obj;
  }
  
  return $controllers;
}
function readFeatureClasses()
{
  $erg = QUERY ( "select SQL_CACHE * from featureClasses order by name" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $featureClasses [$obj->id] = $obj;
  }
  
  return $featureClasses;
}
function getNameForFeatureClass($featureClassesId)
{
  $erg = QUERY ( "select name from featureClasses where id='$featureClassesId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    return $row [0];
  return "";
}
function getClassesIdByName($featureClassName)
{
	if (isset($_SESSION["featureClassNameCache"][$featureClassName])) return $_SESSION["featureClassNameCache"][$featureClassName];
	
  $erg = QUERY ( "select id from featureClasses where name='$featureClassName' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
  {
  	$_SESSION["featureClassNameCache"][$featureClassName]=$row[0];
    return $row [0];
  }
  return "";
}
function getClassesIdByFeatureInstanceId($featureInstanceId)
{
  $erg = QUERY ( "select featureClassesId from featureInstances where id='$featureInstanceId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    return $row [0];
  return "";
}
function getClassIdByName($featureClassName)
{
  $erg = QUERY ( "select classId from featureClasses where name='$featureClassName' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    return $row [0];
  return "";
}
function readFeatureClassesThatSupportType($type)
{
  $erg = QUERY ( "select SQL_CACHE distinct(featureClassesId) from featureFunctions where type='$type'" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $result [$row [0]] = 1;
  }
  return $result;
}
function getRoomForFeatureInstance($featureInstanceId)
{
  $erg = QUERY ( "select roomId from roomFeatures where featureInstanceId='$featureInstanceId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
  {
    $erg = QUERY ( "select * from rooms where id='$row[0]' limit 1" );
    if ($obj = MYSQLi_FETCH_OBJECT ( $erg ))
    {
      return $obj;
    }
  }
  
  $erg = QUERY ( "select controller.name from controller join featureInstances on (featureInstances.controllerId = controller.id) where featureInstances.id='$featureInstanceId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
  {
    $result->name = "Controller " . $row [0];
    $result->id = "0";
    return $result;
  }
  
  $result->name = "Keinem Raum zugeordnet";
  $result->id = "0";
  return $result;
}
function getFeaturesForRoom($roomId)
{
  $pos = 0;
  $erg = QUERY ( "select SQL_CACHE featureInstanceId from roomFeatures where roomId='$roomId'" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $erg2 = QUERY ( "select SQL_CACHE * from featureInstances where id='$row[0]' limit 1" );
    if ($obj = MYSQLi_FETCH_OBJECT ( $erg2 ))
    {
      $result [$pos ++] = $obj;
    }
  }
  return $result;
}
function readRooms()
{
  global $roomsCache;
  
  if (! isset ( $roomsCache ))
  {
    $erg = QUERY ( "select SQL_CACHE * from rooms order by name" );
    while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
    {
      $roomsCache [$obj->id] = $obj;
    }
  }
  
  return $roomsCache;
}
function readRoomFeatures()
{
  $erg = QUERY ( "select SQL_CACHE * from roomFeatures" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $roomFeatures [$obj->id] = $obj;
  }
  
  return $roomFeatures;
}
function readMyRuleSignals($ruleId)
{
  $erg = QUERY ( "select SQL_CACHE * from ruleSignals where ruleId='$ruleId'" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $result [$obj->featureInstanceId . "-" . $obj->featureFunctionId] = 1;
  }
  return $result;
}
function readMyBasicRuleSignals($ruleId)
{
  $erg = QUERY ( "select SQL_CACHE * from basicRuleSignals where ruleId='$ruleId'" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $result [$obj->featureInstanceId] = 1;
  }
  return $result;
}
function readMyRuleActions($ruleId)
{
  $erg = QUERY ( "select SQL_CACHE * from ruleActions where ruleId='$ruleId'" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $result [$obj->featureInstanceId] = 1;
  }
  return $result;
}
function readRuleActions()
{
  $erg = QUERY ( "select SQL_CACHE * from ruleActions order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $ruleActions [$obj->id] = $obj;
  }
  
  return $ruleActions;
}
function readRuleSignals()
{
  $erg = QUERY ( "select SQL_CACHE * from ruleSignals order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $ruleSignals [$obj->id] = $obj;
  }
  
  return $ruleSignals;
}
function readBasicRuleSignals()
{
  $erg = QUERY ( "select SQL_CACHE * from basicRuleSignals order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $ruleSignals [$obj->id] = $obj;
  }
  
  return $ruleSignals;
}
function readGroupFeatures($groupId)
{
  $erg = QUERY ( "select SQL_CACHE * from groupFeatures where groupId='$groupId'" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $result [$obj->featureInstanceId] = $obj;
  }
  return $result;
}
function readGroups()
{
  $erg = QUERY ( "select SQL_CACHE * from groups order by name" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $groups [$obj->id] = $obj;
  }
  
  return $groups;
}

// Sucht feste Einzelgruppe zu einem Feature
function readMySingleGroup($featureInstanceId)
{
  $erg = QUERY ( "select SQL_CACHE groups.id, groups.single from groups join groupFeatures on (groupFeatures.groupId=groups.id) where groups.single='1' and featureInstanceId='$featureInstanceId' limit 1" );
  if ($obj = MYSQLi_FETCH_OBJECT ( $erg ))
    return $obj;
  echo $featureInstanceId . " hat keinen Gruppeneintrag <br>";
}

function readFeatureInstances($fields="*")
{
	if ($fields!="*") $fields="id,".$fields;
  $erg = QUERY ( "select SQL_CACHE $fields from featureInstances" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $featureInstances [$obj->id] = $obj;
  }
  return $featureInstances;
}

function readGroupStates()
{
  $erg = QUERY ( "select SQL_CACHE * from groupStates order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $groupStates [$obj->id] = $obj;
  }
  return $groupStates;
}

function readFeatureFunctions()
{
  $erg = QUERY ( "select SQL_CACHE * from featureFunctions order by type,functionId" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $featureFunctions [$obj->id] = $obj;
  }
  return $featureFunctions;
}

function readFeatureFunctionParams()
{
  $erg = QUERY ( "select SQL_CACHE * from featureFunctionParams order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $featureFunctionParams [$obj->id] = $obj;
  }
  return $featureFunctionParams;
}

function readFeatureFunctionEnums()
{
  $erg = QUERY ( "select SQL_CACHE * from featureFunctionEnums order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $featureFunctionEnums [$obj->id] = $obj;
  }
  return $featureFunctionEnums;
}

function readFeatureFunctionBitmasks()
{
  $erg = QUERY ( "select SQL_CACHE * from featureFunctionBitmasks order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $featureFunctionBitmasks [$obj->id] = $obj;
  }
  return $featureFunctionBitmasks;
}

function getFunctionIdByName($objectId, $featureFunctionName)
{
  $classId = getFeatureClassesId ( $objectId );
  $erg = QUERY ( "select functionId from featureFunctions where name='$featureFunctionName' and featureClassesId='$classId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    return $row [0];
  return "";
}

function getFunctionIdByNameForClassName($className, $featureFunctionName)
{
	$classesId = getClassesIdByName($className);
  $erg = QUERY ( "select functionId from featureFunctions where name='$featureFunctionName' and featureClassesId='$classesId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    return $row [0];
  return "";
}

function getFunctionsIdByNameForClassName($className, $featureFunctionName)
{
	$classesId = getClassesIdByName($className);
  $erg = QUERY ( "select id from featureFunctions where name='$featureFunctionName' and featureClassesId='$classesId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    return $row [0];
  return "";
}

function readFeatureFunctionBitmaskNames($featureFunctionId, $paramId)
{
  $i = 0;
  $erg = QUERY ( "select SQL_CACHE name from featureFunctionBitmasks where featureFunctionId='$featureFunctionId' and paramId='$paramId' order by id limit 8" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $featureFunctionBitmaskNames [$i ++] = $obj->name;
  }
  return $featureFunctionBitmaskNames;
}
function getSelect($value, $possibleValues, $possibleNames = "")
{
  $result = "";
  if ($possibleNames == "") $possibleNames = $possibleValues;
  
  $parts = explode ( ",", $possibleValues );
  $partsNames = explode ( ",", $possibleNames );
  
  $i = 0;
  foreach ( $parts as $act )
  {
    $result .= getSelectItem ( $act, $partsNames [$i ++], $value );
  }
  
  return $result;
}
function getSelectItem($act, $name, $value)
{
	//echo $act." / ".$name." / ".$value."<br>";
  if ($act == $value) $selected = "selected";
  else $selected = "";
  return "<option $selected value='$act'>$name";
}

/// RECURSIVES L�EN
function checkDatabaseIntegrity()
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/dataBaseIntegrity.php");
	/*echo "checkDatabaseIntegrity <br>";
	print_r(debug_backtrace());
	echo "<hr>";
	*/
	checkReferenceIntegrity(0);
}

function deleteController($controllerId, $autoClean=1)
{
  QUERY ("DELETE from controller where id='$controllerId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteFeatureClass($featureClassesId, $autoClean=1)
{
  QUERY ( "DELETE from featureClasses where id='$featureClassesId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteGroup($groupId, $autoClean=1)
{
  QUERY ( "DELETE from groups where id='$groupId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}
 
function deleteFeatureFunctionEnum($featureFunctionEnumId, $autoClean=1)
{
  QUERY ( "DELETE from featureFunctionEnums where id='$featureFunctionEnumId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteFeatureFunctionParam($featureFunctionParamsId, $autoClean=1)
{
  QUERY ( "DELETE from featureFunctionParams where id='$featureFunctionParamsId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteFeatureFunction($featureFunctionId, $autoClean=1)
{
  QUERY ( "DELETE from featureFunctions where id='$featureFunctionId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteFeatureInstance($featureInstanceId, $autoClean=1)
{
  QUERY ( "DELETE from featureInstances where id='$featureInstanceId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteGroupFeature($groupFeatureId, $autoClean=1)
{
  QUERY ( "DELETE from groupFeatures where id='$groupFeatureId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteGroupState($groupStateId, $autoClean=1)
{
  QUERY ( "DELETE from groupStates where id='$groupStateId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteRoomFeature($roomFeatureId, $autoClean=1)
{
  QUERY ( "DELETE from roomFeatures where id='$roomFeatureId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteRoom($roomId, $autoClean=1)
{
  QUERY ( "DELETE from rooms where id='$roomId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteBasicRuleSignalParam($ruleSignalParamId, $autoClean=1)
{
  QUERY ( "DELETE from basicRuleSignalParams where id='$ruleSignalParamId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteRuleAction($ruleActionId, $autoClean=1)
{
  QUERY ( "DELETE from ruleActions where id='$ruleActionId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteRuleActionParam($ruleActionParamId, $autoClean=1)
{
  QUERY ( "DELETE from ruleActionParams where id='$ruleActionParamId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteRule($ruleId, $autoClean=1)
{
  QUERY ( "DELETE from rules where id='$ruleId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteBaseRule($ruleId, $autoClean=1)
{
  QUERY ( "DELETE from basicRules where id='$ruleId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteRuleSignal($ruleSignalId, $autoClean=1)
{
  QUERY ( "DELETE from ruleSignals where id='$ruleSignalId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteRuleSignalParam($signalParamId, $autoClean=1)
{
  QUERY ( "DELETE from ruleSignalParams where id='$signalParamId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function deleteBaseRuleSignal($ruleSignalId, $autoClean=1)
{
  QUERY ( "DELETE from basicRuleSignals where id='$ruleSignalId' limit 1" );
  if ($autoClean==1) checkDatabaseIntegrity();
}

function switchLanguage($language)
{
  $_SESSION ["actLanguage"] = $language;
  
  require_once $_SERVER ["DOCUMENT_ROOT"] . '/homeserver/include/dbconnect.php';
  
  $erg = QUERY ( "select theKey,translation from languages where language = '$language'" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $_SESSION ["actLanguageSet"] [strtolower ( $obj->theKey )] = $obj->translation;
  }
}

function i18n($key)
{
  // if ($_SESSION["actLanguageSet"][strtolower($key)]!="") return $_SESSION["actLanguageSet"][strtolower($key)];
  return $key;
}

function parseWeekTime($value)
{
  $obj = new stdClass ();
  $obj->minute = $value & 0xff;
  $value = $value >> 8;
  $obj->hour = $value & 0x1F;
  $obj->day = $value >> 5;
  return $obj;
}
 
function toWeekTime($day, $hour, $minute)
{
  $value = $day << 5;
  $value += $hour;
  $value = $value << 8;
  $value += $minute;
  return $value;
}

function changesSince($lastStatusId)
{
  $actId = updateLastLogId ();
  if ($actId > $lastStatusId)
    return true;
  return false;
}

function waitForIdle()
{
  $rememberedId = updateLastLogId ();
  
  while ( 1 )
  {
    sleepMS ( 500 );
    $next = updateLastLogId ();
    if ($next == $rememberedId) return;
    $rememberedId = $next;
  }
}

function flushIt()
{
  for($i = 0; $i < 40; $i ++)
  {
    echo "          
    		                                                                              ";
    ob_end_flush ();
    ob_flush ();
    flush ();
  }
}

function updateLastLogId()
{
  global $lastLogId;
  
  $erg = QUERY ( "select max(id) from udpCommandLog" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $lastLogId = $row [0];
  if ($lastLogId < 1) $lastLogId = 0;
  return $lastLogId;
}

function sleepMS($ms)
{
  usleep ( $ms * 1000 );
}

function trace($text, $output=0)
{
  global $PHP_SELF;
  
  $text = query_real_escape_string ( $text );
  $time = time ();
  $script = $PHP_SELF;
  QUERY ( "INSERT into trace (time,message,script) values('$time', '$text','$script')" );
  
  if ($output==1) echo $text."\n";
}

function getBitMask($name, $value, $names)
{
  global $bitScriptDone;
  
  $result = '<table cellspacing="0" cellpadding="0" border="0" style="display:inline-block">
 <tr>
 <td><a href="#" onclick="toggle(\'' . $name . '\',7);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img7" title="' . $names [7] . '"></a></td>
 <td><a href="#" onclick="toggle(\'' . $name . '\',6);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img6" title="' . $names [6] . '"></a></td>
 <td><a href="#" onclick="toggle(\'' . $name . '\',5);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img5" title="' . $names [5] . '"></a></td>
 <td><a href="#" onclick="toggle(\'' . $name . '\',4);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img4" title="' . $names [4] . '"></a></td>
 <td><a href="#" onclick="toggle(\'' . $name . '\',3);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img3" title="' . $names [3] . '"></a></td>
 <td><a href="#" onclick="toggle(\'' . $name . '\',2);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img2" title="' . $names [2] . '"></a></td>
 <td><a href="#" onclick="toggle(\'' . $name . '\',1);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img1" title="' . $names [1] . '"></a></td>
 <td><a href="#" onclick="toggle(\'' . $name . '\',0);return false;"><img src="img/bitOff.gif" border="0" id="' . $name . 'img0" title="' . $names [0] . '"></a></td>
 <td> &nbsp;&nbsp;<input type=text size=2 name="' . $name . '" id="' . $name . '" value="' . $value . '"  maxlength=3 onkeyup="updateBits(\'' . $name . '\')"></td>
</tr>
</table>

<script>
  function toggle(name, id)
  {
     if ((document.getElementById(name).value&Math.pow(2,id))==Math.pow(2,id))
    	 document.getElementById(name).value=Number(document.getElementById(name).value)-Math.pow(2,id);
     else
    	 document.getElementById(name).value=Number(document.getElementById(name).value)+Math.pow(2,id);
     updateBits(name);
  }

  function updateBits(name)
  {
	 for(i=0;i<8;i++)
	 {
		 if ((document.getElementById(name).value&Math.pow(2,i))==Math.pow(2,i))
			 document.getElementById(name+"img"+i).src="img/bitOn.gif";
		 else
			 document.getElementById(name+"img"+i).src="img/bitOff.gif";
	 }
  }

updateBits("' . $name . '");
</script>';
  
  return $result;
}

function getWeekTime($name, $value)
{
  $times = parseWeekTime ( $value );
  
  $type = "Wochentag: <select name='" . $name . "Day'>";
  $type .= getSelect ( $times->day, "7,0,1,2,3,4,5,6", "Immer,Mo.,Di.,Mi.,Do.,Fr.,Sa.,So." );
  $type .= "</select> ";
  
  $options = "";
  for($hour = 0; $hour < 24; $hour ++)
  {
    $myHour = $hour;
    if (strlen ( $myHour ) == 1) $myHour = "0" . $myHour;
    if ($times->hour == $hour) $selected = "selected";
    else $selected = "";
    $options .= "<option $selected value='$hour'>$myHour";
  }
  
  if ($times->hour == 31) $selected = "selected";
  else $selected = "";
  $type .= " &nbsp;&nbsp; Stunde: <select name='" . $name . "Hour'><option $selected value='31'>Immer$options</select> ";
  
  $options = "";
  for($minute = 0; $minute < 60; $minute ++)
  {
    $myMinute = $minute;
    if (strlen ( $myMinute ) == 1) $myMinute = "0" . $myMinute;
    if ($times->minute == $minute) $selected = "selected";
    else $selected = "";
    $options .= "<option $selected value='$minute'>$myMinute";
  }
  
  if ($times->minute == 255) $selected = "selected";
  else $selected = "";
  $type .= " &nbsp;&nbsp; Minute: <select name='" . $name . "Minute'><option $selected value='255'>Immer$options</select> ";
  return $type;
}

function twoDigits($number)
{
  if (strlen ( $number ) == 1) $number = "0" . $number;
  return $number;
}

function generateAndCheckRules()
{
  ob_end_flush ();
  ob_start ();
  
  // echo "Regeln werden generiert und geprüft.<br><br>";
  global $dimmerClassesId, $rolloClassesId, $ledClassesId, $schalterClassesId, $irClassesId, $tasterClassesId, $logicalButtonClassesId, $ethernetClassesId, $pcServerClassesId;
  global $startFunctionId, $stopFunctionId, $moveToPositionFunctionId, $paramToOpen, $paramToClose, $paramToToggle, $paramPosition;
  global $functionTemplates;
  global $ledStatusBrightness;
  global $ledLogicalButtonBrightness;
  global $serverInstances;
  
  $scriptStart = microtime ( TRUE );
  
  $dimmerClassesId = getClassesIdByName ( "Dimmer" );
  $rolloClassesId = getClassesIdByName ( "Rollladen" );
  $ledClassesId = getClassesIdByName ( "Led" );
  $schalterClassesId = getClassesIdByName ( "Schalter" );
  $irClassesId = getClassesIdByName ( "IR-Sensor" );
  $tasterClassesId = getClassesIdByName ( "Taster" );
  $ethernetClassesId = getClassesIdByName ( "Ethernet" );
  $logicalButtonClassesId = getClassesIdByName ( "LogicalButton" );
  $pcServerClassesId = getClassesIdByName ( "PC-Server" );
  
  $startFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "start" );
  $stopFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "stop" );
  $moveToPositionFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "moveToPosition" );
  $paramToOpen = getFunctionParamEnumValueForClassesIdByName ( $rolloClassesId, "start", "direction", "TO_OPEN" );
  $paramToClose = getFunctionParamEnumValueForClassesIdByName ( $rolloClassesId, "start", "direction", "TO_CLOSE" );
  $paramToToggle = getFunctionParamEnumValueForClassesIdByName ( $rolloClassesId, "start", "direction", "TOGGLE" );
  $paramPosition = getClassesIdFunctionParamIdByName ( $rolloClassesId, "moveToPosition", "position" );
  
  $start = microtime ( TRUE );
  
  $erg = QUERY ( "select `signal`,classesId,function,name from functionTemplates" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $functionTemplates [$obj->classesId . "-" . $obj->function . "-" . $obj->name] = $obj->signal;
  }

  $ledStatusBrightness = 100;
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'ledStatusBrightness' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $ledStatusBrightness = $row [0];
  
  $ledLogicalButtonBrightness = 50;
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'ledLogicalBrightness' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $ledLogicalButtonBrightness = $row [0];
    
    // Alte generierten Sachen löschen
  QUERY ( "DELETE from groupSyncHelper" );
  QUERY ( "DELETE from groupfeatures where generated='1'" );
  QUERY ( "DELETE from groups where generated='1'" );
  QUERY ( "DELETE from groupstates where generated='1'" );
  QUERY ( "DELETE from ruleactionparams where generated='1'" );
  QUERY ( "DELETE from ruleactions where generated='1'" );
  QUERY ( "DELETE from rules where generated='1'" );
  QUERY ( "DELETE from rulesignalparams where generated='1'" );
  QUERY ( "DELETE from rulesignals where generated='1'" );
  checkRemoveUnusedHeatingRules();

  // Referenzielle Integrität 
  checkDatabaseIntegrity();
  
  liveOut ( "- Aufräumen " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  // BaseRules erzeugen
  $start = microtime ( TRUE );
  $erg = QUERY ( "select distinct(groupId) from basicRules order by groupId" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $groupId = $row [0];
    generateBaseRulesForGroup ( $groupId );
  }
  liveOut ( "- Basisregeln generieren " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  // Synchronisationsevents für erstellte Gruppen
  $start = microtime ( TRUE );
  $erg = QUERY ( "SELECT groups.id, COUNT( groupFeatures.featureInstanceId ) AS myCount FROM groups JOIN groupFeatures ON ( groupFeatures.groupId = groups.id ) WHERE single =0 AND groups.generated =0 and groups.groupType='' GROUP BY groups.id" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    if ($row [1] > 1)
    {
      $erg2 = QUERY ( "select max(ledStatus) from basicRules where groupId='$row[0]'" );
      $row2 = MYSQLi_FETCH_ROW ( $erg2 );
      if ($row2 [0] > 1)
      {
        $manualGroup [$row [0]] = 1;
        
        if ($row2 [0] == 3) $completeGroupFeedback = 1;
        else if ($row2 [0] == 2) $completeGroupFeedback = 2;
        else $completeGroupFeedback = 0;
        
        generateSyncEvents ( $row [0], $completeGroupFeedback );
      }
    }
  }
  liveOut ( "- Synchronisationsevents für Gruppen erzeugen " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  // Multigruppen erzeugen
  $start = microtime ( TRUE );
  generateMultiGroups ();
  liveOut ( "- Multigruppen erzeugen " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  $erg = QUERY ( "select id from controller where size='999' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
  {
    $erg = QUERY ( "select id from featureInstances where controllerId='$row[0]'" );
    while ( $row = MYSQLi_FETCH_ROW ($erg)) $serverInstances [$row [0]] = 1;
  }
  
  // LED Feedback erzeugen
  $start = microtime ( TRUE );
  $erg = QUERY ( "select distinct(groupId) from basicRules order by groupId" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    generateLedFeedbackForGroup ( $row [0], $manualGroup [$row [0]] );
  }
  
  liveOut ( "- LED Feedback erzeugen " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  // Signalgruppen generieren
  $start = microtime ( TRUE );
  $erg = QUERY ( "select id from groups where groupType!='' and generated='0'" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    generateSignalGroup ( $row [0] );
  }
  
  liveOut ( "- Signalgruppen generieren " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  // Pr�b es Gruppen mit nur DummySignalen gibt
  $start = microtime ( TRUE );
  removeDummyGroups ();
  liveOut ( "- Dummysignale löschen " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  // liveOut("- Dummysignale l�en deaktiviert");
  
  // Pr�b es in Gruppen Signale gibt, die mit StartState ALLE und auch AN oder AUS vorkommen und dann ersetzen
  $start = microtime ( TRUE );
  mixStateAllSignals ();
  liveOut ( "- Regeln optimieren " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  // Subgruppen in die Vatergruppe mischen
  $start = microtime ( TRUE );
  mixSubGroups ();
  liveOut ( "- Subgruppen mischen " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  
  // Regeln pr�  $start = microtime ( TRUE );
  checkRuleConsistency ();
  
  // Controllerleichen ohne features l�en
  $erg = QUERY ( "select controller.id from controller left join featureInstances on (featureInstances.controllerId=controller.id) where featureInstances.id is null and bootloader=0 and online=0 limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) QUERY ( "delete from controller where id='$row[0]' limit 1" );

  // Referenzielle Integrit㲠 
  checkDatabaseIntegrity();

  
  liveOut ( "- Abschlussprüfung " . round ( microtime ( TRUE ) - $start, 2 ) . " Sekunden" );
  liveOut ( "- Gesamtdauer " . round ( microtime ( TRUE ) - $scriptStart, 2 ) . " Sekunden" );
}
function checkRuleConsistency()
{
  // Pr�b Aktoren in mehreren Gruppen durch das gleiche Signal getriggert werden
  /*
   * $erg = QUERY("SELECT group_concat(distinct rulesignalparams.featureFunctionParamsId order by rulesignalparams.featureFunctionParamsId) as con1, group_concat(distinct rulesignalparams.paramValue order by rulesignalparams.paramValue) as con2, rules.groupid as groupId, rulesb.groupid as groupbId, rulesignals.featureinstanceid as mySignal,signalsb.featureinstanceid,ruleactions.featureinstanceid,actionsb.featureinstanceid as myAction,rulesignals.featurefunctionid as myFunction,signalsb.featurefunctionid,rulesignalparams.featureFunctionParamsId,signalparamsb.featureFunctionParamsId,rulesignalparams.paramValue,signalparamsb.paramvalue,ruleactions.featurefunctionid,actionsb.featurefunctionid FROM (rules join groups on (groups.id=rules.groupId) JOIN rulesignals ON ( rulesignals.ruleid = rules.id ) JOIN ruleactions ON ( ruleactions.ruleid = rules.id ) left join rulesignalparams on (rulesignalparams.rulesignalid=rulesignals.id) ) JOIN ( rules AS rulesb join groups as groupsb on (groupsb.id=rulesb.groupId) JOIN rulesignals AS signalsb ON ( signalsb.ruleid = rulesb.id ) JOIN ruleactions AS actionsb ON ( actionsb.ruleid = rulesb.id ) left join rulesignalparams as signalparamsb on (signalparamsb.rulesignalid=signalsb.id) ) WHERE rulesignals.featureinstanceid = signalsb.featureinstanceid AND ruleactions.featureinstanceid = actionsb.featureinstanceid AND rules.id != rulesb.id AND rules.activationstateid =0 AND rulesb.activationstateid =0 AND rulesignals.featurefunctionid = signalsb.featurefunctionid and rulesignals.groupalias=0 and signalsb.groupalias=0 and ruleactions.featureFunctionId!=169 and groups.subOf=0 and groupsb.subOf=0 group by rulesignals.id having (rulesignalparams.featureFunctionParamsId is null or group_concat(distinct rulesignalparams.featureFunctionParamsId order by rulesignalparams.featureFunctionParamsId)=group_concat(distinct signalparamsb.featureFunctionParamsId order by signalparamsb.featureFunctionParamsId)) and (rulesignalparams.paramvalue is null or group_concat(distinct rulesignalparams.paramvalue)=group_concat(distinct signalparamsb.paramvalue))"); while ( $obj = MYSQLi_FETCH_object($erg) ) { if ($obj->mySignal < 0) continue; $key = $obj->myAction . "-" . $obj->mySignal . "-" . $obj->myFunction . "-" . $obj->con1 . "-" . $obj->con2; if ($dones[$key] == 1) continue; $dones[$key] = 1; echo "Warnung: Doppelansteuerung von Aktor " . formatInstance($obj->myAction) . " in <a href='editRules.php?groupId=$obj->groupId' target='_blank'>dieser Gruppe</a> [<a href='editBaseConfig.php?groupId=$obj->groupId' target='_blank'>Basisregeln</a>] sowie in <a href='editRules.php?groupId=$obj->groupbId' target='_blank'>dieser Gruppe</a> [<a href='editBaseConfig.php?groupId=$obj->groupbId' target='_blank'>Basisregeln</a>]<br>"; }
   */
  
  // Pr�b in einer Gruppe vom gleichen Signar sowohl covered als auch DoubleClicked enthalten sind
  $lastGroupId = - 1;
  $foundCovered = "";
  $foundDoubleClick = "";
  
  $tasterClassesId = getClassesIdByName ( "Taster" );
  $evCoveredFunctionId = getClassesIdFunctionsIdByName ( $tasterClassesId, "evCovered" );
  $evClickedFunctionId = getClassesIdFunctionsIdByName ( $tasterClassesId, "evClicked" );
  $evDoubleClickFunctionId = getClassesIdFunctionsIdByName ( $tasterClassesId, "evDoubleClick" );
  $debug = 0;
  
  $erg = QUERY ( "select rules.id,rules.groupId,ruleSignals.featureFunctionId,ruleSignals.featureInstanceId from rules join ruleSignals on (ruleSignals.ruleId=rules.id) join groups on (groups.id = rules.groupId) join groupFeatures on (groupFeatures.groupId = rules.groupId) where groupAlias='0' order by groupId" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    if ($obj->groupId != $lastGroupId && $lastGroupId != - 1)
    {
      if ($debug == 1)
        echo "<br>Gruppe: " . $obj->groupId . "<br>";
      foreach ( ( array ) $foundCovered as $coveredInstance => $dummy )
      {
        foreach ( ( array ) $foundDoubleClick as $doubleClickInstance => $dummy )
        {
          if ($coveredInstance == $doubleClickInstance)
          {
            $erg2 = QUERY ( "select ruleSignals.id from rules join ruleSignals on (ruleSignals.ruleId=rules.id) where groupId='$lastGroupId' and featureInstanceId='$doubleClickInstance' and featureFunctionId='$evCoveredFunctionId'" );
            while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
            {
              // echo $obj2->id." -- ".$lastGroupId." - ".$coveredInstance."<br>";
              QUERY ( "UPDATE ruleSignals set featureFunctionId='$evClickedFunctionId' where id='$obj2->id' limit 1" );
            }
          }
        }
      }
      unset ( $foundCovered );
      unset ( $foundDoubleClick );
    }
    
    $lastGroupId = $obj->groupId;
    
    if ($obj->featureFunctionId == $evCoveredFunctionId)
      $foundCovered [$obj->featureInstanceId] = 1;
    if ($obj->featureFunctionId == $evDoubleClickFunctionId)
      $foundDoubleClick [$obj->featureInstanceId] = 1;
  }
}

// Pr�b es in Gruppen Signale gibt, die mit StartState ALLE und auch AN oder AUS vorkommen und dann ersetzen
function mixStateAllSignals()
{
  $erg = QUERY ( "select id from groups where single!=1" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $groupId = $row [0];
    
    $erg2 = QUERY ( "select featureInstanceId,featureFunctionId,rules.id,groupAlias,ruleSignals.id as ruleSignalId from ruleSignals join rules on (rules.id = ruleSignals.ruleId) left join ruleSignalParams on (ruleSignalParams.ruleSignalId = ruleSignals.id) where activationStateId='0' and groupId='$groupId' and ruleSignals.featureFunctionId!='129'");
    while ( $row2 = MYSQLi_FETCH_ROW ( $erg2 ) )
    {
      $firstInstance = $row2 [0];
      $firstFunction = $row2 [1];
      $firstRuleId = $row2 [2];
      $firstGroupAlias = $row2 [3];
      $firstRuleSignalId = $row2 [4];
      
      $firstInsert = - 1;
      $secondInsert = - 1;
      $erg3 = QUERY ( "select rules.id from ruleSignals join rules on (rules.id = ruleSignals.ruleId) where activationStateId!='0' and groupId='$groupId' and ruleSignals.featureInstanceId='$firstInstance' and ruleSignals.featureFunctionId='$firstFunction' and ruleSignals.featureFunctionId!='129' limit 2");
      while ( $row3 = MYSQLi_FETCH_ROW ( $erg3 ) )
      {
        if ($firstInsert == - 1)
          $firstInsert = $row3 [0];
        else
          $secondInsert = $row3 [0];
      }
      
      if ($firstInsert != - 1 && $secondInsert != - 1)
      {
        // Signale l�en
        QUERY ( "DELETE from ruleSignals where id='$firstRuleSignalId' limit 1" );
        
        // Actions verschieben
        $erg3 = QUERY ( "select * from ruleActions where ruleId='$firstRuleId'" );
        while ( $obj = MYSQLi_FETCH_OBJECT ( $erg3 ) )
        {
          QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$secondInsert','$obj->featureInstanceId','$obj->featureFunctionId','1')" );
          $newActionId = query_insert_id ();
          
          $erg4 = QUERY ( "select * from ruleActionParams where ruleActionId='$obj->id' order by id" );
          while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg4 ) )
          {
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated)
              	                         values('$newActionId','$obj2->featureFunctionParamsId','$obj2->paramValue','1')" );
          }
        }
        QUERY ( "UPDATE ruleActions set ruleId='$firstInsert',generated='1' where ruleId='$firstRuleId'" );
        
        $erg3 = QUERY ( "select count(*) from ruleSignals where ruleId='$firstRuleId'" );
        $row3 = MYSQLi_FETCH_ROW ( $erg3 );
        if ($row3 [0] == 0)
          deleteRule ( $firstRuleId, 0 );
      }
    }
  }
}

// Subgruppen in die Vatergruppe mischen
function mixSubGroups()
{
  // Alle Subgroupen durchgehen, die einen Vater haben
  $erg = QUERY ( "select id,subOf from groups where subOf>0" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $groupId = $row [0];
    $newGroupId = $row [1]; // Deren Regeln mischen wir beim Vater rein
    
    $ergA = QUERY ( "select ruleSignals.id from ruleSignals join rules on (ruleSignals.ruleId=rules.id) where groupId='$groupId'" );
    while ( $rowA = MYSQLi_FETCH_ROW ( $ergA ) )
    {
      $signalId = $rowA [0];
      
      $erg2 = QUERY ( "select rules.*, activation.basics as startBasics, resulting.basics as resultingBasics from rules join ruleSignals on (ruleSignals.ruleId=rules.id) left join groupStates as activation on (activation.id = rules.activationStateId) left join groupStates as resulting on (resulting.id = rules.resultingStateId) where ruleSignals.id='$signalId' limit 1" );
      if ($obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ))
      {
        $myActivationStateId = "0";
        $myResultingStateId = "0";
        $erg4 = QUERY ( "select id,basics from groupStates where basics>0 and groupId='$newGroupId'" );
        while ( $row4 = MYSQLi_FETCH_ROW ( $erg4 ) )
        {
          if ($obj2->startBasics == $row4 [1])
            $myActivationStateId = $row4 [0];
          if ($obj2->resultingBasics == $row4 [1])
            $myResultingStateId = $row4 [0];
        }
        
        // if ($myActivationStateId=="0" || $myResultingStateId=="0") echo "Fehler in Subgruppe. States nicht supported <br>";
        
        $erg3 = QUERY ( "select * from ruleSignals where id='$signalId' limit 1" );
        if ($obj3 = MYSQLi_FETCH_OBJECT ( $erg3 ))
        {
          $signalId = $obj3->id;
          $signalFeatureInstanceId = $obj3->featureInstanceId;
          $signalFeatureFunctionId = $obj3->featureFunctionId;
          
          // Beim Taster aus evCovered evClicked machen, wenn verschiedene Classes angesteuert werden
          if ($convertCoveredEvent == 1 && $signalFeatureFunctionId == 43)
            $signalFeatureFunctionId = 2;
        } else
          die ( "Signal ID $signalId nicht gefunden" );
          
          // Pr�b es die passende Regel schon gibt
          // echo "select rules.id from rules join ruleSignals on (ruleSignals.ruleId=rules.id) where activationStateId='$myActivationStateId' and groupId='$newGroupId' and featureInstanceId='$signalFeatureInstanceId' and featureFunctionId='$signalFeatureFunctionId' limit 1 <br>";
        $erg3 = QUERY ( "select rules.id from rules join ruleSignals on (ruleSignals.ruleId=rules.id) where activationStateId='$myActivationStateId' and groupId='$newGroupId' and featureInstanceId='$signalFeatureInstanceId' and featureFunctionId='$signalFeatureFunctionId' limit 1" );
        if ($row3 = MYSQLi_FETCH_ROW ( $erg3 ))
        {
          $newRuleId = $row3 [0];
          // echo "gefunden $newRuleId <br>";
        } else
        {
          QUERY ( "INSERT into rules (groupId,activationStateId,resultingStateId,startDay,startHour,startMinute,endDay,endHour,endMinute,signalType,baseRule,generated,intraDay)
        	                     values('$newGroupId','$myActivationStateId','$myResultingStateId','$obj2->startDay','$obj2->startHour','$obj2->startMinute','$obj2->endDay','$obj2->endHour','$obj2->endMinute','$obj2->signalType','$obj2->baseRule','1','$obj2->intraDay')" );
          $newRuleId = query_insert_id ();
          
          // echo "neu $newRuleId <br>";
          
          // Signale eintragen
          QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$newRuleId','$signalFeatureInstanceId','$signalFeatureFunctionId','1')" );
          $newSignalId = query_insert_id ();
          
          $erg4 = QUERY ( "select * from ruleSignalParams where ruleSignalId='$signalId' order by id" );
          while ( $obj4 = MYSQLi_FETCH_OBJECT ( $erg4 ) )
          {
            QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$newSignalId','$obj4->featureFunctionParamsId','$obj4->paramValue','1')" );
          }
        }
        
        // Actions eintragen, wenns sie nicht schon gibt
        $erg3 = QUERY ( "select * from ruleActions where ruleId='$obj2->id' order by id" );
        while ( $obj3 = MYSQLi_FETCH_OBJECT ( $erg3 ) )
        {
          // if ($obj2->offRule==1) $myOffInstances[$obj3->featureInstanceId]=1;
          $erg4 = QUERY ( "select id from ruleActions where ruleId='$newRuleId' and featureInstanceId='$obj3->featureInstanceId' and featureFunctionId='$obj3->featureFunctionId' limit 1" );
          if ($row4 = MYSQLi_FETCH_ROW ( $erg4 ))
          {
          } else
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$newRuleId','$obj3->featureInstanceId','$obj3->featureFunctionId','1')" );
            $newSignalId = query_insert_id ();
            
            $erg4 = QUERY ( "select * from ruleActionParams where ruleActionId='$obj3->id' order by id" );
            while ( $obj4 = MYSQLi_FETCH_OBJECT ( $erg4 ) )
            {
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newSignalId','$obj4->featureFunctionParamsId','$obj4->paramValue','1')" );
            }
          }
        }
      }
    }
  }
}

// Pr�b es Gruppen mit nur DummySignalen gibt
function removeDummyGroups()
{
  // 46 19 dimmer evOn,evOff
  // 106 107 led evOn, evOff
  // 56,175 rollo evClosed, evOpen
  // 62, 63 schalter evOn, evOff
  $lastGroupId = - 1;
  $lastGroupEmpty = 1;
  $deleteRules = "";
  $ledClassesId = getClassesIdByName ( "Led" );
  $debug = 0;
  
  $erg = QUERY ( "select activationStateId, resultingStateId, rules.id, rules.groupId,ruleSignals.featureFunctionId,groupFeatures.featureInstanceId,ruleactions.featureInstanceId as actionInstanceId from rules join ruleSignals on (ruleSignals.ruleId=rules.id) join groups on (groups.id = rules.groupId) join groupFeatures on (groupFeatures.groupId = rules.groupId) left join ruleactions on(ruleactions.ruleId=rules.id) where groupAlias='0' and single=1 order by groupId" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    if (getClassesIdByFeatureInstanceId ( $obj->featureInstanceId ) == $ledClassesId)
      continue;
    
    if ($obj->groupId != $lastGroupId)
    {
      if ($debug == 1)
        echo "<br>".time().": Gruppe: " . $obj->groupId . "<br>";
      if ($lastGroupId != - 1)
      {
        if ($lastGroupEmpty == 1)
        {
          // echo $lastGroupId."<br>";
          // Gruppe: 12340
          // 435: 556829,556830
          // 12340: 555950
          
          if ($deleteRules != "")
          {
            if ($debug == 1)
              echo $lastGroupId . ": " . $deleteRules . "<br>";
            $ids = explode ( ",", $deleteRules );
            foreach ( $ids as $actId )
            {
              deleteRule ( $actId ,0 );
            }
          }
        }
        $lastGroupEmpty = 1;
        $deleteRules = "";
      }
    }
    
    $lastGroupId = $obj->groupId;
    
    if ($obj->actionInstanceId > 0) // && $obj->activationStateId!=0)
    {
      if ($debug == 1) echo "A, ";
      $lastGroupEmpty = 0;
    } else if ($obj->actionInstanceId == null || $obj->featureFunctionId == 46 || $obj->featureFunctionId == 19 || $obj->featureFunctionId == 106 || $obj->featureFunctionId == 107 || $obj->featureFunctionId == 56 || $obj->featureFunctionId == 175 || $obj->featureFunctionId == 62 || $obj->featureFunctionId == 63)
    {
      if ($debug == 1)
        echo "D" . $obj->id . ", ";
      if ($deleteRules != "")
        $deleteRules .= ",";
      $deleteRules .= $obj->id;
    } else
    {
      if ($debug == 1)
        echo "B, ";
    }
  }
}

function generateBaseRulesForGroup($groupId)
{
  // echo "Generiere Basisregeln für Gruppe $groupId <br>";
  global $CONTROLLER_CLASSES_ID;
  global $signalParamWildcard,$signalParamWildcardWord;
  global $dimmerClassesId, $rolloClassesId, $ledClassesId, $schalterClassesId, $irClassesId, $tasterClassesId, $logicalButtonClassesId, $ethernetClassesId;
  global $startFunctionId, $stopFunctionId, $moveToPositionFunctionId, $paramToOpen, $paramToClose, $paramToToggle, $paramPosition;
  global $functionTemplates;
  global $ledLogicalButtonBrightness;
  global $configCache;
  
  unset ( $configCache );
  
  $myInstanceCount = 0;
  $doSkipSyncEventsForHeating=false;
  
  unset ( $diffFeatureClasses );
  $erg = QUERY ( "select featureClassesId,featureInstanceId,objectId from groupFeatures join featureInstances on (featureInstances.id=featureInstanceId) where groupId='$groupId'" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $myClassesId = $row [0];
    $diffFeatureClasses [$myClassesId] = 1;
    $myInstanceId = $row [1];
    $myInstances [$myInstanceCount] ["myClassesId"] = $myClassesId;
    $myInstances [$myInstanceCount] ["myInstanceId"] = $myInstanceId;
    $myObjectIds [$myInstanceId] = $row [2];
    $myInstanceCount ++;
  }
  
  // Wenn wir verschiedene Aktortypen in einer Gruppe haben, werden die Schalterfunktionen als kleinster gemeinsamer Nenner angeboten
  if (count ( $diffFeatureClasses ) > 1) $isMixedGroup = 1;
  
  // echo "Generiere Basisregeln für Gruppe $groupId $myInstanceCount <br>";
 
  // Bewegungsmeldersonderfunktion
  $erg = QUERY ( "select id from basicRules where groupId='$groupId' and extras='Bewegungsmelder' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $hasBewegung = TRUE;
  else $hasBewegung = FALSE;
    
    // Standardstates ggf. anlegen
  $erg = QUERY ( "select id from groupStates where groupId='$groupId' and basics='1' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $firstState = $row [0];
  else
  {
    $basicStateNames = getBasicStateNames ( $myClassesId );
    $offName = $basicStateNames->offName;
    $onName = $basicStateNames->onName;
    QUERY ( "INSERT into groupStates (groupId, name,basics,generated) values('$groupId','$offName','1','1')" );
    $firstState = query_insert_id ();
  }
  
  $erg = QUERY ( "select id from groupStates where groupId='$groupId' and basics='2' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $secondState = $row [0];
  else
  {
    QUERY ( "INSERT into groupStates (groupId, name,basics,generated) values('$groupId','$onName','2','1')" );
    $secondState = query_insert_id ();
  }
  
  if ($hasBewegung)
  {
    $erg = QUERY ( "select id from groupStates where groupId='$groupId' and basics='5' limit 1" );
    if ($row = MYSQLi_FETCH_ROW ( $erg )) $bewegungsState = $row [0];
    else
    {
      QUERY ( "INSERT into groupStates (groupId, name,basics,generated) values('$groupId','Bewegung','5','1')" );
      $bewegungsState = query_insert_id ();
    }
  }
  
  $foundStateChange = 0; // Wenn wir keine Statechanges haben, brauchen wir später auch keine Synchronisationsevents
  $erg = QUERY ( "select * from basicRules where groupId='$groupId' and active='1'" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $erg2 = QUERY ( "select count(*) from basicRuleSignals where ruleId='$obj->id'" );
    $row = MYSQLi_FETCH_ROW ( $erg2 );
    if ($row [0] == 0) continue;
    
    if ($obj->extras == "Bewegungsmelder") $isBewegungsMelder = TRUE;
    else $isBewegungsMelder = FALSE;

    if ($obj->extras == "Heizungssteuerung")
    {
    	$doSkipSyncEventsForHeating = generateHeizungsSteuerung($groupId, $obj, $firstState, $secondState);
    	continue;
    }
      
      // Wenn in einer Gruppe LED Feedback Gruppenstatus angewählt wurde, dann will man dass beim Event groupUndefined die Gruppe als aus gilt und sonst als an
    if ($obj->ledStatus == 3) $completeGroupFeedback = 1;
    else if ($obj->ledStatus == 2) $completeGroupFeedback = 2;
    else $completeGroupFeedback = 0;
    
    if ($obj->fkt1 == "true" && ($myClassesId == $dimmerClassesId || $myClassesId == $ledClassesId))
    {
      echo "Repariere true Fehler in Gruppe $groupId <br>";
      QUERY ( "update basicRules set fkt1='-' where id ='$obj->id' limit 1" );
      $obj->fkt1 = "-";
    }
    
    if (((int)$obj->fkt1)>0 && $myClassesId == $rolloClassesId)
    {
      echo "Repariere Wertefehler in Gruppe $groupId <br>";
      QUERY ( "update basicRules set fkt1='-' where id ='$obj->id' limit 1" );
      $obj->fkt1 = "-";
    }
    
    // AN Regeln
    if (isFunctionActive ( $obj->fkt1 ))
    {
      $signalType = $functionTemplates [$myClassesId . "-1-" . $obj->template];
      if ($signalType == "") $signalType = $functionTemplates ["-1-1-" . $obj->template];
      if ($signalType == "") die ( "A: Templatekonfiguration fehlt für $myClassesId Fkt 1 Gruppe $groupId template $obj->template ! ID ".$obj->id );
      
      if ($signalType != "-")
      {
        $ruleSignalType = $signalType;
        if ($signalType == "hold") $ruleSignalType = "holdStart";
        
        $signalType2 = $functionTemplates [$myClassesId . "-2-" . $obj->template];
        if ($signalType2 == "") $signalType2 = $functionTemplates ["-1-2-" . $obj->template];
        if ($signalType2 == "") die ( "B: Templatekonfiguration fehlt für $myClassesId Fkt 2 Gruppe $groupId!" );
        
        if ($isBewegungsMelder)
        {
          $startState = $firstState;
          $endState = $bewegungsState;
          $foundStateChange = 1;
        }         
        // Wenn die Signale für AN und AUS gleich sind, brauchen wir die States
        else if (($signalType == $signalType2 && isFunctionActive ( $obj->fkt2 )) || $hasBewegung)
        {
          $startState = $firstState;
          $endState = $secondState;
          $foundStateChange = 1;
        } else
        {
          $startState = "0";
          $endState = "0";
        }
        
        for($u = 0; $u < 2; $u ++)
        {
          if (!$hasBewegung) $u = 1; // || $isBewegungsMelder)
          else if ($u == 1) $startState = $bewegungsState;
            
            // Regel anlegen
          QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay, groupLock) 
                            values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$startState','$endState','$ruleSignalType','1','1','$obj->extras','$obj->intraDay','$hasBewegung')" );
          $ruleId = query_insert_id ();
          
          // Actions ergäzen
          foreach ( $myInstances as $arr )
          {
            $myClassesId = $arr ["myClassesId"];
            $myInstanceId = $arr ["myInstanceId"];
            
            if ($myClassesId == $dimmerClassesId)
            {
              if ($isMixedGroup == 1)
              {
                $obj->fkt1DauerOrig = $obj->fkt1Dauer;
                $obj->fkt1Orig = $obj->fkt1;
                
                $obj->fkt1Dauer = $obj->fkt1;
                $obj->fkt1 = "100";
              }
              
              QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','25','1')" );
              $newRuleActionId = query_insert_id ();
              
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','90','$obj->fkt1','1')" );
              
              $dauer = "0";
              if ($obj->fkt1Dauer > 0)
                $dauer = $obj->fkt1Dauer;
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','91','$dauer','1')" );
              
              if ($isMixedGroup == 1)
              {
                $obj->fkt1Dauer = $obj->fkt1DauerOrig;
                $obj->fkt1 = $obj->fkt1Orig;
              }
            } 
            else if ($myClassesId == $rolloClassesId)
            {
              QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$startFunctionId','1')" );
              $newRuleActionId = query_insert_id ();
              
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','142','$paramToOpen','1')" );
            } 
            else if ($myClassesId == $ledClassesId)
            {
              QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','101','1')" );
              $newRuleActionId = query_insert_id ();
              
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','165','$obj->fkt1','1')" );
              
              $dauer = "0";
              if ($obj->fkt1Dauer > 0) $dauer = readDauerWithTimebase ( $myObjectIds [$myInstanceId], $obj->fkt1Dauer );
              
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','166','$dauer','1')" );
            } 
            else if ($myClassesId == $logicalButtonClassesId)
            {
              QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','177','1')" );
              $newRuleActionId = query_insert_id ();
              
              if ($obj->fkt1 == "C" || $obj->fkt1 == "true") $brightness = $ledLogicalButtonBrightness;
              else $brightness = $obj->fkt1;
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','359','$brightness','1')" );
              
              $dauer = "0";
              if ($obj->fkt1Dauer > 0) $dauer = $obj->fkt1Dauer;
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','394','$dauer','1')" );
            } 
            else if ($myClassesId == $schalterClassesId)
            {
              QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','60','1')" );
              $newRuleActionId = query_insert_id ();
              
              $dauer = "0";
              if ($obj->fkt1 > 0) $dauer = readDauerWithTimebase ( $myObjectIds [$myInstanceId], $obj->fkt1 );
              
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','265','$dauer','1')" );
            } 
            else if ($myClassesId == $tasterClassesId)
            {
              QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','188','1')" );
              $newRuleActionId = query_insert_id ();
              QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','372','0','1')" ); // enable events off
            } else die ( "nicht implementierte class $myClassesId -3" );
          }
          
          // Signale ergänzen
          $erg2 = QUERY ( "select basicRuleSignals.id, featureInstanceId, controllerId, featureClassesId, featureInstances.id as checkId from basicRuleSignals left join featureInstances on (featureInstances.id=basicRuleSignals.featureInstanceId) where ruleId='$obj->id' order by basicRuleSignals.id" );
          while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
          {
            
            if ($obj2->featureInstanceId < 0) // Signalgruppe
            {
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
              values('$ruleId','$obj2->featureInstanceId','0','1')" );
              continue;
            }
            
            if ($obj2->checkId == null) continue;
            
            $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
            
            // echo $signalClassesId." - ".$CONTROLLER_CLASSES_ID."<br>";
            
            // EV-Time vom Controller (Zeitsteuerung)
            if ($signalClassesId == $CONTROLLER_CLASSES_ID)
            {
              // QUERY("UPDATE rules set activationStateId='0' where id='$ruleId' limit 1");
              
              $erg3 = QUERY ( "select id from featureInstances where controllerId='$obj2->controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1" );
              if ($row = MYSQLi_FETCH_ROW ( $erg3 )) $controllerInstanceId = $row [0];
              else die ( "ControllerInstanz zu controllerId $obj2->controllerId nicht gefunden" );
              
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' limit 1" );
              if ($row = MYSQLi_FETCH_ROW ( $erg3 )) $timeParamValue = $row [0];
              else showRuleError ( "Regel ohne g�n Parameterwert gefunden. RegelID = $obj2->id", $groupId );
              
              if ($timeParamValue == - 1) // evDay
              {
                $evDayFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evDay" );
                QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                        values('$ruleId','$controllerInstanceId','$evDayFunctionId','1')" );
              } else if ($timeParamValue == - 2) // evNight
              {
                $evNightFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evNight" );
                QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                        values('$ruleId','$controllerInstanceId','$evNightFunctionId','1')" );
              } else
              {
                $evTimeFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evTime" );
                QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                        values('$ruleId','$controllerInstanceId','$evTimeFunctionId','1')" );
                $ruleSignalId = query_insert_id ();
                
                $timeParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evTime", "weekTime" );
                QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$timeParamId','$timeParamValue','1')" );
              }
              
              continue;
            }
            
            if ($signalType == "click" || ($signalType == "covered" && $signalClassesId == $irClassesId))
            {
              $evClickedFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evClicked" );
              
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                      values('$ruleId','$obj2->featureInstanceId','$evClickedFunctionId','1','$completeGroupFeedback')" );
              $ruleSignalId = query_insert_id ();
              
              if ($signalClassesId == $irClassesId)
              {
                $param1Value = "";
                $i = 0;
                $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
                while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
                {
                  if ($param1Value == "")
                    $param1Value = $row [0];
                  else
                    $param2Value = $row [0];
                }
                
                if ($param1Value == "") die ( "Params zu ruleSignalId $obj2->id nicht gefunden -1" );
                
                $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "address" );
                QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
                $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "command" );
                QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
              }
            } else if ($signalType == "hold")
            {
              $evHoldStartFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evHoldStart" );
              
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evHoldStartFunctionId','1','$completeGroupFeedback')" );
              $ruleSignalId = query_insert_id ();
              
              if ($signalClassesId == $irClassesId)
              {
                $param1Value = "";
                $i = 0;
                $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
                while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
                {
                  if ($param1Value == "") $param1Value = $row [0];
                  else $param2Value = $row [0];
                }
                if ($param1Value == "") die ( "Params zu ruleSignalId $obj2->id nicht gefunden -2" );
                
                $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "address" );
                QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
                $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "command" );
                QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
              }
            } else if ($signalType == "doubleClick")
            {
              $evDoubleClickFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evDoubleClick" );
              
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                        values('$ruleId','$obj2->featureInstanceId','$evDoubleClickFunctionId','1','$completeGroupFeedback')" );
            } else if ($signalType == "covered")
            {
              $evCoveredFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evCovered" );
              
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                        values('$ruleId','$obj2->featureInstanceId','$evCoveredFunctionId','1','$completeGroupFeedback')" );
            } else if ($signalType == "free")
            {
              $evFreeFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evFree" );
              
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                        values('$ruleId','$obj2->featureInstanceId','$evFreeFunctionId','1','$completeGroupFeedback')" );
            }
          }
        }
      }
    }
    
    // AUS Regeln
    if (isFunctionActive ( $obj->fkt2 ))
    {
      $signalType = $functionTemplates [$myClassesId . "-2-" . $obj->template];
      if ($signalType == "") $signalType = $functionTemplates ["-1-2-" . $obj->template];
      if ($signalType == "") die ( "C: Templatekonfiguration fehlt f�ss $myClassesId Fkt 2 Gruppe $groupId!" );
      
      if ($signalType != "-")
      {
        $ruleSignalType = $signalType;
        if ($signalType == "hold") $ruleSignalType = "holdStart";
        
        $signalType2 = $functionTemplates [$myClassesId . "-1-" . $obj->template];
        if ($signalType2 == "") $signalType2 = $functionTemplates ["-1-1-" . $obj->template];
        if ($signalType2 == "") die ( "D: Templatekonfiguration fehlt f�ss $myClassesId Fkt 1 Gruppe $groupId!" );
        
        if ($isBewegungsMelder)
        {
          $startState = $bewegungsState;
          $endState = $firstState;
          $foundStateChange = 1;
        }         
        // Wenn die Signale f�und AUS gleich sind, brauchen wir die States
        else if (($signalType == $signalType2 && isFunctionActive ( $obj->fkt1 )) || $hasBewegung)
        {
          $startState = $secondState;
          $endState = $firstState;
          $foundStateChange = 1;
        } else
        {
          $startState = "0";
          $endState = "0";
        }
        
        // Regel anlegen
        QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,offRule,extras,intraDay,groupLock) 
                            values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$startState','$endState','$ruleSignalType','1','1','$offRule','$obj->extras','$obj->intraDay','$hasBewegung')" );
        $ruleId = query_insert_id ();
        
        // Actions ergänzen
        foreach ( ( array ) $myInstances as $arr )
        {
          $myClassesId = $arr ["myClassesId"];
          $myInstanceId = $arr ["myInstanceId"];
          
          if ($myClassesId == $dimmerClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','25','1')" );
            $newRuleActionId = query_insert_id ();
            
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','90','0','1')" );
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','91','0','1')" );
          } else if ($myClassesId == $rolloClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$startFunctionId','1')" );
            $newRuleActionId = query_insert_id ();
            
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','142','$paramToClose','1')" );
          } else if ($myClassesId == $ledClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','135','1')" );
          } else if ($myClassesId == $logicalButtonClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','177','1')" );
            $newRuleActionId = query_insert_id ();
            
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','359','0','1')" );
          } else if ($myClassesId == $schalterClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','61','1')" );
          } else if ($myClassesId == $tasterClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','188','1')" );
            $newRuleActionId = query_insert_id ();
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','372','1','1')" ); // enable events on
          } else
            die ( "nicht implementierte class $myClassesId -4" );
        }
        
        // Signale ergänzen
        $erg2 = QUERY ( "select basicRuleSignals.id, featureInstances.controllerId, featureInstanceId, featureClassesId, featureInstances.id as checkId from basicRuleSignals left join featureInstances on (featureInstances.id=basicRuleSignals.featureInstanceId) where ruleId='$obj->id' order by basicRuleSignals.id" );
        while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
        {
          if ($obj2->featureInstanceId < 0) // Signalgruppe
          {
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
            values('$ruleId','$obj2->featureInstanceId','0','1')" );
            continue;
          }
          
          if ($obj2->checkId == null) continue;
          
          $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
            
          // EV-Time vom Controller (Zeitsteuerung)
          if ($signalClassesId == $CONTROLLER_CLASSES_ID)
          {
            // QUERY("UPDATE rules set activationStateId='0' where id='$ruleId' limit 1");
            
            $erg3 = QUERY ( "select id from featureInstances where controllerId='$obj2->controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1" );
            if ($row = MYSQLi_FETCH_ROW ( $erg3 )) $controllerInstanceId = $row [0];
            else die ( "ControllerInstanz zu controllerId $obj2->controllerId nicht gefunden" );
            
            $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' limit 1" );
            if ($row = MYSQLi_FETCH_ROW ( $erg3 )) $timeParamValue = $row [0];
            else showRuleError ( "Regel ohne g�n Parameterwert gefunden. RegelID = $obj2->id", $groupId );
            
            if ($timeParamValue == - 1) // evDay
            {
              $evDayFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evDay" );
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                        values('$ruleId','$controllerInstanceId','$evDayFunctionId','1')" );
            } else if ($timeParamValue == - 2) // evNight
            {
              $evNightFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evNight" );
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                        values('$ruleId','$controllerInstanceId','$evNightFunctionId','1')" );
            } else
            {
              $evTimeFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evTime" );
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                        values('$ruleId','$controllerInstanceId','$evTimeFunctionId','1')" );
              $ruleSignalId = query_insert_id ();
              
              $timeParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evTime", "weekTime" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$timeParamId','$timeParamValue','1')" );
            }
            
            continue;
          }
          
          if ($signalType == "click" || ($signalType == "covered" && $signalClassesId == $irClassesId))
          {
            $evClickedFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evClicked" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                      values('$ruleId','$obj2->featureInstanceId','$evClickedFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "")
                  $param1Value = $row [0];
                else
                  $param2Value = $row [0];
              }
              if ($param1Value == "")
                die ( "Params zu ruleSignalId $obj2->id nicht gefunden -3" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          } else if ($signalType == "hold")
          {
            $evHoldStartFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evHoldStart" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback	)
                                       values('$ruleId','$obj2->featureInstanceId','$evHoldStartFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
           
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "")
                  $param1Value = $row [0];
                else
                  $param2Value = $row [0];
              }
              if ($param1Value == "")
                die ( "Params zu ruleSignalId $obj2->id nicht gefunden -4" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                  values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                  values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          } else if ($signalType == "doubleClick")
          {
            $evDoubleClickFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evDoubleClick" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evDoubleClickFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "covered")
          {
            $evCoveredFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evCovered" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evCoveredFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "free")
          {
            $evFreeFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evFree" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                        values('$ruleId','$obj2->featureInstanceId','$evFreeFunctionId','1','$completeGroupFeedback')" );
          }
        }
      }
    }
    
    // DIMM // STOP'N'GO Regeln
    if (isFunctionActive ( $obj->fkt3 ))
    {
      $signalType = $functionTemplates [$myClassesId . "-3-" . $obj->template];
      if ($signalType == "")
        $signalType = $functionTemplates ["-1-3-" . $obj->template];
      if ($signalType == "")
        die ( "E: Templatekonfiguration fehlt f�ss $myClassesId Fkt 3 Gruppe $groupId!" );
      
      if ($signalType != "-")
      {
        $ruleSignalType = $signalType;
        if ($signalType == "hold")
          $ruleSignalType = "holdStart";
        
        $startState = 0;
        $endState = 0;
        
        if ($isBewegungsMelder)
        {
          $startState = $firstState;
          $endState = $bewegungsState;
        } else if ($hasBewegung)
          $endState = $secondState;
          
          // Regel anlegen f�mmen / toogle
        QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay, groupLock) 
                            values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$startState','$endState','$ruleSignalType','1','1','$obj->extras','$obj->intraDay','$hasBewegung')" );
        $ruleId = query_insert_id ();
        
        // Actions ergänzen
        foreach ( $myInstances as $arr )
        {
          $myClassesId = $arr ["myClassesId"];
          $myInstanceId = $arr ["myInstanceId"];
          
          if ($myClassesId == $dimmerClassesId)
          {
            // TOGGLE
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','64','1')" );
            $newRuleActionId = query_insert_id ();
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','115','0','1')" );
          } else if ($myClassesId == $rolloClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$startFunctionId','1')" );
            $newRuleActionId = query_insert_id ();
            
            // TOGGLE
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','142','0','1')" );
          } else
            die ( "nicht implementierte class $myClassesId -5" );
        }
        
        // Signale ergänzen
        $erg2 = QUERY ( "select basicRuleSignals.id, featureInstanceId, featureClassesId, featureInstances.id as checkId from basicRuleSignals left  join featureInstances on (featureInstances.id=basicRuleSignals.featureInstanceId) where ruleId='$obj->id' order by basicRuleSignals.id" );
        while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
        {
          if ($obj2->featureInstanceId < 0) // Signalgruppe
          {
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
            values('$ruleId','$obj2->featureInstanceId','0','1')" );
            continue;
          }
          
          if ($obj2->checkId == null) continue;
          
          $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
          
          if ($signalType == "click" || ($signalType == "covered" && $signalClassesId == $irClassesId))
          {
            $evClickedFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evClicked" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                      values('$ruleId','$obj2->featureInstanceId','$evClickedFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "") $param1Value = $row [0];
                else $param2Value = $row [0];
              }
              
              if ($param1Value == "") die ( "Params zu ruleSignalId $obj2->id nicht gefunden -5" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          } else if ($signalType == "hold")
          {
            $evHoldStartFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evHoldStart" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evHoldStartFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "") $param1Value = $row [0];
                else $param2Value = $row [0];
              }
              
              if ($param1Value == "") die ( "Params zu ruleSignalId $obj2->id nicht gefunden -6" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          } else if ($signalType == "doubleClick")
          {
            $evDoubleClickFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evDoubleClick" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evDoubleClickFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "covered")
          {
            $evCoveredFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evCovered" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evCoveredFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "free")
          {
            $evFreeFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evFree" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                        values('$ruleId','$obj2->featureInstanceId','$evFreeFunctionId','1','$completeGroupFeedback')" );
          }
        }
        
        // Bei Hold können wir noch holdEnd verwenden
        if ($signalType == "hold")
        {
          $ruleSignalType = "holdEnd";
          
          $startState = 0;
          $endState = 0;
          
          if ($isBewegungsMelder)
          {
            $startState = $firstState;
            $endState = $bewegungsState;
          } else if ($hasBewegung)
            $endState = $secondState;
          
          QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay,groupLock) 
                               values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$startState','$endState','$ruleSignalType','1','1','$obj->extras','$obj->intraDay','$hasBewegung')" );
          $ruleIdHoldEnd = query_insert_id ();
          
          // Actions erg㭺en
          foreach ( $myInstances as $arr )
          {
            $myClassesId = $arr ["myClassesId"];
            $myInstanceId = $arr ["myInstanceId"];
            
            if ($myClassesId == $dimmerClassesId) QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleIdHoldEnd','$myInstanceId','65','1')" );
            else if ($myClassesId == $rolloClassesId) QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleIdHoldEnd','$myInstanceId','$stopFunctionId','1')" );
            else die ( "nicht implementierte class $myClassesId -7" );
          }
          
          // Signale ergänzen
          $erg2 = QUERY ( "select basicRuleSignals.id, featureInstanceId, featureClassesId, featureInstances.id as checkId from basicRuleSignals left  join featureInstances on (featureInstances.id=basicRuleSignals.featureInstanceId) where ruleId='$obj->id' order by basicRuleSignals.id" );
          while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
          {
            if ($obj2->featureInstanceId < 0) // Signalgruppe
            {
              QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
              values('$ruleId','$obj2->featureInstanceId','0','1')" );
              continue;
            }
            
            if ($obj2->checkId == null) continue;
            
            $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
            
            $evHoldEndFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evHoldEnd" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleIdHoldEnd','$obj2->featureInstanceId','$evHoldEndFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "")
                  $param1Value = $row [0];
                else
                  $param2Value = $row [0];
              }
              if ($param1Value == "")
                die ( "Params zu ruleSignalId $obj2->id nicht gefunden -9" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldEnd", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
         		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldEnd", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
         		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          }
        }
      }
    }
    
    // PRESET Regeln
    if (isFunctionActive ( $obj->fkt4 ))
    {
      $signalType = $functionTemplates [$myClassesId . "-4-" . $obj->template];
      if ($signalType == "")
        $signalType = $functionTemplates ["-1-4-" . $obj->template];
      if ($signalType == "")
        die ( "F: Templatekonfiguration fehlt f�ss $myClassesId Fkt 4 Gruppe $groupId!" );
      
      $ruleSignalType = $signalType;
      if ($signalType == "hold")
        $ruleSignalType = "holdStart";
      
      if ($signalType != "-")
      {
        $startState = 0;
        $endState = 0;
        
        if ($isBewegungsMelder)
        {
          $startState = $bewegungsState;
          $endState = $bewegungsState;
        } else if ($hasBewegung)
          $endState = $secondState;
          
          // Regel anlegen
        QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay,groupLock) 
                              values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$startState','$endState','$ruleSignalType','1','1','$obj->extras','$obj->intraDay','$hasBewegung')" );
        $ruleId = query_insert_id ();
        
        // Actions ergänzen
        foreach ( $myInstances as $arr )
        {
          $myClassesId = $arr ["myClassesId"];
          $myInstanceId = $arr ["myInstanceId"];
          
          if ($myClassesId == $dimmerClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','25','1')" );
            $newRuleActionId = query_insert_id ();
            
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','90','$obj->fkt4','1')" );
            
            $dauer = "0";
            if ($obj->fkt4Dauer > 0)
              $dauer = $obj->fkt4Dauer;
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','91','$dauer','1')" );
          } 
          else if ($myClassesId == $rolloClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
            $newRuleActionId = query_insert_id ();
            
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','$paramPosition','$obj->fkt4','1')" );
          } else if ($myClassesId == $ledClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','101','1')" );
            $newRuleActionId = query_insert_id ();
            
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','165','$obj->fkt4','1')" );
            
            $dauer = "0";
            if ($obj->fkt4Dauer > 0) $dauer = readDauerWithTimebase ( $myObjectIds [$myInstanceId], $obj->fkt4Dauer );
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','166','$dauer','1')" );
          } 
          else if ($myClassesId == $logicalButtonClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','137','1')" );
            $newRuleActionId = query_insert_id ();
            
            if ($obj->fkt4 == "C")
              $brightness = $ledLogicalButtonBrightness;
            else
              $brightness = $obj->fkt4;
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','243','$brightness','1')" );
            
            $dauer = "0";
            if ($obj->fkt4Dauer > 0)
              $dauer = $obj->fkt4Dauer;
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','244','$dauer','1')" );
          } else if ($myClassesId == $schalterClassesId)
          {
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','60','1')" );
            $newRuleActionId = query_insert_id ();
            
            $dauer = "0";
            if ($obj->fkt4 > 0) $dauer = readDauerWithTimebase ( $myObjectIds [$myInstanceId], $obj->fkt4 );
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','265','$dauer','1')" );
          } 
          else die ( "nicht implementierte class $myClassesId -8" );
        }
        
        // Signale ergänzen
        $erg2 = QUERY ( "select basicRuleSignals.id, featureInstanceId, featureClassesId, featureInstances.id as checkId from basicRuleSignals left  join featureInstances on (featureInstances.id=basicRuleSignals.featureInstanceId) where ruleId='$obj->id' order by basicRuleSignals.id" );
        while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
        {
          if ($obj2->featureInstanceId < 0) // Signalgruppe
          {
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
            values('$ruleId','$obj2->featureInstanceId','0','1')" );
            continue;
          }
          
          if ($obj2->checkId == null)
            continue;
          
          $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
          
          if ($signalType == "click" || ($signalType == "covered" && $signalClassesId == $irClassesId))
          {
            $evClickedFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evClicked" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                        values('$ruleId','$obj2->featureInstanceId','$evClickedFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "")
                  $param1Value = $row [0];
                else
                  $param2Value = $row [0];
              }
              if ($param1Value == "")
                die ( "Params zu ruleSignalId $obj2->id nicht gefunden -10" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
          		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
          		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          } else if ($signalType == "hold")
          {
            $evHoldStartFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evHoldStart" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                         values('$ruleId','$obj2->featureInstanceId','$evHoldStartFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "")
                  $param1Value = $row [0];
                else
                  $param2Value = $row [0];
              }
              if ($param1Value == "")
                die ( "Params zu ruleSignalId $obj2->id nicht gefunden -11" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
           		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldStart", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
           		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
            
            // Regel dimmStop
            /*
             * $ruleSignalType="holdEnd"; QUERY("INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule) values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','0','$secondState','$ruleSignalType','1')"); $ruleIdHoldEnd=query_insert_id(); $evHoldEndFunctionId = getClassesIdFunctionsIdByName($obj2->featureClassesId, "evHoldEnd"); QUERY("INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId) values('$ruleIdHoldEnd','$obj2->featureInstanceId','$evHoldEndFunctionId')"); $ruleSignalId = query_insert_id(); if ($signalClassesId==$irClassesId) { $param1Value=""; $i=0; $erg = QUERY("select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2"); while($row=MYSQLi_FETCH_ROW($erg)) { if ($param1Value=="") $param1Value=$row[0]; else $param2Value=$row[0]; } if ($param1Value=="") die("Params zu ruleSignalId $obj2->id nicht gefunden -12"); $irParamAddressId = getClassesIdFunctionParamIdByName($irClassesId,"evHoldEnd","address"); QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) values('$ruleSignalId','$irParamAddressId','$param1Value')"); $irParamCommandId = getClassesIdFunctionParamIdByName($irClassesId,"evHoldEnd","command"); QUERY("INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) values('$ruleSignalId','$irParamCommandId','$param2Value')"); } // Actions erg㭺en if ($myClassesId == $dimmerClassesId) { QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleIdHoldEnd','$myInstanceId','65')"); } else die("nicht implementierte class $myClassesId -9");
             */
          } else if ($signalType == "doubleClick")
          {
            $evDoubleClickFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evDoubleClick" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                         values('$ruleId','$obj2->featureInstanceId','$evDoubleClickFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "covered")
          {
            $evCoveredFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evCovered" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                         values('$ruleId','$obj2->featureInstanceId','$evCoveredFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "free")
          {
            $evFreeFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evFree" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                          values('$ruleId','$obj2->featureInstanceId','$evFreeFunctionId','1','$completeGroupFeedback')" );
          }
        }
      }
    }

    
    // Stop Regeln bei Rollos
    if ($myClassesId == $rolloClassesId)
    {
      $signalType = $functionTemplates [$myClassesId . "-5-" . $obj->template];
      if ($signalType == "") $signalType = $functionTemplates ["-1-5-" . $obj->template];
      if ($signalType == "") die ( "G: Templatekonfiguration fehlt f�ss $myClassesId Fkt 5 Gruppe $groupId!" );
      
      $ruleSignalType = $signalType;
      if ($signalType == "hold") $ruleSignalType = "holdEnd";
      
      if ($signalType != "-")
      {
        $startState = 0;
        $endState = 0;
        
        if ($isBewegungsMelder)
        {
          $startState = $bewegungsState;
          $endState = $bewegungsState;
        } else if ($hasBewegung)
          $endState = $secondState;
          
          // Regel anlegen
        QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay, groupLock) 
                            values('$groupId','$obj->startDay','$obj->startHour','$obj->startMinute','$obj->endDay','$obj->endHour','$obj->endMinute','$startState','$endState','$ruleSignalType','1','1','$obj->extras','$obj->intraDay','$hasBewegung')" );
        $ruleId = query_insert_id ();

        // Actions erg㭺en
        foreach ( $myInstances as $arr )
        {
          $myClassesId = $arr ["myClassesId"];
          $myInstanceId = $arr ["myInstanceId"];
          
          QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$stopFunctionId','1')" );
        }
        
        // Signale erg㭺en
        $erg2 = QUERY ( "select basicRuleSignals.id,featureInstanceId, featureClassesId, featureInstances.id as checkId from basicRuleSignals left  join featureInstances on (featureInstances.id=basicRuleSignals.featureInstanceId) where ruleId='$obj->id' order by basicRuleSignals.id" );
        while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
        {
          if ($obj2->featureInstanceId < 0) // Signalgruppe
          {
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
            values('$ruleId','$obj2->featureInstanceId','0','1')" );
            continue;
          }
          
          if ($obj2->checkId == null)
            continue;
          
          $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
          
          if ($signalType == "click" || ($signalType == "covered" && $signalClassesId == $irClassesId))
          {
            $evClickedFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evClicked" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                      values('$ruleId','$obj2->featureInstanceId','$evClickedFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "")
                  $param1Value = $row [0];
                else
                  $param2Value = $row [0];
              }
              if ($param1Value == "")
                die ( "Params zu ruleSignalId $obj2->id nicht gefunden -13" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evClicked", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          } else if ($signalType == "hold")
          {
            $evHoldEndFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evHoldEnd" );
            
            QUERY ( "INSERT  ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evHoldEndFunctionId','1','$completeGroupFeedback')" );
            $ruleSignalId = query_insert_id ();
            
            if ($signalClassesId == $irClassesId)
            {
              $param1Value = "";
              $i = 0;
              $erg3 = QUERY ( "select paramValue from basicRuleSignalParams where ruleSignalId='$obj2->id' order by id limit 2" );
              while ( $row = MYSQLi_FETCH_ROW ( $erg3 ) )
              {
                if ($param1Value == "")
                  $param1Value = $row [0];
                else
                  $param2Value = $row [0];
              }
              if ($param1Value == "")
                die ( "Params zu ruleSignalId $obj2->id nicht gefunden -14" );
              
              $irParamAddressId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldEnd", "address" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamAddressId','$param1Value','1')" );
              $irParamCommandId = getClassesIdFunctionParamIdByName ( $irClassesId, "evHoldEnd", "command" );
              QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        		                                 values('$ruleSignalId','$irParamCommandId','$param2Value','1')" );
            }
          } else if ($signalType == "doubleClick")
          {
            $evDoubleClickFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evDoubleClick" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evDoubleClickFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "covered")
          {
            $evCoveredFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evCovered" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                       values('$ruleId','$obj2->featureInstanceId','$evCoveredFunctionId','1','$completeGroupFeedback')" );
          } else if ($signalType == "free")
          {
            $evFreeFunctionId = getClassesIdFunctionsIdByName ( $obj2->featureClassesId, "evFree" );
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                        values('$ruleId','$obj2->featureInstanceId','$evFreeFunctionId','1','$completeGroupFeedback')" );
          }
        }
      }
    }
  }
  
  // Dummyregeln ergänzen
  if (!$doSkipSyncEventsForHeating && $myClassesId != $ethernetClassesId && $myClassesId != $logicalButtonClassesId && $myClassesId != $tasterClassesId && $myInstanceCount == 1 && $myClassesId != 24) // Bei Gruppen mir mehreren Aktoren generieren wir keine Statewechseldummies
  {
    // Dummy für evOn
    QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) 
                       values('$groupId','7','31','255','7','31','255','$firstState','$secondState','evOn','1','1','1')" );
    $ruleId = query_insert_id ();
    // QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$myInstanceId','-1')");
    
    if ($myClassesId == $dimmerClassesId)
    {
      $evOnFunctionId = getClassesIdFunctionsIdByName ( $dimmerClassesId, "evOn" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$evOnFunctionId','1')" );
      $signalId = query_insert_id ();
      
      $dimmerParamBrightnessId = getClassesIdFunctionParamIdByName ( $dimmerClassesId, "evOn", "brightness" );
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                     values('$signalId','$dimmerParamBrightnessId','255','1')" );
    } else if ($myClassesId == $rolloClassesId)
    {
      $statusFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "evOpen" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$statusFunctionId','1')" );
    } else if ($myClassesId == $schalterClassesId)
    {
      $evOnFunctionId = getClassesIdFunctionsIdByName ( $schalterClassesId, "evOn" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$evOnFunctionId','1')" );
      $signalId = query_insert_id ();
      
      $schalterParamDurationId = getClassesIdFunctionParamIdByName ( $schalterClassesId, "evOn", "duration" );
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                     values('$signalId','$schalterParamDurationId','$signalParamWildcardWord','1')" );                         
                                
    } else if ($myClassesId == $ledClassesId)
    {
      $evOnFunctionId = getClassesIdFunctionsIdByName ( $ledClassesId, "evOn" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$evOnFunctionId','1')" );
    } else
      die ( "nicht implementierte class $myClassesId -10" );
      
      // Dummy für off
    QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) 
                       values('$groupId','7','31','255','7','31','255','0','$firstState','evOff','1','1','1')" );
    $ruleId = query_insert_id ();
    // QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId) values('$ruleId','$myInstanceId','-1')");
    
    if ($myClassesId == $dimmerClassesId)
    {
      $evOffFunctionId = getClassesIdFunctionsIdByName ( $dimmerClassesId, "evOff" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$evOffFunctionId','1')" );
    } else if ($myClassesId == $rolloClassesId)
    {
      $statusFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "evClosed" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$statusFunctionId','1')" );
      $signalId = query_insert_id ();
      
      $positionParamId = getClassesIdFunctionParamIdByName ( $rolloClassesId, "evClosed", "position" );
      
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                     values('$signalId','$positionParamId','255')" );
    } else if ($myClassesId == $schalterClassesId)
    {
      $evOnFunctionId = getClassesIdFunctionsIdByName ( $schalterClassesId, "evOff" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$evOnFunctionId','1')" );
                                
    } else if ($myClassesId == $ledClassesId)
    {
      $evOnFunctionId = getClassesIdFunctionsIdByName ( $ledClassesId, "evOff" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$evOnFunctionId','1')" );
    } else die ( "nicht implementierte class $myClassesId -11" );
  }
}

function isFunctionActive($fkt)
{
  if ($fkt == "-") return FALSE;
  if ($fkt == "") return FALSE;
  if ($fkt == "false") return FALSE;
  return TRUE;
}
function generateLedFeedbackForGroup($groupId, $manualGroup = 0)
{
  // echo "Generiere Ledfeedback für Gruppe $groupId <br>";
  global $CONTROLLER_CLASSES_ID;
  global $signalParamWildcard,$signalParamWildcardWord;
  global $dimmerClassesId, $rolloClassesId, $ledClassesId, $schalterClassesId, $irClassesId, $tasterClassesId, $logicalButtonClassesId;
  global $ledStatusBrightness;
  global $serverInstances;
  
  $erg = QUERY ( "select featureInstanceId,featureClassesId from groupFeatures join featureInstances on (featureInstances.id=featureInstanceId) where groupId='$groupId' limit 1" );
  $obj = MYSQLi_FETCH_OBJECT ( $erg );
  $myInstanceId = $obj->featureInstanceId;
  $myClassesId = $obj->featureClassesId;
  
  // LED Feedback generieren
  $erg = QUERY ( "select * from basicRules where groupId='$groupId' and active='1'" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    // LED Feedback 0 = Kein, 1 = Aktor Einzeln, 2 = Teilszene, 3 = Komplettszene
    if ($obj->ledStatus == 0) {}// 0 = Kein Feedback
    else if ($obj->ledStatus == 2 || $obj->ledStatus == 3) // 2 = Teilszene 3 = Komplettszene
    {
      // $ledStatusBrightness="100";
      // $erg2 = QUERY("select paramValue from basicConfig where paramKey = 'ledStatusBrightness' limit 1");
      // if($row = MYSQLi_FETCH_ROW($erg2)) $ledStatusBrightness=$row[0];
      // $ledStatusBrightness = (int)($ledStatusBrightness*255/100);
      
      $evGroupOnFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn" );
      $evGroupOffFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff" );
      $evGroupUndefinedFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined" );
      
      // LEDs der Signale suchen
      $erg2 = QUERY ( "select featureInstanceId from basicRuleSignals where ruleId='$obj->id' order by id" );
      while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
      {
        $actSignalInstanceId = $row [0];
        $signalClassesId = getClassesIdByFeatureInstanceId ( $actSignalInstanceId );
        
        if ($signalClassesId == $tasterClassesId)
        {
          // Erstmal alle bisherigen Feedbacks zu diesem Aktor-Signal-Paar l�en
          // $ledFeedbackIndent=$myInstanceId."-".$actSignalInstanceId;
          // $erg4 = QUERY("select id from rules where ledFeedbackIndent='$ledFeedbackIndent'");
          // while($row4=MYSQLi_FETCH_ROW($erg4)) deleteRule($row4[0]);
          
          $ledInstanceId = getLedForTaster ( $actSignalInstanceId );
          if (! showError ( $ledInstanceId, $obj->id, $groupId, $actSignalInstanceId ))
          {
            $erg3 = QUERY ( "select groups.id from groups join groupFeatures on (groupFeatures.groupId=groups.id) where featureInstanceId='$ledInstanceId' limit 1" );
            if ($row3 = MYSQLi_FETCH_ROW ( $erg3 ))
              $ledGroupId = $row3 [0];
            else die ( "B) LED Groupid nicht gefunden zu ledInstanceId $ledInstanceId" );
              
              // Standardstates auslesen
            $ledFirstState = "";
            $ledSecondState = "";
            $erg3 = QUERY ( "select id,basics from groupStates where groupId='$ledGroupId' and (basics='1' or basics='2') limit 2" );
            while ( $row3 = MYSQLi_FETCH_ROW ( $erg3 ) )
            {
              if ($row3 [1] == "1") $ledFirstState = $row3 [0];
              else if ($row3 [1] == "2") $ledSecondState = $row3 [0];
            }
            
            if ($ledFirstState == "" || $ledSecondState == "") die ( "Led States nicht gefunden zu Gruppe $groupId" );
            
            if ($manualGroup == 1) $szeneGroupId = $groupId;
            else
            {
              $szeneGroupId = getSyncGroupIdForSignalInstanceId ( $actSignalInstanceId );
              if ($szeneGroupId=="") echo "Gruppe $groupId <br>";
            }
            
            if ($szeneGroupId == - 2) // keine Gruppe->einzelmember generieren
            {
              // Member einzeln switch
              $obj->ledStatus = 1;
              // echo "Aktuell keine Szene vorhanden! ID = $actSignalInstanceId <br>";
            } else if ($szeneGroupId != "")
            {
              $erg3 = QUERY ( "select controllerId, groupIndex from groupSyncHelper where groupId='$szeneGroupId' order by id desc limit 1" );
              if ($row3 = MYSQLi_FETCH_ROW ( $erg3 ))
              {
                $controllerId = getControllerFeatureInstanceIdForControllerId ( $row3 [0] );
                $groupIndex = $row3 [1];
                
                // pr�b es diese regel bei der led nicht schon gibt
                $erg3 = QUERY ( "select ruleId from ruleSignals join rules on(ruleSignals.ruleId=rules.id) where featureInstanceId='$controllerId' and featureFunctionId='$evGroupUndefinedFunctionId' and groupId='$ledGroupId' limit 1" );
                if ($row3 = MYSQLi_FETCH_ROW ( $erg3 ))
                {
                  // echo "Szene schon vorhanden <br>";
                } else
                {
                  // Teilszene: Einschalten bei groupUndefined und groupOn
                  // Komplettszene: Einschalten bei groupOn
                  QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,ledFeedbackIndent,generated) 
                                        values('$ledGroupId','7','31','255','7','31','255','$ledFirstState','$ledSecondState','evOn','1','$ledFeedbackIndent','1')" );
                  $actRuleId = query_insert_id ();
                  
                  if ($obj->ledStatus == 2)
                  {
                    QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                                values('$actRuleId','$controllerId','$evGroupUndefinedFunctionId','1')" );
                    $actSignalId = query_insert_id ();
                    $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
                    QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                                     values('$actSignalId','$indexParamId','$groupIndex','1')" );
                  }
                  
                  QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                              values('$actRuleId','$controllerId','$evGroupOnFunctionId','1')" );
                  $actSignalId = query_insert_id ();
                  $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn", "index" );
                  QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                                   values('$actSignalId','$indexParamId','$groupIndex','1')" );
                  
                  $ledFunctionIdOn = getClassesIdFunctionsIdByName ( $ledClassesId, "on" );
                  
                  QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated)
        		                                  values('$actRuleId','$ledInstanceId','$ledFunctionIdOn','1')" );
                  $newRuleActionId = query_insert_id ();
                  
                  $ledParamBrightnessId = getClassesIdFunctionParamIdByName ( $ledClassesId, "on", "brightness" );
                  $ledParamDurationId = getClassesIdFunctionParamIdByName ( $ledClassesId, "on", "duration" );
                  QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) 
        	                                         values('$newRuleActionId','$ledParamBrightnessId','$ledStatusBrightness','1')" );
                  QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) 
        	                                         values('$newRuleActionId','$ledParamDurationId','0','1')" );
                  
                  // Ausschalten bei groupOff
                  // Bei Komplettszene auch ausschalten bei undefined
                  QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,ledFeedbackIndent,generated) 
                                        values('$ledGroupId','7','31','255','7','31','255','$ledSecondState','$ledFirstState','evOff','1','$ledFeedbackIndent','1')" );
                  $actRuleId = query_insert_id ();
                  
                  if ($obj->ledStatus == 3) // Komplettszene
                  {
                    QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                                values('$actRuleId','$controllerId','$evGroupUndefinedFunctionId','1')" );
                    $actSignalId = query_insert_id ();
                    $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
                    QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                                     values('$actSignalId','$indexParamId','$groupIndex','1')" );
                  }
                  
                  QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                              values('$actRuleId','$controllerId','$evGroupOffFunctionId','1')" );
                  $actSignalId = query_insert_id ();
                  $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff", "index" );
                  QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                                   values('$actSignalId','$indexParamId','$groupIndex','1')" );
                  
                  $ledFunctionIdOff = getClassesIdFunctionsIdByName ( $ledClassesId, "off" );
                  QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated)
        		                                  values('$actRuleId','$ledInstanceId','$ledFunctionIdOff','1')" );
                }
              } else
                die ( "ControllerId und GroupIndex zu Szenengruppe $szeneGroupId nicht gefunden. actSignalInstanceId = $actSignalInstanceId groupId = $groupId ruleId=" . $obj->id );
            } else
              die ( "SzeneGroupId nicht gefunden zu Signal $actSignalInstanceId  " );
          } else
            echo "A Kein LogicalButton $actSignalInstanceId <br>";
        }
      }
    }
    
    if ($obj->ledStatus == 1) // 1 = Aktor Einzeln
    {
      // $ledStatusBrightness="100";
      // $erg2 = QUERY("select paramValue from basicConfig where paramKey = 'ledStatusBrightness' limit 1");
      // if($row = MYSQLi_FETCH_ROW($erg2)) $ledStatusBrightness=$row[0];
      // $ledStatusBrightness = (int)($ledStatusBrightness*255/100);
      
      // LEDs der Signale suchen
      $erg2 = QUERY ( "select featureInstanceId from basicRuleSignals where ruleId='$obj->id' order by id" );
      while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
      {
        $actSignalInstanceId = $row [0];
        if ($serverInstances [$actSignalInstanceId] == 1) continue; // Kein Feedback f�tuelle Servertaster
        
        $signalClassesId = getClassesIdByFeatureInstanceId ( $actSignalInstanceId );
        
        if ($signalClassesId == $tasterClassesId)
        {
          $ledInstanceId = getLedForTaster ( $actSignalInstanceId );
          
          if (! showError ( $ledInstanceId, $obj->id, $groupId, $actSignalInstanceId ))
          {
            // Erstmal alle bisherigen Feedbacks zu diesem Aktor-Signal-Paar l�en
            // $ledFeedbackIndent=$myInstanceId."-".$actSignalInstanceId;
            // $erg4 = QUERY("select id from rules where ledFeedbackIndent='$ledFeedbackIndent'");
            // while($row4=MYSQLi_FETCH_ROW($erg4)) deleteRule($row4[0]);
            
            $erg3 = QUERY ( "select groups.id from groups join groupFeatures on (groupFeatures.groupId=groups.id) where featureInstanceId='$ledInstanceId' limit 1" );
            if ($row3 = MYSQLi_FETCH_ROW ( $erg3 )) $ledGroupId = $row3 [0];
            else die ( "A) LED Groupid nicht gefunden zu ledInstanceId $ledInstanceId" );
              
              // Standardstates auslesen
            $ledFirstState = "";
            $ledSecondState = "";
            $erg3 = QUERY ( "select id,basics from groupStates where groupId='$ledGroupId' and (basics='1' or basics='2') limit 2" );
            while ( $row3 = MYSQLi_FETCH_ROW ( $erg3 ) )
            {
              if ($row3 [1] == "1") $ledFirstState = $row3 [0];
              else if ($row3 [1] == "2") $ledSecondState = $row3 [0];
            }
            
            if ($ledFirstState == "" || $ledSecondState == "") die ( "Led States nicht gefunden zu Gruppe $ledGroupId" );
              // $myClassesId
              // $dimmerClassesId
              // $rolloClassesId
              // $ledClassesId
              // $schalterClassesId

            $paramBrightnessId = - 1; 
            $paramDurationId = -1;             
            
            // dimmer events
            if ($myClassesId == $dimmerClassesId)
            {
              $evOnFunctionId = getClassesIdFunctionsIdByName ( $dimmerClassesId, "evOn" );
              $evOffFunctionId = getClassesIdFunctionsIdByName ( $dimmerClassesId, "evOff" );
              $paramBrightnessId = getClassesIdFunctionParamIdByName ( $dimmerClassesId, "evOn", "brightness" );
              $paramBrightnessValue = $signalParamWildcard;
            } 
            else if ($myClassesId == $schalterClassesId)
            {
              $evOnFunctionId = getClassesIdFunctionsIdByName ( $schalterClassesId, "evOn" );
              $evOffFunctionId = getClassesIdFunctionsIdByName ( $schalterClassesId, "evOff" );
              $paramDurationId = getClassesIdFunctionParamIdByName ( $schalterClassesId, "evOn", "duration" );
              $paramDurationValue = $signalParamWildcardWord;
            } 
            else if ($myClassesId == $ledClassesId || $myClassesId == $logicalButtonClassesId)
            {
              $evOnFunctionId = getClassesIdFunctionsIdByName ( $ledClassesId, "evOn" );
              $evOffFunctionId = getClassesIdFunctionsIdByName ( $ledClassesId, "evOff" );
              $paramBrightnessId = getClassesIdFunctionParamIdByName ( $ledClassesId, "evOn", "brightness" );
              $paramBrightnessValue = $signalParamWildcard;
            } 
            else if ($myClassesId == $rolloClassesId) die ( "LED-FEEDBACK NOCH NICHT IMPLEMENTIERT FüR ROLLOS" );
            
            // Einschalten bei evOn
            QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,baseRule,ledFeedbackIndent,generated) 
                                 values('$ledGroupId','7','31','255','7','31','255','$ledFirstState','$ledSecondState','1','$ledFeedbackIndent','1')" );
            $ruleId = query_insert_id ();
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                       values('$ruleId','$myInstanceId','$evOnFunctionId','1')" );
            $signalId = query_insert_id ();
            
            if ($paramBrightnessId != - 1) QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                              values('$signalId','$paramBrightnessId','$paramBrightnessValue','1')" );

            if ($paramDurationId != - 1) QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                              values('$signalId','$paramDurationId','$paramDurationValue','1')" );
            
            
            $ledFunctionIdOn = getClassesIdFunctionsIdByName ( $ledClassesId, "on" );
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated)
                                       values('$ruleId','$ledInstanceId','$ledFunctionIdOn','1')" );
            $newRuleActionId = query_insert_id ();
            $ledParamBrightnessId = getClassesIdFunctionParamIdByName ( $ledClassesId, "on", "brightness" );
            $ledParamDurationId = getClassesIdFunctionParamIdByName ( $ledClassesId, "on", "duration" );
            
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) 
                                            values('$newRuleActionId','$ledParamBrightnessId','$ledStatusBrightness','1')" );
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) 
                                            values('$newRuleActionId','$ledParamDurationId','0','1')" );
            
            // Ausschalten bei evOff
            QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,baseRule,ledFeedbackIndent,generated) 
                                 values('$ledGroupId','7','31','255','7','31','255','$ledSecondState','$ledFirstState','1','$ledFeedbackIndent','1')" );
            $ruleId = query_insert_id ();
            
            QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
         	                             values('$ruleId','$myInstanceId','$evOffFunctionId','1')" );
            
            $ledFunctionIdOff = getClassesIdFunctionsIdByName ( $ledClassesId, "off" );
            QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated)
            		                       values('$ruleId','$ledInstanceId','$ledFunctionIdOff','1')" );
          } else
            echo "B Kein LogicalButton $actSignalInstanceId <br>";
        }
      }
    }
    
    /*
     * if ($obj->ledStatus==4) // 4 = Gruppenstatus { // LEDs der Signale suchen $erg2=QUERY("select featureInstanceId from basicRuleSignals where ruleId='$obj->id' order by id"); while ($row=MYSQLi_FETCH_ROW($erg2)) { $actSignalInstanceId = $row[0]; if ($serverInstances[$actSignalInstanceId]==1) continue; // Kein Feedback f�tuelle Servertaster $signalClassesId = getClassesIdByFeatureInstanceId($actSignalInstanceId); if ($signalClassesId == $tasterClassesId) { $ledInstanceId = getLedForTaster($actSignalInstanceId); if (!showError($ledInstanceId, $obj->id, $groupId, $actSignalInstanceId)) { // Standardstates auslesen $firstState=""; $secondState=""; $erg3 = QUERY("select id,basics from groupStates where groupId='$groupId' and (basics='1' or basics='2') limit 2"); while ($row3=MYSQLi_FETCH_ROW($erg3)) { if ($row3[1]=="1") $firstState=$row3[0]; else if ($row3[1]=="2") $secondState=$row3[0]; } if ($firstState=="" || $secondState=="") die("States nicht gefunden zu Gruppe $groupId"); // ON Regeln finden $ledFunctionIdOn = getClassesIdFunctionsIdByName($ledClassesId,"on"); $ledParamBrightnessId = getClassesIdFunctionParamIdByName($ledClassesId,"on","brightness"); $ledParamDurationId = getClassesIdFunctionParamIdByName($ledClassesId,"on","duration"); $erg3 = QUERY("select id from rules where groupId='$groupId' and resultingStateId='$secondState'"); while ($row3=MYSQLi_FETCH_ROW($erg3)) { $ruleId=$row3[0]; QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$ledInstanceId','$ledFunctionIdOn','1')"); $newRuleActionId=query_insert_id(); QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','$ledParamBrightnessId','$ledStatusBrightness','1')"); QUERY("INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','$ledParamDurationId','0','1')"); } // OFF Regeln finden $ledFunctionIdOff = getClassesIdFunctionsIdByName($ledClassesId,"off"); $erg3 = QUERY("select id from rules where groupId='$groupId' and resultingStateId='$firstState'"); while ($row3=MYSQLi_FETCH_ROW($erg3)) { $ruleId=$row3[0]; QUERY("INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$ledInstanceId','$ledFunctionIdOff','1')"); } } else echo "Kein LogicalButton $actSignalInstanceId <br>"; } } }
     */
  }
}
function getControllerFeatureInstanceIdForControllerId($controllerId)
{
  $erg = QUERY ( "select featureInstances.id from featureInstances join controller on (controller.objectId=featureInstances.objectId) where controller.id='$controllerId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    return $row [0];
}

/*
 Idee: Wenn mehrere Signale einen Aktor steuern, kann man das problemlos die Gruppe des Aktors regeln, 
       auch wenn ein Signal später in eine Multigruppen rausgezogen wird, wird alles über die Synchronisationsevents synchronisiert. 
       Wenn aber ein Signal mehrere Aktoren ansteuern, muss es ein Statechart für Aktoren gemeinsam gehen. 
       Dafür wird dann eine eigene Multigruppe erstellt. Kriterium ist also ob ein Signal mehrere Aktoren schaltet. 
       Zusätzlich zu den DummyEvents gibt es dann die GroupStates über die der Gruppenzustand und das LED-Feedback gesteuert wird
 */
function generateMultiGroups()
{
  global $CONTROLLER_CLASSES_ID;
  global $debug;
  global $signalParamWildcard,$signalParamWildcardWord;
  global $dimmerClassesId, $rolloClassesId, $ledClassesId, $schalterClassesId, $irClassesId, $tasterClassesId, $logicalButtonClassesId, $pcServerClassesId;
  
  //$debug=1;
  
  if ($debug == 1) echo "Generiere Autogruppen <br>";
  
  $erg = QUERY ("select activationStateId,resultingStateId,
                        ruleactions.featureInstanceId as actionFeatureId, 
                        rulesignals.id as ruleSignalId, rulesignals.featureInstanceId as signalFeatureId, rulesignals.featureFunctionId as signalFunctionId,
                        groups.id as groupId, completeGroupFeedback 
                        from ruleactions 
                        join rules on (rules.id=ruleactions.ruleId) 
                        join ruleSignals on (ruleSignals.ruleId = rules.id) 
                        join groups on (rules.groupId=groups.id) 
                        where  single=1 and groups.generated=0 and groupLock=0 order by groups.id" ); // single=1 and and groupLock=0
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $key = $obj->signalFeatureId;
    
    $myClassesId = getClassesIdByFeatureInstanceId ( $obj->signalFeatureId );
    if ($myClassesId == $CONTROLLER_CLASSES_ID) continue; // Controllerevents nicht gruppieren (EvTime usw)
    if ($myClassesId == $dimmerClassesId) continue; // Dimmerevents nicht gruppieren
    if ($myClassesId == $ledClassesId) continue; // Ledevents nicht gruppieren
    
    // wenns unabhängig vom state ist und kein Feedback benötigt wird -> nicht rausziehen
    if ($obj->activationStateId == 0 && $obj->completeGroupFeedback==0) continue; 
      
    // TODO hier nur Taster und IR zulassen ?
    
    $actionClassesId = getClassesIdByFeatureInstanceId ( $obj->actionFeatureId );
    
    if ($actionClassesId == $ledClassesId) continue; // LEDs als Aktor nicht gruppieren
    if ($actionClassesId == $logicalButtonClassesId) continue; // LogicalButtons nicht gruppieren
    if ($actionClassesId == $pcServerClassesId) continue; // PcServer nicht gruppieren
    if ($actionClassesId == $tasterClassesId) continue; // Taster als Aktor nicht gruppieren
      
      // Bei Infrarot müssen zur Unterscheidung noch die Parameter rein
    if ($myClassesId == $irClassesId)
    {
      $signalParams = "";
      $erg2 = QUERY ( "select paramValue from ruleSignalParams where ruleSignalId='$obj->ruleSignalId' order by featureFunctionParamsId" );
      while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
      {
        if ($signalParams != "") $signalParams .= "-";
        $signalParams .= $row [0];
      }
      
      $key = $obj->signalFeatureId . "-" . $signalParams;
    }
    
    // ID: 0000051 Rollos nur mit Rollos gruppieren
    if ($actionClassesId == $rolloClassesId) $key .= "-rollo";
    
    //if ($key == "237") $aktor [$key] [$obj->actionFeatureId] .= $obj->ruleSignalId . "(" . $obj->groupId . "),";
    //else 
    $aktor [$key] [$obj->actionFeatureId] .= $obj->ruleSignalId . ",";
    $aktorView [$obj->actionFeatureId] [$obj->signalFeatureId] = 1;
    
    $actionClassForSignal [$obj->signalFeatureId] = $actionClassesId;
  }
  
  QUERY ( "update ruleSignals set groupAlias='0'" );
  
  
  // Jetzt noch prüfen, ob beide Aktoren nicht nur mit dem gleichen Sensor, sondern auch mit dem gleichen eventType angesteuert werden
  foreach ( $aktor as $signalFeatureId => $actionIdArr )
  {
    if (count ( $actionIdArr ) > 1)
    {
      if ($debug == 1) echo "Signal $signalFeatureId steuert ";
      foreach ( $actionIdArr as $actionFeatureId => $ruleSignalId )
      {
        if ($debug == 1) echo "$actionFeatureId in Gruppe $groupId [$ruleSignalId] , ";
        if ($debug == 1) echo "anzahl signalFeatures = " . count ( $aktorView [$actionFeatureId] ) . ", ";
        if ($debug == 1) echo "Groupalias: $groupAlias <br>";
        
        $parts = explode ( ",", $ruleSignalId );
        foreach ( $parts as $actRuleSignalId )
        {
          if ($actRuleSignalId > 0)
          {
          	$erg77 = QUERY("select featureFunctionId from ruleSignals where id='$actRuleSignalId' limit 1");
          	$row77 = MYSQLi_FETCH_ROW($erg77);
          	
          	if ($row77[0]==43) $mySignalTypes[$actionFeatureId][]=2; // covered wie clicked behandeln
          	else $mySignalTypes[$actionFeatureId][]=$row77[0];
          }
          // QUERY("update rules set groupAlias='$groupAlias' where groupId='$groupId'");
        }
      }

      $foundDuplicate=0;
      foreach ($mySignalTypes as $actionFeature => $signals)
      {
         foreach ($mySignalTypes as $otherActionFeature => $otherSignals)
         {
         	  if ($actionFeature == $otherActionFeature) continue;
         	  
         	  foreach ($signals as $signal)
         	  {
 	         	  foreach ($otherSignals as $otherSignal)
         	    {
         	    	  if ($signal == $otherSignal)
         	    	  {
         	    	  	$foundDuplicate=1;
         	    	  	break;
         	    	  }
         	    }
         	    if ($foundDuplicate==1) break;
         	  }
         	  if ($foundDuplicate==1) break;
         }
         if ($foundDuplicate==1) break;
      }

      if ($foundDuplicate!=1)
      {
      	 if ($debug == 1)
      	 {
      	 	  echo "Keine Duplicate !";
      	 	  print_r($mySignalTypes);
      	 	  exit;
      	 }
      }
      
      unset($mySignalTypes);
      
      if ($debug == 1) echo "<br>";
    }
  }
 
  $groupAlias = 1;
  foreach ( $aktor as $signalFeatureId => $actionIdArr )
  {
    if (count ( $actionIdArr ) > 1)
    {
      if ($debug == 1) echo "Signal $signalFeatureId steuert ";
      foreach ( $actionIdArr as $actionFeatureId => $ruleSignalId )
      {
        if ($debug == 1) echo "$actionFeatureId in Gruppe $groupId [$ruleSignalId] , ";
        if ($debug == 1) echo "anzahl signalFeatures = " . count ( $aktorView [$actionFeatureId] ) . ", ";
        if ($debug == 1) echo "Groupalias: $groupAlias <br>";
        
        $parts = explode ( ",", $ruleSignalId );
        foreach ( $parts as $actRuleSignalId )
        {
          if ($actRuleSignalId > 0)
          {
            QUERY ( "update ruleSignals set groupAlias='$groupAlias' where id='$actRuleSignalId' limit 1" );
            $aliasActionClass [$groupAlias] = $actionClassForSignal [$actRuleSignalId];
          }
          // QUERY("update rules set groupAlias='$groupAlias' where groupId='$groupId'");
        }
      }
      
      if ($debug == 1) echo "<br>";
      $groupAlias ++;
    }
  }
  
  $erg = QUERY ( "select distinct groupAlias from ruleSignals where groupAlias>0 order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    $basicStateNames = getBasicStateNames ( $aliasActionClass [$obj->groupAlias] ); // einfach einen aktor nehmen
    $offName = $basicStateNames->offName;
    $onName = $basicStateNames->onName;
    
    // Wenn wir keine Statewechsel und kein LED Feedback haben, brauchen wir auch keine Synchronisationsevents
    $foundStateChanges = 0;
    
    // Neue Gruppe erstellen
    if ($debug == 1) echo "Erstelle Gruppe Generated $obj->groupAlias <br>";
    QUERY ( "INSERT into groups (name,single,generated) values('Generated $obj->groupAlias','0','1')" );
    $newGroupId = query_insert_id ();
    
    QUERY ( "INSERT into groupStates (groupId,name, value,basics,generated) values ('$newGroupId','$offName','1','1','1')" );
    $activationStateId = query_insert_id ();
    QUERY ( "INSERT into groupStates (groupId,name, value,basics,generated) values ('$newGroupId','$onName','2','2','1')" );
    $resultingStateId = query_insert_id ();
    
    // Wenn es in der neuen Gruppe verschiedene Classes existieren, machen wir aus evCovered ein evClicked
    $convertCoveredEvent = 0;
    $lastClass = "";
    $erg7 = QUERY ( "select distinct (ruleActions.featureInstanceId) from ruleActions join rules on (rules.id=ruleActions.ruleId) join ruleSignals on (ruleSignals.ruleId=rules.id) where ruleSignals.groupAlias='$obj->groupAlias'" );
    while ( $row = MYSQLi_FETCH_ROW ( $erg7 ) )
    {
      $actClassesId = getClassesIdByFeatureInstanceId ( $row [0] );
      if ($lastClass == "") $lastClass = $actClassesId;
      else if ($lastClass != $actClassesId)
      {
        // echo "$lastClass , $actClassesId <br>";
        $lastClass = $actClassesId;
        $convertCoveredEvent = 1;
      }
    }
    // echo "---- <br>";
    
    // unset($myOffInstances);
    
    $erg7 = QUERY ( "select id from ruleSignals where groupAlias='$obj->groupAlias' order by id" );
    while ( $row = MYSQLi_FETCH_ROW ( $erg7 ) )
    {
      $signalId = $row [0];
      
      // Features eintragen
      $erg2 = QUERY ( "select ruleActions.featureInstanceId from ruleActions join rules on (ruleActions.ruleId = rules.id) join ruleSignals on (ruleSignals.ruleId = rules.id) where ruleSignals.id='$signalId' limit 1" );
      if ($obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ))
      {
        QUERY ( "delete from groupFeatures where featureInstanceId='$obj2->featureInstanceId' and groupId='$newGroupId' limit 1" );
        QUERY ( "INSERT into groupFeatures (featureInstanceId, groupId,generated) values('$obj2->featureInstanceId','$newGroupId','1')" );
      } else die ( "Fehler: ActionInstance nicht gefunden zu GroupAlias $obj->groupAlias" );
        
        // Regeln mischen die für die signals markiert wurden
      $extras = "";
      $erg2 = QUERY ( "select rules.*, activation.basics as startBasics, resulting.basics as resultingBasics from rules join ruleSignals on (ruleSignals.ruleId=rules.id) left join groupStates as activation on (activation.id = rules.activationStateId) left join groupStates as resulting on (resulting.id = rules.resultingStateId) where ruleSignals.id='$signalId' limit 1" );
      if ($obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ))
      {
        if ($obj2->extras != "") $extras = $obj2->extras;
        
        $myActivationStateId = "0";
        if ($obj2->startBasics == "1") $myActivationStateId = $activationStateId;
        else if ($obj2->startBasics == "2") $myActivationStateId = $resultingStateId;
        
        $myResultingStateId = "0";
        if ($obj2->resultingBasics == "1") $myResultingStateId = $activationStateId;
        else if ($obj2->resultingBasics == "2") $myResultingStateId = $resultingStateId;
        
        $erg3 = QUERY ( "select * from ruleSignals where id='$signalId' limit 1" );
        if ($obj3 = MYSQLi_FETCH_OBJECT ( $erg3 ))
        {
          $signalId = $obj3->id;
          $signalFeatureInstanceId = $obj3->featureInstanceId;
          $signalFeatureFunctionId = $obj3->featureFunctionId;
          
          $completeGroupFeedback = $obj3->completeGroupFeedback;
          
          // Beim Taster aus evCovered evClicked machen, wenn verschiedene Classes angesteuert werden
          if ($convertCoveredEvent == 1 && $signalFeatureFunctionId == 43) $signalFeatureFunctionId = 2;
        } else die ( "Signal ID $signalId nicht gefunden" );
          
        // Prüfen ob es die passende Regel schon gibt
        // echo "select rules.id from rules join ruleSignals on (ruleSignals.ruleId=rules.id) where activationStateId='$myActivationStateId' and groupId='$newGroupId' and featureInstanceId='$signalFeatureInstanceId' and featureFunctionId='$signalFeatureFunctionId' limit 1 <br>";
        $erg3 = QUERY ( "select rules.id from rules join ruleSignals on (ruleSignals.ruleId=rules.id) where activationStateId='$myActivationStateId' and groupId='$newGroupId' and featureInstanceId='$signalFeatureInstanceId' and featureFunctionId='$signalFeatureFunctionId' limit 1" );
        if ($row3 = MYSQLi_FETCH_ROW ( $erg3 ))
        {
          $newRuleId = $row3 [0];
          // echo "gefunden $newRuleId <br>";
        } 
        else
        {
          QUERY ( "INSERT into rules (groupId,activationStateId,resultingStateId,startDay,startHour,startMinute,endDay,endHour,endMinute,signalType,baseRule,generated, intraDay)
    	                     values('$newGroupId','$myActivationStateId','$myResultingStateId','$obj2->startDay','$obj2->startHour','$obj2->startMinute','$obj2->endDay','$obj2->endHour','$obj2->endMinute','$obj2->signalType','$obj2->baseRule','1','$obj2->intraDay')" );
          $newRuleId = query_insert_id ();
          if ($myResultingStateId != 0 || $myActivationStateId != 0) $foundStateChanges = 1;
            
            // echo "neu $newRuleId <br>";
            
          // Signale eintragen
          
          QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$newRuleId','$signalFeatureInstanceId','$signalFeatureFunctionId','1')" );
          $newSignalId = query_insert_id ();
          
          $erg4 = QUERY ( "select * from ruleSignalParams where ruleSignalId='$signalId' order by id" );
          while ( $obj4 = MYSQLi_FETCH_OBJECT ( $erg4 ) )
          {
            QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$newSignalId','$obj4->featureFunctionParamsId','$obj4->paramValue','1')" );
          }
        }
        
        // Actions eintragen
        $erg3 = QUERY ( "select * from ruleActions where ruleId='$obj2->id' order by id" );
        while ( $obj3 = MYSQLi_FETCH_OBJECT ( $erg3 ) )
        {
          // if ($obj2->offRule==1) $myOffInstances[$obj3->featureInstanceId]=1;
          
          QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$newRuleId','$obj3->featureInstanceId','$obj3->featureFunctionId','1')" );
          $newSignalId = query_insert_id ();
          
          $erg4 = QUERY ( "select * from ruleActionParams where ruleActionId='$obj3->id' order by id" );
          while ( $obj4 = MYSQLi_FETCH_OBJECT ( $erg4 ) )
          {
            QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newSignalId','$obj4->featureFunctionParamsId','$obj4->paramValue','1')" );
          }
        }
      } else die ( "FEhler: keine regel zu signal id $signalId gefunden" );
    }
    
    if ($extras == "Rotation") generateRotation ( $newGroupId );
      
      // Synchronisationsevents generieren
    if ($foundStateChanges == 1 || $completeGroupFeedback > 0) generateSyncEvents ( $newGroupId, $completeGroupFeedback );
  } // For multigroups
}

function generateSignalGroup($groupId)
{
  // echo "generateSignalGroup $groupId <br>";
  global $CONTROLLER_CLASSES_ID;
  global $debug;
  global $signalParamWildcard,$signalParamWildcardWord;
  global $dimmerClassesId, $rolloClassesId, $ledClassesId, $schalterClassesId, $irClassesId, $tasterClassesId, $logicalButtonClassesId;
  
  $evGroupOnFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn" );
  $evGroupOffFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff" );
  $evGroupUndefinedFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined" );
  
  QUERY ( "DELETE from groupSyncHelper where groupId='$groupId'" );
  
  $erg = QUERY ( "select name,groupType from groups where id='$groupId' limit 1" );
  $row = MYSQLi_FETCH_row ( $erg );
  $name = query_real_escape_string ( "Generated " . $row [0] );
  $groupType = $row [1];
  
  QUERY ( "insert into groups (single,name,generated) values('0','$name','1')" );
  $newGroupId = query_insert_id ();
  
  // Jedem Status der Gruppe einen Index zuweisen
  $index = 0;
  $groupThreshold = 0;
  unset ( $indexList );
  $erg2 = QUERY ( "select activationStateId from rules join ruleSignals on(ruleSignals.ruleId = rules.id) where groupId='$groupId' order by activationStateId" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
  {
    if ($row [0] % 2 == 0)
    {
      $nr = ( int ) ($row [0] / 10);
      $indexList [$nr] = $index ++;
    }
  }
  
  $totalMembers = count ( $indexList );
  if ($totalMembers == 0)
  {
    echo "Keine Member in $groupId <br>";
    return;
  }
  
  if ($totalMembers > 64)
    die ( "Fehler: Signalgruppe mit mehr als 64 Membern gefunden -> $totalMembers" );
    
    // Passende Gruppe(n) erstellen
    // Wenn mehr als 8 Member beteiligt sind m�wir Gruppen kaskadieren
    // Zum Verwalten des Gruppenstatus wird dann eine Obergruppe verwendet, die als Member so viele Untergruppen verwendet, wie Aktoren im Spiel sind.
    // Maximal also 8x8 = 64 Member
  $nrNeededGroups = ( int ) ($totalMembers / 8);
  if ($totalMembers % 8 > 0)
    $nrNeededGroups ++;
  if ($nrNeededGroups > 1)
    $schachtelNeeded = 1;
  
  unset ( $syncGroups );
  for($i = 0; $i < $nrNeededGroups; $i ++)
  {
    // Freie Gruppe auf irgend einem Controller suchen.
    $controllerId = - 1;
    $erg2 = QUERY ( "select id from controller where size!='999' and online='1'" );
    while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
    {
      $controllerId = $row [0];
      
      $myGroupIndex = 0;
      $erg3 = QUERY ( "select groupIndex from groupSyncHelper where controllerId='$controllerId' order by groupIndex desc limit 1" );
      if ($row3 = MYSQLi_FETCH_ROW ( $erg3 ))
        $myGroupIndex = $row3 [0] + 1;
        
        // Controller ist schon voll.
      if ($myGroupIndex > 7)
      {
        $controllerId = - 1;
        continue;
      } else
        break;
    }
    
    if ($controllerId == - 1)
    {
      die ( "Fehler: keine freie Gruppe f�nalgruppe gefunden" );
    }
    
    // Gruppe reservieren
    QUERY ( "INSERT into groupSyncHelper (controllerId, groupIndex, groupId) values('$controllerId','$myGroupIndex','$newGroupId')" );
    
    // Controller als Instanz suchen
    $erg2 = QUERY ( "select id from featureInstances where controllerId='$controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1" );
    if ($row = MYSQLi_FETCH_ROW ( $erg2 ))
      $controllerInstanceId = $row [0];
    else
      die ( "Fehler: Controller Instance zu controllerId $controllerId nicht gefunden" );
      
      // Threshold der Gruppe anhand der Member definieren
    if ($i < $nrNeededGroups - 1)
      $groupThreshold = 8;
    else
      $groupThreshold = $totalMembers % 8;
    
    $syncGroups [$i] ["controllerId"] = $controllerId;
    $syncGroups [$i] ["controllerInstanceId"] = $controllerInstanceId;
    $syncGroups [$i] ["groupThreshold"] = $groupThreshold;
    $syncGroups [$i] ["myGroupIndex"] = $myGroupIndex;
    
    // Nach der letzten normalen Gruppe erstellen wir noch die Schaltungsgruppe
    if ($i == $nrNeededGroups - 1 && $schachtelNeeded == 1)
    {
      $schachtelNeeded = 0;
      $nrNeededGroups ++;
    }
  }
  
  // Pro Status die Aktivierungs und Deaktivierungsevents eintragen und damit Bit in Syncgruppe schalten
  $syncActorDone = "";
  $erg2 = QUERY ( "select rules.id, activationStateId from rules join ruleSignals on(ruleSignals.ruleId = rules.id) where groupId='$groupId' order by activationStateId" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
  {
    $ruleId = $row [0];
    if ($syncActorDone [$ruleId] == 1)
      continue;
    $syncActorDone [$ruleId] = 1;
    
    $activationStateId = $row [1];
    
    $nr = ( int ) ($activationStateId / 10);
    if ($activationStateId % 2 == 0)
      $active = 1;
    else
      $active = 0;
    
    $actFeatureIndex = $indexList [$nr];
    $actGroupIndex = ( int ) ($actFeatureIndex / 8);
    $controllerId = $syncGroups [$actGroupIndex] ["controllerId"];
    $controllerInstanceId = $syncGroups [$actGroupIndex] ["controllerInstanceId"];
    $groupThreshold = $syncGroups [$actGroupIndex] ["groupThreshold"];
    $myGroupIndex = $syncGroups [$actGroupIndex] ["myGroupIndex"];
    $actFeatureIndex = $indexList [$nr] % 8;
    
    // Regel
    QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) 
                      values('$newGroupId','7','31','255','7','31','255','0','0','evOn','1','1','1')" );
    $newRuleId = query_insert_id ();
    
    QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$newRuleId','$controllerInstanceId','169','1')" );
    $newRuleActionId = query_insert_id ();
    QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','326','$myGroupIndex','1')" );
    QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','327','$actFeatureIndex','1')" );
    QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','328','$active','1')" );
    QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','349','$groupThreshold','1')" );
    
    // Signale erg㭺en
    $erg3 = QUERY ( "select * from ruleSignals where ruleId='$ruleId'" );
    while ( $obj3 = MYSQLi_FETCH_object ( $erg3 ) )
    {
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) 
                               values('$newRuleId','$obj3->featureInstanceId','$obj3->featureFunctionId','1')" );
      $signalId = query_insert_id ();
      
      $erg4 = QUERY ( "select * from ruleSignalParams where ruleSignalId='$obj3->id'" );
      while ( $obj4 = MYSQLi_FETCH_object ( $erg4 ) )
      {
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated)
                                 values('$signalId','$obj4->featureFunctionParamsId','$obj4->paramValue','1')" );
      }
    }
  }
  
  // Wenn mehr als eine Gruppe n� war, haben wir geschachtelt.
  // Dann hier die Subevents eintragen und sp㳥r die Gesamtgruppe als Eventgeber eintragen
  if ($nrNeededGroups > 1)
  {
    $globalGroupIndex = $syncGroups [$nrNeededGroups - 1] ["myGroupIndex"];
    $globalControllerInstanceId = $syncGroups [$nrNeededGroups - 1] ["controllerInstanceId"];
    
    $myGroupIndex = $globalGroupIndex;
    $controllerInstanceId = $globalControllerInstanceId;
    
    $nrSubGroups = $nrNeededGroups - 1;
    
    // Synchronisationsevents f� Subgruppen eintragen
    for($i = 0; $i < $nrSubGroups; $i ++)
    {
      $myGroupIndex = $syncGroups [$i] ["myGroupIndex"];
      $controllerInstanceId = $syncGroups [$i] ["controllerInstanceId"];
      
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated) values('$newGroupId','7','31','255','7','31','255','0','0','evOn','1','1')" );
      $actRuleId = query_insert_id ();
      
      if ($groupType == "SIGNALS-OR")
      {
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupUndefinedFunctionId','1')" );
        $actSignalId = query_insert_id ();
        $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      }
      
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupOnFunctionId','1')" );
      $actSignalId = query_insert_id ();
      $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn", "index" );
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$globalControllerInstanceId','169','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','326','$globalGroupIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','327','$i','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','328','1','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','349','$nrSubGroups','1')" );
      
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated) values('$newGroupId','7','31','255','7','31','255','0','0','evOff','1','1')" );
      $actRuleId = query_insert_id ();
      
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupOffFunctionId','1')" );
      $actSignalId = query_insert_id ();
      $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff", "index" );
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      
      if ($groupType == "SIGNALS-AND")
      {
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupUndefinedFunctionId','1')" );
        $actSignalId = query_insert_id ();
        $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      }
      
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$globalControllerInstanceId','169','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','326','$globalGroupIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','327','$i','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','328','0','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','349','$nrSubGroups','1')" );
    }
  }
  
  if ($nrNeededGroups > 1)
  {
    $myGroupIndex = $globalGroupIndex;
    $controllerInstanceId = $globalControllerInstanceId;
  }
  
  // Dann die Gruppenevents in allen Regeln eintragen, die auf diese Gruppen referenzieren
  // $evGroupOnFunctionId $evGroupOffFunctionId $evGroupUndefinedFunctionId
  $erg = QUERY ( "select id,eventType from basicrulegroupsignals where groupId='$groupId'" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $searchFeatureInstanceId = $row [0] * - 1;
    $eventType = $row [1];
    // echo "SearchInstance = " . $searchFeatureInstanceId . "<br>";
    
    $erg2 = QUERY ( "select ruleId from ruleSignals where featureInstanceId='$searchFeatureInstanceId' order by id" );
    while ( $row2 = MYSQLi_FETCH_ROW ( $erg2 ) )
    {
      $actRuleId = $row2 [0];
      // echo $searchFeatureInstanceId . " in " . $actRuleId . "<br>";
      
      // Group-ON
      if ($eventType == "ACTIVE")
      {
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) 
                            values('$actRuleId','$controllerInstanceId','$evGroupOnFunctionId','1')" );
        $actSignalId = query_insert_id ();
        $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn", "index" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                            values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      }
      
      // Group-OFF
      if ($eventType == "DEACTIVE")
      {
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) 
                          values('$actRuleId','$controllerInstanceId','$evGroupOffFunctionId','1')" );
        $actSignalId = query_insert_id ();
        $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff", "index" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                   values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      }
      
      // Group-UNDEFINED
      // Was Signal undefined bewirken soll, h㭧t davon ab welches feedback gew� war
      if (($eventType == "ACTIVE" && $groupType == "SIGNALS-OR") || ($eventType == "DEACTIVE" && $groupType == "SIGNALS-AND"))
      {
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) 
                   values('$actRuleId','$controllerInstanceId','$evGroupUndefinedFunctionId','1')" );
        $actSignalId = query_insert_id ();
        $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                   values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      }
    }
  }
  
  // Dann noch den Controller als Feature der Gruppe eintragen, sonst wird sie nirgens gehostet
  QUERY ( "INSERT into groupFeatures (groupId, featureInstanceId, generated) values('$newGroupId','$controllerInstanceId','1')" );
}
function generateSyncEvents($groupId, $completeGroupFeedback)
{
  // echo "generateSyncEvents $groupId - $completeGroupFeedback <br>";
  global $CONTROLLER_CLASSES_ID;
  global $debug;
  global $signalParamWildcard,$signalParamWildcardWord;
  global $dimmerClassesId, $rolloClassesId, $ledClassesId, $schalterClassesId, $irClassesId, $tasterClassesId, $logicalButtonClassesId;
  
  $evGroupOnFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn" );
  $evGroupOffFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff" );
  $evGroupUndefinedFunctionId = getClassesIdFunctionsIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined" );
  
  QUERY ( "DELETE from groupSyncHelper where groupId='$groupId'" );
  
  // Jedem Aktor der Gruppe einen Index zuweisen
  $index = 0;
  $groupThreshold = 0;
  unset ( $indexList );
  $erg2 = QUERY ( "select distinct featureInstanceId from ruleActions join rules on (rules.id=ruleActions.ruleId) where groupId='$groupId' order by featureInstanceId" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
  {
    $myClassesId = getClassesIdByFeatureInstanceId ( $row [0] );
    if ($myClassesId == $logicalButtonClassesId) continue;
    
    $indexList [$row [0]] = $index ++;
  }
  
  $totalMembers = count ( $indexList );
  if ($totalMembers == 0) return;
  if ($totalMembers > 64) die ( "Fehler: Gruppe mit mehr als 64 Membern gefunden -> $totalMembers" );
    
    // Passende Gruppe(n) erstellen
    // Wenn mehr als 8 Member beteiligt sind müssen wir Gruppen kaskadieren
    // Zum Verwalten des Gruppenstatus wird dann eine Obergruppe verwendet, die als Member so viele Untergruppen verwendet, wie Aktoren im Spiel sind.
    // Maximal also 8x8 = 64 Member
  $nrNeededGroups = ( int ) ($totalMembers / 8);
  if ($totalMembers % 8 > 0)
    $nrNeededGroups ++;
  if ($nrNeededGroups > 1)
    $schachtelNeeded = 1;
    
    // Wir versuchen den ersten Aktor als Host f� Gruppe zu verwenden
  $erg4 = QUERY ( "select featureInstanceId from groupFeatures where groupId='$groupId' order by id limit 1" );
  $row4 = MYSQLi_FETCH_ROW ( $erg4 );
  $firstAktor = $row4 [0];
  
  unset ( $syncGroups );
  for($i = 0; $i < $nrNeededGroups; $i ++)
  {
    // Freie Gruppe auf Controller der beteiligten Aktoren suchen.
    // Dabei zun㢨st versuchen, das erste Feature zu treffen, weil submitRules die Regel auf den Controller des ersten Features packt.
    // Dadurch werden die setGroupState Aufrufe alle intern abgehandelt
    $controllerId = - 1;
    $erg2 = QUERY ( "select distinct(controllerId) from featureInstances join groupFeatures on (groupFeatures.featureInstanceId=featureInstances.id) join controller on (featureInstances.controllerId=controller.id) where groupId='$groupId' and size!=999 order by featureInstances.id='$firstAktor', featureInstances.id" );
    while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
    {
      $controllerId = $row [0];
      
      $myGroupIndex = 0;
      $erg3 = QUERY ( "select groupIndex from groupSyncHelper where controllerId='$controllerId' order by groupIndex desc limit 1" );
      if ($row3 = MYSQLi_FETCH_ROW ( $erg3 ))
        $myGroupIndex = $row3 [0] + 1;
        
        // Controller ist schon voll.
      if ($myGroupIndex > 7)
      {
        $controllerId = - 1;
        continue;
      } else
        break;
    }
    
    if ($controllerId == - 1)
    {
    	echo "select distinct(controllerId) from featureInstances join groupFeatures on (groupFeatures.featureInstanceId=featureInstances.id) join controller on (featureInstances.controllerId=controller.id) where groupId='$groupId' and size!=999 order by featureInstances.id='$firstAktor', featureInstances.id<br>";
      echo "select distinct(controllerId) from featureInstances join groupFeatures on (groupFeatures.featureInstanceId=featureInstances.id) where groupId='$groupId' <br>";
      die ( "Fehler: keine freie Gruppe gefunden" );
    }
    
    // Gruppe reservieren
    QUERY ( "INSERT into groupSyncHelper (controllerId, groupIndex, groupId) values('$controllerId','$myGroupIndex','$groupId')" );
    
    // Controller als Instanz suchen
    $erg2 = QUERY ( "select id from featureInstances where controllerId='$controllerId' and featureClassesId='$CONTROLLER_CLASSES_ID' limit 1" );
    if ($row = MYSQLi_FETCH_ROW ( $erg2 ))
      $controllerInstanceId = $row [0];
    else
      die ( "Fehler: Controller Instance zu controllerId $controllerId nicht gefunden" );
      
      // Threshold der Gruppe anhand der Member definieren
    if ($i < $nrNeededGroups - 1)
      $groupThreshold = 8;
    else
      $groupThreshold = $totalMembers % 8;
    
    $syncGroups [$i] ["controllerId"] = $controllerId;
    $syncGroups [$i] ["controllerInstanceId"] = $controllerInstanceId;
    $syncGroups [$i] ["groupThreshold"] = $groupThreshold;
    $syncGroups [$i] ["myGroupIndex"] = $myGroupIndex;
    
    // Nach der letzten normalen Gruppe erstellen wir noch die Schaltungsgruppe
    if ($i == $nrNeededGroups - 1 && $schachtelNeeded == 1)
    {
      $schachtelNeeded = 0;
      $nrNeededGroups ++;
    }
  }
  
  // Synchronisationsevents in Regeln eintragen
  $myOnStateId = "";
  $myOffStateId = "";
  
  // On und Off State aus der Gruppe auslesen
  $erg2 = QUERY ( "select ruleId,featureInstanceId,groupStates.basics as resultingBasics,groupStates.id as resultingStateId from ruleActions join rules on (rules.id=ruleActions.ruleId) left join groupStates on (groupStates.id=rules.resultingStateId) where rules.groupId='$groupId' order by ruleActions.id" );
  while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
  {
    if ($obj2->resultingBasics == "2") $myOnStateId = $obj2->resultingStateId;
    else if ($obj2->resultingBasics == "1") $myOffStateId = $obj2->resultingStateId;
  }
  
  // Pro Aktor ein Event f� On-Zustand und Off-Zustand generieren und damit Bit in Syncgruppe schalten
  $syncActorDone = "";
  $erg2 = QUERY ( "select ruleId,featureInstanceId,groupStates.name as resultingStateName,groupStates.id as resultingStateId from ruleActions join rules on (rules.id=ruleActions.ruleId) left join groupStates on (groupStates.id=rules.resultingStateId) where rules.groupId='$groupId' order by ruleActions.id" );
  while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
  {
    $myClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
    if ($myClassesId == $logicalButtonClassesId) continue;
    
    $actFeatureIndex = $indexList [$obj2->featureInstanceId];
    $actGroupIndex = ( int ) ($actFeatureIndex / 8);
    $controllerId = $syncGroups [$actGroupIndex] ["controllerId"];
    $controllerInstanceId = $syncGroups [$actGroupIndex] ["controllerInstanceId"];
    $groupThreshold = $syncGroups [$actGroupIndex] ["groupThreshold"];
    $myGroupIndex = $syncGroups [$actGroupIndex] ["myGroupIndex"];
    $actFeatureIndex = $indexList [$obj2->featureInstanceId] % 8;
    
    if ($syncActorDone [$obj2->featureInstanceId] != 1)
    {
      $syncActorDone [$obj2->featureInstanceId] = 1;
      
      // Dummy f�n
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) values('$groupId','7','31','255','7','31','255','0','0','evOn','1','1','1')" );
      $ruleId = query_insert_id ();
      
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$controllerInstanceId','169','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','326','$myGroupIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','327','$actFeatureIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','328','1','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','349','$groupThreshold','1')" );
      
      if ($myClassesId == $dimmerClassesId)
      {
        $evOnFunctionId = getClassesIdFunctionsIdByName ( $dimmerClassesId, "evOn" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$evOnFunctionId','1')" );
        $signalId = query_insert_id ();
        
        $dimmerParamBrightnessId = getClassesIdFunctionParamIdByName ( $dimmerClassesId, "evOn", "brightness" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$signalId','$dimmerParamBrightnessId','$signalParamWildcard','1')" );
      } else if ($myClassesId == $rolloClassesId)
      {
        $statusFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "evOpen" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$statusFunctionId','1')" );
      } else if ($myClassesId == $ledClassesId)
      {
        $evOnFunctionId = getClassesIdFunctionsIdByName ( $ledClassesId, "evOn" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$evOnFunctionId','1')" );
        $signalId = query_insert_id ();
        
        $dimmerParamBrightnessId = getClassesIdFunctionParamIdByName ( $ledClassesId, "evOn", "brightness" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$signalId','$dimmerParamBrightnessId','$signalParamWildcard','1')" );
      } else if ($myClassesId == $schalterClassesId)
      {
        $evOnFunctionId = getClassesIdFunctionsIdByName ( $schalterClassesId, "evOn" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$evOnFunctionId','1')" );
        
        $signalId = query_insert_id ();
        
        $schalterParamDurationId = getClassesIdFunctionParamIdByName ( $schalterClassesId, "evOn", "duration" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$signalId','$schalterParamDurationId','$signalParamWildcardWord','1')" );

      } else if ($myClassesId == 21)
      {
        echo "dummy f�ver fehlt noch <br>";
      } else if ($myClassesId == $tasterClassesId)
      {
        // hat keine Events
      } else
        die ( "nicht implementierte class $myClassesId -1 -> $obj2->ruleId" );
        
        // Dummy f�ff
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) values('$groupId','7','31','255','7','31','255','0','0','evOff','1','1','1')" );
      $ruleId = query_insert_id ();
      
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$controllerInstanceId','169','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','326','$myGroupIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','327','$actFeatureIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','328','0','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','349','$groupThreshold','1')" );
      
      if ($myClassesId == $dimmerClassesId)
      {
        $evOffFunctionId = getClassesIdFunctionsIdByName ( $dimmerClassesId, "evOff" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$evOffFunctionId','1')" );
      } else if ($myClassesId == $rolloClassesId)
      {
        $statusFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "evClosed" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$statusFunctionId','1')" );
        $signalId = query_insert_id ();
        
        $dimmerParamBrightnessId = getClassesIdFunctionParamIdByName ( $rolloClassesId, "evClosed", "position" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$signalId','$dimmerParamBrightnessId','$signalParamWildcard','1')" );
      } else if ($myClassesId == $ledClassesId)
      {
        $evOffFunctionId = getClassesIdFunctionsIdByName ( $ledClassesId, "evOff" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$evOffFunctionId','1')" );
      } else if ($myClassesId == $schalterClassesId)
      {
        $evOffFunctionId = getClassesIdFunctionsIdByName ( $ledClassesId, "evOff" );
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$obj2->featureInstanceId','$evOffFunctionId','1')" );
      } else if ($myClassesId == 21)
      {
        echo "dummy f�ver fehlt noch <br>";
      } else if ($myClassesId == $tasterClassesId)
      {
        // hat keine Events
      } else
        die ( "nicht implementierte class $myClassesId -2" );
    }
  }
  
  // Wenn mehr als eine Gruppe n� war, haben wir geschachtelt.
  // Dann hier die Subevents eintragen und sp㳥r die Gesamtgruppe als Eventgeber eintragen
  if ($nrNeededGroups > 1)
  {
    $globalGroupIndex = $syncGroups [$nrNeededGroups - 1] ["myGroupIndex"];
    $globalControllerInstanceId = $syncGroups [$nrNeededGroups - 1] ["controllerInstanceId"];
    
    $nrSubGroups = $nrNeededGroups - 1;
    
    // Synchronisationsevents f� Subgruppen eintragen
    for($i = 0; $i < $nrSubGroups; $i ++)
    {
      $myGroupIndex = $syncGroups [$i] ["myGroupIndex"];
      $controllerInstanceId = $syncGroups [$i] ["controllerInstanceId"];
      
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated) values('$groupId','7','31','255','7','31','255','0','0','evOn','1','1')" );
      $actRuleId = query_insert_id ();
      
      if ($completeGroupFeedback != 1)
      {
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupUndefinedFunctionId','1')" );
        $actSignalId = query_insert_id ();
        $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      }
      
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupOnFunctionId','1')" );
      $actSignalId = query_insert_id ();
      $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn", "index" );
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$globalControllerInstanceId','169','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','326','$globalGroupIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','327','$i','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','328','1','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','349','$nrSubGroups','1')" );
      
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated) values('$groupId','7','31','255','7','31','255','0','0','evOff','1','1')" );
      $actRuleId = query_insert_id ();
      
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupOffFunctionId','1')" );
      $actSignalId = query_insert_id ();
      $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff", "index" );
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      
      if ($completeGroupFeedback == 1)
      {
        QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupUndefinedFunctionId','1')" );
        $actSignalId = query_insert_id ();
        $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
        QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
      }
      
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$globalControllerInstanceId','169','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','326','$globalGroupIndex','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','327','$i','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','328','0','1')" );
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','349','$nrSubGroups','1')" );
    }
    
    $myGroupIndex = $globalGroupIndex;
    $controllerInstanceId = $globalControllerInstanceId;
  }
  
  // Dann die Events der Synchronisationsgruppe eintragen um damit die Gruppenstates zu schalten
  // $evGroupOnFunctionId $evGroupOffFunctionId $evGroupUndefinedFunctionId
  QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated) values('$groupId','7','31','255','7','31','255','$myOffStateId','$myOnStateId','evOn','1','1')" );
  $actRuleId = query_insert_id ();
  $onRuleId = $actRuleId;
  
  QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupOnFunctionId','1')" );
  $actSignalId = query_insert_id ();
  $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOn", "index" );
  QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
  
  QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated) values('$groupId','7','31','255','7','31','255','$myOnStateId','$myOffStateId','evOff','1','1')" );
  $actRuleId = query_insert_id ();
  $offRuleId = $actRuleId;
  
  QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupOffFunctionId','1')" );
  $actSignalId = query_insert_id ();
  $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupOff", "index" );
  QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
  
  // Was Signal undefined bewirken soll, h㭧t davon ab welches feedback gew� war
  if ($completeGroupFeedback == 1)
    $actRuleId = $offRuleId;
  else
    $actRuleId = $onRuleId;
  
  QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated) values('$actRuleId','$controllerInstanceId','$evGroupUndefinedFunctionId','1')" );
  $actSignalId = query_insert_id ();
  $indexParamId = getClassesIdFunctionParamIdByName ( $CONTROLLER_CLASSES_ID, "evGroupUndefined", "index" );
  QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) values('$actSignalId','$indexParamId','$myGroupIndex','1')" );
}

function generateRotation($groupId)
{
  $firstInstance = "";
  $secondInstance = "";
  $erg = QUERY ( "select featureInstanceId from groupFeatures where groupId='$groupId' order by id" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    if ($firstInstance == "") $firstInstance = $row [0];
    else if ($secondInstance == "") $secondInstance = $row [0];
    else
    {
      echo "Rotation momentan nur mit 2 Aktoren implementiert. Ignoriere Rotation in Gruppe $groupId ... <br>";
      return;
    }
  }
  
  if ($firstInstance == "" || $secondInstance == "")
  {
    echo "Rotation momentan nur mit 2 Aktoren implementiert. Ignoriere Rotation in Gruppe $groupId ... <br>";
    return;
  }
  
  QUERY ( "INSERT into groupStates (groupId,name, value,basics,generated) values ('$groupId','1an','3','0','1')" );
  $firstOnStateId = query_insert_id ();
  QUERY ( "INSERT into groupStates (groupId,name, value,basics,generated) values ('$groupId','2an','4','0','1')" );
  $secondOnStateId = query_insert_id ();
  
  // Standardstates suchen
  $erg = QUERY ( "select id from groupStates where groupId='$groupId' and basics='1' limit 1" );
  $row = MYSQLi_FETCH_ROW ( $erg );
  $ausState = $row [0];
  
  $erg = QUERY ( "select id from groupStates where groupId='$groupId' and basics='2' limit 1" );
  $row = MYSQLi_FETCH_ROW ( $erg );
  $anState = $row [0];
  
  // Regel suchen, die anschaltet
  $erg = QUERY ( "select rules.id from rules join groupStates as activation on (activation.id = rules.activationStateId) left join groupStates as resulting on (resulting.id = rules.resultingStateId) where activation.basics=1 and resulting.basics=2 and rules.groupId='$groupId' limit 1" );
  $row = MYSQLi_FETCH_ROW ( $erg );
  $origRuleId = $row [0];
  
  QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) 
                     values('$groupId','7','31','255','7','31','255','$ausState','$firstOnStateId','on','1','0','1')" );
  $ruleId = query_insert_id ();
  
  $erg = QUERY ( "select * from ruleSignals where ruleId='$origRuleId' order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
  	                        values('$ruleId','$obj->featureInstanceId','$obj->featureFunctionId','1')" );
    $newSignalId = query_insert_id ();
    
    $erg2 = QUERY ( "select * from ruleSignalParams where ruleSignalId='$obj->id' order by id" );
    while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
    {
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                    values('$newSignalId','$obj2->featureFunctionParamsId','$obj2->paramValue','1')" );
    }
  }
  
  $erg = QUERY ( "select * from ruleActions where ruleId='$origRuleId' order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    if ($obj->featureInstanceId == $firstInstance)
    {
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) 
                                values('$ruleId','$obj->featureInstanceId','$obj->featureFunctionId','1')" );
      $newActionId = query_insert_id ();
      
      $erg2 = QUERY ( "select * from ruleActionParams where ruleActionId='$obj->id' order by id" );
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
        QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) 
                                      values('$newActionId','$obj2->featureFunctionParamsId','$obj2->paramValue','1')" );
      }
    }
  }
  
  QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) 
                     values('$groupId','7','31','255','7','31','255','$firstOnStateId','$secondOnStateId','on','1','0','1')" );
  $ruleId = query_insert_id ();
  
  $erg = QUERY ( "select * from ruleSignals where ruleId='$origRuleId' order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
  	                        values('$ruleId','$obj->featureInstanceId','$obj->featureFunctionId','1')" );
    $newSignalId = query_insert_id ();
    
    $erg2 = QUERY ( "select * from ruleSignalParams where ruleSignalId='$obj->id' order by id" );
    while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
    {
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
                                    values('$newSignalId','$obj2->featureFunctionParamsId','$obj2->paramValue','1')" );
    }
  }
  
  $erg = QUERY ( "select * from ruleActions where ruleId='$origRuleId' order by id" );
  while ( $obj = MYSQLi_FETCH_OBJECT ( $erg ) )
  {
    if ($obj->featureInstanceId == $secondInstance)
    {
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) 
                                values('$ruleId','$obj->featureInstanceId','$obj->featureFunctionId','1')" );
      $newActionId = query_insert_id ();
      
      $erg2 = QUERY ( "select * from ruleActionParams where ruleActionId='$obj->id' order by id" );
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
        QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) 
                                      values('$newActionId','$obj2->featureFunctionParamsId','$obj2->paramValue','1')" );
      }
    }
  }
  
  // States der originalRegel auswechseln
  QUERY ( "update rules set activationStateId='$secondOnStateId' where id='$origRuleId'" );
  
  // Zweiten Aktor rauswerfen
  $erg = QUERY ( "select id from ruleActions where ruleId='$origRuleId' and featureInstanceId='$secondInstance' limit 1" );
  $row = MYSQLi_FETCH_ROW ( $erg );
  deleteRuleAction ( $row [0], 0);
  
  // ersten Aktor ausschalten
  // Regel suchen, die ausschaltet
  $erg = QUERY ( "select rules.id from rules join groupStates as activation on (activation.id = rules.activationStateId) left join groupStates as resulting on (resulting.id = rules.resultingStateId) where activation.basics=2 and resulting.basics=1 and rules.groupId='$groupId' limit 1" );
  $row = MYSQLi_FETCH_ROW ( $erg );
  $origRuleId = $row [0];
  
  $erg = QUERY ( "select * from ruleActions where ruleId='$origRuleId' and featureInstanceId='$firstInstance' limit 1" );
  $obj = MYSQLi_FETCH_OBJECT ( $erg );
  
  QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) 
                           values('$ruleId','$obj->featureInstanceId','$obj->featureFunctionId','1')" );
  $newActionId = query_insert_id ();
  
  $erg2 = QUERY ( "select * from ruleActionParams where ruleActionId='$obj->id' order by id" );
  while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
  {
    QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) 
                                     values('$newActionId','$obj2->featureFunctionParamsId','$obj2->paramValue','1')" );
  }
}
function showError($ledInstanceId, $basicRuleId, $groupId, $signalInstanceId)
{
  if (substr ( $ledInstanceId, 0, 1 ) == "E")
  {
    $error = substr ( $ledInstanceId, 1 );
    if (strpos ( $error, "offline" ) === FALSE)
    {
      echo "<hr>";
      echo "Fehler beim erstellen des LED-Feedbacks eines Aktors<br>";
      echo "Fehler: $error <br>";
      echo "Problemsignal: " . formatInstance ( $signalInstanceId ) . "<br>";
      echo "Problemregel: <a href='editBaseConfig.php?groupId=$groupId' target='_blank'>anzeigen</a> <br>";
      echo "<hr>";
    }
    return TRUE;
  }
  return FALSE;
}
function showRuleError($message, $groupId)
{
  echo "<hr>";
  echo "Fehler: $message <br>";
  echo "Problemregel: <a href='editBaseConfig.php?groupId=$groupId' target='_blank'>anzeigen</a> <br>";
  echo "<hr>";
}
function formatInstance($instanceId)
{
  $obj = getFeatureInstanceData ( $instanceId );
  if ($obj->roomName != "")
    $result = $obj->roomName . " » ";
  else
    $result = "Controller " . $obj->controllerName . " » ";
  $result .= $obj->featureInstanceName;
  return $result;
}

function getFeatureInstanceData($instanceId)
{
  $erg = QUERY ( "select featureInstances.name as featureInstanceName, featureInstances.objectId as objectId,
	                      controller.name as controllerName, 
	                      rooms.name as roomName,
	                      featureClasses.name as featureClassName
	                      from featureInstances 
	                      join controller on (controller.id = featureInstances.controllerId)
	                      join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
	                      left join roomFeatures on (roomFeatures.featureInstanceId = featureInstances.id)
	                      left join rooms on (rooms.id = roomFeatures.roomId)
	                      where featureInstances.id = $instanceId limit 1" );
  if ($obj = MYSQLi_FETCH_OBJECT ( $erg )) return $obj;
  else return "Fehler: Instance $instanceId unbekannt";
}
function getLedForTaster($actInstanceId)
{
  global $lastLogId;
  
  $ledClassesId = getClassesIdByName ( "Led" );
  
  // Zum Taster den LogicalButton suchen
  $erg2 = QUERY ( "select parentInstanceId,objectid from featureInstances where id = '$actInstanceId' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg2 ))
  {
    $parentInstanceId = $row [0];
    if ($parentInstanceId == 0) return "EKeine ParentInstanceId gefunden zu Tasterinstanz $actInstanceId. (Kein LogicalButton erstellt?)";
    $myObjectId = $row [1];
    $myInstance = getInstanceId ( $myObjectId );
    
    $erg2 = QUERY ( "select featureInstances.objectid,online,controller.name from featureInstances join controller on (controller.id=featureInstances.controllerId) where featureInstances.id = '$parentInstanceId' limit 1" );
    if ($row = MYSQLi_FETCH_ROW ( $erg2 ))
    {
      if ($row [1] == 0)
        return "EController " . $row [2] . " offline";
      
      $parentObjectId = $row [0];
      
      // Dann schauen bei welchem Index in der Konfiguration die InstanzId des Tasters eingetragen ist
      $erg2 = QUERY ( "select functionData from lastReceived where senderObj = '$parentObjectId' and function='Configuration' and type='RESULT' order by id desc limit 1" );
      if ($row = MYSQLi_FETCH_ROW ( $erg2 ))
        $functionData = unserialize ( $row [0] );
      else
      {
        callObjectMethodByName ( $parentObjectId, "getConfiguration" );
        $result = waitForObjectResultByName ( $parentObjectId, 5, "Configuration", $lastLogId, "funtionDataParams", 0 );
        if ($result == - 1)
        {
          echo "Keine Antwort beim lesen der LogicalButton-Konfiguration zu Objekt $parentObjectId. Wiederhole....";
          callObjectMethodByName ( $parentObjectId, "getConfiguration" );
          $result = waitForObjectResultByName ( $parentObjectId, 5, "Configuration", $lastLogId, "funtionDataParams", 0 );
          if ($result == - 1)
            return "EKeine Antwort beim lesen der LogicalButton-Konfiguration zu Objekt $parentObjectId. Abbruch!";
        }
        $erg2 = QUERY ( "select functionData from lastReceived where senderObj = '$parentObjectId' and function='Configuration' and type='RESULT' order by id desc limit 1" );
        $row = MYSQLi_FETCH_ROW ( $erg2 );
        $functionData = unserialize ( $row [0] );
      }
      
      $buttonIndex = 0;
      foreach ( $functionData->paramData as $actSearchParam )
      {
        if (strpos ( $actSearchParam->name, "button" ) !== FALSE && $actSearchParam->dataValue == $myInstance)
        {
          $buttonIndex = substr ( $actSearchParam->name, strpos ( $actSearchParam->name, "button" ) + 6 );
          break;
        }
      }
      
      // Dann InstanzId der LED zum zugeh�en Index suchen
      if ($buttonIndex > 0)
      {
        $ledInstance = 0;
        foreach ( $functionData->paramData as $actSearchParam )
        {
          if ($actSearchParam->name == "led" . $buttonIndex)
          {
            $ledInstance = $actSearchParam->dataValue;
            break;
          }
        }
        
        if ($ledInstance > 0)
        {
          $erg2 = QUERY ( "select id,objectId from featureInstances where parentInstanceId='$parentInstanceId'" );
          while ( $row = MYSQLi_FETCH_ROW ( $erg2 ) )
          {
            $actClassesId = getClassesIdByFeatureInstanceId ( $row [0] );
            
            // echo $actClassesId." -> ".$ledClassesId.", ".getInstanceId($row[1])." -> $ledInstance <br>";
            
            // Dann die LED als Feedback verwenden
            if ($actClassesId == $ledClassesId && getInstanceId ( $row [1] ) == $ledInstance)
              return $row [0];
          }
          return "ELED Instanz $ledInstance nicht in featureInstances gefunden.";
        } else
          return "ELED Instanz nicht gefunden <br>Pr�ie die Konfiguration des LogicalButton: <a href='editFeatureInstance.php?id=$parentInstanceId' target='_blank'>" . formatInstance ( $parentInstanceId ) . "</a>";
      } else
        return "EButton Index nicht gefunden. <br>Pr�ie die Konfiguration des LogicalButton: <a href='editFeatureInstance.php?id=$parentInstanceId' target='_blank'>" . formatInstance ( $parentInstanceId ) . "</a>";
    } else
      return "EObjectId zum Taster Id $parentInstanceId nicht gefunden <br>Pr�ie die Konfiguration des LogicalButton: <a href='editFeatureInstance.php?id=$parentInstanceId' target='_blank'>" . formatInstance ( $parentInstanceId ) . "</a>";
  } else
    return "ESignal ID $actInstanceId nicht gefunden <br>";
}

function getSyncGroupIdForSignalInstanceId($actSignalInstanceId)
{
  // Member raussuchen, die von diesem Signal getriggert werden
  $myMembers = "";
  $first = "";
  $ledClassesId = getClassesIdByName ( "Led" );
  $erg = QUERY ( "select distinct ruleActions.featureInstanceId,featureClasses.id from ruleActions join rules on (rules.id=ruleActions.ruleId) join ruleSignals on (ruleSignals.ruleId=rules.id) join featureInstances on (featureInstances.id=ruleActions.featureInstanceId) join featureClasses on (featureClasses.id=featureInstances.featureClassesId) where ruleSignals.featureInstanceId='$actSignalInstanceId' and not (activationStateId=0 and resultingStateId=0 and completeGroupFeedback=0) order by ruleActions.featureInstanceId" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    if ($row [1] == $ledClassesId)
      continue; // leds werden nicht gruppiert und nicht synchronisiert
    
    if ($first == "")
      $first = $row [0];
    $myMembers .= $row [0] . "-";
  }
  if ($myMembers == $first . "-" || $myMembers == "")
    return - 2;
    
    // Gruppe zu diesen Features suchen
  $myGroup = - 1;
  $erg = QUERY ( "select groupId from groupFeatures join groups on (groups.id=groupFeatures.groupId) where featureInstanceId='$first'" ); // and groups.generated=1
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    $actGroupMembers = "";
    $erg2 = QUERY ( "select featureInstanceId from groupFeatures where groupId='$row[0]' order by featureInstanceId" );
    while ( $row2 = MYSQLi_FETCH_ROW ( $erg2 ) )
    {
      $actGroupMembers .= $row2 [0] . "-";
    }
    
    if ($myMembers == $actGroupMembers)
    {
      $myGroup = $row [0];
      break;
    }
  }
  
  if ($myGroup != - 1)
    return $myGroup;
  else
    echo "Fehler: Multigruppe zu SignalId $actSignalInstanceId und Members $myMembers nicht gefunden. <br>select distinct ruleActions.featureInstanceId from ruleActions join rules on (rules.id=ruleActions.ruleId) join ruleSignals on (ruleSignals.ruleId=rules.id) where ruleSignals.featureInstanceId='$actSignalInstanceId' order by ruleActions.featureInstanceId <br>";
}
function getBasicStateNames($featureClassesId)
{
  $dimmerClassesId = getClassesIdByName ( "Dimmer" );
  $rolloClassesId = getClassesIdByName ( "Rollladen" );
  $ledClassesId = getClassesIdByName ( "Led" );
  $schalterClassesId = getClassesIdByName ( "Schalter" );
  
  $result = "";
  
  if ($featureClassesId == $dimmerClassesId)
  {
    $result->offName = "aus";
    $result->onName = "an";
  } else if ($featureClassesId == $rolloClassesId)
  {
    $result->offName = "zu";
    $result->onName = "auf";
  } else if ($featureClassesId == $ledClassesId)
  {
    $result->offName = "aus";
    $result->onName = "an";
  } else if ($featureClassesId == $schalterClassesId)
  {
    $result->offName = "aus";
    $result->onName = "an";
  } else
  {
    $result->offName = "aus";
    $result->onName = "an";
  }
  
  return $result;
}

// getWeather( getWoeID( "Deutschland", 33758 ) ); // SHS
// getWeather( 12833587 ); // SHS
// @deprecated
function getWeather($woeid)
{
  $cxContext = getStreamContext ();
  $api = simplexml_load_string ( str_replace ( "yweather:", "", utf8_encode ( file_get_contents ( "http://weather.yahooapis.com/forecastrss?w=" . $woeid . "&u=c", false, getStreamContext () ) ) ) );
  $json = json_encode ( $api );
  $arr = json_decode ( $json, TRUE );
  
  $wetter = array ();
  $wetter ['humidity'] = $arr ["channel"] ["atmosphere"] ["@attributes"] ["humidity"];
  $wetter ['visibility'] = $arr ["channel"] ["atmosphere"] ["@attributes"] ["visibility"];
  $wetter ['pressure'] = $arr ["channel"] ["atmosphere"] ["@attributes"] ["pressure"];
  $wetter ['sunrise'] = $arr ["channel"] ["astronomy"] ["@attributes"] ["sunrise"];
  $wetter ['sunset'] = $arr ["channel"] ["astronomy"] ["@attributes"] ["sunset"];
  $wetter ['text'] = $arr ["channel"] ["item"] ["condition"] ["@attributes"] ["text"];
  $wetter ['temp'] = $arr ["channel"] ["item"] ["condition"] ["@attributes"] ["temp"];
  return $wetter;
}

// @deprecated
function getWoeID()
{
  $erg = QUERY ( "select paramValue, paramKey from basicConfig where paramKey='locationZipCode' or paramKey='locationCountry' limit 2" );
  while ( $row = MYSQLi_FETCH_ROW ( $erg ) )
  {
    if ($row [1] == "locationZipCode")
      $zipCode = $row [0];
    if ($row [1] == "locationCountry")
      $country = $row [0];
  }
  
  if ($country == "")
    return - 1;
  
  $req = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20geo.places%20where%20text%3D%22" . $country . "%20" . $zipCode . "%22&format=xml";
  $api = simplexml_load_string ( utf8_encode ( file_get_contents ( $req, false, getStreamContext () ) ) );
  return $api->results->place->woeid;
}

function isWindows()
{
  // if (!isset($_SESSION["myOS"]))
  {
    ob_start ();
    phpInfo ( INFO_GENERAL );
    $pinfo = ob_get_contents ();
    ob_end_clean ();
    $pos = strpos ( $pinfo, "System" );
    if ($pos === "FALSE")
      echo "Betriebssystem konnte nicht ermittelt werden <br>";
    else
    {
      $check = strtolower ( substr ( $pinfo, $pos, strpos ( $pinfo, "</tr>", $pos ) - $pos ) );
      if (strpos ( $check, "windows" ) !== FALSE)
        $_SESSION ["myOS"] = "windows";
      else
        $_SESSION ["myOS"] = "not_windows";
    }
  }
  
  if ($_SESSION ["myOS"] == "windows")
    return TRUE;
  return FALSE;
}

function triggerTreeUpdate()
{
  forceTreeUpdate();
}

function liveOut($newOut, $withBr=1)
{
	if ($withBr==1) echo "<script>document.getElementById('updateArea').innerHTML=document.getElementById('updateArea').innerHTML+'$newOut'+'<br>';</script>";
  else echo "<script>document.getElementById('updateArea').innerHTML=document.getElementById('updateArea').innerHTML+'$newOut';</script>";
  flushIt ();
  // flush();
  // ob_flush();
}

function treeStatusOut($newOut)
{
  echo "<script>top.frames[0].document.getElementById('treeUpdateArea').innerHTML='$newOut';</script>";
  flushIt ();
}

function checkAndSetTimeZone()
{
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'timeZone' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) date_default_timezone_set ( $row [0] );
  else if (strcmp ( date_default_timezone_get (), ini_get ( 'date.timezone' ) )) echo "Zeitzone muss eingestellt werden unter Weitere Einstellungen -> Standorteinstellungen ! \n\n\n";
}

function readDauerWithTimebase($objectId, $dauer)
{
  global $configCache;
  
  if ($configCache [$objectId] == "")
  {
    $erg = QUERY ( "select functionData from lastReceived  where senderObj='$objectId' and type='RESULT' and function='Configuration' order by id desc limit 1" );
    if ($row = MYSQLi_FETCH_ROW ( $erg )) $configCache [$objectId] = unserialize ( $row [0] )->paramData [2]->dataValue;
    else
    {
      $erg = QUERY ( "select online from controller join featureInstances on (featureInstances.controllerId=controller.id) where featureInstances.objectId='$objectId' limit 1" );
      if (($row = MYSQLi_FETCH_ROW ( $erg )) && $row[0]==0)
      {
    	  echo "Controller for objectId $objectId ist offline. Verwendet 1000 als Timebase <br>";
        $configCache [$objectId] = 1000;
      }
      else
      {
        $result = callObjectMethodByNameAndRecover ( $objectId, "getConfiguration", "", "Configuration", 3, 2, 0 );
        if ($result == - 1)
        {
          echo "Controller offline. Verwendet 1000 als Timebase <br>";
          $configCache [$objectId] = 1000;
        } else
        {
          $configCache [$objectId] = $result [2]->dataValue;
          if ($configCache [$objectId] == 0)
          {
            echo "Achtung: Timebase von 0 gefunden bei ObjectId: $objectId -> verwendet 1000 stattdesse <br>";
            $configCache [$objectId] = 1000;
          }
        }
      }
    }
  }
  
  $timebase = $configCache [$objectId];
  $dauer = $dauer * 1000 / $timebase;
  
  return $dauer;
}
function getStreamContext()
{
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'proxy' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    $myProxy = $row [0];
  
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'proxyPort' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg ))
    $myProxyPort = $row [0];
  
  if ($con = @fsockopen ( $myProxy, $myProxyPort, $eroare, $eroare_str, 3 ))
  {
    $uri = 'tcp://' . $myProxy . ':' . $myProxyPort;
    $aContext = array (
        'http' => array (
            'proxy' => $uri,
            'request_fulluri' => true 
        ) 
    );
    fclose ( $con ); // Close the socket handle
    return stream_context_create ( $aContext );
  }
  return null;
}

function getNetworkIp()
{
  global $UDP_NETWORK_IP;
  
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'networkIp' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $myNetwork = $row [0];
  
  if (filter_var ( $myNetwork, FILTER_VALIDATE_IP )) $UDP_NETWORK_IP = $myNetwork;
  
  return $UDP_NETWORK_IP;
}

function getNetworkMask()
{
  global $UDP_NETWORK_MASK;
  
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'networkMask' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $myNetworkMask = $row [0];
  
  if (filter_var ( $myNetworkMask, FILTER_VALIDATE_IP )) $UDP_NETWORK_MASK = $myNetworkMask;
  
  return $UDP_NETWORK_MASK;
}

function getNetworkPort()
{
  global $UDP_PORT;
  
  $erg = QUERY ( "select paramValue from basicConfig where paramKey = 'networkPort' limit 1" );
  if ($row = MYSQLi_FETCH_ROW ( $erg )) $UDP_PORT = $row [0];
}

function setupNetwork()
{
  global $UDP_NETWORK_IP;
  global $UDP_NETWORK_MASK;
  global $UDP_BCAST_IP;
  
  // update config from database
  getNetworkIp();
  getNetworkPort();
  getNetworkMask();
  
  // calculate UDP_BCAST_IP
  // Network is a logical AND between the address and netmask
  $netmask_int = ip2long($UDP_NETWORK_MASK);
  $network_int = ip2long($UDP_NETWORK_IP) & $netmask_int;
  // Broadcast is a logical OR between the address and the NOT netmask
  $broadcast_int = $network_int | (~ $netmask_int);
  $UDP_BCAST_IP = long2ip($broadcast_int);  
}


function forceTreeUpdate()
{
	 QUERY("INSERT into treeUpdateHelper values('1')");
}

function generateHeizungsSteuerung($groupId, $basicRuleObj, $offState, $onState)
{
	  global $rolloClassesId;
	  $moveToPositionFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "moveToPosition" );
	  
	  $basicRuleId = $basicRuleObj->id;
	  
	  $erg = QUERY("select featureInstanceId from groupFeatures where groupId='$groupId' limit 1") or die(MYSQL_ERROR());
	  if ($row=MYSQLi_FETCH_ROW($erg)) $myInstanceId = $row[0];
	  else
	  {
	  	 echo "Kein Aktor bei Heizungssteuerungsregeln in Gruppe $groupId gefunden<br>";
	  	 return;
	  }

	  $data = getFeatureInstanceData($myInstanceId);

    $tempStatusEvent = getClassesIdFunctionsIdByName(2, "Status");
    $lastEventParamId = getClassesIdFunctionParamIdByName ( 2, "Status", "lastEvent" );
    $celsiusParamId = getClassesIdFunctionParamIdByName ( 2, "Status", "celsius" );
    $centiCelsiusParamId = getClassesIdFunctionParamIdByName ( 2, "Status", "centiCelsius" );
    $coldEnumValue = getFunctionParamEnumValueForClassesIdByName ( 2, "Status", "lastEvent", "COLD" );
    $warmEnumValue = getFunctionParamEnumValueForClassesIdByName ( 2, "Status", "lastEvent", "WARM" );
    $hotEnumValue = getFunctionParamEnumValueForClassesIdByName ( 2, "Status", "lastEvent", "HOT" );
	  
	  // Ventilsteuerung
	  if ($data->featureClassName=="Rollladen")
	  {
	  	// Zusatzstates erstellen
	  	QUERY("INSERT into groupStates (groupId, name, value, basics, generated) values('$groupId', '0 Prozent','10', '0', '1')");
	  	$geschlossenState = query_insert_id();
	  	QUERY("INSERT into groupStates (groupId, name, value, basics, generated) values('$groupId', '25 Prozent','25', '0', '1')");
	  	$firstState = query_insert_id();
	  	QUERY("INSERT into groupStates (groupId, name, value, basics, generated) values('$groupId', '50 Prozent','50', '0', '1')");
	  	$secondState = query_insert_id();
	  	QUERY("INSERT into groupStates (groupId, name, value, basics, generated) values('$groupId', '75 Prozent','75', '0', '1')");
	  	$thirdState = query_insert_id();
	  	
	  	// 1. von Aus nach 25 %
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$geschlossenState','$firstState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','75','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$coldEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }
      
      // 2. von 25% nach 50 %
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$firstState','$secondState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','50','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$coldEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }
      
      // 3. von 50% nach 75 %
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$secondState','$thirdState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','25','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$coldEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }
        
      // 4. von 75% nach 100 %
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$thirdState','$onState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','0','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$coldEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }		 
      
      // 5. von 100% nach 75%
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$onState','$thirdState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','25','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$hotEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }	                                 
      
      // 6. von 75% nach 50%
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$thirdState','$secondState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','50','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$hotEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }	 
      
      // 6. von 50% nach 25%
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$secondState','$firstState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','75','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$hotEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }	 
      
      // 7. von 25% nach 0%
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$firstState','$geschlossenState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','100','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$hotEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }
      
      // 8. Initialzustand von zu -> geschlossen
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$offState','$geschlossenState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','$moveToPositionFunctionId','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','102','100','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','255','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }
      
      // Dummy für evOn
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) 
                         values('$groupId','7','31','255','7','31','255','0','$onState','evOn','1','1','1')" );
      $ruleId = query_insert_id ();
      $statusFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "evOpen" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                 values('$ruleId','$myInstanceId','$statusFunctionId','1')" );
        
        // Dummy für off
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,syncEvent,generated) 
                         values('$groupId','7','31','255','7','31','255','0','$geschlossenState','evOff','1','1','1')" );
      $ruleId = query_insert_id ();

      $statusFunctionId = getClassesIdFunctionsIdByName ( $rolloClassesId, "evClosed" );
      QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated)
                                values('$ruleId','$myInstanceId','$statusFunctionId','1')" );
      $signalId = query_insert_id ();
      $positionParamId = getClassesIdFunctionParamIdByName ( $rolloClassesId, "evClosed", "position" );
      QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue) 
                                      values('$signalId','$positionParamId','100')" );	  
      
      return true; // Syncevents überspringen
	  }
	  // Schaltersteuerung
	  else if ($data->featureClassName=="Schalter")
	  {
	  	// 1. von aus nach an
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$geschlossenState','$onState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','60','1')" );
      $newRuleActionId = query_insert_id ();
      QUERY ( "INSERT into ruleActionParams (ruleActionId,featureFunctionParamsId,paramValue,generated) values('$newRuleActionId','265','0','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$coldEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }	 
      
      // 2. von an nach aus
      QUERY ( "INSERT into rules (groupId,startDay,startHour,startMinute,endDay,endHour,endMinute,activationStateId,resultingStateId,signalType,baseRule,generated,extras,intraDay) 
                           values('$groupId','$basicRuleObj->startDay','$basicRuleObj->startHour','$basicRuleObj->startMinute','$basicRuleObj->endDay','$basicRuleObj->endHour','$basicRuleObj->endMinute','$onState','$geschlossenState','click','1','1','$basicRuleObj->extras','$basicRuleObj->intraDay')" );
      $ruleId = query_insert_id ();
      
      // actions
      QUERY ( "INSERT into ruleActions (ruleId,featureInstanceId,featureFunctionId,generated) values('$ruleId','$myInstanceId','61','1')" );

      // signal
      $erg2 = QUERY ("select featureInstanceId from basicRuleSignals where ruleId='$basicRuleId'");
      while ( $obj2 = MYSQLi_FETCH_OBJECT ( $erg2 ) )
      {
         if ($obj2->featureInstanceId < 0)  continue;
         $signalClassesId = getClassesIdByFeatureInstanceId ( $obj2->featureInstanceId );
         if ($signalClassesId!=2) continue;

         // warm
         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$warmEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );

         // hot
         QUERY ( "INSERT into ruleSignals (ruleId,featureInstanceId,featureFunctionId,generated,completeGroupFeedback)
                                    values('$ruleId','$obj2->featureInstanceId','$tempStatusEvent','1','')" );
         $ruleSignalId = query_insert_id ();
                 
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$lastEventParamId','$hotEnumValue','1')" );

         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
   		                                  values('$ruleSignalId','$celsiusParamId','255','1')" );
   		                                  
         QUERY ( "INSERT into ruleSignalParams (ruleSignalId,featureFunctionParamsId,paramValue,generated) 
        	                              values('$ruleSignalId','$centiCelsiusParamId','255','1')" );
         
         break;
      }	 
	  }
	  else
	  {
	  	echo "Unbekannter Aktor für Heizungssteuerung -> ".$data->featureClassName."<br>";
	  	return;
	  }
	  
}

function checkRemoveUnusedHeatingRules()
{
	 $erg = QUERY("select basicRules.id as basicRuleId, featureInstanceId, heating.id from basicRules join groupFeatures on (groupFeatures.groupId=basicRules.groupId) left join heating on (heating.relay = groupFeatures.featureInstanceId) where extras='Heizungssteuerung'") or die(MYSQL_ERROR());
	 while($obj=MYSQLi_FETCH_OBJECT($erg))
	 {
	 	  if ($obj->id==null) deleteBaseRule($obj->basicRuleId,0);
	 }
}
?>
