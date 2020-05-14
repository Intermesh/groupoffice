go.modules.community.tasks.TasklistDialog = Ext.extend(go.form.Dialog, {
	title: t("Tasklists", "tasks"),
	entityStore: "Tasklist",
	titleField: "name",
	width: dp(800),
	height: dp(600),
	initFormItems: function () {
		this.addPanel(new go.permissions.SharePanel());

		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					},new go.users.UserCombo({
						hiddenName: 'ownerId'
					})]
			}
		];
	}
});
