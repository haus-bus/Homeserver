<?php
$myFile="../../user/gruppen.txt";

include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action=="new")
{
	 $erg = QUERY("Select id from groups where name='$gruppe' limit 1") or die(MYSQL_ERROR());
	 if ($obj=MYSQLi_FETCH_OBJECT($erg))
	 {
	 	  $content = file_get_contents($myFile);
	 	  $id=$obj->id;
	 	  $content.="####".$id."###".$gruppe;
	 	  file_put_contents($myFile,$content);
	 	  die("<script>location='index.php?action=refresh';</script>");
	 } 
	 else $error="Gruppe nicht gefunden: $gruppe";
}
else if ($action=="delete")
{
	 $content = file_get_contents($myFile);
   $parts = explode("####", $content);
   $found=0;
   $newContent="";
   foreach ((array)$parts as $entry)
   {
	   if ($entry=="") continue;
	   
	   $subParts = explode("###", $entry);
	   $myId=$subParts[0];
	   if ($id==$myId)
	   {
	   	 $found=1;
	   	 continue;
	   }
	   
	   $gruppe=$subParts[1];
	   
	   $newContent.="####".$myId."###".$gruppe;
   }
   
   if ($found==1) file_put_contents($myFile,$newContent);
   else $error = "Gruppe mit ID $id nicht gefunden";
}
else if ($action=="refresh")
{
	 callObjectMethodByName(800981249, "reloadUserPlugin");
	 $error="Refeshed!";
}

echo "<html>";
echo '<head><link rel="StyleSheet" href="/homeserver/css/main.css" type="text/css" />';
echo '<body><div class="contentWrap"  id="content" style="margin-right:16px;">';

if ($error!="") $error="<font color=#bb0000><b>".$error."</b></font><br>";

echo "<br><table width=95% align=center><tr><td>";
echo "<b>Gruppen Watchdog</b><hr>";
echo "Mit diesem Watchdog kann man \"Alles-Aus\" Gruppen durch den Raspberry überwachen und Aktoren automatisch nachgeschalten, die kein zugehöriges evOff Event geliefert haben. Das kann bei großen Gruppen sinnvoll sein (z.b. Alles aus mit mehr als 20 Aktoren), wo ggf. Aktionen oder Events verloren gehen können<br>";
echo "<br><b>INFO:</b><br>Aktuell wird nur der erste Eintrag einer Gruppe mit Aktivierungszustand ALLE überwacht!<br><br>";
echo "<br><b>Achtung:</b><br>Immer wenn sich etwas an der überwachten Gruppe ändert, muss man manuell hier den Watchdog mit dem folgenden Button refreshen!";
echo "<form action=index.php method='POST'><input type=hidden name='action' value='refresh'><input type=submit value='Refresh'></form>";

echo $error;

echo "<br><b>Neue Gruppe eintragen</b><br>";
echo "<form action=index.php method='POST'><input type=hidden name='action' value='new'>";
echo "<input type=text name=gruppe size=40> <input type=submit value='Gruppe zur Überwachung anmelden'><br><font size=2>(Name der Gruppe eintragen)</font></form>";
echo "<br><br>";
echo "<b>Aktuell überwachte Gruppen</b><br><br>";

$content = file_get_contents($myFile);
$parts = explode("####", $content);
foreach ((array)$parts as $entry)
{
	if ($entry=="") continue;
	
	$subParts = explode("###", $entry);
	$id=$subParts[0];
	$gruppe=$subParts[1];
  echo "<a href=\"index.php?action=delete&id=$id\"><img src=\"/homeserver/img/remove.gif\" title=\"löschen\"></a> &nbsp;&nbsp; $gruppe<br>";
}


echo "</td></tr></table></div>";

?>  