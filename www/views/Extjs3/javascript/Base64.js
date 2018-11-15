GO.util.Base64 = {
 
	// public method for encoding
	encode : function (str) {
		return window.btoa(unescape(encodeURIComponent(str)));
	},
 
	// public method for decoding
	decode : function (str) {
		return decodeURIComponent(escape(window.atob(str)));
		//return atob(input);
	}
}
