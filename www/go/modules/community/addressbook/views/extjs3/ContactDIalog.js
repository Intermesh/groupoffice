/* global Ext, go, GO */

go.modules.community.addressbook.ContactDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-contact-dialog',
	title: t("Contact"),
	entityStore: go.Stores.get("Contact"),
	width: 600,
	height: 600,

	initFormItems: function () {

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
										name: 'name',
										fieldLabel: t("Name"),
										anchor: '100%',
										allowBlank: false,
										hidden: true
									}),

									this.contactNameField = new Ext.form.CompositeField({
										xtype: 'compositefield',

										fieldLabel: t("Name"),
										anchor: '100%',
										allowBlank: false,
										items: [{
												xtype: 'textfield',
												name: 'firstName',
												emptyText: t("First name"),
												flex: 3,
												listeners: {
													change: this.buildFullName,
													scope: this
												}
											}, {
												xtype: 'textfield',
												name: 'middleName',
												emptyText: t("Middle name"),
												flex: 2,
												listeners: {
													change: this.buildFullName,
													scope: this
												}
											}, {
												xtype: 'textfield',
												name: 'lastName',
												emptyText: t("Last name"),
												flex: 3,
												listeners: {
													change: this.buildFullName,
													scope: this
												}
											}]
									}),
									{
										xtype: "textfield",
										name: "jobTitle",
										fieldLabel: t("Job title"),
										anchor: "100%"
									}, this.genderField = new go.form.RadioGroup({
										xtype: 'radiogroup',
										fieldLabel: t("Gender"),
										name:"gender",
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
								style: "padding: " + dp(16) + "px",
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
					}

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
				items: [
					{
						xtype: "formgroup",
						name: "emailAddresses",
						fieldLabel: t("E-mail addresses"),
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
												data: [['work', t("emailTypes")["work"]], ['private', t("emailTypes")['private']]]
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(120),
											value: "work"
										}, {
											flex: 1,
											xtype: "textfield",
											allowBlank: false,
											vtype: 'emailAddress',
											name: "email"
										}]
								}]
						}
					}
					,

					{
						xtype: "formgroup",
						name: "phoneNumbers",
						fieldLabel: t("Phone numbers"),
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
												data: [['work', t("phoneTypes")["work"]], ['private', t("phoneTypes")['private']]]
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(120),
											value: "work"
										}, {
											flex: 1,
											xtype: "textfield",
											allowBlank: false,
											name: "number"
										}]
								}]
						}
					}
				]
			}, {
				xtype: "fieldset",
				title: t("Street addresses"),
				items: [{
						hideLabel: true,
						xtype: "formgroup",
						name: "addresses",
						pad: true,
						itemCfg: {
							xtype: "panel",
							layout: "form",
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
										data: [['work', t("addressTypes")["work"]], ['private', t("addressTypes")['private']]]
									}),
									valueField: 'value',
									displayField: 'display',
									value: "work"
								}, {
									xtype: "textfield",
									fieldLabel: t("Street"),
									name: "street",
									anchor: "100%"
								}, {
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
									xtype: "textfield",
									fieldLabel: t("Country"),
									name: "country",
									anchor: "100%"
								}]
						}
					}
				]
			}, {
				xtype: "fieldset",
				title: t("Other"),
				items: [{
						xtype: "formgroup",
						fieldLabel: t("Dates"),
						name: "dates",
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
												data: [['birthday', t("dateTypes")["birthday"]], ['anniversary', t("dateTypes")['anniversary']]]
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(120),
											value: "birthday"
										}, {
											flex: 1,
											xtype: "datefield",
											allowBlank: false,
											name: "date"
										}]
								}]
						}
					},
					{
						xtype: "formgroup",
						fieldLabel: t("Online"),
						name: "urls",
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
												data: [
													['homepage', t("urlTypes")["homepage"]],
													['twitter', t("urlTypes")["twitter"]],
													['facebook', t("urlTypes")["facebook"]],
													['linkedin', t("urlTypes")['linkedin']]
												]
											}),
											valueField: 'value',
											displayField: 'display',
											width: dp(120),
											value: "homepage"
										}, {
											flex: 1,
											xtype: "textfield",
											allowBlank: false,
											name: "url"
										}]
								}]
						}
					}]
			}
		].concat(go.modules.core.customfields.CustomFields.getFormFieldSets("Contact"));

		return items;	
	},
	
	show : function() {
		this.setOrganization(this.formPanel.getValues()["isOrganization"]);
		
		return go.modules.community.addressbook.ContactDialog.superclass.show.call(this);
	},

	setOrganization: function (isOrganization) {
		this.contactNameField.setVisible(!isOrganization);
		this.nameField.setVisible(isOrganization);
//		this.organizationsField.setVisible(!isOrganization);		
		this.genderField.setVisible(!isOrganization);
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

	}
});
