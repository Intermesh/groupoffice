/* global go, Ext */

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
     * @param {string} package
     * @param {string} module
		 * @param {string} name
		 * 
     * @returns {undefined}
     */
    register: function (package, module, name) {	
						
			if(!name) {
				throw "Invalid entity registered. 'name' property is required.";
			}
			
      if(entities[name]) {
        throw "Entity name is already registered by module " +entities[name]['package'] + "/" + entities[name]['name'];
      }
      
      entities[name.toLowerCase()] = {     
				name: name,
        module: module,
        package: package,
				title: t(name),
				getRouterPath : function (id) {
					return this.name.toLowerCase() + "/" + id;
				},
        goto: function (id) {
          go.Router.goto(this.getRouterPath(id));
        }			
      };
			
			//these datatypes will be prefetched by go.data.EntityStoreProxy.fetchEntities()
			// Key can also be a function that is called with the record data.
			go.data.types[name] = {
				convert: function (v, data) {
					var key = this.type.getKey.call(this, data);
					
					if(!key) {
						return null;
					}
					

					if(Ext.isArray(key)) {
						var e = [];
						key.forEach(function(k) {
							if(go.Stores.get(name).data[k]) {
								e.push(go.Stores.get(name).data[k]);
							} else
							{
								console.error("Key " + k + " not found in store " + name);
							}
						});
						
						return e;
					} else
					{
						return go.Stores.get(name).data[key];	
					}

				},
				
				getKey : function(data) {
					if(typeof(this.key) === "function") {
						return this.key.call(this, data);
					} else
					{
						if(!this.key) {
							throw "Key is undefined";
						}	
						
						var parts = this.key.split(".");
						
						parts.forEach(function(p) {
							if(Ext.isArray(data)) {
								var arr = [];
								data.forEach(function(i) {
									arr.push(i[p]);
								});
								data = arr;
							} else
							{
								data = data[p];
							}
							if(!data) {
								return false;
							}
						});
						
						return data;
						
					}
				},
				sortType: Ext.data.SortTypes.none,
				type: "entity",
				entity: name.name
			};
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
			var e = [], entity;
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
  };

})();
