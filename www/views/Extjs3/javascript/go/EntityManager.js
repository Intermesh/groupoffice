go.EntityManager = {
	
	/**
	 * Register an entity
	 * 
	 * this will create a global entity and store:
	 * 
	 * go.stores[name]
	 * go.entities[name]
	 * 
	 * @param {string} name
	 * @param {object} jmapMethods
	 * @returns {undefined}
	 */
	register: function(module, name) {
		if(!go.entities) {
			go.entities = {};
		}	
		
		go.entities[name] = {
			name: name,
			module: module,
			goto : function(id) {
				go.Router.goto(this.module + "/" + this.name.toLowerCase() + "/" + id);
			}
		};		
		
		if(!go.stores) {
			go.stores = {};
		}
		
		go.stores[name] = new go.data.EntityStore({		
			entity: go.entities[name]
		});
	}	
};
