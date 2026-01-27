import {Window,t} from "@intermesh/goui";
export const Encrypt = {
	lastPass: '',
	async prompt(text: string): Promise<string> {
		const pw = await Window.prompt({inputLabel:t("Password"), fieldType:'password', title: t("Enter password to decrypt")});
		if(pw) {
			Encrypt.lastPass = pw;
			try {
				return await Encrypt.aesGcmDecrypt(text, pw)
			} catch (error) {
				void Window.alert(t("Wrong password"), t("Cannot decrypt"));
				throw 'wrong password';
			}
		}
		return '';
	},

	isEncrypted(data: string) {
		return data.slice(0, 11) === "{ENCRYPTED}";
	},

	aesGcmDecrypt(ciphertext: string, password:string): Promise<string> {
		ciphertext = ciphertext.slice('{ENCRYPTED}'.length);
		var pwUtf8 = new TextEncoder().encode(password);
		return crypto.subtle.digest('SHA-256', pwUtf8).then(function (pwHash) {
			var iv = ciphertext.slice(0, 24).match(/.{2}/g)!.map(function (byte) {
				return parseInt(byte, 16);
			});
			var alg = {
				name: 'AES-GCM',
				iv: new Uint8Array(iv)
			};
			return crypto.subtle.importKey('raw', pwHash, alg, false, ['decrypt']).then(function (key) {
				var ctStr = atob(ciphertext.slice(24));
				var ctUint8 = new Uint8Array(ctStr.match(/[\s\S]/g)!.map(function (ch) {
					return ch.charCodeAt(0);
				}));
				return crypto.subtle.decrypt(alg, key, ctUint8).then(function (plainBuffer) {
					var plaintext = new TextDecoder().decode(plainBuffer);
					return new Promise(function (resolve, reject) {
						resolve(plaintext);
					});
				});
			});
		});
	},
	aesGcmEncrypt(plaintext:string, password: string): Promise<string>  {

		var pwUtf8 = new TextEncoder().encode(password);
		return crypto.subtle.digest('SHA-256', pwUtf8).then(function (pwHash) {
			var iv = crypto.getRandomValues(new Uint8Array(12));
			var alg = {
				name: 'AES-GCM',
				iv: iv
			};
			return crypto.subtle.importKey('raw', pwHash, alg, false, ['encrypt']).then(function (key) {
				var ptUint8 = new TextEncoder().encode(plaintext);
				return crypto.subtle.encrypt(alg, key, ptUint8).then(function (ctBuffer) {
					var ctArray = Array.from(new Uint8Array(ctBuffer));
					var ctStr = ctArray.map(function (byte) {
						return String.fromCharCode(byte);
					}).join('');
					var ctBase64 = btoa(ctStr);
					var ivHex = Array.from(iv).map(function (b) {
						return ('00' + b.toString(16)).slice(-2);
					}).join('');
					var result = ivHex + ctBase64;
					return new Promise(function (resolve, reject) {
						resolve("{ENCRYPTED}" +result);
					});
				});
			});
		});
	}
}