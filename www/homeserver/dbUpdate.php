<?php

require_once $_SERVER["DOCUMENT_ROOT"].'/homeserver/include/all.php';

MYSQL_QUERY("ALTER TABLE `rules` ADD `offRule` TINYINT NOT NULL ");
MYSQL_QUERY("ALTER TABLE `rulesignals` ADD `generated` TINYINT NOT NULL ");
MYSQL_QUERY("ALTER TABLE `rulesignalparams` ADD `generated` TINYINT NOT NULL");
MYSQL_QUERY("ALTER TABLE `rules` ADD `generated` TINYINT NOT NULL");
MYSQL_QUERY("ALTER TABLE `ruleactions` ADD `generated` TINYINT NOT NULL ");
MYSQL_QUERY("ALTER TABLE `ruleactionparams` ADD `generated` TINYINT NOT NULL");
MYSQL_QUERY("ALTER TABLE `groupstates` ADD `generated` TINYINT NOT NULL ");
MYSQL_QUERY("ALTER TABLE `groupfeatures` ADD `generated` TINYINT NOT NULL");

MYSQL_QUERY("DELETE from ruleSignals where featureInstanceId='0'");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `functiontemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classesId` int(11) NOT NULL,
  `function` tinyint(4) NOT NULL,
  `signal` varchar(30) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `classesId` (`classesId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

MYSQL_QUERY("ALTER TABLE `groups` ADD `generated` TINYINT NOT NULL");
MYSQL_QUERY("ALTER TABLE `groupstates` ADD `basics` TINYINT NOT NULL ");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `groupsynchelper` (
  `controllerId` int(11) NOT NULL,
  `groupIndex` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  KEY `controllerId` (`controllerId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `rulecache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groups` varchar(255) NOT NULL,
  `controllerId` int(11) NOT NULL,
  `data` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `controllerId` (`controllerId`),
  KEY `groupId` (`groups`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

MYSQL_QUERY("ALTER TABLE `rules` ADD `baseRule` TINYINT NOT NULL ,ADD `syncEvent` TINYINT NOT NULL ,ADD `ledFeedbackIndent` VARCHAR( 30 ) NOT NULL ");

MYSQL_QUERY("ALTER TABLE `rulesignals` ADD `groupAlias` INT NOT NULL");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `basicconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paramKey` varchar(30) NOT NULL,
  `paramValue` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `basicrules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `fkt1` varchar(20) NOT NULL,
  `fkt2` varchar(20) NOT NULL,
  `fkt3` varchar(20) NOT NULL,
  `fkt4` varchar(20) NOT NULL,
  `ledStatus` varchar(20) NOT NULL,
  `startDay` smallint(6) NOT NULL,
  `startHour` smallint(6) unsigned NOT NULL,
  `startMinute` smallint(6) unsigned NOT NULL,
  `endDay` smallint(6) NOT NULL,
  `endHour` smallint(6) unsigned NOT NULL,
  `endMinute` smallint(6) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `groupId` (`groupId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `basicrulesignalparams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ruleSignalId` int(11) NOT NULL,
  `featureFunctionParamsId` int(11) NOT NULL,
  `paramValue` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `ruleActionId` (`ruleSignalId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `basicrulesignals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ruleId` int(11) NOT NULL,
  `featureInstanceId` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `ruleId` (`ruleId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `configcache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `featureInstanceId` int(11) NOT NULL,
  `configData` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `featureInstanceId` (`featureInstanceId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

MYSQL_QUERY("ALTER TABLE `configcache` ADD `id` INT NOT NULL AUTO_INCREMENT , ADD PRIMARY KEY ( `id` )");
MYSQL_QUERY("ALTER TABLE `rulecache` ADD `id` INT NOT NULL AUTO_INCREMENT , ADD PRIMARY KEY ( `id` )");

$changes=0;
$erg = MYSQL_QUERY("select featureInstanceId,groupId from groupFeatures");
while($row=MYSQL_FETCH_ROW($erg))
{
	$myClassesId = getClassesIdByFeatureInstanceId($row[0]);
	$groupId=$row[1];
	
	$basicStateNames = getBasicStateNames($myClassesId);
  $offName=$basicStateNames->offName;
  $onName=$basicStateNames->onName;

	$sql = "UPDATE groupStates set name='$offName',basics='1' where groupId='$groupId' and name='1' and basics='0'";
	//echo $sql."<br>";
	MYSQL_QUERY($sql);
	$changes+=mysql_affected_rows();
	$sql = "UPDATE groupStates set name='$onName',basics='2' where groupId='$groupId' and name='2' and basics='0'";
	//echo $sql."<br>";
	MYSQL_QUERY($sql);
	$changes+=mysql_affected_rows();
}

// Controllerinstanzen von bereits existierenden Controllern erstellen
$erg22 = QUERY("select distinct objectId from controller where bootloader='0'");
while($row22=MYSQL_FETCH_ROW($erg22))
{
	$sender=$row22[0];
	
  $controllerId = getControllerId($sender);
  $objectId=$sender;
  $erg = MYSQL_QUERY("select id from featureinstances where objectId='$objectId' limit 1");
  if ($row=MYSQL_FETCH_ROW($erg)) {}
  else
  {
    $featureClassesId = $CONTROLLER_CLASSES_ID;
    $featureName = $featureClasses[$featureClassesId]->name;
    MYSQL_QUERY("INSERT into featureinstances (controllerId,featureClassesId,objectId,name,checked) values ('$controllerId ','$featureClassesId','$objectId','$featureName','1')");
    echo "Neues Feature angelegt: ControllerId = $controllerId , FeatureClassId = $featureClassesId , ObjectId = $objectId , Name = $featureName \n";
    $featureInstanceId = mysql_insert_id();
    MYSQL_QUERY("INSERT into groups (single) values ('1')");
    $groupId = mysql_insert_id();
    MYSQL_QUERY("INSERT into groupFeatures (groupId, featureInstanceId) values ('$groupId','$featureInstanceId')");
    
 		$basicStateNames = getBasicStateNames($CONTROLLER_CLASSES_ID);
		$offName=$basicStateNames->offName;
		$onName=$basicStateNames->onName;

    MYSQL_QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$offName','1','1')");
    MYSQL_QUERY("INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$onName','2','2')");
  }
}

MYSQL_QUERY("ALTER TABLE `basicrules` ADD `extras` VARCHAR( 30 ) NOT NULL");
MYSQL_QUERY("ALTER TABLE `rules` ADD `extras` VARCHAR( 30 ) NOT NULL");
MYSQL_QUERY("ALTER TABLE `functiontemplates` ADD `name` VARCHAR( 150 ) NOT NULL");
MYSQL_QUERY("ALTER TABLE `basicrules` ADD `template` VARCHAR( 150 ) NOT NULL");

MYSQL_QUERY("CREATE TABLE `webappPages` (`id` INT NOT NULL AUTO_INCREMENT ,`name` VARCHAR( 255 ) NOT NULL ,`pos` TINYINT NOT NULL ,PRIMARY KEY ( `id` )) ENGINE = MYISAM ;");
MYSQL_QUERY("CREATE TABLE `webappPagesZeilen` (`id` INT NOT NULL AUTO_INCREMENT ,`name` VARCHAR( 255 ) NOT NULL ,`pos` TINYINT NOT NULL ,PRIMARY KEY ( `id` )) ENGINE = MYISAM ;");
MYSQL_QUERY("ALTER TABLE `webappPagesZeilen` ADD `pageId` INT NOT NULL ,ADD INDEX ( `pageId` );");
MYSQL_QUERY("CREATE TABLE `webappPagesButtons` (`id` INT NOT NULL AUTO_INCREMENT ,`zeilenId` INT NOT NULL ,`name` INT NOT NULL ,`featureInstanceId` INT NOT NULL ,PRIMARY KEY ( `id` ) ,INDEX ( `zeilenId` )) ENGINE = MYISAM ;");
MYSQL_QUERY("ALTER TABLE `webapppagesbuttons` ADD `pos` TINYINT NOT NULL AFTER `zeilenId`");
MYSQL_QUERY("ALTER TABLE `webapppages` ADD `filename` VARCHAR( 50 ) NOT NULL AFTER `pos`");

MYSQL_QUERY("ALTER TABLE `groupsynchelper` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST , ADD PRIMARY KEY ( `id` )");
MYSQL_QUERY("ALTER TABLE `groups` ADD `subOf` INT NOT NULL ");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `servervariables` (
  `name` smallint(6) NOT NULL,
  `value` smallint(6) NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `databuffer` (`id` int(11) NOT NULL AUTO_INCREMENT,`data` text NOT NULL, `time` varchar(20) NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM");

MYSQL_QUERY("ALTER TABLE `controller` ADD `lastChange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
addIndex("controller","lastChange");
MYSQL_QUERY("ALTER TABLE `featureInstances` ADD `lastChange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
addIndex("featureInstances","lastChange");
MYSQL_QUERY("ALTER TABLE `groups` ADD `lastChange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
addIndex("groups","lastChange");
MYSQL_QUERY("ALTER TABLE `rooms` ADD `lastChange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
addIndex("rooms","lastChange");
MYSQL_QUERY("ALTER TABLE `roomFeatures` ADD `lastChange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
addIndex("roomFeatures","lastChange");
addIndex("featureclasses","name");

MYSQL_QUERY("update mysql.user set host='%' where user='root'");

MYSQL_QUERY("ALTER TABLE `rulesignals` ADD `completeGroupFeedback` TINYINT NOT NULL");

MYSQL_QUERY("ALTER TABLE `basicrules` ADD `fkt1Dauer` VARCHAR( 20 ) NOT NULL AFTER `fkt1`");
MYSQL_QUERY("ALTER TABLE `basicrules` ADD `fkt4Dauer` VARCHAR( 20 ) NOT NULL AFTER `fkt4`");

MYSQL_QUERY("CREATE TABLE `homeserver`.`webappGraphs` (`id` INT NOT NULL AUTO_INCREMENT ,`name` VARCHAR( 150 ) NOT NULL ,`featureInstanceId` INT NOT NULL ,`graphType` SMALLINT NOT NULL ,PRIMARY KEY ( `id` )) ENGINE = MYISAM ;");
MYSQL_QUERY("CREATE TABLE `homeserver`.`webappGraphsSignals` (`id` INT NOT NULL AUTO_INCREMENT ,`graphId` INT NOT NULL ,`featureInstanceId` INT NOT NULL ,PRIMARY KEY ( `id` ) ,INDEX ( `graphId` )) ENGINE = MYISAM ;");

MYSQL_QUERY("ALTER TABLE `groups` ADD `groupType` VARCHAR( 15 ) NOT NULL");

MYSQL_QUERY("CREATE TABLE `homeserver`.`basicRuleGroupSignals` (`id` INT NOT NULL AUTO_INCREMENT ,`ruleId` INT NOT NULL ,`groupId` INT NOT NULL ,`eventType` VARCHAR( 10 ) NOT NULL ,PRIMARY KEY ( `id` ) ,INDEX ( `ruleId` )) ENGINE = MYISAM ;");

MYSQL_QUERY("ALTER TABLE `featureclasses` ADD `view` VARCHAR( 30 ) NOT NULL");
MYSQL_QUERY("update featureClasses set view='Standard' where view=''");

MYSQL_QUERY("ALTER TABLE `featurefunctions` ADD `view` VARCHAR( 30 ) NOT NULL");
MYSQL_QUERY("update featurefunctions set view='Standard' where view=''");

MYSQL_QUERY("ALTER TABLE `featurefunctionparams` ADD `view` VARCHAR( 30 ) NOT NULL");
MYSQL_QUERY("update featurefunctionparams set view='Standard' where view=''");

//MYSQL_QUERY("ALTER TABLE `rules` ADD `groupLock` TINYINT NOT NULL");
//MYSQL_QUERY("ALTER TABLE `basicrules` ADD `groupLock` TINYINT NOT NULL");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `graphs` ( `id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(150) NOT NULL, `timeMode` varchar(50) NOT NULL, `timeParam1` int(11) NOT NULL, `timeParam2` int(11) NOT NULL, `width` mediumint(9) NOT NULL, `height` mediumint(9) NOT NULL, `distValue` int(11) NOT NULL, `distType` varchar(15) NOT NULL, PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `graphsignalevents` ( `id` int(11) NOT NULL AUTO_INCREMENT, `graphSignalsId` int(11) NOT NULL, `featureInstanceId` int(11) NOT NULL, `functionId` int(11) NOT NULL, `fkt` varchar(50) NOT NULL, PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `graphsignals` ( `id` int(11) NOT NULL AUTO_INCREMENT, `type` varchar(20) NOT NULL, `title` varchar(150) NOT NULL, `graphId` int(11) NOT NULL, `color` varchar(30) NOT NULL, PRIMARY KEY (`id`), KEY `graphId` (`graphId`) ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

MYSQL_QUERY("ALTER TABLE `graphsignals` ADD `type` VARCHAR( 20 ) NOT NULL");
MYSQL_QUERY("ALTER TABLE `graphsignals` ADD `featureInstanceId` int(11) NOT NULL");
MYSQL_QUERY("ALTER TABLE `graphsignals` ADD `functionId` int(11) NOT NULL");

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'timeZone' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('timeZone','Europe/Berlin')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'proxy' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('proxy','')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'proxyPort' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('proxyPort','')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'networkIp' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('networkIp','255.255.255.255')")  or die(MYSQL_ERROR());

MYSQL_QUERY("ALTER TABLE `servervariables` ADD `instance` TINYINT NOT NULL");
MYSQL_QUERY("ALTER TABLE `homeserver`.`servervariables` DROP INDEX `name` , ADD UNIQUE `name` ( `name` , `instance` )");

MYSQL_QUERY("ALTER TABLE `rules` ADD `groupLock` TINYINT NOT NULL");
MYSQL_QUERY("ALTER TABLE `servervariables` CHANGE `instance` `instance` SMALLINT NOT NULL");

$erg = MYSQL_QUERY("select id from featureclasses where name='PC-Server' limit 1");
if ($row=MYSQL_FETCH_ROW($erg))
{
	$found=0;
  $erg = MYSQL_QUERY("select * from featurefunctions where featureClassesId='$row[0]' and name='evOnline' limit 1");
  if ($obj=MYSQL_FETCH_OBJECT($erg))
  {
  	if ($obj->type!="EVENT" || $obj->functionId!="200") MYSQL_QUERY("DELETE FROM featurefunctions where id='$obj->id' limit 1");
  	else $found=1;
  }
  if ($found!=1) MYSQL_QUERY("INSERT into featurefunctions (featureClassesId,type,name,functionId,view) values('$row[0]','EVENT','evOnline','200','Standard')");

  MYSQL_QUERY("DELETE from featurefunctions where featureClassesId='$row[0]' and name='evOffline' and functionId='2001' limit 1");

  $erg = MYSQL_QUERY("select id from featurefunctions where featureClassesId='$row[0]' and name='evOffline' limit 1");
  if ($roww=MYSQL_FETCH_ROW($erg)){}
  else MYSQL_QUERY("INSERT into featurefunctions (featureClassesId,type,name,functionId,view) values('$row[0]','EVENT','evOffline','201','Standard')");

  $erg = MYSQL_QUERY("select id from featurefunctions where featureClassesId='$row[0]' and name='standby' limit 1");
  if ($roww=MYSQL_FETCH_ROW($erg)){}
  else MYSQL_QUERY("INSERT into featurefunctions (featureClassesId,type,name,functionId,view) values('$row[0]','ACTION','standby','10','Standard')");

  $erg = MYSQL_QUERY("select id from featurefunctions where featureClassesId='$row[0]' and name='shutdown' limit 1");
  if ($roww=MYSQL_FETCH_ROW($erg)){}
  else MYSQL_QUERY("INSERT into featurefunctions (featureClassesId,type,name,functionId,view) values('$row[0]','ACTION','shutdown','11','Standard')");

  $erg = MYSQL_QUERY("select id from featurefunctions where featureClassesId='$row[0]' and name='restart' limit 1");
  if ($roww=MYSQL_FETCH_ROW($erg)){}
  else MYSQL_QUERY("INSERT into featurefunctions (featureClassesId,type,name,functionId,view) values('$row[0]','ACTION','restart','12','Standard')");

  $erg = MYSQL_QUERY("select id from featurefunctions where featureClassesId='$row[0]' and name='reloadUserPlugin' limit 1");
  if ($roww=MYSQL_FETCH_ROW($erg)){}
  else MYSQL_QUERY("INSERT into featurefunctions (featureClassesId,type,name,functionId,view) values('$row[0]','ACTION','reloadUserPlugin','13','Standard')");
}



MYSQL_QUERY("ALTER TABLE `basicrules` ADD `active` TINYINT NOT NULL DEFAULT '1'");
MYSQL_QUERY("ALTER TABLE `rules` ADD `active` TINYINT NOT NULL DEFAULT '1'");

addIndex("udpcommandlog","fktId");
addIndex("controller","objectId");

MYSQL_QUERY("ALTER TABLE udpCommandLog DROP INDEX senderObj");

exec("chmod 777 ".$_SERVER["DOCUMENT_ROOT"]."/homeserver/timeSyncer.sh");
exec("chmod 777 ".$_SERVER["DOCUMENT_ROOT"]."/homeserver/homeserverBackup.sh");
exec("chmod 777 ".$_SERVER["DOCUMENT_ROOT"]."/homeserver/homeserverRestore.sh");

repairDoubleIndexes();

MYSQL_QUERY("ALTER TABLE `graphs` ADD `theme` VARCHAR( 50 ) NOT NULL AFTER `id`");
MYSQL_QUERY("ALTER TABLE `controller` ADD `size` int(11) NOT NULL");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `graphdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `graphId` int(11) NOT NULL,
  `signalId` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `graphId` (`graphId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

MYSQL_QUERY("ALTER TABLE `graphSignals` ADD `fkt` VARCHAR( 150 ) NOT NULL");

MYSQL_QUERY("ALTER TABLE `graphs` ADD `heightMode` VARCHAR( 30 ) NOT NULL AFTER `height`"); 

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `googlecalendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `calendarId` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"); 


MYSQL_QUERY("ALTER TABLE `groups` ADD `active` TINYINT NOT NULL DEFAULT '1'"); 

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `i2cnetworks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `network` int(11) NOT NULL,
  `controllerId` int(11) NOT NULL,
  `ethernet` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `network` (`network`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `i2ctimings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `networkId` int(11) NOT NULL,
  `senderId` int(11) NOT NULL,
  `receiverId` int(11) NOT NULL,
  `scl` int(11) NOT NULL,
  `sda` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `networkId` (`networkId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

MYSQL_QUERY("CREATE TABLE IF NOT EXISTS `userdata` (
  `userKey` varchar(50) NOT NULL,
  `userValue` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

addPrimaryKey("userdata","userKey");

addIndex(" udpcommandlog","senderObj");

repairWetter();

MYSQL_QUERY("ALTER TABLE `basicrules` ADD `intraDay` TINYINT NOT NULL ;");
MYSQL_QUERY("ALTER TABLE `rules` ADD `intraDay` TINYINT NOT NULL ;");

// ab jan 2017
$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webDimmer1' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webDimmer1','20')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webDimmer2' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webDimmer2','40')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webDimmer3' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webDimmer3','60')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webDimmer4' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webDimmer4','80')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webRollo1' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webRollo1','20')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webRollo2' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webRollo2','40')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webRollo3' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webRollo3','60')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webRollo4' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webRollo4','80')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webRoomTemp' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webRoomTemp','1')")  or die(MYSQL_ERROR());

$erg = MYSQL_QUERY("select paramValue from basicConfig where paramKey = 'webRoomHumidity' limit 1") or die(MYSQL_ERROR());
if($row = MYSQL_FETCH_ROW($erg)) {}
else MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('webRoomHumidity','1')")  or die(MYSQL_ERROR());

echo "OK";

function addIndex($table,$column)
{
	$erg = MYSQL_QUERY("SHOW INDEX FROM $table WHERE Column_name = '$column'");
  if ($row=MYSQL_FETCH_ROW($erg)) {}
  else
  {
  	//echo "Ergänze Index $column in $table <br>";
  	MYSQL_QUERY("ALTER TABLE $table ADD INDEX ( $column)");
  }
}

function addPrimaryKey($table,$column)
{
	$erg = MYSQL_QUERY("SHOW INDEX FROM $table WHERE Column_name = '$column'");
  if ($row=MYSQL_FETCH_ROW($erg)) {}
  else
  {
  	//echo "Ergänze Index $column in $table <br>";
  	MYSQL_QUERY("ALTER TABLE $table ADD PRIMARY KEY ( $column)");
  }
}

function repairDoubleIndexes()
{
	$erg = MYSQL_QUERY("SHOW TABLES") or die(MYSQL_ERROR());
	while($row=MYSQL_FETCH_ROW($erg))
	{
		$table=$row[0];

    unset($check);
	  $erg2 = MYSQL_QUERY("SHOW INDEX FROM $table") or die(MYSQL_ERROR());
	  while($obj=MYSQL_FETCH_OBJECT($erg2))
	  {
	  	  if ($check[$obj->Column_name]==1)
	  	  {
	  	  	//echo "Lösche Index: ".$table.": ".$obj->Key_name." - ".$obj->Column_name."<br>";
	  	  	MYSQL_QUERY("ALTER TABLE $table DROP INDEX ".$obj->Key_name);
	  	  }
	  	  $check[$obj->Column_name]=1;
	  }
	}
}

function repairWetter()
{
	$erg = MYSQL_QUERY("select paramValue,paramKey from basicConfig where paramKey = 'locationZipCode' or paramKey='locationCountry' or paramKey='latitude' or paramKey='longitude'") or die(MYSQL_ERROR());
  while($row=MYSQL_FETCH_ROW($erg))
  {
  	 $vals[$row[1]]=$row[0];
  }
  
  if ($vals['latitude']=="" && $vals['locationCountry']!="" && $vals['locationZipCode']!="")
  {
  	$req = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20geo.places%20where%20text%3D%22" . $vals['locationCountry'] . "%20" . $vals['locationZipCode'] . "%22&format=xml";
    $api = simplexml_load_string ( utf8_encode ( file_get_contents ( $req, false, getStreamContext () ) ) );
    $latitude=$api->results->place->centroid->latitude;
    $longitude=$api->results->place->centroid->longitude;
    
    if ($latitude>0)
    {
      MYSQL_QUERY("DELETE from basicConfig where paramKey = 'latitude' or paramKey='longitude' limit 2") or die(MYSQL_ERROR());
      MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('latitude','$latitude')") or die(MYSQL_ERROR());
      MYSQL_QUERY("INSERT into basicConfig (paramKey,paramValue) values('longitude','$longitude')") or die(MYSQL_ERROR());
    }
  }
}

?>