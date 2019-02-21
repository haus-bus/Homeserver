<?php
$myFile="../../user/gruppen.txt";

$debugMe=0;

if ($_GET["debug"]==1)
{
	include("../../include/all.php");
	$content = file_get_contents($myFile);
}
else
{
	$content = file_get_contents("plugins/watchdog/$myFile");
}

$parts = explode("####", $content);
foreach ((array)$parts as $entry)
{
	if ($entry=="") continue;
	$subParts = explode("###", $entry);
	$id=$subParts[0];
	
	$erg = QUERY("select id from rules where groupId='$id' and activationStateId='0' order by id limit 1");
	if($obj=MYSQLi_FETCH_OBJECT($erg))
	{
		 $erg2 = QUERY("select featureInstanceId, featureFunctionId from ruleSignals where ruleId='$obj->id'");
		 while($obj2=MYSQLi_FETCH_OBJECT($erg2))
		 {
		 	  $triggerSignalInstanceIds[$id][]=$obj2->featureInstanceId;
		 	  $triggerSignalFunctionIds[$id][]=$obj2->featureFunctionId;
		 }
		 
 		 $erg2 = QUERY("select featureInstanceId, featureFunctionId from ruleActions where ruleId='$obj->id'");
		 while($obj2=MYSQLi_FETCH_OBJECT($erg2))
		 {
		 	  $triggerActionInstanceIds[$id][]=$obj2->featureInstanceId;
		 	  $triggerActionObjectIds[$id][]=getObjectIdForInstanceId($obj2->featureInstanceId);
		 	  $triggerActionFunctionIds[$id][]=$obj2->featureFunctionId;
		 }
	}
}

if ($debugMe==1)
{
  echo "started<br>";
  print_r($triggerSignalInstanceIds);
  print_r($triggerSignalFunctionIds);
  print_r($triggerActionInstanceIds);
  print_r($triggerActionObjectIds);
  print_r($triggerActionFunctionIds);
}

function triggerEventOccured($senderData, $receiverData, $functionData)
{
	 global $triggerSignalInstanceIds;
	 global $triggerSignalFunctionIds;
	 global $triggerActionInstanceIds;
	 global $triggerActionObjectIds;
	 global $triggerActionFunctionIds;
	 
   if ($debugMe==1) echo "#########\n".$senderData->instanceDbId."-".$functionData->functionDbId."\n";
   
   $slept=0;
   
	 foreach ((array)$triggerSignalInstanceIds as $ruleId=>$arr)
	 {
	 	 foreach ((array)$arr as $index=>$featureInstanceId)
     {
    	 if ($debugMe==1) echo $index."-".$featureInstanceId."-".$triggerSignalFunctionIds[$ruleId][$index]."\n";
    	 	 
   	   if ($featureInstanceId==$senderData->instanceDbId && $functionData->functionDbId == $triggerSignalFunctionIds[$ruleId][$index])
   	   {
   	 	   if ($debugMe==1) echo "treffer\n";
   	 	   if ($slept==0)
   	 	   {
   	 	   	  sleep(2);
   	 	   	  $slept=1;
   	 	   }
   	 	   
   	 	   $actions= $triggerActionObjectIds[$ruleId];
   	 	   
   	 	   foreach ((array)$triggerActionObjectIds[$ruleId] as $actionIndex=>$actionFeatureObjectId)
   	 	   {
   	 	     if ($actionFeatureObjectId>0)
   	 	     {
   	 	     	 if ($debugMe==1) echo $actionFeatureObjectId.": ";
   	 	    	 $result = callObjectMethodByNameAndRecover($actionFeatureObjectId, "getStatus", "", "Status", 3, 1,0);
   	 	    	 $status = $result[0]->dataValue;
   	 	    	 if ($debugMe==1) echo "ergebnis ".$result[0]->dataValue;
   	 	    	 if ($status>0) echo " AN ";

 	    	  	 if (getClassId($actionFeatureObjectId)==17) // Dimmer
	 	    	   {
   	 	    	   if ($debugMe==1) echo " dimmer ";
   	 	    	   if ($status>0) callObjectMethodByNameForEventAndRecover($actionFeatureObjectId, "setBrightness",  array ("brightness" => 0,"duration" => 0), "evOff", 3, 1,0);
   	 	    	   else callObjectMethodByName($actionFeatureObjectId, "evOff"); // Damit die Gruppen richtig synchronisiert sind
   	 	    	 }
   	 	    	 else if (getClassId($actionFeatureObjectId)==19) // Schalter
   	 	    	 {
   	 	    	   if ($debugMe==1)	echo " schalter ";
   	 	    	   if ($status>0) callObjectMethodByNameForEventAndRecover($actionFeatureObjectId, "off",  "", "evOff", 3, 1,0);
   	 	    	   else callObjectMethodByName($actionFeatureObjectId, "evOff"); // Damit die Gruppen richtig synchronisiert sind
   	 	    	 }
   	 	    	 sleepMs(20);
 	 	    	  
 	 	    	   if ($debugMe==1) echo "\n";
   	 	     }
   	 	   }
   	 	   
   	 	   sleep(2);
   	 	   
   	 	   foreach ((array)$triggerActionObjectIds[$ruleId] as $actionIndex=>$actionFeatureObjectId)
   	 	   {
   	 	     if ($actionFeatureObjectId>0)
   	 	     {
   	 	    	 callObjectMethodByName($actionFeatureObjectId, "evOff"); // Damit die Gruppen richtig synchronisiert sind
   	 	    	 sleepMs(100);
   	 	     }
   	 	   }
   	   }
   	 }
	 }
}
?>  