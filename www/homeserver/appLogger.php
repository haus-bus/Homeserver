<?php

file_put_contents("appLogger.log",date("d.m.y H:i:s").": ".$_GET["log"]." - ".$_SERVER['REMOTE_ADDR']." \n", FILE_APPEND);
die("OK");
?>