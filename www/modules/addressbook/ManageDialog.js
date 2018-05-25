/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: ManageDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.addressbook.ManageDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
	
	initComponent : function(){

		Ext.apply(this, {
			title:t("Manage", "addressbook"),
			formControllerUrl: 'addressbook/settings',
			enableOkButton : false,
			enableApplyButton : false,
			width:900,
			height:600
		});
		
		GO.addressbook.ManageDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function(){
		
		this.addressbooksGrid = new GO.addressbook.ManageAddressbooksGrid();
		this.templatesGrid = new GO.addressbook.TemplatesGrid();
		this.addresslistsGrid = new GO.addressbook.AddresslistsGrid();
		
		this.addPanel(this.addressbooksGrid);
		this.addPanel(this.templatesGrid);
		this.addPanel(this.addresslistsGrid);
		
		if(GO.settings.has_admin_permission){
			this.exportPermissionsTab = new GO.grid.PermissionsPanel({
				title:t("Export permissions", "addressbook"),
				hideLevel:true
			});
			this.addPanel(this.exportPermissionsTab);
		}
	},
	
	show : function(){
		if(!this.rendered){
			this.render(Ext.getBody());
		}

		if(GO.settings.has_admin_permission){
			this.exportPermissionsTab.setAcl(GO.addressbook.export_acl_id);
		}
		
		GO.addressbook.ManageDialog.superclass.show.call(this);
	}
});
