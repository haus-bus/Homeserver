<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

setupTreeAndContent("guiControl_design.html", $message);

$html = str_replace("%SCRIPT%",$script,$html);
$html = str_replace("%FEATURE_INSTANCE_ID%",$featureInstanceId,$html);
$html = str_replace("%GROUP_ID%",$groupId,$html);

die($html);
?>