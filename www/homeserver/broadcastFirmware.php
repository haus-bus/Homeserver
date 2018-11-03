<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
header( 'Content-Encoding: none; ' );//disable apache compressed

$controllers = readControllers();

function statusOut($bytes, $fileSize, $blockSize)
{
  $percent = (int)($bytes*100/$fileSize);
  echo "<script>document.getElementById(\"status\").innerHTML=\"Updatestatus: $bytes/$fileSize Bytes - $percent%\";</script>";
}

if ($submitted!="")
{
  ob_end_flush();
  ob_start();
  
  setupTreeAndContent("fwUpdate.html");
  show(0);
  
  $blockSize=0;
  $erg = QUERY("select id, objectId,name,size from controller order by name");
  while($obj=mysqli_fetch_OBJECT($erg))
  {
    if ($controllers[$obj->id]->online!=1) continue;
    if ($obj->size=="999") continue;
    
    $found=0;
    $actFeature = "instar8".$obj->id;
    if ($$actFeature==1)
    {
    	$found=1;
    	$neededFirmware="AR8";
    }
    else
    {
      $actFeature = "instms6".$obj->id;
      if ($$actFeature==1)
      {
      	$found=1;
      	$neededFirmware="MS6";
      }
	  else
	  {
		  $actFeature = "instsd6".$obj->id;
		  if ($$actFeature==1)
		  {
			$found=1;
			$neededFirmware="SD6";
		  }
		  else
		  {
			  $actFeature = "instsd485".$obj->id;
			  if ($$actFeature==1)
			  {
				$found=1;
				$neededFirmware="SD485";
			  }
		  }		  
	  }
    }
    
    if ($found==0) continue;
    
    if ($blockSize==0)
    {
      callObjectMethodByName($obj->objectId, "getConfiguration");
      $result = waitForObjectResultByName($obj->objectId, 5, "Configuration", $lastLogId);
      $blockSize = getResultDataValueByName("dataBlockSize", $result);
    }
    
    if (getInstanceId($obj->objectId)!=$BOOTLOADER_INSTANCE_ID)
    {
      liveOut("<b>Bootloader wird aktiviert bei ".$obj->name."</b>");
      flushIt();
      callObjectMethodByName($obj->objectId, "reset");
      //sleep(2);
      sleepMs(500);
      $receiverObjectId= getObjectId(getDeviceId($obj->objectId), getClassId($obj->objectId), $BOOTLOADER_INSTANCE_ID);
      callObjectMethodByName($receiverObjectId, "ping");
      $result = waitForObjectResultByName($receiverObjectId,5, "pong", $lastLogId,"senderData");
      $objectId = $result->objectId;
      //updateControllerStatus();
    }
    
    $loadController[$obj->name]=$obj->objectId;
    if ($booterObjectId=="") $booterObjectId = getObjectId(getDeviceId($BROADCAST_OBJECT_ID), $CONTROLLER_CLASS_ID, $BOOTLOADER_INSTANCE_ID);
  }

  // Firmware laden
  $orig=$_FILES['userfile']['name'];
  if ($orig=="")
  {
     	 $newestFirmware="";
     	 $newestFirmwareTime=0;
     	 $handle = opendir("../firmware/");
       while (false !== ($file = readdir($handle))) 
       {
          if (strpos($file,$neededFirmware)!==FALSE && strpos($file,".bin")!==FALSE && strpos($file,"BOOTER")===FALSE && filemtime("../firmware/".$file)>$newestFirmwareTime)
          {
           	$newestFirmwareTime = filemtime("../firmware/".$file);
           	$newestFirmware = $file;
          }
       }
       closedir($handle);
     	 if ($newestFirmware=="") die("Fehler: Keine Datei gewählt und keine Defaultfirmware vorhanden -> $neededFirmware");
       
       $fwfile = "../firmware/".$newestFirmware;
       $show = $newestFirmware;
  }
  else
  {
    	$fwfile = $_FILES['userfile']['tmp_name'];
    	$show=$_FILES['userfile']['name'];
  }

  if (strpos($show,$neededFirmware)===FALSE) die("Fehler: Nicht kompatible Firmware. Erwartet wird: $neededFirmware");
    
  liveOut('');
  liveOut("<b>Firmware Update ...($show)</b>");
  $fileSize = filesize($fwfile);
  liveOut("Dateigröße: $fileSize Bytes");
  liveOut("Daten Blockgröße: ".$blockSize." Bytes");
  liveOut('');
  liveOut("<div id=\"status\">Updatestatus: 0/$fileSize Bytes - 0%</div>",0);

  $fd = fopen ($fwfile, "r");
  $ready=0;
  $firstWriteId=-1;
  $memoryStatusOk = getFunctionParamEnumValueByName($objectId, "MemoryStatus", "status", "OK");

  $rounds=0;
  while (!feof ($fd))
  {
    $buffer = fread($fd, $blockSize);
    $data["address"]=$ready;
    $data["data"]=$buffer;
    if ($firstWriteId==-1) $firstWriteId=$lastLogId;

    callObjectMethodByName($booterObjectId, "writeMemory",$data);
    
    sleepMs(500);
    
    /*
    $myLastLogId = $lastLogId;
    foreach($loadController as $name=>$objectId)
    { 
      $result = waitForObjectResultByName($objectId, 5, "MemoryStatus", $myLastLogId,"funtionDataParams", 1, 0);
      $memoryStatus = getResultDataValueByName("status", $result);
      if ($memoryStatus!=$memoryStatusOk)
      {
         liveOut($name." hat fehlerhaften MemoryStatus gemeldet: ".$result[0]->dataValue);
         exit;
      }
    }*/
    
    $ready+=strlen($buffer);
    if ($round%5==0) statusOut($ready, $fileSize, $blockSize);
    $i++;
    $rounds++;
    flushIt();
   }
   fclose($fd);

   liveOut("Übertragung erfolgreich beendet");
   liveOut("");
   liveOut("<b>Starte Controller neu...</b>");
   
   callObjectMethodByName($booterObjectId, "reset");
   flush();
   
   sleep(5);
   updateControllerStatus();
   liveOut("");
   liveOut("Firmwareupdate beendet");
   exit;
}

setupTreeAndContent("broadcastFirmware_design.html", $message);

$ar8Tag = getTag("%INSTANCES%",$html);
$ms6Tag = getTag("%TASTER_INSTANCES%",$html);
$sd6Tag = getTag("%TASTER_SD6_INSTANCES%",$html);
$sd485Tag = getTag("%TASTER_SD485_INSTANCES%",$html);

$instances="";
$erg = QUERY("select id, name from controller where firmwareId=1 order by name");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $ar8Tag;
  if ($controllers[$obj->id]->online!=1) continue;
  $actTag = str_replace("%FEATURE_NAME%",$obj->name, $actTag);
  $actTag = str_replace("%FEATURE_INSTANCE_ID%",$obj->id, $actTag);
  $instances.=$actTag;
}
$html = str_replace("%INSTANCES%", $instances, $html);

$instances="";
$erg = QUERY("select id, name from controller where firmwareId=2 order by name");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $ms6Tag;
  if ($controllers[$obj->id]->online!=1) continue;
  $actTag = str_replace("%FEATURE_NAME%",$obj->name, $actTag);
  $actTag = str_replace("%FEATURE_INSTANCE_ID%",$obj->id, $actTag);
  $instances.=$actTag;
}
$html = str_replace("%TASTER_INSTANCES%", $instances, $html);

$instances="";
$erg = QUERY("select id, name from controller where firmwareId=3 order by name");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $sd6Tag;
  if ($controllers[$obj->id]->online!=1) continue;
  $actTag = str_replace("%FEATURE_NAME%",$obj->name, $actTag);
  $actTag = str_replace("%FEATURE_INSTANCE_ID%",$obj->id, $actTag);
  $instances.=$actTag;
}
$html = str_replace("%TASTER_SD6_INSTANCES%", $instances, $html);

$instances="";
$erg = QUERY("select id, name from controller where firmwareId=4 order by name");
while($obj=mysqli_fetch_OBJECT($erg))
{
  $actTag = $sd485Tag;
  if ($controllers[$obj->id]->online!=1) continue;
  $actTag = str_replace("%FEATURE_NAME%",$obj->name, $actTag);
  $actTag = str_replace("%FEATURE_INSTANCE_ID%",$obj->id, $actTag);
  $instances.=$actTag;
}
$html = str_replace("%TASTER_SD485_INSTANCES%", $instances, $html);

show();

?>
