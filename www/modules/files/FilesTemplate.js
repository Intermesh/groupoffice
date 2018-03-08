GO.files.filesTemplate =

		'<tpl if="values.files && values.files.length">'+
		
		'{[this.collapsibleSectionHeader(GO.files.lang.files, "files-"+values.panelId, "files")]}'+

		
		'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="files-{panelId}">'+		
		'<tr>'+							
			'<td class="table_header_links" style="width:100%">' + GO.lang['strName'] + '</td>'+							
			'<td class="table_header_links" style="white-space:nowrap">' + GO.lang['strMtime'] + '</td>'+
			'<td class="table_header_links">&nbsp;</td>'+
			'<td class="table_header_links">&nbsp;</td>'+
		'</tr>'+
		'<tpl if="!files.length">'+
			'<tr><td colspan="5">'+GO.lang.strNoItems+'</td></tr>'+
		'</tpl>'+
		'<tpl for="files">'+
			'<tr>'+											
				'<td>'+
				'<tpl if="locked_user_id&gt;0"><div class="fs-grid-locked"></tpl>'+
				'<a class="go-grid-icon filetype filetype-{extension}" href="#files_{[xindex-1]}">{name}</a>'+
				'<tpl if="locked_user_id&gt;0"></div></tpl>'+
				'</td>'+

				'<td style="white-space:nowrap">{mtime}</td>'+

				'<tpl if="extension!=\'folder\'">'+
					'<tpl if="locked">'+
						'<td style="white-space:nowrap">'+
							'<div style="display:block;opacity:0.4;filter:alpha(opacity=40);" class="go-icon btn-edit">&nbsp;</div>'+
						'</td>'+
					'</tpl>'+
					'<tpl if="!locked">'+
						'<td style="white-space:nowrap">'+
							'<a style="display:block" class="go-icon btn-edit" href="#files_{[xindex-1]}">&nbsp;</a>'+
						'</td>'+
					'</tpl>'+
					'<td>'+
						'<a style="display:block" class="go-icon btn-download" href="{[GO.files.filesTemplateConfig.getDownloadUrl(values.id,false)]}">&nbsp;</a>'+
					'</td>'+
				'</tpl>'+
				'<tpl if="extension==\'folder\'">'+
					'<td style="white-space:nowrap">'+					
						'<a style="display:block" class="go-icon btn-info" href="#" onclick="'+

							'<tpl if="extension!=\'folder\'">'+
							'GO.linkHandlers[\'GO\\\\\\\\Files\\\\\\\\Model\\\\\\\\File\'].call(this, {id});'+
							'</tpl>'+
							'<tpl if="extension==\'folder\'">'+
							'GO.linkHandlers[\'GO\\\\\\\\Files\\\\\\\\Model\\\\\\\\Folder\'].call(this, {id});'+
							//'GO.files.openFolder({[this.panel.data.files_folder_id]}, {id});'+
							'</tpl>'+

						'">&nbsp;</a>'+					
					'</td>'+
				'</tpl>'+
			'</tr>'+
		'</tpl>'+
		
		'<tr><td colspan="4"><a class="display-panel-browse" href="#browsefiles">'+GO.lang.browse+'</a></td></tr>'+

		'</table>'+
	
'</tpl>';
GO.files.filesTemplateConfig={
	getPath : function(path)
	{
		return path.replace(/\'/g,'\\\'');
	},
	getDownloadUrl : function(id,inline){

		if(GO.util.empty(inline)){
			inline=false;
		} else {
			inline=true;
		}
		
		return GO.url("files/file/download",{id:id,inline:inline})
	}
	
};