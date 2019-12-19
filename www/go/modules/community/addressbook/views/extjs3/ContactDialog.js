/* global Ext, go, GO */

go.modules.community.addressbook.ContactDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-contact-dialog',
	title: t("Contact"),
	entityStore: "Contact",
	width: dp(800),
	height: dp(1000),
	modal: false,
	maximizable: true,
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

	focus: function () {
		if(this.formPanel.currentId) {
			return;
		}
		if (this.nameField.getValue() != "") {
			this.jobTitle.focus();
		} else
		{
			this.nameField.focus();
		}
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
	setDialogValues: function(firstName, middleName, lastName,email) {
		this.nameField.firstName.setValue(firstName);
		this.nameField.middleName.setValue(middleName);
		this.nameField.lastName.setValue(lastName);
		var panel = this.emailAddressesField.addPanel();
		this.emailAddressesField.doLayout();
		var emailField = panel.formField.items.items[0].items.items[1];
		emailField.setValue(email);
		//var example = panel.formField.find("name","email");//.setValue(firstName);
	},
	initFormItems: function () {

		this.addPanel(this.businessPanel = new Ext.Panel({
			title: t("Business"),
			items: [{
					xtype: "fieldset",
					defaults: {
						anchor: "-20",
					},
					items: [

						{
							xtype: "textfield",
							name: "IBAN",
							fieldLabel: t("IBAN")
						},
						this.registrationNumberField = new Ext.form.TextField({
							xtype: "textfield",
							name: "registrationNumber",
							fieldLabel: t("Registration number")
						}),
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
				}]

		}));
		var items = [this.infoFieldSet = new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [
					{
						layout: "hbox",
						items: [
							{
								flex: 1,
								layout: "form",
								items: [
									this.nameField = new go.modules.community.addressbook.NameField({
										flex: 1
									}),

									this.jobTitle = new Ext.form.TextField({
										xtype: "textfield",
										name: "jobTitle",
										fieldLabel: t("Job title"),
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
						anchor: '-20',
						xtype: "chips",
						entityStore: "Contact",
						displayField: "name",
						valueField: 'id',
						allowNew: {
							isOrganization: true,
							addressBookId: go.User.addressBookSettings.defaultAddressBookId 
						},
						comboConfig: {
							sortInfo: {
								field: 'firstName',
								direction: 'ASC'
							},
							baseParams:  {
								filter: {
									isOrganization: true
								}
							}
						},
						name: "organizationIds",
						fieldLabel: t("Organizations")
					}),

					this.addressBook = new go.modules.community.addressbook.AddresBookCombo({
						anchor: '-20',
						value: go.User.addressBookSettings ? go.User.addressBookSettings.defaultAddressBookId : null,
						allowBlank: false,
						listeners: {
							scope: this,
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
					this.phoneNumbersField = new go.modules.community.addressbook.PhoneNumbersField()
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

		var me = this;

		// this.avatarComp.menu.items.item(1).setHandler( this.avatarComp.menu.items.item(1).handler.createSequence(function() {
		// 	window.open("https://www.google.com/search?q=" + encodeURIComponent(me.getValues()['name']) + "&tbm=isch");
		// }), this.avatarComp);


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

		if (isOrganization) {
			this.tabPanel.unhideTabStripItem(this.businessPanel);
			this.jobTitle.setFieldLabel(t("LOB"));
		} else
		{
			this.tabPanel.hideTabStripItem(this.businessPanel);
			this.jobTitle.setFieldLabel(t("Job title"));
		}

		this.nameField.nameMenuEnabled = !isOrganization;
		
		this.updateTitle();
	}
});
