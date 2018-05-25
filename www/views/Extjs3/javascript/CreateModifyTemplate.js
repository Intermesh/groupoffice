GO.createModifyTemplate =
	
		'{[this.collapsibleSectionHeader(t("Creation and modification"), "createmodifypane-"+values.panelId, "name")]}'+
		'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="createmodifypane-{panelId}">'+

//	'{[this.collapsibleSectionHeader(t("Creation and modification"), "createModify-"+values.panelId, "createModify")]}'+
		'<tr>'+
			'<td width="120">'+t("Created at")+':</td>'+'<td style="padding-right:2px">{ctime}</td>'+
			'<td width="80">'+t("Modified at")+':</td>'+'<td>{mtime}</td>'+
		'</tr><tr>'+
			'<td width="120" style="vertical-align:top;">'+t("Created by")+':</td>'+'<td style="padding-right:2px">{username}</td>'+
			'<td width="120" style="vertical-align:top;">'+t("Modified by")+':</td>'+'<td>{musername}</td>'+
		'</tr>'+
	'</table>';
