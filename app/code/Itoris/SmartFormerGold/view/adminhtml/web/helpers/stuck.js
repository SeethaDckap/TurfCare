var div = document.createElement('div');
div.id = 'stuckLeftLine';
div.style.height = document.getElementById('sfg_inner_container').offsetHeight + 'px';
document.getElementById('sfg_inner_container').appendChild(div);

var div = document.createElement('div');
div.id = 'stuckRightLine';
div.style.height = document.getElementById('sfg_inner_container').offsetHeight + 'px';
document.getElementById('sfg_inner_container').appendChild(div);

var div = document.createElement('div');
div.id = 'stuckTopLine';
div.style.width = document.getElementById('sfg_inner_container').offsetWidth + 'px';
document.getElementById('sfg_inner_container').appendChild(div);

var div = document.createElement('div');
div.id = 'stuckBottomLine';
div.style.width = document.getElementById('sfg_inner_container').offsetWidth + 'px';
document.getElementById('sfg_inner_container').appendChild(div);

function setStuckPoints() {
	document.stuckPointsX = Array();
	document.stuckPointsY = Array();
	if (!document.stuckEffect) return;
	for (i=0; i<document.currentPageElements.length; i++)
		if (document.currentPageElements[i].page == document.currentPage && !document.currentPageElements[i].selected) {
			if (!in_array(document.currentPageElements[i].object.offsetLeft, document.stuckPointsX)) document.stuckPointsX[document.stuckPointsX.length] = document.currentPageElements[i].object.offsetLeft;
			if (!in_array(document.currentPageElements[i].object.offsetLeft + document.currentPageElements[i].object.offsetWidth, document.stuckPointsX)) document.stuckPointsX[document.stuckPointsX.length] = document.currentPageElements[i].object.offsetLeft + document.currentPageElements[i].object.offsetWidth;
			if (!in_array(document.currentPageElements[i].object.offsetTop, document.stuckPointsY)) document.stuckPointsY[document.stuckPointsY.length] = document.currentPageElements[i].object.offsetTop;
			if (!in_array(document.currentPageElements[i].object.offsetTop + document.currentPageElements[i].object.offsetHeight, document.stuckPointsY)) document.stuckPointsY[document.stuckPointsY.length] = document.currentPageElements[i].object.offsetTop + document.currentPageElements[i].object.offsetHeight;
		}
}

function getStuckPints(x1, y1, x2, y2) {
	var arr = Array(0,0,0,0);
	if (!document.stuckEffect) return arr;
	for (i=0; i<document.stuckPointsX.length; i++) if (Math.abs(x1-document.stuckPointsX[i])<3) { arr[0] = document.stuckPointsX[i] + document.brc1; break; }
	for (i=0; i<document.stuckPointsX.length; i++) if (Math.abs(x2-document.stuckPointsX[i])<3) { arr[2] = document.stuckPointsX[i] + document.brc1; break; }
	for (i=0; i<document.stuckPointsY.length; i++) if (Math.abs(y1-document.stuckPointsY[i])<3) { arr[1] = document.stuckPointsY[i] + document.brc1; break; }
	for (i=0; i<document.stuckPointsY.length; i++) if (Math.abs(y2-document.stuckPointsY[i])<3) { arr[3] = document.stuckPointsY[i] + document.brc1; break; }
	var stuckLines = Array('stuckLeftLine','stuckTopLine','stuckRightLine','stuckBottomLine');
	for (i=0; i<4; i++)
		if (arr[i] != 0) {
			if (i==0 || i==2) document.getElementById(stuckLines[i]).style.left = arr[i] + 'px';
				else document.getElementById(stuckLines[i]).style.top = arr[i] + 'px';
			document.getElementById(stuckLines[i]).style.visibility = 'visible';
		} else document.getElementById(stuckLines[i]).style.visibility = 'hidden';
	return arr;
}

function hideStuckLines() {
	if (!document.stuckEffect) return;
	var stuckLines = Array('stuckLeftLine','stuckTopLine','stuckRightLine','stuckBottomLine');
	for (i=0; i<4; i++) document.getElementById(stuckLines[i]).style.visibility = 'hidden';	
}

setStuckPoints();