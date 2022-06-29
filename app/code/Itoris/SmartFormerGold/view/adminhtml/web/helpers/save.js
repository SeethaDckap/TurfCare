
document.sfdFormName = '';
document.sfdFormDescription = '';
document.formExternalAccess = [-1];
document.formInternalAccess = true;
document.formAutoResponsive = 1;
document.sfgMaxPerCustomer = 0;
document.formSubmitAjax = 1;
document.sfgEditSubmissions = 1;

function formProperties() {
	showMask();
	var div = document.createElement('div');
	div.id = 'over_mask6';
	var div2 = document.createElement('div');
	div2.id = 'over_mask_header';
	div2.innerHTML = '<span>'+getTranslation('Properties')+'</span>';
	var img = document.createElement('img');
	img.src = document.adminURL+'/images/close.png';
	img.id = 'over_maskClose';
	img.title = getTranslation('Close this Bar');
	addEvent(img, 'mousedown', closeFormProperties);
	div2.appendChild(img);
	div.appendChild(div2);
	document.body.appendChild(div);
	var div2 = document.createElement('div');
	div2.id = 'propertiesContainer';
	div.appendChild(div2);
	//div2.style.width = div2.parentNode.offsetWidth - document.brc + 'px';
	//div2.style.height = div2.parentNode.offsetHeight - document.getElementById('over_mask_header').offsetHeight - document.brc + 'px';
	div2.innerHTML = '<table cellpadding=0 cellspacing=2 border=0 style="width:100%;line-height: 28px;"><tr><td align="left" valign="top" width="70%"></td><td align="left" valign="top"></td></tr></table>';
	div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].innerHTML='<b style="color:blue; margin-right:5px;">'+getTranslation('Form Name')+':</b><input type="text" style="width:250px" onkeyup="document.sfdFormName=this.value"><br /><b style="color:blue; margin-right:5px;">'+getTranslation('Form Description')+':</b><br /><textarea style="width:95%; height:100px;" onkeyup="document.sfdFormDescription=this.value"></textarea><br />';
    div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].innerHTML += '<b style="color:blue; margin-right:5px;">'+getTranslation('Maximum submissions per customer')+':</b><input type="text" style="width:50px" onkeyup="document.sfgMaxPerCustomer=this.value" />&nbsp;(<span>'+getTranslation('0 - unlimited')+'</span>)<br />';
	div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].innerHTML += '<b style="color:blue">'+getTranslation('Allow to edit submissions')+':</b>&nbsp;&nbsp;<input type="radio" name="sfg_edit_submissions" '+(document.sfgEditSubmissions?'checked':'')+' onclick="if (this.checked) document.sfgEditSubmissions=1;">&nbsp;<b>'+getTranslation('Yes')+'</b>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="sfg_edit_submissions" '+(!document.sfgEditSubmissions?'checked':'')+' onclick="if (this.checked) document.sfgEditSubmissions=0;">&nbsp;<b>'+getTranslation('No')+'</b><br />';
	div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].innerHTML += '<b style="color:blue">'+getTranslation('Submit form via AJAX')+':</b>&nbsp;&nbsp;<input type="radio" name="sfg_submit_ajax" '+(document.formSubmitAjax?'checked':'')+' onclick="if (this.checked) document.formSubmitAjax=1;">&nbsp;<b>'+getTranslation('Yes')+'</b>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="sfg_submit_ajax" '+(!document.formSubmitAjax?'checked':'')+' onclick="if (this.checked) document.formSubmitAjax=0;">&nbsp;<b>'+getTranslation('No')+'</b><br />';
	div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].innerHTML += '<div id="dbCheck"></div>';
    div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].getElementsByTagName('input')[0].value = document.sfdFormName;
	div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].getElementsByTagName('textarea')[0].value = document.sfdFormDescription;
	div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].getElementsByTagName('input')[1].value = document.sfgMaxPerCustomer;
	if (document.sfgDB.name=='') {
		document.getElementById('dbCheck').innerHTML='<b style="color:red">'+getTranslation('The form is not connected with the database!')+'</b><input type="button" style="margin-left:5px;" value="'+getTranslation('Connect Now')+'" onclick="closeFormProperties(); showDBEditor();">';
	} else {
		document.getElementById('dbCheck').innerHTML='<b style="color:blue">'+getTranslation('The form is connected to DB table')+':</b> <b>'+document.sfgDB.name+'</b>';
	}
    var access = '<b style="color:blue">'+getTranslation('External Access to the form')+':</b><br />';
    access += '<select id="external_access" multiple="multiple">';
    for(var i=0; i<document.customerGroups.length; i++) {
        var value = document.customerGroups[i]['value'], label = document.customerGroups[i]['label'];
        if (value > 10000) value = "-1";
        access += '<option value="'+value+'"'+(document.formExternalAccess.indexOf(value) > -1 ? ' selected="selected"' : '')+'>'+label+'</option>';
    }
    access += '</select><br />';
    div2.getElementsByTagName('table')[0].getElementsByTagName('td')[1].innerHTML = access;
	div2.getElementsByTagName('table')[0].getElementsByTagName('td')[1].innerHTML+='<b style="color:blue">'+getTranslation('Auto-responsive form')+':</b><br /><input type="radio" name="sfg_autoresponsive" '+(document.formAutoResponsive?'checked':'')+' onclick="if (this.checked) document.formAutoResponsive=1;">&nbsp;<b>'+getTranslation('Yes')+'</b>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="sfg_autoresponsive" '+(!document.formAutoResponsive?'checked':'')+' onclick="if (this.checked) document.formAutoResponsive=0;">&nbsp;<b>'+getTranslation('No')+'</b>';
    div2.getElementsByTagName('table')[0].getElementsByTagName('td')[0].getElementsByTagName('input')[0].focus();
    jQuery('#external_access').on('change', function(){
        document.formExternalAccess = jQuery('#external_access').val();
        if (document.formExternalAccess === null || document.formExternalAccess.length == 0) {
            jQuery('#external_access option[value="-1"]')[0].selected = true;
            document.formExternalAccess = jQuery('#external_access').val();
        }
    });
    jQuery('<div class="popup-footer"><div align="right"><button type="button" class="primary" onclick="this.disabled=\'true\';saveForm();this.disabled=\'\';"><span>'+getTranslation('Save Form')+'</span></button></div>').appendTo(div);

}

function closeFormProperties() {
	var div = document.getElementById('over_mask6');
	if (div) div.parentNode.removeChild(div);
	hideMask();
	hideLoadingImage();
}

function encodeXMLString(str) {
	if (!str) return '';
	if (typeof str == 'boolean' && str == true) str = 'true';
	str = str.replace(/&/g,'&amp;');
	str = str.replace(/</g,'&lt;');
	str = str.replace(/>/g,'&gt;');
	return str;
}

function addslashes(str) {
	if (!str) return '';
	if (typeof str == 'boolean' && str == true) str = 'true';
	str = encodeXMLString(str);
	str = str.replace(/\"/g,'&quot;');
	return str;
}

function saveForm() {
	if (document.sfdFormName=='') {
		if (!document.getElementById('propertiesContainer')) formProperties();
		hideLoadingImage();
		alert(getTranslation('Please specify the form name'));
		return;
	}   
    
    var allElements = [];
    for (i=0; i<document.allElements.length; i++) {
        var _element = document.allElements[i];
        allElements[i] = {
            alias: _element.alias,
            tag: _element.tag,
            page: _element.page,
            attributes: _element.attributes,
            events: _element.events,
            styles: _element.styles,
            params: _element.params,
            content: _element.content,
            contentphp: _element.contentPHP
        }
    }
    
    formObject = {
        name: document.sfdFormName,
        ext_access: document.formExternalAccess,
        autoresponsive: document.formAutoResponsive,
        maxsubmissions: document.sfgMaxPerCustomer,
        submitajax: document.formSubmitAjax,
        allow_editing: document.sfgEditSubmissions,
        description: document.sfdFormDescription,
        globalphp: document.sfg_php,
        globaljs: document.sfg_js,
        globalhtml: document.sfg_html,
        globalcss: document.sfg_css,
        database: {
            name: document.sfgDB.name,
            map: document.sfgDBMapping
        },
        pages: document.sfgPages,
        elements: allElements,
        email_templates: document.emailTemplates,
        validators: document.validators
    }
	
    jQuery.ajax({
        url: document.saveFormUrl,
        data: {
            content: JSON.stringify(formObject),
            description: document.sfdFormDescription,
            name: document.sfdFormName,
            fid: document.form_id
        },
        type: "POST"
    }).done(function (response) {
        //if (document.getElementById('xmlText')) {
        //    document.getElementById('xmlText').innerHTML = encodeXMLString(JSON.stringify(formObject));
        //}

        if (document.form_id==0 && parseInt(response) > 0) document.form_id = parseInt(response);
        if (parseInt(response) > 0) response = response.substr(response.indexOf(' '));
        hideLoadingImage();
        alertSfg(response);
    });
}

function stripslashes(str) {
	for (i=str.length-1; i>=0; i--) {
		if (str.substr(i,2)='\"') str = str.substring(0,i)+str.substr(i+1);
	}
	return str;
}

SFG_InterimElement = function(tag,page,alias,content,contentphp,attributes,styles,events,params) {
	this.tag=tag;
	this.page=page;
	this.alias=alias;
	this.content=content;
	this.contentPHP=contentphp;
	this.attributes=attributes;
	this.styles=styles;
	this.events=events;
	this.params=params;
}

function loadForm() {
	showLoadingImage();
    jQuery.getJSON(document.loadFormUrl + '?fid='+document.form_id+'&tmp='+Math.random(), function(formObject){
        document.sfdFormName = formObject.name;
        document.formExternalAccess = formObject.ext_access;
        if (!Array.isArray(document.formExternalAccess)) document.formExternalAccess = [-1];
        document.formAutoResponsive = formObject.autoresponsive;
        document.sfdFormDescription = formObject.description;
        document.formSubmitAjax = formObject.submitajax;
        document.sfgMaxPerCustomer = formObject.maxsubmissions;
        document.sfgEditSubmissions = formObject.allow_editing;
        document.sfgDB.name = formObject.database.name;
        document.sfgDBMapping = formObject.database.map; 
        for (i=0; i<document.bdTables.length; i++) {
            if (document.bdTables[i].toLowerCase()==document.bdPrefix.toLowerCase()+formObject.database.name.toLowerCase().replace('#__','') || document.bdTables[i].toLowerCase()==formObject.database.name.toLowerCase()) { getTableInfo(i); break; }
        }
        if (document.sfgDB.fields.length > 0) {
            for (i=0; i<document.sfgDBMapping.length; i++) {
                for (o=0; o<document.sfgDB.fields.length; o++) if (document.sfgDB.fields[o].field.toLowerCase()==document.sfgDBMapping[i][0].toLowerCase()) {
                    document.sfgDB.fields[o].initialField = document.sfgDBMapping[i][0].toLowerCase();
                    document.sfgDB.fields[o].sfgField = document.sfgDBMapping[i][1].toLowerCase();
                    break;
                }
            }
        }
        //console.log(document.sfgDBMapping);
        //console.log(document.sfgDB.fields);
        document.sfg_php = formObject.globalphp;
        document.sfg_css = formObject.globalcss;
        document.sfg_js = formObject.globaljs;
        document.sfg_html = formObject.globalhtml;
        document.sfgPages = [];
        for(var i=0; i<formObject.pages.length; i++) pagerAddPage(null);
        for (w=0; w<formObject.elements.length; w++) {
            var _element = formObject.elements[w];
            document.allElements[document.allElements.length] = new SFG_Element(
                new SFG_InterimElement(_element.tag, _element.page, _element.alias, _element.content, _element.contentphp, _element.attributes, _element.styles, _element. events, _element.params)
            );
            document.allElements[document.allElements.length-1].page = _element.page;
            document.allElements[document.allElements.length-1].alias = _element.alias;            
        }
        document.emailTemplates = formObject.email_templates;
        document.validators = formObject.validators;
        switchPage(1);
        switchPage(0);
        hideLoadingImage();
    });
}
