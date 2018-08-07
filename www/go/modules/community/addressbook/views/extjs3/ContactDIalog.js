go.modules.community.addressbook.ContactDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-contact-dialog',
	title: t("Contact"),
	entityStore: go.Stores.get("Contact"),
	width: 600,
	height: 600,

	initFormItems: function () {

		var items = [{
				xtype: 'fieldset',			
				layout: "hbox",
				items: [
					{
						flex: 1,
						layout: "form",
						items: [
							{
								xtype: "switch",
								boxLabel: t("This is an organization"),
								name: "isOrganization",
								hideLabel: true,
								listeners: {
									check: function (sw, checked) {
										this.setOrganization(checked);
									},
									scope: this
								}
							},
							{
								xtype: "hidden",
								name: "addressBookId"
							},

							this.nameField = new Ext.form.TextField({
								xtype: 'textfield',
								name: 'name',
								fieldLabel: t("Name"),
								anchor: '-' + dp(28), //for delete button of gridfields
								allowBlank: false,
								hidden: true
							})
							,this.contactNameField = new Ext.form.CompositeField({
								xtype: 'compositefield',
								
								fieldLabel: t("Name"),
								anchor: '-' + dp(28), //for delete button of gridfields
								allowBlank: false,
								items: [{
									xtype: 'textfield',
									name: 'firstName',
									emptyText: t("First name"),
									flex: 2,									
									listeners: {
										change: this.buildFullName,
										scope: this
									}
								},{
									xtype: 'textfield',
									name: 'middleName',
									emptyText: t("Middle name"),
									flex: 1,									
									listeners: {
										change: this.buildFullName,
										scope: this
									}
								},{
									xtype: 'textfield',
									name: 'lastName',
									emptyText: t("Last name"),
									flex: 2,									
									listeners: {
										change: this.buildFullName,
										scope: this
									}
								}]
							}),
						]
					},
					{
						width: dp(120),
						
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
							}),
						]
					}
					//new go.modules.community.addressbook.ContactBookCombo(),

				]
			},

			{
				xtype: 'fieldset',
				title: t("Communication"),
				autoHeight: true,
				items: [
					{
						xtype: "gridfield",
						name: "emailAddresses",
						store: new Ext.data.JsonStore({
							autoDestroy: true,
							root: "records",
							fields: [
								'id',
								'type',
								'email'
							]
						}),
						fieldLabel: t("E-mail addresses"),

						autoExpandColumn: "email",
						columns: [
							{
								xtype: "combocolumn",
								store: new Ext.data.ArrayStore({
									id: 0,
									fields: [
										'value',
										'display'
									],
									data: [['work', t("Work")], ['home', t("Home")]]
								}),
								id: 'type',
								sortable: false,
								dataIndex: 'type',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								width: dp(100)

							}, {
								id: 'email',
								sortable: false,
								dataIndex: 'email',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								editor: new Ext.form.TextField({
									allowBlank: false,
									vtype: 'emailAddress'
								})
							}
						]
					}

					, {
						xtype: "gridfield",
						name: "phoneNumbers",
						store: new Ext.data.JsonStore({
							autoDestroy: true,
							root: "records",
							fields: [
								'id',
								'type',
								'number'
							]
						}),
						fieldLabel: t("Phone numbers"),

						autoExpandColumn: "number",
						columns: [
							{
								xtype: "combocolumn",
								store: new Ext.data.ArrayStore({
									id: 0,
									fields: [
										'value',
										'display'
									],
									data: [['mobile', t("Mobile")], ['work', t("Work")], ['home', t("Home")]]
								}),
								id: 'type',
								sortable: false,
								dataIndex: 'type',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								width: dp(100)

							}, {
								id: 'number',
								sortable: false,
								dataIndex: 'number',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								editor: new Ext.form.TextField({
									allowBlank: false
								})
							}
						]
					}


				]
			}
		];//.concat(go.CustomFields.getFormFieldSets("Contact"));

		return items;
	},

	setOrganization: function (isOrganization) {
		this.contactNameField.setVisible(!isOrganization);
		this.nameField.setVisible(isOrganization);
	},
	
	buildFullName : function() {
		var f = this.formPanel.getForm(), name = f.findField('firstName').getValue(),
			m = f.findField('middleName').getValue(),
			l = f.findField('lastName').getValue();
			
		if(m) {
			name += " " + m;
		}
		
		if(l) {
			name += " " + l;
		}
		console.log(name);
		
		this.nameField.setValue(name);
		
	}
});
