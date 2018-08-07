<?php
require("include/all.php");

$id = str_replace("'","",$id);
$id = str_replace('"',"",$id);
$id=trim($id);

$erg=QUERY("select paramValue from ruleActionParams where id='$id' limit 1");
$row=mysqli_fetch_ROW($erg);
$command=$row[0];
$command = fillVariables($command);
echo "<pre>";
echo "execute $command \n";

traceToJournal($command,"exec");

$erg = shell_exec($command);
echo $erg;


function fillVariables($command)
{
	 $pos = strpos($command,"$");
	 if ($pos!==FALSE)
	 {
	 	  $pos2 = strpos($command," ",$pos);
	 	  if ($pos2===FALSE) $pos2=strlen($command);
	 	  $name = substr($command,$pos+1,$pos2-$pos-1);
      $erg=QUERY("select value from serverVariables where name='$name' limit 1");
      if ($row=mysqli_fetch_ROW($erg))
   	  {
   	  	 if (strpos($command,"nircmdc")!==FALSE && $row[0]==1) $row[0]=65536;
   	  	 
   	  	 $command = str_replace('$'.$name,$row[0],$command);
   	  }
   	  else
   	  {
   	  	traceToJournal("Variable $name nicht gefunden für Befehl: $command","evError");
   	  	$command = str_replace('$'.$name,"0",$command);
   	  }
	 }
	 return $command;
}

function traceToJournal($message, $functionStr="")
{
	  $time=time();
	  $messageType="FUNCTION";
	  $messageCounter="0";
	  $senderSubscriberDataDebugStr="PC-Executor";
	  $receiverSubscriberDataDebugStr="PC-Executor";
	  $paramsStr=query_real_escape_string($message);
	  
	  QUERY("INSERT into udpCommandLog (time, type, messageCounter,  sender,  receiver,  function,  params, functionData, senderSubscriberData,  receiverSubscriberData, udpDataLogId,senderObj,fktId) 
        values('$time','$messageType','$messageCounter','$senderSubscriberDataDebugStr','$receiverSubscriberDataDebugStr','$functionStr','$paramsStr','$functionData', '$senderSubscriberData','$receiverSubscriberData','$udpDataLogId','$senderObj','$fktId')");
}
?>