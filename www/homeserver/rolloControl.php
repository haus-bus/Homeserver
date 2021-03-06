<?php
require($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
error_reporting(E_ERROR);

$erg = QUERY("select featureClassesId,objectId from featureInstances where id='$featureInstanceId' limit 1");
if ($obj=mysqli_fetch_OBJECT($erg))
{
  $featureClassesId = $obj->featureClassesId;
  $objectId=$obj->objectId;
}
else die("FEHLER! Ungültige featureInstanceId $featureInstanceId");

if ($action=="save" && $preset!="")
{
  $value=(int)$value;
  if ($value>100) $value=100;
  else if ($value<0) $value=0;
  QUERY("INSERT into guiControlsSaved (featureInstanceId, name, value) values('$featureInstanceId','$preset','$value')");
  $lastId=query_insert_id();
  $erg = QUERY("select max(sort) from guiControlsSaved where featureInstanceId='$featureInstanceId'");
  $row=mysqli_fetch_ROW($erg);
  $sort=$row[0]+1;
  QUERY("update guiControlsSaved set sort='$sort' where id='$lastId' limit 1") ;
  header("Location: rolloControl.php?featureInstanceId=$featureInstanceId");
  exit;
}
else if ($action=="editSort")
{
  $parts = explode(",",$ids);
  foreach ((array)$parts as $actId)
  {
    $value="delete$actId";
    $delete=$$value;
    if ($delete==1) QUERY("delete from guiControlsSaved where id='$actId' limit 1") ;
    else
    {
      $value="sort$actId";
      $sort=$$value;
      $value="name$actId";
      $name=$$value;
      $value="position$actId";
      $position=$$value;
      $position=(int)$position;
      if ($position>100) $position=100;
      else if ($position<0) $position=0;

      QUERY("update guiControlsSaved set sort='$sort',name='$name',value='$position' where id='$actId' limit 1") ;
    }
  }
}
else if ($action=="control")
{
  callInstanceMethodByName($featureInstanceId, "moveToPosition", array("position"=>(100-$position)));
  die($debug);
}
else if ($action=="toOpen")
{
  callInstanceMethodByName($featureInstanceId, "start", array("direction"=>"TO_OPEN"));
  die($debug);
}
else if ($action=="toClose")
{
  callInstanceMethodByName($featureInstanceId, "start", array("direction"=>"TO_CLOSE"));
  die($debug);
}
else if ($action=="status")
{
	if (changesSince($lastStatusId))
	{
    callInstanceMethodByName($featureInstanceId, "getStatus");
    $result = waitForInstanceResultByName($featureInstanceId, 2, "Status", $lastLogId);
    updateLastLogId();
    die($lastLogId."#".(100-getResultDataValueByName("position", $result)));
    //die(updateLastLogId()."#".getLastStatus($lastStatusId));
  }
  exit;
}


$html = loadTemplate("rolloControl_design.html");

$html = str_replace("%FEATURE_INSTANCE_ID%",$featureInstanceId,$html);

$presetTags = getTag("%PRESET%",$html);
$configTag = getTag("%CONFIG%",$html);
$presets="";
$config="";
$paramIds="";
$i=0;
$erg = QUERY("select name, value,sort,id from guiControlsSaved where featureInstanceId='$featureInstanceId' order by sort");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $presetTags;
  $actTag = str_replace("%PRESET%",$obj->name,$actTag);
  $actTag = str_replace("%PRESET_POSITION%",$obj->value,$actTag);

  $presets.=$actTag;

  $i++;
  if ($i%2==0) $presets.="</tr><tr>";

  if ($paramIds!="") $paramIds.=",";
  $paramIds.=$obj->id;
   
  $actTag = $configTag;
  $actTag = str_replace("%ACT_ID%",$obj->id,$actTag);
  $actTag = str_replace("%SORT%",($i*10),$actTag);
  $actTag = str_replace("%NAME%",$obj->name,$actTag);
  $actTag = str_replace("%POSITION%",$obj->value,$actTag);
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
  
  $statusId = getObjectFunctionIdByName($objectId, "Status");
  $evStopId = getObjectFunctionIdByName($objectId, "evClosed");
  $evOpenId = getObjectFunctionIdByName($objectId, "evOpen");
  $erg3 = QUERY("select functionData,fktId from udpCommandLog where senderObj='$objectId' and (fktId='$statusId'||fktId='$evStopId'||fktId='$evOpenId') and id>'$lastStatusId' order by id desc limit 1");
  if ($row3=mysqli_fetch_ROW($erg3))
  {
  	if ($row3[1]==$evOpenId) return 100;
  	
    $actFunctionData = unserialize($row3[0]);
      
    foreach($actFunctionData->paramData as $actSearchParam)
    {
      if ($actSearchParam->name=="position") return 100-$actSearchParam->dataValue;
    }
  }
  return -1;
}
?>