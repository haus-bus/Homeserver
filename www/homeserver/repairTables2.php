<?php
include("/var/www/homeserver/include/all.php");

if ($i=="") $i=1;

echo "Dieser Vorgang kann mehrere Minuten dauern!<hr>";

$act=1;
$repaired=0;

$erg = query("SHOW TABLES FROM homeserver");
while ($row = mysqli_fetch_row($erg))
{
	if ($act<$i)
	{
		 $act++;
		 continue;
	}

	$tableName = $row[0];
  echo "Prüfe Tabelle $i: $tableName -> ";

  $i++;
	
  $erg2 = query("describe $tableName");
  $obj= mysqli_fetch_object($erg2);
	if ($obj->Extra=="auto_increment")
	{
		 $field = $obj->Field;
     $erg2 = query("select max($field) from $tableName");
     $row=MYSQLi_FETCH_ROW($erg2);
     $max = $row[0];

     $erg2 = query("SHOW TABLE STATUS FROM homeserver LIKE '$tableName'");
     $status = mysqli_fetch_object($erg2);
     $autoInc = $status->Auto_increment;

     if ($autoInc<$max+1)
     {
     	 echo "Fehlerhafter autoInc $autoInc != ".($max+1).": repariere <br>";
     	 QUERY("ALTER TABLE $tableName AUTO_INCREMENT = 1;");
     	 $repaired=1;
     	 break;
     }
     else echo "OK <br>";
	}
	else echo "OK <br>";
}

if ($repaired!=1) die("Überprüfung abgeschlossen");

for ($i=0;$i<50;$i++)
{
	 echo "                                                                            ";
	 flush();
	 ob_flush();
}
sleep(5);
echo "<script>top.location='repairTables2.php?i=$i';</script>";
  

?>