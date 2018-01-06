<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action=="broadcast")
{
	callInstanceMethodForObjectId( 45057, 219);
	die("send");
}

if($action=="renewTiming" && $network>0)
{
	renewTimingOfNetwork($network);
	flushOut("<br><b>&nbsp;&nbsp;&nbsp;<a href='i2cNetwork.php'>weiter</a></b><br><br>");
	exit;
}

if($action=="renewAllTimings")
{
	$erg = QUERY("select distinct network from i2cNetworks order by network");
	while($obj=MYSQL_FETCH_OBJECT($erg))
	{
		 renewTimingOfNetwork($obj->network);
	}
	
	flushOut("<br><b>&nbsp;&nbsp;&nbsp;<a href='i2cNetwork.php'>weiter</a></b><br><br>");
	exit;
}

if ($action=="csv")
{
	 $erg = QUERY("select * from controller");
	 while($obj=MYSQL_FETCH_OBJECT($erg))
	 {
		 $deviceIds[$obj->id]=getDeviceId($obj->objectId);
		 $deviceNames[$obj->id]=$obj->name;
	 }
	 
	 $result="Netzwerk;Sender-DeviceId;Empfänger-DeviceId;SCL;SDA\n";
	 $erg = QUERY("select * from i2cTimings order by networkId,senderId") or die(MYSQL_ERROR());
	 while($obj=MYSQL_FETCH_OBJECT($erg))
	 {
	 	  $result.=$obj->networkId.";".$deviceIds[$obj->senderId].";".$deviceIds[$obj->receiverId].";".$obj->scl.";".$obj->sda."\n";
	 }
	 
	 $file="busTimings.csv";
	 file_put_contents($file,$result);
	 $size = filesize($file);
   header("Content-type: application/octet-stream");
   header("Content-disposition: attachment; filename=".$file);
   header("Content-Length: ".$size);
   header("Pragma: no-cache");
   header("Expires: 0");
   readfile($file);
}

function renewTimingOfNetwork($networkId)
{
	$erg = QUERY("select * from controller where online='1' and bootloader!=1");
	while($obj=MYSQL_FETCH_OBJECT($erg))
	{
		 if ($obj->majorRelease==0 && $obj->minorRelease<80) $error.="Fehler: Controller $obj->name hat zu alte Firmware Version $obj->minorRelease geladen <br>";
		 $deviceIds[$obj->id]=getDeviceId($obj->objectId);
		 $deviceNames[$obj->id]=$obj->name;
	}
	if ($error!="") die($error."<b>Bitte zunächst die Controllerfirmware aktualisieren</b>");

  flushOut("<br><table width=95% align=center><tr><td>");
  flushOut("Ermittle Bus-Timing von Netzwerk $networkId <hr>");
  
  QUERY("DELETE from i2cTimings where networkId='$networkId'");
   
  $erg = QUERY("select * from i2cNetworks where network='$networkId' order by id");
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
  	// einmal broadcast alle werte löschen
  	callInstanceMethodForObjectId( 45057, 222);
  	sleepMs(200);
  	
  	$gatewayObjectId = getObjectIdOfFirstGateway($obj->controllerId);
 	  callObjectMethodByName( $gatewayObjectId, "checkBusTiming");
 	  sleep(1);

    $erg2 = QUERY("select * from i2cNetworks where network='$networkId' order by id");
    while($obj2=MYSQL_FETCH_OBJECT($erg2))
    {
    	$gatewayObjectId = getObjectIdOfFirstGateway($obj2->controllerId);
      $result = callObjectMethodByNameAndRecover ( $gatewayObjectId, "getBusTiming", "", "BusTiming", 3, 2, 0 );    	  
   	  $timings=$result[0]->dataValue;
   	  
   	  $parts = explode(";",$timings);
   	  $sclParts=explode(",",$parts[0]);
   	  $sdaParts=explode(",",$parts[8]);
   	  
   	  $scl = $sclParts[0]+$sclParts[1]*256;
   	  $sda = $sdaParts[0]+$sdaParts[1]*256;
   	  
   	  flushOut("<b>".$deviceIds[$obj2->controllerId]." ".$deviceNames[$obj2->controllerId]."</b><br>Timings: ".$timings."<br>SCL: $scl SDA: $sda <br>");
   	  
  	 	QUERY("INSERT into i2cTimings (networkId,senderId,receiverId,scl,sda) values('$networkId','$obj->controllerId','$obj2->controllerId','$scl','$sda')");
    }
  }
   
  flushOut("</td></tr></table>");
}

function getObjectIdOfFirstGateway($controllerId)
{
	$erg = QUERY("select objectId from featureInstances where controllerId='$controllerId' and featureClassesId='27' order by objectId limit 1");
 	$obj = MYSQL_FETCH_OBJECT($erg);
 	if (getInstanceId($obj->objectId)!=1) die("Softwarefehler. Bitte Herm Bescheid geben: ".$obj->objectId." für Controler $controllerId");
 	return $obj->objectId;
}

if ($action=="renew")
{
//	updateControllerStatus();
	
	$erg = QUERY("select * from controller where online='1' and bootloader!=1");
	while($obj=MYSQL_FETCH_OBJECT($erg))
	{
		 if ($obj->majorRelease==0 && $obj->minorRelease<80) $error.="Fehler: Controller $obj->name hat zu alte Firmware Version $obj->minorRelease geladen <br>";
		 $deviceIds[$obj->id]=getDeviceId($obj->objectId);
		 $deviceNames[$obj->id]=$obj->name;
	}
	
	if ($error!="") die($error."<b>Bitte zunächst die Controllerfirmware aktualisieren</b>");
	else
	{
	
		 /*
		 BusTiming-Messung
     A. ermitteln der Busnetze (LAN, I2C)
        1. auf einen I2C Controller, der kein LAN-Gateway hat und noch keinem I2C-Netz zugeordnet ist, das Kommando checkBusTiming schicken
        2. Broadcast-Abfrage auf Gateway1 Objekten ( receiverId = 0x0000B001) Funktion getBusTiming
        3. alle Antworten, die Werte ungleich 0 enthalten gehören zu einem I2C Netzwerk
        4. Schritte 1-3 so oft wiederholen bis alle Controller, die online sind, einem Netzwerk zugeordnet sind
     */
     
     // Netzwerkcontroller ermitteln
    flushOut("<br><table width=95% align=center><tr><td>");
    flushOut("Ermittle Ethernetcontroller<hr>");
    $erg = MYSQL_QUERY("select controller.id,controller.name from controller join featureInstances on (featureInstances.controllerId = controller.id) where featureClassesId='21' and size!=999 and online='1' order by controller.id") or die(MYSQL_ERROR());
    while($obj=MYSQL_FETCH_OBJECT($erg))
    {
    	 $netzwerkController[$obj->id]=$obj->name;
    	 flushOut("DeviceId ".$deviceIds[$obj->id]." - ".$obj->name."<br>");
    }
    
    QUERY("TRUNCATE table i2cNetworks");
    QUERY("TRUNCATE table i2cTimings");
 
    // Netzwerke abklappern
    $network=0;
    $erg = QUERY("select id,name from controller where size!=999 and online='1' and bootloader!=1 order by id");
    while($obj=MYSQL_FETCH_OBJECT($erg))
    {
    	if (isset($netzwerkController[$obj->id])) continue;
    	if ($done[$obj->id]==1) continue;
    	
    	$network++;
    	flushOut("<br>Ermittle Teilnehmer vom Netzwerk $network<hr>");

      flushOut("DeviceId ".$deviceIds[$obj->id]." ".$deviceNames[$obj->id]." gehört zum Netzwerk<br>");
      QUERY("INSERT into i2cNetworks (network, controllerId,ethernet) values('$network','$obj->id','0')");
      $done[$obj->id]=1;
    	
     	// einmal broadcast alle werte löschen
    	callInstanceMethodForObjectId( 45057, 222);
    	sleepMs(200);

 	  	$gatewayObjectId = getObjectIdOfFirstGateway($obj->id);
   	  callObjectMethodByName( $gatewayObjectId, "checkBusTiming");
   	  sleep(1);

      /*
   	  // Ergebnisse einsammeln per Broadcast
   	  $rememberedId =  $lastLogId;
   	  callInstanceMethodForObjectId( 45057, 219);
   	  sleep(2);
       unset($inserted);  
   	  $erg3 = QUERY("select senderObj,functionData from udpCommandLog where id>'$rememberedId' and fktId='129' order by id");
   	  while($obj3=MYSQL_FETCH_OBJECT($erg3))
   	  {
	  	  $data=unserialize($obj3->functionData);

   	  */

   	  // Ergebnisse einsammeln
     	$erg2 = MYSQL_QUERY("select * from controller where size!=999 and online='1' and bootloader!=1") or die(MYSQL_ERROR());
	    while($obj2=MYSQL_FETCH_OBJECT($erg2))
	    {
	    	if ($done[$obj2->id]==1) continue;
	    	
	 	  	$gatewayObjectId = getObjectIdOfFirstGateway($obj2->id);
        $result = callObjectMethodByNameAndRecover ( $gatewayObjectId, "getBusTiming", "", "BusTiming", 3, 2, 0 );    	  
	  	  $timings=$result[0]->dataValue;
	  	  
	  	  $foundValid=0;
	  	  $parts = explode(";",$timings);
	  	  foreach($parts as $partVal)
	  	  {
	  	  	$internParts = explode(",",$partVal);
	  	  	$first = $internParts[0];
	  	  	$second = $internParts[1];
	  	  	if ($first>0 || $second>0)
	  	  	{
	  	  		$foundValid=1;
	  	  		break;
	  	  	}
	  	  }
	  	  
	  	  if ($foundValid==1)
	  	  {
	  	    $show=""; $ethernet="";
	  	    if (isset($netzwerkController[$obj2->id])) {$show = "als Ethernetgateway"; $ethernet=1;}
	  	    
 	        flushOut("DeviceId ".$deviceIds[$obj2->id]." ".$deviceNames[$obj2->id]." gehört zum Netzwerk $show <br>");
  	      QUERY("INSERT into i2cNetworks (network, controllerId,ethernet) values('$network','$obj2->id','$ethernet')");
  	      $done[$obj2->id]=1;
	  	  }
    	}
    }
	}
	
	flushOut("<br><b><a href='i2cNetwork.php?action=renewAllTimings'>weiter</a></b>");
	exit;
}

function flushOut($message)
{
	 echo "<font face=verdana size=2>".$message;
	 flushIt();
}


setupTreeAndContent("i2cNetwork_design.html", $message);

$erg = MYSQL_QUERY("select * from controller where online='1' and bootloader!=1 and size!=999") or die(MYSQL_ERROR());
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	 $deviceIds[$obj->id]=getDeviceId($obj->objectId);
	 $deviceNames[$obj->id]=$obj->name;
	 $checked[$obj->id]=0;
}

$networksNormal="";
$networksEthernet="";
$erg = QUERY("select * from i2cNetworks order by network");
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	 $checked[$obj->controllerId]=1;
	 if ($obj->ethernet==1)
	 {
	 	 if ($networksEthernet[$obj->network]!="") $networksEthernet[$obj->network].=", ";
	 	 $networksEthernet[$obj->network].=$deviceIds[$obj->controllerId]." ".$deviceNames[$obj->controllerId];
	 }
	 else
	 {
	 	 if ($networksNormal[$obj->network]!="") $networksNormal[$obj->network].=", ";
	 	 $networksNormal[$obj->network].=$deviceIds[$obj->controllerId]." ".$deviceNames[$obj->controllerId];
	 }
}
	
$networkTag = getTag("%NETWORK%",$html);
$networks="";
foreach ($networksNormal as $networkId=>$elements)
{
	$actTag = $networkTag;
	$actTag = str_replace("%NETWORK_ID%",$networkId,$actTag);
	$actTag = str_replace("%ETHERNET%",$networksEthernet[$networkId],$actTag);
	$actTag = str_replace("%I2C%",$elements,$actTag);
	$networks.=$actTag;
	
	if (strlen($networksEthernet[$networkId])<2) $networksEthernet[$networkId]="-";
}

$html=str_replace("%NETWORK%",$networks,$html);

$error="";
foreach($checked as $controllerId=>$status)
{
	 if ($status==0) $error.=$deviceIds[$controllerId]." ".$deviceNames[$controllerId].", ";
}
if ($error!="") $error="<b>Warnung:</b> Nicht alle Controller wurden einem Netwerk zugeordnet. Bitte Topologie neu ermitteln<br>Fehlende Controller: ".substr($error,0,strlen($error)-1)."<br>";

$html=str_replace("%ERROR%",$error,$html);


$networkTimingTag = getTag("%NETWORK_TIMINGS%",$html);
$matrix="";
foreach($networksEthernet as $network=>$member)
{
	 $actTag = $networkTimingTag;
	 $actTag = str_replace("%NETWORK_ID%",$network,$actTag);
	 $actTag = str_replace("%NETWORK_ETHERNET%",$member,$actTag);
	 
	 unset($senderData);
	 unset($receiverData);
	 $erg = QUERY("select * from i2cTimings where networkId='$network' order by senderId");
	 while($obj=MYSQL_FETCH_OBJECT($erg))
	 {
	 	  $senderData[$obj->senderId][$obj->receiverId]="1";
	 	  $receiverData[$obj->receiverId][$obj->senderId]["scl"]=$obj->scl;
	 	  $receiverData[$obj->receiverId][$obj->senderId]["sda"]=$obj->sda;
	 }
	 
	 $matrix="<table class='bordered'><tr><td class='borderedTd'></td>";
	 foreach ($senderData as $senderId => $dummy)
	 {
	 	   $matrix.="<td class='borderedTd' align=center><b><a title='".$deviceNames[$senderId]."'>".$deviceIds[$senderId]."</a></b></td>";
	 }

         $matrix.="<td class='borderedTd' rowspan=2 align=center>&nbsp;&nbsp;&nbsp;&nbsp;SCL<br>&nbsp;&nbsp;&nbsp;&nbsp;Min Max</td><td class='borderedTd' rowspan=2 align=center>SDA<br>&nbsp;Min Max</td>";
	 
	 $matrix.="</tr><tr><td class='borderedTd'></td>";
	 foreach ($senderData as $senderId => $dummy)
	 {
	 	   $matrix.="<td class='borderedTd'>SCL SDA</td>";
	 }

	 
	 $matrix.="</tr>";
	 
         $minSclAbsolut="";
         $maxSclAbsolut="";
         $minSdaAbsolut="";
         $maxSdaAbsolut="";
         $minSclZeile="";
         $maxSclZeile="";
         $minSdaZeile="";
         $maxSdaZeile="";
         unset($minSclSpalte);
         unset($maxSclSpalte);
         unset($minSdaSpalte);
         unset($maxSdaSpalte);

	 foreach ($receiverData as $receiver => $arr)
	 {
           $matrix.="<tr><td class='borderedTd' align=center><b><a title='".$deviceNames[$receiver]."'>".$deviceIds[$receiver]."</a></b></td>";

           $minSclZeile="";
           $maxSclZeile="";
           $minSdaZeile="";
           $maxSdaZeile="";
	  
           $spalte=0;	  
           foreach ($arr as $senderId => $val)
   	   {
             $actScl = $val["scl"];
             $actSda = $val["sda"];

             if (abs($actScl-$actSda)>2) $fett="bgcolor=#f58EA8"; else $fett="";
       	     $matrix.="<td class='borderedTd' align=right $fett>".$actScl." ".$actSda."</td>";

             if ($minSclAbsolut=="" || $actScl<$minSclAbsolut) $minSclAbsolut=$actScl;
             if ($maxSclAbsolut=="" || $actScl>$maxSclAbsolut) $maxSclAbsolut=$actScl;
             if ($minSdaAbsolut=="" || $actSda<$minSdaAbsolut) $minSdaAbsolut=$actSda;
             if ($maxSdaAbsolut=="" || $actSda>$maxSdaAbsolut) $maxSdaAbsolut=$actSda;

             if ($minSclZeile=="" || $actScl<$minSclZeile) $minSclZeile=$actScl;
             if ($maxSclZeile=="" || $actScl>$maxSclZeile) $maxSclZeile=$actScl;
             if ($minSdaZeile=="" || $actSda<$minSdaZeile) $minSdaZeile=$actSda;
             if ($maxSdaZeile=="" || $actSda>$maxSdaZeile) $maxSdaZeile=$actSda;

             if ($minSclSpalte[$spalte]=="" || $actScl<$minSclSpalte[$spalte]) $minSclSpalte[$spalte]=$actScl;
             if ($maxSclSpalte[$spalte]=="" || $actScl>$maxSclSpalte[$spalte]) $maxSclSpalte[$spalte]=$actScl;
             if ($minSdaSpalte[$spalte]=="" || $actSda<$minSdaSpalte[$spalte]) $minSdaSpalte[$spalte]=$actSda;
             if ($maxSdaSpalte[$spalte]=="" || $actSda>$maxSdaSpalte[$spalte]) $maxSdaSpalte[$spalte]=$actSda;

                          
             $spalte++;
           }
           $matrix.="<td class='borderedTd' align=right>&nbsp;&nbsp;&nbsp;&nbsp;".$minSclZeile." ".$maxSclZeile."</td><td class='borderedTd' align=right>&nbsp;".$minSdaZeile." ".$maxSdaZeile."</td>";

    	   $matrix.="</tr>";
	 }
         
         $matrix.="<tr><td class='borderedTd'><br>Min- SCL SDA<br>Max-SCL SDA</td>";
         for ($i=0;$i<$spalte;$i++)
         {
       	     $matrix.="<td class='borderedTd' align=right><br>".$minSclSpalte[$i]." ".$minSdaSpalte[$i]."<br>".$maxSclSpalte[$i]." ".$maxSdaSpalte[$i]."</td>";
         }

         $matrix.="<td class='borderedTd' align=right valign=top>".$minSclAbsolut." ".$maxSclAbsolut."</td><td valign=top>&nbsp;".$minSdaAbsolut." ".$maxSdaAbsolut."</td>";

 	 $matrix.="</tr>";

	 $matrix.="</table>";
	 
	 $actTag = str_replace("%MATRIX%",$matrix,$actTag);	
	 $networkTimings.=$actTag;
}

$html=str_replace("%NETWORK_TIMINGS%",$networkTimings,$html);

if ($action=="email")
{
	 $pos=0;
	 $errorCounter=0;
	 while($errorCounter<100)
	 {
	 	  $errorCounter++;
	 	  $pos = strpos($html,"<a ");
	 	  if ($pos===FALSE) break;
	 	  $pos2 = strpos($html,">",$pos);
	 	  $pos2 = strpos($html,">",$pos2+1);
	 	  $html = substr($html,0,$pos-1).substr($html,$pos2+2);
	 }
	
	 $file="busdiagnose.html";
	 file_put_contents($file,$html);
	 $size = filesize($file);
   header("Content-type: application/octet-stream");
   header("Content-disposition: attachment; filename=".$file);
   header("Content-Length: ".$size);
   header("Pragma: no-cache");
   header("Expires: 0");
   readfile($file);
}
show();
?>