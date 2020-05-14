go.modules.community.notes.Decrypter = {
	decrypt: function (data) {

		if (data.substring(0, 8) !== "{GOCRYPT") {
			return Promise.resolve(data);
		}

		var me = this;

		return new Promise(function(resolve, reject) {

			var dlg = new GO.dialog.PasswordDialog({
				title: t("Enter password to decrypt"),
				scope: this,
				handler: function (dlg, btn, password) {
					if (btn == "ok") {
						me.doDecrypt(data, password)
							.then(function (text) {
								resolve([text,password]);

							})
							.catch(function () {
								Ext.MessageBox.alert(t("Error"), t("Invalid password"));
								reject();
							})
					} else {
						go.modules.community.notes.lastDecryptedValue = "";
					}
				}
			});
			dlg.show();
		});
	},

	doDecrypt : function(data, password) {
		if(data.substring(0, 9) === "{GOCRYPT}") {

			var msg = window.atob(data.substring(9));

			var iv = (msg.substring(0, 32));			 // extract iv
			var body = (msg.substring(32, msg.length - 32));	 //extract ciphertext
			var serialized = mcrypt.Decrypt(body, iv, password, "rijndael-256", "ctr");

			//result should be a serialized sting by PHP
			var match = serialized.match(/^s:[0-9]+:"([\s\S]*)"/);
			if (!match) {
				//data = "Encrypted text";
				return Promise.reject();
			}

			var decrypted = Ext.util.Format.nl2br(match[1]);
			return Promise.resolve(decrypted);
		} else
		{
			//new encryption
			//data = "Decrypting...";

			return go.Jmap.request({
				method: "Note/decrypt",
				params: {
					data: data,
					password: password
				}
			}). then(function(response) {
				return Ext.util.Format.nl2br(response.content);
			});
		}
	}
}