document.imageSelectorBasePath = '';

function showImageSelector(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	document.propertyEdit =  obj.parentNode.parentNode;
	showMask();
	var div = document.getElementById('imageSelectorBack');
	if (div) {
		div.style.visibility = 'visible';
		div.style.left = Math.floor((document.documentElement.scrollWidth - div.offsetWidth)/2) + 'px';
		return;
	}
	var div = document.createElement('div');
	div.id = 'imageSelectorBack';
	document.body.appendChild(div);
	var div2 = document.createElement('div');
	div2.id = 'imageSelectorHeader';
	div2.innerHTML = '<span>'+getTranslation('Image Selector')+'</span>';
	var img = document.createElement('img');
	img.src = document.adminURL+'/images/close.png';
	img.id = 'imageSelectorClose';
	img.title = getTranslation('Close this Bar');
	addEvent(img, 'mousedown', closeImageSelector);
	div2.appendChild(img);
	div.appendChild(div2);
	var div2 = document.createElement('div');
	div2.id = 'imageSelector';
	div.appendChild(div2);
	div.style.left = Math.floor((document.documentElement.scrollWidth - div.offsetWidth)/2) + 'px';
    jQuery('<div class="popup-footer" style="position:absolute; bottom:0; width:100%"><div align="right"><b>Upload Image:</b> <form id="upload-image-form" method="post" style="display:inline-block" enctype="multipart/form-data"><input type="file" id="upload-input" name="upload-input" style="width:240px" /></form><button type="button" class="primary" onclick="this.disabled=\'true\';uploadImage();this.disabled=\'\';"><span>'+getTranslation('Upload Here')+'</span></button></div>').appendTo(div);
	getImagesList();
}

function uploadImage() {
    if (!document.getElementById('upload-input').value) {alert(getTranslation('Please select an image')); return;}
    showLoadingImage();
    var form = document.getElementById('upload-image-form');
    var formData = new FormData(form);
    var url = document.getUploadImageUrl;
    url += (url.indexOf('?') > -1 ? '&' : '?') + 'form_key='+escape(FORM_KEY) + '&base_dir='+escape(document.imageSelectorBasePath);
    jQuery.ajax({
        url: url,
        type: 'POST',
        data: formData,
        async: true,
        success: function (data) {
            jQuery('#upload-input').remove();
            jQuery('<input type="file" id="upload-input" name="upload-input" style="width:240px" />').appendTo(form);
            hideLoadingImage();
            getImagesList();
        },
        cache: false,
        contentType: false,
        processData: false
    });
}

function updateImagesList(htm) {
	var div = document.getElementById('imageSelector');
	div.innerHTML = htm;
	var trs = div.getElementsByTagName('tr');
	for (i=0; i<trs.length; i++) {
		var img = trs[i].getElementsByTagName('img')[1];
		if (img) trs[i].title = img.src;
	}
	hideLoadingImage();	
}

function getImagesList() {
	showLoadingImage();
	var loc = document.getImageList;
	loc += '?start=' + document.imageSelectorBasePath;
	var xmlhttp = HTTPRequest(loc, false, updateImagesList);
	updateImagesList(xmlhttp.responseText);
}

function imagesListChangeLevel(path) {
	document.imageSelectorBasePath = path;
	getImagesList();
}

function applyImage(path) {
	closeImageSelector();
	var input = document.propertyEdit.getElementsByTagName('input')[0];
	var type = document.activeObject.tag;
	var type2 = document.activeObject.getProp(document.activeObject.attributes,'type')
	if (type2) type +=','+type2;
	var prop = document.propertyEdit.getElementsByTagName('div')[0].innerHTML;
	if (document.propertiesTab == 2 || in_array(prop,document.msdn[type].styles)) input.value = 'url(' + path + ')'; else input.value = path;
	input.focus();
	input.blur();
}

function closeImageSelector(ev) {
	document.getElementById('imageSelectorBack').style.visibility = 'hidden';
	hideMask();
	hideLoadingImage();
}