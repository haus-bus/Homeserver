<?php
$server= "127.0.0.1";
$dbuser= "root";
$dbpasswort= "";
$datenbank= "homeserver";

if ($waitForDb==1)
{
	while(true)
	{
	  $db = @MYSQLi_CONNECT($server, $dbuser, $dbpasswort,$datenbank);
	  if ($db===FALSE)
	  {
	  	 echo "waiting for db...."; 
	  	 sleep(1);
	  	 continue;
	  }
	  else break;
	}
}
else
{
	$db = @MYSQLi_CONNECT($server, $dbuser, $dbpasswort,$datenbank) or die ( "<script>setTimeout('location.reload()',3000);</script>Datenbank nicht erreichbar");
}
//mysqli_set_charset('utf_8');
query("SET collation_connection = latin1_swedish_ci");

function query_insert_id()
{ 
	 global $db;
	 return MYSQLi_INSERT_ID($db);
}

function query_real_escape_string($escapestr)
{ 
	 global $db;
	 return mysqli_real_escape_string ($db, $escapestr);
}

function query($sql, $ignoreErrors=FALSE)
{
	 global $scriptStart;
	 global $debugTime;
	 global $db;
	 
	 //echo $sql."\n";
	 
	 $start = microtime(TRUE);
	 if ($ignoreErrors) $erg = MYSQLi_QUERY($db, $sql);
	 else $erg = MYSQLi_QUERY($db, $sql) or die("<br>".MYSQLi_ERROR($db).debug_print_backtrace());
	 if ($debugTime==1)
	 {
	 	 $scriptDuration = (microtime(TRUE)-$scriptStart)*1000;
	 	 $queryDuration = (microtime(TRUE)-$start)*1000;
	 	echo "<nobr>".$scriptDuration."ms - ".$queryDuration." ms: $sql </nobr><br>";
	 }
	 return $erg;
	 
}
?>
