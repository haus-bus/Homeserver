<?php
include("include/all.php");

$blockSize=128;
$objectId= 1681719297;
$totalSize = 52084; 

$totalSize+=4;

$rounds=(int)($totalSize/$blockSize);
$rest = $totalSize- ($rounds*$blockSize);

$fp = fopen('testFw.bin', 'w');
for ($i=0;$i<$rounds;$i++)
{
  $address=$i*$blockSize;
  $length=$blockSize;
  $result = executeCommand($objectId, "readMemory", array("address" => $address,"length" => $length), "MemoryData");
  $data = $result["data"];
  $bytes = explode(",",$data);
  
  for ($a=0;$a<$blockSize;$a++)
  {
    fwrite($fp, chr($bytes[$a]));
  }
}

$address=$rounds*$blockSize;
$length=$rest;
$result = executeCommand($objectId, "readMemory", array("address" => $address,"length" => $length), "MemoryData");
$data = $result["data"];
$bytes = explode(",",$data);

for ($a=0;$a<$rest;$a++)
{
  fwrite($fp, chr($bytes[$a]));
}


fclose($fp);

die("done");

?>