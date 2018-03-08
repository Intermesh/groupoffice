GO.files.FolderPanel = Ext.extend(GO.DisplayPanel,{
	model_name : "GO\\Files\\Model\\Folder",



	noFileBrowser:true,
	
	editGoDialogId : 'folder',

	editHandler : function(){	
	},

	createTopToolbar : function(){
		var tbar = GO.files.FilePanel.superclass.createTopToolbar.call(this);

		tbar.splice(1,0,{
			iconCls: 'btn-settings',
			text: GO.lang.strProperties,
			cls: 'x-btn-text-icon',
			handler: function(){
				GO.files.showFolderPropertiesDialog(this.link_id+"");
			},
			scope: this
		});

		return tbar;
	},

	setData : function(data)
	{
//		this.setTitle(data.name);
	
		this.topToolbar.items.items[0].setVisible(false);

		GO.files.FolderPanel.superclass.setData.call(this, data);
	},

	initComponent : function(){	
		
		this.loadUrl=('files/folder/display');
		
		this.template =

				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">'+GO.files.lang.folder+': {path}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.lang.strType+':</td>'+
						'<td>{type}</td>'+
					'</tr>'+					
					
					'<tr>'+
						'<td>'+GO.lang['strCtime']+':</td>'+'<td>{ctime}</td>'+
					'</tr><tr>'+
						'<td>'+GO.lang['createdBy']+':</td>'+'<td>{username}</td>'+
					'</tr><tr>'+
						'<td>'+GO.lang['strMtime']+':</td>'+'<td>{mtime}</td>'+
					'</tr><tr>'+
						'<td>'+GO.lang['mUser']+':</td>'+'<td>'+
							'<tpl if="muser_id">{musername}</tpl>'+
							'</td>'+
					'</tr>'+
					
					'<tr>'+
						'<td>URL:</td>'+
						'<td><a target="_blank" href="{url}">'+GO.files.lang.rightClickToCopy+'</a></td>'+
					'</tr>'+

					'<tpl if="!GO.util.empty(comment)">'+
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">'+GO.files.lang.comments+'</td>'+
						'</tr>'+
						'<tr>'+
							'<td colspan="2">{comment}</td>'+
						'</tr>'+
					'</tpl>'+
				'</table>';

		if(GO.customfields)
		{
			this.template +=GO.customfields.displayPanelTemplate;
		}

		if(GO.tasks)
			this.template +=GO.tasks.TaskTemplate;

		if(GO.calendar)
			this.template += GO.calendar.EventTemplate;

		if(GO.workflow)
			this.template +=GO.workflow.WorkflowTemplate;
		
		if(GO.lists)
			this.template += GO.lists.ListTemplate;

		this.template += GO.linksTemplate;	
		
		Ext.apply(this.templateConfig, GO.linksTemplateConfig);

		if(GO.comments)
		{
			this.template += GO.comments.displayPanelTemplate;
		}

		GO.files.FolderPanel.superclass.initComponent.call(this);
	}
});