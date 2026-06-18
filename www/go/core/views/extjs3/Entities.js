/* global go, Ext */

go.Entities = (function () {

	/**
	 *
	 * @type {go.Entity}
	 */
	var entities = {};


	return {



		/**
		 * Get entity object
		 *
		 * An entity has these properties:
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
		 * @returns {go.Entity}
		 */
		get: function (name) {
			return window.groupofficeCore.entities.get(name);
		},

		/**
		 * Get all entity objects
		 *
		 * This function will check module availability for the current user.
		 *
		 * @see get();
		 * @returns {Object[]}
		 */
		getAll: function () {
			return window.groupofficeCore.entities.getAvailable();
		},

		getAllInstalled: function () {
			return window.groupofficeCore.entities.getAll();
		},

		/**
		 * Get link configurations as defined in Module.js with go.Modules.register();
		 *
		 * @returns {Array}
		 */
		getLinkConfigs: function () {
			return window.groupofficeCore.entities.getLinkConfigs();
		},

		getLinkIcon: function (entity, filter) {
			var linkConfig = this.getLinkConfigs().find(function (cfg) {

				if (entity != cfg.entity) {
					return false;
				}

				if (filter != cfg.filter) {
					return false;
				}

				return true;
			});

			return linkConfig ? linkConfig.iconCls : "";
		}
	};
})();