<?php
include ($_SERVER ["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

//callObjectMethodByName(508, "getModuleId", array("index"=>"0"));

if ($submitted == 1)
{
	  setupTreeAndContent ( "empty_design.html", $message );
    echo $html;
	  
	  liveOut("<b>Test:</b> $test");
	  unset($testControllerIds);
	  $controller="";
	  $erg = QUERY("select id, name, objectId from controller where online='1' and size!='999'");
    while($obj=mysqli_fetch_OBJECT($erg))
    {
    	  $var="controller".$obj->id;
    	  $var = $$var;
    	  if ($var==1)
    	  {
    	  	if ($controller!="") $controller.=", ";
    	  	$controller.=$obj->name;
    	  	$testControllerIds[$obj->id]=$obj->objectId;
        }
    }
    liveOut("<b>Gewählte Controller:</b> ".$controller);
    liveOut("<b>Parameter:</b> Runden = ".$rounds.", Verzögerung = ".$delay.", Überlappte Sendenrunden = ".$overlay);
    liveOut("");
    
    for ($round=1;$round<=$rounds;$round++)
    {
    	liveOut("Runde $round: ",0);
      
      QUERY("TRUNCATE table smoketest");
      QUERY("TRUNCATE table smoketestHelper");
    
      for ($overlapping=0;$overlapping<=$overlay;$overlapping++)
      {
        foreach ((array)$testControllerIds as $controllerId=>$controllerObjectId)
        {
        	//liveOut(getController($controllerObjectId)->name);
        	$logId=updateLastLogId();
          QUERY("insert into smoketest (controllerId,objectId,command,logid) value('$controllerId','$controllerObjectId','ModuleId','$logId')");
          callObjectMethodByName($controllerObjectId, "getModuleId", array("index"=>"0"));
          QUERY("insert into smoketest (controllerId,objectId,command,logid) value('$controllerId','$controllerObjectId','Configuration','$logId')");
     	    callObjectMethodByName($controllerObjectId, "getConfiguration");
          QUERY("insert into smoketest (controllerId,objectId,command,logid) value('$controllerId','$controllerObjectId','RemoteObjects','$logId')");
   	      callObjectMethodByName($controllerObjectId, "getRemoteObjects");
   	      if ($delay>0) sleepMs($delay);
   	    }
   	  }
   	  
   	  $i=0;
   	  $checkDelay=200;
   	  for ($i=0;$i<20;$i++)
   	  {
   	  	sleepMs($checkDelay);
   	  
   	    $errors=0;
   	    $erg = QUERY("select id, controllerId, objectId, command, logid from smoketest order by id") or die(MYSQL_ERROR());
   	    while($obj=MYSQLi_FETCH_OBJECT($erg))
   	    {
   	  	  $erg2 = QUERY("select id from udpcommandlog left join smoketestHelper on (smoketestHelper.commandLogId=udpcommandlog.id) where function='$obj->command' and senderObj='$obj->objectId' and id>$obj->logid and commandLogId is null limit 1");
    	  	if ($row2=MYSQLi_FETCH_ROW($erg2))
    	  	{
    	  		//liveOut($row2[0]." für ".$obj->command." von ".getController($obj->objectId)->name);
    	  		QUERY("INSERT into smoketestHelper (commandLogId) values('$row2[0]')");
    	  		QUERY("delete from smoketest where id='$obj->id' limit 1");
    	  	}
    	    else $errors++;
   	    }
   	    
   	    if ($errors==0)
   	    {
   	    	liveOut("Alle Antworten eingetroffen innerhalb von ".(($i+1)*$checkDelay)." ms");
   	    	break;
   	    }
   	  }
  
      $errors=0;
      $erg = QUERY("select id, controllerId, objectId, command, logid from smoketest order by id") or die(MYSQL_ERROR());
   	  while($obj=MYSQLi_FETCH_OBJECT($erg))
   	  {
   	  	$errors++;
      	liveOut("Fehlende Antwort $obj->command von Controller ".getController($obj->objectId)->name." ab ID ".$obj->logid);
      }
      
      if ($errors>0)
      {
      	liveOut("");
        liveOut("Smoketest fehlerhaft beendet");
        exit;
      }
 	  }
 	  
    QUERY("TRUNCATE table smoketest");
    QUERY("TRUNCATE table smoketestHelper");

    liveOut("");
    liveOut("Smoketest beendet");
    exit;
}

setupTreeAndContent ( "smoketest_design.html", $message );

$instanceTag = getTag("%INSTANCES%",$html);
$instances="";
$erg = QUERY("select id, name from controller where online='1' and size!='999' order by name");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $instanceTag;
  $actTag = str_replace("%CONTROLLER_NAME%",$obj->name, $actTag);
  $actTag = str_replace("%CONTROLLER_ID%",$obj->id, $actTag);
  $instances.=$actTag;
}
$html = str_replace("%INSTANCES%", $instances, $html);

show ();

?>