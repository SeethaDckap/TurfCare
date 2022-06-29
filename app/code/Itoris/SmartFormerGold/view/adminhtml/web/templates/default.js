function resizeEditor() {
	var toolbar_box = document.getElementById('toolbar-box');
	if (toolbar_box) toolbar_box.parentNode.removeChild(toolbar_box);
	document.getElementById('sfg_elements_bar').style.height = document.sfgArea.offsetHeight - document.getElementById('sfg_menu').offsetHeight - document.getElementById('sfg_top').offsetHeight - 2 + 'px';
	document.getElementById('sfg_container').style.height = document.sfgArea.offsetHeight - document.getElementById('sfg_menu').offsetHeight - document.getElementById('sfg_top').offsetHeight - 4 + 'px';
	document.getElementById('sfg_central').style.height = document.sfgArea.offsetHeight - document.getElementById('sfg_menu').offsetHeight - document.getElementById('sfg_top').offsetHeight - 2 + 'px';
	document.getElementById('sfg_container').style.width = document.sfgArea.offsetWidth - document.getElementById('sfg_elements_bar').offsetWidth - 2 + 'px';
	document.mostLeft = document.getElementById('sfg_container').offsetLeft + document.sfgArea.offsetLeft + 1;
	document.mostTop = document.getElementById('sfg_container').offsetTop + document.sfgArea.offsetTop + 1;
}

function scrollEditor() {
	document.mostLeftScrolled = document.mostLeft - document.getElementById('sfg_container').scrollLeft;
	document.mostTopScrolled = document.mostTop - document.getElementById('sfg_container').scrollTop;
}

if (!document.getElementById('sfg_elements_bar')) {
	document.location = document.siteURL+'/administrator/index.php?option=com_sfg&task=editform&fid='+document.form_id+'&token='+Math.random();
} else {
	resizeEditor();
	scrollEditor();
	addEvent(window,'resize',resizeEditor);
	addEvent(document.getElementById('sfg_container'),'scroll',scrollEditor);
	document.brc = document.attachEvent ? 2 : 0;
	document.brc1 = document.attachEvent ? 0 : 0;
}
var logo = document.createElement('div');
logo.id = 'sfg_logo';
logo.innerHTML='<img src="'+document.adminURL+'/images/logo.gif'+'" />';
logo.title = 'IToris Inc. Website development company.';
//document.getElementById('sfg_top').appendChild(logo);

