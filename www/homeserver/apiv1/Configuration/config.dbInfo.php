<?php 
return [
	'basicconfig' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'paramKey' => [
			'dataType' => 'varchar',
			'alias' => 'name',
		],
		'paramValue' => [
			'dataType' => 'varchar',
			'alias' => 'value',
		],
	],
	'basicrulegroupsignals' => [
		'eventType' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'groupId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ruleId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'basicrules' => [
		'startMinute' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'endDay' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'endHour' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'endMinute' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'extras' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'template' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'active' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'groupLock' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'intraDay' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'startHour' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'startDay' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'groupId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'fkt1' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'fkt1Dauer' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'fkt2' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'fkt3' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'fkt4' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'fkt4Dauer' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'ledStatus' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'basicrulesignalparams' => [
		'paramValue' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'featureFunctionParamsId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ruleSignalId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'basicrulesignals' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ruleId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'configcache' => [
		'configData' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'controller' => [
		'lastChange' => [
			'dataType' => 'timestamp',
			'alias' => '',
		],
		'booterMinor' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'booterMajor' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'bootloader' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'lastContact' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'online' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'dataBlockSizeByte' => [
			'dataType' => 'mediumint',
			'alias' => '',
		],
		'startupDelay' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'firmwareId' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'objectId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'size' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'majorRelease' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'minorRelease' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'databuffer' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'data' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'featureclasses' => [
		'view' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'smoketest' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'guiControlFunctions' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'guiControl' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'classId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'featurefunctionbitmasks' => [
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'bit' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'paramId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureFunctionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'featurefunctionenums' => [
		'value' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'paramId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureFunctionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'featurefunctionparams' => [
		'view' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'comment' => [
			'dataType' => 'text',
			'alias' => 'description',
		],
		'type' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'featureFunctionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'featurefunctions' => [
		'view' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'functionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'type' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'featureClassesId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'featureinstances' => [
		'lastChange' => [
			'dataType' => 'timestamp',
			'alias' => 'lastModified',
		],
		'parentInstanceId' => [
			'dataType' => 'int',
			'alias' => 'parentId',
		],
		'checked' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'port' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'objectId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureClassesId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'controllerId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'functiontemplates' => [
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'signal' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'function' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'classesId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'googlecalendar' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'calendarId' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'graphdata' => [
		'value' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'signalId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'graphId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'graphs' => [
		'distType' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'distValue' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'heightMode' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'height' => [
			'dataType' => 'mediumint',
			'alias' => '',
		],
		'width' => [
			'dataType' => 'mediumint',
			'alias' => '',
		],
		'timeParam2' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'timeParam1' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'timeMode' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'type' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'title' => [
			'dataType' => 'varchar',
			'alias' => 'name',
		],
		'theme' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'graphsignalevents' => [
		'fkt' => [
			'dataType' => 'varchar',
			'alias' => 'graphValueFunction',
		],
		'functionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'graphSignalsId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'graphsignals' => [
		'type' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'fkt' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'functionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'color' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'graphId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'title' => [
			'dataType' => 'varchar',
			'alias' => 'name',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'groupfeatures' => [
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'groupId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'groups' => [
		'active' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'groupType' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'lastChange' => [
			'dataType' => 'timestamp',
			'alias' => '',
		],
		'subOf' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'single' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'groupstates' => [
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'basics' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'value' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'groupId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'groupsynchelper' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'controllerId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'groupIndex' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'groupId' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'guicontrolssaved' => [
		'value' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'sort' => [
			'dataType' => 'mediumint',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'i2cnetworks' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'network' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'controllerId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ethernet' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
	],
	'i2ctimings' => [
		'sda' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'scl' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'receiverId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'senderId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'networkId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'languages' => [
		'checked' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'translation' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'theKey' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'language' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'lastreceived' => [
		'senderObj' => [
			'dataType' => 'int',
			'alias' => 'senderObjectId',
		],
		'functionData' => [
			'dataType' => 'text',
			'alias' => 'data',
		],
		'function' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'type' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'int',
			'alias' => 'created',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'recovery' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'objectId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'configuration' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'lastTime' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'roomfeatures' => [
		'lastChange' => [
			'dataType' => 'timestamp',
			'alias' => 'lastModified',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => 'featureId',
		],
		'roomId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'rooms' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'picture' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'lastChange' => [
			'dataType' => 'timestamp',
			'alias' => 'lastModified',
		],
	],
	'ruleactionparams' => [
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'paramValue' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'featureFunctionParamsId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ruleActionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'ruleactions' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ruleId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureFunctionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
	],
	'rulecache' => [
		'data' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'controllerId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'groups' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'rules' => [
		'baseRule' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'syncEvent' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'ledFeedbackIndent' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'offRule' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'extras' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'groupLock' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'active' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'intraDay' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'signalType' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'endMinute' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'groupId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'activationStateId' => [
			'dataType' => 'mediumint',
			'alias' => '',
		],
		'resultingStateId' => [
			'dataType' => 'mediumint',
			'alias' => '',
		],
		'startDay' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'startHour' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'startMinute' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'endDay' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'endHour' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
	],
	'rulesignalparams' => [
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'paramValue' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'featureFunctionParamsId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ruleSignalId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'rulesignals' => [
		'completeGroupFeedback' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'generated' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'groupAlias' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureFunctionId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'ruleId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'servervariables' => [
		'name' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'value' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'instance' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
	],
	'trace' => [
		'script' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'message' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'udpcommandlog' => [
		'fktId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'senderObj' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'udpDataLogId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'receiverSubscriberData' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'senderSubscriberData' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'functionData' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'params' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'function' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'type' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'messageCounter' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'sender' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'receiver' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'udpcommandlogimport' => [
		'functionData' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'senderSubscriberData' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'receiverSubscriberData' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'udpDataLogId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'senderObj' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'fktId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'params' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'function' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'receiver' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'sender' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'messageCounter' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'type' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'udpdatalog' => [
		'data' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'udpdatalogimport' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'data' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'udphelper' => [
		'dummy' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'userdata' => [
		'userKey' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'userValue' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'webappgraphs' => [
		'graphType' => [
			'dataType' => 'smallint',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'webappgraphssignals' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'graphId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'webapppages' => [
		'filename' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'pos' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'webapppagesbuttons' => [
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'zeilenId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'pos' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'featureInstanceId' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
	],
	'webapppageszeilen' => [
		'pageId' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'pos' => [
			'dataType' => 'tinyint',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
	],
	'zz_apitest' => [
		'modifiedBy' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'lastModified' => [
			'dataType' => 'timestamp',
			'alias' => '',
		],
		'time' => [
			'dataType' => 'timestamp',
			'alias' => '',
		],
		'num' => [
			'dataType' => 'int',
			'alias' => 'number',
		],
		'description' => [
			'dataType' => 'text',
			'alias' => '',
		],
		'name' => [
			'dataType' => 'varchar',
			'alias' => '',
		],
		'id' => [
			'dataType' => 'int',
			'alias' => '',
		],
		'created' => [
			'dataType' => 'timestamp',
			'alias' => '',
		],
	],

];