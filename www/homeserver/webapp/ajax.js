var xmlhttp=false;
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
    }
  }
}

var xmlhttpStatus=false;
if (typeof XMLHttpRequest != 'undefined') xmlhttpStatus = new XMLHttpRequest();
if (!xmlhttpStatus) 
{
  try 
  {
	  xmlhttpStatus = new ActiveXObject("Msxml2.XMLHTTP");
  }
  catch(e) 
  {
  	alert(e);
    try 
    {
    	xmlhttpStatus = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
    	alert(e);
    	xmlhttpStatus = null;
    }
  }
}

var xmlhttpAsync=false;
if (typeof XMLHttpRequest != 'undefined') xmlhttpAsync = new XMLHttpRequest();
if (!xmlhttpAsync) 
{
  try 
  {
	  xmlhttpAsync = new ActiveXObject("Msxml2.XMLHTTP");
  }
  catch(e) 
  {
  	alert(e);
    try 
    {
    	xmlhttpAsync = new ActiveXObject("Microsoft.XMLHTTP");
    }
    catch(e)
    {
    	alert(e);
    	xmlhttpAsync = null;
    }
  }
}

function send(request, callbackFunction)
{
  try
  {
  	debugIt("send");
  	//alert("NORMAL: "+request);
  	//debugArea("NORMAL: "+request);
    xmlhttp.open("GET",request);
  	debugIt("send done");
    xmlhttp.onreadystatechange = function()
    {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
      {
      	debugIt("send response");
      	if (callbackFunction!="") eval(callbackFunction)(xmlhttp.responseText);
      }
    }
    xmlhttp.send(null);
  } catch (e)
  {
  	 alert(e);
  	 print_r(e);
  }
}

function sendAsync(request)
{
  try
  {
  	debugIt("sendAsync");
  	//debugArea("NORMAL: "+request);
    xmlhttpAsync.open("GET",request);
  	debugIt("xmlhttpAsync done");
    xmlhttpAsync.onreadystatechange = function()
    {
      if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
      {
      	debugIt("xmlhttpAsync response");
      }
    }
    xmlhttpAsync.send(null);
  } catch (e)
  {
  	 alert(e);
  	 print_r(e);
  }
}

function sendStatus(request, callbackFunction)
{
  try
  {
  	debugIt("sendStatus");
  	//debugArea("STATUS: "+request);
    xmlhttpStatus.open("GET",request);
    debugIt("sendStatus done");
    xmlhttpStatus.onreadystatechange = function()
    {
      if (xmlhttpStatus.readyState == 4 && xmlhttpStatus.status == 200)
      {
      	debugIt("sendStatus response");
      	
        if (callbackFunction!="")
        {
        	eval(callbackFunction)(xmlhttpStatus.responseText);
        }
      }
    }
    xmlhttpStatus.send(null);
  } catch (e)
  {
  	 alert(e);
  	 print_r(e);
  }
}