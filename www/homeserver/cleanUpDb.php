<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/dataBaseIntegrity.php");

if ($dry=="") $dry=1;
checkReferenceIntegrity($dry);
checkOldController();
echo "Alles geprüft";

function checkOldController()
{
	if ($clean!=1) echo "Prüfe auf alte Controller .... <br>";
		
	global $clean;

  if ($clean=="controller")
  {
  	QUERY("DELETE from controller where online='0' or bootloader='1' order by id");
  	die("<script>location='cleanUpDb.php';</script>");
  }

  $foundErrors=0;
  
  $erg = QUERY("select id, name, online, bootloader from controller where online='0' or bootloader='1' order by id");
  while($obj=mysqli_fetch_object($erg))
  {
  	$foundErrors=1;
  	echo "<li> ".$obj->name;
  	if ($obj->bootloader=='1') echo " [Bootloader] <br>";
  	else echo " [Offline] <br>";
  }
  if ($foundErrors==1) die("<a href='cleanUpDb.php?clean=controller'>Controller aufräumen</a>");
  else "Alle alten Controller geprüft <br>";
}

?>