<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

set_time_limit(0);

if ($action=="Aktuelle Konfiguration speichern" || $action=="save")
{
	 if ($confirm==1)
	 {
 		 $message="Konfiguration wurde gespeichert.... <br>";

	 	 QUERY("TRUNCATE table recovery");
	 	 $actTime = time();

     $erg = QUERY("select id from featureFunctions where featureClassesId='$CONTROLLER_CLASSES_ID' and name='getConfiguration' limit 1");
     $row=MYSQL_FETCH_ROW($erg);
     $controllerConfigFktId = $row[0];
	 	 
     $erg = QUERY("select controller.name as ControllerName, objectId from controller where online='1' and size!='999'");
     while($obj=MYSQL_FETCH_OBJECT($erg))
     {
        callObjectMethodByName($obj->objectId, "getConfiguration");
        $result = waitForObjectResultByName($obj->objectId,5, "Configuration", $lastLogId,"funtionDataParams",0);
        if ($result==-1) $message.="Fehler bei $obj->ControllerName <br>";
        else
        {
          $config = mysql_real_escape_string(serialize($result));
          $sql = "INSERT into recovery (objectId, configuration, lastTime) values('$obj->objectId','$config','$actTime')";
          QUERY($sql);
          echo ". ";
          flushIt();
        }
        sleepMS(10);
     } 


     
	   $erg = QUERY("select featureInstances.name as featureInstanceName, featureInstances.featureClassesId, featureInstances.objectId,
                          featureClasses.name as featureClassName,
                          featureFunctions.id  as featureFunctionId,featureFunctions.name  as featureFunctionName,
                          controller.name as ControllerName, controller.objectId as controllerObjectId
                          from featureInstances
                          join controller on (controller.id = featureInstances.controllerId)
                          join featureClasses on (featureClasses.id = featureInstances.featureClassesId)
                          join featureFunctions on (featureInstances.featureClassesId=featureFunctions.featureClassesId) 
                          where featureFunctions.name='setConfiguration' and checked='1' and controller.size!='999'");
     while($obj=MYSQL_FETCH_OBJECT($erg))
     {
        callObjectMethodByName($obj->objectId, "getConfiguration");
        $result = waitForObjectResultByName($obj->objectId,5, "Configuration", $lastLogId,"funtionDataParams",0);
        if ($result==-1) $message.="Fehler bei $obj->featureClassName - $obj->featureInstanceName <br>";
        else
        {
          $config = mysql_real_escape_string(serialize($result));
          $sql = "INSERT into recovery (objectId, configuration, lastTime) values('$obj->objectId','$config','$actTime')";
          QUERY($sql);
          echo ". ";
          flushIt();
        }
     } 
	 }
	 else showMessage("Soll die aktuelle Konfiguration nun gespeichert werden?", "Konfiguration speichern", "recovery.php?action=save&confirm=1", "Ja, Speichern","recovery.php", "Nein, zur�ck");
}
else if ($action=="Konfiguration wiederherstellen" || $action=="recover")
{
	if ($confirm==1)
	{
		 if ($lastId=="") $lastId="-1";
		 $erg = MYSQL_QUERY("select id,name,objectId from controller where online='1' and bootloader!='1' and id>'$lastId' and size!='999' order by id");
		 while($obj=MYSQL_FETCH_OBJECT($erg))
		 {
		 	 echo "Wiederherstellung l�uft: ".$obj->name."<br>";
		 	 echo "<iframe src='editController.php?id=$obj->id&action=recover&callback=1' width=400 height=800 scrolling=0 border=0 frameborder=0></iframe>";
		 	 exit;
		 }
		 $message="Wiederherstellung beendet";
	}
	else showMessage("Soll die aktuelle Konfiguration nun wiederhergestellt werden?", "Konfiguration wiederherstellen", "recovery.php?action=recover&confirm=1", "Ja, Wiederherstellen","recovery.php", "Nein, zur�ck");
}

setupTreeAndContent("recovery_design.html", $message);

$erg = QUERY("select lastTime from recovery limit 1");
if ($row=MYSQL_FETCH_ROW($erg)) $html = str_replace("%LAST_TIME%",date("d.m.Y H:i", $row[0])." Uhr",$html);
else $html = str_replace("%LAST_TIME%"," - ",$html);

show();


?>