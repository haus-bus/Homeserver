// Konfigurationen
var paramHoldTime=500; // Zeit die eine Maus gedrückt sein muss, bis es als HALTEN erkannt wird
var pollTime=1000;      // Interval in ms in dem der Server nach dem aktuellen Status gefragt wird

document.write('<scr'+'ipt type="text/javascript" src="ajax.js"></sc'+'ript>');
document.write('<scr'+'ipt type="text/javascript" src="utils.js"></sc'+'ript>');
document.write('<scr'+'ipt type="text/javascript" src="svgUtils.js"></sc'+'ript>');


var minMoveDist=5;
var debugActive=0;
var myObjects = new Array();
var myObjectCount = 0;
var myAliasCount=0;
var selectedId=-1;
var downTime=-1;
var downPosition=-1;
var lastShownId=-1;
var debug="";
var contextTimer=-1;
var actionTimer=-1;
var pollingTimer=-1;
var initDone=0;

var startTime=new Date().getTime();
var lastClickTime=new Date().getTime();
var maxRunTime=600000; // 10 min
var minClickDelay=60000; // 1 min
//var maxRunTime=10000; // 10 sec
//var minClickDelay=5000; // 5sec

function init()
{
	//alert("init");
	debugIt("init");
	
	if (initDone==0)
	{
 	  registerAllObjects();
 	  //setTimeout("init()",5000);
 	}
	//if (isMobile()) paramHoldTime+=300; // Mobile Browser verzögern die Events um 300ms
	//reloader();
}

function reloader()
{
	 var actTime = new Date().getTime();
	 if (actTime-startTime>maxRunTime && actTime-lastClickTime>minClickDelay) location.reload();
	 else setTimeout("reloader()",minClickDelay);
}

function registerCallback(result)
{
	  debugIt("registerCallback");
	  initDone=1;
		sendAsync("ajaxServer.php?command=readStatus");
		updateStatus();
		//pollStatus();
}

/**
 * Prüft anhand vorhandener Touchevents, ob wir auf einem Handy laufen
 */
function isMobile()
{
	var supportsTouch = 'ontouchstart' in window || navigator.msMaxTouchPoints;
	return supportsTouch;
	//return window.Touch;
}

/**
 * Wählt ein angeklicktes Objekt aus und merkt es sich
 */
function select(id, evt)
{
	//alert("select: "+id+", "+evt+","+new Date().getTime());
	if (myObjects[id]!=null)
	{
 	  selectedId=id;
    downTime = new Date().getTime();
    //activateMoving(id, evt);
	  
	  if (hasContext())
	  {
	    if (contextTimer!=-1) clearTimeout(contextTimer);
	    evt.preventDefault();
 		  contextTimer = setTimeout(function () {showContext()},paramHoldTime);
	  }
	  
	  //showContext();
	}
	
	lastClickTime = new Date().getTime();
}


/**
 * Wird aufgerufen wenn ein Element nach oben oder nach unten verschoben wird
 */
function move(id, evt)
{
	var yPos=0;
	if (isMobile())
	{
		var touches = evt.changedTouches;
	  if (touches==null) return;
 	  var first = touches[0];
 	  yPos = first.clientY;
 	}
 	else
 		yPos = evt.clientY;

  //alert("move "+yPos+","+downPosition);

	var diff = yPos-downPosition;
	var direction="";
	if (diff>minMoveDist) direction="down";
	else if (diff <-minMoveDist) direction="up";
		
	if (direction!="")
	{
		send("ajaxServer.php?command=click"+direction+"&id="+id.replace("#", "_"), "dummyCallback");
		//deActivateMoving(id);
		
		moveFeedback(id, direction, 0);
	}
	
	//evt.preventDefault();
}

function moveFeedback(id, direction, step)
{
	 if (step>10) return;
	 if (direction=="up") document.getElementById("arrowUp"+id).style.opacity=step%2;
	 else document.getElementById("arrowDown"+id).style.opacity=step%2;
	 step++;
	 setTimeout(function(){moveFeedback(id, direction, step)},300);
}

function debugIt(message)
{
	if (debugActive==1) debug+=((new Date().getTime())/1000)+": "+message+"\n";
}

function showDebug()
{
  alert(debug);
  debug="";
}

/**
 * Zeigt das Kontextmenü zu einem Element
 */
function showContext()
{
	if (myObjects[selectedId]!=null)
	{
		lastShownId=selectedId;
	  var type = myObjects[selectedId]["type"];
  	if (type=="dimmer" || type=="rollo")
  	{
 		  //displayDimmerSlider(selectedId);
 		  var myObject = document.getElementById("status"+selectedId);

 		  var slideObj = document.getElementById("slideup");
 	 	  var dx=myObject.getBBox().x-slideObj.getBBox().x+myObject.getBBox().width*1.2;
 	 	  var dy=myObject.getBBox().y-slideObj.getBBox().y-+myObject.getBBox().height*0.5;
 		  slideObj.setAttribute('transform', 'translate('+dx+','+dy+')');
 		  slideObj.setAttribute("visibility","visible");

 		  var slideObj = document.getElementById("slidedown");
 	 	  var dx=myObject.getBBox().x-slideObj.getBBox().x+myObject.getBBox().width*1.2;
 	 	  var dy=myObject.getBBox().y-slideObj.getBBox().y-myObject.getBBox().height*0.5+slideObj.getBBox().height*1.2;
 		  slideObj.setAttribute('transform', 'translate('+dx+','+dy+')');
 		  slideObj.setAttribute("visibility","visible");

 		  updateStatusOfLastShownIdByServer();
  	}
  }
}

/**
 * Versteckt das Kontextmenü zu einem Element
 */
function hideContext()
{
	if (myObjects[selectedId]!=null)
	{
		lastShownId=-1;
	  var type = myObjects[selectedId]["type"];
  	if (type=="dimmer" || type=="rollo")
  	{
  		var slideObj = document.getElementById("slideup");
  		slideObj.setAttribute("visibility","hidden");
  		var slideObj = document.getElementById("slidedown");
  		slideObj.setAttribute("visibility","hidden");
  	}
  }
}

function hasContext()
{
	if (myObjects[selectedId]==null) return false;
	
  var type = myObjects[selectedId]["type"];
  if (type=="dimmer")	return true;
  if (type=="rollo")	return true;
  return false;
}

function activateMoving(id, evt)
{
	if (myObjects[selectedId]["type"]=="rollo") 
	{
  	if (isMobile())
	  {
  		var touches = evt.changedTouches;
	    if (touches==null) {alert("touches is null");return;}
 	    var first = touches[0];
 	    downPosition = first.clientY;
		  document.getElementById(id).ontouchmove=function (e){move(id, e);}
  	}
	  else
    {
    	downPosition = evt.clientY;
    	document.getElementById(id).onmousemove=function (e){move(id, e);}
    }
  }
}


function deActivateMoving(id)
{
	if (myObjects[selectedId]["type"]=="rollo") 
	{
	  downPosition=-1;
	
	  if (isMobile()) document.getElementById(id).ontouchmove=null;
	  else document.getElementById(id).onmousemove=null;
	}
}


/**
 * Wird aufgerufen, nachdem ein Element angeklickt wurde.
 * Wurde die Mouse lange gehalten, wird das Kontextmenü angezeigt
 * und sonst die Standard-Klickaktion durchgeführt.
 */
function action(id)
{
	if (selectedId==-1 || downTime==-1) return;
  if (contextTimer!=-1) clearTimeout(contextTimer);
  
  debugIt("action"+id);
	var holdTime = new Date().getTime()-downTime;
	if (holdTime > paramHoldTime && hasContext()) 
	{
		//alert("Hold");
		//showContext();
	}
	else
  {
  	
  	if (lastShownId!=-1) hideContext();
  	
  	feedback(id,0);
  	var functionParam1 = myObjects[selectedId]["functionParam1"];
  	var functionParam2 = myObjects[selectedId]["functionParam2"];
  	var myFunction = myObjects[selectedId]["function"];
  	send("ajaxServer.php?command=click&id="+id.replace("#", "_")+"&functionParam1="+functionParam1+"&functionParam2="+functionParam2+"&function="+myFunction, "dummyCallback");
  }
	
  if (debugActive==1) showDebug();
	
	//deActivateMoving(id);
	
	downTime=-1;
	selectedId=-1;
}

/**
 * Aktualisiert die Kontextanzeige nach Statusupdate vom Server
 */
function updateStatusOfLastShownIdByServer()
{
	if (myObjects[lastShownId]!=null)
	{
	  var type = myObjects[lastShownId]["type"];
  	//if (type=="dimmer") setSliderValue(myObjects[lastShownId]["text"]);
  }
}

/**
 * Senden den neuen Status zum Server nachdem ein Kontextmenü verstellt wurde.
 */
function updateStatusOfLastShownIdByContext(value)
{
	if (myObjects[lastShownId]!=null) send("ajaxServer.php?command=setValue&newValue="+value+"&id="+lastShownId.replace("#", "_"), "updateStatusOfLastShownIdByContextCallback");
}

function updateStatusOfLastShownIdByContextCallback()
{
	//debugArea("updateStatusOfLastShownIdByContextCallback");
}

/**
 * kompatibilität. kann später mal raus
 */
function registerObject(id, type, action, paramOn, paramOff, functionParam1)
{
	registerObject(id, type, action, paramOn, paramOff, functionParam1, "", "");

}

function registerObject(id, type, action, paramOn, paramOff, functionParam1, functionParam2, myFunction)
{
	registerObject(id, type, action, paramOn, paramOff, functionParam1, functionParam2, myFunction, "", "", "", "", "", "", "", "", "", "");
}

/**
 * Registriert ein GUI Element für die Steuerung
 */
function registerObject(id, type, action, paramOn, paramOff, functionParam1, functionParam2, myFunction, otherObjectId, otherAction, otherParamOn, otherParamOff, otherParam1, otherObjectId2, otherAction2, otherParamOn2, otherParamOff2, otherParam12)
{
	var newObject = new Array();
	newObject["status"]=-1;
	newObject["text"]="###";
	newObject["feedback"]=0;
	newObject["type"]=type;
	newObject["action"]=action;
	newObject["paramOn"]=paramOn;
	newObject["paramOff"]=paramOff;
	newObject["functionParam1"]=functionParam1;
	newObject["functionParam2"]=functionParam2;
	newObject["function"]=myFunction;
	newObject["otherObjectId"]=otherObjectId;
	newObject["otherAction"]=otherAction;
	newObject["otherParamOn"]=otherParamOn;
	newObject["otherParamOff"]=otherParamOff;
	newObject["otherParam1"]=otherParam1;
	newObject["otherObjectId2"]=otherObjectId2;
	newObject["otherAction2"]=otherAction2;
	newObject["otherParamOn2"]=otherParamOn2;
	newObject["otherParamOff2"]=otherParamOff2;
	newObject["otherParam12"]=otherParam12;
	
	myObjects[id]=newObject;
	myObjectCount++;
	if (id.indexOf("#")!=-1) myAliasCount++;
}


/**
 * Meldet alle Listener auf den GUI Elementen an und regestriert sie beim Server.
 */
function registerAllObjects()
{
	debugIt("registerAllObjects");
	
	
	var params="objects="+(myObjectCount-myAliasCount);
	var i=0;
	for (id in myObjects)
  {
  	
		if (isMobile())
		{
			document.getElementById(id).ontouchstart = function (e) {select(this.id, e);}
			document.getElementById(id).ontouchend = function (e) {action(this.id);}
		}
		else
	  {
	  	document.getElementById(id).onmousedown = function (e) {select(this.id, e);}
  	  document.getElementById(id).onmouseup = function (e) {action(this.id);}
  	}

		//document.getElementById(id).ondoubleclick = function (e) {drag(this.id);}
		if (id.indexOf("#")==-1)
		{
		  params+="&object"+i+"="+id;
		  i++;
		}
	}

  console.log("Registered: "+params);
	send("ajaxServer.php?command=registerObjects&"+params, "registerCallback");
	//send("ajaxServer.php?command=registerObjects&"+params, "updateStatusCallback");

  // Sliders registrieren
  var slideObj=document.getElementById("slideup");
  if (isMobile())
  {
  	slideObj.ontouchstart = function (e) {slideUp(e);}
  	slideObj.ontouchend = function (e) {slideRelease(e);}
  }
	else
  {
    slideObj.onmousedown = function (e) {slideUp(e);}
	  slideObj.onmouseup = function (e) {slideRelease(e);}
	}
	
	var slideObj=document.getElementById("slidedown");
  if (isMobile())
  {
  	slideObj.ontouchstart = function (e) {slideDown(e);}
  	slideObj.ontouchend = function (e) {slideRelease(e);}
  }
	else
  {
    slideObj.onmousedown = function (e) {slideDown(e);}
	  slideObj.onmouseup = function (e) {slideRelease(e);}
	}
	
	debugIt("registerAllObjects done");
}

function slideUp(evt)
{
	//alert("slideup");
	evt.preventDefault();
	send("ajaxServer.php?command=clickup&id="+lastShownId.replace("#", "_"), "dummyCallback");
}

function slideDown(evt)
{
	//alert("slidedown");
	evt.preventDefault();
	send("ajaxServer.php?command=clickdown&id="+lastShownId.replace("#", "_"), "dummyCallback");
}

function slideRelease(evt)
{
	//alert("sliderelease");
	//evt.preventDefault();
	send("ajaxServer.php?command=clickrelease&id="+lastShownId.replace("#", "_"), "dummyCallback");
}

/**
 * Dummy callback Funktion für Requests die keine Antwort brauchen.
 */
function dummyCallback(result)
{
	 //alert("dummyCallback: "+result);
}

/**
 * Fragt zyklisch den aktuellen Status beim Server ab.
 */
function pollStatus()
{
 	 updateStatus();
 	 setTimeout("pollStatus()",pollTime);
}

function updateStatus()
{
	debugIt("updateStatus");
	sendStatus("ajaxServer.php?command=updateMyStatus", "updateStatusCallback");
}

//1493176580=100,1493176581=-1,1493176582=50
function updateStatusCallback(result)
{
	debugIt("updateStatusCallback");
	
	//debugArea("updateStatusCallback: "+result);
	//alert(result);
	if (result!="")
	{
		 var el = result.split(",");
		 for (var i=0;i<el.length;i++)
		 {
		 	  var act = el[i].split("=");
		 	  var objectId = act[0];
		 	  act = act[1].split(";");
		 	  
		 	  var status=act[0];
		 	  var text=act[1];
		 	  
		 	  var toDirection="";
		 	  if (act.length>2) toDirection=act[2];
		 	  
		 	  var oldStatus = myObjects[objectId]["status"];

	 	  	console.log(objectId+": status "+myObjects[objectId]["status"]+" -> "+status);
		 	  
		 	  if (myObjects[objectId]["status"]!=status || myObjects[objectId]["text"]!=text)
		 	  {
		 	  	//alert("anders: "+objectId+","+status+","+text);
		 	  	//debugArea("anders: "+objectId+","+status+","+text);
		 	  	
		 	    myObjects[objectId]["status"]=status;
		 	    if (text!="###") myObjects[objectId]["text"]=text;
		 	    myObjects[objectId]["feedback"]=0;
		 	    if (myObjects[objectId+"#2"]!=null) myObjects[objectId+"#2"]["feedback"]=0;
		 	    
          updateSvgObject(objectId);
		 	    updateStatusOfLastShownIdByServer();
		 	  }
		 	  //else alert("Gleich: "+objectId+","+status+","+text);
		 	  
		 	  //alert(objectId+"="+status+", "+text);
		 	  
		 	  if (toDirection!="") handleDirection(objectId, toDirection);
		 }
  }
  
  setTimeout("updateStatus()",pollTime);
}

function handleDirection(id, toDirection)
{
	if (myObjects[id]["type"]=="rollo") moveFeedback(id, toDirection, 0);
}

function updateSvgObject(objectId)
{
	var origId=objectId;
	
	for (var i=0;i<2;i++)
	{
		if (i>0) objectId=origId+"#"+(i+1);
		
    var myObj = document.getElementById("status"+objectId);
  	if (myObj!=null)
  	{
      var status = myObjects[origId]["status"];
  		var text = myObjects[origId]["text"];
  		
      var style = myObj.style;
      var actAction = myObjects[origId]["action"];
      if (actAction=="fill")
      {
      	 console.log(objectId+": fill status = "+status);
         if (status==1) style.fill=myObjects[origId]["paramOn"];
         else style.fill=myObjects[origId]["paramOff"];
      }
      else if (actAction=="scale")
      {
      	 var scaleMode = style.fill=myObjects[origId]["paramOn"];
      	 var value = myObjects[origId]["text"];
      	 var scale = value/100;
      	 if (scale==0) scale=0.001;
      	 
      	 if (scaleMode=="x") scaleObj(myObj,scale, 1);
      	 else if (scaleMode=="y") scaleObj(myObj,1,scale);
      	 else if (scaleMode=="xy") scaleObj(myObj,scale,scale);
      	 else alert("Unbekannter scale mode "+scaleMode);
      }
      else if (actAction=="opacity")
      {
      	 var value = myObjects[origId]["text"];
      	 if (status==1 && myObjects[origId]["paramOn"]!="") value = myObjects[origId]["paramOn"];
      	 else if (status==0 && myObjects[origId]["paramOff"]!="") value = myObjects[origId]["paramOff"];

       	 console.log(objectId+" opacity = "+value);
      	 
      	 if (value!="")
      	 {
 	      	 var opacity = value/100;
      	   myObj.style.opacity=opacity;
      	 }
      }
      else if (actAction!="") alert("Nicht implementierte Action: "+actAction);
      
      
      if (i==0)
      {
      	 var otherObjectId = myObjects[objectId]["otherObjectId"];
      	 if (otherObjectId!="" && document.getElementById("status"+otherObjectId)!=null)
      	 {
        	 var otherObj = document.getElementById("status"+otherObjectId);
        	 var style = otherObj.style;
        	 var actAction = myObjects[objectId]["otherAction"];
        	 var paramOn = myObjects[objectId]["otherParamOn"];
        	 var paramOff = myObjects[objectId]["otherParamOff"];
        	 var param1 = myObjects[objectId]["otherParam1"];
  
        	 console.log(objectId+": other status = "+status+" other action ="+actAction+", paramOn = "+paramOn+", paramOff = "+paramOff+", param1 = "+param1);
  
        	 if (actAction=="fill")
           {
              if (status==1 && paramOn!="") style.fill=paramOn;
              else if (status==0 && paramOff!="") style.fill=paramOff;
           }
           else if (actAction=="scale")
           {
           	 var scaleMode = style.fill=myObjects[origId]["paramOn"];
           	 var value = 0;
           	 if (status==1) value = paramOn;
           	 else value=paramOff;
           	 var scale = value/100;
           	 if (scale==0) scale=0.001;
           	 
           	 if (scaleMode=="x") scaleObj(otherObj,scale, 1);
           	 else if (scaleMode=="y") scaleObj(otherObj,1,scale);
           	 else if (scaleMode=="xy") scaleObj(otherObj,scale,scale);
           	 else alert("Unbekannter scale mode "+scaleMode);
           }
           else if (actAction=="opacity")
           {
           	 var value = "";
           	 if (status==1) value = paramOn;
           	 else value=paramOff;
           	 
           	 console.log(otherObjectId+" opacity = "+value);
  
           	 if (value!="")
           	 {
             	 var opacity = value/100;
             	 otherObj.style.opacity=opacity;
             }
           }
           else if (actAction!="") alert("Nicht implementierte Action: "+actAction);
        }
        
        var otherObjectId = myObjects[objectId]["otherObjectId2"];
      	if (otherObjectId!="" && document.getElementById("status"+otherObjectId)!=null)
      	{
        	 var otherObj = document.getElementById("status"+otherObjectId);
        	 var style = otherObj.style;
        	 var actAction = myObjects[objectId]["otherAction2"];
        	 var paramOn = myObjects[objectId]["otherParamOn2"];
        	 var paramOff = myObjects[objectId]["otherParamOff2"];
        	 var param1 = myObjects[objectId]["otherParam12"];
  
        	 console.log(objectId+": other2 status = "+status+" other action ="+actAction+", paramOn = "+paramOn+", paramOff = "+paramOff+", param1 = "+param1);
  
        	 if (actAction=="fill")
           {
              if (status==1 && paramOn!="") style.fill=paramOn;
              else if (status==0 && paramOff!="") style.fill=paramOff;
           }
           else if (actAction=="scale")
           {
           	 var scaleMode = style.fill=myObjects[origId]["paramOn"];
           	 var value = 0;
           	 if (status==1) value = paramOn;
           	 else value=paramOff;
           	 var scale = value/100;
           	 if (scale==0) scale=0.001;
           	 
           	 if (scaleMode=="x") scaleObj(otherObj,scale, 1);
           	 else if (scaleMode=="y") scaleObj(otherObj,1,scale);
           	 else if (scaleMode=="xy") scaleObj(otherObj,scale,scale);
           	 else alert("Unbekannter scale mode "+scaleMode);
           }
           else if (actAction=="opacity")
           {
           	 var value = "";
           	 if (status==1) value = paramOn;
           	 else value=paramOff;
           	 
           	 console.log(otherObjectId+" opacity = "+value);
  
           	 if (value!="")
           	 {
             	 var opacity = value/100;
             	 otherObj.style.opacity=opacity;
             }
           }
           else if (actAction!="") alert("Nicht implementierte Action: "+actAction);
       }
  	}
  
    var myObj = document.getElementById("text"+objectId);
  	if (myObj!=null)
	{
	  if (myObjects[origId]["text"]!="###") myObj.textContent=myObjects[origId]["text"];
	}
  	else if (i==0) alert("Unbekanntes Objekt text"+objectId);
	  }
  }
}

function feedback(objectId, step)
{
	var type = myObjects[objectId]["type"];
	
	if (type=="Taster") return;
	
	var myObj = document.getElementById("status"+objectId);
	if (myObj!=null)
	{
		var status = myObjects[objectId]["status"];
		if (step==0)
		{
			myObjects[objectId]["feedback"]=1;
			if (status==1) step=-1;
		}
		
		if (myObjects[objectId]["feedback"]==0 || Math.abs(step)>30) return;
		
    var style = myObj.style;
    var actAction = myObjects[objectId]["action"];
    if (actAction=="fill")
    {
       if (step%2==0) style.fill=myObjects[objectId]["paramOff"];
       else style.fill=myObjects[objectId]["paramOn"];
       if (step<0) step--;
       else step++;
       setTimeout(function(){feedback(objectId, step)},100);
    }
	}
	//else alert("feedback: Unbekanntes Objekt status"+objectId);
}

function itemClicked(id)
{
	alert("Click: "+id);
}

function vollbild() 
{
 var element = document.getElementById("myBody");
 
 if (element.requestFullScreen) element.requestFullScreen();
 else if (element.mozRequestFullScreen) element.mozRequestFullScreen();
 else if (element.webkitRequestFullScreen) element.webkitRequestFullScreen();
}