<?php
$versionFile="homeserver/version.chk";
$in = file_get_contents($versionFile);
$pos = strpos($in,"-");
$version = trim(substr($in,0,$pos));
echo "Aktuelle Version $version \n";
$pos = strpos($version,".");
$front = trim(substr($version,0,$pos));
$back = trim(substr($version,$pos+1, strlen($version)-$pos-1));
echo "Major: $front \n";
echo "Minor: $back \n";
echo "\n";
$newVersion = $front.".".($back+1)." - ".date("d.m.Y");
echo "Neu: $newVersion \n";

file_put_contents($versionFile, $newVersion);

$zip = new Zipper;
if ($zip->open("apl.zip", ZIPARCHIVE::OVERWRITE) === TRUE) 
{
  $zip->addDir("homeserver");
  $zip->addFile("haus-bus-de homeserver-325876d8d669.p12");	
  $zip->close();
  exit;
} 
die("FEHLER!");

class Zipper extends ZipArchive 
{ 
  public function addDir($path) 
  { 
    $this->addEmptyDir($path); 
    $nodes = glob($path . '/*'); 
    foreach ($nodes as $node) 
    { 
       if (is_dir($node)) 
       { 
       	  if (strpos($node,"/user/")===FALSE && strpos($node,"/libs/")===FALSE) $this->addDir($node); 
        } else if (is_file($node))  
        { 
       	  if (strpos($node,".cmd")===FALSE && strpos($node,".bin")===FALSE && strpos($node,"/user/")===FALSE && strpos($node,".exe")===FALSE && strpos($node,"restore.tar")===FALSE && strpos($node,".webapp")===FALSE && strpos($node,"import.svg")===FALSE && strpos($node,"Thumbs.db")===FALSE)
              $this->addFile($node); 
        } 
    } 
  } 
}

?>