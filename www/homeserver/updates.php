<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

if ($action=="readOnlineVersion")
{
  $cxContext = getStreamContext();

  $in = @file_get_contents("http://www.haus-bus.de/version2018.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlineversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionWebapplication"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateWebapplication"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }

  $in = @file_get_contents("http://www.haus-bus.de/AR8.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlinerversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionMainController"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateMainController"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }

  $in = @file_get_contents("http://www.haus-bus.de/MS6.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlinerversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionMultiTaster"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateMultiTaster"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }

  $in = @file_get_contents("http://www.haus-bus.de/SD6.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlinerversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionMultiTasterSD6"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateMultiTasterSD6"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }
  
  $in = @file_get_contents("http://www.haus-bus.de/AR8_BOOTER.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlinerversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionMainControllerBooter"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateMainControllerBooter"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }

  $in = @file_get_contents("http://www.haus-bus.de/MS6_BOOTER.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlinerversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionMultiTasterBooter"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateMultiTasterBooter"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }
  
  $in = @file_get_contents("http://www.haus-bus.de/SD6_BOOTER.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlinerversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionMultiTasterBooterSD6"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateMultiTasterBooterSD6"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }
  
  $in = @file_get_contents("http://www.haus-bus.de/sonoff.chk", False, $cxContext);
  $pos = strpos($in,"-");
  if ($pos===FALSE) $message="Onlinerversionen konnten nicht gelesen werden - Keine Internetverbindung?";
  else
  {
    $_SESSION["onlineVersionSonoff"]=trim(substr($in,0,$pos));
    $_SESSION["onlineVersionDateSonoff"]=trim(substr($in,$pos+1,strlen($in)-$pos-1));
  }
  
  if ($forward!="")
  {
  	 header("Location: $forward"."?forward=1");
  	 exit;
  }
}

setupTreeAndContent("updates_design.html", $message);

$versionWebApplication=trim(file_get_contents("version2018.chk"));
$html = str_replace("%VERSION_WEB_APPLICATION%","V".$versionWebApplication, $html);

$versionMainController=trim(@file_get_contents("../firmware/AR8.chk"));
$html = str_replace("%VERSION_MAIN_CONTROLLER%","V".$versionMainController, $html);

$versionMultiTaster=trim(@file_get_contents("../firmware/MS6.chk"));
$html = str_replace("%VERSION_MULTI_TASTER%","V".$versionMultiTaster, $html);

$versionMainController=trim(@file_get_contents("../firmware/AR8_BOOTER.chk"));
$html = str_replace("%VERSION_MAIN_CONTROLLER_BOOTER%","V".$versionMainController, $html);

$versionMultiTaster=trim(@file_get_contents("../firmware/MS6_BOOTER.chk"));
$html = str_replace("%VERSION_MULTI_TASTER_BOOTER%","V".$versionMultiTaster, $html);

$versionMultiTaster=trim(@file_get_contents("../firmware/SD6.chk"));
$html = str_replace("%VERSION_MULTI_TASTER_SD6%","V".$versionMultiTaster, $html);

$versionMultiTaster=trim(@file_get_contents("../firmware/SD6_BOOTER.chk"));
$html = str_replace("%VERSION_MULTI_TASTER_SD6_BOOTER%","V".$versionMultiTaster, $html);

$versionSonoff=trim(@file_get_contents("../firmware/sonoff.chk"));
$html = str_replace("%VERSION_SONOFF%","V".$versionSonoff, $html);

chooseTag("%OPT_READ%", $html);

$html = str_replace("%VERSION_WEB_APPLICATION_ONLINE%","V".$_SESSION["onlineVersionWebapplication"]." - ".$_SESSION["onlineVersionDateWebapplication"], $html);
chooseTag("%OPT_UPDATE%", $html);

$html = str_replace("%VERSION_MAIN_CONTROLLER_ONLINE%","V".$_SESSION["onlineVersionMainController"]." - ".$_SESSION["onlineVersionDateMainController"], $html);
chooseTag("%OPT_UPDATE_MAINCONTROLLER%", $html);

$html = str_replace("%VERSION_MULTI_TASTER_ONLINE%","V".$_SESSION["onlineVersionMultiTaster"]." - ".$_SESSION["onlineVersionDateMultiTaster"], $html);
chooseTag("%OPT_UPDATE_MULTI_TASTER%", $html);

$html = str_replace("%VERSION_MULTI_TASTER_SD6_ONLINE%","V".$_SESSION["onlineVersionMultiTasterSD6"]." - ".$_SESSION["onlineVersionDateMultiTasterSD6"], $html);
chooseTag("%OPT_UPDATE_MULTI_TASTER_SD6%", $html);

$html = str_replace("%VERSION_MAIN_CONTROLLER_BOOTER_ONLINE%","V".$_SESSION["onlineVersionMainControllerBooter"]." - ".$_SESSION["onlineVersionDateMainControllerBooter"], $html);
chooseTag("%OPT_UPDATE_MAINCONTROLLER_BOOTER%", $html);

$html = str_replace("%VERSION_MULTI_TASTER_BOOTER_ONLINE%","V".$_SESSION["onlineVersionMultiTasterBooter"]." - ".$_SESSION["onlineVersionDateMultiTasterBooter"], $html);
chooseTag("%OPT_UPDATE_MULTI_TASTER_BOOTER%", $html);

$html = str_replace("%VERSION_MULTI_TASTER_SD6_BOOTER_ONLINE%","V".$_SESSION["onlineVersionMultiTasterBooterSD6"]." - ".$_SESSION["onlineVersionDateMultiTasterBooterSD6"], $html);
chooseTag("%OPT_UPDATE_MULTI_TASTER_SD6_BOOTER%", $html);

$html = str_replace("%VERSION_SONOFF_ONLINE%","V".$_SESSION["onlineVersionSonoff"]." - ".$_SESSION["onlineVersionDateSonoff"], $html);
chooseTag("%OPT_UPDATE_SONOFF%", $html);


$versionsTag = getTag("%VERSIONS%",$html);
$versions="";
$lastId="";
$erg = QUERY("select name,firmwareId,majorRelease,minorRelease,booterMajor,booterMinor,online from controller where not (bootloader=1 and online!=1) order by firmwareId");
while($obj=mysqli_fetch_OBJECT($erg))
{
	 $actTag = $versionsTag;
	 
	 if ($obj->firmwareId!=$lastId && $lastId!="")
	 {
	 	  $versions.="<tr><td colspan=5><hr></td></tr>";
	 }
	 $lastId=$obj->firmwareId;
	 
	 if ($obj->firmwareId==1) $typ="Maincontroller";
	 else if ($obj->firmwareId==2) $typ="Multitaster MS6";
	 else if ($obj->firmwareId==3) $typ="Multitaster SD6";
	 else if ($obj->firmwareId==4) $typ="IO128";
	 else if ($obj->firmwareId==5) $typ="Sonoff WLAN Relais";
	 else if ($obj->firmwareId==6) $typ="ESP TCP Brücke";
	 else $typ="Unbekannt";
	 $actTag = str_replace("%TYP%",$typ,$actTag);
	 if ($obj->online!=1) $obj->name="[offline] ".$obj->name;
	 $actTag = str_replace("%NAME%",$obj->name,$actTag);
	 $actTag = str_replace("%VERSION%",$obj->majorRelease.".".$obj->minorRelease,$actTag);
	 $actTag = str_replace("%BOOTER_VERSION%",$obj->booterMajor.".".$obj->booterMinor,$actTag);
	 
	 $versions.=$actTag;
}
$html = str_replace("%VERSIONS%",$versions,$html);

show();


?>
