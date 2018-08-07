<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($info=="instanceId")
{
	 $erg = QUERY("select objectId from featureInstances where id='$id' limit 1");
	 if ($row=mysqli_fetch_ROW($erg))
	 {
	 	 	 die("".getInstanceId($row[0]));
	 }
}

?>