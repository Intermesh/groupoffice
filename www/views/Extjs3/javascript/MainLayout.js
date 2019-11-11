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
	
	/**
	 * Always called after all scripts are loaded in Ext.onReady();
	 * @returns {undefined}
	 */
	boot : function() {

		Ext.QuickTips.init();
		Ext.apply(Ext.QuickTips.getQuickTip(), {
			dismissDelay: 0,
			maxWidth: 500
		});
		var me = this;
		Ext.Ajax.defaultHeaders = {'Accept-Language': GO.lang.iso};

		if(go.User.accessToken){
			Ext.Ajax.defaultHeaders.Authorization = 'Bearer ' + go.User.accessToken;
			go.User.authenticate(function(data, options, success, response){
				
				if(success) {
					me.on('render', function() {
						me.fireEvent('boot', me);
					}, me, {single:true});
					me.onAuthentication(); // <- start Group-Office
				} else {
					go.User.clearAccessToken();
					
					me.fireEvent("boot", this);
					go.Router.check();
				}
			});
		} else {
			this.fireEvent("boot", this); // In the router there is an event attached.
			go.Router.check();
		}
	},

	saveState: function () {
		Ext.state.Manager.getProvider().set('open-modules', this.getOpenModules());
	},

	fireReady: function () {
		this.fireEvent('ready', this);
		this.ready = true;
//		this.initLogoutTimer();
//		GO.playAlarm('desktop-login');
	},

//	/**
//	 * Set a timer that will automatically logout when no mouseclicks or keypresses
//	 * @param start set the false to stop the logout timer
//	 * @see fireReady
//	 */
//	initLogoutTimer: function (start) {
//		//Does work in IE since 3-jan-2014
//
//		if (!GO.util.empty(GO.settings.config['session_inactivity_timeout'])) {
//			var ms = GO.settings.config['session_inactivity_timeout'] * 1000;
//			var delay = (function () {
//				var timer = 0;
//				return function (ms) {
//					clearTimeout(timer);
//					if (ms > 0)
//						timer = setTimeout(function () {
//							window.location = GO.url('core/auth/logout');
//						}, ms);
//				};
//			})();
//			var keyevent = (Ext.isIE || Ext.isWebKit || Ext.isOpera) ? 'keydown' : 'keypress';
//			Ext.EventManager.on(document, keyevent, function () {
//				delay(ms);
//			});
//			Ext.EventManager.on(document, 'click', function () {
//				delay(ms);
//			});
//			this.timeout = delay;
//			this.timeout(ms);
//		} else {
//			//dummy
//			this.timeout = function (ms) {}
//		}
//	},

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

	createTabPanel: function (items) {

		this.tabPanel = new Ext.TabPanel({
			cls: "go-main-tab-panel",
			region: 'center',
			titlebar: false,
			enableTabScroll: true,
			border: false,
//			activeTab:'go-module-panel-'+GO.settings.start_module,
			tabPosition: 'top',
			items: items,
			deferedRender:true
		});

		this.tabPanel.setActiveTab(null);


		//blur active form fields on tab change. Otherwise auto complete combo boxes
		//will remain focussed but the autocomplete functionality fails.
		this.tabPanel.on('tabchange', function (tabpanel, newTab) {

			if (!newTab) {
				return;
			}

			//update hash if not already set.
			if (!go.Router.routing) {
				window.go.Router.setPath(newTab.moduleName);
			}

			if (document.activeElement && typeof document.activeElement.blur === 'function')
				if (document.activeElement.tagName === 'TEXTAREA' || document.activeElement.tagName === 'INPUT') {
					document.activeElement.blur();
				}
		}, this);

		this.tabPanel.on('contextmenu', function (tp, panel, e) {

			if (panel.closable) {
				return false;
			}

			var openModules = this.getOpenModules();

			//don't hide last tab
			if (openModules.length > 1) {

				tp.hideTabStripItem(panel);
				panel.hide();

				//var menuItem = this.startMenu.items.item('go-start-menu-'+panel.moduleName);
				//menuItem.show();

				if (panel == tp.activeTab) {
					var next = tp.stack.next();
					if (next) {
						tp.setActiveTab(next);
					} else if (tp.items.getCount() > 0) {
						tp.setActiveTab(0);
					} else {
						tp.activeTab = null;
					}
				}
				//this.refreshMenu();
				this.saveState();
			}
		}, this);
	},

	getModulePanel: function (moduleName) {
		var panelId = 'go-module-panel-' + moduleName;

		if (this.tabPanel.items.map[panelId])
		{
			return this.tabPanel.items.map[panelId];
		} else
		{
			return false;
		}
		
	},

	//overridable
	beforeRender: function () {

	},
	
	loadLegacyModuleScripts : function() {

		return new Promise(function(resolve, reject) {
				//legacy scripts loaded from scripts.inc.php
			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.setAttribute('src', BaseHref + "views/Extjs3/modulescripts.php?mtime=" + go.User.modifiedAt);
			script.charset = 'utf-8';
			script.id = 'testing';
			script.defer = true;
			script.async = true;
			script.onload = function () {
				resolve();
			};
			script.onerror = function () {
				reject();
			};

			document.body.appendChild(script);
		});
		
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
	 * @returns {MainLayoutAnonym$1.initModule@pro;tabPanel@pro;items@arr;map|MainLayoutAnonym$1.initModule@pro;tabPanel@call;insert|MainLayoutAnonym$1.initModule.panel|Boolean}
	 */
	addModulePanel : function(moduleName, panelClass, panelConfig) {		
		
//		if(!this.rendered) {
//			this.on("beforerender", function() {
//				GO.mainLayout.addModulePanel(moduleName, panelClass, panelConfig);
//			}, {single: true});
//			return;
//		}
//		
//		panelConfig = panelConfig || {};
//		
//		this.startMenu.add({
//			id: 'go-start-menu-' + moduleName,
//			moduleName: moduleName,
//			text: panelConfig.title || panelClass.prototype.title,
//			iconCls: panelConfig.iconCls || panelClass.prototype.iconCls || 'go-menu-icon-' + moduleName,
//			handler: function (item, e) {
//				this.openModule(item.moduleName);
//			},
//			scope: this
//		});

		panelConfig =panelConfig || {}
		panelConfig.package = panelClass.prototype.package;
//		
		GO.moduleManager._addModule(moduleName, panelClass, panelConfig);
				
		go.Router.add(new RegExp('^(' + moduleName + ")$"), function (name) {
			var pnl = GO.mainLayout.openModule(name);
			if(pnl.routeDefault) {
				pnl.routeDefault();
			}
		});
		
		//this.initModule(moduleName);
	},

	onAuthentication: function () {
		
		//load state
		Ext.state.Manager.setProvider(new GO.state.HttpProvider());
		
		this.fireEvent('authenticated', this);
		var me = this;

		Ext.getBody().mask(t("Loading..."));
	
		go.Modules.init().then(function() {
			Promise.all([
				go.customfields.CustomFields.init(),				
				me.loadLegacyModuleScripts()
			]).then(function(){
				go.Entities.init();
				me.addDefaultRoutes();
				me.renderUI();
				go.Router.check();
				Ext.getBody().unmask();
				
			}).catch(function(error){
				console.error(error);
				Ext.getBody().unmask();
				Ext.MessageBox.alert(t("Error"), t("An error occurred. More details can be found in the console."));
			});
		});
		
		
		
	},
	
	addDefaultRoutes : function() {
		var me = this;

		if(go.User.isAdmin) {
			go.Router.add(/systemsettings\/?([a-z0-9-_]*)?/i, function(tabId) {		
				me.openSystemSettings().setActiveItem(tabId);
			});
		}

		//Add these default routes on boot so they are added as last options for sure.
		//
		//default route for entities		
		go.Router.add(/([a-zA-Z0-9]*)\/([0-9]*)/, function(entity, id) {
			var entityObj = go.Entities.get(entity);
			if(!entityObj) {
				console.log("Entity ("+entity+") not found in default entity route")
				return false;
			}
			
			var module = entityObj.module; 
			var mainPanel = GO.mainLayout.openModule(module);
			var detailViewName = entity.charAt(0).toLowerCase() + entity.slice(1) + "Detail";

			if (mainPanel.route) {
				mainPanel.route(id, entityObj);
			} else if(mainPanel[detailViewName]) {
				mainPanel[detailViewName].load(id);
				mainPanel[detailViewName].show();
			} else {
				console.log("Default entity route failed because " + detailViewName + " or 'route' function not found in mainpanel of " + module + ":", mainPanel);
				console.log(arguments);
			}
		});
	},

	renderUI : function() {

		GO.checker = new GO.Checker();


		this.fireReady();				

		//Ext need to know where this charting swf file is in order to draw charts
//		Ext.chart.Chart.CHART_URL = 'views/Extjs3/ext/resources/charts.swf';

		var allPanels = GO.moduleManager.getAllPanelConfigs();

		var items = [];

		this.startMenu = new Ext.menu.Menu({
			id: 'startMenu',
			hideOnClick: true
		});

		if(GO.util.isMobileOrTablet()) {
			this.startMenu.on("show", function() {
				this.startMenu.setPosition(0,0);
				this.startMenu.setWidth(Ext.getBody().getWidth());
				this.startMenu.setHeight(Ext.getBody().getHeight());
			}, this);
		}

		if (allPanels.length == 0) {
			items = new Ext.Panel({
				id: 'go-module-panel-' + GO.settings.start_module,
				region: 'center',
				border: false,
				cls: 'go-form-panel',
				title: 'No modules',
				html: '<h1>No modules available</h1>You have a valid account but you don\'t have access to any of the modules. Please contact the administrator if you feel this is an error.'
			});
		}

		var adminMenuItems = [];
		var menuItemConfig;

		this.state = Ext.state.Manager.get('open-modules');

		for (var i = 0, l = allPanels.length; i < l; i++) {

			var panel = GO.moduleManager.getPanel(allPanels[i].moduleName);			

			if (this.state && this.state.indexOf(allPanels[i].moduleName) > -1) {
				items.push(panel);
			}
				
			menuItemConfig = {
				id: 'go-start-menu-' + allPanels[i].moduleName,
				moduleName: allPanels[i].moduleName,
				text: allPanels[i].title,
				//iconCls: 'go-menu-icon-' + allPanels[i].moduleName,
				iconStyle: "background-position: center middle; background-image: url("+go.Jmap.downloadUrl('core/moduleIcon/' + (panel.package || "legacy") + "/" + allPanels[i].moduleName)+")",
				//icon: ,
				handler: function (item, e) {
					this.openModule(item.moduleName);
				},
				scope: this
			};

			if (!allPanels[i].admin) {
				if (!this.state)
					items.push(GO.moduleManager.getPanel(allPanels[i].moduleName));

				// Check the subMenu property, if it is a submenu then don't add this item to the start menu
				if (!allPanels[i].inSubmenu) {
					this.startMenu.add(menuItemConfig);
				}
			} else
			{
				adminMenuItems.push(menuItemConfig);
			}
		}
		
		var subMenus = GO.moduleManager.getAllSubmenus();

		for (var key in subMenus) {

			var subMenuItems = [];
			var subItems = subMenus[key].items;

			for (var i = 0; i < subItems.length; i++) {
				if (!GO.util.empty(subItems[i])) {
					subMenuItems.push({
						id: 'go-start-menu-' + subItems[i].moduleName,
						moduleName: subItems[i].moduleName,
						text: subItems[i].title,
						iconCls: 'go-menu-icon-' + subItems[i].moduleName,
						handler: function (item, e) {
							this.openModule(item.moduleName);
						},
						scope: this
					});
				}
			}

			var subMenu = new Ext.menu.Menu({
				items: subMenuItems,
				cls: 'startmenu-submenu'
			});

			var subitemConfig = {
				text: key,
				menu: subMenu
			};

			Ext.apply(subitemConfig, subMenus[key].subMenuConfig);

			this.startMenu.add(new Ext.menu.Item(subitemConfig));
		}

		if (adminMenuItems.length) {

			this.startMenu.add(new Ext.menu.TextItem({id: 'go-start-menu-admin-menu', text: '<div class="menu-title">' + t("Admin menu") + '</div>'}));

			for (var i = 0; i < adminMenuItems.length; i++) {
				this.startMenu.add(adminMenuItems[i]);
			}
		}

		this.createTabPanel(items);

		this.beforeRender();
		this.rendered = true;
		this.fireEvent("beforerender", this);

		function getUserImgStyle() {
			if(!go.User.avatarId) {
				return "";
			}
			return 'background-image:url('+go.Jmap.downloadUrl(go.User.avatarId)+');'
		}

				var topPanel = new Ext.Panel({
					id:"mainNorthPanel",
					region: 'north',
					html:  '<div class="go-header-left"><div id="go-logo" title="'+GO.settings.config.product_name+'"></div></div>\
					<div class="go-header-right">\
						<div id="secondary-menu">\
							<div id="search_query"></div>\
							<div id="start-menu-link" ></div>\
							<a id="user-menu" class="user-img" style="'+getUserImgStyle()+'">\
								<span id="reminder-icon" style="display: none;">notifications</span>\
							</a>\
						</div>\
					</div>',
					height: dp(64),
					titlebar: false,
					border: false
				});

	//			var winSize = [window.scrollWidth , window.scrollHeight];

				GO.viewport = new Ext.Viewport({
					layout: 'border',
					border: false,
					items: [topPanel, this.tabPanel]
				});


				this.startMenuLink = new Ext.Button({
					menu: this.startMenu,
					menuAlign: 'tr-br?',
					text: '<i class="icon">apps</i>',
					renderTo: 'start-menu-link',
					clickEvent: 'mousedown',
					template: new Ext.XTemplate('<span><button></button></span>')
				});





				var userBtn = Ext.get('user-menu');
				var userMenuTpl = userBtn.dom.innerHTML;
				this.userMenuLink = new Ext.Button({
					menu: new Ext.menu.Menu({
						items: [
							{
								xtype: 'menutextitem',
								text: Ext.util.Format.htmlEncode(go.User.displayName),
								cls: 'go-display-name'
							}, '-', {
								text: t("My account"),
								iconCls: 'ic-account-circle',
								handler: function () {
									var dlg = new go.usersettings.UserSettingsDialog();
									dlg.load(go.User.id).show();
								},
								scope: this
							},
							'-',{
							iconCls: 'ic-help',
							text: t("Help"),
							handler: function () {
								
								if (Ext.form.VTypes.email(GO.settings.config.support_link)) {
									if (GO.email && GO.settings.modules.email.read_permission) {
										GO.email.showComposer({
											values: {to: GO.settings.config.support_link}
										});
									} else {
										document.location = 'mailto:' + GO.supportLink;
									}
								} else {
									window.open(GO.settings.config.support_link);
								}
							},
							scope: this
				}
//						,{
//							iconCls: 'ic-connect',
//							text:t("Connect your device"),
//							handler: function() {
//								var cyd;
//							}
//						}
				,{
					iconCls: 'ic-info',
					text: t("About {product_name}"),
					handler: function () {
						if (!this.aboutDialog)
						{
							this.aboutDialog = new GO.dialog.AboutDialog();
						}
						this.aboutDialog.show();
					},
					scope: this
				},
				'-',
				{
						text: t("Logout"),
						iconCls: 'ic-exit-to-app',
						handler: function() {
							go.AuthenticationManager.logout();
						},
						scope: this
					}
				]
			}),
			text: userMenuTpl,
			renderTo: userBtn,
			clickEvent: 'mousedown',
			template: new Ext.XTemplate('<span><button></button></span>')
		});


		if(go.User.isAdmin) {
			this.userMenuLink.menu.insert(3, {
				text: t("System settings"),
				iconCls: 'ic-settings',
				handler: function() {
					go.Router.goto("systemsettings");
				},
				scope: this
			});
		}

		GO.checker.init.defer(2000, GO.checker);
		GO.checker.on('alert', function (data) {
			if (data.notification_area)
			{
				Ext.get('notification-area').update(data.notification_area);
			}
		}, this);



		
		this.fireEvent('render');

		this.welcome();

		// Start in 10s to give the browser some time to boot other requests.
		setTimeout(function() {
			go.Jmap.sse();
		},10000);
		
	},
	
	
	openSystemSettings : function() {
		if(!this.systemSettingsWindow)
		{ 
			this.systemSettingsWindow = new go.systemsettings.Dialog({
				closeAction: "hide"
			});					
		}

		this.systemSettingsWindow.show();

		return this.systemSettingsWindow;
	},
	
	welcome : function() {
		if(go.User.id==1 && go.User.logins == 1) {
			
			Ext.MessageBox.alert(t("Welcome!"), t("Please complete the installation by running through the system settings. Click OK to continue to the system settings dialog."), function() {
				go.systemsettingsDialog = new go.systemsettings.Dialog();						
				go.systemsettingsDialog.show();
			});
			
			
		}
	},
//
//	search: function (query) {
//		if (!this.searchPanel) {
//			this.searchPanel = new GO.grid.SearchPanel(
//							{
//								query: query,
//								id: 'go-search-panel'
//							}
//			);
//			this.tabPanel.add(this.searchPanel);
//		} else
//		{
//			this.searchPanel.query = query;
//			this.searchPanel.load();
//		}
//		this.tabPanel.unhideTabStripItem(this.searchPanel);
//		this.searchPanel.show();
//	},

		initModule: function (moduleName) {
			if(!this.tabPanel) {
				return false;
			}

		var panelId = 'go-module-panel-' + moduleName;
		var panel;

		if (!this.tabPanel.items.map[panelId])
		{
			panel = GO.moduleManager.getPanel(moduleName);

			//Find the correct tab order for the tabpanel
			var volgorde = GO.moduleManager.getAllPanelConfigs(),
							order = this.tabPanel.items.length;
			for (var i = 0; i < volgorde.length; i++)
				if (volgorde[i].id == panelId)
					order = i;

			if (panel) {
				panel.id = panelId;
				panel = this.tabPanel.insert(order, panel);
				this.saveState();
			} else {
				return false;
			}

		} else {
			panel = this.tabPanel.items.map[panelId];
			this.tabPanel.unhideTabStripItem(panel);
		}

		return panel;
	},

	setNotification: function (moduleName, number, color) {
		var panel = this.getModulePanel(moduleName);
		if (panel) {

			if (!panel.origTitle) {
				panel.origTitle = panel.title;
			}

			var newTitle = number ? panel.origTitle + ' <div class="go-tab-notification" style="background-color:' + color + '">' + number + '</div>' : panel.origTitle;

			panel.setTitle(newTitle);
		}

	},

	openModule: function (moduleName) {
		var panel = this.initModule(moduleName);
		if (panel) {
			panel.show();
			return panel;
		}

		return false;
	},

	removeLoadMask: function () {

		var loading = Ext.get('loading');
		if (loading) {
			loading.fadeOut({duration: .2, remove: true});
		}
	},

	onLinksDeletedHandler: function (link_types, modulePanel, store) {
		if (link_types) {
			for (var i = 0; i < link_types.length; i++) {
				if (store.getById(link_types[i])) {
					modulePanel.on('show', function () {
						store.reload();
					}, this, {single: true});
				}
			}
		}
	}
});

GO.mainLayout = new GO.MainLayout();

// needed in pre v6.4
GO.mainLayout.on('callto', GO.util.callToHandler);
