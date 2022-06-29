document.bdTables = Array();
document.bdPrefix = '';
document.sfgDBMapping = Array();

SFG_DBField = function (field,type,is_null,key,def,extra) {
	this.initialField = '';
	this.field = field;
	this.type = type;
	this.is_null = is_null;
	this.key = key;
	this.def = def;
	this.extra = extra;
	this.sfgField = '';
}

SFG_DB = function (name) {
	this.name = name;
	this.fields = Array();
}

document.sfgDB = new SFG_DB('');

function showDBEditor() {
	showMask();
	var div = document.createElement('div');
	div.id = 'over_mask5';
	var div2 = document.createElement('div');
	div2.id = 'over_mask_header';
	div2.innerHTML = '<span>'+getTranslation('Database Designer')+'</span>';
	var img = document.createElement('img');
	img.src = document.adminURL+'/images/close.png';
	img.id = 'over_maskClose';
	img.title = getTranslation('Close this Bar');
	addEvent(img, 'mousedown', closeDBEditor);
	div2.appendChild(img);
	div.appendChild(div2);
	document.body.appendChild(div);
	var div2 = document.createElement('div');
	div2.id = 'dbContainer';
	div2.innerHTML = '<table cellpadding=0 cellspacing=2 border=0 style="width:100%; height:100%"><tr><td width="180" align="left" valign="top"></td><td width="180" align="left" valign="top"></td><td align="left" valign="top"></td></tr></table>';
	div.appendChild(div2);
	//div2.style.height = div2.parentNode.offsetHeight - 20 + 'px';
	var s = '';
	for (i=0; i<document.bdTables.length; i++) s += '<option value="'+i+'">'+document.bdTables[i];
	div2.getElementsByTagName('td')[0].innerHTML='<center><b>'+getTranslation('DB tables')+'</b></center><select size=20 style="width:180px;" onchange="getFieldsList(this.selectedIndex)">'+s+'</select>';
	div2.getElementsByTagName('select')[0].style.height = div.offsetHeight - 98 + 'px';
	div2.getElementsByTagName('td')[1].innerHTML='<center><b>'+getTranslation('Fields in selected table')+'</b></center><div id="fieldsList"></div>';
	document.getElementById('fieldsList').style.height = div.offsetHeight - 70 + 'px';
	var button = document.createElement('input');
	button.type = 'button';
	addEvent(button,'click',assignTable);
	button.value = getTranslation('Associate Table with the Form');
	button.style.width = '170px';
	div2.getElementsByTagName('td')[0].appendChild(button);
	var div3 = document.createElement('div');
	div3.innerHTML = '<b>'+getTranslation('DB table')+':&nbsp;</b><input type="text" id="table_name" style="width:200px;" onchange="document.sfgDB.name = this.value">&nbsp;<input type="button" value="'+getTranslation('Generate automatically')+'" style="width:170px;" onclick="generateDB()" />&nbsp;<input type="button" value="'+getTranslation('Add field')+'" onclick="addField()" />&nbsp;<input type="button" value="'+getTranslation('Commit')+'" onclick="showLoadingImage(); setTimeout(\'commitDB()\',50);"><br /><div id="err_msg"></div><table cellpadding=0 cellspacing=1 border=0 style="width:97%"><tr style="background-color:#eeeeee"><td><b>'+getTranslation('SFG Field')+'</b></td><td><b>'+getTranslation('DB Field Name')+'</b></td><td><b>'+getTranslation('Type')+'</b></td><td><b>'+getTranslation('Len')+'</b></td><td><b>'+getTranslation('IS NULL')+'</b></td><td><b>'+getTranslation('PK')+'</b></td><td><b>'+getTranslation('Default')+'</b></td><td><b>'+getTranslation('Extra')+'</b></td><td><b>'+getTranslation('Remove')+'</b></td></tr></table>';
	div3.id = 'dbProperties';
	div2.getElementsByTagName('td')[2].appendChild(div3);
	div3.style.height = div3.parentNode.offsetHeight- 2 + 'px';
	updateFields();
}

function assignTable() {
	var id = document.getElementById('dbContainer').getElementsByTagName('select')[0].selectedIndex;
	if (id == -1) {
		alert(getTranslation('Please select a table to assign'));
		return;
	}
	if (!confirm(getTranslation('Are you sure want to assign the table to the form?'))) return;
	getTableInfo(id);
	updateFields();
	document.getElementById('err_msg').innerHTML = '';
}

function closeDBEditor() {
	var div = document.getElementById('over_mask5');
	if (div) div.parentNode.removeChild(div);
	hideMask();
	hideLoadingImage();
}

function addField() {
	document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField('','','','','','');
	updateFields();
}

function remField(id) {
	if (!confirm(getTranslation('Are you sure want to remove this field?'))) return;
	document.sfgDB.fields.splice(id,1);
	updateFields();
}

function generateDB() {
	document.sfgDB.name = '';
	document.sfgDB.fields = Array();
	var names = Array();
	document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField('id','int(11)','NOT NULL','PRI','','auto_increment');
	for (i=0; i<document.allElements.length; i++) {
		var name = document.allElements[i].getProp(document.allElements[i].attributes, 'name');
		if (name && !in_array(name, names)) {
			names[names.length] = name;
			var type = document.allElements[i].getProp(document.allElements[i].attributes, 'type');
			var tag = document.allElements[i].tag + ((type)?','+type:'');
			if (tag=='input,file' || tag=='input,text' || tag=='input,password' || tag=='input,checkbox' || tag=='input,radio' || tag=='select' || tag=='canvas') {
				name2 = name.replace(/^\s+|\s+$/g,"");
				if (name.indexOf('[')>-1) name = name.substr(0,name.indexOf('['));
				document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField(name,'varchar(255)','NOT NULL','','','');
				document.sfgDB.fields[document.sfgDB.fields.length-1].sfgField = name2;
			}
			if (tag=='textarea') {
				name2 = name.replace(/^\s+|\s+$/g,"");
				if (name.indexOf('[')>-1) name = name.substr(0,name.indexOf('['));
				document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField(name,'text','NOT NULL','','','');
				document.sfgDB.fields[document.sfgDB.fields.length-1].sfgField = name2;
			}
		}
		if (document.allElements[i].content) {
			var tmp = document.createElement('div');
			tmp.innerHTML = document.allElements[i].content;
			var inputs = tmp.getElementsByTagName('input');
			for (o=0; o<inputs.length; o++) if (inputs[o].name && inputs[o].name!='' && !in_array(inputs[o].name, names)) {
				var name = inputs[o].name;
				name2 = name.replace(/^\s+|\s+$/g,"");
				if (name.indexOf('[')>-1) name = name.substr(0,name.indexOf('['));
				document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField(name,'varchar(255)','NOT NULL','','','');
				document.sfgDB.fields[document.sfgDB.fields.length-1].sfgField = name2;
			}
			var inputs = tmp.getElementsByTagName('select');
			for (o=0; o<inputs.length; o++) if (inputs[o].name && inputs[o].name!='' && !in_array(inputs[o].name, names)) {
				var name = inputs[o].name;
				name2 = name.replace(/^\s+|\s+$/g,"");
				if (name.indexOf('[')>-1) name = name.substr(0,name.indexOf('['));
				document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField(name,'varchar(255)','NOT NULL','','','');
				document.sfgDB.fields[document.sfgDB.fields.length-1].sfgField = name2;
			}
			var inputs = tmp.getElementsByTagName('textarea');
			for (o=0; o<inputs.length; o++) if (inputs[o].name && inputs[o].name!='' && !in_array(inputs[o].name, names)) {
				var name = inputs[o].name;
				name2 = name.replace(/^\s+|\s+$/g,"");
				if (name.indexOf('[')>-1) name = name.substr(0,name.indexOf('['));
				document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField(name,'text','NOT NULL','','','');
				document.sfgDB.fields[document.sfgDB.fields.length-1].sfgField = name2;
			}
		}
	}
	updateFields();
}

function commitDB() {
	if (document.sfgDB.name.replace(/^\s+|\s+$/g,"")=='') {hideLoadingImage(); alert(getTranslation('Table name cannot be left blank')); return;}
	if (document.sfgDB.fields.length==0) {alert(getTranslation('Table should have at least one field')); return;}	
	var s = document.sfgDB.name.replace(/^\s+|\s+$/g,"")+'|';
	for (i=0; i<document.sfgDB.fields.length; i++) {
		if (document.sfgDB.fields[i].initialField == '') document.sfgDB.fields[i].initialField = document.sfgDB.fields[i].field;
		s+=document.sfgDB.fields[i].initialField+'|';
		s+=document.sfgDB.fields[i].field+'|';
		s+=document.sfgDB.fields[i].type+'|';
		s+=document.sfgDB.fields[i].is_null+'|';
		s+=document.sfgDB.fields[i].key+'|';
		s+=document.sfgDB.fields[i].def+'|';
		s+=document.sfgDB.fields[i].extra+'|';
	}
	var s2='q='+s;

    jQuery.ajax({
        url: document.execQueryUrl,
        data: s2,
        type: "POST"
    }).done(function (msg) {
        if (msg=='' || msg == '0') {
            msg='<b style="color:blue">'+getTranslation('Changes have been applied successfully')+'</b>';
            document.sfgDBMapping = Array();
            for (i=0; i<document.sfgDB.fields.length; i++) {
                document.sfgDB.fields[i].initialField = document.sfgDB.fields[i].field;
                if (document.sfgDB.fields.sfgField != '') document.sfgDBMapping[document.sfgDBMapping.length] = new Array(document.sfgDB.fields[i].field, document.sfgDB.fields[i].sfgField);
            }
        }
        document.bdTables = Array();
        getTablesList();
        closeDBEditor();
        showDBEditor();
        hideLoadingImage();
        document.getElementById('err_msg').innerHTML=msg;
    });	
}

function getTablesList() {
	var xmlhttp = HTTPRequest(document.getDbTableListUrl, false, null);
	var tables = xmlhttp.responseText.split('|');
	document.bdPrefix = tables[0];
	for (i=1; i<tables.length; i++) if (tables[i]!='') document.bdTables[document.bdTables.length] = tables[i];
}

function updateFields() {
	var names = Array();
	for (i=0; i<document.allElements.length; i++) {
		var name = document.allElements[i].getProp(document.allElements[i].attributes, 'name');
		if (name && !in_array(name, names)) names[names.length] = name;
		if (document.allElements[i].content) {
			var tmp = document.createElement('div');
			tmp.innerHTML = document.allElements[i].content;
			var inputs = tmp.getElementsByTagName('input');
			for (o=0; o<inputs.length; o++) if (inputs[o].name && inputs[o].name!='' && !in_array(inputs[o].name, names)) names[names.length] = inputs[o].name;
			var inputs = tmp.getElementsByTagName('select');
			for (o=0; o<inputs.length; o++) if (inputs[o].name && inputs[o].name!='' && !in_array(inputs[o].name, names)) names[names.length] = inputs[o].name;
			var inputs = tmp.getElementsByTagName('textarea');
			for (o=0; o<inputs.length; o++) if (inputs[o].name && inputs[o].name!='' && !in_array(inputs[o].name, names)) names[names.length] = inputs[o].name;
		}
	}
	var tmp = document.createElement('div');
	tmp.innerHTML = document.sfg_html;
	var inputs = tmp.getElementsByTagName('input');
	for (i=0; i<inputs.length; i++) if (inputs[i].name && inputs[i].name!='' && !in_array(inputs[i].name, names)) names[names.length] = inputs[i].name;
	var inputs = tmp.getElementsByTagName('select');
	for (i=0; i<inputs.length; i++) if (inputs[i].name && inputs[i].name!='' && !in_array(inputs[i].name, names)) names[names.length] = inputs[i].name;
	var inputs = tmp.getElementsByTagName('textarea');
	for (i=0; i<inputs.length; i++) if (inputs[i].name && inputs[i].name!='' && !in_array(inputs[i].name, names)) names[names.length] = inputs[i].name;
	
	var types = Array("VARCHAR","TINYINT","TEXT","DATE","SMALLINT","MEDIUMINT","INT","BIGINT","FLOAT","DOUBLE","DECIMAL","DATETIME","TIMESTAMP","TIME","YEAR","CHAR","TINYBLOB","TINYTEXT","BLOB","MEDIUMBLOB","MEDIUMTEXT","LONGBLOB","LONGTEXT","ENUM","SET","BINARY","VARBINARY");
	var table = document.getElementById('dbProperties').getElementsByTagName('table')[0];
	var trs = table.getElementsByTagName('tr');
	for (i=trs.length-1; i>0; i--) trs[i].parentNode.removeChild(trs[i]);
	document.getElementById('table_name').value = document.sfgDB.name;
	for (i=0; i<document.sfgDB.fields.length; i++) {
		var tr = document.createElement('tr');
		var td = document.createElement('td');
		td.style.width = '10px';
		var s = '<option value="">'+getTranslation('nothing selected');
		for (o=0; o<names.length; o++) s+='<option value="'+names[o]+'" '+((names[o]==document.sfgDB.fields[i].sfgField)?'selected':'')+'>'+names[o];
		td.innerHTML = '<select>'+s+'</select>';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.innerHTML = '<input type="text" value="'+document.sfgDB.fields[i].field+'" style="width:95%" />';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.style.width = '10px';
		var type = document.sfgDB.fields[i].type.split('(');
		var s = '';
		for (o=0; o<types.length; o++) s+='<option value="'+types[o].toLowerCase()+'" '+((types[o].toLowerCase()==type[0].toLowerCase())?'selected':'')+'>'+types[o].toLowerCase();
		td.innerHTML = '<select>'+s+'</select>';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.style.width = '10px';
		if (type[1]) type[1] = parseInt(type[1]);
		td.innerHTML = '<input type="text" value="'+((type[1])?type[1]:'')+'" style="width:20px" />';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.style.width = '10px';
		td.innerHTML = '<select><option value="NULL" '+((document.sfgDB.fields[i].is_null=='NULL')?'selected':'')+'>NULL<option value="NOT NULL" '+((document.sfgDB.fields[i].is_null!='NULL')?'selected':'')+'>NOT NULL</select>';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.style.width = '10px';
		td.innerHTML = '<input type="checkbox" '+((document.sfgDB.fields[i].key=='PRI')?'checked':'')+' />';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.innerHTML = '<input type="text" value="'+document.sfgDB.fields[i].def+'" style="width:95%" />';
		tr.appendChild(td);
		var td = document.createElement('td');
		td.style.width = '10px';
		td.innerHTML = '<select><option value="" '+((document.sfgDB.fields[i].extra=='')?'selected':'')+'><option value="auto_increment" '+((document.sfgDB.fields[i].extra=='')?'':'selected')+'>auto_increment</select>';
		tr.appendChild(td);
		trs[0].parentNode.appendChild(tr);
		var td = document.createElement('td');
		td.style.width = '10px';
		td.innerHTML = '<span style="color:red; cursor:pointer" onclick="remField('+i+')">'+getTranslation('rem')+'</span>';
		tr.appendChild(td);
	}
	var inputs = document.getElementById('dbProperties').getElementsByTagName('table')[0].getElementsByTagName('input');
	for (i=0; i<inputs.length; i++) {
		addEvent(inputs[i],'change',fieldChanged);
		if (inputs[i].type.toLowerCase()=='checkbox') addEvent(inputs[i],'click',fieldChanged);
	}
	var selects = document.getElementById('dbProperties').getElementsByTagName('table')[0].getElementsByTagName('select');
	for (i=0; i<selects.length; i++) addEvent(selects[i],'change',fieldChanged);
}

function fieldChanged(ev) {
	if (window.event) obj=window.event.srcElement; else obj=ev.currentTarget;
	var tr = obj.parentNode;
	while (tr.tagName.toLowerCase()!='tr') tr = tr.parentNode;
	var trs = document.getElementById('dbProperties').getElementsByTagName('table')[0].getElementsByTagName('tr');
	var id = -1;
	for (i=0; i<trs.length; i++) if (trs[i] == tr) { id = i-1; break; }
	if (id == -1) return;
	document.sfgDB.fields[id].sfgField = tr.getElementsByTagName('select')[0].value;
	document.sfgDB.fields[id].field = tr.getElementsByTagName('input')[0].value;
	document.sfgDB.fields[id].type = tr.getElementsByTagName('select')[1].value;
	if (tr.getElementsByTagName('input')[1].value.replace(/^\s+|\s+$/g,"")!='') document.sfgDB.fields[id].type += '('+tr.getElementsByTagName('input')[1].value+')';
	document.sfgDB.fields[id].is_null = tr.getElementsByTagName('select')[2].value;
	if (tr.getElementsByTagName('input')[2].checked) document.sfgDB.fields[id].key = 'PRI'; else document.sfgDB.fields[id].key = '';
	document.sfgDB.fields[id].def = tr.getElementsByTagName('input')[3].value;
	document.sfgDB.fields[id].extra = tr.getElementsByTagName('select')[3].value;	
}

function getTableInfo(id) {
	showLoadingImage();
	var xmlhttp = HTTPRequest(document.getDbTableInfoUrl + '?table='+document.bdTables[id], false, null);
	var fields = xmlhttp.responseText.split('|');
	document.sfgDB.name = document.bdTables[id];
	document.sfgDB.fields = Array();
	for (i=0; i<fields.length; i+=6) if (fields[i] && fields[i]!='') document.sfgDB.fields[document.sfgDB.fields.length] = new SFG_DBField(fields[i],fields[i+1],((fields[i+2]=='YES')?'NULL':'NOT NULL'),fields[i+3],fields[i+4],fields[i+5]);
	hideLoadingImage();
}

function getFieldsList(id) {
	showLoadingImage();
	var xmlhttp = HTTPRequest(document.getDbTableFieldsUrl + '?table='+document.bdTables[id], false, null);
	var fields = xmlhttp.responseText.split('|');
	document.getElementById('fieldsList').innerHTML = '';
	for (i=0; i<fields.length; i++) {
		var div = document.createElement('div');
		div.innerHTML = fields[i];
		document.getElementById('fieldsList').appendChild(div);
	}
	hideLoadingImage();
}

getTablesList();