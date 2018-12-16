<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/homeserver/include/all.php';

$classesId=12;
$functionId=128;
$functionsId = getOrCreateFunction($classesId, "ModuleId", $functionId, "RESULT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "firmwareId", "ENUM", "","Standard");
getOrCreateFunctionEnum($functionsId, $paramsId, "SONOFF", 5);
getOrCreateFunctionEnum($functionsId, $paramsId, "ESP Bridge", 6);
QUERY("UPDATE featurefunctionenums set name='S0 Reader' where paramId='$paramsId' and name='ESP Bridge' limit 1");



createConfigParam("current1d",0);
createConfigParam("current7d",0);
createConfigParam("current30d",0);

createConfigParam("webRollo1",15);
createConfigParam("webRollo2",30);
createConfigParam("webRollo3",60);
createConfigParam("webRollo4",85);

createConfigParam("webDimmer1",15);
createConfigParam("webDimmer2",30);
createConfigParam("webDimmer3",60);
createConfigParam("webDimmer4",85);

createConfigParam("webRoomTemp",1);
createConfigParam("webRoomHumidity",1);


//// TcpClient ////
$classId=91;
$classesId=32;
getOrCreateClass($classId, "TcpClient", $classesId, "Standard");

$functionId=1;
$functionsId = getOrCreateFunction($classesId, "announceServer", $functionId, "ACTION", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP0", "BYTE",'',"Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP1", "BYTE",'',"Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP2", "BYTE",'',"Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP3", "BYTE",'',"Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "port", "WORD",'',"Standard");


$functionId=2;
$functionsId = getOrCreateFunction($classesId, "getCurrentIp", $functionId, "FUNCTION", "Standard");

$functionId=128;
$functionsId = getOrCreateFunction($classesId, "CurrentIp", $functionId, "RESULT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP0", "BYTE",'',"Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP1", "BYTE",'',"Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP2", "BYTE",'',"Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "IP3", "BYTE",'',"Standard");

$functionId=200;
$functionsId = getOrCreateFunction($classesId, "evWhoIsServer", $functionId, "EVENT", "Standard");


//// CurrentReader ////
$classId=90;
$classesId=31;
getOrCreateClass($classId, "CurrentReader", $classesId, "Standard");

$functionId=3;
$functionsId = getOrCreateFunction($classesId, "setConfiguration", $functionId, "FUNCTION", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "config", "BITMASK",'',"Standard");
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableSignalEvent", 0);
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableCurrentEvent", 1);
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableInterruptEvent", 2);
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableDebugEvent", 3);
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 4);
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 5);
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 6);
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 7);

$paramsId = getOrCreateFunctionParam($functionsId, "impPerKwh", "WORD", "Anzahl Signale pro kWh","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "startCurrent", "DWORD", "Startwert Stromverbrauch in Wattstunden","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "currentReportInterval", "WORD", "Interval in Sekunden nach dem immer der aktuelle Gesamtstromverbrauch gemeldet wird","Standard");


$functionId=4;
$functionsId = getOrCreateFunction($classesId, "getConfiguration", $functionId, "FUNCTION", "Standard");

$functionId=129;
$functionsId = getOrCreateFunction($classesId, "Configuration", $functionId, "RESULT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "config", "BITMASK", "","Standard");
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableSignalEvent", 0);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableCurrentEvent", 1);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableInterruptEvent", 2);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "enableDebugEvent", 3);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 4);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 5);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 6);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "", 7);  
$paramsId = getOrCreateFunctionParam($functionsId, "impPerKwh", "WORD", "Anzahl Signale pro kWh","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "startCurrent", "DWORD", "Startwert Stromverbrauch in Wattstunden","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "currentReportInterval", "WORD", "Interval in Sekunden nach dem immer der aktuelle Gesamtstromverbrauch gemeldet wird","Standard");



$functionId=200;
$functionsId = getOrCreateFunction($classesId, "evSignal", $functionId, "EVENT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "time", "DWORD", "Systemzeit des ESP zu Debugzwecken","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "signalCount", "DWORD", "Anzahl gezählter S0 Signale seit dem letzten Zurücksetzen");
$paramsId = getOrCreateFunctionParam($functionsId, "power", "WORD", "Aktuelle Leistung in Watt","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "signalDuration", "DWORD", "Dauer des gemessenen S0 Signals in ms","Standard");


$functionId=1;
$functionsId = getOrCreateFunction($classesId, "getCurrent", $functionId, "FUNCTION", "Standard");

$functionId=201;
$functionsId = getOrCreateFunction($classesId, "evCurrent", $functionId, "EVENT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "current", "DWORD", "Verbrauchter Strom in Wattstunden","Standard");
  
$functionId=130;
$functionsId = getOrCreateFunction($classesId, "Power", $functionId, "RESULT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "power", "WORD", "Aktuelle Leistung in Watt","Standard");
  
$functionId=128;
$functionsId = getOrCreateFunction($classesId, "Current", $functionId, "RESULT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "current", "DWORD", "verbrauchter Strom in Wattstunden","Standard");

$functionId=6;
$functionsId = getOrCreateFunction($classesId, "getSignalCount", $functionId, "FUNCTION", "Standard");

$functionId=131;
$functionsId = getOrCreateFunction($classesId, "SignalCount", $functionId, "RESULT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "signalCount", "DWORD", "Anzahl gezählter S0 Signale seit dem letzten Zurücksetzen","Standard");
  
$functionId=7;
$functionsId = getOrCreateFunction($classesId, "clearSignalCount", $functionId, "FUNCTION", "Standard");

$functionId=2;
$functionsId = getOrCreateFunction($classesId, "setSignalCount", $functionId, "FUNCTION", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "signalCount", "DWORD", "","Standard");

$functionId=5;
$functionsId = getOrCreateFunction($classesId, "getPower", $functionId, "FUNCTION", "Standard");

$functionId=9;
$functionsId = getOrCreateFunction($classesId, "incSignalCount", $functionId, "FUNCTION", "Standard");
	 
$functionId=10;
$functionsId = getOrCreateFunction($classesId, "decSignalCount", $functionId, "FUNCTION", "Standard");


$functionId=210;
$functionsId = getOrCreateFunction($classesId, "evDebug", $functionId, "EVENT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "data", "DWORD", "","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "type", "BITMASK", "","Standard");
getOrCreateFunctionBitMask($functionsId, $paramsId, "invalidSignalLength", 0);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "firmwareUpdateFinished", 1);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "mainLoopTooLong", 2);  
getOrCreateFunctionBitMask($functionsId, $paramsId, "gotMultipleEvents", 3);  

deleteFunction($classesId, "getCurrentIp", 8);
deleteFunction($classesId, "CurrentIp", 132);

$functionId=211;
$functionsId = getOrCreateFunction($classesId, "evInterrupt", $functionId, "EVENT", "Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "value", "BYTE", "","Standard");
$paramsId = getOrCreateFunctionParam($functionsId, "stamp", "DWORD", "","Standard");

QUERY("ALTER TABLE `controller` ADD `receivedModuleId` TINYINT NOT NULL AFTER `lastChange`, ADD `receivedConfiguration` TINYINT NOT NULL AFTER `receivedModuleId`, ADD `receivedObjects` TINYINT NOT NULL AFTER `receivedConfiguration`",TRUE);
QUERY("CREATE TABLE IF NOT EXISTS `smoketest` (`id` int(11) NOT NULL AUTO_INCREMENT,`controllerId` int(11) NOT NULL,`objectId` int(11) NOT NULL,`command` varchar(50) NOT NULL,`logid` int(11) NOT NULL,UNIQUE KEY `id` (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1",TRUE);
QUERY("CREATE TABLE IF NOT EXISTS `smoketesthelper` (`commandLogId` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=latin1",TRUE);


if (!hasIndex("plugins","id"))
{
	QUERY("ALTER TABLE `plugins` DROP `id`");
	QUERY("ALTER TABLE `plugins` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
	addPrimaryKey("plugins","id");
}

$erg = QUERY("select id from plugins where url='/homeserver/heatingControl.php' limit 1");
if ($row=MYSQLi_FETCH_ROW($erg)){}
else QUERY("INSERT into plugins (title, url) values('Heizungssteuerung','/homeserver/heatingControl.php')");

QUERY("ALTER TABLE `featureinstances` ADD `extra` TINYINT NOT NULL AFTER `lastChange`",TRUE);

QUERY("CREATE TABLE IF NOT EXISTS `heating` (`id` int(11) NOT NULL AUTO_INCREMENT, `sensor` int(11) NOT NULL, `relay` int(11) NOT NULL, `diagram` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1", TRUE);


echo "OK";

function getOrCreateClass($classId, $className, $classesId, $view)
{
	$erg = QUERY("select id from featureclasses where name='$className' limit 1");
  if ($row=mysqli_fetch_ROW($erg)) return $row[0];
  $sql = "INSERT into featureclasses (id,classId,name,view) values('$classesId','$classId','$className','$view')";
  QUERY($sql);
  return query_insert_id();
}

function getOrCreateFunction($classesId, $functionName, $functionId, $functionType, $view)
{
	$erg = QUERY("select id from featurefunctions where featureClassesId='$classesId' and name='$functionName' limit 1");
  if ($row=mysqli_fetch_ROW($erg)) return $row[0];

  $sql = "INSERT into featurefunctions (featureClassesId,type,name,functionId, view) values('$classesId','$functionType','$functionName','$functionId', 'Standard')";
  QUERY($sql);
  return query_insert_id();
}

function deleteFunction($classesId, $functionName, $functionId, $functionType, $view)
{
	QUERY("delete from featurefunctions where featureClassesId='$classesId' and name='$functionName' and functionId='$functionId' limit 1");
}


function getOrCreateFunctionParam($functionsId, $paramName, $paramType,$comment,$view)
{
	$erg = QUERY("select id from featurefunctionparams where featureFunctionId='$functionsId' and name='$paramName' limit 1");
	if ($row=mysqli_fetch_ROW($erg)) return $row[0];
	
	$comment = query_real_escape_string($comment);
	
  $sql = "INSERT into featurefunctionparams (featureFunctionId,name,type,comment,view) values('$functionsId','$paramName','$paramType','$comment','$view')";
  QUERY($sql);
  return query_insert_id();
}

function getOrCreateFunctionEnum($functionsId, $functionParamsId, $functionParamName, $functionParamValue)
{
	$erg = QUERY("select id from featurefunctionenums where paramId='$functionParamsId' and name='$functionParamName' limit 1");
  if ($row=mysqli_fetch_ROW($erg)) return $row[0];

  $sql = "INSERT into featurefunctionenums (featureFunctionId,paramId,name,value) values('$functionsId','$functionParamsId','$functionParamName','$functionParamValue')";
  QUERY($sql);
  return query_insert_id();
}

function getOrCreateFunctionBitMask($functionsId, $functionParamsId, $maskName, $maskBit)
{
  $erg = QUERY("select id,name from featurefunctionbitmasks where featureFunctionId='$functionsId' and paramId='$functionParamsId' and bit='$maskBit' limit 1");
	if ($row=mysqli_fetch_ROW($erg))
	{
		if ($row[1]!=$maskName)
		{
			$maskName = query_real_escape_string($maskName);
			$sql = "UPDATE featurefunctionbitmasks set name='$maskName' where id='$row[0]' limit 1";
			QUERY($sql);
		}
		return $row[0];
	}
	
	$maskName = query_real_escape_string($maskName);
  
  $sql = "INSERT into featurefunctionbitmasks (featureFunctionId,paramId,bit,name) values('$functionsId','$functionParamId','$maskBit','$maskName')";
  QUERY($sql);
  return query_insert_id();
}

function createConfigParam($paramKey, $paramValue)
{
  $erg = QUERY("select paramValue from basicConfig where paramKey='$paramKey' limit 1");
  if ($obj=mysqli_fetch_OBJECT($erg)) return;

  $sql = "insert into basicConfig (paramKey,paramValue) values('$paramKey','$paramValue')";
  QUERY($sql);
}

function addIndex($table,$column)
{
	$erg = QUERY("SHOW INDEX FROM $table WHERE Column_name = '$column'");
  if ($row=mysqli_fetch_ROW($erg)) {}
  else
  {
  	//echo "Ergänze Index $column in $table <br>";
  	QUERY("ALTER TABLE $table ADD INDEX ( $column)");
  }
}

function hasIndex($table,$column)
{
	$erg = QUERY("SHOW INDEX FROM $table WHERE Column_name = '$column'");
  if ($row=mysqli_fetch_ROW($erg)) return TRUE;
  return FALSE;
}

function addPrimaryKey($table,$column)
{
	if (!hasIndex($table,$column))
  {
  	//echo "Ergänze Index $column in $table <br>";
  	QUERY("ALTER TABLE $table ADD PRIMARY KEY ( $column)");
  }
}

function repairDoubleIndexes()
{
	$erg = QUERY("SHOW TABLES");
	while($row=mysqli_fetch_ROW($erg))
	{
		$table=$row[0];

    unset($check);
	  $erg2 = QUERY("SHOW INDEX FROM $table");
	  while($obj=mysqli_fetch_OBJECT($erg2))
	  {
	  	  if ($check[$obj->Column_name]==1)
	  	  {
	  	  	//echo "Lösche Index: ".$table.": ".$obj->Key_name." - ".$obj->Column_name."<br>";
	  	  	QUERY("ALTER TABLE $table DROP INDEX ".$obj->Key_name);
	  	  }
	  	  $check[$obj->Column_name]=1;
	  }
	}
}
?>