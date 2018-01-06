<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($info=="instanceId")
{
	 $erg = MYSQL_QUERY("select objectId from featureInstances where id='$id' limit 1") or die(MYSQL_ERROR());
	 if ($row=MYSQL_FETCH_ROW($erg))
	 {
	 	 	 die("".getInstanceId($row[0]));
	 }
}

?>