<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($action=="generateDeviceIds")
{
  if ($confirm==1)
  {
     callObjectMethodByName($BROADCAST_OBJECT_ID, "generateRandomDeviceId");
     sleep(2);
     callObjectMethodByName($BROADCAST_OBJECT_ID, "getConfiguration");
     sleep(3);
     $ok=1;
     $nrController=0;
     $myFunctionId = getObjectFunctionIdByName($BROADCAST_OBJECT_ID, "Configuration");
     $erg = MYSQL_QUERY("select functionData,senderSubscriberData from udpCommandLog where type='RESULT' and id>'$lastLogId'") or die(MYSQL_ERROR());
     while($obj=MYSQL_FETCH_OBJECT($erg))
     {
        $functionData = unserialize($obj->functionData);
        if ($functionData->functionId==$myFunctionId)
        {
           $nrController++;
           if ($found[$functionData->paramData[0]->dataValue]==1)
           {
             $message="Fehler. Kollision erkannt bei DeviceId ".($functionData->paramData[0]->dataValue)."<br>Bitte Funktion wiederholen!<br><br>";
             $ok=0;
             break; 
           }               
        }               
     }
     updateControllerStatus();
     if ($ok==1) $message="DeviceIDs erfolgreich neu vergeben. Anzahl erkannter Controller: ".$nrController."<br><br>";
  }
  else showMessage("Nachdem die Controller Device IDs neu vergeben wurden, müssen die Controller, Features und Regeln neu zugeordnet werden.<br>Die Aktion kann nicht rückgängig gemacht werden !","Achtung!","firstInstall.php?action=generateDeviceIds&confirm=1","Ja, IDs neu vergeben","firstInstall.php","Abbruch");
}

setupTreeAndContent("firstInstall_design.html", $message);
if ($exit==1) show();

$statusOk=1;
$someOffline=0;
$lastDeviceId="";
$erg = MYSQL_QUERY("select objectId,online from controller where bootloader!='1' order by objectId") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
   $obj->objectId = $obj->objectId;
   if ($obj->online==0) $someOffline=1;
   
   $actDeviceId = getDeviceId($obj->objectId);
   if ($actDeviceId==$lastDeviceId)
   {
     $statusOk=0;
     break;
   }
   $lastDeviceId=$actDeviceId;
}

if ($statusOk==1)
{
  $deviceIdStatus="OK";
  if ($someOffline==1) $deviceIdStatus.=", aber einige Controller sind nicht online";
}
else $deviceIdStatus="<b>Einge Device IDs sind doppel vergeben</b>";

$html = str_replace("%DEVICE_ID_STATUS%",$deviceIdStatus,$html);

show();

?>