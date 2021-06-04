/* global go, Ext */

go.modules.community.notes.SettingsPanel = Ext.extend(Ext.Panel, {
	title: t("Notes"),
	iconCls: 'ic-note',
	labelWidth: 125,
	layout: "form",
	initComponent: function () {

		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
			xtype: "fieldset",
			title: t("Display options for notebooks"),
			items: [
				{
					xtype: "notebookcombo",
					hiddenName: "notesSettings.defaultNoteBookId",
					fieldLabel: t("Default note book"),
					allowBlank: true
				},
				this.defaultNoteBookOptions = new go.form.RadioGroup({
					allowBlank: true,
					fieldLabel: t('Start in'),
					name: 'notesSettings.rememberLastItems',
					columns: 1,

					items: [
						{
							boxLabel: t("Default note book"),
							inputValue: false
						},
						{
							boxLabel: t("Remember last selected note book"),
							inputValue: true
						}
					]
				})
			]
		}
		];

		go.modules.community.notes.SettingsPanel.superclass.initComponent.call(this);
	}
});
