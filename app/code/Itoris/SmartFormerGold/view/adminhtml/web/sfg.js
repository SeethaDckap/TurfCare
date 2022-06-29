document.allElements = Array();
document.currentPageElements = Array();
document.selectedElements = Array();

document.loadedPlugins = 0;

document.gridSize = 10;
document.showGrid = true;
document.stuckEffect = true;
document.bindEffect = false;
document.showList = false;
document.showProps = true;
document.currentPage = 0;

document.sfg_php = '';
document.sfg_js = '';
document.sfg_html = '';
document.sfg_css = '';

document.currentBrowser = '';

var mouse = Array();

function showLoadingImage() {
	if (document.getElementById('loadingImage')) return;
	var img = document.createElement('img');
	img.id = 'loadingImage';
	img.src = document.adminURL + '/images/loading.gif';
	if (document.sfgArea.parentNode) document.sfgArea.parentNode.appendChild(img);
}

function hideLoadingImage() {
	var img = document.getElementById('loadingImage');
	if (img) img.parentNode.removeChild(img);
}

function sfgInitialize() {
	if (checkNavigator()) return;
	showLoadingImage();
	loadLanguage();
	loadTemplate();
	loadPlugins();
	loadHelpers();
	loadModules();
	setTimeout('keepAlive()',60000);
}

function keepAlive() {
	//HTTPRequest(document.siteURL+'/administrator/index.php?option=com_sfg&task=ping',false,null);
	//setTimeout('keepAlive()',60000);
}

function checkNavigator() {
	return false; //always support all modern browsers
}

function pluginLoaded(obj) {
	if (obj && obj.readyState && obj.readyState=="complete" || obj && obj=="complete") document.loadedPlugins--;
	if (document.loadedPlugins == 0) {
		hideLoadingImage();		
		if (document.form_id > 0) {
			loadForm();
			document.getElementById('container').scrollIntoView(false);		
		}
		setTimeout('resizeEditor(); document.getElementById("container").scrollIntoView(false);', 500);		
	} else showLoadingImage();
}

function loadLanguage() {
	document.write('<script language="Javascript" type="text/javascript" src="'+document.adminURL+'/languages/'+document.lang+'.js"></script>');
}

function loadTemplate() {
	document.write('<link rel="stylesheet" href="'+document.adminURL+'/templates/'+sfg_template+'.css" type="text/css" />');
	if (!document.getElementById('sfg_inner_container')) {
/*		var xmlhttp = HTTPRequest(document.adminURL+'/templates/'+sfg_template+'.html', false, null);
		document.sfgArea.innerHTML = xmlhttp.responseText;*/
		var div = document.createElement('div');
		div.id = 'sfg_menu';
		document.sfgArea.appendChild(div);
		var div = document.createElement('div');
		div.id = 'sfg_top';
		document.sfgArea.appendChild(div);
		var div = document.createElement('div');
		div.id = 'sfg_central';
		var div2 = document.createElement('div');
		div2.id = 'sfg_elements_bar';
		div.appendChild(div2);
		var div2 = document.createElement('div');
		div2.id = 'sfg_container';
		div.appendChild(div2);
		var div3 = document.createElement('div');
		div3.id = 'sfg_inner_container';
		div2.appendChild(div3);
		document.sfgArea.appendChild(div);
	}
	document.loadedPlugins++;
	document.write('<script language="Javascript" type="text/javascript" src="'+document.adminURL+'/templates/'+sfg_template+'.js" '+((window.attachEvent && document.currentBrowser!='opera')?'onreadystatechange="pluginLoaded(this)"':'onload="pluginLoaded(\'complete\')"')+'></script>');
}

function loadHelpers() {
	var xmlDoc = loadXMLFile(document.adminURL+'/sfg_helpers.xml');
	var helpers = xmlDoc.documentElement.getElementsByTagName('helper');
	for (hlp=0; hlp<helpers.length; hlp++) {
		var controller = helpers[hlp].getAttribute('controller');
		document.loadedPlugins++;
		document.write('<link rel="stylesheet" href="'+document.adminURL+'/helpers/'+controller+'.css" type="text/css" />');
		document.write('<script language="Javascript" type="text/javascript" src="'+document.adminURL+'/helpers/'+controller+'.js" '+((window.attachEvent && document.currentBrowser!='opera')?'onreadystatechange="pluginLoaded(this)"':'onload="pluginLoaded(\'complete\')"')+'></script>');
	}
}

function loadModules() {
	var xmlDoc = loadXMLFile(document.adminURL+'/sfg_modules.xml');
	var modules = xmlDoc.documentElement.getElementsByTagName('module');
	for (mdl=0; mdl<modules.length; mdl++) {
		var target = modules[mdl].getAttribute('target');
		var controller = modules[mdl].getAttribute('controller');
		document.loadedPlugins++;
		document.write('<link rel="stylesheet" href="'+document.adminURL+'/modules/'+controller+'/'+controller+'.css" type="text/css" />');
		document.write('<script language="Javascript" type="text/javascript" src="'+document.adminURL+'/modules/'+controller+'/'+controller+'.js" '+((window.attachEvent && document.currentBrowser!='opera')?'onreadystatechange="pluginLoaded(this)"':'onload="pluginLoaded(\'complete\')"')+'></script>');
	}
}

function loadPlugins() {
	var xmlDoc = loadXMLFile(document.adminURL+'/sfg_plugins.xml');
	var plugins = xmlDoc.documentElement.getElementsByTagName('plugin');
	for (plg=0; plg<plugins.length; plg++) {
		var controller = plugins[plg].getAttribute('controller');
		document.loadedPlugins++;
		document.write('<script language="Javascript" type="text/javascript" src="'+document.adminURL+'/plugins/'+controller+'/'+controller+'.js" '+((window.attachEvent && document.currentBrowser!='opera')?'onreadystatechange="pluginLoaded(this)"':'onload="pluginLoaded(\'complete\')"')+'></script>');
	}
}

function loadXMLFile(xmlfile) {
	var xmlDoc;
	
	if(window.XMLHttpRequest) {
		xmlDoc = new window.XMLHttpRequest();
		xmlDoc.open("GET",xmlfile,false);
		xmlDoc.send("");
		return xmlDoc.responseXML;
	} else if(window.ActiveXObject) {
		xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async = false;
		xmlDoc.load(xmlfile);
		return xmlDoc;
	} else {
		alert(getTranslation("XML loading not supported."));
		return null;
	}
}

function HTTPRequest(url, async, func) {
	var xmlhttp;
	try{ xmlhttp = new XMLHttpRequest(); }
	catch (e) {
		try{ xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");	}
		catch (e) {
			try{ xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (e) { alert(e.message); return; }
		}
	}
	xmlhttp.open("GET", url , async);
	if (async == true) {
		xmlhttp.onreadystatechange= function() {
			if (xmlhttp.readyState==4) {
				window[func](xmlhttp);
			}
		}
	}
	xmlhttp.send(null);
	if (async == false) {
		return xmlhttp;
	}
}

function addEvent (el, ev, func) {
	if (el.attachEvent) el.attachEvent("on"+ev, func);
		else el.addEventListener(ev, func, true); 
}

function stopEvent (ev) {
	if (window.event) {
		window.event.cancelBubble = true;
		window.event.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
}

function in_array(needle, haystack, strict) {
	var found = false, key, strict = !!strict;
		for (key in haystack) {
			if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
				found = true;
				break;
			}
		}
	return found;
}

function getTranslation(str) {
	return lang[str] ? lang[str] : str;
}

function showMask() {
	if (document.getElementById('sgf_mask')) return;
	var div = document.createElement('div');
	div.id = 'sgf_mask';
	if (document.attachEvent && document.currentBrowser!='opera') div.style.filter='progid:DXImageTransform.Microsoft.BasicImage(opacity=.3)';
		else div.style.opacity='0.3';
	document.body.appendChild(div);
}

function hideMask() {
	var mask = document.getElementById('sgf_mask');
	if (mask) mask.parentNode.removeChild(mask);
}

function alertSfg(message){
    var displayTime = 1500;
    var hidingTime = 1000;
    var container = document.getElementById('container');
    var alwidth = 150; // integer
    var alheight = 100; // integer
    var posX = (parseInt(container.offsetWidth)-alwidth)/2; // integer
    var posY = (parseInt(container.offsetHeight)-alheight)/2; // integer
    var al = document.createElement('div');
    al.id = 'alert_div';
    al.style.width = alwidth+'px';
    //al.style.height = alheight+'px';
    al.style.top = posY+'px';
    al.style.left = posX+'px';
    al.innerHTML = message;
    container.appendChild(al);
    setTimeout("hideAlertSfg("+hidingTime+");", displayTime);
}

function hideAlertSfg(timeout){
    var al = document.getElementById('alert_div');
    reduceOpacity(timeout, 100);
    setTimeout("removeAlert();", timeout+1000);
}

function reduceOpacity(timeout, index){
    var al = document.getElementById('alert_div');
    var interval = timeout/20;
    // Firefox:
    al.style.opacity=index/100;
    // IE:
    al.style.filter='alpha(opacity='+index+')';
    index=index-5;
    if (index>0) setTimeout("reduceOpacity("+timeout+", "+index+");", interval);
}

function removeAlert(){
    var al = document.getElementById('alert_div');
    al.parentNode.removeChild(al);
}
