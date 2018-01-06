<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($submitted==1)
{
	 if ($action=="new")
	 {
	 	 if ($neuname=="") $message="Fehler: Bitte Templatenamen angeben";
	 	 else
	 	 {
	     QUERY("DELETE from functionTemplates where classesId='$id' and name='$neuname'");
	     QUERY("INSERT into functionTemplates (classesId, function,`signal`,name) values('$id','1','$neufkt1','$neuname')");
	     QUERY("INSERT into functionTemplates (classesId, function,`signal`,name) values('$id','2','$neufkt2','$neuname')");
  	   QUERY("INSERT into functionTemplates (classesId, function,`signal`,name) values('$id','3','$neufkt3','$neuname')");
	     QUERY("INSERT into functionTemplates (classesId, function,`signal`,name) values('$id','4','$neufkt4','$neuname')");
	     QUERY("INSERT into functionTemplates (classesId, function,`signal`,name) values('$id','5','$neufkt5','$neuname')");
	 	 	 $message="Template $neuname wurde gespeichert";
	 	 }
	 }
	 else
	 {
	   QUERY("DELETE from functionTemplates where classesId='$id' and name=''");
	   QUERY("INSERT into functionTemplates (classesId, function,`signal`) values('$id','1','$fkt1')");
	   QUERY("INSERT into functionTemplates (classesId, function,`signal`) values('$id','2','$fkt2')");
  	 QUERY("INSERT into functionTemplates (classesId, function,`signal`) values('$id','3','$fkt3')");
	   QUERY("INSERT into functionTemplates (classesId, function,`signal`) values('$id','4','$fkt4')");
	   QUERY("INSERT into functionTemplates (classesId, function,`signal`) values('$id','5','$fkt5')");
	   $message="Template wurde gespeichert";
	 }
}

if ($delete!="" && $id!="")
{
	 $erg = QUERY("select name from functionTemplates where id='$delete' limit 1");
	 $row=MYSQL_FETCH_ROW($erg);
	 $deleteName=mysql_real_escape_string($row[0]);
	 if ($deleteName!="") QUERY("DELETE from functionTemplates where classesId='$id' and name='$deleteName'");
}


setupTreeAndContent("templateConfig_design.html", $message);

$erg = QUERY("select name from featureClasses where id='$id' limit 1");
if ($row=MYSQL_FETCH_ROW($erg))
{
	 $html = str_replace("%CLASS_NAME%",$row[0],$html);
}
else die("Unbekannte classId $id");

$erg = QUERY("select * from functionTemplates where classesId='$id' and name=''");
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	$fkt[$obj->function]=$obj->signal;	
}

$dimmerClassesId = getClassesIdByName("Dimmer");
$rolloClassesId = getClassesIdByName("Rollladen");
$ledClassesId = getClassesIdByName("Led");
$logicalButtonClassesId = getClassesIdByName("LogicalButton");
$schalterClassesId = getClassesIdByName("Schalter");
$tasterClassesId = getClassesIdByName("Taster");


$templatesTag = getTag("%TEMPLATES%",$html);
$templates="";

if ($id==$dimmerClassesId)
{
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AN",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt1 style='width:130px'>".getSelect($fkt[1], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AUS",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt2 style='width:130px'>".getSelect($fkt[2], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","DIMM",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt3 style='width:130px'>".getSelect($fkt[3], "-,hold", "Kein,Halten-Loslassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","PRESET",$actTag); 
  $actTag = str_replace("%TEMPLATE%","<select name=fkt4 style='width:130px'>".getSelect($fkt[4], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
}
else if ($id==$rolloClassesId)
{
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","HOCH",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt1 style='width:130px'>".getSelect($fkt[1], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","RUNTER",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt2 style='width:130px'>".getSelect($fkt[2], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","STOP'N'GO",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt3 style='width:130px'>".getSelect($fkt[3], "-,hold", "Kein,Halten-Loslassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","PRESET",$actTag); 
  $actTag = str_replace("%TEMPLATE%","<select name=fkt4 style='width:130px'>".getSelect($fkt[4], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","STOP",$actTag); 
  $actTag = str_replace("%TEMPLATE%","<select name=fkt5 style='width:130px'>".getSelect($fkt[5], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
}
else if ($id==$ledClassesId || $id==$logicalButtonClassesId)
{
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AN",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt1 style='width:130px'>".getSelect($fkt[1], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AUS",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt2 style='width:130px'>".getSelect($fkt[2], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
  
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","PRESET",$actTag); 
  $actTag = str_replace("%TEMPLATE%","<select name=fkt4 style='width:130px'>".getSelect($fkt[4], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
}
else if ($id==$schalterClassesId)
{
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AN",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt1 style='width:130px'>".getSelect($fkt[1], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AUS",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt2 style='width:130px'>".getSelect($fkt[2], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
  
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","PRESET",$actTag); 
  $actTag = str_replace("%TEMPLATE%","<select name=fkt4 style='width:130px'>".getSelect($fkt[4], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
}
else if ($id==$tasterClassesId)
{
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","Signale Aus",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt1 style='width:130px'>".getSelect($fkt[1], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","Signale An",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt2 style='width:130px'>".getSelect($fkt[2], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
}
else if ($id==-1)
{
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AN/HOCH",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt1 style='width:130px'>".getSelect($fkt[1], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","AUS/RUNTER",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt2 style='width:130px'>".getSelect($fkt[2], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","DIMM/STOP'N'GO",$actTag);
  $actTag = str_replace("%TEMPLATE%","<select name=fkt3 style='width:130px'>".getSelect($fkt[3], "-,hold", "Kein,Halten-Loslassen")."</select>",$actTag);
  $templates.=$actTag;

  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","PRESET",$actTag); 
  $actTag = str_replace("%TEMPLATE%","<select name=fkt4 style='width:130px'>".getSelect($fkt[4], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
  
  $actTag = $templatesTag;
  $actTag = str_replace("%FKT%","STOP",$actTag); 
  $actTag = str_replace("%TEMPLATE%","<select name=fkt5 style='width:130px'>".getSelect($fkt[5], "-,covered,click,doubleClick,hold,free", "Kein,Gedrückt,Klick,Doppelklick,Gehalten,Losgelassen")."</select>",$actTag);
  $templates.=$actTag;
}

$html = str_replace("%TEMPLATES%", $templates, $html);

$newTemplates = str_replace("selected","",$templates);
$newTemplates = str_replace("name=","name=neu",$newTemplates);
$html = str_replace("%NEW_TEMPLATES%", $newTemplates, $html);

$html = str_replace("%ID%", $id, $html);


$erg = QUERY("select * from functionTemplates where classesId='$id' and name!=''");
while($obj=MYSQL_FETCH_OBJECT($erg))
{
	 $save[$obj->name][$obj->function]=$obj;
}

$savedTemplatesTag = getTag("%SAVED_TEMPLATES%",$html);
$savedTemplates="";
foreach((array)$save as $name=>$arr)
{
	$actTag = $savedTemplatesTag;
	$actTag = str_replace("%SAVED_ID%",$arr[1]->id,$actTag);
	$actTag = str_replace("%NAME%",$arr[1]->name,$actTag);
	$actTag = str_replace("%SAVED_NAME%",$arr[1]->signal." ".$arr[2]->signal." ".$arr[3]->signal." ".$arr[4]->signal." ".$arr[5]->signal,$actTag);
	$savedTemplates.=$actTag;
}
	
$html = str_replace("%SAVED_TEMPLATES%", $savedTemplates, $html);

show();
?>