/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: ContactDialog.js 20952 2017-03-20 07:59:18Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */


GO.addressbook.ContactDialog = function(config)
{
	config = config || {};

	this.goDialogId = 'contact';
	this.originalPhotoUrl = Ext.BLANK_IMAGE_URL;

	this.personalPanel = new GO.addressbook.ContactProfilePanel();

	GO.addressbook.ContactPhoto = Ext.extend(Ext.BoxComponent, {
		autoEl : {
				tag: 'img',
				cls:'ab-photo',
				src:Ext.BLANK_IMAGE_URL
			},
	
		setPhotoSrc : function(url)
		{
			var now = new Date();
			if (this.el)
				this.el.set({
					src: GO.util.empty(url) ? Ext.BLANK_IMAGE_URL : url
				});
			this.setVisible(true);
		}
	});

	this.contactPhoto = new GO.addressbook.ContactPhoto();

	this.deleteImageCB = new Ext.form.Checkbox({
		boxLabel: GO.addressbook.lang.deleteImage,
		labelSeparator: '',
		name: 'delete_photo',
		allowBlank: true,
		hideLabel:true,
		disabled:true
	});

	this.uploadFile = new GO.form.UploadFile({
		inputName : 'image',
		max: 1
	})

	this.fullImageButton = new Ext.Button({
			text:GO.addressbook.lang.downloadFullImage,
			disabled:false,
			handler:function(){
				window.open(this.originalPhotoUrl,'_blank');
			},
			scope:this
		});

	this.photoPanel = new Ext.Panel({
		title : GO.addressbook.lang.photo,
		layout: 'form',
		border:false,
		cls : 'go-form-panel',		
		autoScroll:true,
		labelAlign:'top',
		items:[	{
				style:'margin-bottom:15px',
				xtype:'button',
				text:GO.addressbook.lang.searchForImages,
				scope:this,
				handler:function(){
					var f= this.formPanel.form;
					var mn = f.findField('middle_name').getValue();
					
					if(mn)
						mn = ' '+mn+' ';
					else
						mn = ' ';
					
					var name = f.findField('first_name').getValue()+mn+f.findField('last_name').getValue();
					var sUrl = 'http://www.google.com/search?tbm=isch&q="'+encodeURIComponent(name)+'"';
					window.open(sUrl);
				}
			},
			{
				
				xtype:'textfield',
				fieldLabel:GO.addressbook.lang.downloadPhotoUrl,
				name:'download_photo_url',
				anchor:'100%'
			},{
				style:'margin-top:15px;margin-bottom:10px;',
				html:GO.addressbook.lang.orBrowseComputer+':',
				xtype:'htmlcomponent'
			},
			this.uploadFile,
			{
				style:'margin-top:15px',
				html:GO.addressbook.lang.currentImage+':',
				xtype:'htmlcomponent'
			},
			this.contactPhoto,
			this.deleteImageCB,
			this.fullImageButton
		]
	});

	this.commentPanel = new Ext.Panel({
		title: GO.addressbook.lang['cmdPanelComments'], 
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
	
	this.personalPanel.on('show', 
		function()
		{ 
			var firstName = Ext.get('first_name');					
			if (firstName)
			{
				firstName.focus();
			}
		}, this);
		
	this.commentPanel.on('show', function(){ 
		this.formPanel.form.findField('comment').focus();
	}, this);
	
	//var selectMailingsPanel = new GO.addressbook.SelectMailingsPanel();
	
	this.socialMediaPanel = new Ext.Panel({
		title: GO.addressbook.lang['cmdPanelSocialMedia'], 
		layout: 'form',
		border:false,
		cls : 'go-form-panel',	
		items: [ new Ext.form.TextField({
			name: 'url_linkedin',
			fieldLabel: GO.addressbook.lang['linkedinUrl'],
			anchor:'-20',
			maxLength: 100
		}), new Ext.form.TextField({
			name: 'url_facebook',
			fieldLabel: GO.addressbook.lang['facebookUrl'],
			anchor:'-20',
			maxLength: 100
		}), new Ext.form.TextField({
			name: 'url_twitter',
			fieldLabel: GO.addressbook.lang['twitterUrl'],
			anchor:'-20',
			maxLength: 100
		}), new Ext.form.TextField({
			name: 'skype_name',
			fieldLabel: GO.addressbook.lang['skypeName'],
			anchor:'-20',
			maxLength: 100
		}) ]
	});
	
	var items = [
		this.personalPanel,
		this.photoPanel,
		this.socialMediaPanel,
		this.commentPanel
	];
	 
	// Remove the original comment panel if it is set in the settings of the user.
	if(GO.comments && GO.comments.hideOriginalTab('contact')){
		items.pop();
	}
	
	this.selectAddresslistsPanel = new GO.addressbook.SelectAddresslistsPanel();
				
	items.push(this.selectAddresslistsPanel);
	
	if(GO.customfields && GO.customfields.types["GO\\Addressbook\\Model\\Contact"])
	{
		for(var i=0;i<GO.customfields.types["GO\\Addressbook\\Model\\Contact"].panels.length;i++)
		{
			items.push(GO.customfields.types["GO\\Addressbook\\Model\\Contact"].panels[i]);
		}
	}

	if(GO.comments){
		this.commentsGrid = new GO.comments.CommentsGrid({title:GO.comments.lang.comments});
		items.push(this.commentsGrid);
	}

	this.formPanel = new Ext.FormPanel({
		waitMsgTarget:true,
		baseParams: {},
		border: false,
		fileUpload : true,
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
	this.id= 'addressbook-window-new-contact';
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
	this.title= GO.addressbook.lang['cmdContactDialog'];
	this.items= this.formPanel;
	this.buttons= [
	{
		text: GO.lang['cmdOk'],
		handler:function(){
			this.saveContact(true);
		},
		scope: this
	},
	/*{
		text: GO.lang['cmdApply'],
		handler: function(){
			this.saveContact();
		},
		scope: this
	},*/
	{
		text: GO.lang['cmdClose'],
		handler: function()
		{
			this.hide();
		},
		scope: this
	}
	];
	
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

//	if (GO.customfields) {
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
	
		if(!this.rendered)
		{
			this.render(Ext.getBody());
		}
		
		if(!GO.addressbook.writableAddresslistsStore.loaded)
		{
			GO.addressbook.writableAddresslistsStore.load({
				callback:function(){
					//var values = GO.util.empty(contact_id) ? this.formPanel.form.getValues() : {};
					this.show(contact_id, config);
//					delete values.addressbook_id;
//					delete values.iso_address_format;
//					delete values.salutation;
//					this.formPanel.form.setValues(values);
				},
				scope:this
			});
		}else if(!GO.addressbook.writableAddressbooksStore.loaded)
		{
			GO.addressbook.writableAddressbooksStore.load(
			{
				callback:function(){
					//var values = GO.util.empty(contact_id) ? this.formPanel.form.getValues() : {};
					this.show(contact_id, config);
//					delete values.addressbook_id;
//					delete values.iso_address_format;
//					delete values.salutation;
//					this.formPanel.form.setValues(values);
				},
				scope:this
			});
		}else
		{
			this.formPanel.form.reset();

	
			if(contact_id)
			{
				this.contact_id = contact_id;
			} else {
				this.contact_id = 0;
			}
			
			if(!GO.util.empty(config.addresslistIds))
				this.setAddresslistCheckBoxes(config.addresslistIds);
						
//			if(this.contact_id > 0)
//			{
				this.loadContact(this.contact_id, config);
//			} else {
//				this.setPhoto(0);
//				GO.addressbook.ContactDialog.superclass.show.call(this);
//			}
			//var abRecord = this.personalPanel.formAddressBooks.store.getById(this.personalPanel.formAddressBooks.getValue());
			
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
				
//				if(!action.result.data.write_permission)
//				{
//					GO.errorDialog.show(GO.lang['strNoWritePermissions']);						
//				}else
//				{		

					if(config && config.values)
						this.formPanel.form.setValues(config.values);

					this.personalPanel.setAddressbookID(action.result.data.addressbook_id);
					this.formPanel.form.findField('addressbook_id').setRemoteText(action.result.remoteComboTexts.addressbook_id);
					this.formPanel.form.findField('company_id').setRemoteText(action.result.remoteComboTexts.company_id);
					if(!GO.util.empty(action.result.data.photo_url))
						this.setPhoto(action.result.data.photo_url);
					if(!GO.util.empty(action.result.data.original_photo_url))
						this.setOriginalPhoto(action.result.data.original_photo_url);

					if(GO.customfields)
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
					
					if(GO.comments){
						if(action.result.data['id'] > 0){
							if (!GO.util.empty(action.result.data['action_date'])) {
								this.commentsGrid.actionDate = action.result.data['action_date'];
							} else {
								this.commentsGrid.actionDate = false;
							}
							this.commentsGrid.setLinkId(action.result.data['id'], 'GO\\Addressbook\\Model\\Contact');
							this.commentsGrid.store.load();
							this.commentsGrid.setDisabled(false);
						} else {
							this.commentsGrid.setDisabled(true);
						}
					}
					
					this.afterLoad(action);
					
					GO.addressbook.ContactDialog.superclass.show.call(this);
				//}
			},
			scope: this
		});
	},
	
		
	afterLoad  : function(action){		
		if(!GO.util.empty(action.result.data.original_photo_url))
			this.setOriginalPhoto(action.result.data.original_photo_url);
		else
			this.setOriginalPhoto("");
		
		if(!GO.util.empty(action.result.data.photo_url))
			this.setPhoto(action.result.data.photo_url);
		else
			this.setPhoto("");
	},
	
	beforeLoad  : function(){
		
	},
	
	saveContact : function(hide)
	{		
		var company = this.personalPanel.formCompany.getRawValue();

		this.formPanel.form.submit({
			waitMsg:GO.lang['waitMsgSave'],
			url:GO.url('addressbook/contact/submit'),			
			params:
			{				
				id : this.contact_id,
				company: company
			},
			success:function(form, action){
				
				if (!action.result.success) {
					
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
					
				} else {
				
					if(action.result.id)
					{
						this.contact_id = action.result.id;
					}
					this.uploadFile.clearQueue();
					this.fireEvent('save', this, this.contact_id);

					GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);

					//this.personalPanel.setContactID(this.contact_id);
					if(!GO.util.empty(action.result.photo_url))
						this.setPhoto(action.result.photo_url);
					else
						this.setPhoto("");

					if(!GO.util.empty(action.result.original_photo_url))
						this.setOriginalPhoto(action.result.original_photo_url);
					else
						this.setOriginalPhoto("");				

					if (hide)
					{
						this.hide();
					}
				
				}
				
			},
			failure: function(form, action) {				
				if(action.failureType == 'client')
				{					
					Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);			
				} else {
					Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
				}
			},
			scope: this
		});
	},
	setOriginalPhoto : function(url){
		this.originalPhotoUrl = url;
	},
	setPhoto : function(url)
	{
		this.contactPhoto.setPhotoSrc(url);
		this.deleteImageCB.setValue(false);
		this.deleteImageCB.setDisabled(url=='');
	},
	
	setAddresslistCheckBoxes : function(addresslistIds) {
		for (var i=0; i<addresslistIds.length; i++) {
			var field = this.formPanel.find('name', 'addresslist_'+addresslistIds[i]);
			if (!GO.util.empty(field))
				field[0].setValue(true);
		}
	}
	
});
