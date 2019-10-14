
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
			items: [{
					xtype: "notebookcombo",
					hiddenName: "notesSettings.defaultNoteBookId",
					fieldLabel: t("Default note book"),
					allowBlank: true
				}]
			}
		];

		go.modules.community.notes.SettingsPanel.superclass.initComponent.call(this);
	}

});

