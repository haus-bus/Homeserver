<?php 
require("include/all.php");

  $featureClassesId = getClassesIdByName("Taster");
  $evClickedFunctionId = getClassesIdFunctionsIdByName($featureClassesId, "evClicked");
  $evHoldStartFunctionId = getClassesIdFunctionsIdByName($featureClassesId, "evHoldStart");
  $evHoldEndFunctionId = getClassesIdFunctionsIdByName($featureClassesId, "evHoldEnd");
  $evDoubleClickFunctionId = getClassesIdFunctionsIdByName($featureClassesId, "evDoubleClick");
  $evCoveredFunctionId = getClassesIdFunctionsIdByName($featureClassesId, "evCovered");

$erg = MYSQL_QUERY("select rules.id,featureFunctionId from rules join ruleSignals on(ruleSignals.ruleId = rules.id) where signalType='0' order by id") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	$type="other";
	
	if ($obj->featureFunctionId==$evClickedFunctionId) $type="click";
	else if ($obj->featureFunctionId==$evDoubleClickFunctionId) $type="doubleClick";
	else if ($obj->featureFunctionId==$evHoldStartFunctionId) $type="holdStart";
	else if ($obj->featureFunctionId==$evHoldEndFunctionId) $type="holdEnd";
	else if ($obj->featureFunctionId==$evCoveredFunctionId) $type="covered";
	
	echo $obj->id." -  ".$obj->featureFunctionId." - $type <br>";
	MYSQL_QUERY("UPDATE rules set signalType='$type' where id='$obj->id' limit 1") or die(MYSQL_ERROR());
}

?>
