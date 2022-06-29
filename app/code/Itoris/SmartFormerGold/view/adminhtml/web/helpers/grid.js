function updateGrid() {
	if (document.showGrid) {
		document.getElementById('sfg_inner_container').style.background = 'url('+document.adminURL+'/images/grid-'+document.gridSize+'.gif'+')';
	} else {
		document.getElementById('sfg_inner_container').style.background = 'none';
	}
}

function bindToGrid(x, y) {
	var arr = Array(0,0);
	if (!document.bindEffect) return arr;
	arr[0] = Math.floor(x/document.gridSize)*document.gridSize;
	arr[1] = Math.floor(y/document.gridSize)*document.gridSize;
	return arr;
}

updateGrid();
