<?php
require("include/dbconnect.php");

session_start();

$tables=array("controller","featureInstances","groups","rooms","roomFeatures");


$myLastUpdate=$_SESSION["lastTreeUpdate"];
$newMax=-1;

$erg = QUERY("SELECT forceUpdate from treeUpdateHelper where forceUpdate='1' limit 1");
if ($row=mysqli_fetch_ROW($erg))
{
	query("truncate table treeUpdateHelper");
	die("1");
}


foreach($tables as $actTable)
{
  $erg = QUERY("SELECT max(lastChange) from $actTable where lastChange>'$myLastUpdate' limit 1");
  if ($row=mysqli_fetch_ROW($erg)) if ($row[0]>$newMax) $newMax= $row[0];
}

if ($newMax!=-1)
{
	 $_SESSION["lastTreeUpdate"]=$newMax;
	 die("1");
}
die("0");

?>

