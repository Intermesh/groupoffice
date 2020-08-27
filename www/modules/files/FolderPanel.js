GO.files.FolderPanel = Ext.extend(GO.DisplayPanel,{
	model_name : "GO\\Files\\Model\\Folder",



	noFileBrowser:true,
	
	editGoDialogId : 'folder',

	editHandler : function(){	
		GO.files.showFolderPropertiesDialog(this.link_id+"");
	},

	// createTopToolbar : function(){
	// 	var tbar = GO.files.FolderPanel.superclass.createTopToolbar.call(this);

	// 	tbar.splice(1,1,{
	// 		iconCls: 'btn-settings',
	// 		text: t("Properties"),
	// 		cls: 'x-btn-text-icon',
	// 		handler: function(){
	// 			GO.files.showFolderPropertiesDialog(this.link_id+"");
	// 		},
	// 		scope: this
	// 	});

	// 	return tbar;
	// },

	setData : function(data)
	{
//		this.setTitle(data.name);
	
		// this.topToolbar.items.items[0].setVisible(false);

		GO.files.FolderPanel.superclass.setData.call(this, data);
	},

	initComponent : function(){	
		
		this.loadUrl=('files/folder/display');
		
		this.template =

				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">'+t("Folder", "files")+': {path}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>URL:</td>'+
						'<td><a target="_blank" href="{url}">'+t("Right click to copy", "files")+'</a></td>'+
					'</tr>'+

					'<tpl if="!GO.util.empty(comment)">'+
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">'+t("Comments", "files")+'</td>'+
						'</tr>'+
						'<tr>'+
							'<td colspan="2">{comment}</td>'+
						'</tr>'+
					'</tpl>'+
				'</table>';

		
					
		if(go.Modules.isAvailable("legacy", "workflow"))
			this.template +=GO.workflow.WorkflowTemplate;

		GO.files.FolderPanel.superclass.initComponent.call(this);

		this.add(go.customfields.CustomFields.getDetailPanels("Folder"));

		this.add(new go.detail.CreateModifyPanel());
	}
});
