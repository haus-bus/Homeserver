<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

$html = loadTemplate("signalLiveModeGeneric_design.html");

if ($check==1)
{
  $erg = QUERY("select id,sender,function,senderSubscriberData,functionData from udpCommandLog where type='EVENT' and id>'$lastId' order by id limit 1");
  if ($obj=mysqli_fetch_OBJECT($erg))
  {
    $senderData = unserialize($obj->senderSubscriberData);
    $senderId = $senderData->featureInstanceObject->id;
    $fktData = unserialize($obj->functionData);
    $param1Value=$fktData->paramData[0]->dataValue;
    $param2Value=$fktData->paramData[1]->dataValue;
    
    die($obj->id."#<a href='$returnUrl?params=$params&featureInstanceId=$senderId&featureFunctionId=$fktData->id&param1Value=$param1Value&param2Value=$param2Value' target='main'><img src='img/action.gif' border=0> ".$obj->function." ".$obj->sender."</a><br>");
  }
  else die("");
}

$erg = QUERY("select max(id) from udpCommandLog");
if ($row=mysqli_fetch_ROW($erg)) $lastId=$row[0];
else $lastId=0;

$html = str_replace("%FIRST_ID%",$lastId,$html);
$html = str_replace("%PARAMS%",$params,$html);
$html = str_replace("%RETURN_URL%",$returnUrl,$html);

die($html);

?>
