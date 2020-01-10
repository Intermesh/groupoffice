go.modules.community.notes.NoteDialog = Ext.extend(go.form.Dialog, {
	stateId: 'notes-noteForm',
	title: t("Note"),
	entityStore: "Note",
	width: dp(800),
	height: dp(800),
	maximizable: true,
	collapsible: true,
	modal: false,
	
	initFormItems: function () {
		
	var formFieldSets = go.customfields.CustomFields.getFormFieldSets("Note").filter(function(fs) {return !fs.fieldSet.isTab;}),
			 fieldSetAnchor = formFieldSets.length ? '100% 80%' : '100% 100%';
		
		var items = [{
				xtype: 'fieldset',
				anchor: fieldSetAnchor,
				items: [new go.modules.community.notes.NoteBookCombo({
					value: go.User.notesSettings.defaultNoteBookId 
				}),
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					},
					{
						xtype: 'xhtmleditor',
						name: 'content',
						fieldLabel: "",
						hideLabel: true,
						anchor: '0 -90',
						allowBlank: false,
						listeners: {
							scope: this,
							ctrlenter: function() {
								this.submit();
							}
						}
					}]
			}
		];

		return items;
	}
});
