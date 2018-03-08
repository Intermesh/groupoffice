/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ModulePriceDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
GO.servermanager.ModulePriceDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'modulePrice',
			layout:'fit',
			title:GO.servermanager.lang.modulePrice,
			width: 400,
			height: 120,
			resizable:false,
			formControllerUrl: 'servermanager/modulePrice'
		});
		
		GO.servermanager.ModulePriceDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function () {

		this.formPanel = new Ext.Panel({
			cls:'go-form-panel',
			layout:'form',
			labelWidth:100,
			items: [
				{
					xtype: 'combo',
					fieldLabel: GO.servermanager.lang['moduleName'],
					mode: 'remote',
					autoLoad: true,
					triggerAction: 'all',
					hiddenName: 'module_name',
					store: new GO.data.JsonStore({
						url : GO.url('servermanager/installation/modules'),
						fields : ['id','name']
					}),
					valueField: 'id',
					displayField: 'name'
				},
				{
					xtype: 'numberfield',
					fieldLabel: GO.servermanager.lang['modulePrice'],
					name: 'price_per_month',
					allowBlank:false
				}
			]
		});	

		this.addPanel(this.formPanel);
	}
	
});