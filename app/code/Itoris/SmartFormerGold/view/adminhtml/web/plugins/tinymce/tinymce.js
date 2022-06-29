document.write('<script language="Javascript" type="text/javascript" src="'+document.adminURL+'/plugins/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>');
document.emailTemplates = Array();
document.activeTemplate = -1;
document.currentFocus = null;
document.leaveProperties = 1;
document.templateId = 0;
document.template_name = '';
document.template_from_name = '';
document.template_email = '';
document.template_subject = '';
document.template_cc = '';
document.template_format = '';
document.template_bcc = '';

SFG_EmailTemplate = function (name, body, fromName, fromEmail, subject, cc, bcc, format) {
	var max = 0;
	for (p=0; p<document.emailTemplates.length; p++) if (document.emailTemplates[p].id > max) max = document.emailTemplates[p].id;
	this.id = max+1;
	this.name = name;
	this.fromName = fromName;
	this.fromEmail = fromEmail;
	this.subject = subject;
	this.cc = cc;
	this.bcc = bcc;
	this.body = body;
	this.format = format;
}

function showPropertyWYSIWYG(ev) {
	showLoadingImage();
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	document.propertyEdit = obj.parentNode.parentNode;
	showMask();
	var div = document.getElementById('over_mask4');
	if (!div) {
		var div = document.createElement('div');
		div.id = 'over_mask4';
		var div2 = document.createElement('div');
		div2.id = 'over_mask_header';
		div2.innerHTML = '<span>'+getTranslation('WYSIWYG editor')+'</span>';
		var img = document.createElement('img');
		img.src = document.adminURL+'/images/close.png';
		img.id = 'over_maskClose';
		img.title = getTranslation('Close this Bar');
		addEvent(img, 'mousedown', closePropertyWYSIWYG);
		div2.appendChild(img);
		div.appendChild(div2);
		var textarea = document.createElement('textarea');
		textarea.id = 'sfg_propertytinymce';
		textarea.className = "mce_editable2";
		div.appendChild(textarea);
		document.body.appendChild(div);
		div.style.top = div.offsetTop + document.documentElement.scrollTop + 'px';
	} else {
		div.style.visibility = 'visible';
		document.getElementById('over_mask_header').getElementsByTagName('div')[0].innerHTML = getTranslation('WYSIWYG editor');
		textarea = div.getElementsByTagName('textarea')[0];
	}
	textarea.style.height = textarea.parentNode.offsetHeight - document.getElementById('over_mask_header').offsetHeight - 70 + 'px';
	textarea.style.width = '100%';//textarea.parentNode.offsetWidth - 2 - document.brc + 'px';
	tinyMCE.init({
		// General options
		mode : "textareas",
		editor_selector : "mce_editable2",
		elements : "sfg_propertytinymce",
		theme : "advanced",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
		language: document.tinyMCELang,

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false,

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		oninit: "propertyWYSIWYGLoaded",
		//cleanup_callback: "newTemplate",
		//save_callback: "saveTemplate",

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
    jQuery('<div class="popup-footer" style="position:absolute; bottom:0; left:0; right:0;"><div align="right"><button type="button" class="" onclick="savePropertyWYSIWYG(); closePropertyWYSIWYG(event);"><span>'+getTranslation('Save & Close')+'</span></button><button type="button" class="" onclick="savePropertyWYSIWYG()"><span>'+getTranslation('Save & Continue Edit')+'</span></button><button type="button" class="primary" onclick="savePropertyWYSIWYG(); saveForm()"><span>'+getTranslation('Save HTML & Save Form')+'</span></button></div>').appendTo(div);

}

function propertyWYSIWYGLoaded() {
	var save = document.getElementById('sfg_propertytinymce_save');
	while (save.tagName.toLowerCase() != 'td') save = save.parentNode;
	var tmp = save.innerHTML;
	save.innerHTML = tmp;
	addEvent(save,'click', savePropertyWYSIWYG);
	tinyMCE.activeEditor.setContent(document.activeObject.content);
	hideLoadingImage();
}

function savePropertyWYSIWYG(ev) {
	var prop = document.propertyEdit.getElementsByTagName('div')[0].innerHTML;
	var html = tinyMCE.activeEditor.getContent();
	document.activeObject.content = html;
	document.propertyEdit.getElementsByTagName('textarea')[0].value = document.activeObject.content;
	document.activeObject.applyContent();
}

function closePropertyWYSIWYG(ev) {
	var div = document.getElementById('over_mask4');
	tinyMCE.activeEditor.remove();
	div.innerHTML='';
	div.parentNode.removeChild(div);
	if (div) div.style.visibility = 'hidden';
	hideMask();
}

function showTemplateEditor() {
	showLoadingImage();
	showMask();
	var div = document.getElementById('over_mask2');
	if (!div) {
		var div = document.createElement('div');
		div.id = 'over_mask2';
		var div2 = document.createElement('div');
		div2.id = 'over_mask_header';
		div2.innerHTML = '<span>'+getTranslation('Email Templates')+'</span>';
		var img = document.createElement('img');
		img.src = document.adminURL+'/images/close.png';
		img.id = 'over_maskClose';
		img.title = getTranslation('Close this Bar');
		addEvent(img, 'mousedown', closeTemplateEditor);
		div2.appendChild(img);
		div.appendChild(div2);
		div2 = document.createElement('div');
        div2.style.cssText = 'position:absolute; top:40px; bottom:50px; left:5px; right:5px;';
		div2.innerHTML = '<table cellpadding=0 cellspacing=1 border=0 style="width:100%; height:100%; background-color:#ffffff"><tr><td align=left valign=top style="width:200px"></td><td align=left valign=top><div style="display:inline-block; width:100px;"><b style="color:red">'+getTranslation('Template Alias')+': </b></div><input type="text" id="template_name" style="width:250px; margin-bottom:2px;" />&nbsp;[<b style="color:blue; cursor:pointer" onclick="removeTemplate()">'+getTranslation('Remove Template')+'</b>]<br /><div style="display:inline-block; width:100px;"><b style="color:red">'+getTranslation('From Name')+': </b></div><input type="text" id="template_from_name" style="width:250px; margin-bottom:2px;" onfocus="document.currentFocus=this" />&nbsp;<b>'+getTranslation('Format')+':&nbsp;</b><select id="template_format" onchange="document.emailTemplates[document.activeTemplate].format=this.value"><option value="1">HTML<option value="0">'+getTranslation('Plain Text')+'</select><br /><div style="display:inline-block; width:100px;"><b style="color:red">'+getTranslation('From Email')+': </b></div><input type="text" id="template_email" style="width:250px; margin-bottom:2px;" onfocus="document.currentFocus=this" /><br /><div style="display:inline-block; width:100px;"><b style="color:red">'+getTranslation('Subject')+': </b></div><input type="text" id="template_subject" style="width:250px; margin-bottom:2px;" onfocus="document.currentFocus=this" /><br /><div style="display:inline-block; width:100px;"><b style="color:red">'+getTranslation('CC')+': </b></div><input type="text" id="template_cc" style="width:250px; margin-bottom:2px;" onfocus="document.currentFocus=this" /><br /><div style="display:inline-block; width:100px;"><b style="color:red;">'+getTranslation('BCC')+': </b></div><input type="text" id="template_bcc" style="width:250px; margin-bottom:2px;" onfocus="document.currentFocus=this" /><b style="margin-left:10px;">'+getTranslation('Insert Field')+': </b><select id="insert_field" onchange="insert_field(this.value)"></select><br /></td></tr></table>';
		div.appendChild(div2);
		var textarea = document.createElement('textarea');
		textarea.id = 'sfg_tinymce';
		textarea.className = "mce_editable";
		div2.getElementsByTagName('td')[1].appendChild(textarea);
		document.body.appendChild(div);
		div.style.top = div.offsetTop + document.documentElement.scrollTop + 'px';
		div2 = document.createElement('div');
		div2.id = 'templateLister';
		div.getElementsByTagName('td')[0].appendChild(div2);
		refreshTemplatesList();
	} else {
		div.style.visibility = 'visible';
		document.getElementById('over_mask_header').getElementsByTagName('div')[0].innerHTML = getTranslation('Email Templates');
		textarea = div.getElementsByTagName('textarea')[0];
	}
	while (document.getElementById('insert_field').options.length>0) document.getElementById('insert_field').remove(0);
	var values = Array();
	for (p=0; p<document.allElements.length; p++) {
		var name = document.allElements[p].getProp(document.allElements[p].attributes, 'name');
		if (name && name.indexOf('[')>-1) name = name.substr(0,name.indexOf('['));
		if (name && name!='') values[values.length] = Array(name, document.allElements[p].alias);
	}
	var option = document.createElement('option');
	option.value = '';
	option.innerHTML = '-- '+getTranslation('available fields')+' --';
	document.getElementById('insert_field').appendChild(option);
	for (o=0; o<values.length; o++) {
		var option = document.createElement('option');
		option.value = values[o][0];
		if (values[o][1] == '') values[o][1]='no SFG Alias';
		option.innerHTML = values[o][0]+' ('+values[o][1]+')';
		document.getElementById('insert_field').appendChild(option);
	}
	//div.getElementsByTagName('table')[0].style.height = div.offsetHeight + 'px';
	document.getElementById('templateLister').style.height = div.getElementsByTagName('td')[1].offsetHeight + 'px';
	document.getElementById('templateLister').style.width = '190px';
    textarea.style.cssText = 'width:100%; height:'+(textarea.parentNode.offsetHeight - textarea.offsetTop - 16)+'px';
	tinyMCE.init({
		// General options
		mode : "textareas",
		editor_selector : "mce_editable",
		elements : "sfg_tinymce",
		theme : "advanced",
		language: document.tinyMCELang,
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false,

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		oninit: "templateEditorLoaded",
		handle_event_callback: "setMCEFocus",
		cleanup_callback: "newTemplate",
		//save_callback: "saveTemplate",

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
    jQuery('<div class="popup-footer" style="position:absolute; bottom:0; left:0; right:0;"><div align="right"><button type="button" class="" onclick="saveTemplate(); closeTemplateEditor(event);"><span>'+getTranslation('Save & Close')+'</span></button><button type="button" class="" onclick="saveTemplate()"><span>'+getTranslation('Save & Continue Edit')+'</span></button><button type="button" class="primary" onclick="saveTemplate(); saveForm()"><span>'+getTranslation('Save Template & Save Form')+'</span></button></div>').appendTo(div);

}

function setMCEFocus(ev) {
	document.currentFocus=null;
}

function insert_field(name) {
	if (name=='') return;
	document.getElementById('insert_field').selectedIndex = 0;
	if (!document.currentFocus) {
		tinyMCE.execInstanceCommand('sfg_tinymce', 'mceInsertContent',false,'{{'+name+'}}');
	} else {
        if (document.selection) {
            document.currentFocus.focus();
            sel = document.selection.createRange();
            sel.text = '{{'+name+'}}';
        } else if (document.currentFocus.selectionStart || document.currentFocus.selectionStart == "0") {
            var startPos = document.currentFocus.selectionStart;
            var endPos = document.currentFocus.selectionEnd;
            var messageln = document.currentFocus.value;
            document.currentFocus.value = messageln.substring(0, startPos) + '{{'+name+'}}' + messageln.substring(endPos, messageln.length);
        } else {
            document.currentFocus.value += '{{'+name+'}}';
        }
        document.currentFocus.focus();
	}
}

function refreshTemplatesList() {
	div = document.getElementById('templateLister');
	div.innerHTML = '';
	var c = 0;
	for(i=0; i<document.emailTemplates.length; i++) {
		var div2 = document.createElement('div');
		div2.className = 'templateName'+c;
		if (document.activeTemplate == i) div2.className = 'templateNameSelected';
		div2.id = 'template'+i;
		c = 1 - c;
		div2.innerHTML = document.emailTemplates[i].name+' ('+document.emailTemplates[i].id+')';
		div.appendChild(div2);
		addEvent(div2, 'click', selectTemplate);
	}
}

function newTemplate(type, value) {
	if ((type == 'insert_to_editor')&&(value=='')) {
		document.activeTemplate = -1;
		document.getElementById('template_name').value = '';
		document.getElementById('template_from_name').value = '';
		document.getElementById('template_email').value = '';
		document.getElementById('template_subject').value = '';
		document.getElementById('template_cc').value = '';
		document.getElementById('template_format').selectedIndex = 0;
		document.getElementById('template_bcc').value = '';
		refreshTemplatesList();
		return value;
	}
	return value;
}

function removeTemplate() {
	if (confirm(getTranslation('Are you sure want to remove this email template?'))) {
		if (document.activeTemplate > -1) {
			document.emailTemplates.splice(document.activeTemplate,1);
		}
		tinyMCE.activeEditor.setContent('');
		document.getElementById('template_name').value = '';
		document.getElementById('template_from_name').value = '';
		document.getElementById('template_email').value = '';
		document.getElementById('template_subject').value = '';
		document.getElementById('template_cc').value = '';
		document.getElementById('template_format').selectedIndex = 0;
		document.getElementById('template_bcc').value = '';
	}
}

function selectTemplate(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	document.activeTemplate = obj.id.substr(8)-0;
	tinyMCE.activeEditor.setContent(document.emailTemplates[document.activeTemplate].body);
	document.activeTemplate = obj.id.substr(8)-0;
	document.getElementById('template_name').value = document.emailTemplates[document.activeTemplate].name;
	document.getElementById('template_from_name').value = document.emailTemplates[document.activeTemplate].fromName;
	document.getElementById('template_email').value = document.emailTemplates[document.activeTemplate].fromEmail;
	document.getElementById('template_subject').value = document.emailTemplates[document.activeTemplate].subject;
	document.getElementById('template_cc').value = document.emailTemplates[document.activeTemplate].cc;
	document.getElementById('template_format').selectedIndex = (1-document.emailTemplates[document.activeTemplate].format);
	document.getElementById('template_bcc').value = document.emailTemplates[document.activeTemplate].bcc;
	refreshTemplatesList();
}

function closeTemplateEditor(ev) {
	var div = document.getElementById('over_mask2');
	tinyMCE.activeEditor.remove();
	div.innerHTML='';
	div.parentNode.removeChild(div);
	if (div) div.style.visibility = 'hidden';
	hideMask();
}

function templateEditorLoaded() {
	var save = document.getElementById('sfg_tinymce_save');
	while (save.tagName.toLowerCase() != 'td') save = save.parentNode;
	var tmp = save.innerHTML;
	save.innerHTML = tmp;
	addEvent(save,'click', saveTemplate);
	if (document.emailTemplates.length>0) {
		tinyMCE.activeEditor.setContent(document.emailTemplates[0].body);
		document.getElementById('template_name').value = document.emailTemplates[0].name;
		document.getElementById('template_from_name').value = document.emailTemplates[0].fromName;
		document.getElementById('template_email').value = document.emailTemplates[0].fromEmail;
		document.getElementById('template_subject').value = document.emailTemplates[0].subject;
		document.getElementById('template_cc').value = document.emailTemplates[0].cc;
		document.getElementById('template_format').selectedIndex = (1-document.emailTemplates[0].format);
		document.getElementById('template_bcc').value = document.emailTemplates[0].bcc;
		refreshTemplatesList();
		document.activeTemplate = 0;
		refreshTemplatesList();
	} else document.activeTemplate = -1;
	hideLoadingImage();
	//addEvent(document.getElementById('sfg_tinymce_ifr'),'focus',setMCEFocus);
	//addEvent(document.getElementById('sfg_tinymce_parent'),'mousedown',setMCEFocus);
}

function saveTemplate(ev) {
	//stopEvent(ev);
	var name = document.getElementById('template_name').value.replace(/^\s+|\s+$/g,"");
	var fromName = document.getElementById('template_from_name').value.replace(/^\s+|\s+$/g,"");
	var fromEmail = document.getElementById('template_email').value.replace(/^\s+|\s+$/g,"");
	var subject = document.getElementById('template_subject').value.replace(/^\s+|\s+$/g,"");
	var cc = document.getElementById('template_cc').value.replace(/^\s+|\s+$/g,"");
	var format = document.getElementById('template_format').value;
	var bcc = document.getElementById('template_bcc').value.replace(/^\s+|\s+$/g,"");
	if (name == '') name = 'No name';
	var body = tinyMCE.activeEditor.getContent();
	if (document.activeTemplate == -1) {
		document.activeTemplate = document.emailTemplates.length;
		document.emailTemplates[document.activeTemplate] = new SFG_EmailTemplate(name, body, fromName, fromEmail, subject, cc, bcc, format);
	} else {
		document.emailTemplates[document.activeTemplate].name = name;
		document.emailTemplates[document.activeTemplate].fromName = fromName;
		document.emailTemplates[document.activeTemplate].fromEmail = fromEmail;
		document.emailTemplates[document.activeTemplate].subject = subject;
		document.emailTemplates[document.activeTemplate].cc = cc;
		document.emailTemplates[document.activeTemplate].format = format;
		document.emailTemplates[document.activeTemplate].bcc = bcc;
		if (body=='<!--{###SfgEmptyValue###}-->') body='';
		document.emailTemplates[document.activeTemplate].body = body;
	}
	refreshTemplatesList();
	alert(getTranslation('The email template has been saved'));
}

var xmlDoc = loadXMLFile(document.adminURL+'/defaults/e-templates.xml');
var templates = xmlDoc.documentElement.getElementsByTagName('email_template');

for (i=0; i<templates.length; i++) {
	var name=templates[i].getAttribute('name');
	var from_name=templates[i].getAttribute('from_name');
	var from_email=templates[i].getAttribute('from_email');
	var subject=templates[i].getAttribute('subject');
	var cc=templates[i].getAttribute('cc');
	var format=templates[i].getAttribute('format');
	var bcc=templates[i].getAttribute('bcc');
	var id=parseInt(templates[i].getAttribute('id'));
	var body = templates[i].text ? templates[i].text : templates[i].textContent ? templates[i].textContent.replace(/^\s+|\s+$/g,"") : '';
	document.emailTemplates[document.emailTemplates.length] = new SFG_EmailTemplate(name, body, from_name, from_email, subject, cc, bcc, format);
	document.emailTemplates[document.emailTemplates.length-1].id = id;
}

var xmlhttp = HTTPRequest(document.adminURL+'/defaults/globalphp.txt', false, null);
document.sfg_php = xmlhttp.responseText;
var xmlhttp = HTTPRequest(document.adminURL+'/defaults/globalhtml.txt', false, null);
document.sfg_html = xmlhttp.responseText;
var xmlhttp = HTTPRequest(document.adminURL+'/defaults/globaljs.txt', false, null);
document.sfg_js = xmlhttp.responseText;
var xmlhttp = HTTPRequest(document.adminURL+'/defaults/globalcss.txt', false, null);
document.sfg_css = xmlhttp.responseText;

