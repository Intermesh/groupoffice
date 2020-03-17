Ext.onReady(function() {

	Ext.override(go.modules.community.notes.NoteDetail, {
		onLoad: go.modules.community.notes.NoteDetail.prototype.onLoad.createSequence(function() {
			if (!this.data.content || !go.modules.community.notes.isEncrypted(this.data.content)) {
				return;
			}

			var me = this;
			var contentStripped = go.modules.community.notes.stripTag(this.data.content);
			var passwordPrompt = new go.PasswordPrompt({
				text: t('Provide your password to decrypt content'),
				title: t('Current password required'),
				listeners:{
					'ok': function(password){
						go.modules.community.notes.aesGcmDecrypt(contentStripped,password).then(function(plaintext) {
							me.data.content = plaintext;
							me.items.item(0).onLoad(me);
							go.modules.community.notes.lastDecryptedValue = plaintext;
							go.modules.community.notes.lastNoteBookId = me.data.noteBookId;
						}).catch(function(error) {
							Ext.Msg.alert(t("Error", "Password"), "Wrong password");
						});
					},
					'cancel': function () {
						go.modules.community.notes.lastDecryptedValue = me.data.content;
						go.modules.community.notes.lastNoteBookId = me.data.noteBookId;
					},
					scope:this
				}
			});
			passwordPrompt.show();
		})

	})
});