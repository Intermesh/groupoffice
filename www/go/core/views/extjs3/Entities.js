/* global go, Ext */

go.Entities = (function () {
  
  var entities = {};

  
  return {
		
		/**
		 * Populate some entity properties with server info.
		 * 
		 * Called in mainlayout after authentication and loading of custom fields and modules.
		 */
		init : function() {
			go.Entities.getAll().forEach(function(entity) {			
				var module = go.Modules.get(entity.package, entity.module),			
					serverInfo = module.entities[entity.name];

				if(serverInfo) {
					if(!entity.customFields) {
						entity.customFields = serverInfo.supportsCustomFields;
					}
					entity.supportsFiles = serverInfo.supportsFiles;
					
					entity.isAclOwner = serverInfo.isAclOwner;
					entity.defaultAcl = serverInfo.defaultAcl;	
				}
				
				if(entity.customFields) {
					var existingNames = entity.filters.column("name");
					var customFieldFilters = go.customfields.CustomFields.getFilters(entity.name);
					customFieldFilters = customFieldFilters.filter(function(f) {
						var exists = existingNames.indexOf(f.name) > -1;
						if(exists) {
							console.warn("Custom field name " + f.name+ " can't be filtered as the name conflicts with an existing filter for entity " + entity.name);
						}
						return !exists;

					});
					entity.filters = entity.filters.concat(customFieldFilters);
				}

				entity.filters =  go.util.Filters.normalize(entity.filters);		
				
				entity.relations = entity.relations || {};
				entity.relations.customFields = go.customfields.CustomFields.getRelations(entity.name);
			});
		},

    /**
     * Register an entity
     * 
     * this will create a global entity and store:
     * 
     * go.Db.store("name")]
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
		
		getAllInstalled : function() {
			return entities;
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
})();