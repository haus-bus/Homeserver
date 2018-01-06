<?php
$server= "127.0.0.1";
$dbuser= "root";
$dbpasswort= "";
$datenbank= "homeserver";

$db = @MYSQL_CONNECT($server, $dbuser, $dbpasswort) or die ( "<script>setTimeout('location.reload()',3000);</script>Datenbank nicht erreichbar");
MYSQL_SELECT_DB($datenbank) or die ( "<H3>Datenbank nicht vorhanden</H3>");


function query($sql)
{
	 global $scriptStart;
	 global $debugTime;
	 
	 $start = microtime(TRUE);
	 $erg = MYSQL_QUERY($sql) or die("<br>".MYSQL_ERROR().debug_print_backtrace());
	 if ($debugTime==1)
	 {
	 	 $scriptDuration = (microtime(TRUE)-$scriptStart)*1000;
	 	 $queryDuration = (microtime(TRUE)-$start)*1000;
	 	echo "<nobr>".$scriptDuration."ms - ".$queryDuration." ms: $sql </nobr><br>";
	 }
	 return $erg;
	 
}
?>
