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
GO.addressbook.AddresslistGroupGridDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'ab-addresslistgroupGridDialog',
			title: t("Addresslist group", "addressbook"),
			loadOnNewModel:false,
			width:600,
			height:400,
			enableOkButton : false,
			enableApplyButton : false
		});
		
		GO.addressbook.AddresslistGroupGridDialog.superclass.initComponent.call(this);
	},
	
	buildForm : function () {
		this.addresslistGroupGrid = new GO.addressbook.AddresslistGroupGrid();
		this.addPanel(this.addresslistGroupGrid);
	}
});
