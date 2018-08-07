<?php

exec("/etc/init.d/mysql stop");
exec("rm /var/lib/mysql/homeserver/treeupdatehelper.*");
exec("rm /var/lib/mysql/homeserver/i2cnetworks.*");
exec("rm /var/lib/mysql/homeserver/i2ctimings.*");
exec("rm /var/lib/mysql/homeserver/plugins.*");
exec("rm /var/lib/mysql/homeserver/userdata.*");
exec("/etc/init.d/mysql start");

include("/var/www/homeserver/include/dbconnect.php");

QUERY("CREATE TABLE `treeupdatehelper` ( `forceUpdate` tinyint(4) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
QUERY("CREATE TABLE IF NOT EXISTS `i2cnetworks` (`id` int(11) NOT NULL,  `network` int(11) NOT NULL,  `controllerId` int(11) NOT NULL,  `ethernet` tinyint(4) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
QUERY("CREATE TABLE IF NOT EXISTS `i2ctimings` (`id` int(11) NOT NULL,  `networkId` int(11) NOT NULL,  `senderId` int(11) NOT NULL,  `receiverId` int(11) NOT NULL,  `scl` int(11) NOT NULL,  `sda` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
QUERY("CREATE TABLE IF NOT EXISTS `plugins` (`id` tinyint(4) NOT NULL,  `title` varchar(60) NOT NULL,  `url` varchar(250) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
QUERY("CREATE TABLE IF NOT EXISTS `userdata` (  `userKey` varchar(50) NOT NULL,  `userValue` varchar(255) NOT NULL) ENGINE=MYISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;");
QUERY("ALTER TABLE `userdata` ADD PRIMARY KEY (`userKey`)");

QUERY("INSERT INTO `plugins` (`title`, `url`) VALUES ('Datenanzeige UserPlugin', '/homeserver/editUserPluginData.php'), ('Raspberry Webstream Player', '/homeserver/plugins/radio/index.php')");
?>
