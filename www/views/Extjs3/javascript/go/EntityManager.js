(function () {
  
  var entities = {};
  var stores = {};
  
  go.Entities = {

    /**
     * Register an entity
     * 
     * this will create a global entity and store:
     * 
     * go.Stores.get("name")]
     * go.Entities.get(name)
     * 
     * @param {string} name
     * @param {object} jmapMethods
     * @returns {undefined}
     */
    register: function (package, module, name) {
      if(entities[name]) {
        throw "Entity name is already registered by module " +entities[name]['package'] + "/" + entities[name]['name'];
      }
      
      entities[name.toLowerCase()] = {
        name: name,
        module: module,
        package: package,
				getRouterPath : function (id) {
					return this.name.toLowerCase() + "/" + id
				},
        goto: function (id) {
          go.Router.goto(this.getRouterPath(id));
        },
			
      };     
			
			//these datatypes will be prefetched by go.data.JmapProxy.fetchEntities()
			// Key can also be a function that is called with the record data.
			go.data.types[name] = {
				convert: function (v, data) {
					var key = this.type.getKey.call(this, data);
					
					if(!key) {
						return null;
					}
					
					var entities = go.Stores.get(name).get([key]);
					return entities ? entities[0] : null;	
				},
				
				getKey : function(data) {
					if(typeof(this.key) == "function") {
						return this.key.call(this, data);
					} else
					{
						if(!this.key) {
							throw "Key is undefined";
						}	

						return data[this.key];
					}
				},
				sortType: Ext.data.SortTypes.none,
				type: "entity",
				entity: name
			}
    },

		/**
		 * Get entity object
		 * 
		 * An entiy has these properties:
		 * 
		 * name: "Contact"
		 * module: "addressbook",
		 * package: "community"
		 * 
		 * Functions:
		 * 
		 * getRouterPath : "contact/1"
		 * goto: Navigates to the contact
		 * 
		 * @param {string} name
		 * @returns {entities|EntityManagerL#1.entities}
		 */
    get: function (name) {      
      return entities[name.toLowerCase()];      
    },
    
		/**
		 * Get all entity objects
		 * 
		 * @see get(); 
		 * @returns {Object[]}
		 */
    getAll: function() {
			var e = [];
      for(entity in entities) {
				if(go.Modules.isAvailable(entities[entity].package, entities[entity].module)) {
					e.push(entities[entity]);
				}
			}
			
			return e;
    }
  };
  
  
  go.Stores = {
		
		/**
		 * Get EntityStore by entity name
		 * 
		 * @param {string} entityName eg. "Contact"
		 * @returns {Boolean|EntityManagerL#1.stores|stores}
		 */
    get: function (entityName) {
      
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
  }

})();
