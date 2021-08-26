Ext.onReady(function() {
	Ext.override(go.modules.community.notes.NoteDialog, {
		onLoad: go.modules.community.notes.NoteDialog.prototype.onLoad.createSequence(function(entityValues) {
			var me = this;
			var contentField = this.find('name', 'content')[0];
			var content = contentField.getRawValue();

			if(go.modules.community.notes.isEncrypted(content)) {
				contentField.setValue(t("Encrypted data"));
				content = go.modules.community.notes.stripTag(content);
				if(!go.modules.community.notes.isUsingOldEncryption(content)) {

					if(!crypto.subtle) {
						GO.errorDialog.show(t("Cryptographic functions are not available. Please run Group-Office with SSL."));
						this.close();
						return;
					}


					//check if data was just decrypted in detail view
					if(go.modules.community.notes.decrypted[entityValues.id]) {
						contentField.setValue(go.modules.community.notes.decrypted[entityValues.id].content);

						me.checkEncrypt.setValue(true);
						me.passwordField.setValue(go.modules.community.notes.decrypted[entityValues.id].password);
						me.confirmPasswordField.setValue(go.modules.community.notes.decrypted[entityValues.id].password);
					} else {

						var dlg = new GO.dialog.PasswordDialog({
							title: t("Enter password to decrypt"),
							scope: this,
							handler: function (dlg, btn, password) {
								if (btn == "ok") {
									go.modules.community.notes.aesGcmDecrypt(content, password).then(function (plaintext) {
										contentField.setValue(plaintext);

										me.checkEncrypt.setValue(true);
										me.passwordField.setValue(password);
										me.confirmPasswordField.setValue(password);

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

			//this.formPanel.ownerCt.doLayout();
			var contentField = this.find('name', 'content')[0];
			var firstFieldSet = this.findByType("fieldset")[0];
			firstFieldSet.items.itemAt(1).anchor = '0 -120';
			firstFieldSet.items.insert(2,this.checkEncrypt = new Ext.form.Checkbox(
				{
					xtype: 'checkbox',
					name: 'encryptcheck',
					fieldLabel: 'Encrypt content',
					submit:false,
					listeners: {

						check: function(obj,checked) {

							if(checked && !crypto.subtle) {
								GO.errorDialog.show(t("Cryptographic functions are not available. Please run Group-Office with SSL."));
								obj.setValue(false);
								return false;
							}

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
		}),

		submit: function() {
			var contentField = this.find('name', 'content')[0],
				isEncrypted = this.checkEncrypt.getValue();
			if(isEncrypted == true) {

				if(contentField.getRawValue().indexOf('<img') > -1) {
					Ext.MessageBox.alert(t("Error"), t("Sorry, you can't use images in encrypted notes"));
					return Promise.reject(t("Sorry, you can't use images in encrypted notes"));
				}

				var passfield = this.passwordField.getValue();
				var passconfirm = this.confirmPasswordField.getValue();
				var name = this.titleField.getValue();

				if(passfield != passconfirm || passfield == "" || passconfirm == "") {
					Ext.Msg.alert(t("Error", "Password"), "Passwords do not match");
				} else {
					var plaintext = contentField.getRawValue(), password = this.passwordField.getValue();

					return go.modules.community.notes.aesGcmEncrypt(plaintext, password).then(function(text){

						this.formPanel.values.content = "{ENCRYPTED}" + text;
						if(!go.modules.community.notes.decrypted || !go.modules.community.notes.decrypted[this.currentId]) {
							go.modules.community.notes.decrypted = {};
						}

						var me = this;

						return go.modules.community.notes.NoteDialog.superclass.submit.call(this).then(function() {
							go.modules.community.notes.decrypted[me.currentId] = {};
							go.modules.community.notes.decrypted[me.currentId].content = plaintext;
							go.modules.community.notes.decrypted[me.currentId].password = password;
						});
					}.bind(this));
				}

			} else {
				return go.modules.community.notes.NoteDialog.superclass.submit.call(this);
			}
		}
	});
});
