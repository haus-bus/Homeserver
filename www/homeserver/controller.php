<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted!="")
{
   MYSQL_QUERY("UPDATE featureinstances set checked='0'") or die(MYSQL_ERROR());
   updateControllerStatus();
   sleep($CONTROLLER_READ_TIMEOUT);
   $message="Controllerstatus wurde aktualisiert"; 
}

if ($broadcastPing==1) callObjectMethodByName($BROADCAST_OBJECT_ID, "ping");
else if ($broadcastReset==1) callObjectMethodByName($BROADCAST_OBJECT_ID, "reset");
else if ($broadcastSetTime==1)
{
	$data["weekTime"]=toWeekTime(date("N")-1,date("H"),date("i"));
	callObjectMethodByName($BROADCAST_OBJECT_ID, "setTime", $data);
	$message="Zeit wurde gemδί Serverzeit gestellt";
}

setupTreeAndContent("controller_design.html", $message);
show();

?>