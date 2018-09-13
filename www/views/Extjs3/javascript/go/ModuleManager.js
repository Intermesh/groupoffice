/* global GO, go, Ext */

(function () {
	var Modules = Ext.extend(Ext.util.Observable, {

		registered: {},

		/**
		 * 
		 * @example
		 * 
		 * go.Modules.register("community", 'addressbook', {
		 * 	  mainPanel: GO.addressbook.MainPanel,
		 * 	  title: t("Address book", "addressbook"),
		 * 	  iconCls: 'go-tab-icon-addressbook',
		 * 	  entities: ["Contact", "Company"],
		 * 	  userSettingsPanels: ["GO.addressbook.SettingsPanel"],
		 * 	  systemSettingsPanels: ["go.modules.commmunity.addressbook.SystemSettingsPanel"],
		 * 	  initModule: function () {	
		 * 	}
		 * });
		 * 
		 * @param {type} package
		 * @param {type} name
		 * @param {type} config
		 * @returns {undefined}
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
				config.requiredPermissionLevel = GO.permissionLevels.read;
			}

			if (config.mainPanel) {
				go.Router.add(new RegExp(name + "$"), function () {
					GO.mainLayout.openModule(name);
				});
			}

			if (config.entities) {
				config.entities.forEach(function (e) {
					go.Entities.register(package, name, e);
				});
			}
		},

		/**
		 * Check if the current user has thie module
		 * 
		 * @param {string} package
		 * @param {string} name
		 * @returns {boolean}
		 */
		isAvailable: function (package, name) {

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
			return module.permissionLevel >= this.registered[package][name].requiredPermissionLevel;
		},

		getConfig: function (package, name) {
			if (!package) {
				package = "legacy";
			}
			if (!this.registered[package] || !this.registered[package][name]) {
				return false;
			}

			return this.registered[package][name];
		},

		get: function (package, name) {

			if (!package) {
				package = "legacy";
			}
			if (!this.registered[package] || !this.registered[package][name]) {
				return false;
			}

			var all = go.Stores.get("Module").data, id;

			for (id in all) {
				if ((package === "legacy" || all[id].package == package) && all[id].name === name) {
					return all[id];
				}
			}

			return false;
		},

		getAll: function () {
			return go.Stores.get("Module").data;
		},

		getAvailable: function () {
			var available = [],all = go.Stores.get("Module").data, id;

			for (id in all) {
				if (this.isAvailable(all[id].package, all[id].name)) {
					available.push(all[id]);
				}
			}

			return available;
		},

		//will be called after login
		init: function () {
			var package, name;
			
			go.Stores.get("Module").getUpdates(function () {

				for (package in this.registered) {
					for (name in this.registered[package]) {
						if (!this.isAvailable(package, name)) {
							continue;
						}

						var config = this.registered[package][name];

						if (config.mainPanel) {
							//todo GO.moduleManager is deprecated
							GO.moduleManager._addModule(name, config.mainPanel, config.panelConfig, config.subMenuConfig);
						}

						if (config.initModule)
						{
							go.Translate.setModule(package, name);
							config.initModule();
						}

					}
				}


				go.Modules.fireReady();
			}, this);
		},

		isReady: false,

		fireReady: function () {
			this.isReady = true;
			this.fireEvent('internalready', this);
		},
		/**
		 * Use this to do stuff after the custom fields data has been loaded
		 * 
		 * @param {type} fn
		 * @param {type} scope
		 * @returns {undefined}
		 */
		onReady: function (fn, scope) {
			if (!this.isReady) {
				this.on('internalready', fn, scope || this);
			} else {
				fn.call(scope || this, this);
			}
		}

//		/**
//		 * Call function when module becomes available.
//		 * 
//		 * @param {string} module
//		 * @param {function} fn
//		 * @param {object} scope		 
//		 */
//		onAvailable: function(package, module, fn, scope) {
//			this.onReady(function() {
//				if(this.isAvailable(module)) {
//					fn.call(scope);
//				}
//			}, this);
//		}

//		onModuleReady: function(module, fn, scope) {
//			if(!this.isReady) {
//				this.on('internalready', function(){
//					this.onModuleReady(module, fn, scope);
//				}, scope);
//			} else
//			{				
//				if(this.isAvailable(module)) {
//					fn.call(scope || this, this);
//				}
//			}
//		}
	});

	go.Modules = new Modules;

})();
