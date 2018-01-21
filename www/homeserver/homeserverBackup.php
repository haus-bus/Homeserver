<?php
$dir = "/var/lib/mysql/homeserver/";
$dh  = opendir($dir);

while (false !== ($filename = readdir($dh))) 
{
	  if (strpos($filename,"udpcommandlog")!==FALSE) continue;
	  if (strpos($filename,"udpdatalog")!==FALSE) continue;
	  if (strpos($filename,"udphelper")!==FALSE) continue;
	  if (strpos($filename,"trace")!==FALSE) continue;
	  if (strpos($filename,"lastreceived")!==FALSE) continue;
	  if (strpos($filename,"graphdata")!==FALSE) continue;
	  
	  if (strpos($filename,".frm")!==FALSE)
	  {
	  	 exec("mysqlhotcopy --addtodest --allowold homeserver./".substr($filename,0,strpos($filename,".frm"))."/ /homeserverBackup/db/ \n");
	  }
	  
}
die("ok");
?>
