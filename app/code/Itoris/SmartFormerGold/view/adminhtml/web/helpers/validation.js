SFG_Validator = function (alias, js, php) {
	this.alias = alias;
	this.js = js;
	this.php = php;
}

document.validators = Array();

function showValidators() {
	if (document.validators.length > 0) showLoadingImage();
	showMask();
	document.loadedValidators = 0;
	var div = document.getElementById('over_mask3');
	if (div) div.style.visibility = 'visible';
	if (!div) {
		var div = document.createElement('div');
		div.id = 'over_mask3';
		var div2 = document.createElement('div');
		div2.id = 'over_mask_header';
		div2.innerHTML = '<span>'+getTranslation('Validation Rules')+'</span>';
		var img = document.createElement('img');
		img.src = document.adminURL+'/images/close.png';
		img.id = 'over_maskClose';
		img.title = getTranslation('Close this Bar');
		addEvent(img, 'mousedown', closeValidationEditor);
		div2.appendChild(img);
		div.appendChild(div2);
		var div2 = document.createElement('div');
		div2.id = 'validatorEditor';
        div2.style.cssText = 'position:absolute; top:35px; bottom:50px; left:0px; right:0px;';
		div.appendChild(div2);
		document.body.appendChild(div);
		//document.getElementById('validatorEditor').style.height = document.getElementById('over_mask3').offsetHeight - document.getElementById('over_mask_header').offsetHeight + 'px';
		//document.getElementById('validatorEditor').style.width = document.getElementById('over_mask3').offsetWidth - 10 + 'px';
		
        jQuery('<div class="popup-footer" style="position:absolute; bottom:0; left:0; right:0;"><div align="right"><button type="button" class="" style="float:left" onclick="loadDefaultValidators(1)"><span>'+getTranslation('Load Default')+'</span><button type="button" class="" onclick="addValidator()"><span>'+getTranslation('Add one more')+'</span></button><button type="button" class="" onclick="saveValidators(); closeValidationEditor()"><span>'+getTranslation('Save & Close')+'</span></button><button type="button" class="" onclick="saveValidators();"><span>'+getTranslation('Save & Continue Edit')+'</span></button><button type="button" class="primary" onclick="saveValidators(); saveForm()"><span>'+getTranslation('Save Validators & Save Form')+'</span></button></div>').appendTo(div);

        /*div2 = document.createElement('div');
		div2.id = "validatorToolbar";
		div2.innerHTML = '<input type="button" value="'+getTranslation('Save')+'" onclick="saveValidators()" />&nbsp;<input type="button" value="'+getTranslation('Add one more')+'" onclick="addValidator()" />&nbsp;<input type="button" value="'+getTranslation('Load Default')+'" onclick="loadDefaultValidators(1)" />';
		div.appendChild(div2);*/

    }
	for (i=0; i<document.validators.length; i++) setValidatorArea(i);
}

function setValidatorArea(n) {
		var div2 = document.getElementById('validatorEditor');
		if (!document.getElementById('validatorName'+n)) {
            var block = document.createElement('div');
            block.className = 'validationBlock';
            div2.appendChild(block);
			var div3 = document.createElement('div');
			div3.className = 'validatorName';
			div3.innerHTML = '<b>'+getTranslation('Alias')+': </b><input type="text" id="validatorName'+n+'" '+((n<2 || document.validators[n].alias=='Check identical')?'readonly style="background-color:#dddddd;"':'')+' />'+((n>1 && document.validators[n].alias!='Check identical')?'&nbsp; [<b style="color:blue; cursor:pointer" onclick="removeValidator(event,0)">'+getTranslation('Remove this rule')+'</b>]':'');
			div3.getElementsByTagName('input')[0].value = document.validators[n].alias;
			block.appendChild(div3);
			var div3 = document.createElement('div');
			div3.className = 'validatorArea';
			div3.innerHTML = '<b>'+getTranslation('PHP script')+': </b><br /><textarea id="validatorPHP'+n+'" style="width:100%; height:70px"></textarea>';
			div3.getElementsByTagName('textarea')[0].value = document.validators[n].php;
			block.appendChild(div3);
			var div3 = document.createElement('div');
			div3.className = 'validatorArea';
			div3.innerHTML = '<b>'+getTranslation('JS script')+': </b><br /><textarea id="validatorJS'+n+'" style="width:100%; height:70px"></textarea>';
			div3.getElementsByTagName('textarea')[0].value = document.validators[n].js;
			block.appendChild(div3);
		} else {
			document.getElementById("validatorPHP"+n).parentNode.style.display = 'block';
			document.getElementById("validatorJS"+n).parentNode.style.display = 'block';
			document.getElementById("validatorName"+n).parentNode.style.display = 'block';
			document.getElementById("validatorName"+n).value = document.validators[n].alias;
			document.getElementById("validatorPHP"+n).value = document.validators[n].php;
			document.getElementById("validatorJS"+n).value = document.validators[n].js;
		}
		editAreaLoader.init({
			id: "validatorPHP"+n	// id of the textarea to transform	
			,start_highlight: true	
			,font_size: "8"
			,font_family: "verdana, monospace"
			,allow_resize: "n"
			,allow_toggle: false
			,language: "en"
			,syntax: 'php'	
			,toolbar: "charmap, |, search, go_to_line, |, undo, redo, |, select_font, |, change_smooth_selection, highlight, reset_highlight, |, help"
			,EA_load_callback: "validatorAreaLoaded"
			,plugins: "charmap"
			,charmap_default: "arrows"
		});
		editAreaLoader.init({
			id: "validatorJS"+n	// id of the textarea to transform	
			,start_highlight: true	
			,font_size: "8"
			,font_family: "verdana, monospace"
			,allow_resize: "n"
			,allow_toggle: false
			,language: "en"
			,syntax: 'js'	
			,toolbar: "charmap, |, search, go_to_line, |, undo, redo, |, select_font, |, change_smooth_selection, highlight, reset_highlight, |, help"
			,EA_load_callback: "validatorAreaLoaded"
			,plugins: "charmap"
			,charmap_default: "arrows"
		});
}

function saveValidators() {
	for (i=0; i<document.validators.length; i++) {
		document.validators[i].alias = document.getElementById("validatorName"+i).value;
		document.validators[i].php = editAreaLoader.getValue("validatorPHP"+i);
		document.validators[i].js = editAreaLoader.getValue("validatorJS"+i);
	}
	alert(getTranslation('Validation rules has been updated'));
}

function addValidator() {
	showLoadingImage();
	document.validators[document.validators.length] = new SFG_Validator('','','');
	setValidatorArea(document.validators.length-1);
}

function removeValidator(ev, mode) {
	if (mode==1 || confirm(getTranslation('Are you sure want to remove this rule?'))) {
		if (typeof ev == 'object') {
			if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
			var id = obj.parentNode.getElementsByTagName('input')[0].id.substr(13);
		} else id = ev;
		document.validators.splice(id,1);
		for (i=id; i<document.validators.length; i++) {
			document.getElementById("validatorName"+i).value = document.getElementById("validatorName"+(i-0+1)).value;
			document.getElementById("validatorPHP"+i).value = document.getElementById("validatorPHP"+(i-0+1)).value;
			document.getElementById("validatorJS"+i).value = document.getElementById("validatorJS"+(i-0+1)).value;
			editAreaLoader.setValue("validatorPHP"+i, editAreaLoader.getValue("validatorPHP"+(i-0+1)));
			editAreaLoader.setValue("validatorJS"+i, editAreaLoader.getValue("validatorJS"+(i-0+1)));
		}
		document.getElementById("validatorName"+document.validators.length).value = '';
		editAreaLoader.setValue("validatorPHP"+document.validators.length, '');
		editAreaLoader.setValue("validatorJS"+document.validators.length, '');
		editAreaLoader.delete_instance("validatorPHP"+document.validators.length);
		editAreaLoader.delete_instance("validatorJS"+document.validators.length);
		document.getElementById("validatorPHP"+document.validators.length).parentNode.style.display = 'none';
		document.getElementById("validatorJS"+document.validators.length).parentNode.style.display = 'none';
		document.getElementById("validatorName"+document.validators.length).parentNode.style.display = 'none';
		document.loadedValidators -= 2;
	}
}

function closeValidationEditor(ev) {
	var div = document.getElementById('over_mask3');
	for (i=0; i<document.validators.length; i++) {
		editAreaLoader.delete_instance("validatorPHP"+i);
		editAreaLoader.delete_instance("validatorJS"+i);
	}
	//div.innerHTML='';
	//div.parentNode.removeChild(div);
	if (div) div.style.visibility = 'hidden';
	hideMask();
	hideLoadingImage();
}

function validatorAreaLoaded() {
	document.loadedValidators ++;
	if (document.loadedValidators == document.validators.length*2) hideLoadingImage();
}

function loadDefaultValidators(mode) {
	if (mode==1) {
		if (!confirm(getTranslation('Are you sure want to load default validators?'))) return;
		showLoadingImage();		
	}
	var xmlDoc = loadXMLFile(document.adminURL+'/defaults/validators.xml');
	var validators = xmlDoc.documentElement.getElementsByTagName('validator');
	if (mode==1) for (i=document.validators.length-1; i>=0; i--) removeValidator(i,1);
	document.validators = Array();
	for (i=0; i<validators.length; i++) {
		var name=validators[i].getAttribute('name');
		var js = validators[i].getElementsByTagName('js')[0];
		js = js.text ? js.text.replace(/^\s+|\s+$/g,"") : js.textContent ? js.textContent.replace(/^\s+|\s+$/g,"") : '';
		var php = validators[i].getElementsByTagName('php')[0];
		php = php.text ? php.text.replace(/^\s+|\s+$/g,"") : php.textContent ? php.textContent.replace(/^\s+|\s+$/g,"") : '';
		
		document.validators[document.validators.length] = new SFG_Validator(name,js,php);
		if (mode==1) setValidatorArea(i);
	}
}

loadDefaultValidators(0);