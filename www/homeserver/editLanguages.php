<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

// Methodenaufrufe

if ($action=="chooseLanguage")
{
  // Alle Tabellen auf zu übersetzende Namen überprüfen
  if ($update==1)
  {
    QUERY("update languages set checked='0'");

    $erg = QUERY("select name from featureFunctions");
    while($obj=mysqli_fetch_OBJECT($erg))
    {
      $erg2 = QUERY("select id from languages where language='$language' and theKey='$obj->name' limit 1");
      if ($row=mysqli_fetch_ROW($erg2)) QUERY("update languages set checked='1' where language='$language' and theKey='$obj->name' limit 1");
      else QUERY("INSERT into languages (language,theKey,translation,checked) values('$language','$obj->name','$obj->name','1')");
    }
     
    $erg = QUERY("select name from featureFunctionParams");
    while($obj=mysqli_fetch_OBJECT($erg))
    {
      $erg2 = QUERY("select id from languages where language='$language' and theKey='$obj->name' limit 1");
      if ($row=mysqli_fetch_ROW($erg2)) QUERY("update languages set checked='1' where language='$language' and theKey='$obj->name' limit 1");
      else QUERY("INSERT into languages (language,theKey,translation,checked) values('$language','$obj->name','$obj->name','1')");
    }

    $erg = QUERY("select name from featureFunctionEnums");
    while($obj=mysqli_fetch_OBJECT($erg))
    {
      $erg2 = QUERY("select id from languages where language='$language' and theKey='$obj->name' limit 1");
      if ($row=mysqli_fetch_ROW($erg2)) QUERY("update languages set checked='1' where language='$language' and theKey='$obj->name' limit 1");
      else QUERY("INSERT into languages (language,theKey,translation,checked) values('$language','$obj->name','$obj->name','1')");
    }
    
    QUERY("delete from languages where language='$language' and checked='0'");
  }
}

if ($submitted!="")
{
  $ids = explode(",",$ids);
  foreach ((array)$ids as $id)
  {
    $translation = "key".$id;
    $translation=$$translation;
    QUERY("UPDATE languages set translation='$translation' where id='$id' limit 1");
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
$erg = QUERY("select id,theKey, translation from languages where language='$language' order by theKey");
while($obj = mysqli_fetch_OBJECT($erg))
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