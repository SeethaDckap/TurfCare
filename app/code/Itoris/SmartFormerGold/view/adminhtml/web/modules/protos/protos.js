document.prototypes = Array();
document.presets = Array();

SFG_PresetElement = function (obj) {
	this.protoName = obj.getAttribute('proto');
	this.data = new SFG_Prototype(obj);
}

SFG_Preset = function (obj) {
	this.name = obj.getAttribute('name');
	this.elements = Array();
	var preview = obj.getElementsByTagName('preview')[0].childNodes;
	this.preview = '';
	for (p=0; p<preview.length; p++) if (preview[p].tagName) {
		var text = preview[p].text ? preview[p].text.replace(/^\s+|\s+$/g,"") : preview[p].textContent ? preview[p].textContent.replace(/^\s+|\s+$/g,"") : '';
		this.preview += '<'+preview[p].tagName;
		var style = preview[p].getAttribute('style');
		if (style) this.preview += ' style="'+style+'"';
		var type = preview[p].getAttribute('type');
		if (type) this.preview += ' type="'+type+'"';
		var src = preview[p].getAttribute('src');
		if (src) this.preview += ' src="'+src+'"';
		this.preview += '>'+text+'</'+preview[p].tagName+'>';
	}
	this.preview = this.preview.replace('{captcha0}', document.adminURL+'/images/alikon-captcha.png');
	this.preview = this.preview.replace('{media_url}', document.mediaUrl);
	var presets = obj.getElementsByTagName('elm');
	for (q=0; q<presets.length; q++) this.elements[this.elements.length] = new SFG_PresetElement(presets[q]);
}

SFG_Prototype = function (obj) {
	this.name = obj.getAttribute('name');
	this.tag = obj.getAttribute('tag');
	this.attributes = new SFG_ArrtibutesList (obj.getElementsByTagName('attributes')[0]);
	this.events = new SFG_ArrtibutesList (obj.getElementsByTagName('events')[0]);
	this.styles = new SFG_ArrtibutesList (obj.getElementsByTagName('styles')[0]);
	this.params = new SFG_ArrtibutesList (obj.getElementsByTagName('params')[0]);
	this.content = obj.getElementsByTagName('content')[0];
	this.contentPHP = obj.getElementsByTagName('contentPHP')[0];
	if (this.content) this.content = this.content.text ? this.content.text.replace(/^\s+|\s+$/g,"") : this.content.textContent.replace(/^\s+|\s+$/g,"");
}

SFG_ArrtibutesList = function (obj) {
	var list = Array();
	if (!obj) return list;
	for (o=0; o<obj.childNodes.length; o++) {
		var tag = obj.childNodes[o].tagName;
		var text = obj.childNodes[o].text ? obj.childNodes[o].text.replace(/^\s+|\s+$/g,"") : obj.childNodes[o].textContent.replace(/^\s+|\s+$/g,"");
		text = text.replace('{media_url}', document.mediaUrl);
		if (text == 'true')	text = true;		
		if (tag && text) list[list.length] = Array(tag, text, '');
	}
	return list;
}

function protoMouseDown(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	stopEvent(ev);
	deselectAll();
	setStuckPoints();
	var id = parseInt(obj.id.substr(5));
	document.tmpObject = new SFG_Element(document.prototypes[id]);
	document.tmpObject.object.style.left = mouse['x'] - 5 + 'px';
	document.tmpObject.object.style.top = mouse['y'] - 5 + 'px';
	addEvent(document.tmpObject.object, 'mouseup', protoMouseUp);
	document.body.appendChild(document.tmpObject.object);
	hideList();
}

function protoMouseUp(ev) {
	document.body.removeChild(document.tmpObject.object);
	var left = parseInt(document.tmpObject.object.style.left) - document.mostLeftScrolled;
	var top = parseInt(document.tmpObject.object.style.top) - document.mostTopScrolled;
	var cnt = document.allElements.length;
	document.tmpObject.updateProp(document.tmpObject.styles,'left', left + 'px', document.tmpObject.getPHP(document.tmpObject.styles, 'left')) ;
	document.tmpObject.updateProp(document.tmpObject.styles,'top', top + 'px', document.tmpObject.getPHP(document.tmpObject.styles, 'top')) ;
	document.allElements[cnt] = document.tmpObject.clone();
	document.allElements[cnt].select();
	document.tmpObject = null;
	document.getElementById('sfg_inner_container').appendChild(document.allElements[cnt].object);
	document.currentPageElements[document.currentPageElements.length] = document.allElements[cnt];
	setStuckPoints();
	showProps();
	showAllElementsList();	
}

function showPresetList(ev) {
	document.overPresets = true;
	document.body.appendChild(document.presetList);
	document.presetList.style.left = document.mostLeft + 'px';
	document.presetList.style.top = document.mostTop + 'px';
	document.presetList.style.height = document.getElementById('sfg_container').offsetHeight - 20 + 'px';
	document.getElementById('presetsData').style.height = document.presetList.offsetHeight - 20 + 'px';
}

function presetsOver(ev) {
	document.overPresets = true;
}

function presetsOut(ev) {
	document.overPresets = false;
	setTimeout('closePresetsList()',500);
}

function closePresetsList() {
	if (!document.overPresets && document.getElementById('presetList')) document.body.removeChild(document.getElementById('presetList'));
}

function presetMouseDown(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	while (obj.tagName.toLowerCase() != 'div' && obj.id.indexOf('preset')==-1) obj = obj.parentNode;
	stopEvent(ev);
	deselectAll();
	setStuckPoints();
	var id = parseInt(obj.id.substr(6));
	document.overPresets = false;
	closePresetsList();
	for (q=0; q<document.presets[id].elements.length; q++) {
		var p = -1;
		for (o=0; o<document.prototypes.length; o++) if (document.presets[id].elements[q].protoName == document.prototypes[o].name) {p=o; break;}
		if (p==-1) continue;
		var tmp = new SFG_Element(document.prototypes[p]);
		document.allElements[document.allElements.length] = tmp;
		document.currentPageElements[document.currentPageElements.length] = tmp;
		document.getElementById('sfg_inner_container').appendChild(tmp.object);
		tmp.select();
		tmp.page = document.currentPage;
		for (w=0; w<document.presets[id].elements[q].data.styles.length; w++) tmp.updateProp(tmp.styles,document.presets[id].elements[q].data.styles[w][0],document.presets[id].elements[q].data.styles[w][1],tmp.getPHP(tmp.styles,document.presets[id].elements[q].data.styles[w][0]));
		for (w=0; w<document.presets[id].elements[q].data.attributes.length; w++) tmp.updateProp(tmp.attributes,document.presets[id].elements[q].data.attributes[w][0],document.presets[id].elements[q].data.attributes[w][1],tmp.getPHP(tmp.attributes,document.presets[id].elements[q].data.attributes[w][0]));
		for (w=0; w<document.presets[id].elements[q].data.events.length; w++) tmp.updateProp(tmp.events,document.presets[id].elements[q].data.events[w][0],document.presets[id].elements[q].data.events[w][1],tmp.getPHP(tmp.events,document.presets[id].elements[q].data.events[w][0]));
		for (w=0; w<document.presets[id].elements[q].data.params.length; w++) tmp.updateProp(tmp.params,document.presets[id].elements[q].data.params[w][0],document.presets[id].elements[q].data.params[w][1],tmp.getPHP(tmp.params,document.presets[id].elements[q].data.params[w][0]));
		if (document.presets[id].elements[q].data.content) tmp.content = document.presets[id].elements[q].data.content;
		tmp.updateStyles();
		tmp.updateAttributes();
		if (tmp.content) tmp.applyContent();
		document.deltaLeft = 5;
		document.deltaTop = 5;
		if (tmp.getProp(document.presets[id].elements[q].data.styles,'left')=='0px' && tmp.getProp(document.presets[id].elements[q].data.styles,'top')=='0px') document.drag = tmp;
	}
	//areaMove(ev);
}

var img = document.createElement('img');
img.className = 'prototype';
img.src = document.adminURL+'/modules/protos/presets.png';
addEvent(img,'dragstart',stopEvent);
addEvent(img,'mouseover',showPresetList);
addEvent(img,'mouseout',presetsOut);
document.getElementById('sfg_elements_bar').appendChild(img);

var protoXML = loadXMLFile(document.adminURL+'/modules/protos/protos.xml');
var items = protoXML.documentElement.getElementsByTagName('preset');
for (i=0; i<items.length; i++) document.presets[document.presets.length] = new SFG_Preset(items[i]);

var items = protoXML.documentElement.getElementsByTagName('element');
for (i=0; i<items.length; i++) {
	document.prototypes[document.prototypes.length] = new SFG_Prototype (items[i]);
	var img = document.createElement('img');
	img.className = 'prototype';
	img.id = 'proto'+i;
	img.src = document.adminURL+'/modules/protos/' + items[i].getAttribute('image');
	img.title = '<b>'+lang['Element']+': </b><b style="color:red">'+items[i].getAttribute('name')+'</b><br /><b>'+lang['HTML Tag']+': </b><b style="color:blue">'+items[i].getAttribute('tag')+'</b>';
	var proto = document.prototypes[document.prototypes.length-1];
	if (proto.attributes.length>0) {
		img.title += '<br /><b>'+lang['Attributes']+':</b>';
		for(o=0; o<proto.attributes.length; o++) img.title += '<br /><b style="color:blue; margin-left:10px">' + proto.attributes[o][0]+'="'+proto.attributes[o][1] + '"</b>';
	}
	if (proto.styles.length>0) {
		img.title += '<br /><b>'+lang['Styles']+':</b><br /><b style="color:blue; margin-left:10px">';
		for(o=0; o<proto.styles.length; o++) img.title += proto.styles[o][0]+': '+proto.styles[o][1] + '; ';
		img.title += '</b>';
	}
	addEvent(img,'dragstart',stopEvent);
	addEvent(img,'mousedown',protoMouseDown);
	addEvent(img,'mousemove',showHint);
	addEvent(img,'mouseout',hideHint);
	document.getElementById('sfg_elements_bar').appendChild(img);
}

document.presetList = document.createElement('div');
document.presetList.id = 'presetList';
addEvent(document.presetList,'mousemove',updateMouse);
var div = document.createElement('div');
div.id = 'propertiesHeader';
div.innerHTML = '<div style="float:left">'+getTranslation('Snippets')+'</div>';
addEvent(document.presetList,'mouseover',presetsOver);
addEvent(document.presetList,'mouseout',presetsOut);
document.presetList.appendChild(div);
var div = document.createElement('div');
div.id = 'presetsData';
document.presetList.appendChild(div);
for (i=0; i<document.presets.length; i++) {
	var div2 = document.createElement('div');
	div2.id = 'preset'+i;
	div2.innerHTML = '<center><b style="color:red">'+document.presets[i].name+'</b></center>'+document.presets[i].preview;
	div.appendChild(div2);
	addEvent(div2,'mousedown', presetMouseDown);
	var imgs = div2.getElementsByTagName('img');
	for (o=0; o<imgs.length; o++) addEvent(imgs[o],'drag',stopEvent);
}
