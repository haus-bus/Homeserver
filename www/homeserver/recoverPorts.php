<?php 
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

$tasterClassesId=getClassesIdByName("DigitalPort");

$erg = QUERY("select featureInstances.objectId,recovery.configuration from featureInstances join recovery on (recovery.objectId=featureInstances.objectId) where featureClassesId='$tasterClassesId' order by featureInstances.id");
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	$config = unserialize($obj->configuration);
	$ledMask = $config[0]->dataValue;
	$buttonMask = $config[1]->dataValue;

  unset($data);
  
  echo $ledMask." - ".$buttonMask.": <br>";	
  for ($i=0;$i<8;$i++)
	{
	  $bit = pow(2,$i);
	  echo $i.": ".($ledMask & $bit)." - ".($buttonMask & $bit)."<br>";
	  if (($ledMask & $bit) == $bit) {echo "led, <br>";$data["pin".$i]=21;}
	  else if (($buttonMask & $bit) == $bit) {echo "taster,<br>";	$data["pin".$i]=16;}
	  else {echo "nix,<br>";$data["pin".$i]=255;}
	}
	
	callObjectMethodByName($obj->objectId, "setConfiguration",$data);
}
?>