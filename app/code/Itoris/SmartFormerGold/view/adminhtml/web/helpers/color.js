document.colorSelectorBlue = 0;
document.colorScroll = false;
document.recentColors = Array();

function showColorSelector(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	document.propertyEdit =  obj.parentNode;
	showMask();
	var div = document.getElementById('colorSelectorBack');
	if (div) {
		div.style.visibility = 'visible';
		div.style.left = Math.floor((document.documentElement.scrollWidth - div.offsetWidth)/2) + 'px';
		return;
	}
	var div = document.createElement('div');
	div.id = 'colorSelectorBack';
	addEvent(div, 'mousemove', colorSelectorScrollMove);
	addEvent(div, 'mouseup', colorSelectorScrollUp);
	document.body.appendChild(div);
	var div2 = document.createElement('div');
	div2.id = 'colorsHeader';
	div2.innerHTML = '<span>'+getTranslation('Color Selector')+'</span>';
	var img = document.createElement('img');
	img.src = document.adminURL+'/images/close.png';
	img.id = 'colorsClose';
	img.title = getTranslation('Close this Bar');
	addEvent(img, 'mousedown', closeColorSelector);
	div2.appendChild(img);
	div.appendChild(div2);
	var div2 = document.createElement('div');
	div2.id = 'colorSelector';
	for (i=0; i<256; i++) {
		var div3 = document.createElement('div');
		addEvent(div3, 'mouseover', colorOver);
		addEvent(div3, 'mousedown', applyColor);
		div2.appendChild(div3);
	}
	div.appendChild(div2);
	var img = document.createElement('img');
	img.id = 'colorScale';
	img.src = document.adminURL+'/images/colorscale.gif';
	div.appendChild(img);
	var img = document.createElement('img');
	img.id = 'colorScroller';
	addEvent(img, 'drag', stopEvent);
	addEvent(img, 'mousedown', colorSelectorScrollDown);
	addEvent(img, 'mouseup', colorSelectorScrollUp);
	addEvent(img, 'mousemove', colorSelectorScrollMove);
	img.src = document.adminURL+'/images/scroller.gif';
	div.appendChild(img);
	div2 = document.createElement('div');
	div2.id = 'colorPreview';
	div.appendChild(div2);
	div2 = document.createElement('div');
	div2.id = 'colorPreviewText';
	div.appendChild(div2);
	div2 = document.createElement('div');
	div2.id = 'recentColors';
	div2.innerHTML = '<div>'+getTranslation('Recent Colors')+'</div>';
	for (i=0; i<10; i++) {
		div3 = document.createElement('div');
		div3.className = 'recentColor';
		addEvent(div3, 'mouseover', colorOver);
		addEvent(div3, 'mousedown', applyColor);
		if (document.recentColors[i]) div3.style.backbroundColor = document.recentColors[i];
		div2.appendChild(div3);
	}
	div.appendChild(div2);
	div.style.left = Math.floor((document.documentElement.scrollWidth - div.offsetWidth)/2) + 'px';
	refreshColors();
}

function refreshColors() {
	var divs = document.getElementById('colorSelector').getElementsByTagName('div');
	var i = 0;
	var b = document.colorSelectorBlue*17;
	if (b > 255) b = 255;
	for(r=0; r<=256; r+=17) {
		if (r > 255) r=255;		
		for (g=0; g<=256; g+=17) {
			if (g > 255) g=255;
			divs[i].style.backgroundColor='rgb('+r+','+g+','+b+')';
			i++;
		}
	}
	
}

function colorSelectorScrollDown(ev) {
	stopEvent(ev);
	document.colorScroll = true;
}

function colorSelectorScrollUp(ev) {
	document.colorScroll = false;
}

function colorSelectorScrollMove(ev) {
	updateMouse(ev);
	if (document.colorScroll) {
		var newPos = Math.round((mouse['y'] - document.getElementById('colorSelectorBack').offsetTop - 23)/21);
		if (newPos < 0) newPos = 0;
		if (newPos > 15) newPos = 15;
		if (document.colorSelectorBlue != newPos) {
			document.colorSelectorMouseY = mouse['y'];
			document.getElementById('colorScroller').style.top = 43 + Math.floor(newPos*21) + 'px';
			document.colorSelectorBlue = newPos;
			refreshColors();
		}
	}
}

function colorOver(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	var color = obj.style.backgroundColor;
	if (!color) return;
	document.getElementById('colorPreview').style.backgroundColor = color;
	document.getElementById('colorPreviewText').innerHTML = rgbToAbs(color);
	
}

function applyColor(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	var color = obj.style.backgroundColor;
	if (!color) return;
	var bool = false;
	for (i=0; i<10; i++) if (document.recentColors[i] && document.recentColors[i]==color) {bool=true; break;}
	if (!bool) {
		for (i=8; i>=0; i--) if (document.recentColors[i]) {
			document.recentColors[i+1] = document.recentColors[i];
			document.getElementById('recentColors').getElementsByTagName('div')[i+2].style.backgroundColor = document.recentColors[i+1];
		}
		document.recentColors[0] = color;
		document.getElementById('recentColors').getElementsByTagName('div')[1].style.backgroundColor = document.recentColors[0];
	}
	closeColorSelector(ev);
	document.propertyEdit.getElementsByTagName('div')[3].style.backgroundColor = color;
	var input = document.propertyEdit.getElementsByTagName('input')[0];
	input.value = rgbToAbs(color);
	input.focus();
	input.blur();
}

function rgbToAbs(color) {
	if (color.indexOf('#')>-1) return color;
	var hex = '0123456789abcdef';
	color = color.substr(4);
	color = color.substring(0,color.length-1);
	var parts = color.split(',');
	var color2 = '#';
	for (p=0; p<3; p++) color2 += hex.substr(Math.floor(parts[p]/16),1) + hex.substr(parts[p]-Math.floor(parts[p]/16)*16,1);
	return color2;
}

function closeColorSelector(ev) {
	document.getElementById('colorSelectorBack').style.visibility = 'hidden';
	hideMask();
	hideLoadingImage();
}