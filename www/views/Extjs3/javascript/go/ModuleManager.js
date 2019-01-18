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
		 * 	  links: [{
		 *			/**
		 *			 * Entity name
		 *			 *
		 *			entity: "Contact",
		 *			
		 *			/**
		 *			 * For advanced usage only when search results need to be filtered.
		 *			 *
		 *			filter: "isContact",
		 *			
		 *			/**
		 *			 * Opens a dialog to create a new linked item
		 *			 * 
		 *			 * @param {string} entity eg. "Note"
		 *			 * @param {string|int} entityId
		 *			 * @returns {go.form.Dialog}
		 *			 *
		 *			linkWindow: function(entity, entityId) {
		 *				return new go.modules.community.addressbook.ContactDialog();
		 *			},
		 *			
		 *			/**
		 *			 * Return component for the detail view
		 *			 * 
		 *			 * @returns {go.panels.DetailView}
		 *			 *
		 *			linkDetail: function() {
		 *				return new go.modules.community.addressbook.ContactDetail();
		 *			}	
		 *		}],
		 * 	  userSettingsPanels: ["GO.addressbook.SettingsPanel"],
		 * 	  systemSettingsPanels: ["go.modules.commmunity.addressbook.SystemSettingsPanel"],
		 * 	  initModule: function () {	
		 * 	}
		 * });
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
		 * Check if the current user has this module
		 * 
		 * @param {string} package
		 * @param {string} name
		 * @param {int} Required permission level
		 * 
		 * @returns {boolean}
		 */
		isAvailable: function (package, name, permissionLevel) {
			
			if(!Ext.isDefined(permissionLevel)) {
				permissionLevel = GO.permissionLevels.read;
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
			return module.permissionLevel >= permissionLevel;
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
			var package, name, me = this;
			
			return new Promise(function(resolve, reject){
			
				go.Stores.get("Module").all(function (entities) {

					this.entities = entities;

					for (package in me.registered) {
						for (name in me.registered[package]) {							
							var config = me.registered[package][name];
						
							if (!me.isAvailable(package, name, config.requiredPermissionLevel)) {
								continue;
							}

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

					resolve(me);
				}, me);
			
			});
		}
	});

	go.Modules = new Modules;

})();
