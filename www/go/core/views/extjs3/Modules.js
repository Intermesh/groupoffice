/**
 * Module class
 */
go.Modules = (function () {
	var Modules = Ext.extend(function() {
		this.registered = {};
	}, Ext.util.Observable, {



		/**
		 *
		 * Register a module.
		 *
		 * @see www/go/modules/community/addressbook/views/extjs3/Module.js for an extended example.
		 * @see FileBrowser.js for an example on how to add panels conditionally to the main tab panel
		 *
		 * @param {string} package
		 * @param {string} name
		 * @param {object} config
		 * @returns {void}
		 */
		register: function (package, name, config ) {
			Ext.ns('go.modules.' + package + '.' + name);

			config = config || {};

			if (!this.registered[package]) {
				this.registered[package] = {};
			}

			config.package = package;
			config.name = name;

			this.registered[package][name] = config;

			// for onmoduleready event
			GO.moduleManager.onAddModule(name);

			if(config.entities) {
				config.entities.forEach(function(e) {
					if(e.filters) {
						const filterMap = {};
						e.filters.forEach(function(f) {
							filterMap[f.name] = f;
						})

						e.filters = filterMap;
					}

				});
			}

			window.groupofficeCore.modules.register(config);
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

		/**
		 * Check if the current user has this module
		 * 
		 * @param {string} package
		 * @param {string} name
		 * @param {int} @deprecated Required permission level Use: go.Modules.get("studio","animals").userRights.mayManage
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

			const module = window.groupofficeCore.modules.get(package, name);

			if(!module) {
				return false;
			}


			//for the logged in user we can simply check permissionLevel
			if(!user || user.id == go.User.id) {
				return module.permissionLevel >= permissionLevel;
			}

			//if a user is given we must check the groups			
			for(let groupId in module.permissions) {
				const p = module.permissions[groupId];
				let allow;
				if(permissionLevel > go.permissionLevels.read) {
					allow = p.right.mayManage;
				} else
				{
					allow = true;
				}
				if(allow && user.groups.indexOf(groupId) != -1) {
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
				return window.groupofficeCore.modules.getConfig(package, name);
			}

			return this.registered[package][name]
		},

		// getConfigs: function() {
		// 	return this.registered;
		// },

		/**
		 * Check if a module is installed
		 *
		 * @param package
		 * @param name
		 * @return {boolean}
		 */
		isInstalled: function (package, name) {
			return this.getConfig(package, name) !== false;
		},

		/**
		 * Get module entity
		 * 
		 * @param {string} package
		 * @param {string} name
		 * @returns {Module|Boolean}
		 */
		get: function (package, name) {

			return window.groupofficeCore.modules.get(package, name);
		},

		/**
		 * Get all available modules
		 * 
		 * @returns {Module[]}
		 */
		getAvailable: function () {
			return window.groupofficeCore.modules.getAvailable();
		}
	});

	return new Modules();

})();
