/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddresslistDialog.js 21434 2017-09-14 12:59:40Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.addressbook.AddresslistDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'ab-addresslist',
			title:GO.addressbook.lang.addresslist,
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
			  fieldLabel: GO.lang.strName
			};
			
		this.defaultSalutationField = {
				xtype: 'textfield',
			  name: 'default_salutation',
				anchor: '100%',
			  allowBlank:false,
			  fieldLabel: GO.addressbook.lang.cmdFormLabelSalutation,
			  value: GO.addressbook.lang.cmdSalutation+' '+GO.addressbook.lang.cmdSir+'/'+GO.addressbook.lang.cmdMadam
			};
		
		this.selectUser = new GO.form.SelectUser({
			fieldLabel:GO.lang.strOwner,
			disabled: !GO.settings.has_admin_permission,
			value: GO.settings.user_id,
			anchor: '100%'
		});
		
		this.selectLinkField = new GO.form.SelectLink({
			anchor:'100%'
		});
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.nameField,	
				new GO.form.HtmlComponent({
					html: GO.addressbook.lang.defaultSalutationText,
					style:'padding:10px 0px'
				}),
				this.defaultSalutationField,
				this.selectUser,
				this.selectLinkField
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