var menuXML = loadXMLFile(document.adminURL+'/modules/menu/menu.xml');
var items = menuXML.documentElement.getElementsByTagName('toplevel');
for (i=0; i<items.length; i++) {
	var item = document.createElement('div');
	item.className = 'toplevel';
	item.innerHTML = getTranslation(items[i].getAttribute('name'));
	var sublevelPan = document.createElement('div');
	sublevelPan.className = 'sublevel-pan';
	item.appendChild(sublevelPan);
	document.getElementById('sfg_menu').appendChild(item);
	var sublevels = items[i].getElementsByTagName('sublevel');
	for (o=0; o<sublevels.length; o++) {
		var sublevel = document.createElement('div');
		sublevel.className = 'sublevel';
		var img = sublevels[o].getAttribute('img');
		if (img && img!='') sublevel.style.backgroundImage = 'url('+document.adminURL+'/modules/menu/'+img+')';
		var preload = sublevels[o].getAttribute('preload');
		if (preload && preload!='' && window[preload]) window[preload](sublevel);
		var span = document.createElement('span');
		var text = getTranslation(sublevels[o].getAttribute('name'));
		if (text.indexOf('(') > -1) {
			text = '<div style="width:100%; height:22px;"><div style="float:left;">'+text.substring(0,text.indexOf('(')-1)+'</div><div style="margin-right:3px; color:#000055; float:right;">'+text.substr(text.indexOf('('))+'</div></div>';
		}
		span.innerHTML = text;
		sublevel.appendChild(span);
		var action = sublevels[o].getAttribute('action');
		if (action && action!='' && window[action]) addEvent(sublevel,'mousedown',window[action]);
		var separator = sublevels[o].getAttribute('separator');
		if (separator && separator =='true') sublevel.style.borderTop = '1px solid #dddddd';
		var disabled = sublevels[o].getAttribute('disabled');
		if (disabled && disabled =='true') sublevel.className += " sfg_menu_disabled";
		var id = sublevels[o].getAttribute('id');
		if (id && id != '') sublevel.id = id;
		sublevelPan.appendChild(sublevel);
	}
}

function check_stuck(obj) {
	obj.style.paddingLeft='0px';
	var checkbox = document.createElement('input');
	checkbox.type = 'checkbox';
	checkbox.disabled = true;
	obj.appendChild(checkbox);
	if (document.stuckEffect && document.stuckEffect == true) checkbox.checked = true;
}

function stuck_effect_click(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	while (obj.tagName.toLowerCase() != 'div') obj = obj.parentNode;
	if (!document.stuckEffect) document.stuckEffect = false;
	document.stuckEffect = !document.stuckEffect;
	obj.getElementsByTagName('input')[0].checked = document.stuckEffect;
}

function check_bind(obj) {
	obj.style.paddingLeft='0px';
	var checkbox = document.createElement('input');
	checkbox.type = 'checkbox';
	checkbox.disabled = true;
	obj.appendChild(checkbox);
	if (document.bindEffect && document.bindEffect == true) checkbox.checked = true;
}

function bind_click(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	while (obj.tagName.toLowerCase() != 'div') obj = obj.parentNode;
	if (!document.bindEffect) document.bindEffect = false;
	document.bindEffect = !document.bindEffect;
	obj.getElementsByTagName('input')[0].checked = document.bindEffect;
}

function check_grid(obj) {
	obj.style.paddingLeft='0px';
	var checkbox = document.createElement('input');
	checkbox.type = 'checkbox';
	checkbox.disabled = true;
	obj.appendChild(checkbox);
	if (document.showGrid && document.showGrid == true) checkbox.checked = true;
}

function grid_click(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	while (obj.tagName.toLowerCase() != 'div') obj = obj.parentNode;
	if (!document.showGrid) document.showGrid = false;
	document.showGrid = !document.showGrid;
	obj.getElementsByTagName('input')[0].checked = document.showGrid;
	updateGrid();
}

function check_list(obj) {
	obj.style.paddingLeft='0px';
	var checkbox = document.createElement('input');
	checkbox.type = 'checkbox';
	checkbox.disabled = true;
	obj.appendChild(checkbox);
	if (document.showList && document.showList == true) checkbox.checked = true;
}

function list_click(ev) {
	if (ev) {
		if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
		while (obj.tagName.toLowerCase() != 'div') obj = obj.parentNode;
	} else {
		var items = document.getElementById('sfg_menu').getElementsByTagName('div');
		for (i=items.length-1; i>=0; i--) if (items[i].innerHTML.indexOf(getTranslation('Show Elements List'))>-1) { obj = items[i]; break; }
	}
	if (!document.showList) document.showList = false;
	document.showList = !document.showList;
	obj.getElementsByTagName('input')[0].checked = document.showList;
	if (document.showList) showAllElementsList(); else hideList();
	propListResizer();
}

function check_props(obj) {
	obj.style.paddingLeft='0px';
	var checkbox = document.createElement('input');
	checkbox.type = 'checkbox';
	checkbox.disabled = true;
	obj.appendChild(checkbox);
	if (document.showProps && document.showProps == true) checkbox.checked = true;
}

function props_click(ev) {
	if (ev) {
		if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
		while (obj.tagName.toLowerCase() != 'div') obj = obj.parentNode;
	} else {
		var lng = lang['Show Properties'] ? lang['Show Properties'] : 'Show Properties';
		var items = document.getElementById('sfg_menu').getElementsByTagName('div');
		for (i=items.length-1; i>=0; i--) if (items[i].innerHTML.indexOf(lng)>-1) { obj = items[i]; break; }
	}
	document.showProps = !document.showProps;
	obj.getElementsByTagName('input')[0].checked = document.showProps;
	if (document.showProps) showProps(); else hideProps();
	propListResizer();
}

function increase_grid_click(ev) {
	if (document.gridSize < 20) document.gridSize += 5;
	updateGrid();
}

function decrease_grid_click(ev) {
	if (document.gridSize > 5) document.gridSize -= 5;
	updateGrid();
}

function openHelpTopics() {
	oNewWin = window.open('https://www.itoris.com/magento-2-form-builder.html');
}

function openForum() {
	oNewWin = window.open('http://forum.itoris.com');
}

function contactIToris() {
	oNewWin = window.open('https://www.itoris.com/contact-us.html');
}

function exitForm() {
	if (!confirm(getTranslation('Are you sure want to exit the form?'))) return;
	document.location = document.backListUrl;
}

function aboutSFG() {
	showMask();
	var div = document.createElement('div');
	div.id = 'over_mask6';
	var div2 = document.createElement('div');
	div2.id = 'over_mask_header';
	div2.innerHTML = '<span>'+getTranslation('About SFG')+'</span>';
	var img = document.createElement('img');
	img.src = document.adminURL+'/images/close.png';
	img.id = 'over_maskClose';
	img.title = getTranslation('Close this Bar');
	addEvent(img, 'mousedown', closeAboutSFG);
	div2.appendChild(img);
	div.appendChild(div2);
	document.body.appendChild(div);
	var div2 = document.createElement('div');
	div2.id = 'propertiesContainer';
	div.appendChild(div2);
	div2.style.width = '100%';//div2.parentNode.offsetWidth - document.brc + 'px';
	div2.style.height = '300px';//div2.parentNode.offsetHeight - document.getElementById('over_mask_header').offsetHeight - document.brc + 'px';
	var version = document.sfgVersion;
	div2.innerHTML = '<h1 style="margin-left:10px">SmartFormer Gold (SFG) v'+version+'</h1>';
	var xmlhttp = HTTPRequest(document.adminURL+'/defaults/about.html', false, null);
	div2.innerHTML += xmlhttp.responseText;
}

function closeAboutSFG() {
	var div = document.getElementById('over_mask6');
	if (div) div.parentNode.removeChild(div);
	hideMask();
	hideLoadingImage();
}