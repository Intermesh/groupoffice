/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ViewGroupGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
GO.calendar.ViewGroupGrid = Ext.extend(GO.grid.GridPanel,{

	initComponent : function(){
		
		Ext.apply(this,{
			title:t("Group", "calendar"),
			standardTbar:true,
			store: new GO.data.JsonStore({
				url:GO.url("calendar/viewGroup/store"),
				fields:['id','name', 'description']
			}),
                        editDialogClass: GO.dialog.SelectGroups,
			border: false,
			paging:true,
			listeners:{
				show:function(){
					this.store.load();
				},
				scope:this
			},
			cm:new Ext.grid.ColumnModel({
				defaults:{
					sortable:true
				},
				columns:[
				{
					header: t("Name"), 
					dataIndex: 'name'
				},
                {
					header: t("Description"), 
					dataIndex: 'description'
				}
				]
			})
		});
		
		GO.calendar.ViewGroupGrid.superclass.initComponent.call(this);		
	}
});
