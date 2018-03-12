/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistGroupGrid.js 21434 2017-09-14 12:59:40Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
GO.addressbook.AddresslistGroupGrid = Ext.extend(GO.grid.GridPanel,{
	
	noDelete: !(GO.settings.modules.addressbook.permission_level >= GO.permissionLevels.write),
	standardTbarDisabled : !(GO.settings.modules.addressbook.permission_level >= GO.permissionLevels.write),
	
	initComponent : function(){

		var fields = {
			fields:['id', 'name'],
			columns:[{
				header: GO.lang.strId,
				dataIndex: 'id',
				hidden:true,				
				width:30
			},{
				header: GO.lang.strName,
				dataIndex: 'name'
			}
		]};

		this.columnModel = new Ext.grid.ColumnModel({
			defaults:{
				sortable:true
			},
			columns: fields.columns
		});
				
		Ext.apply(this,{
			id: 'ab-addresslist-group-grid',
			title:GO.addressbook.lang.groups,
			standardTbar:true,
			store: new GO.data.JsonStore({
				url: GO.url('addressbook/addresslistgroup/store'),
				root: 'results',
				id: 'id',
				totalProperty:'total',
				fields: fields.fields,
				baseParams: {
					permissionLevel: GO.permissionLevels.write
				},
				remoteSort:true
			}),
			view:new Ext.grid.GridView({
				emptyText: GO.lang['strNoItems'],
				autoFill: true,
				forceFit: true
			}),
			border: false,
			paging:true,
			editDialogClass:GO.addressbook.AddresslistGroupDialog,
			cm:this.columnModel
		});
		
		GO.addressbook.AddresslistGroupGrid.superclass.initComponent.call(this);
		
		this.store.load();
	}
});