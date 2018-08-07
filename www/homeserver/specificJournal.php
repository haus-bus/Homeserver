<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

$html = loadTemplate("specificJournal_design.html");

$entryTag=getTag("%ENTRY%",$html);
$html = str_replace("%ENTRY%","", $html);
echo $html;
flush();

$start=time();
while(time()-$start<$CONTROLLER_READ_TIMEOUT)
{
	sleepMs(100);
  $erg = QUERY("select id,time,type,messageCounter,sender,receiver,function,params, senderSubscriberData,receiverSubscriberData  from udpCommandLog where id>'$minId' order by id");
  while($obj=mysqli_fetch_OBJECT($erg))
  {
    $minId=$obj->id;
    $senderSubscriberData=unserialize($obj->senderSubscriberData);
    $receiverSubscriberData=unserialize($obj->receiverSubscriberData);
    
    $senderSubscriberData->objectId = $senderSubscriberData->objectId;
    $receiverSubscriberData->objectId = $receiverSubscriberData->objectId;
    
    if ($senderSubscriberData->objectId!=$objectId && $receiverSubscriberData->objectId!=$objectId) continue;
  
    $actTag = $entryTag;
    $actTag = str_replace("%ID%",$obj->id,$actTag);
    $actTag = str_replace("%TIME%",date("d.m.y H:i:s",$obj->time),$actTag);
    
    if ($obj->type=="FUNCTION" || $obj->type=="ACTION") $directionSenderReceiver="»";
    else  $directionSenderReceiver="«";
    
    $actTag = str_replace("%DIRECTION_SENDER_RECEIVER%",$directionSenderReceiver,$actTag);
    $actTag = str_replace("%TYP%",$obj->type,$actTag);
    $actTag = str_replace("%FUNCTION%",$obj->function,$actTag);
    $actTag = str_replace("%PARAMS%",$obj->params,$actTag);
    
    echo $actTag;
    flush();
  }
}
?>
