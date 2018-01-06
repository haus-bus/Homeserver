<?php
require("include/dbconnect.php");

session_start();

$tables=array("controller","featureInstances","groups","rooms","roomFeatures");


$myLastUpdate=$_SESSION["lastTreeUpdate"];
$newMax=-1;



foreach($tables as $actTable)
{
  $erg = MYSQL_QUERY("SELECT max(lastChange) from $actTable where lastChange>'$myLastUpdate' limit 1") or die(MYSQL_ERROR());
  if ($row=MYSQL_FETCH_ROW($erg)) if ($row[0]>$newMax) $newMax= $row[0];
}

if ($newMax!=-1)
{
	 $_SESSION["lastTreeUpdate"]=$newMax;
	 die("1");
}
die("0");

?>

