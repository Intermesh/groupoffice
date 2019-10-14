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
	}
	
};
