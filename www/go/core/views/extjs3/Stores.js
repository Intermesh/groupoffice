
go.Db = (function() {
	var stores = {};
	return {			
		/**
		 * Get EntityStore by entity name
		 * 
		 * @param {string} entityName eg. "Contact"
		 * @returns {Boolean|go.data.EntityStore}
		 */
		store: function (entityName) {
			
			lcname = entityName.toLowerCase();
			
			var entity = go.Entities.get(lcname);
			if(!entity) {
				console.debug("'" + lcname + "' is not a registered store. Registered entities: ", go.Entities.getAll());
				return false;
			}
		
			if(!stores[lcname]) {
				stores[lcname] = new go.data.EntityStore({
					entity: entity
				});
			}
			
			return stores[lcname];
		}
	};
})();