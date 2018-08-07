go.modules.community.addressbook.ContactDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-contact-dialog',
	title: t("Contact"),
	entityStore: go.Stores.get("Contact"),
	width: 600,
	height: 600,

	initFormItems: function () {

		var items = [{
				xtype: 'fieldset',
				autoHeight: true,
				items: [
					//new go.modules.community.addressbook.ContactBookCombo(),
					{
						xtype: "hidden",
						name: "addressBookId"
					},
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '-' + dp(28), //for delete button of gridfields
						allowBlank: false
					}, {
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
					
					,{
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
	}
});
