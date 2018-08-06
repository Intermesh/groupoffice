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
						anchor: '100%',
						allowBlank: false
					},{
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
						fieldLabel: "E-mail addresses",

						autoExpandColumn: "email",
						columns: [
							{
								id: 'type',
								sortable: false,
								dataIndex: 'type',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								width: dp(80),
								editor: new Ext.form.TextField({
									allowBlank: false,
									emptyText: "Type"
								})
							},{
								id: 'email',
								sortable: false,
								dataIndex: 'email',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								editor: new Ext.form.TextField({
									allowBlank: false,
									vtype: 'emailAddress',
									emptyText: "E-mail"
								})
							}
						],
					}]
			}
		];//.concat(go.CustomFields.getFormFieldSets("Contact"));

		return items;
	}
});
