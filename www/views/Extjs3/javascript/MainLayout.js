
/* global GO, Ext, go */

/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: MainLayout.js 22429 2018-02-27 16:02:26Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */


/**
 * 
 * 
 * 
 * @returns {undefined}
 * 
 */
GO.MainLayout = function () {

	this.addEvents({
		/**
		 * Deprecated. Use boot instead
		 */
		'ready': true,
		
		/**
		 * Fires when main tab panel is rendered
		 */
		'render': true,
		'linksDeleted': true,
		'focus': true,
		'blur': true,
		
		/**
		 * Fires on login
		 */
		'login': true,
		/**
		 * Fires lastly when either the tab panel is rendered or the login screen is shown
		 */
		'boot': true,
		
		/**
		 * Fires when user is authenticated. Can be before boot too when user was already logged in when reloading.
		 */
		'authenticated' : true
	});

	this.resumeEvents();
};

Ext.extend(GO.MainLayout, Ext.util.Observable, {

	ready: false,
	state: false,
	stateSaveScheduled: false,
	rendered: false,

	/**
	 * 
	 * @deprecated
	 */
	onReady: function (fn, scope) {
		if (!this.ready) {
			this.on('ready', fn, scope);
		} else {
			fn.call(scope, this);
		}
	},


	saveState: function () {
		Ext.state.Manager.getProvider().set('open-modules', this.getOpenModules());
	},

	fireReady: function () {
		this.fireEvent('ready', this);
		this.ready = true;
	},

	getOpenModules: function () {
		var openModules = [];
		this.tabPanel.items.each(function (p) {
			var tabEl = this.tabPanel.getTabEl(p);

			if (tabEl.style.display !== 'none') {
				openModules.push(p.moduleName);
			}
		}, this);

		return openModules;

	},

	getModulePanel: function (moduleName, unhide = true) {
		const wrapper = groupofficeCore.main.getPanelById(moduleName);
		if(!wrapper) {
			return false;
		}
		return wrapper.extJSComp;
	},


	
	/**
	 * Add module panel after rendering of main layout
	 *
	 * @example
	 * ```
	 * initModule: function () {
	 *
	 * 		setTimeout(function() {
	 *
	 * 			var test = Ext.extend(Ext.Panel, {
	 * 				title: "Test",
	 * 				html: "Dit is een test"
	 * 			})
	 *
	 * 			GO.mainLayout.addModulePanel("test", test);
	 *
	 * 		}, 2000);
	 *
	 * 	}
	 * 	```
	 *
	 * @param {type} moduleName
	 * @param {type} panelClass
	 * @param {type} panelConfig
	 * @returns
	 */
	addModulePanel : function(moduleName, panelClass, panelConfig) {

		panelConfig =panelConfig || {}
		panelConfig.package = panelClass.prototype.package ?? "legacy";

		// GO.moduleManager._addModule(moduleName, panelClass, panelConfig);

		window.groupofficeCore.main.addLegacyPanel(panelConfig.package, moduleName, panelClass.prototype.title, panelClass,panelConfig);

		// go.Router.add(new RegExp('^(' + moduleName + ")$"), function (name) {
		// 	var pnl = GO.mainLayout.openModule(name);
		// 	if(pnl.routeDefault) {
		// 		pnl.routeDefault();
		// 	}
		// });
	},




	setNotification: function (moduleName, number, color) {

		window.groupofficeCore.main.setPanelBadge(moduleName, number);
		// var panel = this.getModulePanel(moduleName, false);
		// if (panel) {
		//
		// 	if (!panel.origTitle) {
		// 		panel.origTitle = panel.title;
		// 	}
		//
		// 	var newTitle = number ? panel.origTitle + ' <div class="go-tab-notification" style="background-color:' + color + '">' + number + '</div>' : panel.origTitle;
		//
		// 	panel.notification = number;
		//
		// 	panel.setTitle(newTitle);
		// }

	},

	panelIsVisible : function(panelId) {
		const activeItemComponent = window.groupofficeCore.main.container.activeItemComponent;
		return activeItemComponent && activeItemComponent.itemId === panelId;
	},

	openModule: function (moduleName) {
		const pnl = groupofficeCore.main.openPanel(moduleName);
		return pnl?.extJSComp;
	},



});

GO.mainLayout = new GO.MainLayout();

// needed in pre v6.4
GO.mainLayout.on('callto', GO.util.callToHandler);

//
// window.addEventListener('beforeinstallprompt', (e) => {
//
// 	// Prevent Chrome 67 and earlier from automatically showing the prompt
// 	e.preventDefault();
// 	// Stash the event so it can be triggered later.
// 	GO.mainLayout.deferredInstallPrompt = e;
// 	// Update UI to notify the user they can add to home screen
// 	GO.mainLayout.installBtn.show();
//
// });
