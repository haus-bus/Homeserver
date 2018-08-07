<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/homeserver/include/all.php';

echo "OK";

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
//$classId=90;
//$classesId=31;
//getOrCreateClass($classId, "CurrentReader", $classesId, "Standard");

//$functionId=3;
//$functionsId = getOrCreateFunction($classesId, "setConfiguration", $functionId, "FUNCTION", "Standard");
//$paramsId = getOrCreateFunctionParam($functionsId, "config", "BITMASK",'',"Standard");
//getOrCreateFunctionBitMask($functionsId, $paramsId, "enableSignalEvent", 0);
//$paramsId = getOrCreateFunctionParam($functionsId, "impPerKwh", "WORD", "Anzahl Signale pro kWh","Standard");
//deleteFunction($classesId, "getCurrentIp", 8);
//getOrCreateFunctionEnum($functionsId, $paramsId, "SONOFF", 5);
//createConfigParam("current30d",0);


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

function addPrimaryKey($table,$column)
{
	$erg = QUERY("SHOW INDEX FROM $table WHERE Column_name = '$column'");
  if ($row=mysqli_fetch_ROW($erg)) {}
  else
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