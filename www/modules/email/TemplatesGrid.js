/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */


GO.email.TemplatesGrid = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	
	config.sm= new Ext.grid.RowSelectionModel({
		singleSelect:false
	});
	config.title= t("Templates");
	
	config.store = new GO.data.JsonStore({
		url: GO.url('email/template/store'),
		baseParams: {
			permissionLevel: GO.permissionLevels.write
		},
		root: 'results',
		id: 'id',
		fields: ['id', 'user_id', 'owner', 'name', 'type', 'acl_id','extension'],
		remoteSort: true
	});
	config.store.setDefaultSort('name', 'ASC');
//	if (GO.util.empty(config.noDocumentTemplates)) {
//		config.store.on('load', function(){
//			if(go.Modules.isAvailable("legacy", "email"))
//				GO.email.ooTemplatesStore.load();
//		}, this);
//	} else {
//		config.store.on('beforeload',function(store,options){
//			store.baseParams['type']=0;
//		}, this);
//	}
	
	var tbarItems = [];
	
		tbarItems.push({
			iconCls: 'ic-add',
			text: t("Add", "email"),
//			disabled:!GO.settings.modules.email.write_permission,
			handler: function(){
				this.showEmailTemplateDialog();
			},
			scope: this
		});
	
	
	tbarItems.push({
		iconCls: 'ic-delete',
		text: t("Delete"),
		disabled:!GO.settings.modules.email.write_permission,
		handler: function(){
			this.deleteSelected();
		},
		scope: this
	},'->',{
		xtype:'tbsearch',
		store: config.store
	}
	);
	
	config.tbar= tbarItems;
	
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header: t("Name"),
			dataIndex: 'name'
		},
		{
			header: t("Owner"),
			dataIndex: 'owner' ,
			width: 200,
			sortable: false
		}
		]
	});


	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")
	});
	config.cm= columnModel;
	config.border= false;
	config.paging= true;

	if (GO.util.empty(config.noDocumentTemplates)) {
		config.deleteConfig= {
			callback: function(){
				GO.email.ooTemplatesStore.reload();
			},
			scope: this
		};
	}
	
	
	GO.email.TemplatesGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);
		
		if(record.data.type=='0')
		{
			this.showEmailTemplateDialog(record.data.id);
		}else
		{
			this.showOOTemplateDialog(record.data.id);
		}		
	}, this);	
}

Ext.extend(GO.email.TemplatesGrid, GO.grid.GridPanel,{
	templateType : {
		'0' : 'E-mail',
		'1' : t("Document template", "email")
	},

	

	showEmailTemplateDialog : function(template_id){
		if(!this.emailTemplateDialog){
			this.emailTemplateDialog = new GO.email.EmailTemplateDialog();
			this.emailTemplateDialog.on('save', function(){
				this.store.load();
			}, this);
		}
		this.emailTemplateDialog.show(template_id);
	},


	
	afterRender : function()
	{
		GO.email.TemplatesGrid.superclass.afterRender.call(this);
		
		if(this.isVisible())
		{
			if(!this.store.loaded)
			{
				this.store.load();
			}
		}

	},
	
	onShow : function(){
		GO.email.TemplatesGrid.superclass.onShow.call(this);
		if(!this.store.loaded)
		{
			this.store.load();
		}
	}

});


