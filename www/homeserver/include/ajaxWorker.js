var xmlhttp1=false;
var ajaxBusy1=false;
var consoleLogging=false;
if (typeof XMLHttpRequest != 'undefined') xmlhttp = new XMLHttpRequest();
if (!xmlhttp)
{
  try 
  {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  }
  catch(e) 
  {
    try 
    {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
    	xmlhttp = null;
    	alert(e);
    }
  }
}

var xmlhttp2=false;
var ajaxBusy2=false;
if (typeof XMLHttpRequest != 'undefined') xmlhttp2 = new XMLHttpRequest();
if (!xmlhttp2)
{
  try 
  {
    xmlhttp2 = new ActiveXObject("Msxml2.XMLHTTP");
  }
  catch(e) 
  {
    try 
    {
      xmlhttp2 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
    	xmlhttp2 = null;
    	alert(e);
    }
  }
}

function sendAjax(request)
{
	if (request.indexOf("?")==-1) request=request+"?ajax=1";
	else request=request+"&ajax=1";
	
	if (!ajaxBusy1) _sendAjax1(request);
	else if (!ajaxBusy2) _sendAjax2(request);
	else
	{
		if (consoleLogging) console.log("delay: "+request);
		setTimeout(function () {sendAjax(request)},300);
	}
}

function _sendAjax1(request)
{
	if (consoleLogging) console.log("ajax1: "+request);
  try
  {
  	//alert(request);
  	ajaxBusy1=true;
    xmlhttp.open("GET",request);
    xmlhttp.onreadystatechange = function()
    {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
      {
      	ajaxBusy1=false;
      	if (consoleLogging) console.log("ajax1 ok");
      	//alert("ok");
      }
    }
    xmlhttp.send(null);
  } catch (e)
  {
  	 alert(e);
  }
}

function _sendAjax2(request)
{
	if (consoleLogging) console.log("ajax2: "+request);
  try
  {
  	//alert(request);
  	ajaxBusy2=true;
    xmlhttp2.open("GET",request);
    xmlhttp2.onreadystatechange = function()
    {
      if (xmlhttp2.readyState == 4 && xmlhttp2.status == 200)
      {
      	ajaxBusy2=false;
      	if (consoleLogging) console.log("ajax2 ok");
      	//alert("ok");
      }
    }
    xmlhttp2.send(null);
  } catch (e)
  {
  	 alert(e);
  }
}