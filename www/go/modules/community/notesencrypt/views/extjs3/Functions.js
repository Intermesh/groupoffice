"use strict";

go.modules.community.notes.aesGcmDecrypt = function (ciphertext, password) {

	var pwUtf8 = new TextEncoder().encode(password);
	return crypto.subtle.digest('SHA-256', pwUtf8).then(function (pwHash) {
		var iv = ciphertext.slice(0, 24).match(/.{2}/g).map(function (byte) {
			return parseInt(byte, 16);
		});
		var alg = {
			name: 'AES-GCM',
			iv: new Uint8Array(iv)
		};
		return crypto.subtle.importKey('raw', pwHash, alg, false, ['decrypt']).then(function (key) {
			var ctStr = atob(ciphertext.slice(24));
			var ctUint8 = new Uint8Array(ctStr.match(/[\s\S]/g).map(function (ch) {
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
};

go.modules.community.notes.isEncrypted = function(data) {
	if(data.substring(0, 11) === "{ENCRYPTED}") {
		return true;
	}
	return false;
}

go.modules.community.notes.isUsingOldEncryption = function(data) {
	if(data.substring(0, 10) === "{GOCRYPT2}") {
		return true;
	}
	return false;
}

go.modules.community.notes.stripTag = function(data) {
	if(data.substring(0, 11) === "{ENCRYPTED}") {
		var re = new RegExp('{ENCRYPTED}', 'g');
		data = data.replace(re, "");
	}

	return data;
}

"use strict";

go.modules.community.notes.aesGcmEncrypt = function (plaintext, password) {

	if(!crypto.subtle) {
		GO.errorDialog.show(t("Cryptographic functions are not available. Please run Group-Office with SSL."));
		return false;
	}

	var ivHex = "";
	var ctBase64 = "";
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
					resolve(result);
				});
			});
		});
	});
};