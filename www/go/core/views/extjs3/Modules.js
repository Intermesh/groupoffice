/**
 * Module class
 */
go.Modules = (function () {
	var Modules = Ext.extend(function() {
		this.registered = {};
	}, Ext.util.Observable, {

		registered: null,

		/**
		 *
		 * Register a module.
		 *
		 * @see www/go/modules/community/addressbook/views/extjs3/Module.js for an extended example.
		 *
		 *
		 * @param {string} package
		 * @param {string} name
		 * @param {object} config
		 * @returns {void}
		 */
		register: function (package, name, config) {	
			
			Ext.ns('go.modules.' + package + '.' + name);
			
			config = config || {};

			if (!this.registered[package]) {
				this.registered[package] = {};
			}

			this.registered[package][name] = config;

			if (!config.panelConfig) {
				config.panelConfig = {title: config.title, admin: config.admin};
			}

			if (!config.requiredPermissionLevel) {
				config.requiredPermissionLevel = go.permissionLevels.read;
			}

			if (config.mainPanel) {
				go.Router.add(new RegExp("^" + name + "$"), function () {					
					var pnl = GO.mainLayout.openModule(name);
					
					if(pnl.routeDefault) {
						pnl.routeDefault();
					}
				});
			}

			if (config.entities) {
				config.entities.forEach(function (entity) {
					
					if(Ext.isString(entity)) {
						entity = {name: entity};
					}
					
					entity.package = package;
					entity.module = name;
					
					go.Entities.register(entity);
				});
			}
		},

		/**
		 * Check if the current user has this module
		 * 
		 * @param {string} package
		 * @param {string} name
		 * @param {int} Required permission level
		 * @param {User} User entity
		 * @returns {boolean}
		 */
		isAvailable: function (package, name, permissionLevel, user) {
			
			if(!Ext.isDefined(permissionLevel)) {
				permissionLevel = go.permissionLevels.read;
			}

			if (!package) {
				package = "legacy";
			}

			if (!this.registered[package] || !this.registered[package][name]) {
				return false;
			}

			var module = this.get(package, name);
			if (!module) {
				return false;
			}

			//for the logged in user we can simply check permissionLevel
			if(!user) {
				return module.permissionLevel >= permissionLevel;
			}

			//if a user is given we must check the groups			
			for(groupId in module.acl) {
				if(module.acl[groupId] >= permissionLevel && user.groups.indexOf(parseInt(groupId)) != -1) {
					return true;
				}
			}

			return false;
		},

		/**
		 * Get module configuration object as passed with go.Modules.register()
		 * 
		 * @param {string} package
		 * @param {string} name
		 * @returns {Object}
		 */
		getConfig: function (package, name) {
			if (!package) {
				package = "legacy";
			}
			if (!this.registered[package] || !this.registered[package][name]) {
				return false;
			}

			return this.registered[package][name];
		},

		/**
		 * Get module entity
		 * 
		 * @param {string} package
		 * @param {string} name
		 * @returns {Module|Boolean}
		 */
		get: function (package, name) {

			if (!package) {
				package = "legacy";
			}
			if (!this.registered[package] || !this.registered[package][name]) {
				return false;
			}
			
			for (var id in this.entities) {
				if ((package === "legacy" || this.entities[id].package === package) && this.entities[id].name === name) {
					return this.entities[id];
				}
			}

			return false;
		},

		/**
		 * Get all entities including those the current user has no permission for.
		 * 
		 * @returns {Module[]}
		 */
		getAll: function () {
			return this.entities;
		},

		/**
		 * Get all available modules
		 * 
		 * @returns {Module[]}
		 */
		getAvailable: function () {
			var available = [],all = this.entities, id;

			for (id in all) {
				if (this.isAvailable(all[id].package, all[id].name)) {
					available.push(all[id]);
				}
			}

			return available;
		},
		
		

		//will be called after login
		init: function () {
			var me = this;
			
			return go.Db.store("Module").all().then(function(entities) {
				me.entities = entities;
				var promises = [];

				for (var id in me.entities) {
					
					var mod = me.entities[id];
					
					// for (name in me.registered[package]) {	
						var pkg = mod.package || "legacy";
						if(!me.registered[pkg]) {
							continue;
						}
						var config = me.registered[pkg][mod.name];
						if(!config){
							continue;
						}
					
						if (config.requiredPermissionLevel > mod.permissionLevel) {
							continue;
						}
						
						if (config.initModule){
							go.Translate.setModule(mod.package, mod.name);

							var initModulePromise = config.initModule.call(me);
							if(initModulePromise) {
								promises.push(initModulePromise);
							}
						}

						if (config.mainPanel) {
							if(Ext.isArray(config.mainPanel)) {
								for(var i = 0; i < config.mainPanel.length; i++) {
								
									//todo panel is only constructed to grab config.title/id
									var m = new config.mainPanel[i]();
									//todo GO.moduleManager is deprecated									
									GO.moduleManager._addModule(config.mainPanel[i].prototype.id, config.mainPanel[i], {title:m.title, package: mod.package}, config.subMenuConfig);
								}
							} else {
								config.panelConfig.package = mod.package;

								GO.moduleManager._addModule(mod.name, config.mainPanel, config.panelConfig, config.subMenuConfig);
							}
						}							
					// }
				}

				return Promise.all(promises).then(function() {
					return me.entities;
				});
			});
		},
		
		addPanel : function(panels) {
			if(!Ext.isArray(panels)) {
				panels = [panels];
			}
			
			panels.forEach(function(p) {
				if(!p.prototype.id) {
					throw "Module panel must have an 'id'";
				}								
				GO.mainLayout.addModulePanel(p.prototype.id, p);
			}, this);

		},

		hasPermission: function(level) {
			// @see line 241 this info should not be in Translate as it is usefull for other components as well
			// todo create go.currentModule
			var module = this.get(go.Translate.package, go.Translate.module);
			if (!module) {
				return false;
			}
			return module.permissionLevel >= level;
		}
	});

	return new Modules();

})();
