/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: ContactDialog.js 22345 2018-02-08 15:24:09Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */


GO.addressbook.ContactDialog = function(config)
{
	config = config || {};

	this.goDialogId = 'contact';

	this.personalPanel = new GO.addressbook.ContactProfilePanel();

	this.photoPanel = new GO.addressbook.PicturePanel({
		getName: function() {
			var f= this.formPanel.form;
			var mn = f.findField('middle_name').getValue();

			if(mn)
				mn = ' '+mn+' ';
			else
				mn = ' ';
			return f.findField('first_name').getValue()+mn+f.findField('last_name').getValue();
		}.createDelegate(this)
	});

	this.commentPanel = new Ext.Panel({
		title: t("Remarks", "addressbook"), 
		layout: 'fit',
		forceLayout:true,
		border:false,
		items: [ new Ext.form.TextArea({
			name: 'comment',
			fieldLabel: '',
			hideLabel: true,
			anchor:'100% 100%'
		})
		]
	});
	
	this.personalPanel.on('show', function() { 
		var firstName = Ext.get('first_name');					
		if (firstName) {
			firstName.focus();
		}
	}, this);
		
	this.commentPanel.on('show', function(){ 
		this.formPanel.form.findField('comment').focus();
	}, this);
	
	//var selectMailingsPanel = new GO.addressbook.SelectMailingsPanel();
	
	this.socialMediaPanel = new Ext.form.FieldSet({
		title: t("Social media", "addressbook"), 
		layout: 'form',
		defaults: {
			anchor: '100%',
			xtype: 'textfield'
		},
		items: [{
			name: 'url_linkedin',
			fieldLabel: t("LinkedIn URL", "addressbook")
		},{
			name: 'url_facebook',
			fieldLabel: t("Facebook URL", "addressbook")
		},{
			name: 'url_twitter',
			fieldLabel: t("Twitter URL", "addressbook")
		},{
			name: 'skype_name',
			fieldLabel: t("Skype name", "addressbook")
		}]
	});
	
	var items = [
		this.personalPanel,
		this.photoPanel,
		this.socialMediaPanel,
		this.commentPanel
	];
	 
	this.selectAddresslistsPanel = new GO.addressbook.SelectAddresslistsPanel();
				
	items.push(this.selectAddresslistsPanel);
	
	if(GO.customfields && GO.customfields.types["GO\\Addressbook\\Model\\Contact"]) {
		for(var i=0;i<GO.customfields.types["GO\\Addressbook\\Model\\Contact"].panels.length;i++) {
			items.push(GO.customfields.types["GO\\Addressbook\\Model\\Contact"].panels[i]);
		}
	}

//	if(go.Modules.isAvailable("legacy", "comments")){
//		this.commentsGrid = new GO.comments.CommentsGrid({title:t("Comments", "comments")});
//		items.push(this.commentsGrid);
//	}

	this.formPanel = new Ext.FormPanel({
		waitMsgTarget:true,
		baseParams: {},
		border: false,
		fileUpload : true, // for picture panel
		items: [
		this.tabPanel = new Ext.TabPanel({
			border: false,
			activeTab: 0,
			hideLabel: true,
			deferredRender: false,
			enableTabScroll:true,
			anchor:'100% 100%',
			items: items
		})
		]
	});
	
	
	//this.downloadDocumentButton = new Ext.Button();

	this.collapsible=true;
	this.stateId= 'addressbook-window-new-contact';
	this.layout= 'fit';
	this.modal=false;
	this.shadow= false;
	this.border= false;
	this.height= 640;
	
	//autoHeight= true;
	this.width= 820;
	this.plain= true;
	this.closeAction= 'hide';
	//this.iconCls= 'btn-addressbook-contact';
	this.title= t("Edit contact", "addressbook");
	this.items= this.formPanel;
	this.buttons= [{
		text: t("Save"),
		handler:function(){
			this.saveContact(true);
		},
		scope: this
	}];
	
	var focusFirstField = function(){
		this.formPanel.form.findField('first_name').focus(true);
	};
	
	this.focus= focusFirstField.createDelegate(this);
	
	
	this.personalPanel.formAddressBooks.on({
		scope:this,
		change:function(sc, newValue, oldValue){
			var record = sc.store.getById(newValue);
			GO.customfields.disableTabs(this.tabPanel, record.data,'contactCustomfields');	
		}
	});

	GO.addressbook.ContactDialog.superclass.constructor.call(this, config);
	
	this.addEvents({
		'save':true
	});

//	if(go.Modules.isAvailable("core", "customfields")) {
//		this.personalPanel.formAddressBooks.on('select',function(combo,record,index){
//			var allowed_cf_categories = record.data.allowed_cf_categories.split(',');
//			this.updateCfTabs(allowed_cf_categories);
//		},this);
//	}
}

Ext.extend(GO.addressbook.ContactDialog, GO.Window, {

	show : function(contact_id, config)
	{
		
		var config = config || {};
	
		if(!this.rendered) {
			this.render(Ext.getBody());
		}
		
		if(!GO.addressbook.writableAddresslistsStore.loaded) {
			GO.addressbook.writableAddresslistsStore.load({
				callback:function(){
					this.show(contact_id, config);
				},
				scope:this
			});
		} else if(!GO.addressbook.writableAddressbooksStore.loaded) {
			GO.addressbook.writableAddressbooksStore.load({
				callback:function(){
					this.show(contact_id, config);
				},
				scope:this
			});
		} else {
			this.formPanel.form.reset();

			this.contact_id = contact_id || 0;
			
			if(!GO.util.empty(config.addresslistIds))
				this.setAddresslistCheckBoxes(config.addresslistIds);

			this.loadContact(this.contact_id, config);

			if(config.activeTab)
				this.tabPanel.setActiveTab(config.activeTab);
			else
				this.tabPanel.setActiveTab(0);
		}

	},


	/*setAddressbookId : function(addressbook_id)
	{
		this.personalPanel.formAddressBooks.setValue(addressbook_id);
		this.personalPanel.formCompany.store.baseParams['addressbook_id'] = addressbook_id;			
		this.addressbook_id = addressbook_id;
	},*/
	
	loadContact : function(id, config)
	{
		this.beforeLoad();
		
		var params = config.values || {};
		params.id = id;
		
		this.formPanel.form.load({
			url:GO.url('addressbook/contact/load'),
			params:params,
			success: function(form, action) {
				
				if(config && config.values)
					this.formPanel.form.setValues(config.values);

				this.personalPanel.setAddressbookID(action.result.data.addressbook_id);
				this.formPanel.form.findField('addressbook_id').setRemoteText(action.result.remoteComboTexts.addressbook_id);
				this.formPanel.form.findField('company_id').setRemoteText(action.result.remoteComboTexts.company_id);
				
				

				if(go.Modules.isAvailable("core", "customfields"))
					GO.customfields.disableTabs(this.tabPanel, action.result);	

				if (!GO.util.empty(config.contactData)) {
					this.personalPanel.formFirstName.setValue(config.contactData['first_name']);
					this.personalPanel.formMiddleName.setValue(config.contactData['middle_name']);
					this.personalPanel.formLastName.setValue(config.contactData['last_name']);
					this.personalPanel.formTitle.setValue(config.contactData['title']);
					this.personalPanel.formAfternameTitle.setValue(config.contactData['suffix']);
					this.personalPanel.sexCombo.setValue(config.contactData['sex']);
					this.personalPanel.formBirthday.setValue(config.contactData['birthday']);
					if (!GO.util.empty(this.personalPanel.formEmail))
						this.personalPanel.formEmail.setValue(config.contactData['email']);
					this.personalPanel.formEmail2.setValue(config.contactData['email2']);
					this.personalPanel.formEmail3.setValue(config.contactData['email3']);
					this.personalPanel.formHomePhone.setValue(config.contactData['home_phone']);
					this.personalPanel.formFax.setValue(config.contactData['fax']);
					this.personalPanel.formCellular.setValue(config.contactData['cellular']);
					this.personalPanel.formHomepage.setValue(config.contactData['homepage']);
					this.personalPanel.formAddress.setValue(config.contactData['address']);
					this.personalPanel.formAddressNo.setValue(config.contactData['address_no']);
					this.personalPanel.formPostal.setValue(config.contactData['zip']);
					this.personalPanel.formCity.setValue(config.contactData['city']);
					this.personalPanel.formState.setValue(config.contactData['state']);
					this.personalPanel.formCountry.setValue(config.contactData['country']);
					this.personalPanel.formWorkPhone.setValue(config.contactData['work_phone']);
					this.personalPanel.formWorkFax.setValue(config.contactData['work_fax']);
//						this.personalPanel.formCompany.setValue(config.contactData['company_id']);
					this.personalPanel.formDepartment.setValue(config.contactData['department']);
					this.personalPanel.formFunction.setValue(config.contactData['function']);
				}

				this.afterLoad(action);

				GO.addressbook.ContactDialog.superclass.show.call(this);
				
				this.photoPanel.setPhoto(action.result.data.photo_url, action.result.data.original_photo_url);

			},
			scope: this
		});
	},	
	afterLoad  : function(action){		},
	beforeLoad  : function(){},
	saveContact : function(hide)
	{		
		var company = this.personalPanel.formCompany.getRawValue();

		this.formPanel.form.submit({
			waitMsg:t("Saving..."),
			url:GO.url('addressbook/contact/submit'),			
			params: {				
				id : this.contact_id,
				company: company
			},
			success:function(form, action){
				
				if (!action.result.success) {
					Ext.MessageBox.alert(t("Error"), action.result.feedback);
				} else {
				
					if(action.result.id)
					{
						this.contact_id = action.result.id;
					}
					
					this.photoPanel.setPhoto(action.result.photo_url, action.result.original_photo_url);
					
					this.fireEvent('save', this, this.contact_id);

					GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);

					if (hide) {
						this.hide();
					}
				}
			},
			failure: function(form, action) {				
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));			
				} else {
					Ext.MessageBox.alert(t("Error"), action.result.feedback);
				}
			},
			scope: this
		});
	},
	
	setAddresslistCheckBoxes : function(addresslistIds) {
		for (var i=0; i<addresslistIds.length; i++) {
			var field = this.formPanel.find('name', 'addresslist_'+addresslistIds[i]);
			if (!GO.util.empty(field))
				field[0].setValue(true);
		}
	}
	
});
