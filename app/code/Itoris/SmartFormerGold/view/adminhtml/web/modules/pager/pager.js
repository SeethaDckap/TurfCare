if (!document.form_id || parseInt(document.form_id)==0) document.sfgPages = Array();
document.pagerScrollDirection = 0;
document.currentPage = 0;

var img = document.createElement('img');
img.src = document.adminURL+'/modules/pager/left-arrow.gif';
img.className = 'pager-arrow';
img.title = lang['Scroll Left'] ? lang['Scroll Left'] : 'Scroll Left';
addEvent(img, 'dragstart', stopEvent);
addEvent(img, 'mousedown', pagerScrollLeft);
addEvent(img, 'mouseup', pagerScrollStop);
document.getElementById('sfg_top').appendChild(img);

var div = document.createElement('div');
div.className = 'pager-area';
var div2 = document.createElement('div');
div2.className = 'pager-inner-area';
div2.id = 'pager-inner-area';
div.appendChild(div2);
var div3 = document.createElement('div');
div3.className = 'page-tab';
div3.innerHTML = getTranslation('add new');
addEvent(div3, 'mousedown', pagerAddPage);
div2.appendChild(div3);

document.getElementById('sfg_top').appendChild(div);

var img = document.createElement('img');
img.src = document.adminURL+'/modules/pager/right-arrow.gif';
img.className = 'pager-arrow';
img.title = lang['Scroll Right'] ? lang['Scroll Right'] : 'Scroll Right';
addEvent(img, 'dragstart', stopEvent);
addEvent(img, 'mousedown', pagerScrollRight);
addEvent(img, 'mouseup', pagerScrollStop);
document.getElementById('sfg_top').appendChild(img);

if (!document.form_id || parseInt(document.form_id)==0) {
	for(o=0; o<10; o++) pagerAddPage(null);
	switchPage(document.currentPage);
}

function pagerScrollLeft(ev) {
	stopEvent(ev);
	document.pagerScrollDirection = 1;
	smothScroll();
}

function pagerScrollRight(ev) {
	stopEvent(ev);
	document.pagerScrollDirection = 2;
	smothScroll();
}

function pagerScrollStop (ev) {
	document.pagerScrollDirection = 0;
}

function smothScroll() {
	var pager = document.getElementById('pager-inner-area');
	if (pager.parentNode.offsetWidth > pager.offsetWidth) {
		document.pagerScrollDirection = 0;
		return;
	}
	if (document.pagerScrollDirection == 2) {
		if (pager.offsetLeft - 10 < pager.parentNode.offsetWidth - pager.offsetWidth) {
			pager.style.left = pager.parentNode.offsetWidth - pager.offsetWidth + 'px';
			document.pagerScrollDirection = 0;
			return;
		}
		pager.style.left = pager.offsetLeft - 10 + 'px';
		setTimeout('smothScroll()',30);
	}
	if (document.pagerScrollDirection == 1) {
		if (pager.offsetLeft + 10 > 0) {
			pager.style.left = '0px';
			document.pagerScrollDirection = 0;
			return;
		}
		pager.style.left = pager.offsetLeft + 10 + 'px';
		setTimeout('smothScroll()',30);
	}
}

function pagerAddPage(ev) {
	var max=0;
	var lng = lang['Page'] ? lang['Page'] : 'Page';
	//for (i=0; i<document.sfgPages.length; i++) if (parseInt(document.sfgPages[i].substr(lng.length+1))>max) max = parseInt(document.sfgPages[i].substr(lng.length+1));
	max = document.sfgPages.length;
	document.sfgPages[document.sfgPages.length] = lng + ' '+(max+1);
	var pager = document.getElementById('pager-inner-area');
	var div = document.createElement('div');
	div.className = 'page-tab';
	div.id = 'pageTab'+(document.sfgPages.length-1);
	div.innerHTML = document.sfgPages[document.sfgPages.length-1];
	addEvent(div, 'mousedown', switchPage);
	pager.insertBefore(div,pager.getElementsByTagName('div')[document.sfgPages.length-1]);
	var width = 0;
	var pages = pager.getElementsByTagName('div');
	for (i=0; i<pages.length; i++) width += pages[i].offsetWidth;
	pager.style.width = width + pages.length + 3 +'px';
	if (ev) {
		document.pagerScrollDirection = 2;
		smothScroll();
	}
}

function switchPage(ev) {
	if (typeof ev != 'object') {
		var id = ev;
		obj = document.getElementById('pageTab'+id);
	} else {
		if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
		while (obj.tagName.toLowerCase() != 'div') obj = obj.parentNode;
		var id = parseInt(obj.id.substr(7));
	}
	var pages = document.getElementById('pager-inner-area').getElementsByTagName('div');
	for (o=0; o<pages.length; o++) pages[o].className = 'page-tab';
	obj.className = 'page-tab-active';
	for (o=0; o<document.currentPageElements.length; o++) {
		document.currentPageElements[o].unSelect();
		document.getElementById('sfg_inner_container').removeChild(document.currentPageElements[o].object);
	}
	document.currentPageElements = Array();
	for (o=0; o<document.allElements.length; o++) {
		if (document.allElements[o].page == id) {
			document.currentPageElements[document.currentPageElements.length] = document.allElements[o];
			document.getElementById('sfg_inner_container').appendChild(document.allElements[o].object);
		}
	}
	document.selectedElements = Array();
	document.currentPage = id;
	showAllElementsList();
}