<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

// Methodenaufrufe

if ($action=="chooseLanguage")
{
  // Alle Tabellen auf zu übersetzende Namen überprüfen
  if ($update==1)
  {
    MYSQL_QUERY("update languages set checked='0'") or die(MYSQL_ERROR());

    $erg = MYSQL_QUERY("select name from featureFunctions") or die(MYSQL_ERROR());
    while($obj=MYSQL_FETCH_OBJECT($erg))
    {
      $erg2 = MYSQL_QUERY("select id from languages where language='$language' and theKey='$obj->name' limit 1") or die(MYSQL_ERROR());
      if ($row=MYSQL_FETCH_ROW($erg2)) MYSQL_QUERY("update languages set checked='1' where language='$language' and theKey='$obj->name' limit 1") or die(MYSQL_ERROR());
      else MYSQL_QUERY("INSERT into languages (language,theKey,translation,checked) values('$language','$obj->name','$obj->name','1')") or die(MYSQL_ERROR());
    }
     
    $erg = MYSQL_QUERY("select name from featureFunctionParams") or die(MYSQL_ERROR());
    while($obj=MYSQL_FETCH_OBJECT($erg))
    {
      $erg2 = MYSQL_QUERY("select id from languages where language='$language' and theKey='$obj->name' limit 1") or die(MYSQL_ERROR());
      if ($row=MYSQL_FETCH_ROW($erg2)) MYSQL_QUERY("update languages set checked='1' where language='$language' and theKey='$obj->name' limit 1") or die(MYSQL_ERROR());
      else MYSQL_QUERY("INSERT into languages (language,theKey,translation,checked) values('$language','$obj->name','$obj->name','1')") or die(MYSQL_ERROR());
    }

    $erg = MYSQL_QUERY("select name from featureFunctionEnums") or die(MYSQL_ERROR());
    while($obj=MYSQL_FETCH_OBJECT($erg))
    {
      $erg2 = MYSQL_QUERY("select id from languages where language='$language' and theKey='$obj->name' limit 1") or die(MYSQL_ERROR());
      if ($row=MYSQL_FETCH_ROW($erg2)) MYSQL_QUERY("update languages set checked='1' where language='$language' and theKey='$obj->name' limit 1") or die(MYSQL_ERROR());
      else MYSQL_QUERY("INSERT into languages (language,theKey,translation,checked) values('$language','$obj->name','$obj->name','1')") or die(MYSQL_ERROR());
    }
    
    MYSQL_QUERY("delete from languages where language='$language' and checked='0'") or die(MYSQL_ERROR());
  }
}

if ($submitted!="")
{
  $ids = explode(",",$ids);
  foreach ((array)$ids as $id)
  {
    $translation = "key".$id;
    $translation=$$translation;
    MYSQL_QUERY("UPDATE languages set translation='$translation' where id='$id' limit 1") or die(MYSQL_ERROR());
  }

  switchLanguage($language);
}

setupTreeAndContent("editLanguages_design.html", $message);

if ($language=="") $language="Deutsch";

$languageOptions="";
$act="Deutsch";
if ($language==$act) $selected="selected"; else $selected="";
$languageOptions.="<option $selected>$act";
$act="Englisch";
if ($language==$act) $selected="selected"; else $selected="";
$languageOptions.="<option $selected>$act";

$html = str_replace("%LANGUAGE_OPTIONS%",$languageOptions, $html);

$entryTag = getTag("%ENTRIES%", $html);
$entries="";
$ids="";
$erg = MYSQL_QUERY("select id,theKey, translation from languages where language='$language' order by theKey") or die(MYSQL_ERROR());
while($obj = MYSQL_FETCH_OBJECT($erg))
{
  $actTag = $entryTag;
  $actTag = str_replace("%KEY%",$obj->theKey, $actTag);
  $actTag = str_replace("%ID%",$obj->id, $actTag);
  $actTag = str_replace("%TRANSLATION%",$obj->translation, $actTag);

  if ($ids!="") $ids.=",";
  $ids.=$obj->id;
  $entries.=$actTag;
}
$html = str_replace("%ENTRIES%",$entries, $html);
$html = str_replace("%IDS%",$ids, $html);
$html = str_replace("%LANGUAGE%",$language, $html);

show();

?>