<?php
include($_SERVER["DOCUMENT_ROOT"]."/homeserver/include/all.php");

setupTreeAndContent("rolloAdjustment_design.html", $message);

$rolloWait=500;

// Zum Start nach unten fahren
if ($step==1)
{
	   // Rollo stoppen
   callInstanceMethodByName($featureInstanceId, "stop");
   
   sleepMS($rolloWait);

	 // Rolloposition auf ganz oben stellen
   callInstanceMethodByName($featureInstanceId, "setPosition", array("position"=>"0"));
   
	 // Rollozeit auf 200 stellen
   callInstanceMethodByName($featureInstanceId, "setConfiguration", array("closeTime"=>"200" , "openTime"=>"200"));
   
   sleepMS($rolloWait);
   
   // Rollo schließen
   unset($paramData);
   callInstanceMethodByName($featureInstanceId, "start", array("direction"=>"TO_CLOSE"));
}
// dann nach oben fahren und zeit messen
else if ($step==2)
{
   // Rollo stoppen
   callInstanceMethodByName($featureInstanceId, "stop");
   
   sleepMS($rolloWait);

	 // Rolloposition auf ganz unten stellen
   callInstanceMethodByName($featureInstanceId, "setPosition", array("position"=>"100"));
   
   sleepMS($rolloWait);
   
   // Rollo öffnen
   callInstanceMethodByName($featureInstanceId, "start", array("direction"=>"TO_OPEN"));
   
   // Zeit starten
   $_SESSION["rolloCalibrationStart"]=time();
}
// openTime merken und nach unten fahren
else if ($step==3)
{
   // Rollo stoppen
   callInstanceMethodByName($featureInstanceId, "stop");

   // Zeit merken
	 $zeit = time()-$_SESSION["rolloCalibrationStart"];
	 $_SESSION["rolloOpenTime"]=$zeit;

   sleepMS($rolloWait);

	 // Rolloposition auf ganz oben stellen
   callInstanceMethodByName($featureInstanceId, "setPosition", array("position"=>"0"));

   sleepMS($rolloWait);
   
   // Rollo schließen
   unset($paramData);
   callInstanceMethodByName($featureInstanceId, "start", array("direction"=>"TO_CLOSE"));
   
   // Zeit starten
   $_SESSION["rolloCalibrationStart"]=time();
}
// closeTime und openTime konfigurieren
else if ($step==4)
{
   // Rollo stoppen
   callInstanceMethodByName($featureInstanceId, "stop");

	 $zeit = time()-$_SESSION["rolloCalibrationStart"];

   sleepMS($rolloWait);
   
   // Ergebnisse konfigurieren
   callInstanceMethodByName($featureInstanceId, "setConfiguration", array("closeTime"=>$zeit,"openTime"=>$_SESSION["rolloOpenTime"]));
   
   	 // Rolloposition auf ganz unten stellen
   callInstanceMethodByName($featureInstanceId, "setPosition", array("position"=>"100"));
}

$html = str_replace("%FEATURE_INSTANCE_ID%",$featureInstanceId, $html);
$html = str_replace("%LAST_OPEN%",$lastOpen, $html);

if ($step=="") $step=0;

if ($step==0)
{
	$message="Zum Start der Kalibrierung wird das Rollo ganz geschlossen.<br>Bitte jeweils die Anweisungen unterhalb bestätigen:";
	$stepMessage="Rolloschließen starten";
}
else if ($step==1)
{
	$message="Bitte bestätigen, wenn das Rollo unten angekommen ist und nicht mehr fährt:";
	$stepMessage="Rollo ist unten angekommen";
}
else if ($step==2)
{
	$message="Nun bitte sofort bestätigen, sobald das Rollo oben ist und nicht mehr fährt:";
	$stepMessage="Rollo ist oben angekommen";
}
else if ($step==3)
{
	$message="<b>OK!</b><br>Nun bitte sofort bestätigen, sobald das Rollo unten ist und nicht mehr fährt:";
	$stepMessage="Rollo ist unten angekommen";
}
else if ($step==4)
{
	$message="<b>OK!</b><br>Die Kalibrierung ist abgeschlossen.<br><br>Eingestellte Werte:<br>Öffnungszeit: ".$_SESSION["rolloOpenTime"]." Sekunden<br>Schließzeit: $zeit Sekunden";
	$stepMessage="";
}

$html = str_replace("%MESSAGE%",$message, $html);

if ($stepMessage=="") removeTag("%STEP%",$html);
else chooseTag("%STEP%",$html);

$html = str_replace("%STEP_MESSAGE%",$stepMessage, $html);

$step++;
$html = str_replace("%N%",$step, $html);




show();

?>