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
        goto: function (id) {
          go.Router.goto(this.name.toLowerCase() + "/" + id);
        },
			
      };     
			
			
			go.data.types[name] = {
				convert: function (v, data) {
					if(!data[this.key]) {
						return "-";
					}
					
					var entities = go.Stores.get(name).get([data[this.key]]);					
					return entities ? entities[0] : '-';	
				},
				sortType: Ext.data.SortTypes.none,
				type: "entity",
				entity: name
			}
    },
	 
	 isset: function(name) {
			name = name.toLowerCase();
		
			if(entities[name]) {
				return true;
			}
			return false;
	 },

    get: function (name) {      
		
		name = name.toLowerCase();
		
		if(!entities[name]) {
			return false; //throw "Entity " + name + " does not exist";
		}
		
      return entities[name];      
    },
    
    getAll() {
      return entities;
    }
  };
  
  
  go.Stores = {
    get: function (name) {
      
      name = name.toLowerCase();
			
			var entity = go.Entities.get(name);
			if(!entity) {
				return false;
			}
     
      if(!stores[name]) {
        stores[name] = new go.data.EntityStore({
          entity: entity
        });
      }
      
      return stores[name];
    }
  }

})();
