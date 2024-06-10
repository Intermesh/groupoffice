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
	config.store = new Ext.data.GroupingStore({
		url:GO.url("email/template/store"),
		sortInfo:{field: 'name',direction: "ASC"},
		baseParams: {
			// permissionLevel: GO.permissionLevels.write
			permissionLevel: GO.permissionLevels.read
		},
		reader: new Ext.data.JsonReader({
			root: 'results',
			totalProperty: 'total',
			id: 'id',
			fields: ['id', 'user_id', 'owner', 'name', 'type', 'acl_id','extension','group_name', 'group_id', 'permissionLevel'],
		}),
		groupField:'group_name',
		remoteSort:true,
		remoteGroup:true
	});

	config.store.setDefaultSort('name', 'ASC');

	var tbarItems = [];
	
		tbarItems.push({
			iconCls: 'ic-add',
			text: t("Add", "email"),
			handler: function(){
				this.showEmailTemplateDialog();
			},
			scope: this
		});
	
	
	tbarItems.push({
		iconCls: 'ic-delete',
		text: t("Delete"),
		handler: function(){
			this.deleteSelected();
		},
		scope: this
	},{
		iconCls: 'ic-list',
		text: t('Groups'),
		handler() {
			(new GO.Window({
				title: t('Groups'),
				width: 500,
				layout:'fit',
				height: 400,
				items: [
					new GO.email.TemplateGroupGrid()
				]
			})).show()
		}
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
		columns:[{
			header: '#',
			width:30,
			hidden:true,
			dataIndex: 'id'
		},
		{
			header: t("Name"),
			dataIndex: 'name'
		},{
			header: t('Group'),
			dataIndex: 'group_name'
		}, {
			header: t("Owner"),
			dataIndex: 'owner' ,
			width: 200,
			sortable: false,
				renderer: (value,elem,record) => {
					return record.json.user_name || "";
				}
		},{
			header: "",
				dataIndex: 'permissionLevel',
				width: 30,
				sortable: false,
				renderer: (value) => {
					if(value < GO.permissionLevels.write) {
						return '<i class="icon ic-lock"></i>';
					}
					return "";
				}
			}
		]
	});

	config.view = new Ext.grid.GroupingView({
		showGroupName: false,
		//enableNoGroups:false, // REQUIRED!
		hideGroupedColumn: true,
		emptyText: t("No items to display"),
		autoFill: true,
		forceFit: true
	});

	config.cm= columnModel;
	config.border= false;
	config.paging= true;

	if (GO.util.empty(config.noDocumentTemplates)) {
		config.deleteConfig= {
			callback: function(){
				config.store.reload();
			},
			scope: this
		};
	}
	
	
	GO.email.TemplatesGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		const record = grid.getStore().getAt(rowIndex);
		if(record.data.permissionLevel < GO.permissionLevels.write) {
			return;
		}

		if (parseInt(record.data.type) === 0) {
			this.showEmailTemplateDialog(record.data.id);
		} else {
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
				this.store.reload();
			}, this);
		}
		this.emailTemplateDialog.show(template_id);
	},


	
	afterRender : function()
	{
		GO.email.TemplatesGrid.superclass.afterRender.call(this);
		if(!this.store.loaded)
		{
			this.store.load();
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


