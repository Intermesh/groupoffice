/* global go, Ext */

go.modules.community.notes.SettingsPanel = Ext.extend(Ext.Panel, {
	title: t("Notes"),
	iconCls: 'ic-note',
	labelWidth: 125,
	layout: "form",
	initComponent: function () {
		var me = this;

		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
			xtype: "fieldset",
			title: t("Display options for notebooks", "notes", "community"),
			validationEvent: false,
			items: [
				this.noteBookCombo = {
					xtype: "notebookcombo",
					hiddenName: "notesSettings.defaultNoteBookId",
					fieldLabel: t("Default note book"),
					allowBlank: true
				},
				this.defaultNoteBookOptions = new Ext.form.RadioGroup({
					allowBlank: true,
					fieldLabel: t("Default behavior for notebooks", 'notes', 'community'),
					name: 'notesSettings.defaultNoteBookOptions',
					columns: 1,
					listeners: {
						'added': function (elm, ownerCt, index) {
							if (me.rememberLastItemHiddenCB.checked) {
								me.rememberLastItemOption.setValue(true);
							} else {
								me.defaultNoteBookOption.setValue(true);
							}
						},
						'change': function (elm, rb) {
							var rli = false;
							if (rb.id === 'rememberLastItems') {
								rli = true;
							}
							me.rememberLastItemHiddenCB.setValue(rli);
						}
					},
					items: [
						this.defaultNoteBookOption = new Ext.form.Radio({
							id: 'defaultNoteBookByDefault',
							boxLabel: t("Start in Default Notebook"),
							name: 'noteBookOptions',
							hint: t("Remember last selected notebook when reopening the notes module", 'notes', 'community')
						}),
						this.rememberLastItemOption = new Ext.form.Radio({
							id: 'rememberLastItems',
							boxLabel: t("Remember last selected item"),
							name: 'noteBookOptions',
							hint: t("Remember last selected notebook when reopening the notes module", 'notes', 'community')
						})
					]
				}),
				this.rememberLastItemHiddenCB = new Ext.form.Checkbox({
					hidden: true,
					name: 'notesSdukeettings.rememberLastItems',
					fieldLabel: 'rememberLastItems'
				})

			]
		}
		];

		go.modules.community.notes.SettingsPanel.superclass.initComponent.call(this);
	},

	onSubmitStart: function (value) {
		if(value.notesSettings) {
			delete value.notesSettings.defaultNoteBookOptions;
		}
	},

	onLoadStart: function (userId) {

	},

	onLoadComplete: function (action) {

	},

	onSubmitComplete: function () {

	}

});
