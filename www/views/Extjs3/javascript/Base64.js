//deprecated, use atob and btoa

GO.util.Base64 = {
 
	// public method for encoding
	encode : function (input) {
		return btoa(input);
	},
 
	// public method for decoding
	decode : function (input) {
		return atob(input);
 
	}
 
}
