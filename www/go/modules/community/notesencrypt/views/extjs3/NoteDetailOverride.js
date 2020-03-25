Ext.onReady(function() {

	Ext.override(go.modules.community.notes.NoteDetail, {
		onLoad: go.modules.community.notes.NoteDetail.prototype.onLoad.createSequence(function() {
			if (!this.data.content || !go.modules.community.notes.isEncrypted(this.data.content)) {
				return;
			}
			this.originalData = this.data.content;
			this.data.content = t("Encrypted data");
			var item = this.items.item(0);
			item.update(this.data);
			item.onLoad(this);

			var me = this;
			var contentStripped = go.modules.community.notes.stripTag(this.originalData);



			var dlg = new GO.dialog.PasswordDialog({
				title: t("Enter password to decrypt"),
				scope: this,
				handler: function (dlg, btn, password) {
					if (btn == "ok") {
						go.modules.community.notes.aesGcmDecrypt(contentStripped,password).then(function(plaintext) {
							me.data.content = plaintext;
							var item = me.items.item(0);
							item.update(me.data);
							item.onLoad(me);
							go.modules.community.notes.password = password;
							go.modules.community.notes.lastDecryptedValue = plaintext;
							go.modules.community.notes.lastNoteBookId = me.data.noteBookId;
						}).catch(function(error) {
							Ext.Msg.alert(t("Error", "Password"), t("Wrong password"));
						});
					} else {
						go.modules.community.notes.lastDecryptedValue = "";
						go.modules.community.notes.lastNoteBookId = me.data.noteBookId;
					}
				}

			});
			dlg.show();
		})

	})
});