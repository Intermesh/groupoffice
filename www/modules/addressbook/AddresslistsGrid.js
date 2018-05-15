/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistsGrid.js 21434 2017-09-14 12:59:40Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
GO.addressbook.AddresslistsGrid = Ext.extend(GO.grid.GridPanel,{
	
	noDelete: !(GO.settings.modules.addressbook.permission_level >= GO.permissionLevels.write),
	standardTbarDisabled : !(GO.settings.modules.addressbook.permission_level >= GO.permissionLevels.write),
	
	initComponent : function(){

		var fields = {
			fields:['id', 'name', 'user_name','acl_id','addresslistGroupName'],
			columns:[{
				header: GO.lang.strId,
				dataIndex: 'id',
				groupable:false,
				hidden:true,				
				width:30
			},{
				header: GO.lang.strName,
				dataIndex: 'name',
				groupable:false,
			},{
				header: GO.addressbook.lang.addresslistGroup,
				dataIndex: 'addresslistGroupName'
			},{
				header: GO.addressbook.lang.cmdOwner,
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
			title:GO.addressbook.lang.cmdPanelMailings,
			standardTbar:true,
			tbar: [
				'-'
				,{
					iconCls: 'btn-folder',
					text: GO.addressbook.lang.manageGroups,
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
		   	emptyText: GO.lang.strNoItems,
		   	showGroupName:false,
				startCollapsed:true
			}),
			border: false,
			paging:GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):true,
			editDialogClass:GO.addressbook.AddresslistDialog,
			cm:this.columnModel
		});
		
		GO.addressbook.AddresslistsGrid.superclass.initComponent.call(this);
		
		this.store.load();
	}
});