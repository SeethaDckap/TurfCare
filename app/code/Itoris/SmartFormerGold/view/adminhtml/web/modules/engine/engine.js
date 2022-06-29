SFG_Element = function (proto) {
	this.proto = proto;
	this.tag = proto.tag;
	this.alias = '';
	this.attributes = Array();
	for(o=0; o<proto.attributes.length; o++) this.attributes[o] = Array (proto.attributes[o][0], proto.attributes[o][1], proto.attributes[o][2]);
	this.styles = Array();
	for(o=0; o<proto.styles.length; o++) this.styles[o] = Array (proto.styles[o][0], proto.styles[o][1], proto.styles[o][2]);
	this.events = Array();
	for(o=0; o<proto.events.length; o++) this.events[o] = Array (proto.events[o][0], proto.events[o][1], proto.events[o][2]);
	this.params = Array();
	for(o=0; o<proto.params.length; o++) this.params[o] = Array (proto.params[o][0], proto.params[o][1], proto.params[o][2]);
	this.content = proto.content;
	this.contentPHP = proto.contentPHP;
	this.page = document.currentPage;
	this.selected = false;
	this.object = document.createElement(this.tag);
	this.object.parent = this;
	addEvent(this.object, 'mousemove', areaMove);
	addEvent(this.object, 'mouseover', elementOver);
	addEvent(this.object, 'mouseout', elementOut);
	addEvent(this.object, 'mousedown', elementDown);
	addEvent(this.object, 'mouseup', elementUp);
	addEvent(this.object, 'keydown', elementKeyDown);
	addEvent(this.object, 'drag', stopEvent);
	addEvent(this.object, 'click', stopEvent);
	
	this.applyContent = function () {
		if (this.tag.toLowerCase() == 'select') {
			while (this.object.options.length > 0) this.object.remove(0);
			var parts = this.content.split('\n');
			for (i=0; i<parts.length; i++) {
				parts[i] = parts[i].replace(/^\s+|\s+$/g,"");
				var val = null, txt = null, sel = false;
				if (parts[i].substring(0,1)=='*') {
					sel = true;
					parts[i] = parts[i].substr(1);
				}
				var p = parts[i].indexOf('|');
				if (p == -1) {
					val = parts[i];
					txt = parts[i];
				} else {
					var val = parts[i].substring(0,parts[i].indexOf('|')).replace(/^\s+|\s+$/g,"");
					var txt = parts[i].substr(parts[i].indexOf('|')+1).replace(/^\s+|\s+$/g,"");
				}
				if (txt != '' || val) {
					var option = document.createElement('option');
					option.selected = sel;
					if (val) option.value = val;
					if (txt != '') option.innerHTML = txt;
					this.object.appendChild(option);
				}
			}			
		} else if (this.tag.toLowerCase() == 'textarea') this.object.value = this.content;
			else this.object.innerHTML = this.content;
	}
	
	this.updateStyles = function () {
		var s = 'position:absolute; ';
		for (i=0; i<this.styles.length; i++) if (this.styles[i][1] && this.styles[i][1]!='') s += this.styles[i][0] + ':' + this.styles[i][1] + '; ';
		this.object.style.cssText = s;
		if (this.selected) this.object.style.border = '1px solid blue';
	}
	
	this.updateAttributes = function () {
		for (i=0; i<this.attributes.length; i++) {
			try {
				this.object.setAttribute(this.attributes[i][0], this.attributes[i][1]);
			} catch (e) { }
			if (this.attributes[i][1]=='{captcha0}') this.object.setAttribute(this.attributes[i][0], document.adminURL+'/images/alikon-captcha.png');
			if (this.attributes[i][1]=='{captcha1}') this.object.setAttribute(this.attributes[i][0], document.adminURL+'/images/captcha-form-captcha.png');
			if (this.attributes[i][1]=='{captcha2}') this.object.setAttribute(this.attributes[i][0], document.adminURL+'/images/secur-image-captcha.png');
		}
	}
		
	this.unSelect = function () {
		if (this.selected) {
			this.selected = false;
			this.updateStyles();
			for (p=0; p<document.selectedElements.length; p++) if (document.selectedElements[p] == this) {
				document.selectedElements.splice(p,1);
				break;
			}
		}
	}
	
	this.select = function () {
		if (!this.selected) {
			this.selected = true;
			this.object.style.border = '1px solid blue';
			document.selectedElements[document.selectedElements.length] = this;
		}
	}
	
	this.clone = function () {
		var clone = new SFG_Element(this);
		return clone;
	}
	
	this.updateProp = function (arr, tag, value, php) {		
		for (o=0; o<arr.length; o++) {
			if (arr[o][0] == tag) {
				arr[o][1] = value;
				arr[o][2] = php;
				if ((!value || value == '') && (!php || php == '')) arr.splice(o,1);
				return;
			}
		}
		arr[arr.length] = Array(tag, value, php);
	}
	
	this.getProp = function (arr, tag) {
		for (o=0; o<arr.length; o++) {
			if (arr[o][0] == tag) return arr[o][1];
		}
	}
	
	this.getPHP = function (arr, tag) {
		for (o=0; o<arr.length; o++) {
			if (arr[o][0] == tag) return arr[o][2];
		}
	}
	
	this.updateAttributes();
	this.updateStyles();
	if (this.content) this.applyContent();
}

function elementKeyDown(ev) {
	//alert(ev.keyCode);
	if (ev.shiftKey) return;
	if (ev.keyCode == 39) {
		for (p=0; p<document.selectedElements.length; p++) {
			document.selectedElements[p].updateProp(document.selectedElements[p].styles, 'left', parseInt(document.selectedElements[p].object.style.left) + 1 + 'px', document.selectedElements[p].getPHP(document.selectedElements[p].styles, 'left'));			
			document.selectedElements[p].updateStyles();
		}
	}
	if (ev.keyCode == 37) {
		for (p=0; p<document.selectedElements.length; p++) {
			document.selectedElements[p].updateProp(document.selectedElements[p].styles, 'left', parseInt(document.selectedElements[p].object.style.left) - 1 + 'px', document.selectedElements[p].getPHP(document.selectedElements[p].styles, 'left'));			
			document.selectedElements[p].updateStyles();
		}
	}
	if (ev.keyCode == 40) {
		for (p=0; p<document.selectedElements.length; p++) {
			document.selectedElements[p].updateProp(document.selectedElements[p].styles, 'top', parseInt(document.selectedElements[p].object.style.top) + 1 + 'px',  document.selectedElements[p].getPHP(document.selectedElements[p].styles, 'top'));			
			document.selectedElements[p].updateStyles();
		}
	}
	if (ev.keyCode == 38) {
		for (p=0; p<document.selectedElements.length; p++) {
			document.selectedElements[p].updateProp(document.selectedElements[p].styles, 'top', parseInt(document.selectedElements[p].object.style.top) - 1 + 'px', document.selectedElements[p].getPHP(document.selectedElements[p].styles, 'top'));			
			document.selectedElements[p].updateStyles();
		}
	}
	if (ev.keyCode == 46) {
		deleteElements();
	}
	if (ev.keyCode == 67 && ev.ctrlKey) {
		stopEvent(ev);
		copyElements();
	}
	if (ev.keyCode == 86 && ev.ctrlKey) {
		stopEvent(ev);
		pasteElements();
	}
	if (ev.keyCode == 65 && ev.ctrlKey) {
		stopEvent(ev);
		selectAll();
	}
	if (ev.keyCode == 88 && ev.ctrlKey) {
		stopEvent(ev);
		cutElements();
	}
	if (ev.keyCode == 83 && ev.ctrlKey) {
		stopEvent(ev);
		showLoadingImage();
		setTimeout('saveForm()',100);
	}
}

function selectAll() {
	for (o=0; o<document.currentPageElements.length; o++) document.currentPageElements[o].select();
	showProps();
	showAllElementsList();
}

function deselectAll() {
	while (document.selectedElements.length>0) document.selectedElements[0].unSelect();
	showProps();
	showAllElementsList();
}

function copyElements() {
	document.copiedElements = Array();
	for (p=0; p<document.selectedElements.length; p++) document.copiedElements[document.copiedElements.length] = document.selectedElements[p].clone();
}

function pasteElements() {
	if (!document.copiedElements || document.copiedElements.length == 0) return;
	deselectAll();
	for (p=0; p<document.copiedElements.length; p++) {
		var cnt = document.allElements.length;
		document.currentPageElements[document.currentPageElements.length] = document.copiedElements[p];
		document.allElements[cnt] = document.copiedElements[p];
		document.getElementById('sfg_inner_container').appendChild(document.allElements[cnt].object);
		document.allElements[cnt].updateProp(document.allElements[cnt].styles, 'left', parseInt(document.allElements[cnt].object.style.left) + 13 + 'px', document.allElements[cnt].getPHP(document.allElements[cnt].styles, 'left'));
		document.allElements[cnt].updateProp(document.allElements[cnt].styles, 'top', parseInt(document.allElements[cnt].object.style.top) + 13 + 'px', document.allElements[cnt].getPHP(document.allElements[cnt].styles, 'top'));
		document.allElements[cnt].updateStyles();
		document.allElements[cnt].select();
		document.allElements[cnt].page = document.currentPage;
	}
	copyElements();
	setStuckPoints();
	showProps();
	showAllElementsList();
	focusHiddenInput();
}

function deleteElements() {
	for (i=0; i<document.selectedElements.length; i++) {
		for (o=0; o<document.currentPageElements.length; o++) if (document.selectedElements[i] == document.currentPageElements[o]) {
			document.currentPageElements.splice(o,1);
			break;
		}
		for (o=0; o<document.allElements.length; o++) if (document.selectedElements[i] == document.allElements[o]) {
			document.getElementById('sfg_inner_container').removeChild(document.allElements[o].object);
			document.allElements.splice(o,1);
			break;
		}
	}
	hideProps();
	showAllElementsList();
	focusHiddenInput();
}

function cutElements() {
	copyElements();
	deleteElements();
}

function clearForm() {
	if (!confirm(getTranslation('Are you sure want to clear the form?'))) return;
	selectAll();
	deleteElements();
	document.selectedElements = Array();
	document.currentPageElements = Array();
	document.allElements = Array();
	hideProps();
	setStuckPoints();
	switchPage(0);
}

var hidden_input = document.createElement('input');
hidden_input.id = 'hiddenInput';
document.sfgArea.appendChild(hidden_input);
addEvent(hidden_input, 'keydown', elementKeyDown);

function focusHiddenInput() {
	var arr = Array('input', 'textarea');
	if (document.selectedElements.length > 0) {
		if (document.lastOver) {
			if (document.lastOver.tag.toLowerCase() == 'textarea' || document.lastOver.tag.toLowerCase() == 'input' && document.lastOver.getProp(document.lastOver.attributes, 'type') == 'text') {
				try {
					document.lastOver.object.focus();
					document.getElementById('container').scrollIntoView(false);
				} catch(e) {
					document.getElementById('hiddenInput').focus();
					document.getElementById('container').scrollIntoView(false);
				}
				return;
			}
		}
		for (i=document.selectedElements.length-1; i>=0; i--)
			if (document.selectedElements[i].tag.toLowerCase() == 'textarea' || document.selectedElements[i].tag.toLowerCase() == 'input' && document.selectedElements[i].getProp(document.selectedElements[i].attributes, 'type') == 'text') {
				try {
					document.selectedElements[i].object.focus();
					document.getElementById('container').scrollIntoView(false);
				} catch(e) {
					document.getElementById('hiddenInput').focus();
					document.getElementById('container').scrollIntoView(false);
				}
				try {
					document.lastOver.object.focus();
					document.getElementById('container').scrollIntoView(false);
				} catch(e) {
					document.getElementById('hiddenInput').focus();
					document.getElementById('container').scrollIntoView(false);
				}
				return;
			}		
	}
	document.getElementById('hiddenInput').focus();
	document.getElementById('container').scrollIntoView(false);
}

focusHiddenInput();