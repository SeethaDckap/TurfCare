document.msdn = Array();

SFG_MSDN = function (attributes, styles, events, params) {
	this.attributes = Array();
	if (attributes)	{
		var tmp = attributes.text ? attributes.text.replace(/^\s+|\s+$/g,"") : attributes.textContent.replace(/^\s+|\s+$/g,"");
		tmp = tmp.split(',');
		for(p=0; p<tmp.length; p++) {
			tmp[p] = tmp[p].toLowerCase().replace(/^\s+|\s+$/g,"") 
			if (tmp[p] != '') this.attributes[this.attributes.length] = tmp[p];
		}
	}
	this.styles = Array();
	if (styles)	{
		var tmp = styles.text ? styles.text.replace(/^\s+|\s+$/g,"") : styles.textContent.replace(/^\s+|\s+$/g,"");
		tmp = tmp.split(',');
		for(p=0; p<tmp.length; p++) {
			tmp[p] = tmp[p].toLowerCase().replace(/^\s+|\s+$/g,"") 
			if (tmp[p] != '') this.styles[this.styles.length] = tmp[p];
		}
	}
	this.events = Array();
	if (events)	{
		var tmp = events.text ? events.text.replace(/^\s+|\s+$/g,"") : events.textContent.replace(/^\s+|\s+$/g,"");
		tmp = tmp.split(',');
		for(p=0; p<tmp.length; p++) {
			tmp[p] = tmp[p].toLowerCase().replace(/^\s+|\s+$/g,"") 
			if (tmp[p] != '') this.events[this.events.length] = tmp[p];
		}
	}
	this.params = Array();
	if (params)	{
		var tmp = params.text ? params.text.replace(/^\s+|\s+$/g,"") : params.textContent.replace(/^\s+|\s+$/g,"");
		tmp = tmp.split(',');
		for(p=0; p<tmp.length; p++) {
			tmp[p] = tmp[p].toLowerCase().replace(/^\s+|\s+$/g,"") 
			if (tmp[p] != '') this.params[this.params.length] = tmp[p];
		}
	}
}

SFG_MSDNProp = function (obj) {
	this.type = obj.getAttribute('type');
	this.list = obj.getAttribute('list');
	this.desc = obj.text ? obj.text.replace(/^\s+|\s+$/g,"") : obj.textContent.replace(/^\s+|\s+$/g,"");
}

SFG_MSDNDesc = function (attributes, styles, events, params) {
	this.attributes = Array();
	for (i=0; i<attributes.childNodes.length; i++) if (attributes.childNodes[i].tagName) this.attributes[attributes.childNodes[i].tagName.toLowerCase().replace(/^\s+|\s+$/g,"")] = new SFG_MSDNProp(attributes.childNodes[i]);
	this.styles = Array();
	for (i=0; i<styles.childNodes.length; i++) if (styles.childNodes[i].tagName) this.styles[styles.childNodes[i].tagName.toLowerCase().replace(/^\s+|\s+$/g,"")] = new SFG_MSDNProp(styles.childNodes[i]);
	this.events = Array();
	for (i=0; i<events.childNodes.length; i++) if (events.childNodes[i].tagName) this.events[events.childNodes[i].tagName.toLowerCase().replace(/^\s+|\s+$/g,"")] = new SFG_MSDNProp(events.childNodes[i]);
	this.params = Array();
	for (i=0; i<params.childNodes.length; i++) if (params.childNodes[i].tagName) this.params[params.childNodes[i].tagName.toLowerCase().replace(/^\s+|\s+$/g,"")] = new SFG_MSDNProp(params.childNodes[i]);
}

var msdnXML = loadXMLFile(document.adminURL+'/modules/msdn/msdn.xml');
var items = msdnXML.documentElement.getElementsByTagName('element');

for (i=0; i<items.length; i++) {
	var type = items[i].getAttribute('type');
	document.msdn[type] = new SFG_MSDN(items[i].getElementsByTagName('attributes')[0],items[i].getElementsByTagName('styles')[0],items[i].getElementsByTagName('events')[0],items[i].getElementsByTagName('params')[0]);
}

document.msdnDesc = new SFG_MSDNDesc (
	msdnXML.documentElement.getElementsByTagName('clarification')[0].getElementsByTagName('attributes')[0],
	msdnXML.documentElement.getElementsByTagName('clarification')[0].getElementsByTagName('styles')[0],
	msdnXML.documentElement.getElementsByTagName('clarification')[0].getElementsByTagName('events')[0],
	msdnXML.documentElement.getElementsByTagName('clarification')[0].getElementsByTagName('params')[0]
);

function showProps() {
	if (!document.showProps) return;
	if (document.selectedElements.length != 1) { hideProps(); return; }
	var obj = document.selectedElements[0];
	var center = obj.object.offsetLeft + Math.floor(obj.object.offsetWidth/2) - document.getElementById('sfg_container').scrollLeft;
	if (!document.getElementById('elementProperties')) document.body.appendChild(document.elementProps);
	if (center > document.getElementById('sfg_container').offsetWidth/2) document.elementProps.style.left = document.mostLeft + 2 + 'px';
		else document.elementProps.style.left = document.mostLeft + document.getElementById('sfg_container').offsetWidth - document.elementProps.offsetWidth - 20 + 'px';
	propListResizer();
	document.activeObject = obj;
	renderProps();
}

function renderHelper(arr, arr2, arr3, obj) {
	for (i=0; i<arr.length; i++) {
		if (arr[i]=='src' && obj.getProp(obj.params,'captcha-type')) continue;
		var div = document.createElement('div');
		div.className = 'property';
		if (arr2 == document.msdnDesc.params) div.className = 'propertyX';
		var div2 = document.createElement('div');
		div2.className = 'propertyName';
		div2.innerHTML = arr[i];
		if (arr[i] == 'name') div2.style.color='#ff0000';
		div.appendChild(div2);
		var div2 = document.createElement('div');
		div2.className = 'propertyControl';
		var prop = obj.getProp(arr3, arr[i]);
		if (arr2[arr[i]] && arr2[arr[i]].type == 'bool') {
			var control = document.createElement('input');
			control.className = 'propertyCheckbox';
			control.type='checkbox';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'list') {
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = arr2[arr[i]].list ? arr2[arr[i]].list.split(',') : Array();
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=0; o<values.length; o++) if (values[o]!='') {
				var option = document.createElement('option');
				option.value = values[o];
				option.innerHTML = values[o];
				if (prop && prop == values[o]) option.selected = true;
				control.appendChild(option);
			}
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'validation-list') {
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array();
			for (o=0; o<document.validators.length; o++) values[values.length] = document.validators[o].alias;
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=2; o<values.length; o++) if (values[o]!='' && values[o]!='Check identical') {
				var option = document.createElement('option');
				option.value = values[o];
				option.innerHTML = values[o];
				if (prop && prop.toLowerCase() == values[o].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'on-click-action') {
			if (obj.getProp(obj.params,'captcha-type')) continue;
			var actions = Array(getTranslation('do nothing'),getTranslation('Submit the form'),getTranslation('Show Calendar'),getTranslation('Generate PDF'),getTranslation('Run custom JS line'));
			var control = document.createElement('select');
			control.className = 'propertySelect';
			for (p=0; p<actions.length; p++) {
				var option = document.createElement('option');
				option.value = p;
				option.innerHTML = actions[p];
				if (prop && prop-0 == p) option.selected = true;
				control.appendChild(option);
			}
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'date-input-field') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array();
			for (p=0; p<document.currentPageElements.length; p++) {
				var name = document.currentPageElements[p].getProp(document.currentPageElements[p].attributes, 'name');
				if (name && name != '' && document.currentPageElements[p] != obj) values[values.length] = Array(name, document.currentPageElements[p].alias);
			}
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o][0];
				if (values[o][1] == '') values[o][1]=getTranslation('no SFG Alias');
				option.innerHTML = values[o][0]+' ('+values[o][1]+')';
				if (prop && prop.toLowerCase() == values[o][0].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 2) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'date-format') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array(getTranslation('mm/dd/y'),getTranslation('mm/dd/yy'),getTranslation('dd/mm/y'),getTranslation('dd/mm/yy'),getTranslation('y-mm-dd'));
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o];
				option.innerHTML = values[o];
				if (prop && prop.toLowerCase() == values[o].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 2) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'custom-js-line') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('input');
			prop = obj.getProp(obj.events,'onclick');
			control.className = 'propertyInput';
			if (!tmp || tmp-0 != 4) div.style.display = 'none';
			control.type='text';
			if (prop) control.value = prop;
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'pdf-page-size') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = ['A4', 'A3', 'A2', 'Letter', 'Legal', 'Ledger', 'Tabloid', 'Executive', 'Folio'];
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o];
				option.innerHTML = values[o];
				if (prop && prop.toLowerCase() == values[o].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 3) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'pdf-orientation') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = ['Portrait', 'Landscape'];
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o];
				option.innerHTML = values[o];
				if (prop && prop.toLowerCase() == values[o].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 3) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'save-data') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('input');
			control.className = 'propertyCheckbox';
			if (!tmp || tmp-0 != 1) div.style.display = 'none';
			control.type='checkbox';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'disable-validation') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('input');
			control.className = 'propertyCheckbox';
			if (!tmp || tmp-0 != 1) div.style.display = 'none';
			control.type='checkbox';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'after-submit') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var actions = Array(getTranslation('open next page'),getTranslation('open previous page'),getTranslation('open selected page'),getTranslation('stay on current page'),getTranslation('redirect to URL'));
			var control = document.createElement('select');
			control.className = 'propertySelect';
			for (p=0; p<actions.length; p++) {
				var option = document.createElement('option');
				option.value = p;
				option.innerHTML = actions[p];
				if (prop && prop-0 == p) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 1) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'selected-page') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var tmp2 = obj.getProp(obj.params,'after-submit');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			for (o=0; o<document.sfgPages.length; o++) {
				var option = document.createElement('option');
				option.value = o;
				option.innerHTML = document.sfgPages[o];
				if (prop && prop-0 == o) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 1 || !tmp2 || tmp2-0 != 2) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'redirect-url') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var tmp2 = obj.getProp(obj.params,'after-submit');
			var control = document.createElement('input');
			control.className = 'propertyInput';
			control.type='text';
			if (prop) control.value = prop;
			if (!tmp || tmp-0 != 1 || !tmp2 || tmp2-0 != 4) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'email-to-admin') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('input');
			control.className = 'propertyCheckbox';
			if (!tmp || tmp-0 != 1) div.style.display = 'none';
			control.type='checkbox';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'admin-email') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var tmp2 = obj.getProp(obj.params,'email-to-admin');
			var control = document.createElement('input');
			control.className = 'propertyInput';
			control.type='text';
			if (prop) control.value = prop;
			if (!tmp || tmp-0 != 1 || !tmp2 || tmp2 != true) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'admin-email-template') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var tmp2 = obj.getProp(obj.params,'email-to-admin');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array();
			for (p=0; p<document.emailTemplates.length; p++) values[values.length] = Array(document.emailTemplates[p].id, document.emailTemplates[p].name);
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o][0];
				option.innerHTML = values[o][1]+' ('+values[o][0]+')';
				if (prop && prop-0 == values[o][0]-0) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 1 || !tmp2 || tmp2 != true) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'email-to-user') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var control = document.createElement('input');
			control.className = 'propertyCheckbox';
			if (!tmp || tmp-0 != 1) div.style.display = 'none';
			control.type='checkbox';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'user-email-template') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var tmp2 = obj.getProp(obj.params,'email-to-user');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array();
			for (p=0; p<document.emailTemplates.length; p++) values[values.length] = Array(document.emailTemplates[p].id, document.emailTemplates[p].name);
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o][0];
				option.innerHTML = values[o][1]+' ('+values[o][0]+')';
				if (prop && prop-0 == values[o][0]-0) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 1 || !tmp2 || tmp2 != true) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'user-email-addr-field') {
			var tmp = obj.getProp(obj.params,'on-click-action');
			var tmp2 = obj.getProp(obj.params,'email-to-user');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array();
			for (p=0; p<document.allElements.length; p++) if (document.allElements[p].getProp(document.allElements[p].attributes,'type')=='text') {
				var name = document.allElements[p].getProp(document.allElements[p].attributes, 'name');
				if (name && name != '') values[values.length] = Array(name, document.allElements[p].alias);
			}
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o][0];
				if (values[o][1] == '') values[o][1]=getTranslation('no SFG Alias');
				option.innerHTML = values[o][0]+' ('+values[o][1]+')';
				if (prop && prop.toLowerCase() == values[o][0].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
			if (!tmp || tmp-0 != 1 || !tmp2 || tmp2 != true) div.style.display = 'none';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'captcha-type') {
			if (!obj.getProp(obj.params,'captcha-type')) continue;
			var captchas = Array('Alikon Mod','Captcha Form','SecurImage');
			var control = document.createElement('select');
			control.className = 'propertySelect';
			for (p=0; p<captchas.length; p++) {
				var option = document.createElement('option');
				option.value = p;
				option.innerHTML = captchas[p];
				if (prop && prop-0 == p) option.selected = true;
				control.appendChild(option);
			}
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'captcha-length') {
			if (!obj.getProp(obj.params,'captcha-length')) continue;
			var control = document.createElement('select');
			control.className = 'propertySelect';
			for (p=2; p<8; p++) {
				var option = document.createElement('option');
				option.value = p;
				option.innerHTML = p+' chars';
				if (prop && prop-0 == p) option.selected = true;
				control.appendChild(option);
			}
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'captcha-symbols') {
			if (!obj.getProp(obj.params,'captcha-symbols')) continue;
			var control = document.createElement('input');
			control.className = 'propertyInput';
			control.type='text';
			if (prop) control.value = prop;
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'captcha-field') {
			if (!obj.getProp(obj.params,'captcha-type')) continue;
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array();
			for (p=0; p<document.currentPageElements.length; p++) {
				var name = document.currentPageElements[p].getProp(document.currentPageElements[p].attributes, 'name');
				if (name && name != '' && document.currentPageElements[p] != obj) values[values.length] = Array(name, document.currentPageElements[p].alias);
			}
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o][0];
				if (values[o][1] == '') values[o][1]=getTranslation('no SFG Alias');
				option.innerHTML = values[o][0]+' ('+values[o][1]+')';
				if (prop && prop.toLowerCase() == values[o][0].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'equal-to') {
			var control = document.createElement('select');
			control.className = 'propertySelect';
			var values = Array();
			for (p=0; p<document.currentPageElements.length; p++) {
				var name = document.currentPageElements[p].getProp(document.currentPageElements[p].attributes, 'name');
				if (name && name != '' && document.currentPageElements[p] != obj) values[values.length] = Array(name, document.currentPageElements[p].alias);
			}
			var option = document.createElement('option');
			option.value = '';
			option.innerHTML = '-- '+getTranslation('nothing selected')+' --';
			control.appendChild(option);
			for (o=0; o<values.length; o++) {
				var option = document.createElement('option');
				option.value = values[o][0];
				if (values[o][1] == '') values[o][1]=getTranslation('no SFG Alias');
				option.innerHTML = values[o][0]+' ('+values[o][1]+')';
				if (prop && prop.toLowerCase() == values[o][0].toLowerCase()) option.selected = true;
				control.appendChild(option);
			}
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'select-list') {
			var control = document.createElement('textarea');
			control.className = 'propertyTextArea';
			control.value = (obj.content) ? obj.content : '';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'inner-content') {
			var control = document.createElement('textarea');
			control.className = 'propertyTextArea';
			control.value = (obj.content) ? obj.content : '';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'inner-html') {
			var control = document.createElement('textarea');
			control.className = 'propertyTextArea';
			control.value = (obj.content) ? obj.content : '';
		} else if (arr2[arr[i]] && arr2[arr[i]].type == 'rich-text') {
			var control = document.createElement('textarea');
			control.className = 'propertyTextArea';
			control.value = (obj.content) ? obj.content : '';
		} else {
			var control = document.createElement('input');
			control.className = 'propertyInput';
			control.type='text';
			if (prop) control.value = prop;
		}
		addEvent(control, 'change', applyProp);
		addEvent(control, 'blur', applyProp);
		addEvent(control, 'keyup', applyProp);
		div2.appendChild(control);
		div.appendChild(div2);
		if (arr2 != document.msdnDesc.params || arr2[arr[i]].type == 'select-list' || arr2[arr[i]].type == 'inner-content' || arr2[arr[i]].type == 'inner-html' || arr2[arr[i]].type == 'rich-text' || arr2[arr[i]].type == 'hidden-if' || arr2[arr[i]].type == 'canvas-pen-size' || arr2[arr[i]].type == 'color') {
			var div2 = document.createElement('div');
			div2.className = 'phpIconContainer';
			var img = document.createElement('img');
			img.className = 'phpIcon';
			if (arr2 == document.msdnDesc.params && (arr2[arr[i]].type == 'select-list' || arr2[arr[i]].type == 'inner-content' || arr2[arr[i]].type == 'inner-html' || arr2[arr[i]].type == 'rich-text' || arr2[arr[i]].type == 'hidden-if' || arr2[arr[i]].type == 'canvas-pen-size' || arr2[arr[i]].type == 'color')) var php=obj.contentPHP;
				else var php = obj.getPHP(arr3, arr[i]);
			addEvent(img, 'mousedown', editPropertyPHP);
			if (php && php.replace(/^\s+|\s+$/g,"") != '') {
				img.title = getTranslation('Edit PHP script');
				img.src = document.adminURL+'/modules/msdn/editphp.gif';
			} else {
				img.title = getTranslation('Add PHP script');
				img.src = document.adminURL+'/modules/msdn/addphp.gif';
			}
			div2.appendChild(img);
			div.appendChild(div2);
		}
		if (arr2[arr[i]] && arr2[arr[i]].type == 'inner-html') {
			var div2 = document.createElement('div');
			div2.className = 'imageContainer';
			var img = document.createElement('img');
			img.className = 'imageIcon';
			img.title = getTranslation('Show HTML editor');
			img.src = document.adminURL+'/modules/msdn/enlarge.gif';
			addEvent(img, 'mousedown', editPropertyHTML);
			div2.appendChild(img);
			div.appendChild(div2);
		}
		if (arr2[arr[i]] && arr2[arr[i]].type == 'rich-text') {
			var div2 = document.createElement('div');
			div2.className = 'imageContainer';
			var img = document.createElement('img');
			img.className = 'imageIcon';
			img.title = getTranslation('Show WYSIWYG');
			img.src = document.adminURL+'/modules/msdn/enlarge.gif';
			addEvent(img, 'mousedown', showPropertyWYSIWYG);
			div2.appendChild(img);
			div.appendChild(div2);
		}
		if (arr2[arr[i]] && arr2[arr[i]].type == 'color') {
			var div2 = document.createElement('div');
			div2.className = 'propertyColorSelect';
			if (prop) div2.style.backgroundColor = prop;
			div2.title = getTranslation('Select Color');
			addEvent(div2, 'mousedown', showColorSelector);
			div.appendChild(div2);
		}
		if (arr2[arr[i]] && arr2[arr[i]].type == 'image') {
			var div2 = document.createElement('div');
			div2.className = 'imageContainer';
			var img = document.createElement('img');
			img.className = 'imageIcon';
			img.title = getTranslation('Select Image');
			img.src = document.adminURL+'/modules/msdn/image.gif';
			addEvent(img, 'mousedown', showImageSelector);
			div2.appendChild(img);
			div.appendChild(div2);
		}
		document.getElementById('attrList').appendChild(div);
		/*if (arr2[arr[i]] && arr2[arr[i]].type == 'bool')*/ if (prop && prop == true) control.checked = true;
		if (control.type && control.type == 'checkbox') addEvent(control, 'click', applyProp);
	}
}

function renderProps() {
	var obj = document.activeObject;
	var nodes = document.getElementById('attrList').childNodes;
	for (i=0; i<nodes; i++) document.getElementById('attrList').removeChild(nodes[i]);
	document.getElementById('attrList').innerHTML = '';
	var type = obj.tag;
	var type2 = obj.getProp(obj.attributes,'type');
	if (type2) type +=','+type2;
	var arr = Array();
	var arr2 = Array();
	var arr3 = Array();
	if (document.msdn[type]) {
		if (document.propertiesTab == 1) { arr = document.msdn[type].attributes; arr2 = document.msdnDesc.attributes; arr3 = obj.attributes; }
		if (document.propertiesTab == 2) { arr = document.msdn[type].styles; arr2 = document.msdnDesc.styles; arr3 = obj.styles; }
		if (document.propertiesTab == 3) { arr = document.msdn[type].events; arr2 = document.msdnDesc.events; arr3 = obj.events; }
		if (document.propertiesTab == 0) {
			var div = document.createElement('div');
			div.className = 'property';
			div.style.backgroundColor = '#ffeeee';
			var div2 = document.createElement('div');
			div2.className = 'propertyName';
			div2.innerHTML = 'SFG Alias';
			div2.style.color = '#ff0000';
			div.appendChild(div2);
			var div2 = document.createElement('div');
			div2.className = 'propertyControl';
			var prop = obj.alias;
			var control = document.createElement('input');
			control.className = 'propertyInput';
			control.type='text';
			if (prop) control.value = prop;
			addEvent(control, 'change', applyProp);
			addEvent(control, 'blur', applyProp);
			addEvent(control, 'keyup', applyProp);
			div2.appendChild(control);
			div.appendChild(div2);
			document.getElementById('attrList').appendChild(div);
			
			arr = Array(/*'left','top','width','height',*/'font-family','font-size','color','border-width','border-color','border-style','background-color','background-image');
			for (i=arr.length-1; i>=0; i--) if (!in_array(arr[i],document.msdn[type].styles)) arr.splice(i,1);
			arr2 = document.msdnDesc.styles;
			arr3 = obj.styles;
			renderHelper(arr, arr2, arr3, obj);
			
			arr = Array('class','id','name','src','checked','value','href');
			for (i=arr.length-1; i>=0; i--) if (!in_array(arr[i],document.msdn[type].attributes)) arr.splice(i,1);
			arr2 = document.msdnDesc.attributes;
			arr3 = obj.attributes;
			renderHelper(arr, arr2, arr3, obj);
			
			arr = document.msdn[type].params; arr2 = document.msdnDesc.params; arr3 = obj.params;
		}
	}
	renderHelper(arr, arr2, arr3, obj);
}

function getPropertyDiv(obj, propName) {
	var divs = obj.parentNode.parentNode.parentNode.getElementsByTagName('div');
	for (i=0; i<divs.length; i++) if (divs[i].innerHTML == propName) return divs[i].parentNode;
}

function hasPhp(obj) {
	for (var i = 0; i < obj.attributes.length; i++) {
		if (typeof(obj.attributes[i][2]) != 'undefined' && obj.attributes[i][2] != '') {
			return true;
		}
	}
	return false;
}

function applyProp(ev) {

    if (window.event) {
		obj=window.event.srcElement;
	} else {
		obj=ev.currentTarget;
	}
	
	var prop = obj.parentNode.parentNode.getElementsByTagName('div')[0].innerHTML;
    
    if (prop == 'name') {
        obj.value = obj.value.toLowerCase();
    }

	var value = obj.value;
	
	if (prop == 'SFG Alias') { 
		document.activeObject.alias = value; 
		return; 
	}    
	
	if (obj.type && obj.type.toLowerCase() == 'checkbox') {
		if (obj.checked) {
			value = true; 
		} else { 
			value = '';
		}
	}
	
	var type = document.activeObject.tag;
	
	var type2 = document.activeObject.getProp(document.activeObject.attributes,'type');
	if (type2) type +=','+type2;
	if (document.propertiesTab == 1 || in_array(prop,document.msdn[type].attributes)) {
	    document.activeObject.updateProp(document.activeObject.attributes, prop, value, document.activeObject.getPHP(document.activeObject.attributes, prop));
		var prop2 = prop;
		if (prop == 'class') prop2 = 'className';
		if (obj.type && obj.type.toLowerCase() == 'checkbox' && value=='') document.activeObject.object.setAttribute(prop2, false);
		document.activeObject.object.removeAttribute(prop2, 0);
		if (document.activeObject.getProp(document.activeObject.attributes, prop)) {
		
			var update = prop2 != 'disabled' 
					  || (prop2 == 'disabled' && value == '')
					  || (prop2 == 'disabled' && value == true && !hasPhp(document.activeObject));
					  
			if (update) {
				document.activeObject.object.setAttribute(prop2, value);
			}
			
		}
	} else if (document.propertiesTab == 2 || in_array(prop,document.msdn[type].styles)) {
	    if (document.msdnDesc.styles[prop].type == 'pos' && parseInt(value) == value) { value += 'px'; obj.value = value; setCaretPosition(obj, value.length-2); }
		document.activeObject.updateProp(document.activeObject.styles, prop, value, document.activeObject.getPHP(document.activeObject.styles, prop));
		document.activeObject.updateStyles();
	} else if (document.propertiesTab == 3) {
		document.activeObject.updateProp(document.activeObject.events, prop, value, document.activeObject.getPHP(document.activeObject.events, prop));
	} else {
		if (prop == 'on-click-action') {
			var custom_js_line = getPropertyDiv(obj, 'custom-js-line');
			var save_data = getPropertyDiv(obj, 'save-data');
			var disable_validation = getPropertyDiv(obj, 'disable-validation');
			var after_submit = getPropertyDiv(obj, 'after-submit');
			var selected_page = getPropertyDiv(obj, 'selected-page');
			var date_input_field = getPropertyDiv(obj, 'date-input-field');
			var date_format= getPropertyDiv(obj, 'date-format');
			var email_to_admin = getPropertyDiv(obj, 'email-to-admin');
			var admin_email = getPropertyDiv(obj, 'admin-email');
			var admin_email_template = getPropertyDiv(obj, 'admin-email-template');
			var email_to_user = getPropertyDiv(obj, 'email-to-user');
			var user_email_template = getPropertyDiv(obj, 'user-email-template');
			var user_email_addr_field = getPropertyDiv(obj, 'user-email-addr-field');
            var pdf_page_size = getPropertyDiv(obj, 'pdf-page-size');
            var pdf_orientation = getPropertyDiv(obj, 'pdf-orientation');
			if (custom_js_line) if (value-0 == 4) custom_js_line.style.display = 'block'; else custom_js_line.style.display = 'none';
            if (pdf_page_size) pdf_page_size.style.display = (value-0 == 3) ? 'block' : 'none';
            if (pdf_orientation) pdf_orientation.style.display = (value-0 == 3) ? 'block' : 'none';
			if (save_data) if (value-0 == 1) {
				save_data.style.display = 'block';
				disable_validation.style.display = 'block';
				after_submit.style.display = 'block';
				email_to_admin.style.display = 'block';
				email_to_user.style.display = 'block';
				var tmp = document.activeObject.getProp(document.activeObject.params,'after-submit');
				if (tmp && tmp == 2) selected_page.style.display = 'block'; else selected_page.style.display = 'none';
				var tmp = document.activeObject.getProp(document.activeObject.params,'email-to-admin');
				if (tmp && tmp == true) {
					admin_email.style.display = 'block';
					admin_email_template.style.display = 'block';
				} else {
					admin_email_template.style.display = 'none';
					admin_email.style.display = 'none';
				}
				var tmp = document.activeObject.getProp(document.activeObject.params,'email-to-user');
				if (tmp && tmp == true) {
					user_email_template.style.display = 'block';
					user_email_addr_field.style.display = 'block';
				} else {
					user_email_template.style.display = 'none';
					user_email_addr_field.style.display = 'none';
				}
			} else {
				save_data.style.display = 'none';
				disable_validation.style.display = 'none';
				after_submit.style.display = 'none';
				selected_page.style.display = 'none';
				email_to_admin.style.display = 'none';
				admin_email_template.style.display = 'none';
				admin_email.style.display = 'none';
				email_to_user.style.display = 'none';
				user_email_template.style.display = 'none';
				user_email_addr_field.style.display = 'none';
			}
			if (date_input_field) if (value-0 == 2) {
				date_input_field.style.display = 'block';
				date_format.style.display = 'block';
			} else {
				date_input_field.style.display = 'none';
				date_format.style.display = 'none';
			}
		}
		if (prop == 'after-submit') {
			var selected_page = getPropertyDiv(obj, 'selected-page');
			var redirect_url = getPropertyDiv(obj, 'redirect-url');
			if (selected_page) if (value-0 == 2) selected_page.style.display = 'block'; else selected_page.style.display = 'none';
			if (redirect_url) if (value-0 == 4) redirect_url.style.display = 'block'; else redirect_url.style.display = 'none';
		}
		if (prop == 'email-to-admin') {
			var admin_email_template = getPropertyDiv(obj, 'admin-email-template');
			var admin_email = getPropertyDiv(obj, 'admin-email');
			if (admin_email_template) if (value == true) admin_email_template.style.display = 'block'; else admin_email_template.style.display = 'none';
			if (admin_email) if (value == true) admin_email.style.display = 'block'; else admin_email.style.display = 'none';
		}
		if (prop == 'email-to-user') {
			var user_email_template = getPropertyDiv(obj, 'user-email-template');
			var user_email_addr_field = getPropertyDiv(obj, 'user-email-addr-field');
			if (user_email_template) if (value == true) user_email_template.style.display = 'block'; else user_email_template.style.display = 'none';
			if (user_email_addr_field) if (value == true) user_email_addr_field.style.display = 'block'; else user_email_addr_field.style.display = 'none';
		}
		if (prop == 'select-list' || prop == 'inner-content' || prop == 'inner-html' || prop == 'rich-text') {
			document.activeObject.content = value;
			document.activeObject.applyContent();
		} else if (prop == 'custom-js-line') document.activeObject.updateProp(document.activeObject.events, 'onclick', value, document.activeObject.getPHP(document.activeObject.events, 'onclick'));
			else document.activeObject.updateProp(document.activeObject.params, prop, value, document.activeObject.getPHP(document.activeObject.params, prop));
		if (prop == 'captcha-type') {
			document.activeObject.updateProp(document.activeObject.attributes,'src','{captcha'+value+'}',document.activeObject.getPHP(document.activeObject.attributes, 'src'));
			document.activeObject.updateAttributes();
		}
	}
	renderElementsList();
}

function hideProps() {
	if (document.getElementById('elementProperties')) document.body.removeChild(document.elementProps);
}

function closeProps(ev) {
	props_click(null);
	hideProps();
	hideLoadingImage();
	if (!document.propertiesCloseAlert) {
		document.propertiesCloseAlert = true;
		var lng = getTranslation('Properties Bar is disabled.\nFor enabling it again please follow:\nOptions -> Show Properties');
		alert(lang[lng] ? lang[lng] : lng);
	}
}

function propertiesTabClick(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	while (obj.tagName.toLowerCase() != 'div') obj = obj.parentNode;
	var id = parseInt(obj.id.substr(13));
	if (id == document.propertiesTab) return;
	document.getElementById('propertiesTab'+document.propertiesTab).className = 'propertiesTab';
	document.getElementById('propertiesTab'+id).className = 'propertiesTabSelected';
	document.propertiesTab = id;
	renderProps();
}

function setCaretPosition(ctrl, pos){
	if(ctrl.setSelectionRange){
		ctrl.focus();
		ctrl.setSelectionRange(pos,pos);
	} else if (ctrl.createTextRange){
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
}

document.elementProps = document.createElement('div');
document.elementProps.id = 'elementProperties';
var div = document.createElement('div');
div.id = 'propertiesHeader';
div.innerHTML = '<div style="float:left">'+getTranslation('Properties list')+'</div>';
var img = document.createElement('img');
img.src = document.adminURL+'/modules/msdn/cross.gif';
img.id = 'propertiesClose';
img.title = getTranslation('Close this Bar');
addEvent(img, 'mousedown', closeProps);
div.appendChild(img);
document.elementProps.appendChild(div);
var div = document.createElement('div');
div.id = 'propertiesTabList';

var div2 = document.createElement('div');
div2.className = 'propertiesTabSelected';
div2.id = 'propertiesTab0';
addEvent(div2, 'mousedown', propertiesTabClick);
div2.innerHTML = lang['Common'] ? lang['Common'] : 'Common';
div.appendChild(div2);

var div2 = document.createElement('div');
div2.className = 'propertiesTab';
div2.id = 'propertiesTab1';
addEvent(div2, 'mousedown', propertiesTabClick);
div2.innerHTML = lang['Attributes'] ? lang['Attributes'] : 'Attributes';
div.appendChild(div2);

var div2 = document.createElement('div');
div2.className = 'propertiesTab';
div2.id = 'propertiesTab2';
addEvent(div2, 'mousedown', propertiesTabClick);
div2.innerHTML = lang['Styles'] ? lang['Styles'] : 'Styles';
div.appendChild(div2);

var div2 = document.createElement('div');
div2.className = 'propertiesTab';
div2.id = 'propertiesTab3';
addEvent(div2, 'mousedown', propertiesTabClick);
div2.innerHTML = lang['Events'] ? lang['Events'] : 'Events';
div.appendChild(div2);

document.elementProps.appendChild(div);

var div = document.createElement('div');
div.id = 'attrList';
document.elementProps.appendChild(div);

document.propertiesTab = 0;
