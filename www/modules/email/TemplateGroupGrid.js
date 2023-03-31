

GO.email.TemplateGroupGrid = Ext.extend(GO.grid.GridPanel,{

	layout:'fit',
	autoScroll:true,
	loadMask:true,
	paging:true,
	editDialogClass:GO.email.TemplateGroupDialog,

	initComponent() {


		this.store = new Ext.data.JsonStore({
			url:GO.url("email/templateGroup/store"),
			//sortInfo:{field: 'name',direction: "ASC"},
			root: 'results',
			id: 'id',
			fields:['id','name'],
			remoteSort:true,
		});


		//this.groupDialog = new GO.email.TemplateGroupDialog();
		this.standardTbar = true;
		// this.tbar = [{
		// 	iconCls: 'ic-add',
		// 	text: t("Add"),
		// 	handler()
		// 	{
		// 		this.groupDialog.show();
		// 	},scope:this
		// },{
		// 	iconCls: 'ic-delete',
		// 	text: t("Delete"),
		// 	handler()
		// 	{
		// 		this.deleteSelected();
		// 	},scope: this
		// }]

		this.cm = new Ext.grid.ColumnModel({
			// defaults:{
			// 	sortable:true
			// },
			columns:[
				{
					header: 'ID',
					dataIndex: 'id',
					hidden:true
				},{
					header: t("Name"),
					dataIndex: 'name',
				}
			]
		});

		this.supr().initComponent.call(this);

		this.on('render', () => {
			this.store.load();
		});

		// this.on('rowdblclick', function(grid, rowIndex) {
		// 	var record = grid.getStore().getAt(rowIndex);
		// 	this.groupDialog.show(record.data.id);
		//
		// }, this);

	},
});
