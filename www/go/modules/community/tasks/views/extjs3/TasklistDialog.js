go.modules.community.tasks.TasklistDialog = Ext.extend(go.form.Dialog, {
	title: t("Tasklist", "tasks"),
	entityStore: "Tasklist",
	titleField: "name",
	width: dp(800),
	height: dp(600),
	redirectOnSave: false,
	hidePermissions: false,
	initFormItems: function () {
		if(!this.hidePermissions) {
			this.addPanel(new go.permissions.SharePanel());
		} else
		{
			this.height = null;
			this.autoHeight = true;
			this.width = dp(400);

		}

		return [{
			xtype: 'fieldset',
			items: [{
				xtype: 'hidden',
				allowBlank: false,
				value: 'list',
				name: 'role'
			},{
				xtype: 'hidden',
				allowBlank: false,
				value: null,
				name: 'projectId'
			},{
				xtype: 'textfield',
				name: 'name',
				fieldLabel: t("Name"),
				anchor: '100%',
				allowBlank: false
			}]
		}];
	}
});
