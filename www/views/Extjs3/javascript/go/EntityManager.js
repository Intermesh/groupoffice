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
		 * @param {string|object} config
		 * 
		 * You can pass just a string as config for the name of the entity. 
		 * If you need linking then you can pass a config object:
		 * 
		 * 
		 * @example
		 * 
		 *		 /**
		 *			 * Entity name
		 *			 *
		 *			name: "Note",
		 *			
		 *			/**
		 *			 * Opens a dialog to create a new linked item
		 *			 * 
		 *			 * @param {string} entity eg. "Note"
		 *			 * @param {string|int} entityId
		 *			 * @returns {go.form.Dialog}
		 *			 *
		 *			linkWindow: function(entity, entityId) {
		 *				return new go.modules.community.notes.NoteForm();
		 *			},
		 *			
		 *			/**
		 *			 * Return component for the detail view
		 *			 * 
		 *			 * @returns {go.panels.DetailView}
		 *			 *
		 *			linkDetail: function() {
		 *				return new go.modules.community.notes.NoteDetail();
		 *			}	
		 * 
     * @returns {undefined}
     */
    register: function (package, module, config) {
			
			if(!Ext.isObject(config)) {
				config = {
					name: config,
					linkable: false
				}
			}
			
			if(!config.name) {
				throw "Invalid entity registered. 'name' property is required."
			}
			
      if(entities[config.name]) {
        throw "Entity name is already registered by module " +entities[name]['package'] + "/" + entities[name]['name'];
      }
			
			if(!config.linkable && config.linkWindow) {
				config.linkable = true;
			}
      
      entities[config.name.toLowerCase()] = Ext.applyIf(config, {        
        module: module,
        package: package,
				title: t(config.name),
				getRouterPath : function (id) {
					return this.name.toLowerCase() + "/" + id;
				},
        goto: function (id) {
          go.Router.goto(this.getRouterPath(id));
        }			
      });
			
			
			
			//these datatypes will be prefetched by go.data.JmapProxy.fetchEntities()
			// Key can also be a function that is called with the record data.
			go.data.types[config.name] = {
				convert: function (v, data) {
					var key = this.type.getKey.call(this, data);
					
					if(!key) {
						return null;
					}
					
					var entities = go.Stores.get(config.name).get([key]);
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
				entity: config.name
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
  };

})();
