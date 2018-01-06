<?php
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
error_reporting(E_ERROR);

$erg = MYSQL_QUERY("select featureClassesId,objectId from featureInstances where id='$featureInstanceId' limit 1") or die(MYSQL_ERROR());
if ($obj=MYSQL_FETCH_OBJECT($erg))
{
  $featureClassesId = $obj->featureClassesId;
  $objectId=$obj->objectId;
}
else die("FEHLER! Ungültige featureInstanceId $featureInstanceId");

if ($action=="switch")
{
	$status=getLastStatus(0);
	if ($status==1) callInstanceMethodByName($featureInstanceId, "off");
	else callInstanceMethodByName($featureInstanceId, "on", array("brightness"=>"100", "fadingTime"=>"1"));
}
else if ($action=="status")
{
	if (changesSince($lastStatusId))
	{
    callInstanceMethodByName($featureInstanceId, "getStatus");
    $result = waitForInstanceResultByName($featureInstanceId, 2, "Status", $lastLogId);
    updateLastLogId();
    die($lastLogId."#".getResultDataValueByName("status", $result));
    //die(updateLastLogId()."#".getLastStatus($lastStatusId));
  }
  exit;
}

$html = loadTemplate("ledControl_design.html");

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
  $erg3 = MYSQL_QUERY("select functionData from udpCommandLog where senderObj='$objectId' and (fktId='$statusId'||fktId='$evOffId'||fktId='$evOnId') and id>'$lastStatusId' order by id desc limit 1") or die(MYSQL_ERROR());
  if ($row3=MYSQL_FETCH_ROW($erg3))
  {
    $actFunctionData = unserialize($row3[0]);
    if ($actFunctionData->functionId==$evOffId) return 0;
    if ($actFunctionData->functionId==$evOnId) return 1;
    
    foreach($actFunctionData->paramData as $actSearchParam)
    {
      if ($actSearchParam->name=="status") return $actSearchParam->dataValue;
    }
  }
  return -1;
}
?>