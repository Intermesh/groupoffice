/* global Ext, go, GO */

go.modules.community.addressbook.ContactDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-contact-dialog',
	title: t("Contact"),
	entityStore: "Contact",
	width: dp(800),
	height: dp(1000),
	defaults: {
		labelWidth: dp(140)
	},
	
	focus : function() {
		if(this.nameField.getValue() != "") {
			this.jobTitle.focus();
		} else
		{
			this.nameField.focus();
		}
	},	
	
	initComponent: function() {
		
		go.modules.community.addressbook.ContactDialog.superclass.initComponent.call(this);
		
		this.formPanel.on("setvalues", function(form, v){
			this.setOrganization(v["isOrganization"]);
		}, this);
		
		
		//register name menu form fields
		this.nameMenu.items.get(0).items.each(function(i) {						
			this.formPanel.form.add(i);
		}, this);
	},
	
	langToStoreData : function(langKey) {
		var emailTypes = [], emailTypeLang = t(langKey);
		
		for(var key in emailTypeLang) {
			emailTypes.push([key, emailTypeLang[key]]);
		}
		return emailTypes;
	},
	
	createNameMenu : function() {
		var me = this;		
		
		this.nameMenu = new Ext.menu.Menu({			
			items: this.createContactNameFieldSet(),
			focus : function() {
				me.firstName.focus();
			},
			listeners: {
				hide: function() {
					this.buildFullName();
					this.jobTitle.focus();
				},
				afterrender: function(menu) {
					
					this.nameMenu.keyNav.destroy();
					this.nameMenu.keyNav = new Ext.KeyNav(menu.getEl(), {
							enter : function(e){
								e.preventDefault();
								this.nameMenu.hide();
							},
							scope: this
					});
					
					this.suffixField.on('specialkey', function(field, e) {
						
						// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
						// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
						if (e.getKey() == e.TAB) {
								
								this.nameMenu.hide();
						}

					}, this);
				},
				scope: this
			}
		});
		
		return this.nameMenu;
	},

	initFormItems: function () {		
		
		this.createNameMenu();
		
		var items = [{
				xtype: 'fieldset',
				items: [
					{
						layout: "hbox",
						items: [
							{
								flex: 1,
								layout: "form",
								items: [		
									this.nameField = new Ext.form.TextField({
										xtype: 'textfield',
										flex: 1,
										name: 'name',
										fieldLabel: t("Name"),
										anchor: '100%',
										allowBlank: false,
										listeners: {
											scope: this,
											focus: function() {		
												if(!this.getValues()['isOrganization']) {
													this.nameMenu.show(this.nameField.getEl());
												}
											}
										}
									}),										
									
									this.jobTitle = new Ext.form.TextField({
										xtype: "textfield",
										name: "jobTitle",
										fieldLabel: t("Job title"),
										anchor: "100%"
									}), 
									this.genderField = new go.form.RadioGroup({
										xtype: 'radiogroup',
										fieldLabel: t("Gender"),
										name:"gender",
										value: null,
										items: [
											{boxLabel: t("Unknown"), inputValue: null},
											{boxLabel: t("Male"), inputValue: 'M'},
											{boxLabel: t("Female"), inputValue: 'F'}
										]
									})
								]
							},
							{
								width: dp(152),								
								style: "padding-left: " + dp(16) + "px",
								layout: "form",
								items: [
									this.avatarComp = new go.form.FileField({
										hideLabel: true,
										buttonOnly: true,
										name: 'photoBlobId',
										height: dp(120),
										cls: "avatar",
										autoUpload: true,
										buttonCfg: {
											text: '',
											width: dp(120)
										},
										setValue: function (val) {
											if (this.rendered && !Ext.isEmpty(val)) {
												this.wrap.setStyle('background-image', 'url(' + go.Jmap.downloadUrl(val) + ')');
											}
											go.form.FileField.prototype.setValue.call(this, val);
										},
										accept: 'image/*'
									})
								]
							}
						]
					},
					
					
					this.organizationsField = new go.form.Chips({
						anchor: '-20',
						xtype: "chips",
						entityStore: "Contact",
						displayField: "name",
						valueField: 'id',
						storeBaseParams: {
							filter: {
								isOrganization: true
							}
						},
						name: "organizationIds",
						fieldLabel: t("Organizations")
					}),
					
					this.addressBook = new go.modules.community.addressbook.AddresBookCombo({
						anchor: '-20',
						value: go.User.addressBookSettings ? go.User.addressBookSettings.defaultAddressBookId : null,
						allowBlank: false
					})
							

					//new go.modules.community.addressbook.ContactBookCombo(),

//					this.organizationsField = new go.form.FormGroup({
//						name: "organizations",
//						fieldLabel: t("Organizations"),
//						itemCfg: {
//							layout: "form",
//							items: [{
//									hideLabel: true,
//									xtype: "contactcombo",
//									hiddenName: "organizationContactId",
//									permissionLevel: GO.permissionLevels.write,
//									isOrganization: true
//								}]
//						}
//					})

				]
			},

			{
				xtype: 'fieldset',
				title: t("Communication"),
				autoHeight: true,
				defaults: {
					anchor: "-20"
				},
				items: [
					{
						hideLabel: true,
						xtype: "formgroup",
						name: "emailAddresses",
						addButtonIconCls: 'ic-email',
						addButtonText: t("Add e-mail address"),
						itemCfg: {
							anchor: "100%",
							layout: "form",
							items: [{			
									anchor: "100%",
									xtype: "compositefield",
									hideLabel: true,
									items: [
										{
											xtype: 'combo',
											name: 'type',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											store: new Ext.data.ArrayStore({
												idIndex: 0,
												fields: [
													'value',
													'display'
												],
												data: this.langToStoreData('emailTypes')
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(140),
											value: "work"
										}, 
										{
											flex: 1,
											xtype: "textfield",
											allowBlank: false,
											vtype: 'emailAddress',
											name: "email",
											setFocus: true
										}]
								}]
						}
					}
					,

					{
						xtype: "formgroup",
						name: "phoneNumbers",
						addButtonText: t("Add phone number"),
						addButtonIconCls: 'ic-phone',
						itemCfg: {
							layout: "form",
							items: [{
									xtype: "compositefield",
									hideLabel: true,
									items: [{
											xtype: 'combo',
											name: 'type',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											store: new Ext.data.ArrayStore({
												idIndex: 0,
												fields: [
													'value',
													'display'
												],
												data: this.langToStoreData('phoneTypes')
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(140),
											value: "work"
										}, {
											flex: 1,
											xtype: "textfield",
											allowBlank: false,
											name: "number",
											setFocus: true
										}]
								}]
						}
					}
				]
			}, {
				xtype: "fieldset",
				//title: t("Street addresses"),
				defaults: {
					anchor: "-20"
				},
				
				items: [{
						hideLabel: true,
						xtype: "formgroup",
						name: "addresses",
						addButtonText: t("Add street address"),
						addButtonIconCls: 'ic-add-location',
						pad: true,
						itemCfg: {
							xtype: "panel",
							layout: "form",
							labelWidth: dp(140),
							items: [{
									anchor: "100%",
									fieldLabel: t("Type"),
									xtype: 'combo',
									name: 'type',
									mode: 'local',
									editable: false,
									triggerAction: 'all',
									store: new Ext.data.ArrayStore({
										id: 0,
										fields: [
											'value',
											'display'
										],
										data: this.langToStoreData('addressTypes')
									}),
									valueField: 'value',
									displayField: 'display',
									value: "work"
								}, {
									xtype: "textfield",
									fieldLabel: t("Street"),
									name: "street",
									anchor: "100%",
									setFocus: true
								}, {
									xtype: "textfield",
									fieldLabel: t("Street 2"),
									name: "street2",
									anchor: "100%"
								},{
									xtype: "textfield",
									fieldLabel: t("ZIP code"),
									name: "zipCode",
									anchor: "100%"
								}, {
									xtype: "textfield",
									fieldLabel: t("City"),
									name: "city",
									anchor: "100%"
								}, {
									xtype: "textfield",
									fieldLabel: t("State"),
									name: "state",
									anchor: "100%"
								}, {
									xtype: "selectcountry",
									fieldLabel: t("Country"),
									hiddenName: "countryCode",
									anchor: "100%"
								}]
						}
					}
				]
			}, {
				xtype: "fieldset",
				title: t("Other"),
				defaults: {
					anchor: "-20"
				},
				items: [{
						xtype: "formgroup",
						name: "dates",
						addButtonText: t("Add date"),
						addButtonIconCls: 'ic-event',
						itemCfg: {
							
							items: [{
									xtype: "compositefield",
									hideLabel: true,
									items: [{
											xtype: 'combo',
											name: 'type',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											store: new Ext.data.ArrayStore({
												id: 0,
												fields: [
													'value',
													'display'
												],
												data: this.langToStoreData("dateTypes")
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(140),
											value: "birthday"
										}, {
											flex: 1,
											xtype: "datefield",
											allowBlank: false,
											name: "date",
											setFocus: true
										}]
								}]
						}
					},
					{
						xtype: "formgroup",
						name: "urls",
						addButtonText: t("Add online url"),
						addButtonIconCls: 'ic-home',
						itemCfg: {
							layout: "form",
							items: [{
									xtype: "compositefield",
									hideLabel: true,
									items: [{
											xtype: 'combo',
											name: 'type',
											mode: 'local',
											editable: false,
											triggerAction: 'all',
											store: new Ext.data.ArrayStore({
												id: 0,
												fields: [
													'value',
													'display'
												],
												data: this.langToStoreData("urlTypes")
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(140),
											value: "homepage"
										}, {
											flex: 1,
											xtype: "textfield",
											allowBlank: false,
											name: "url",
											setFocus: true
										}]
								}]
						}
					},				
					{						
						fieldLabel: t("Notes"),
						name: "notes",
						xtype: "textarea",
						grow: true
					}]
			}, 
			this.businessFieldSet = new Ext.form.FieldSet({
				xtype: "fieldset",
				title: t("Business"),
				defaults: {
					anchor: "-20",
				},
				items: [
					{
						xtype: "textfield",
						name: "IBAN",
						fieldLabel: t("IBAN")
					},
					{
						xtype: "textfield",
						name: "registrationNumber",
						fieldLabel: t("Registration number")
					},
					{
						xtype: "textfield",
						name: "debtorNumber",
						fieldLabel: t("Customer number")
					},
					{
						xtype: "xcheckbox",
						name: "vatReverseCharge",
						hideLabel: true,
						boxLabel: t("Reverse charge VAT")
					},
					{
						xtype: "textfield",
						name: "vatNo",
						fieldLabel: t("VAT number")
					}
				]
			})
		].concat(go.modules.core.core.CustomFields.getFormFieldSets("Contact"));

		return items;	
	},

	setOrganization: function (isOrganization) {
		//this.contactNameField.setVisible(!isOrganization);
		//this.nameField.setVisible(isOrganization);
		this.organizationsField.setVisible(!isOrganization);		
		this.genderField.setVisible(!isOrganization);
		this.businessFieldSet.setVisible(isOrganization);
	},

	buildFullName: function () {
		var f = this.formPanel.getForm(), name = f.findField('firstName').getValue(),
						m = f.findField('middleName').getValue(),
						l = f.findField('lastName').getValue();

		if (m) {
			name += " " + m;
		}

		if (l) {
			name += " " + l;
		}
		console.log(name);

		this.nameField.setValue(name);

	},
	
//	onBeforeSubmit : function() {
//		
//		//build full name on submit. Because when ENTER is pressed in one of the name
//		//fields the blur event is not triggered.
//		if(!this.getValues()['isOrganization']) {
//			
//		}
//		
//		return go.modules.community.addressbook.ContactDialog.superclass.onBeforeSubmit.call(this);
//	},
	
	
	createContactNameFieldSet: function () {
		return new Ext.form.FieldSet(
						{
							items: [
								{
									xtype: 'textfield',
									name: 'prefixes',
									fieldLabel: t("Prefix")
								}, this.firstName = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'firstName',
									fieldLabel: t("First")
								}), {
									xtype: 'textfield',
									name: 'middleName',
									fieldLabel: t("Middle")
								}, {
									xtype: 'textfield',
									name: 'lastName',
									fieldLabel: t("Last")
								}, this.suffixField = new Ext.form.TextField({
									xtype: 'textfield',
									name: 'suffixes',
									fieldLabel: t("Suffix")
								})
							]
						});
	}
});
