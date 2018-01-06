<?php
set_time_limit(0);

if ($dauerlauf==1)
{
  $_SESSION["dauerlauf_runde"]="0";
  $_SESSION["dauerlauf_test"]=$test;
  $_SESSION["dauerlauf_nr"]=$nr;
  header("Location: ".$PHP_SELF."?featureInstanceId=$featureInstanceId");
  exit;
}

function checkDauerlauf()
{
  global $featureInstanceId;
  global $abort;
  if ($_SESSION["dauerlauf_runde"]!="")
  {
    if ($abort==1)
    {
      echo "Dauerlauf abgebrochen";
      $_SESSION["dauerlauf_runde"]="";
      exit;
    }
    else if ($_SESSION["dauerlauf_runde"]==$_SESSION["dauerlauf_nr"])
    {
      echo "Dauerlauf beendet";
      $_SESSION["dauerlauf_runde"]="";
      exit;
    }
    else
    {
      die("<script>location='".$PHP_SELF."?featureInstanceId=".$featureInstanceId."';</script>");
    }
  }
}

function showTests($tests)
{
  global $featureInstanceId;
  global $test;
  global $abort;

  if ($_SESSION["dauerlauf_runde"]>=$_SESSION["dauerlauf_nr"] || $abort==1) $_SESSION["dauerlauf_runde"]="";

  if ($_SESSION["dauerlauf_runde"]!="")
  {
    $_SESSION["dauerlauf_runde"]++;
    echo "Dauerlauf Runde ".$_SESSION["dauerlauf_runde"]." / ".$_SESSION["dauerlauf_nr"]." (<a href='".$PHP_SELF."?abort=1&featureInstanceId=$featureInstanceId'>Abbrechen</a>)<br><br>";
    $test = $_SESSION["dauerlauf_test"];
  }
  else
  {
    echo "<li><a href='".$PHP_SELF."?featureInstanceId=$featureInstanceId&test=all'>Alle Tests durchführen</a><br>";
    $options="<option selected value=all>Alle Tests durchführen";
    foreach ($tests as $key=>$actTest)
    {
      echo "<li><a href='".$PHP_SELF."?featureInstanceId=$featureInstanceId&test=$key'>$actTest</a><br>";
      $options.="<option value='$key'>$actTest";
    }
    echo "<form action='".$PHP_SELF."' method=post><input type=hidden name=dauerlauf value='1'><input type=hidden name=featureInstanceId value='$featureInstanceId'><li> <input type=text name=nr size=4 value='10'> x <select name=test>$options </select><input type=submit value='Dauerlauf'></form>";
  }
}


?>