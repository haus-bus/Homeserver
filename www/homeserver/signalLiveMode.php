<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

$html = loadTemplate("signalLiveMode_design.html");
$html = str_replace("%LOGICAL_GROUP_MODE%",$logicalGroupMode,$html);
$html = str_replace("%GRAPH_ID%",$graphId,$html);
$html = str_replace("%SIGNAL_ID%",$signalId,$html);
$html = str_replace("%SIGNAL_EVENT_ID%", $signalEventId, $html);

if ($action=="") $action="addSignal";

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
    
    //$obj->sender = utf8_encode($obj->sender);
    die($obj->id."#<a href='editRules.php?action=$action&submitted=1&logicalGroupMode=$logicalGroupMode&graphId=$graphId&signalId=$signalId&signalEventId=$signalEventId&groupId=$groupId&ruleId=$ruleId&featureInstanceId=$senderId&featureFunctionId=$fktData->id&param1Value=$param1Value&param2Value=$param2Value' target='main'><img src='img/action.gif' border=0> ".$obj->function." ".$obj->sender."</a><br>");
  }
  else die("");
}

$erg = QUERY("select max(id) from udpCommandLog");
if ($row=mysqli_fetch_ROW($erg)) $lastId=$row[0];
else $lastId=0;

$html = str_replace("%FIRST_ID%",$lastId,$html);
$html = str_replace("%GROUP_ID%",$groupId,$html);
$html = str_replace("%RULE_ID%",$ruleId,$html);
$html = str_replace("%ACTION%",$action,$html);

die($html);

?>
