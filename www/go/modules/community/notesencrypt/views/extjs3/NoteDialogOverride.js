Ext.onReady(function() {
	Ext.override(go.modules.community.notes.NoteDialog, {
		onLoad: go.modules.community.notes.NoteDialog.prototype.onLoad.createSequence(function(entityValues) {
			var me = this;
			var contentField = this.find('name', 'content')[0];
			var noteBookId = entityValues.noteBookId
			if(noteBookId == go.modules.community.notes.lastNoteBookId && !go.modules.community.notes.isUsingOldEncryption(entityValues.content)) {
				if(go.modules.community.notes.lastDecryptedValue != "") {
					contentField.setValue(go.modules.community.notes.lastDecryptedValue);
					this.checkEncrypt.setValue(true);
					this.passwordField.setValue(go.modules.community.notes.password);
					this.confirmPasswordField.setValue(go.modules.community.notes.password);
				}
			}

			var content = contentField.getRawValue();

			if(go.modules.community.notes.isEncrypted(content)) {
				contentField.setValue(t("Encrypted data"));
				content = go.modules.community.notes.stripTag(content);
				if(!go.modules.community.notes.isUsingOldEncryption(content)) {

					var dlg = new GO.dialog.PasswordDialog({
						title: t("Enter password to decrypt"),
						scope: this,
						handler: function (dlg, btn, password) {
							if (btn == "ok") {
								go.modules.community.notes.aesGcmDecrypt(content, password).then(function (plaintext) {
									contentField.setValue(plaintext);
								}).catch(function (error) {
									Ext.Msg.alert(t("Error", "Password"), t("Wrong password"));
									me.hide();
								});
							}
						}
					});
					dlg.show();
				}
			}
		}),
		initComponent: go.modules.community.notes.NoteDialog.prototype.initComponent.createSequence(function() {
			this.passwordField = new Ext.form.TextField({
				fieldLabel: t("Password"),
				inputType: 'password',
				anchor:'100%',
				visible: false,
				submit: false
			});


			this.confirmPasswordField = new Ext.form.TextField({
				fieldLabel: t("Confirm"),
				inputType: 'password',
				anchor:'100%',
				visible: false,
				submit: false
			});

			this.passwordField.setVisible(false);
			this.confirmPasswordField.setVisible(false);

			this.formPanel.ownerCt.doLayout();
			var contentField = this.find('name', 'content')[0];
			this.findByType("fieldset")[0].items.insert(2,this.checkEncrypt = new Ext.form.Checkbox(
				{
					xtype: 'checkbox',
					name: 'encryptcheck',
					fieldLabel: 'Encrypt content',
					submit:false,
					listeners: {
						check: function(obj,checked) {
							contentField.submit = !checked;
							this.passwordField.setVisible(checked);
							this.confirmPasswordField.setVisible(checked);
							this.passwordField.setDisabled(!checked);
							this.confirmPasswordField.setDisabled(!checked);
						},
						scope: this
					},
				}
			));
			this.findByType("fieldset")[0].items.insert(3,this.passwordField);
			this.findByType("fieldset")[0].items.insert(4,this.confirmPasswordField);
			var passfield = this.passwordField;

		}),

		submit: function() {
			var contentField = this.find('name', 'content')[0],
				isEncrypted = this.checkEncrypt.getValue();
			if(isEncrypted == true) {

				var passfield = this.passwordField.getValue();
				var passconfirm = this.confirmPasswordField.getValue();
				var name = this.titleField.getValue();

				if(passfield != passconfirm || passfield == "" || passconfirm == "") {
					Ext.Msg.alert(t("Error", "Password"), "Passwords do not match");
				} else {
					go.modules.community.notes.aesGcmEncrypt(contentField.getRawValue(), this.passwordField.getValue()).then(function(text){

						this.formPanel.values.content = "{ENCRYPTED}" + text;
						go.modules.community.notes.NoteDialog.superclass.submit.call(this);
					}.bind(this));
				}

			} else {
				go.modules.community.notes.NoteDialog.superclass.submit.call(this);
			}
		}
	});
});
