<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
	if ($delete==1)
	{
		$erg = MYSQL_QUERY("select featureClassesId,objectId from featureInstances where id='$id' limit 1") or die(MYSQL_ERROR());
		if ($row=MYSQL_FETCH_ROW($erg))
		{
			 $featureClassesId=$row[0];
			 $objectId=$row[1];
		}
		else die("ung�ltige instance $id");
		
  	$featureClassName = getNameForFeatureClass($featureClassesId);
    if ($featureClassName=="Led" || $featureClassName=="Taster" || $featureClassName=="LogicalButton")
    {
    	 if ($featureClassName=="Led" || $featureClassName=="Taster")
    	 {
			   $ledPortInstance = hexdec(substr(decHex(getInstanceId($objectId)),0,1)."0");
			   $ledBit = substr(decHex(getInstanceId($objectId)),1,1)-1;
			   $ledPortObjectId = getObjectId(getDeviceId($objectId), getClassesIdByName("DigitalPort"), $ledPortInstance);
         callObjectMethodByName($ledPortObjectId, "getConfiguration");
      
         $result = waitForObjectResultByName($ledPortObjectId,5, "Configuration", $lastLogId);
         
         $myPin = "pin".$ledBit;
         
         unset($data);
         foreach ($result as $obj)
         {
         	  if ($obj->name==$myPin) $obj->dataValue=255;
         	  
         	  $data[$obj->name]=$obj->dataValue;
         }

         callObjectMethodByName($ledPortObjectId, "setConfiguration",$data);

         $message="OK, �nderungen erst nach Reset sichtbar";
       }
       else if ($featureClassName=="LogicalButton")
    	 {
			   $bit = getInstanceId($objectId);
		   
	 		   $controllerObjectId = getObjectId(getDeviceId($objectId), $CONTROLLER_CLASS_ID, $FIRMWARE_INSTANCE_ID);
         callObjectMethodByName($controllerObjectId, "getConfiguration");
      
         $result = waitForObjectResultByName($controllerObjectId,5, "Configuration", $lastLogId);
         
         foreach($result as $obj)
         {
         	 if ($obj->name=="logicalButtonMask") $obj->dataValue=getResultDataValueByName("logicalButtonMask", $result)&~ pow(2,$bit-1);
         	 $params[$obj->name]=$obj->dataValue;
         }

         callObjectMethodByName($controllerObjectId, "setConfiguration",$params);
         
         MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='0' where parentInstanceId='$id'") or die(MYSQL_ERROR());
         $message="OK, �nderungen erst nach Reset sichtbar";
    	 }
    }
	}
	else
	{
    $erg = MYSQL_QUERY("select featureClassesId from featureInstances where id='$id' limit 1") or die(MYSQL_ERROR());
    $row=MYSQL_FETCH_ROW($erg);
    $featureClassesId=$row[0];
    $erg = MYSQL_QUERY("select name from featureClasses where id='$featureClassesId' limit 1") or die(MYSQL_ERROR());
    $row=MYSQL_FETCH_ROW($erg);
    if ($row[0]==$name) $message="FEHLER! Ein Feature darf nicht so hei�en wie sein Typ. Bitte anderen Namen w�hlen!";
    else
    {
      MYSQL_QUERY("UPDATE featureInstances set name='$name' where id='$id' limit 1") or die(MYSQL_ERROR());
      $message="Einstellungen gespeichert";
    }
    
    //wetter
    /*if ($featureClassesId==25)
    {
    	MYSQL_QUERY("delete from basicConfig where paramKey = 'offsetSunrise' or paramKey = 'offsetSunset' limit 2") or die(MYSQL_ERROR());
    	MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('offsetSunrise','$offsetSunrise')") or die(MYSQL_ERROR());
    	MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('offsetSunset','$offsetSunset')") or die(MYSQL_ERROR());
    }*/
  }
}

// Methodenaufrufe
if ($action=="callMethod")
{
  $erg = MYSQL_QUERY("select max(id) from udpCommandLog") or die(MYSQL_ERROR());
  $row=MYSQL_FETCH_ROW($erg);
  $minId=$row[0];

  $message="<iframe style='position:relative;left:0px;top:0px' src='specificJournal.php?objectId=$objectId&minId=$minId' width='100%' height='55' frameborder=0 border=0></iframe>";

  $erg = MYSQL_QUERY("select id,name,type from featureFunctionParams where featureFunctionId='$featureFunctionId' order by id") or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
    if ($obj->type=="WEEKTIME")
    {
      $value="param".$obj->id."Day";
      $day=$$value;
      $value="param".$obj->id."Hour";
      $hour=$$value;
      $value="param".$obj->id."Minute";
      $minute=$$value;
      $value=toWeekTime($day,$hour,$minute);
      $paramData[trim($obj->name)]=$value;
    }
    else
    {
      $param="param".$obj->id;
      $paramData[trim($obj->name)]=$$param;
    }
  }
  
  $foundSetConfiguration=0;
  $erg = MYSQL_QUERY("select featureFunctions.name as featureFunctionName, featureClasses.name as featureClassName
                             from featureFunctions join featureClasses on (featureFunctions.featureClassesId = featureClasses.id)
                             where featureFunctions.id='$featureFunctionId' limit 1") or die(MYSQL_ERROR());
  $obj=MYSQL_FETCH_OBJECT($erg);
  {
  	 if ($obj->featureClassName=="LogicalButton" && $obj->featureFunctionName=="setConfiguration")
  	 {
  	 	   $erg = MYSQL_QUERY("select classId,name from featureClasses where name='Taster' or name='Led' limit 2") or die(MYSQL_ERROR());
  	 	   while($row=MYSQL_FETCH_ROW($erg))
  	 	   {
  	 	   	  if ($row[1]=="Taster") $tasterClassId=$row[0];
  	 	   	  else if ($row[1]=="Led") $ledClassId=$row[0];
  	 	   }
  	 	   
  	 	   // zugeh�rige featureInstances neufinden
  	 	   MYSQL_QUERY("update featureInstances set parentInstanceId='0' where parentInstanceId='$id'") or die(MYSQL_ERROR());
  	 	   
  	 	   for ($i=1;$i<=8;$i++)
  	 	   {
  	 	     setParentId("button$i", $tasterClassId);
  	 	   }
  	 	   
   	 	   for ($i=1;$i<=8;$i++)
  	 	   {
  	 	     setParentId("led$i", $ledClassId);
  	 	   }
  	 }
  	 
  	 if ($obj->featureFunctionName=="setConfiguration") $foundSetConfiguration=1;
  	   
  }

  $waitForId=$minId+2;
  
  callInstanceMethodForObjectId($objectId, $featureFunctionId, $paramData);
  
  // Nach dem Setzen der Konfiguration lesen wir direkt die Konfiguration neu aus, damit sie in lastReceived steht
  if ($foundSetConfiguration==1)
  {
  	sleepMs(300);
  	callObjectMethodByName($objectId, "getConfiguration");
  	waitForObjectResultByName($objectId,5, "Configuration", $lastLogId);
  	$minId+=2;
  }
  
  waitForCommandId($waitForId);
}

function setParentId($paramDataName, $childClassId)
{
	global $id;
	global $paramData;
	global $objectId;
	
	if ($paramData[$paramDataName]>0)
  {
     $childObjectId = getObjectId(getDeviceId($objectId), $childClassId, $paramData[$paramDataName]);
  	 MYSQL_QUERY("UPDATE featureInstances set parentInstanceId='$id' where objectId='$childObjectId' limit 1") or die(MYSQL_ERROR());
  }
}



setupTreeAndContent("editFeatureInstance_design.html", $message);

$html = str_replace("%ID%",$id, $html);

$allFeatureClasses = readFeatureClasses();
$allFeatureFunctions = readFeatureFunctions();
$allFeatureFunctionParams = readFeatureFunctionParams();
$allFeatureFunctionEnums = readFeatureFunctionEnums();
$allFeatureInstances = readFeatureInstances();

foreach($allFeatureInstances as $obj)
{
	if ($obj->id==$id)
  {
    $objectId = $obj->objectId;
    $featureType = $allFeatureClasses[$obj->featureClassesId]->name;
    $html = str_replace("%FEATURE_NAME%",i18n($obj->name), $html);
    $html = str_replace("%FEATURE_TYP%",i18n($featureType), $html);
    
    if ($featureType=="Taster" || $featureType=="Led")
    {
    	$hex = dechex(getInstanceId($obj->objectId));
    	$port = substr($hex,0,1);
    	$pin = substr($hex,1,1)-1;
    	$add=" - Port: $port Pin: $pin";
    }
    
    $html = str_replace("%OBJECT_ID_FORMATED%",getFormatedObjectId($obj->objectId).$add, $html);
    $html = str_replace("%OBJECT_ID%",$obj->objectId, $html);
    $html = str_replace("%PORT%",$obj->port, $html);
    $featureClassesId=$obj->featureClassesId;
    break;
  }
}
if ($objectId=="") die("FEHLER! Ung�ltige ID $id");

if ($featureClassesId==25) //wetter
{
	chooseTag("%OPT_WEATHER%",$html);
  $erg = MYSQL_QUERY("select paramKey, paramValue from basicConfig where paramKey = 'offsetSunrise' or paramKey = 'offsetSunset' limit 2") or die(MYSQL_ERROR());
  while($row = MYSQL_FETCH_ROW($erg))
  {
  	if ($row[0]=="offsetSunrise") $offsetSunrise=$row[1];
  	else if ($row[0]=="offsetSunset") $offsetSunset=$row[1];
  }
  
  $html = str_replace("%OFFSET_SUNRISE%", $offsetSunrise, $html);
  $html = str_replace("%OFFSET_SUNSET%", $offsetSunset, $html);
}
else removeTag("%OPT_WEATHER%",$html);

// Zuletzt empfangene Daten von diesem Sender
$erg = QUERY("select function,functionData,id from lastReceived  where senderObj='$objectId' order by id desc limit 50");
while ($row=MYSQL_FETCH_ROW($erg))
{
	 if (!isset($lastReceived[$row[0]]))
	 {
	    $lastReceived[$row[0]]=$row[1];
	    $lastReceivedId[$row[0]]=$row[2];
	 }
}

$typeRound[0]="EVENT";
$typeRound[1]="ACTION";
$typeRound[2]="FUNCTION";
$typeRound[3]="RESULT";

$functionTag = getTag("%FUNCTION%",$html);
$html = str_replace("%FUNCTION%","", $html);

$ansicht=$_SESSION["ansicht"];
foreach ($typeRound as $actType)
{
  $content="";
  foreach($allFeatureFunctions as $obj)
  {
  	 if ($obj->featureClassesId==$featureClassesId and $obj->type==$actType)
  	 {
  	   
  	   if ($ansicht=="Experte" && $obj->view=="Entwickler") continue;
  	   if ($ansicht=="Standard" && ($obj->view=="Experte" || $obj->view=="Entwickler")) continue;
  	   
  	   $actFunctionData=getLastFunctionData($obj->name, $lastReceived, $lastReceivedId);
  	 	 
       $actTag = $functionTag;
       $actTag = str_replace("%FUNCTION%",i18n($obj->name), $actTag);
       $actTag = str_replace("%FEATURE_FUNCTION_ID%",$obj->id, $actTag);

       $paramTag = getTag("%PARAM%", $actTag);
       $params="";
       foreach($allFeatureFunctionParams as $obj2)
       {
       	  if ($obj2->featureFunctionId==$obj->id)
       	  {
       	    if ($ansicht=="Experte" && $obj2->view=="Entwickler") continue;
       	    if ($ansicht=="Standard" && ($obj2->view=="Experte" || $obj2->view=="Entwickler")) continue;
       	    
            $actParamsTag = $paramTag;
            $actParamsTag = str_replace("%PARAM_NAME%",i18n($obj2->name),$actParamsTag);

            $actParamValue="";
           	
           	if ($actFunctionData!="")
           	{
                foreach($actFunctionData->paramData as $actSearchParam)
                {
                  if ($actSearchParam->name==$obj2->name)
                  {
                    $actParamValue=$actSearchParam->dataValue;
                    break;
                  }
                }
            }

            if ($obj2->type=="ENUM")
            {
              $type="<select name='param".$obj2->id."'>";
              foreach ($allFeatureFunctionEnums as $obj3)
              {
              	if ($obj3->featureFunctionId==$obj->id and $obj3->paramId==$obj2->id)
              	{
                  if ($obj3->value==$actParamValue) $selected="selected"; else $selected="";
                  $type.="<option value='$obj3->value' $selected>".i18n($obj3->name);
                }
              }
             $type.="</select>";
           }
           else if ($obj2->type=="BITMASK")
           {
             if ($actParamValue=="") $actParamValue=0;
             $type=getBitMask("param".$obj2->id,$actParamValue, readFeatureFunctionBitmaskNames($obj->id, $obj2->id));
           }
           else if ($obj2->type=="WEEKTIME")
           {
             $type=getWeekTime("param".$obj2->id, $actParamValue);
           }
           else
           {
             $size = strlen($actParamValue);
             if ($size<5) $size=5;
             $type="<input name='param".$obj2->id."' value='$actParamValue' type='text' size='$size' ondragover='return false' ondrop='addInstanceIdFromClipboard(event, this)'>";
           }

           $actParamsTag = str_replace("%PARAM_ENTRY%",$type,$actParamsTag);
           $actParamsTag = str_replace("%COMMENT%",$obj2->comment,$actParamsTag);
           $params.=$actParamsTag;
        }
      }
    
      $actTag = str_replace("%PARAM%",$params, $actTag);
      $content.=$actTag;
    }
  }
  $html = str_replace("%".$actType."S%",$content, $html);
}

$featureClassName = getNameForFeatureClass($featureClassesId);
if ($featureClassName=="Led" || $featureClassName=="Taster" || $featureClassName=="LogicalButton") chooseTag("%OPT_DELETE%",$html);
else removeTag("%OPT_DELETE%",$html);

$myRooms="";
$erg = QUERY("select rooms.name from rooms join roomFeatures on (rooms.id=roomfeatures.roomId) where roomFeatures.featureInstanceId='$id' order by rooms.name");
while($row=MYSQL_FETCH_ROW($erg))
{
	if ($myRooms!="") $myRooms.=", ";
	$myRooms.=$row[0];
}
$html = str_replace("%ROOMS%",$myRooms, $html);

$myGroups="";
$erg = QUERY("select groups.name from groups join groupFeatures on (groups.id=groupFeatures.groupId) where groupFeatures.featureInstanceId='$id' and single!=1 and groups.generated!=1 order by groups.name");
while($row=MYSQL_FETCH_ROW($erg))
{
  if ($myGroups!="") $myGroups.=", ";
  $myGroups.=$row[0];
}
$html = str_replace("%GROUPS%",$myGroups, $html);


/*$directRules=0;
$erg = QUERY("SELECT rules.id from rules join ruleActions on (rules.id=ruleActions.ruleId) join groups on (groups.id=rules.groupId) where single=1 and ruleActions.featureInstanceId='$id' limit 1");
if (MYSQL_FETCH_ROW($erg)) $directRules=1;
if ($directRules==1) $html = str_replace("%DIRECT%","Ja", $html);
else $html = str_replace("%DIRECT%","Nein", $html);
*/

show();

function getLastFunctionData($name, $lastReceived, $lastReceivedId)
{
	$lastReceivedNormal = $lastReceivedId[$name];
	$setName = str_replace("set","",$name);
	$lastReceivedSet = $lastReceivedId[$setName];
	
	if ($lastReceivedNormal=="" && $lastReceivedSet=="") return "";
	if ($lastReceivedNormal=="" && $lastReceivedSet!="") return unserialize($lastReceived[$setName]);
	if ($lastReceivedNormal!="" && $lastReceivedSet=="") return unserialize($lastReceived[$name]);
	if ($lastReceivedNormal>$lastReceivedSet) return unserialize($lastReceived[$name]);
	return unserialize($lastReceived[$setName]);
}
?>