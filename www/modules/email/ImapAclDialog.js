GO.email.ImapAclDialog = Ext.extend(GO.Window, {

	initComponent : function(){

		this.grid = new GO.grid.GridPanel({
			store:new GO.data.JsonStore({
				url: GO.url("email/folder/aclStore"),
				baseParams: {
					mailbox:"",
					account_id:0
				},
				root: 'results',
				id: 'identifier',
				fields:['identifier','permissions']
			}),
			cm: new Ext.grid.ColumnModel({
				defaults:{
					sortable:false
				},
				columns:[
				{
					header:t("User"),
					dataIndex: 'identifier'
				},{
					header: t("Permissions"),
					dataIndex: 'permissions'
				}]
			}),
			view: new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: t("No items to display")
			}),
			loadMask:true,
			tbar: [{
				iconCls: 'btn-add',
				text: t("Add"),
				cls: 'x-btn-text-icon',
				handler: function(){
					this.showUserDialog();
				},
				scope: this
			},{
				iconCls: 'btn-delete',
				text: t("Delete"),
				cls: 'x-btn-text-icon',
				handler: function(){
					this.grid.deleteSelected();
				},
				scope:this
			}],
			listeners:{
				rowdblclick:function(grid, rowIndex){
					var record = grid.getStore().getAt(rowIndex);
					this.showUserDialog(record);
				},
				scope:this
			}
		});

		Ext.apply(this, {
			width:500,
			height:400,
			title:t("Share", "email"),
			layout:'fit',
			items:[this.grid],
			buttons:[{
				text : t("Close"),
				handler : function() {
					this.hide();
				},
				scope : this
			}]
		});
		GO.email.ImapAclDialog.superclass.initComponent.call(this);
	},

	setParams : function(account_id, mailbox, mailboxtext){
		this.grid.store.baseParams.account_id=account_id;
		this.grid.store.baseParams.mailbox=mailbox;
		this.grid.store.load();

		this.setTitle(t("Share", "email")+": "+mailboxtext);
	},

	showUserDialog : function(record){

		if(!this.userDialog){
			this.userDialog = new GO.email.ImapAclUserDialog({
				listeners:{
					scope:this,
					save:function(){
						this.grid.store.load();
					}
				}
			});
		}		
		this.userDialog.show();
		this.userDialog.setData(this.grid.store.baseParams.mailbox, this.grid.store.baseParams.account_id, record);

	}

});
