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
						plugins: [new GO.plugins.HtmlEditorImageInsert(), go.form.HtmlEditor.emojiPlugin],
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
	},

	onLoad : function(entityValues) {

		this.supr().onLoad.call(this, entityValues);

		if (!entityValues.content || entityValues.content.substring(0, 8) !== "{GOCRYPT") {
			return;
		}

		var contentField = this.find('name', 'content')[0];
		var noteBookId = entityValues.noteBookId
		if(noteBookId == go.modules.community.notes.lastNoteBookId && go.modules.community.notes.isUsingOldEncryption(entityValues.content)) {
			if(go.modules.community.notes.lastDecryptedValue != "") {
				contentField.setValue(go.modules.community.notes.lastDecryptedValue);
				this.checkEncrypt.setValue(true);
				this.passwordField.setValue(go.modules.community.notes.password);
				this.confirmPasswordField.setValue(go.modules.community.notes.password);
			} else {
				var data = entityValues.content, me = this;
				me.setValues({"content": t("Encrypted data")});
				go.modules.community.notes.Decrypter.decrypt(data).then(function(text) {
					me.setValues({"content": text});
				}).catch(function(){});
			}
		}



	}
});
