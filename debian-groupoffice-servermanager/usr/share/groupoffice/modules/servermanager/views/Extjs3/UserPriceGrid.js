/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: UserPriceGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */

GO.servermanager.UserPriceGrid = function(config){

	config = config || {};

	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	
	config.editDialogClass = GO.servermanager.UserPriceDialog;

	config.title=t("users", "servermanager");
	config.store = new GO.data.JsonStore({
		url : GO.url('servermanager/userPrice/store'),
		fields:['max_users','price_per_month'],
		id: 'max_users'
	});

	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[	{
			header: t("users", "servermanager"),
			dataIndex: 'max_users',
			editor: new Ext.form.TextField({
				allowBlank: false
			})
		},{
			header: t("price", "servermanager"),
			dataIndex: 'price_per_month',
			editor: new GO.form.NumberField({
				allowBlank: false
			}),
			align:'right'
		}]
	});

	config.cm=columnModel;
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")
	});
	config.sm=new Ext.grid.RowSelectionModel( {singleSelect : true} );
	config.loadMask=true;

	config.tbar=[{
		iconCls: 'btn-add',
		text: t("Add"),
		cls: 'x-btn-text-icon',
		handler: function(){
			this.showEditDialog();
		},
		scope: this
	},{
		iconCls: 'btn-delete',
		text: t("Delete"),
		cls: 'x-btn-text-icon',
		handler: function(){
		    this.deleteSelected()
		},
		scope: this
	}];

	GO.servermanager.UserPriceGrid.superclass.constructor.call(this, config);

};
Ext.extend(GO.servermanager.UserPriceGrid, GO.grid.GridPanel,{

});
