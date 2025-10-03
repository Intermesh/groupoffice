go.modules.community.tasks.TasklistDialog = Ext.extend(go.form.Dialog, {
	title: t("List"),
	entityStore: "TaskList", // or "SupportList" for support module
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
			this.width = dp(600);

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
			},{
				xtype:'colorfield',
				name: 'color',
				fieldLabel: t('Color')
			}
			, new go.modules.community.tasks.TaskListGroupingCombo(),
			{xtype:'checkbox', name: 'syncToDevice', boxLabel: t('Sync to device'), hint: t('Make tasklist available in CalDAV and ActiveSync')}
			]
		}];
	}
});
