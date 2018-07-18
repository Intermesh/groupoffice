/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistsGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
GO.addressbook.AddresslistsGrid = Ext.extend(GO.grid.GridPanel,{
	
	
	initComponent : function(){
		
		Ext.apply(this, {
			noDelete: !(GO.settings.modules.addressbook.permission_level >= GO.permissionLevels.write),
	standardTbarDisabled : !(GO.settings.modules.addressbook.permission_level >= GO.permissionLevels.write),
	
		});

		var fields = {
			fields:['id', 'name', 'user_name','acl_id','addresslistGroupName'],
			columns:[{
				header: t("ID"),
				dataIndex: 'id',
				groupable:false,
				hidden:true,				
				width:30
			},{
				header: t("Name"),
				dataIndex: 'name',
				groupable:false,
			},{
				header: t("Address list group", "addressbook"),
				dataIndex: 'addresslistGroupName'
			},{
				header: t("Owner", "addressbook"),
				dataIndex: 'user_name',
				groupable:false,
				sortable: false
			}
		]};

		this.columnModel = new Ext.grid.ColumnModel({
			defaults:{
				sortable:true
			},
			columns: fields.columns
		});
				
		Ext.apply(this,{
			id: 'ab-addresslist-grid',
			title:t("Address lists", "addressbook"),
			standardTbar:true,
			tbar: [
				'-'
				,{
					iconCls: 'btn-folder',
					text: t("Manage groups", "addressbook"),
					cls: 'x-btn-text-icon',
					handler: function(){
						if(!this.groupDialog)
						{
							this.groupDialog = new GO.addressbook.AddresslistGroupGridDialog();
							this.groupDialog.on('change', function(){this.store.reload();}, this);						
						}
						this.groupDialog.show();
					},
					scope: this
				}
			],
			store: new Ext.data.GroupingStore({
				reader: new Ext.data.JsonReader({
					totalProperty: "total",
					root: "results",
					id: "id",
					fields:fields.fields
				}),
				baseParams: {
					permissionLevel: GO.permissionLevels.read
				},
				proxy: new Ext.data.HttpProxy({
					url:GO.url('addressbook/addresslist/store')
				}),        
				groupField:'addresslistGroupName',
				remoteSort:true,
				remoteGroup:true
			}),
			view:new Ext.grid.GroupingView({
				autoFill:true,
				forceFit:true,
		    hideGroupedColumn:true,
		    groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
		   	emptyText: t("No items found"),
		   	showGroupName:false,
				startCollapsed:true
			}),
			border: false,
			paging:GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):true,
			editDialogClass:GO.addressbook.AddresslistDialog,
			view:new Ext.grid.GridView({
				emptyText: t("No items to display"),
				autoFill: true,
				forceFit: true
			}),
			cm:this.columnModel
		});
		
		GO.addressbook.AddresslistsGrid.superclass.initComponent.call(this);
		
		this.store.load();
	}
});
