<?php
include ($_SERVER["DOCUMENT_ROOT"] . "/homeserver/include/all.php");

if ($action == "new")
{
	$title = query_real_escape_string($title);
	$url = query_real_escape_string($url);
	QUERY("INSERT into plugins (title, url) values('$title','$url')");
	forceTreeUpdate();
	$id = query_insert_id();
	header("Location: editPlugin.php?id=$id");
	exit;
}

if ($action == "delete")
{
	 if ($confirm==1)
	 {
	 	 QUERY("delete from plugins where id='$id' limit 1");
	 	 forceTreeUpdate();
	 }
	 else showMessage("Soll dieses Plugin wirklich vom Baum entfernt werden?","Plugineintrag löschen?","editPlugin.php?action=delete&id=$id&confirm=1","Ja, löschen","editPlugin.php","Nein, zurück");
}

setupTreeAndContent("editPlugin_design.html");

$pluginTag = getTag("%PLUGIN%", $html);
$plugins = "";

$erg = QUERY("select id, title,url from plugins order by title");
while($obj=MYSQLi_FETCH_OBJECT($erg))
{
	  $actTag = $pluginTag;
	  $actTag = str_replace("%ID%",$obj->id,$actTag);
	  $actTag = str_replace("%TITLE%",$obj->title,$actTag);
	  $actTag = str_replace("%URL%",$obj->url,$actTag);
	  
	  $plugins.=$actTag;
}
$html = str_replace("%PLUGIN%",$plugins,$html);


show(); 
?>
