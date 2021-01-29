Ext.onReady(function() {

	Ext.override(go.modules.community.notes.NoteDetail, {
		onLoad: go.modules.community.notes.NoteDetail.prototype.onLoad.createSequence(function() {

			//reset decrypted data cache. Password must be re-entered when another note loads.
			if(!go.modules.community.notes.decrypted || !go.modules.community.notes.decrypted[this.data.id]) {
				go.modules.community.notes.decrypted = {};
			}

			if (!this.data.content || !go.modules.community.notes.isEncrypted(this.data.content)) {
				return;
			}



			this.originalData = this.data.content;
			this.data.content = t("Encrypted data");
			var item = this.items.item(0);
			item.update(this.data);
			item.onLoad(this);

			if(!crypto.subtle) {
				GO.errorDialog.show(t("Cryptographic functions are not available. Please run Group-Office with SSL."));
				return;
			}

			var me = this;
			var contentStripped = go.modules.community.notes.stripTag(this.originalData);

			if(go.modules.community.notes.decrypted[me.data.id]) {
				me.data.content = go.modules.community.notes.decrypted[me.data.id].content;
				var item = me.items.item(0);
				item.update(me.data);
				item.onLoad(me);
			} else {
				var dlg = new GO.dialog.PasswordDialog({
					title: t("Enter password to decrypt"),
					scope: this,
					handler: function (dlg, btn, password) {
						if (btn == "ok") {
							go.modules.community.notes.aesGcmDecrypt(contentStripped, password).then(function (plaintext) {
								me.data.content = plaintext;
								var item = me.items.item(0);
								item.update(me.data);
								item.onLoad(me);

								//remind decrypted data for edit dialog and reload.
								go.modules.community.notes.decrypted[me.data.id] = {
									content: plaintext,
									password: password
								};

							}).catch(function (error) {
								Ext.Msg.alert(t("Error", "Password"), t("Wrong password"));
							});
						}
					}

				});
				dlg.show();
			}
		})

	})
});