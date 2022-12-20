/* global Ext, go, GO */

go.modules.community.addressbook.ContactDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-contact-dialog',
	title: t("Contact"),
	entityStore: "Contact",
	width: dp(800),
	height: dp(1000),
	modal: false,
	collapsible: true,
	defaults: {
		labelWidth: dp(140)
	},

	onBeforeSubmit: function() {

		//When address book has changed then clear groups.
		var modified = this.getValues(true);
		if(!("addressBookId" in modified)) {
			return true;
		}

		this.setValues({
			"groups" : []
		});

		return true;
	},

	setLinkEntity: function(config) {
		if(config.entity == "Contact") {
			var me = this;			
			go.Db.store("Contact").single(config.entityId).then(function(contact) {
				if(contact.isOrganization) {
					me.organizationsField.setValue([contact.id]);
					me.createLinkButton.reset();
					me.createLinkButton.cancelAddLink();
				} 
			});
		}
	},

	initComponent: function () {

		go.modules.community.addressbook.ContactDialog.superclass.initComponent.call(this);

		this.formPanel.on("setvalues", function (form, v) {
			if(Ext.isDefined(v.isOrganization)) {
				this.setOrganization(!!v.isOrganization);
			}

			if(v.addressBookId) {
				this.organizationsField.allowNew.addressBookId = v.addressBookId;
			}
		}, this);
	},

	updateTitle : function() {
		if(!this.origTitle) {
			this.origTitle = this.title;
		}
		var title = this.getValues().isOrganization ? t("Organization") : t("Contact"), v = this.titleField.getValue();

		if(v) {
			title += ": " + Ext.util.Format.htmlEncode(v);
		}

		this.setTitle(title);
	},

	initFormItems: function () {

		this.addPanel(this.businessPanel = new Ext.Panel({
			title: t("Business"),
			items: [
				{
					xtype: "fieldset",
					defaults: {
						anchor: "-20",
					},
					title: t("Information"),
					items: [
						this.registrationNumberField = new Ext.form.TextField({
							xtype: "textfield",
							name: "registrationNumber",
							fieldLabel: t("Registration number")
						}),
						{
							xtype: "textfield",
							name: "debtorNumber",
							fieldLabel: t("Customer number")
						}
					]
				},
				{
					xtype: "fieldset",
					defaults: {
						anchor: "-20",
					},
					title: t("Bank details"),
					items: [
						{
							xtype: "textfield",
							name: "nameBank",
							fieldLabel: t("Name bank")
						},
						{
							xtype: "textfield",
							name: "IBAN",
							fieldLabel: t("IBAN")
						},
						{
							xtype: "textfield",
							name: "BIC",
							fieldLabel: t("BIC")
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
				}
			]


		}));

		this.nameField = new go.modules.community.addressbook.NameField({
			flex: 1
		});

		this.colorField = new GO.form.ColorField({
			name: "color",
			width: dp(120),
			mobile: {
				width: undefined
			},
			fieldLabel: t("Color")
		});

		var items = [this.infoFieldSet = new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [
					{
						xtype: "container",
						cls: "go-hbox",
						items: [
							{
								flex: 1,
								layout: "form",

								mobile: {
									items: [
										this.nameField,
										this.colorField
									]
								},
								items: [
									{
										xtype: "container",
										flex: 1,
										cls: "go-hbox",
										items: [
											{
												flex: 1,
												layout:"form",
												items: [this.nameField]
											},{
												width: dp(8),
												xtype: "box"
											},
											{
												layout: "form",
												items: [
													this.colorField
												]
											}
										]
									},


									this.jobTitle = new Ext.form.TextField({
										xtype: "textfield",
										name: "jobTitle",
										fieldLabel: t("Job title"),
										anchor: "100%"
									}),
									this.departmentField = new Ext.form.TextField({
										xtype: "textfield",
										name: "department",
										fieldLabel: t("Department"),
										anchor: "100%"
									})
								]
							},
							{
								width: dp(152),
								style: "padding-left: " + dp(16) + "px",
								layout: "form",
								items: [
									this.avatarComp = new go.form.ImageField({			
										name: 'photoBlobId'										
									})
								]
							}
						]
					},

					this.genderField = new go.form.RadioGroup({
						anchor: '100%',
						xtype: 'radiogroup',
						fieldLabel: t("Gender"),
						name: "gender",
						value: null,
						items: [
							{boxLabel: t("Unknown"), inputValue: null},
							{boxLabel: t("Male"), inputValue: 'M'},
							{boxLabel: t("Female"), inputValue: 'F'}
						]
					}),

					this.organizationsField = new go.form.Chips({
						anchor: '100%',
						xtype: "chips",
						entityStore: "Contact",
						displayField: "name",
						valueField: 'id',
						allowNew: {
							isOrganization: true,
							addressBookId: go.User.addressBookSettings.defaultAddressBookId 
						},
						comboStoreConfig: {
							sortInfo: {
								field: 'name',
								direction: 'ASC'
							},
							filters:  {
								defaults: {
									isOrganization: true
								}
							}
						},
						name: "organizationIds",
						fieldLabel: t("Organizations")
					}),

					this.addressBook = new go.modules.community.addressbook.AddresBookCombo({
						anchor: '100%',
						value: go.User.addressBookSettings ? go.User.addressBookSettings.defaultAddressBookId : null,
						allowBlank: false,
						listeners: {
							scope: this,
							valuenotfound: function(cmp, id) {
								if(id == go.User.addressBookSettings.defaultAddressBookId) {

									GO.errorDialog.show("Your default address book wasn't found. Please select an address book and it will be set as default.");

									cmp.setValue(null);

									cmp.on('change', function(cmp, id) {
										go.Db.store("User").save({
											addressBookSettings: {defaultAddressBookId: id}
										}, go.User.id);
									}, {single: true});
								}
							},
							change: function(cmp, id) {
								go.customfields.CustomFields.filterFieldSets(this.formPanel);
								this.organizationsField.allowNew.addressBookId = id;
							}
						}
					})

				]
			}),

			this.communicationFieldSet = new Ext.form.FieldSet({
				xtype: 'fieldset',
				title: t("Communication"),
				layout: 'column',
				defaults: {
					columnWidth: 0.5,
					anchor: "-20"
				},
				mobile: {
					defaults: {
						columnWidth: 1,
						anchor: "-20"
					}
				},
				items: [
					this.emailAddressesField = new go.modules.community.addressbook.EmailAddressesField(),
					this.phoneNumbersField = new go.modules.community.addressbook.PhoneNumbersField(),
					{
						layout: "form",
						xtype: "container",
						items: {
							xtype: 'golanguagecombo'
						}
					}
				]
			}),
			{
				xtype: "fieldset",
				defaults: {
					anchor: "-20"
				},

				items: [this.addressesField = new go.modules.community.addressbook.AddressesField()]
			},
			{
				xtype: "fieldset",
				title: t("Other"),
				layout: 'column',
				defaults: {
					columnWidth: 0.5,
					anchor: "-20"
				},
				mobile: {
					defaults: {
						columnWidth: 1,
						anchor: "-20"
					}
				},
				items: [
					this.datesField = new go.modules.community.addressbook.DatesField(),
					this.urlsField = new go.modules.community.addressbook.UrlsField()

				]
			}
		];

		this.addPanel(new Ext.Panel({
			layout: 'fit',
			title: t("Notes"),
			items: [{
					xtype: "fieldset",
					layout: 'fit',
					items: [{
							name: "notes",
							xtype: "textarea"
						}]
				}]
		}));

		return items;
	},

	setOrganization: function (isOrganization) {
		
		this.organizationsField.setVisible(!isOrganization);
		this.organizationsField.setDisabled(isOrganization);
		this.genderField.setVisible(!isOrganization);
		this.genderField.setDisabled(isOrganization);

		this.departmentField.setVisible(!isOrganization);

		if (isOrganization) {
			this.tabPanel.unhideTabStripItem(this.businessPanel);
			this.jobTitle.setFieldLabel(t("LOB", "addressbook", "community"));
		} else
		{
			this.tabPanel.hideTabStripItem(this.businessPanel);
			this.jobTitle.setFieldLabel(t("Job title", "addressbook", "community"));
		}

		this.nameField.nameMenuEnabled = !isOrganization;
		
		this.updateTitle();
	}
});
