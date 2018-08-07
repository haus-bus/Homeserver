<?php

if ($_SERVER["DOCUMENT_ROOT"]=="")
{
	$_SERVER["DOCUMENT_ROOT"]="/var/www";
	$action = $argv[1];
	$snapshot = $argv[2];
}

include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($recover!="")
{
	 if (strlen($backupUserId)<2) $error="Backup UserID ungültig";
	 else
	 {
	   if ($confirm==1)
	   {
	 	    $recover=substr($recover,6,4)."-".substr($recover,3,2)."-".substr($recover,0,2);
	 	  
	 	    exec("wget \"http://www.haus-bus.de/backup.php?userId=$backupUserId&action=restore&date=$recover\" -O restore.tar.gz");
	 	    if (!file_exists("restore.tar.gz")) die("Fehler: Datei wurde nicht runtergeladen!");
	 	    $message = "Download erfolgreich. Das Backup wird nun eingespielt. Das kann einige Minuten dauern....";
	 	
	 	    $erg = QUERY("select id from featureInstances where objectId='800981249' limit 1");
        if ($obj=mysqli_fetch_OBJECT($erg)) callObjectMethodByName(800981249, "exec", array("command"=>"-1"));
        else die("Der Raspberry wurde noch nicht als Busteilnehmer gefunden. Bitte zuerst einmal den Controllerstatus aktualisieren!");
	   }
	   else showMessage("Soll die Datenbank, die Webapplikation und alle Skripte im Verzeichnis User<br> aus dem Onlinebackup vom <b>$recover</b> wiederhergestellt werden?", "Wiederherstellung aus Onlinebackup", "editOnlineBackup.php?recover=$recover&confirm=1&backupUserId=$backupUserId", "JA, Wiederherstellung durchführen", "editOnlineBackup.php", "NEIN, zurück");
	 }
}

if ($action=="snapshot")
{
	 if (strlen($backupUserId)<2) $error="Backup UserID ungültig";
   else
	 {
  	 if ($confirm==1)
	   {
	 	    callObjectMethodByName(800981249, "exec", array("command"=>"-2"));
	 	    $message = "Snapshot wird erstellt. Das kann eine Weile dauern. Der Homeserver reagiert in der Zeit ggf. nicht auf Nachrichten.<br>Bitte keinen weiteren Snapshot erstellen, solange der erste nicht erledigt ist.";
   	 }
  	 else showMessage("Mit der Snapshotfunktion wird der aktuelle Zustand des Homeservers im Backup gespeichert.<br>Ein ggf. vorhandener Snapshot wird dabei überschieben.<br>Während der Erstellung des Snapshots reagiert der Homeserver nicht auf Busnachrichten!<br><br>Soll nun ein Snapshot erstellt werden?", "Snapshot erstellen", "editOnlineBackup.php?action=snapshot&confirm=1&backupUserId=$backupUserId", "Ja, Snapshot erstellen", "editOnlineBackup.php", "NEIN, zurück");
   }
}

if ($action=="backup")
{
	$erg = QUERY("select paramKey, paramValue from basicConfig where paramKey = 'onlineBackup' or paramKey='proxy' or paramKey='proxyPort' limit 3");
  while($row = mysqli_fetch_ROW($erg))
  {
  	if ($row[0]=="onlineBackup") $backupUserId=$row[1];
  	else if ($row[0]=="proxy") $proxy=$row[1];
  	else if ($row[0]=="proxyPort") $proxyPort=$row[1];
  	else echo "unbekannter parameter ".$row[0];
  }
  
  if ($backupUserId!="")
  {
  	$myVersion = urlencode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/homeserver/version2018.chk"));
		$result = file_get_contents("http://www.haus-bus.de/backup.php?userId=$backupUserId&action=list&version=$myVersion", false, getStreamContext());

	  if (substr($result,0,2)=="OK")
	  {
	  	 $backupFile = "/homeserverBackup.tar.gz";
	  	 if (file_exists($backupFile))
	  	 {
	  	 	  if (time()-filemtime($backupFile)<60*60)
	  	 	  {
	  	 	  	 echo "Übertrage backup an $backupUserId";
             
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_URL,"https://haus-bus.secure-stores.de/backup.php?userId=$backupUserId&snapshot=$snapshot");
             if ($proxy!="") curl_setopt($ch, CURLOPT_PROXY, $proxy.":".$proxyPort);

             curl_setopt($ch, CURLOPT_POST,1);
             $cfile = new CURLFile($backupFile,'application/tar+gzip','myfile');
             $post = array('extra_info' => '123456','myfile'=>$cfile);
             curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
             //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
             //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
             //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
             curl_setopt($ch, CURLOPT_VERBOSE, true);
             $result=curl_exec ($ch);
             echo $result."\n";
             print_r(curl_getinfo($ch));
             echo "\n";
             curl_close ($ch);
             exit;
	  	 	  }
	  	 	  else die("Backupdatei zu alt. Fehler im Backup ?"); 
	  	 }
	  	 else die("Backupdatei nicht vorhanden");
	  }
	  else die("Ungültiger Backup User $backupUserId konfiguriert");
  }
  else die("Kein Backup User konfiguriert");
  
  exit;
}


if ($submitted==1)
{
  QUERY("DELETE from basicConfig where paramKey = 'onlineBackup' limit 1");
  QUERY("INSERT into basicConfig (paramKey,paramValue) values('onlineBackup','$backupUserId')");
  
  $message="Einstellung wurde gespeichert.";
}

setupTreeAndContent("editOnlineBackup_design.html", $message);

$erg = QUERY("select paramValue from basicConfig where paramKey = 'onlineBackup' limit 1");
if($row = mysqli_fetch_ROW($erg)) $backupUserId=$row[0];


$html = str_replace("%BACKUP_USER_ID%", $backupUserId, $html);

if ($backupUserId!="")
{
	 $myVersion = urlencode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/homeserver/version2018.chk"));
	 $result = file_get_contents("http://www.haus-bus.de/backup.php?userId=$backupUserId&action=list&version=$myVersion", false, getStreamContext());
	 if (substr($result,0,2)!="OK") $online="Fehler: ".$result;
	 else
	 {
	 	  $online="";
	 	  $parts = explode(",",$result);
	 	  for ($i=1;$i<count($parts);$i++)
	 	  {
	 	  	 $act = $parts[$i];
	 	  	 if (strpos($act,"snapshot")!==FALSE)
	 	  	 {
	 	  	 	 $snapshot.="<li> ".str_replace("snapshot","",$act)." [<a href='editOnlineBackup.php?recover=snapshot&backupUserId=$backupUserId'>wiederherstellen</a>]<br>";	 
	 	  	 }
	 	  	 else
	 	  	 {
	 	  	   $ident = substr($act,0,10);
  	 	  	 $online.="<li> $act [<a href='editOnlineBackup.php?recover=".$ident."&backupUserId=$backupUserId'>wiederherstellen</a>]<br>";	 	  	 
  	 	   }
	 	  }
	 	  $html = str_replace("%ONLINE%", $online, $html);
	 }
}

$html = str_replace("%ONLINE%", $online, $html);
$html = str_replace("%SNAPSHOT%", $snapshot, $html);

show();

?>