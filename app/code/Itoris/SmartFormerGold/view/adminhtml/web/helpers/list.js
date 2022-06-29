function showAllElementsList() {
	if (!document.showList) return;
	if (document.selectedElements.length > 0 || document.hlObj) {
		if (document.hlObj) var obj = document.hlObj; else var obj = document.selectedElements[0];
		var center = obj.object.offsetLeft + Math.floor(obj.object.offsetWidth/2) - document.getElementById('sfg_container').scrollLeft;
	} else var center = 0;
	if (!document.getElementById('elementsList')) document.body.appendChild(document.elementsList);
	if (center > document.getElementById('sfg_container').offsetWidth/2) document.elementsList.style.left = document.mostLeft + 2 + 'px';
		else document.elementsList.style.left = document.mostLeft + document.getElementById('sfg_container').offsetWidth - document.elementsList.offsetWidth - 20 + 'px';
	propListResizer();
	renderElementsList();
}

function propListResizer() {
	if (document.getElementById('elementProperties') && document.getElementById('elementsList')) {
		document.elementsList.style.left = document.elementProps.style.left;
		document.elementsList.style.top = document.mostTop + Math.floor(document.getElementById('sfg_container').offsetHeight/2-10) + 2 + 'px';
		document.elementsList.style.height = Math.floor(document.getElementById('sfg_container').offsetHeight/2+10) - 23 + 'px';
		document.getElementById('elList').style.height = document.elementsList.offsetHeight - 23 + 'px';
		document.elementProps.style.top = document.mostTop + 2 + 'px';
		document.elementProps.style.height = Math.floor(document.getElementById('sfg_container').offsetHeight/2+10) - 23 + 'px';
		document.getElementById('attrList').style.height = document.elementProps.offsetHeight - 41 + 'px';
	} else if (document.getElementById('elementsList')) {
		document.elementsList.style.top = document.mostTop + 2 + 'px';
		document.elementsList.style.height = document.getElementById('sfg_container').offsetHeight - 23 + 'px';
		document.getElementById('elList').style.height = document.elementsList.offsetHeight - 23 + 'px';
	} else if (document.getElementById('elementProperties')) {
		document.elementProps.style.top = document.mostTop + 2 + 'px';
		document.elementProps.style.height = document.getElementById('sfg_container').offsetHeight - 23 + 'px';
		document.getElementById('attrList').style.height = document.elementProps.offsetHeight - 41 + 'px';
	}
	
}

function renderElementsList() {
	if (!document.getElementById('elList')) return;
	var table = document.createElement('table');
	table.cellSpacing = 0;
	var tbody = document.createElement('tbody');
	table.appendChild(tbody);
	var tr = document.createElement('tr');
	tr.style.backgroundColor = '#dddddd';
	tr.style.cursor = 'default';
	var td = document.createElement('td');
	td.innerHTML = '<b>sel</b>';
	td.style.width = '12px';
	tr.appendChild(td);
	var td = document.createElement('td');
	td.innerHTML = '<b>move</b>';
	td.style.width = '24px';
	tr.appendChild(td);
	var td = document.createElement('td');
	td.innerHTML = '<b>Tag</b>';
	tr.appendChild(td);
	var td = document.createElement('td');
	td.innerHTML = '<b>Type</b>';
	tr.appendChild(td);
	var td = document.createElement('td');
	td.innerHTML = '<b>Name</b>';
	tr.appendChild(td);
	var td = document.createElement('td');
	td.innerHTML = '<b>ID</b>';
	tr.appendChild(td);
	tbody.appendChild(tr);
	for (i=0; i<document.currentPageElements.length; i++) {
		var tr = document.createElement('tr');
		var td = document.createElement('td');
		td.innerHTML = '<input type="checkbox" '+((document.currentPageElements[i].selected)?'checked="checked"':'')+' width="14" style="width:14px; height:14px; margin:0" onclick="if (this.checked) document.currentPageElements['+i+'].select(); else document.currentPageElements['+i+'].unSelect();elementUp(null)" />';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.innerHTML = '<div onclick="elListMoveUp(this,'+i+')" class="el-list-up"'+(i==0?' style="visibility:hidden"':'')+'></div><div onclick="elListMoveDown(this,'+i+')" class="el-list-down"'+(i==document.currentPageElements.length-1?' style="visibility:hidden"':'')+'></div>';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.innerHTML = document.currentPageElements[i].tag;
		tr.appendChild(td);
		var td = document.createElement('td');
		var val = document.currentPageElements[i].getProp(document.currentPageElements[i].attributes,'type');
		td.innerHTML = val ? val : '&nbsp;';
		tr.appendChild(td);
		var td = document.createElement('td');
		var val = document.currentPageElements[i].getProp(document.currentPageElements[i].attributes,'name');
		td.innerHTML = val ? val : '&nbsp;';
		tr.appendChild(td);
		var td = document.createElement('td');
		var val = document.currentPageElements[i].getProp(document.currentPageElements[i].attributes,'id');
		td.innerHTML = val ? val : '&nbsp;';
		tr.appendChild(td);
		tbody.appendChild(tr);
		addEvent(tr, 'mousedown', elementsListClick);
		tr.parent = document.currentPageElements[i];
	}
	document.getElementById('elList').innerHTML = '';
	document.getElementById('elList').appendChild(table);
}

function elListMoveUp(obj, i){
	if (i<1 || i>document.currentPageElements.length-1) return;
	for(var o=0, a=-1, b=-1; o<document.allElements.length; o++) {
		if (document.allElements[o] == document.currentPageElements[i]) a = o;
		if (document.allElements[o] == document.currentPageElements[i-1]) b = o;
	}
	if (a<0 || b<0) return;
	var tmp = document.currentPageElements[i];
	document.currentPageElements[i].object.parentNode.insertBefore(document.currentPageElements[i].object, document.currentPageElements[i-1].object);
	document.currentPageElements[i] = document.currentPageElements[i-1];
	document.currentPageElements[i-1] = tmp;
	var tmp = document.allElements[a];
	document.allElements[a] = document.allElements[b];
	document.allElements[b] = tmp;
}

function elListMoveDown(obj, i){
	if (i<0 || i>document.currentPageElements.length-2) return;
	elListMoveUp(obj, i + 1);
}

function elementsListClick(ev) {
	//if (document.hlObj) return;
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	while (obj.tagName.toLowerCase() != 'tr') obj = obj.parentNode;
	document.hlObj = obj.parent;
	scrollIntoView();
	showProps();
}

function scrollIntoView() {
	var step = 50;
	var left = Math.floor(document.hlObj.object.offsetLeft + document.hlObj.object.offsetWidth/2 - document.getElementById('sfg_container').offsetWidth/2);
	var top = Math.floor(document.hlObj.object.offsetTop + document.hlObj.object.offsetHeight/2 - document.getElementById('sfg_container').offsetHeight/2);
	
	if (left < 0) left = 0;
	if (top < 0) top = 0;
		
	if (left > document.getElementById('sfg_inner_container').offsetWidth - document.getElementById('sfg_container').offsetWidth) 
		left = document.getElementById('sfg_inner_container').offsetWidth - document.getElementById('sfg_container').offsetWidth;
		
	if (top > document.getElementById('sfg_inner_container').offsetHeight - document.getElementById('sfg_container').offsetHeight) 
		top = document.getElementById('sfg_inner_container').offsetTop - document.getElementById('sfg_container').offsetHeight;
		
	var scL = document.getElementById('sfg_container').scrollLeft;
	var scT = document.getElementById('sfg_container').scrollTop;
	
	if (Math.abs(scL - left) < 45 && scT == top) { 
		highlightObject(0); 
		return;
	}
	
	if (scL < left) 
		document.getElementById('sfg_container').scrollLeft = scL + step;
		
	if (scL > left) 
		document.getElementById('sfg_container').scrollLeft = scL - step;
		
	if (scT < top) 
		document.getElementById('sfg_container').scrollTop = scT + step;
		
	if (scT > top) 
		document.getElementById('sfg_container').scrollTop = scT - step;
		
	if (Math.abs(document.getElementById('sfg_container').scrollLeft - left) < step) 
		document.getElementById('sfg_container').scrollLeft = left;
		
	if (Math.abs(document.getElementById('sfg_container').scrollTop - top) < step) 
		document.getElementById('sfg_container').scrollTop = top;
		
	setTimeout('scrollIntoView()', 10);
}

function highlightObject(i) {
	var queue = new HighlightObjectQueue(document.hlObj);
	queue.run();
/*
	if (i>7) {
		document.hlObj.updateStyles();
		showAllElementsList();
		document.hlObj = null;
		return;
	}
	var c;
	if (i==0 || i==3 || i==6) c = '#ff0000';
	if (i==1 || i==4 || i==7) c = '#00ff00';
	if (i==2 || i==5 || i==8) c = '#0000ff';
	document.hlObj.object.style.border = '2px solid ' + c;
	document.hlObj.object.style.background = c;
	setTimeout('highlightObject('+(i+1)+')',50);*/
}

function HighlightObjectQueue(obj) {
	this.target = obj;
	this.iteration = 0;
//	console.log('created highlight object');
}

HighlightObjectQueue.prototype.run = function(){
//	console.log('run highlight queue iteration: ' + this.iteration);
	if(this.iteration > 7) {
		return this.terminate();
	}
	
	var colors = ['#ff0000', '#00ff00', '#0000ff'];
	var color = colors[this.iteration % 3];
	
//	console.log('select color : ' + color);
	
	this.target.object.style.border = '2px solid ' + color;
	this.target.object.style.background = color;
	
	setTimeout(this.run.bind(this), 50);
	this.iteration++;
};

HighlightObjectQueue.prototype.terminate = function() {
//	console.log('termination of highlight queue');
	this.target.updateStyles();
	showAllElementsList();
//	console.log('finish of highlight queue');
};

function closeElementsList(ev) {
	list_click(null);
	hideList();
	hideLoadingImage();
	if (!document.listCloseAlert) {
		document.listCloseAlert = true;
		alert(getTranslation('Elements List is disabled.\nFor enabling it again please follow:\nOptions -> Show Elements List'));
	}
}

function hideList() {
	if (document.getElementById('elementsList')) document.body.removeChild(document.elementsList);
}

document.elementsList = document.createElement('div');
document.elementsList.id = 'elementsList';
var div = document.createElement('div');
div.id = 'elementsListHeader';
div.innerHTML = '<div style="float:left">'+getTranslation('Elements of current page')+'</div>';
var img = document.createElement('img');
img.src = document.adminURL+'/modules/msdn/cross.gif';
img.id = 'elementsListClose';
img.title = getTranslation('Close this Bar');
addEvent(img, 'mousedown', closeElementsList);
div.appendChild(img);
document.elementsList.appendChild(div);

var div = document.createElement('div');
div.id = 'elList';
document.elementsList.appendChild(div);

//setTimeout("showAllElementsList()",500);