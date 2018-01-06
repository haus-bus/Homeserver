<?php
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
error_reporting(E_ERROR);


$erg = QUERY("select featureClassesId,objectId from featureInstances where id='$featureInstanceId' limit 1");
if ($obj=MYSQL_FETCH_OBJECT($erg))
{
  $featureClassesId = $obj->featureClassesId;
  $objectId=$obj->objectId;
}
else die("FEHLER! Ungültige featureInstanceId $featureInstanceId");

if ($action=="switch") callInstanceMethodByName($featureInstanceId, "toggle",array("offTime"=>"0", "onTime"=>"0", "quantity"=>"1"));
else if ($action=="status")
{
	if (changesSince($lastStatusId))
	{
    callInstanceMethodByName($featureInstanceId, "getStatus");
    $result = waitForInstanceResultByName($featureInstanceId, 2, "Status", $lastLogId);
    updateLastLogId();
    die($lastLogId."#".getResultDataValueByName("state", $result));
    //die(updateLastLogId()."#".getLastStatus($lastStatusId));
  }
  exit;
}


$html = loadTemplate("switchControl_design.html");

$html = str_replace("%FEATURE_INSTANCE_ID%",$featureInstanceId,$html);
$html = str_replace("%INITIAL_STATUS_ID%",updateLastLogId(),$html);

$status=getLastStatus(0);

if ($status==1)
{
  $html = str_replace("%TITLE%","AN",$html);
  $html = str_replace("%IMG%","img/tasterGruen.png",$html);
}
else
{
  $html = str_replace("%TITLE%","AUS",$html);
  $html = str_replace("%IMG%","img/tasterRot.png",$html);
}

callInstanceMethodByName($featureInstanceId, "getStatus");

echo $html;


function getLastStatus($lastStatusId)
{
  global $objectId;
  
  $statusId = getObjectFunctionIdByName($objectId, "Status");
  $evOffId = getObjectFunctionIdByName($objectId, "evOff");
  $evOnId = getObjectFunctionIdByName($objectId, "evOn");
  $erg3 = QUERY("select functionData from udpCommandLog where senderObj='$objectId' and (fktId='$statusId'||fktId='$evOffId'||fktId='$evOnId') and id>'$lastStatusId' order by id desc limit 1");
  if ($row3=MYSQL_FETCH_ROW($erg3))
  {
    $actFunctionData = unserialize($row3[0]);
    if ($actFunctionData->functionId==$evOffId) return 0;
    if ($actFunctionData->functionId==$evOnId) return 1;
    
    foreach($actFunctionData->paramData as $actSearchParam)
    {
      if ($actSearchParam->name=="state") return $actSearchParam->dataValue;
    }
  }
  return -1;
}
?>