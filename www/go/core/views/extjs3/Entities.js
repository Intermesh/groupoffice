/* global go, Ext */

(function () {
  
  var entities = {};
  var stores = {};
  
  go.Entities = {
		
		/**
		 * Populate some entity properties with server info.
		 * 
		 * Called in mainlayout after authentication and loading of custom fields and modules.
		 */
		init : function() {
			go.Entities.getAll().forEach(function(e) {			
				var module = go.Modules.get(e.package, e.module),			
					serverInfo = module.entities.find(function(serverInfo) {
						return serverInfo.name == e.name;
					});

				if(serverInfo) {
					if(!e.customFields) {
						e.customFields = serverInfo.supportsCustomFields;
					}				
					
					e.isAclOwner = serverInfo.isAclOwner;
					e.defaultAclId = serverInfo.defaultAclId;	
				}
				
				if(e.customFields) {
					e.filters = e.filters.concat(go.customfields.CustomFields.getFilters(e.name));
				}
				e.filters =  go.util.Filters.normalize(e.filters);
				console.log(e);
			});
		},

    /**
     * Register an entity
     * 
     * this will create a global entity and store:
     * 
     * go.Stores.get("name")]
     * go.Entities.get(name)
     * 
		 * @param {object} cfg
		 * 
     * @returns {undefined}
     */
    register: function (cfg) {	
			if(!cfg.name) {
				throw "Invalid entity registered. 'name' property is required.";
			}
			
			var lcName = cfg.name.toLowerCase();
			
      if(entities[lcName]) {
        throw "Entity name is already registered by module " +entities[lcName]['package'] + "/" + entities[lcName]['module'];
      }
		      
      entities[lcName] = new go.Entity(cfg);
			
			//these datatypes will be prefetched by go.data.EntityStoreProxy.fetchEntities()
			// Key can also be a function that is called with the record data.
			go.data.types[cfg.name] = {
				entity: cfg,
				sortType: Ext.data.SortTypes.none,
				type: "entity",
				convert: function (v, data) {
					var key = this.type.getKey.call(this, data), entity = this.type.entity;
					
					if(!key) {
						return null;
					}
					

					if(Ext.isArray(key)) {
						var e = [];
						key.forEach(function(k) {
							if(go.Stores.get(entity.name).data[k]) {
								e.push(go.Stores.get(entity.name).data[k]);
							} else
							{
								console.error("Key " + k + " not found in store " + entity.name);
							}
						});
						
						return e;
					} else
					{
						return go.Stores.get(entity.name).data[key];	
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
				}
								
			};
			
			if(!Ext.data.Types[cfg.name.toUpperCase()]) {
				Ext.data.Types[cfg.name.toUpperCase()] = go.data.types[cfg.name];
			}
    },

		/**
		 * Get entity object
		 * 
		 * An entiy has these properties:
		 * 
		 * name: "Contact"
		 * module: "addressbook",
		 * package: "community",
		 * customfields: true | {customFieldSetDialog: "class"}
		 * files: true
		 * isAclOwner: true
		 * defaultsPanel: "class"
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
		 * This function will check module availability for the current user.
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
    },
		
		
		/**
		 * Get link configurations as degined in Module.js with go.Modules.register();
		 * 
		 * @returns {Array}
		 */
		getLinkConfigs : function() {
			var linkConfigs = [];	


			go.Entities.getAll().forEach(function (m) {					
				linkConfigs = linkConfigs.concat(m.links);			
			});	

			linkConfigs.sort(function (a, b) {
				return a.title.localeCompare(b.title);
			});

			return linkConfigs;
		},
		
		getLinkIcon : function(entity, filter) {
			var linkConfig = this.getLinkConfigs().find(function(cfg) {

				if(entity != cfg.entity) {
					return false;
				}

				if(filter != cfg.filter) {
					return false;
				}

				return true;
			});

			return linkConfig ? linkConfig.iconCls : "";

			
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
