/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: UserPriceDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
GO.servermanager.UserPriceDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'sm-userPrice',
			layout:'fit',
			title:GO.servermanager.lang.users,
			width: 400,
			height: 120,
			resizable:false,
			formControllerUrl: 'servermanager/userPrice'
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
					xtype: 'numberfield',
					fieldLabel: GO.servermanager.lang.users,
					name: 'max_users',
					allowBlank: false,
					decimals: 0
				},
				{
					xtype: 'numberfield',
					fieldLabel: GO.servermanager.lang.price,
					name: 'price_per_month',
					allowBlank:false
				}
			]
		});	

		this.addPanel(this.formPanel);
	}
	
});