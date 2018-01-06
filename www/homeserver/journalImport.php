<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

MYSQL_QUERY("TRUNCATE table udpCommandLogImport") or die(MYSQL_ERROR());
MYSQL_QUERY("TRUNCATE table udpDataLogImport") or die(MYSQL_ERROR());

$file = $_FILES['inputFile']['tmp_name'];
$file=file_get_contents($file);
$commandPars = explode("XYZ",$file);
foreach($commandPars as $obj)
{
	 $obj = unserialize($obj);
	 $obj->sender=mysql_real_escape_string($obj->sender);
	 $obj->receiver=mysql_real_escape_string($obj->receiver);
	 $obj->functionData=mysql_real_escape_string($obj->functionData);
	 $obj->senderSubscriberData=mysql_real_escape_string($obj->senderSubscriberData);
	 $obj->receiverSubscriberData=mysql_real_escape_string($obj->receiverSubscriberData);
	 
	 MYSQL_QUERY("INSERT into udpCommandLogImport (time,type,messageCounter,sender,receiver,function,params,functionData,senderSubscriberData,receiverSubscriberData,udpDataLogId,senderObj,fktId)
	                               values ('$obj->time','$obj->type','$obj->messageCounter','$obj->sender','$obj->receiver','$obj->function','$obj->params','$obj->functionData','$obj->senderSubscriberData','$obj->receiverSubscriberData','$obj->udpDataLogId','$obj->senderObj','$obj->fktId')") or die(MYSQL_ERROR());

   MYSQL_QUERY("INSERT into udpdatalogimport (id, time, data) values('$obj->udpDataLogId','$obj->time','$obj->data')") or die(MYSQL_ERROR());
}


setupTreeAndContent("journalImport_design.html");

$entryTag=getTag("%ENTRY%",$html);
$entries="";
$c=0;

$erg = MYSQL_QUERY("select id,time,type,messageCounter,sender,receiver,function,params from udpCommandLogImport order by id limit 5000") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
  $c++;
  $actTag = $entryTag;
  $actTag = str_replace("%ID%",$obj->id,$actTag);
  $actTag = str_replace("%TIME%",date("d.m.y H:i:s",$obj->time),$actTag);
  $actTag = str_replace("%TYP%",$obj->type,$actTag);
  $actTag = str_replace("%SENDER%",$obj->sender,$actTag);
  $actTag = str_replace("%RECEIVER%",$obj->receiver,$actTag);
  $actTag = str_replace("%FUNCTION%",$obj->function,$actTag);
  $actTag = str_replace("%PARAMS%",$obj->params,$actTag);
  $actTag = str_replace("%ID%",$obj->id,$actTag);
  
  if ($c%2==0) $actTag = str_replace("%BG%","#eeeeee",$actTag);
  else $actTag = str_replace("%BG%","#ffffff",$actTag);
  $entries.=$actTag;
}
$html = str_replace("%ENTRY%",$entries,$html);

show();

//17.1.2011 20:31:57
function selectToTime($in)
{
   $in=trim($in);
   
   $pos=strpos($in,".");
   $pos2=strpos($in,".",$pos+1);
   $pos3=strpos($in,":");
   $pos4=strpos($in,":",$pos3+1);
   $pos5=strpos($in," ");
   if ($pos5===FALSE) $pos=strlen($in);
   
   $day = substr($in,0,$pos);
   $month = substr($in,$pos+1,$pos2-$pos-1);
   $year =  substr($in,$pos2+1,$pos5-$pos2-1);
   
   if ($pos3!==FALSE)
   {
      $hour = substr($in,$pos5+1,$pos3-$pos5-1);
      $minute = substr($in,$pos3+1,$pos4-$pos3-1);
      $second = substr($in,$pos4+1,strlen($in)-$pos4-1);
      return mktime ($hour, $minute, $second, $month, $day, $year); 
   }
   else return mktime (0, 0, 0, $month, $day, $year);
  
}

?>
