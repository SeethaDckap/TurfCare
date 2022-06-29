function resizeEditor() {
	var toolbar_box = document.getElementById('toolbar-box');
	if (toolbar_box) toolbar_box.parentNode.removeChild(toolbar_box);
    var height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    var newHeight = height - 100, newHeight = newHeight > 600 ? newHeight : 600;
    document.sfgArea.style.height = newHeight + 'px';
	document.getElementById('sfg_elements_bar').style.height = document.sfgArea.offsetHeight - document.getElementById('sfg_menu').offsetHeight - document.getElementById('sfg_top').offsetHeight + 'px';
	document.getElementById('sfg_container').style.height = document.sfgArea.offsetHeight - document.getElementById('sfg_menu').offsetHeight - document.getElementById('sfg_top').offsetHeight + 'px';
	document.getElementById('sfg_central').style.height = document.sfgArea.offsetHeight - document.getElementById('sfg_menu').offsetHeight - document.getElementById('sfg_top').offsetHeight + 'px';
	document.getElementById('sfg_container').style.width = document.sfgArea.offsetWidth - document.getElementById('sfg_elements_bar').offsetWidth + 'px';
	document.mostLeft = document.getElementById('sfg_container').offsetLeft + document.sfgArea.offsetLeft + 1;
	document.mostTop = document.getElementById('sfg_container').offsetTop + document.sfgArea.offsetTop + 1;
}

function scrollEditor() {
	document.mostLeftScrolled = document.mostLeft - document.getElementById('sfg_container').scrollLeft;
	document.mostTopScrolled = document.mostTop - document.getElementById('sfg_container').scrollTop;
}

resizeEditor();
scrollEditor();
addEvent(window, 'resize', resizeEditor);
addEvent(document.getElementById('sfg_container'),'scroll',scrollEditor);
document.brc = document.attachEvent ? 2 : 0;
document.brc1 = document.attachEvent ? 0 : 0;

