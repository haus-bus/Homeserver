<?php
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
error_reporting(E_ERROR);

$erg = MYSQL_QUERY("select featureClassesId,objectId from featureInstances where id='$featureInstanceId' limit 1") or die(MYSQL_ERROR());
$obj=MYSQL_FETCH_OBJECT($erg);
$featureClassesId = $obj->featureClassesId;
$objectId=$obj->objectId;
if ($featureClassesId=="") die("FEHLER! Ungültige featureInstanceId $featureInstanceId");


if ($action=="save" && $preset!="")
{
  $value=(int)$value;
  if ($value>100) $value=100;
  else if ($value<0) $value=0;
  MYSQL_QUERY("INSERT into guiControlsSaved (featureInstanceId, name, value) values('$featureInstanceId','$preset','$value')") or die(MYSQL_ERROR());
  $lastId=mysql_insert_id();
  $erg = MYSQL_QUERY("select max(sort) from guiControlsSaved where featureInstanceId='$featureInstanceId'") or die(MYSQL_ERROR());
  $row=MYSQL_FETCH_ROW($erg);
  $sort=$row[0]+1;
  MYSQL_QUERY("update guiControlsSaved set sort='$sort' where id='$lastId' limit 1")  or die(MYSQL_ERROR());
  header("Location: dimmerControl.php?featureInstanceId=$featureInstanceId");
  exit;
}
else if ($action=="editSort")
{
  $parts = explode(",",$ids);
  foreach ((array)$parts as $actId)
  {
    $value="delete$actId";
    $delete=$$value;
    if ($delete==1) MYSQL_QUERY("delete from guiControlsSaved where id='$actId' limit 1")  or die(MYSQL_ERROR());
    else
    {
      $value="sort$actId";
      $sort=$$value;
      $value="name$actId";
      $name=$$value;
      $value="brightness$actId";
      $brightness=$$value;
      $brightness=(int)$brightness;
      if ($brightness>100) $brightness=100;
      else if ($brightness<0) $brightness=0;

      MYSQL_QUERY("update guiControlsSaved set sort='$sort',name='$name',value='$brightness' where id='$actId' limit 1")  or die(MYSQL_ERROR());
    }
  }
}
else if ($action=="control")
{
  callInstanceMethodByName($featureInstanceId, "setBrightness", array("brightness"=>$brightness, "fadingTime"=>10));
  die($debug);
}
else if ($action=="toBrightness")
{
  callInstanceMethodByName($featureInstanceId, "start", array("direction"=>"TO_LIGHT"));
  die($debug);
}
else if ($action=="toDarkness")
{
  callInstanceMethodByName($featureInstanceId, "start", array("direction"=>"TO_DARK"));
  die($debug);
}
else if ($action=="stop")
{
  callInstanceMethodByName($featureInstanceId, "stop");
  die($debug);
}
else if ($action=="status")
{
	if (changesSince($lastStatusId))
	{
    callInstanceMethodByName($featureInstanceId, "getStatus");
    $result = waitForInstanceResultByName($featureInstanceId, 2, "Status", $lastLogId);
    updateLastLogId();
    die($lastLogId."#".getResultDataValueByName("brightness", $result));
    //die(updateLastLogId()."#".getLastStatus($lastStatusId));
  }
  exit;
}


$html = loadTemplate("dimmerControl_design.html");

$html = str_replace("%FEATURE_INSTANCE_ID%",$featureInstanceId,$html);

$presetTags = getTag("%PRESET%",$html);
$configTag = getTag("%CONFIG%",$html);
$presets="";
$config="";
$paramIds="";
$i=0;
$erg = MYSQL_QUERY("select name, value,sort,id from guiControlsSaved where featureInstanceId='$featureInstanceId' order by sort") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
  $actTag = $presetTags;
  $actTag = str_replace("%PRESET%",$obj->name,$actTag);
  $actTag = str_replace("%PRESET_BRIGHTNESS%",$obj->value,$actTag);

  $presets.=$actTag;

  $i++;
  if ($i%2==0) $presets.="</tr><tr>";

  if ($paramIds!="") $paramIds.=",";
  $paramIds.=$obj->id;
   
  $actTag = $configTag;
  $actTag = str_replace("%ACT_ID%",$obj->id,$actTag);
  $actTag = str_replace("%SORT%",($i*10),$actTag);
  $actTag = str_replace("%NAME%",$obj->name,$actTag);
  $actTag = str_replace("%BRIGHTNESS%",$obj->value,$actTag);
  $config.=$actTag;
}
$html = str_replace("%PRESET%",$presets,$html);
$html = str_replace("%CONFIG%",$config,$html);
$html = str_replace("%IDS%",$paramIds,$html);

$html = str_replace("%INITIAL_STATUS_ID%",updateLastLogId(),$html);
$html = str_replace("%INITIAL_VALUE%",getLastStatus(0),$html);


if ($online==1) removeTag("%OPT_ADMIN%",$html);
else chooseTag("%OPT_ADMIN%",$html);


callInstanceMethodByName($featureInstanceId, "getStatus");

echo $html;


function getLastStatus($lastStatusId)
{
  global $objectId;
  global $functionDefinitions;
  
  $statusId = getObjectFunctionIdByName($objectId, "Status");
  $evOffId = getObjectFunctionIdByName($objectId, "evOff");
  $evOnId = getObjectFunctionIdByName($objectId, "evOn");
  $erg3 = MYSQL_QUERY("select functionData from udpCommandLog where senderObj='$objectId' and (fktId='$statusId'||fktId='$evOffId'||fktId='$evOnId') and id>'$lastStatusId' order by id desc limit 1") or die(MYSQL_ERROR());
  if ($row3=MYSQL_FETCH_ROW($erg3))
  {
    $actFunctionData = unserialize($row3[0]);
    if ($actFunctionData->functionId==$evOffId) return 0;
      
    foreach($actFunctionData->paramData as $actSearchParam)
    {
      if ($actSearchParam->name=="brightness") return $actSearchParam->dataValue;
    }
  }
  return -1;
}
?>