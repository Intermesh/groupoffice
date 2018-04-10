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


GO.addressbook.TemplatesGrid = function(config)
{
	if(!config)
	{
		config = {};
	}
	
	
	config.sm= new Ext.grid.RowSelectionModel({
		singleSelect:false
	});
	config.title= t("Templates", "addressbook");
	
	config.store = new GO.data.JsonStore({
		url: GO.url('addressbook/template/store'),
		baseParams: {
			permissionLevel: GO.permissionLevels.write
		},
		root: 'results',
		id: 'id',
		fields: ['id', 'user_id', 'owner', 'name', 'type', 'acl_id','extension'],
		remoteSort: true
	});
	config.store.setDefaultSort('name', 'ASC');
	if (GO.util.empty(config.noDocumentTemplates)) {
		config.store.on('load', function(){
			if(go.Modules.isAvailable("legacy", "documenttemplates"))
				GO.documenttemplates.ooTemplatesStore.load();
		}, this);
	} else {
		config.store.on('beforeload',function(store,options){
			store.baseParams['type']=0;
		}, this);
	}
	
	var tbarItems = [{
		iconCls: 'ic-add',
		text: t("Add e-mail template", "addressbook"),
//		disabled:!GO.settings.modules.addressbook.write_permission,
		handler: function(){

			this.showEmailTemplateDialog();
		},
		scope: this
	}];
	
	if (GO.util.empty(config.noDocumentTemplates)) {
		tbarItems.push({
			iconCls: 'ic-add',
			text: t("Add document template", "addressbook"),
//			disabled:!GO.settings.modules.addressbook.write_permission,
			handler: function(){
				this.showOOTemplateDialog();
			},
			scope: this
		});
	}
	
	tbarItems.push({
		iconCls: 'ic-delete',
		text: t("Delete"),
		disabled:!GO.settings.modules.addressbook.write_permission,
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
			header: t("Type", "addressbook"),
			dataIndex: 'type' ,
			renderer: this.typeRenderer.createDelegate(this),
			width: 100
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
	config.layout= 'fit';

	if (GO.util.empty(config.noDocumentTemplates)) {
		config.deleteConfig= {
			callback: function(){
				GO.documenttemplates.ooTemplatesStore.reload();
			},
			scope: this
		};
	}
	
	
	GO.addressbook.TemplatesGrid.superclass.constructor.call(this, config);
	
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

Ext.extend(GO.addressbook.TemplatesGrid, GO.grid.GridPanel,{
	templateType : {
		'0' : 'E-mail',
		'1' : t("Document template", "addressbook")
	},

	showOOTemplateDialog : function(template_id){

		if(!GO.documenttemplates){
			alert(t("The %s module is required for this function").replace('%s', 'Document templates'));
			return false;
		}

		if(!this.ooTemplateDialog){
			this.ooTemplateDialog = new GO.documenttemplates.OOTemplateDialog();
			this.ooTemplateDialog.on('save', function(){
				this.store.load();
			}, this);
		}

		this.ooTemplateDialog.show(template_id);
	},

	showEmailTemplateDialog : function(template_id){
		if(!this.emailTemplateDialog){
			this.emailTemplateDialog = new GO.addressbook.EmailTemplateDialog();
			this.emailTemplateDialog.on('save', function(){
				this.store.load();
			}, this);
		}
		this.emailTemplateDialog.show(template_id);
	},

	typeRenderer : function(val, meta, record)
	{
		var type = this.templateType[val];
		
		if(val=='1'){
			type+=' ('+record.get('extension')+')';
		}

		return type;
	},
	
	afterRender : function()
	{
		GO.addressbook.TemplatesGrid.superclass.afterRender.call(this);
		
		if(this.isVisible())
		{
			if(!this.store.loaded)
			{
				this.store.load();
			}
		}

	},
	
	onShow : function(){
		GO.addressbook.TemplatesGrid.superclass.onShow.call(this);
		if(!this.store.loaded)
		{
			this.store.load();
		}
	}

});
