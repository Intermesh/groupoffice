Ext.onReady(function() {
	Ext.override(go.modules.community.notes.NoteDialog, {
		onLoad: go.modules.community.notes.NoteDialog.prototype.onLoad.createSequence(function(entityValues) {

			debugger;
			var me = this;
			var noteBookId = this.items.items[0].items.items[0].items.items[0].items.items[0].getValue();
			if(noteBookId == go.modules.community.notes.lastNoteBookId) {
				this.items.items[0].items.items[0].items.items[0].items.items[5].setValue(go.modules.community.notes.lastDecryptedValue);
			}

			var content = this.items.items[0].items.items[0].items.items[0].items.items[5].getRawValue();

			if(go.modules.community.notes.isEncrypted(content)) {
				content = go.modules.community.notes.stripTag(content);
				if(!go.modules.community.notes.isUsingOldEncryption(entityValues.content)) {
					var passwordPrompt = new go.PasswordPrompt({
						text: t('Provide your password.'),
						title: t('Current password required'),
						listeners: {
							'ok': function (password) {
								var cipher = this.items.items[0].items.items[0].items.items[0].items.items[5].getRawValue();
								go.modules.community.notes.aesGcmDecrypt(content, password).then(function (plaintext) {
									me.items.items[0].items.items[0].items.items[0].items.items[5].setValue(plaintext);
								}).catch(function (error) {
									Ext.Msg.alert(t("Error", "Password"), "Wrong password");
									me.hide();
								});
							},
							scope: this
						}
					});
					passwordPrompt.show();
				}
			}

			this.buttons[2].handler = function() {
				var me = this;
				var contentValue = this.items.items[0].items.items[0].items.items[0].items.items[5].getRawValue();
				var noteBookId = this.items.items[0].items.items[0].items.items[0].items.items[0].getValue();
				var noteName = this.titleField.getValue();
				var encryptCheck = this.checkEncrypt.getValue();

				if(encryptCheck && !go.modules.community.notes.isEncrypted(contentValue)) {
					var passfield = this.passwordField.getValue();
					var passconfirm = this.confirmPasswordField.getValue();
					var noteName = this.titleField.getValue();

					if(passfield != passconfirm || passfield == "" || passconfirm == "") {
						Ext.Msg.alert(t("Error", "Password"), "Passwords do not match");
					} else {
						var contentValue = this.items.items[0].items.items[0].items.items[0].items.items[5].getRawValue();
						go.modules.community.notes.aesGcmEncrypt(contentValue,passconfirm).then(function(encryptedValue){

							var update = {};
							update[entityValues.id] = {
								content: "{ENCRYPTED}" + encryptedValue,
								name: noteName,
								noteBookId: noteBookId
							};

							go.Db.store("Note").set({
								update: update
							}).then(function() {
								//go.modules.community.notes.lastDecryptedValue = me.data.content;
								me.hide();
							});
						});
					}
				} else {
					var update = {};
					update[entityValues.id] = {
						content: contentValue,
						name: noteName,
						noteBookId: noteBookId
					};

					go.Db.store("Note").set({
						update: update
					}).then(function() {
						me.hide();
					});
				}
			};
		}),
		initComponent: go.modules.community.notes.NoteDialog.prototype.initComponent.createSequence(function() {
			this.buttons[2].handler = function() {
				var me = this;
				var contentValue = this.items.items[0].items.items[0].items.items[0].items.items[5].getRawValue();
				var noteBookId = this.items.items[0].items.items[0].items.items[0].items.items[0].getValue();
				var noteName = this.titleField.getValue();
				var encryptCheck = this.checkEncrypt.getValue();

				if(encryptCheck) {
					var passfield = this.passwordField.getValue();
					var passconfirm = this.confirmPasswordField.getValue();
					var name = this.titleField.getValue();

					if(passfield != passconfirm || passfield == "" || passconfirm == "") {
						Ext.Msg.alert(t("Error", "Password"), "Passwords do not match");
					} else {
						var contentValue = this.items.items[0].items.items[0].items.items[0].items.items[5].getRawValue();
						go.modules.community.notes.aesGcmEncrypt(contentValue,passconfirm).then(function(encryptedValue){
							var create = {};

							create[Ext.id()] = {
								noteBookId: noteBookId,
								content: "{ENCRYPTED}" + encryptedValue,
								name: noteName
							};

							go.Db.store("Note").set({
								create: create
							}).then(function() {
								me.hide();
							});
						});
					}
				} else {
					var create = {};

					create[Ext.id()] = {
						noteBookId: noteBookId,
						content: contentValue,
						name: noteName
					};

					go.Db.store("Note").set({
						create: create
					}).then(function() {
						me.hide();
					});
				}
			};

			this.passwordField = new Ext.form.TextField({
				fieldLabel: t("Password"),
				inputType: 'password',
				allowBlank:false,
				anchor:'100%',
				visible: false,
				disabled: true
			});


			this.confirmPasswordField = new Ext.form.TextField({
				fieldLabel: t("Password"),
				inputType: 'password',
				allowBlank:false,
				anchor:'100%',
				visible: false,
				disabled: true
			});

			this.passwordField.setVisible(false);
			this.confirmPasswordField.setVisible(false);

			this.formPanel.items.items[0].items.items[0].items.insert(2,this.passwordField);
			this.formPanel.items.items[0].items.items[0].items.insert(3,this.confirmPasswordField);

			this.formPanel.ownerCt.doLayout();

			this.formPanel.items.items[0].items.items[0].items.insert(4,this.checkEncrypt = new Ext.form.Checkbox(
				{
					xtype: 'checkbox',
					name: 'encryptcheck',
					fieldLabel: 'Encrypt content',
					listeners: {
						check: function(obj,checked) {
							this.passwordField.setVisible(checked);
							this.passwordField.setDisabled(!checked);
							this.confirmPasswordField.setVisible(checked);
							this.confirmPasswordField.setDisabled(!checked);
						},
						scope: this
					},
				}
			));
		})
	});
});
