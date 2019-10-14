/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistGroupDialog.js 21434 2017-09-14 12:59:40Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.addressbook.AddresslistGroupDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'ab-addresslistgroup',
			title: t("Addresslist group", "addressbook"),
			formControllerUrl: 'addressbook/addresslistgroup',
			width:400,
			height:120,
			enableApplyButton : false
		});
		
		GO.addressbook.AddresslistGroupDialog.superclass.initComponent.call(this);
	},
	
	buildForm : function () {
		
		this.nameField = new Ext.form.TextField({
			  name: 'name',
				anchor: '100%',
			  allowBlank:false,
			  fieldLabel: t("Name")
			});
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.nameField
			]
		});
		
		this.addPanel(this.propertiesPanel);
	}
});
