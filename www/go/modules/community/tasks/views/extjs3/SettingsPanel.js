/* global go, Ext */

go.modules.community.tasks.SettingsPanel = Ext.extend(Ext.Panel, {
	title: t("Tasks"),
	iconCls: 'ic-todo',
	labelWidth: 125,
	layout: "form",
	initComponent: function () {

		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
			xtype: "fieldset",
			title: t("Display options for tasklists"),
			items: [
				{
					xtype: "tasklistcombo",
					hiddenName: "tasksSettings.defaultTasklistId",
					fieldLabel: t("Default tasklist"),
					allowBlank: true
				},
				this.defaultTasklistOptions = new go.form.RadioGroup({
					allowBlank: true,
					fieldLabel: t('Start in'),
					name: 'tasksSettings.rememberLastItems',
					columns: 1,

					items: [
						{
							boxLabel: t("Default tasklist"),
							inputValue: false
						},
						{
							boxLabel: t("Remember last selected tasklist"),
							inputValue: true
						}
					]
				})
			]
		}
		];

		go.modules.community.tasks.SettingsPanel.superclass.initComponent.call(this);
	}
});
