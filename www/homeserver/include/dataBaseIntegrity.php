<?php

/**
* Falls sich jemand fragt, was das hier soll:
* Da wir MyIsam Tabellen verwenden und keine InnoDb, können keine Foreign-Index verknüpft werden.
* Das automatische Löschen aller Referenzen in fremden Tabellen wird also nicht möglich.
* Wir verwenden MyIsam anstatt InnoDb, weil diese automatisch beim Start der Datenbank repariert werden, falls es einen Stromausfall gab
* Das ist keine Kenntnis nach mit InnoDb nicht möglich. Falls da jemand andere Informationen hat, bitte an Herm melden
*/
function checkReferenceIntegrity($dry)
{
	//print_r(debug_backtrace());
	global $foundErrors;
	global $wasDry;
	$start=time();
	if ($dry==1) echo "Prüfe referenzielle Integrität der Datenbank.... <br>";

  checkSingleGroups($dry);
	
  checkReference($dry, "basicrulegroupsignals", "ruleid", "basicrules", "id");
  checkReference($dry, "basicrulegroupsignals", "groupid", "groups", "id");

  checkReference($dry, "basicrules", "groupid", "groups", "id");
  
  checkReference($dry, "basicrulesignalparams", "rulesignalid", "basicrulesignals", "id");
  checkReference($dry, "basicrulesignalparams", "featureFunctionParamsId", "featureFunctionParams", "id");
  
  checkReference($dry, "basicrulesignals", "ruleid", "basicrules", "id");
  checkReference($dry, "basicrulesignals", "featureInstanceId", "featureInstances", "id");
  
  checkReference($dry, "configcache", "featureInstanceId", "featureInstances", "id");
  
  checkReference($dry, "featurefunctionbitmasks", "featureFunctionId", "featureFunctions", "id");
  checkReference($dry, "featurefunctionbitmasks", "paramId", "featureFunctionParams", "id");
  
  checkReference($dry, "featurefunctionenums", "featureFunctionId", "featureFunctions", "id");
  checkReference($dry, "featurefunctionenums", "paramId", "featureFunctionParams", "id");
  
  checkReference($dry, "featurefunctionparams", "featureFunctionId", "featureFunctions", "id");
  
  checkReference($dry, "featurefunctions", "featureClassesId", "featureClasses", "id");
  
  checkReference($dry, "featureinstances", "controllerId", "controller", "id");
  checkReference($dry, "featureinstances", "featureClassesId", "featureClasses", "id");
  
  checkReference($dry, "functiontemplates", "classesId", "featureClasses", "id");
  
  checkReference($dry, "graphsignalevents", "featureInstanceId", "featureInstances", "id");
  checkReference($dry, "graphsignalevents", "functionId", "featureFunctions", "id");
  
  checkReference($dry, "graphsignals", "featureInstanceId", "featureInstances", "id");
  checkReference($dry, "graphsignals", "functionId", "featureFunctions", "id");
  
  checkReference($dry, "groupfeatures", "featureInstanceId", "featureInstances", "id");
  checkReference($dry, "groupfeatures", "groupid", "groups", "id");
  
  checkReference($dry, "groupstates", "groupid", "groups", "id");
  
  checkReference($dry, "guicontrolssaved", "featureInstanceId", "featureInstances", "id");
  
  checkReference($dry, "lastreceived", "senderObj", "featureInstances", "objectid");
  
  checkReference($dry, "recovery", "objectId", "featureInstances", "objectid");
  
  checkReference($dry, "roomFeatures", "roomId", "rooms", "id");
  checkReference($dry, "roomFeatures", "featureInstanceId", "featureInstances", "id");
  
  
  checkReference($dry, "ruleactionparams", "ruleActionId", "ruleActions", "id");
  checkReference($dry, "ruleactionparams", "featureFunctionParamsId", "featureFunctionParams", "id");
  
  checkReference($dry, "ruleactions", "ruleId", "rules", "id");
  checkReference($dry, "ruleactions", "featureInstanceId", "featureInstances", "id");
  checkReference($dry, "ruleactions", "featureFunctionId", "featureFunctions", "id");
  
  checkReference($dry, "rulecache", "controllerid", "controller", "id");
  
  checkReference($dry, "rules", "groupid", "groups", "id");
  
  checkReference($dry, "rulesignalparams", "ruleSignalId", "ruleSignals", "id");
  checkReference($dry, "rulesignalparams", "featureFunctionParamsId", "featureFunctionParams", "id");
  
  // Sonderlocken
  // Wenn featureInstance in basicRuleSignals oder ruleSignals <0 dann ist damit die id in basicrulegroupsignals gemeint
  $erg = MYSQL_QUERY("select * from basicRuleSignals where featureInstanceId<0") or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
  	 $obj->featureInstanceId=abs($obj->featureInstanceId);
  	 $sql = "select id from basicrulegroupsignals where id='$obj->featureInstanceId' limit 1";
  	 $erg2 = MYSQL_QUERY($sql) or die(MYSQL_ERROR());
  	 if ($obj2=MYSQL_FETCH_OBJECT($erg2)){}
  	 else MYSQL_QUERY("delete from basicRuleSignals where id='$obj->id' limit 1") or die(MYSQL_ERROR());
  }

  $erg = MYSQL_QUERY("select * from ruleSignals where featureInstanceId<0") or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
  	 $obj->featureInstanceId=abs($obj->featureInstanceId);
  	 $sql = "select id from basicrulegroupsignals where id='$obj->featureInstanceId' limit 1";
  	 $erg2 = MYSQL_QUERY($sql) or die(MYSQL_ERROR());
  	 if ($obj2=MYSQL_FETCH_OBJECT($erg2)){}
  	 else MYSQL_QUERY("delete from ruleSignals where id='$obj->id' limit 1") or die(MYSQL_ERROR()); 
  }
  
  // Controllerleichen ohne Features löschen
  $erg = MYSQL_QUERY("SELECT controller.id from controller left join featureInstances on (featureInstances.controllerId = controller.id) where bootloader!=1 and featureInstances.id is null") or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
  	 MYSQL_QUERY("delete from controller where id='$obj->id' limit 1") or die(MYSQL_ERROR()); 
  }
  
  if ($dry==1)
  {
  	 if ($foundErrors==1) die("Bitte angzeigte Fehler sorgfältig prüfen und zum Reparieren <a href='cleanUpDb.php?dry=0&wasDry=1'>HIER KLICKEN</A>");
  	 else echo "Keine Fehler gefunden ".(time()-$start)." s<br>";
  }
  	
  if ($wasDry==1) echo "Alle Datenbanktabellen wurden repariert <br>";
}

function checkSingleGroups($dry)
{
	if ($dry==1) echo "Prüfe Instanzgruppenzuordnung ...<br>";
	$erg = MYSQL_QUERY("select 
	featureinstances.*, groups.single from featureinstances
	left join groupfeatures on (groupfeatures.featureInstanceId = featureInstances.id) 
	left join groups on (groupfeatures.groupId = groups.id)") or die(MYSQL_ERROR());
	while($obj=MYSQL_FETCH_OBJECT($erg))
	{
		 if ($obj->single==null)
		 {
		 	  $foundErrors==1;
		 	  
		 	  if ($dry==1) echo "Instanz $obj->id hat keine Gruppe <br>";

 	  	  $erg2 = MYSQL_QUERY("select name from featureClasses where id='$obj->featureClassesId' limit 1") or die(MYSQL_ERROR());
	  	  $obj2=MYSQL_FETCH_OBJECT($erg2);
        $featureName = $obj2->name." ".getInstanceId($obj->objectId);

        $sql = "INSERT into groups (single) values ('1')";
        if ($dry==1) echo $sql."<br>";
        else MYSQL_QUERY($sql) or die(MYSQL_ERROR());
        $groupId = mysql_insert_id();
        $sql = "INSERT into groupFeatures (groupId, featureInstanceId) values ('$groupId','$obj->id')";
        if ($dry==1) echo $sql."<br>";
        else MYSQL_QUERY($sql) or die(MYSQL_ERROR());
        
     		$basicStateNames = getBasicStateNames($obj->featureClassesId);
		    $offName=$basicStateNames->offName;
		    $onName=$basicStateNames->onName;

        $sql = "INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$offName','1','1')";
        if ($dry==1) echo $sql."<br>";
        else MYSQL_QUERY($sql) or die(MYSQL_ERROR());

        $sql = "INSERT into groupStates (groupId,name, value,basics) values ('$groupId','$onName','2','2')";
        if ($dry==1) echo $sql."<br>";
        else MYSQL_QUERY($sql) or die(MYSQL_ERROR());
		 }
	}
	
	// zu einer Instanz darf es nur eine SINGLE Group geben
	$lastId=-1;
  $lastGroup=-1;
  $erg = MYSQL_QUERY("select groups.id as groupId, featureInstances.id as featureInstanceId from featureInstances join groupFeatures on (groupFeatures.featureInstanceId = featureInstances.id) join groups on (groups.id = groupFeatures.groupId) where groups.single='1' order by featureInstanceId" ) or die(MYSQL_ERROR());
  while($obj=MYSQL_FETCH_OBJECT($erg))
  {
	   if ($obj->featureInstanceId==$lastId)
	   {
	   	 $sql = "DELETE from groups where id = '$obj->groupId' limit 1";
	   	 if ($dry==1) echo $sql."<br>";
	   	 else MYSQL_QUERY($sql);
	   }
	   $lastGroup = $obj->groupId;
	   $lastId = $obj->featureInstanceId;
  }
  
  // eine Singlegruppe muss auch instanzen haben
  $erg = MYSQL_QUERY("select groups.id,groupFeatures.groupId from groups left join groupFeatures on (groupFeatures.groupId=groups.id) where single=1") or die(MYSQL_ERROR());
	while($obj=MYSQL_FETCH_OBJECT($erg))
	{
		 if ($obj->groupId==null)
		 {
		 	  $sql = "DELETE from groups where id='$obj->id' limit 1";
		 	  if ($dry==1) echo $sql."<br>";
		 	  else MYSQL_QUERY($sql);

		 	  $sql = "DELETE from groupStates where groupId='$obj->id'";
		 	  if ($dry==1) echo $sql."<br>";
		 	  else MYSQL_QUERY($sql);
		 }
	}
}

function checkReference($dry, $srcTable, $srcColumn, $destTable, $destColumn)
{
	global $foundErrors;
	
	$sql = "select 
	$srcTable.id as srcId, $srcTable.$srcColumn as src$srcColumn, 
	$destTable.$destColumn as dest$destColumn
	from $srcTable left join $destTable on ($destTable.$destColumn=$srcTable.$srcColumn)";

  //echo $sql."<br>";	
  
  $ok=0;
  $errors=0;
  $output="";
	$erg = MYSQL_QUERY($sql) or die(MYSQL_ERROR());
	while($obj=MYSQL_FETCH_OBJECT($erg))
  {
  	$srcSearch="src$srcColumn";
  	$destSearch="dest$destColumn";
  	
	  if ($obj->$destSearch==null && $obj->$srcSearch>0)
	  {
	  	$errors++;
	  	$foundErrors=1;
	  	
	  	//print_r($obj);
	  	
	  	$output.="<li> Id ".$obj->srcId.": $srcColumn ".$obj->$srcSearch." nicht vorhanden - ";
	  	$sqlDelete="DELETE from $srcTable where id='$obj->srcId' limit 1";
	 	  if ($dry==1) $output.=$sqlDelete;
	 	  else MYSQL_QUERY($sqlDelete) or die(MYSQL_ERROR());
	 	  $output.="<br>";
	  }
	  else $ok++;
  }
  if ($errors>0 && $dry==1)
  {
  	echo "<hr>$errors Fehler in Tabelle $srcTable gefunden <br>";
  	echo "$ok Einträge sind ok <br>";
  	echo "Fehler: <br> ";
  	echo $output;
  }
}
?>