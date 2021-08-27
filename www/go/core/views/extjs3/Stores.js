
go.Db = (function() {

	const stores = {};

	return {			
		/**
		 * Get EntityStore by entity name
		 * 
		 * @param {string} entityName eg. "Contact"
		 * @returns {Boolean|go.data.EntityStore}
		 */
		store: function (entityName) {

			const lcname = entityName.toLowerCase();
			
			const entity = go.Entities.get(lcname);
			if(!entity) {
				console.debug("'" + entityName + "' is not a registered store. Registered entities: ", go.Entities.getAll().column('name'));
				return false;
			}
		
			if(!stores[lcname]) {
				stores[lcname] = new go.data.EntityStore({
					entity: entity
				});
			}
			
			return stores[lcname];
		},


		/**
		 * Get all entity stores.
		 *
		 * @returns {Object[]}
		 */
		stores : function() {
			const all = [];

			go.Entities.getAll().forEach(function(e) {
				all.push(go.Db.store(e.name));
			});

			return all;
		}
	};
})();