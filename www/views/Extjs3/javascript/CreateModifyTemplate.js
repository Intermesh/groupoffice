GO.createModifyTemplate =
	
		'{[this.collapsibleSectionHeader(GO.lang.createModify, "createmodifypane-"+values.panelId, "name")]}'+
		'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="createmodifypane-{panelId}">'+

//	'{[this.collapsibleSectionHeader(GO.lang.createModify, "createModify-"+values.panelId, "createModify")]}'+
		'<tr>'+
			'<td width="120">'+GO.lang['strCtime']+':</td>'+'<td style="padding-right:2px">{ctime}</td>'+
			'<td width="80">'+GO.lang['strMtime']+':</td>'+'<td>{mtime}</td>'+
		'</tr><tr>'+
			'<td width="120" style="vertical-align:top;">'+GO.lang['createdBy']+':</td>'+'<td style="padding-right:2px">{username}</td>'+
			'<td width="120" style="vertical-align:top;">'+GO.lang['mUser']+':</td>'+'<td>{musername}</td>'+
		'</tr>'+
	'</table>';