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
					header:GO.lang.strUser,
					dataIndex: 'identifier'
				},{
					header: GO.lang.strPermissions,
					dataIndex: 'permissions'
				}]
			}),
			view: new Ext.grid.GridView({
				autoFill: true,
				forceFit: true,
				emptyText: GO.lang['strNoItems']
			}),
			loadMask:true,
			tbar: [{
				iconCls: 'btn-add',
				text: GO.lang.cmdAdd,
				cls: 'x-btn-text-icon',
				handler: function(){
					this.showUserDialog();
				},
				scope: this
			},{
				iconCls: 'btn-delete',
				text: GO.lang.cmdDelete,
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
			title:GO.email.lang.shareFolder,
			layout:'fit',
			items:[this.grid],
			buttons:[{
				text : GO.lang['cmdClose'],
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

		this.setTitle(GO.email.lang.shareFolder+": "+mailboxtext);
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