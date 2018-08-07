<?php
include("include/all.php");

if ($delete!="") 
{
	QUERY("delete from userData where userKey='$delete' limit 1");
	header("Location: editUserPluginData.php");
	exit;
}

if ($edit!="") 
{
	if ($submitted==1)
	{
	  QUERY("update userData set userValue='$value' where userKey='$edit' limit 1");
	  header("Location: editUserPluginData.php");
	  exit;
	}
	else
	{
		echo "<center><form action='editUserPluginData.php' method='POST'><input type=hidden name=edit value='$edit'><input type=hidden name=submitted value='1'>";
		$erg = QUERY("select userValue from userData where userKey='$edit' limit 1");
    $obj=mysqli_fetch_OBJECT($erg);
    $value=$obj->userValue;
		echo "Wert von Key $edit ändern <hr><br><input type=text name=value size=30 value='$value'><br><input type=submit value='Ändern'><br><br><br><br><br><a href='editUserPluginData.php'>abbrechen</a>";
		
		echo "</form>";
		exit;
	}
}

$html = file_get_contents("templates/editUserPluginData_design.html");
$elementsTag = getTag("%ELEMENTS%",$html);
$elements="";
$erg = QUERY("select * from userData order by userKey");
while($obj=mysqli_fetch_OBJECT($erg))
{
	  $actTag = $elementsTag;
	  $actTag = str_replace("%KEY_ENCODED%",urlencode($obj->userKey),$actTag);
	  $actTag = str_replace("%KEY%",$obj->userKey,$actTag);
	  $actTag = str_replace("%VALUE%",$obj->userValue,$actTag);
	  $elements.=$actTag;
}
$html = str_replace("%ELEMENTS%",$elements,$html);


die($html);
?>