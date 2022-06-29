function areaMove(ev) {
	updateMouse(ev);
	if (document.flowSelector) {
		if (document.flowSelector.x < mouse['x2']) {
			document.flowSelector.style.left = document.flowSelector.x + 'px';
			document.flowSelector.style.width = mouse['x2'] - document.flowSelector.offsetLeft + 'px';
		} else {
			document.flowSelector.style.left = mouse['x2'] + 'px';
			document.flowSelector.style.width = document.flowSelector.x - mouse['x2'] + 'px';
		}
		if (document.flowSelector.y < mouse['y2']) {
			document.flowSelector.style.top = document.flowSelector.y + 'px';
			document.flowSelector.style.height = mouse['y2'] - document.flowSelector.offsetTop + 'px';
		} else {
			document.flowSelector.style.top = mouse['y2'] + 'px';
			document.flowSelector.style.height = document.flowSelector.y - mouse['y2'] + 'px';			
		}
	} else if (document.tmpObject) {
		var x = mouse['x'] - document.mostLeftScrolled - 5;
		var y = mouse['y'] - document.mostTopScrolled - 5;
		var cor = getStuckPints(x,y,x+document.tmpObject.object.offsetWidth,y+document.tmpObject.object.offsetHeight);
		var x = ((cor[0] != 0) ? cor[0] : ((cor[2] != 0) ? cor[2] - document.tmpObject.object.offsetWidth : x));
		var y = ((cor[1] != 0) ? cor[1] : ((cor[3] !=0) ? cor[3] - document.tmpObject.object.offsetHeight : y));
		
		var offsetLeft = x + document.mostLeftScrolled;
		var offsetTop = y + document.mostTopScrolled;
		var areaWidth = parseInt(document.getElementById('sfg_container').style.width);
		var areaHeight = parseInt(document.getElementById('sfg_container').style.height);
		var offsetRight = document.mostLeftScrolled+areaWidth-document.tmpObject.object.offsetWidth-20+document.getElementById('sfg_container').scrollLeft;
		var offsetBottom = document.mostTopScrolled+areaHeight-document.tmpObject.object.offsetHeight-20+document.getElementById('sfg_container').scrollTop;
		var objectLeftCoord = (x>0) ? (offsetLeft) : (document.mostLeftScrolled);
		var objectRightCoord = objectLeftCoord+document.tmpObject.object.offsetWidth;
		var objectTopCoord = (y>0) ? (offsetTop) : (document.mostTopScrolled);
		var objectBottomCoord = objectTopCoord+document.tmpObject.object.offsetHeight;
		if(objectTopCoord+areaHeight-offsetBottom<=60){
		    document.getElementById('sfg_container').scrollTop -= 20;
		    if(objectTopCoord+areaHeight-offsetBottom<=37){
		        objectTopCoord = parseInt(document.getElementById('sfg_container').style.top)+30;
		    }
		}
		if (offsetBottom-objectTopCoord<=10){
		    var objectOffset = document.getElementById('sfg_inner_container').offsetHeight-document.getElementById('sfg_container').scrollTop-objectBottomCoord;
		    if (objectOffset>-150){
		        document.getElementById('sfg_container').scrollTop += 20;
    		    objectTopCoord = document.getElementById('sfg_container').scrollTop-(offsetBottom-objectTopCoord);
		    }else{
		        var a = document.getElementById('sfg_inner_container').offsetHeight+20;
		        document.getElementById('sfg_inner_container').style.height = a+'px';
		    }
		}else if(offsetBottom-objectTopCoord>10){
		    document.tmpObject.object.style.top = objectTopCoord+'px';
		}
		if(objectLeftCoord+areaWidth-offsetRight<=100){
		    document.getElementById('sfg_container').scrollLeft -= 20;
		    if(objectLeftCoord+areaWidth-offsetRight<=37){
		        objectLeftCoord = parseInt(document.getElementById('sfg_container').style.left)+30;
		    }
		}
		if (offsetRight-objectLeftCoord<=10){
		    var objectOffset = document.getElementById('sfg_inner_container').offsetWidth-document.getElementById('sfg_container').scrollLeft-objectRightCoord;
		    if (objectOffset>20){
		        document.getElementById('sfg_container').scrollLeft += 20;
		        objectLeftCoord = document.getElementById('sfg_container').scrollLeft-(offsetRight-objectLeftCoord);
		    }else{
		        var a = document.getElementById('sfg_inner_container').offsetWidth+20;
		        document.getElementById('sfg_inner_container').style.width = a+'px';
		        document.getElementById('sfg_container').scrollLeft += 20;
		    }
		}else if(offsetRight-objectLeftCoord>10){
		    document.tmpObject.object.style.left = objectLeftCoord+'px';
		}
		
	} else if (document.drag && document.resizeId > 0 && document.lastOver) {
		if (document.drag && document.resizeId!=3 && document.drag && document.resizeId!=4 && !document.lastOver.object.style.width) document.lastOver.object.style.width = document.lastOver.object.offsetWidth + 'px';
		if (document.drag && document.resizeId!=1 && document.drag && document.resizeId!=2 && !document.lastOver.object.style.height) document.lastOver.object.style.height = document.lastOver.object.offsetHeight + 'px';
		var x = mouse['x2'] - document.deltaLeft;
		var y = mouse['y2'] - document.deltaTop;
		var x2 = mouse['x2'] - document.deltaLeft2 - parseInt(document.lastOver.object.style.left);
		var y2 = mouse['y2'] - document.deltaTop2 - parseInt(document.lastOver.object.style.top);
		var x3 = parseInt(document.lastOver.object.style.left) - x;
		var y3 = parseInt(document.lastOver.object.style.top) - y;
		if (document.resizeId == 1 && parseInt(document.lastOver.object.style.width) + x3>0) {
			document.lastOver.object.style.width = parseInt(document.lastOver.object.style.width) + x3 + 'px';
			document.lastOver.object.style.left = x + 'px';
		}
		if (document.resizeId == 2 && x2 > 0) document.lastOver.object.style.width = x2 + 'px';
		if (document.resizeId == 3 && parseInt(document.lastOver.object.style.height) + y3 > 0) {
			document.lastOver.object.style.height = parseInt(document.lastOver.object.style.height) + y3 + 'px';
			document.lastOver.object.style.top = y + 'px';
		}
		if (document.resizeId == 4 && y2 > 0) document.lastOver.object.style.height = y2 + 'px';
		if (document.resizeId == 5 && parseInt(document.lastOver.object.style.width) + x3>0 && parseInt(document.lastOver.object.style.height) + y3>0) {
			document.lastOver.object.style.width = parseInt(document.lastOver.object.style.width) + x3 + 'px';
			document.lastOver.object.style.left = x + 'px';
			document.lastOver.object.style.height = parseInt(document.lastOver.object.style.height) + y3 + 'px';
			document.lastOver.object.style.top = y + 'px';
		}
		if (document.resizeId == 6 && x2>0 && y2>0) {
			document.lastOver.object.style.width = x2 + 'px';
			document.lastOver.object.style.height = y2 + 'px';
		}
		if (document.resizeId == 7 && parseInt(document.lastOver.object.style.height) + y3>0 && x2>0) {
			document.lastOver.object.style.height = parseInt(document.lastOver.object.style.height) + y3 + 'px';
			document.lastOver.object.style.top = y + 'px';
			document.lastOver.object.style.width = x2 + 'px';
		}
		if (document.resizeId == 8 && parseInt(document.lastOver.object.style.width) + x3>0 && y2>0) {
			document.lastOver.object.style.width = parseInt(document.lastOver.object.style.width) + x3 + 'px';
			document.lastOver.object.style.left = x + 'px';
			document.lastOver.object.style.height = y2 + 'px';
		}
		
	} else if (document.drag) {
		var x = mouse['x2'] - document.deltaLeft;
		var y = mouse['y2'] - document.deltaTop;
		var bind = bindToGrid(x, y);
		x = ((bind[0] != 0) ? bind[0] : x);
		y = ((bind[1] != 0) ? bind[1] : y);
		var cor = getStuckPints(x,y,x+document.drag.object.offsetWidth,y+document.drag.object.offsetHeight);
		var dx = document.drag.object.offsetLeft - ((cor[0] != 0) ? cor[0] : ((cor[2] != 0) ? cor[2] - document.drag.object.offsetWidth : x));
		var dy = document.drag.object.offsetTop - ((cor[1] != 0) ? cor[1] : ((cor[3] !=0) ? cor[3] - document.drag.object.offsetHeight : y));
		if (document.drag.object.offsetLeft<=0 && dx>0 || document.drag.object.offsetTop<=0 && dy>0) elementUp(null);
		var compensationX = 0, compensationY = 0;
		for (p=0; p<document.selectedElements.length; p++) {
			var x2 = parseInt(document.selectedElements[p].object.style.left) - dx;
			var y2 = parseInt(document.selectedElements[p].object.style.top) - dy;
			document.selectedElements[p].object.style.left = x2 + 'px';
			document.selectedElements[p].object.style.top = y2 + 'px';
			if (x2 < 0 && x2 < compensationX) compensationX = x2;
			if (y2 < 0 && y2 < compensationY) compensationY = y2;
		}
		if (compensationX < 0 || compensationY < 0) {
			for (p=0; p<document.selectedElements.length; p++) {
				document.selectedElements[p].object.style.left = parseInt(document.selectedElements[p].object.style.left) - compensationX + 'px';
				document.selectedElements[p].object.style.top = parseInt(document.selectedElements[p].object.style.top) - compensationY + 'px';
			}
		}
		if(document.drag){
    		var areaWidth = parseInt(document.getElementById('sfg_container').style.width);
    		var areaHeight = parseInt(document.getElementById('sfg_container').style.height);
    		var offsetRight = document.getElementById('sfg_container').scrollLeft+areaWidth;
    		var offsetBottom = document.getElementById('sfg_container').scrollTop+areaHeight;
    		var objectBottomCoord = y+document.drag.object.offsetHeight;
    		var objectRightCoord = x+document.drag.object.offsetWidth;
    		if(offsetBottom-objectBottomCoord<=20){//alert('objectBottomCoord='+objectBottomCoord+'; offsetBottom='+offsetBottom);
    		    var objectOffset = document.getElementById('sfg_inner_container').offsetHeight-objectBottomCoord;
    		    if (objectOffset>10){
    		        document.getElementById('sfg_container').scrollTop += 20;
        		}else{
    		        var a = document.getElementById('sfg_inner_container').offsetHeight+20;
    		        document.getElementById('sfg_inner_container').style.height = a+'px';
    		    }
    		}
    		if (y-(offsetBottom-areaHeight)<=20){
    		    document.getElementById('sfg_container').scrollTop -= 20;
    		    objectTopCoord = document.getElementById('sfg_container').scrollTop-(offsetBottom-y);
    		}
    		
    		if(offsetRight-objectRightCoord<=20){
    		    var objectOffset = document.getElementById('sfg_inner_container').offsetWidth-objectRightCoord;
    		    if (objectOffset>10){
    		        document.getElementById('sfg_container').scrollLeft += 20;
        		}else{
    		        var a = document.getElementById('sfg_inner_container').offsetWidth+20;
    		        document.getElementById('sfg_inner_container').style.width = a+'px';
    		    }
    		}
    		if (x-(offsetRight-areaWidth)<=20){
    		    document.getElementById('sfg_container').scrollLeft -= 20;
    		    objectLeftCoord = document.getElementById('sfg_container').scrollLeft-(offsetRight-x);
    		}
		}
	} else if (document.lastOver && !document.drag) {
		var x = mouse['x2'];
		var y = mouse['y2'];
		var x3 = document.lastOver.object.offsetLeft + document.lastOver.object.offsetWidth;
		var y3 = document.lastOver.object.offsetTop + document.lastOver.object.offsetHeight;
		var x1 = Math.abs(document.lastOver.object.offsetLeft - x);
		var x2 = Math.abs(x3 - x);
		var y1 = Math.abs(document.lastOver.object.offsetTop - y);
		var y2 = Math.abs(y3 - y);
		var lastResize = document.resizeId;
		document.resizeId = 0;
		if (x1 < 3 && y > document.lastOver.object.offsetTop && y < y3) document.resizeId = 1;
		if (x2 < 3 && y > document.lastOver.object.offsetTop && y < y3) document.resizeId = 2;
		if (y1 < 3 && x > document.lastOver.object.offsetLeft && x < x3) document.resizeId = 3;
		if (y2 < 3 && x > document.lastOver.object.offsetLeft && x < x3) document.resizeId = 4;
		if (x1 < 3 && y1 < 3) document.resizeId = 5;
		if (x2 < 3 && y2 < 3) document.resizeId = 6;
		if (x2 < 3 && y1 < 3) document.resizeId = 7;
		if (x1 < 3 && y2 < 3) document.resizeId = 8;
		if (lastResize != document.resizeId) {
			var area = document.getElementById('sfg_inner_container');
			if (document.resizeId == 0) { document.lastOver.object.style.cursor = 'auto'; area.style.cursor = 'auto'; }
			if (document.resizeId == 1 || document.resizeId == 2) { document.lastOver.object.style.cursor = 'E-resize'; area.style.cursor = 'E-resize'; }
			if (document.resizeId == 3 || document.resizeId == 4) { document.lastOver.object.style.cursor = 'N-resize'; area.style.cursor = 'N-resize'; }
			if (document.resizeId == 5 || document.resizeId == 6) { document.lastOver.object.style.cursor = 'SE-resize'; area.style.cursor = 'SE-resize'; }
			if (document.resizeId == 7 || document.resizeId == 8) { document.lastOver.object.style.cursor = 'NE-resize'; area.style.cursor = 'NE-resize'; }
		}
	}
}

function areaDown(ev) {
	if (document.resizeId > 0 && document.lastOver) {
		document.drag = document.lastOver;
	}
	
	if (document.drag) {
		document.deltaLeft = mouse['x2'] - obj.offsetLeft;
		document.deltaTop = mouse['y2'] - obj.offsetTop;
		document.deltaLeft2 = mouse['x2'] - obj.offsetLeft - obj.offsetWidth + document.brc;
		document.deltaTop2 = mouse['y2'] - obj.offsetTop - obj.offsetHeight + document.brc;
		return;
	}
	
	document.selecting = true;
	if (!document.attachEvent) {
		setTimeout('flowSelector('+mouse['x2']+','+mouse['y2']+')',50);
	} else {
		flowSelector(mouse['x2'],mouse['y2']);
	}
}

function flowSelector(x, y) {
	if (document.drag || document.tmpObject || !document.selecting) return;
	if (!document.flowSelector) {
		document.flowSelector = document.createElement('div');
		document.flowSelector.id = 'flowSelector';
		document.getElementById('sfg_inner_container').appendChild(document.flowSelector);
	}
	document.flowSelector.x = x;
	document.flowSelector.y = y;
	document.flowSelector.style.left = x + 'px';
	document.flowSelector.style.top = y + 'px';
	document.flowSelector.style.width = '0px';
	document.flowSelector.style.height = '0px';
}

function areaUp(ev) {
	document.selecting = false;
	if (document.resizeId > 0 && document.lastOver) elementUp(ev);
	if (document.flowSelector) {
		document.flowSelector.parentNode.removeChild(document.flowSelector);
		deselectAll();
		for (i=0; i<document.currentPageElements.length; i++) {
			if (document.currentPageElements[i].object.offsetLeft >= parseInt(document.flowSelector.style.left) &&
				document.currentPageElements[i].object.offsetLeft + document.currentPageElements[i].object.offsetWidth <= parseInt(document.flowSelector.style.left) + parseInt(document.flowSelector.style.width) &&
				document.currentPageElements[i].object.offsetTop >= parseInt(document.flowSelector.style.top) &&
				document.currentPageElements[i].object.offsetTop + document.currentPageElements[i].object.offsetHeight <= parseInt(document.flowSelector.style.top) + parseInt(document.flowSelector.style.height))
				document.currentPageElements[i].select();
		}
		setStuckPoints();
		document.flowSelector = null;
	}
	document.drag = null;
	showProps();
	showAllElementsList();
	focusHiddenInput();
}

function elementOver(ev) {
	if (document.drag || document.tmpObject) return;
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	if (obj.parent) obj.style.border = '1px solid red';
	document.lastOver = obj.parent;
}

function elementOut(ev) {
	if (document.drag || document.tmpObject) return;
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	if (obj.parent) obj.parent.updateStyles();
}

function elementDown(ev) {
	document.selecting = false;
	
	if (document.tmpObject) {
		return;
	}
	
	if (window.event) {
		obj=window.event.srcElement; 
	} else {
		obj=ev.currentTarget;
	}
	
	while (obj && !obj.parent) {
		obj = obj.parentNode;
	}
	
	if (!obj) {
		return;
	}
	
	stopEvent(ev);
	document.drag = obj.parent;
	
	if (document.drag) {
		if (ev.ctrlKey) {
			if (document.drag.selected) document.drag.unSelect(); else document.drag.select();
			document.drag = null;
			return;
		}
		if (!document.drag.selected) {
			deselectAll();
			document.drag.select();
			setStuckPoints();
		}
	}
	hideProps();
	hideList();
	areaDown(ev);	
}

function elementUp(ev) {
	for (i=0; i<document.selectedElements.length; i++) {
	    document.selectedElements[i].updateProp(
				document.selectedElements[i].styles,
				'left', 
				document.selectedElements[i].object.style.left, 
				document.selectedElements[i].getPHP(document.selectedElements[i].styles, 'left')
		);
		
		document.selectedElements[i].updateProp(
			document.selectedElements[i].styles,
			'top', 
			document.selectedElements[i].object.style.top, 
			document.selectedElements[i].getPHP(document.selectedElements[i].styles, 'top')
		);
		
		if (document.selectedElements[i].object.style.width) {
			document.selectedElements[i].updateProp(
				document.selectedElements[i].styles,
				'width', 
				document.selectedElements[i].object.style.width, 
				document.selectedElements[i].getPHP(document.selectedElements[i].styles, 'width')
			);
		}
		if (document.selectedElements[i].object.style.height) {
			document.selectedElements[i].updateProp(
				document.selectedElements[i].styles,
				'height', 
				document.selectedElements[i].object.style.height, 
				document.selectedElements[i].getPHP(document.selectedElements[i].styles, 'height')
			);
		}
	}
	document.drag = null;
	setStuckPoints();
	hideStuckLines();
	showProps();
	showAllElementsList();
	focusHiddenInput();
}

function updateMouse(ev) {
		mouse['x'] = (window.event && document.currentBrowser != 'safari' ? window.event.clientX + document.documentElement.scrollLeft : ev.pageX) - document.brc;
		mouse['y'] = (window.event && document.currentBrowser != 'safari' ? window.event.clientY + document.documentElement.scrollTop : ev.pageY) - document.brc;
		mouse['x2'] = mouse['x'] - document.mostLeftScrolled;
		mouse['y2'] = mouse['y'] - document.mostTopScrolled;
}

addEvent(document.getElementById('sfg_central'), 'mousemove', areaMove);
addEvent(document.getElementById('sfg_central'), 'mousedown', areaDown);
addEvent(document.getElementById('sfg_central'), 'mouseup', areaUp);

