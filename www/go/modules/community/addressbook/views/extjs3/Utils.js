go.modules.community.addressbook.Utils = {
	
	findPreferredType : function(items, types, returnAny) {
		var result;
		for(var i = 0, l = types.length; i < l; i++) {
			result = items.find(function(item){
				return item.type === types[i];
			});			
			
			if(result) {
				return result;
			}
		};
		
		if(returnAny && items.length) {
			return items[0];
		}
		
		return false;		
	},


	transformUrl: function(url, type) {

		var isUri = url.toLowerCase().indexOf('http') !== -1;
		if(isUri) {
			return url;
		}

		switch(type) {
			case "twitter":
				return "https://twitter.com/" + url;

			case "facebook":
				return "https://www.facebook.com/" + url;

			case "linkedin":
				return "https://linkedin.com/in/" + url;

			default:
				return 'http://' + url;
		}
	}
	
};
