/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.addressbook.AddresslistDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'ab-addresslist',
			title:t("Address list", "addressbook"),
			formControllerUrl: 'addressbook/addresslist',
			width:750,
			height:600
		});
		
		GO.addressbook.AddresslistDialog.superclass.initComponent.call(this);
	},
	
	buildForm : function () {
		
		this.nameField = {
				xtype: 'textfield',
			  name: 'name',
				anchor: '100%',
			  allowBlank:false,
			  fieldLabel: t("Name")
			};
			
		this.defaultSalutationField = {
				xtype: 'textfield',
			  name: 'default_salutation',
				anchor: '100%',
			  allowBlank:false,
			  fieldLabel: t("Salutation", "addressbook"),
			  value: t("Dear", "addressbook")+' '+t("sir", "addressbook")+'/'+t("madam", "addressbook")
			};
		
		this.selectALGroup = new GO.addressbook.SelectAddresslistGroup({
			hiddenName:'addresslist_group_id',
			fieldLabel:  t("Addresslist group", "addressbook"),
			anchor: '100%'
		});
		
		this.selectUser = new GO.form.SelectUser({
			fieldLabel:t("Owner"),
			disabled: !GO.settings.has_admin_permission,
			value: GO.settings.user_id,
			anchor: '100%'
		});
		

		
		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),			
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.nameField,
				this.selectALGroup,
				new GO.form.HtmlComponent({
					html: t("Enter a salutation that is used when the salutation for the recipient is unknown", "addressbook"),
					style:'padding:10px 0px'
				}),
				this.defaultSalutationField,
				this.selectUser
			]
		});
		
		this.contactsGrid = new GO.addressbook.AddresslistContactsGrid();
		this.companiesGrid = new GO.addressbook.AddresslistCompaniesGrid();

		this.addPanel(this.propertiesPanel);
		this.addPanel(this.contactsGrid);
		this.addPanel(this.companiesGrid);
		this.addPermissionsPanel(new GO.grid.PermissionsPanel());
	},

	setRemoteModelId : function(remoteModelId){
		GO.addressbook.AddresslistDialog.superclass.setRemoteModelId.call(this, remoteModelId);
				
		this.contactsGrid.setMailingId(remoteModelId);
		this.companiesGrid.setMailingId(remoteModelId);
	}
});
