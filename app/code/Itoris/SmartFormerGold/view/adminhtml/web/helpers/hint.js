function showHint(ev) {
	if (!mouse || !mouse['x']) return;
	var hint = document.getElementById('sfg_hint');
	if (document.tmpObject) {
		if (hint) hint.parentNode.removeChild(hint);
		return;
	}
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	var title = obj.title;
	obj.title = '';
	if (!hint) {
		hint = document.createElement('div');
		hint.id = 'sfg_hint';
		document.body.appendChild(hint);
	}
	hint.style.left = mouse['x'] + 15 + 'px';
	hint.style.top = mouse['y'] + 10 + 'px';
	if (!title) return;
	hint.innerHTML = title;
}

function hideHint(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	var hint = document.getElementById('sfg_hint');
	if (hint && obj) obj.title = hint.innerHTML;
	if (hint && hint.parentNode != obj.parentNode) hint.parentNode.removeChild(hint);	
}