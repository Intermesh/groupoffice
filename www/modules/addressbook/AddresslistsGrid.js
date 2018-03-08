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
			fields:['id', 'name', 'user_name','acl_id'],
			columns:[{
				header: t("ID"),
				dataIndex: 'id',
				hidden:true,				
				width:30
			},{
				header: t("Name"),
				dataIndex: 'name'
			},{
				header: t("Owner", "addressbook"),
				dataIndex: 'user_name' ,
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
			store: new GO.data.JsonStore({
				url: GO.url('addressbook/addresslist/store'),
				root: 'results',
				id: 'id',
				totalProperty:'total',
				fields: fields.fields,
				baseParams: {
					permissionLevel: GO.permissionLevels.write
				},
				remoteSort: true,
				model:"GO\\Addressbook\\Model\\Addresslist"
			}),
			border: false,
			paging:true,
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