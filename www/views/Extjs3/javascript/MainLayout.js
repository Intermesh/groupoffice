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
			Ext.Ajax.defaultHeaders['Authorization'] = 'Bearer '+go.User.accessToken;
			go.User.authenticate(function(data, options, success, response){
				
				if(success) {
					me.on('render', function() {
						me.fireEvent('boot', me);
					}, me, {single:true});
					me.onAuthentication(); // <- start Group-Office
				} else {
					go.User.clearAccessToken();
					
					me.fireEvent("boot", this);
					if(go.Router.requireAuthentication) {
						go.Router.pathBeforeLogin = go.Router.getPath();
						go.Router.goto("login");
					}
				}
			});
		} else {
			this.fireEvent("boot", this); // In the router there is an event attached.
			if(go.Router.requireAuthentication) {
				go.Router.pathBeforeLogin = go.Router.getPath();
				go.Router.goto("login");
			}
		}
	},

	login: function () {		
		GO.mainLayout.on('render', function () {
			go.Router.goto(go.Router.pathBeforeLogin);
		}, this, {single: true});
		
		if(!this.loginPanel) {
			//go.AuthenticationManager.register('password', new go.login.PasswordPanel(), 0);
			
			this.loginPanel = new go.login.LoginPanel();
			this.loginPanel.on('destoy', function() {
				this.loginPanel = null;
			}, this);
		}
		
		//console.log('ja');
			

		this.fireEvent('login', this);
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

			if (tabEl.style.display != 'none') {
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
				if (document.activeElement.tagName == 'TEXTAREA' || document.activeElement.tagName == 'INPUT') {
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

	onAuthentication: function () {
		
		//load state
		Ext.state.Manager.setProvider(new GO.state.HttpProvider());
		
		go.Modules.init();
		
		//legacy scripts loaded from scripts.inc.php
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.setAttribute('src', GO.url('core/moduleScripts'));
		script.charset = 'utf-8';
		script.id = 'testing';
		script.defer = true;
		script.async = true;
		script.onload = function () {
			
			//load modules
			go.Modules.onReady(function () {

				if (this.loginPanel) {
					this.loginPanel.destroy();
				}
				GO.checker = new GO.Checker();


				this.fireReady();

				this.fireEvent('authenticated', this);

				//Ext need to know where this charting swf file is in order to draw charts
	//		Ext.chart.Chart.CHART_URL = 'views/Extjs3/ext/resources/charts.swf';

				var allPanels = GO.moduleManager.getAllPanelConfigs();

				var items = [];

				this.startMenu = new Ext.menu.Menu({
					id: 'startMenu',
					hideOnClick: true
				});

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

					if (this.state && this.state.indexOf(allPanels[i].moduleName) > -1)
						items.push(GO.moduleManager.getPanel(allPanels[i].moduleName));

					menuItemConfig = {
						id: 'go-start-menu-' + allPanels[i].moduleName,
						moduleName: allPanels[i].moduleName,
						text: allPanels[i].title,
						iconCls: 'go-menu-icon-' + allPanels[i].moduleName,
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
					renderTo: 'viewport',
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


				var helpMenu = new Ext.menu.Menu({
					id: 'helpMenu',
					items: [{
							iconCls: 'ic-help',
							text: t("Help contents"),
							handler: function () {
								GO.openHelp('');
							},
							scope: this
						}]
				});

				if (GO.settings.config.product_name == 'Group-Office') {
					helpMenu.addItem({
						iconCls: 'ic-forum',
						text: t("Community forum"),
						handler: function () {
							var win = window.open('https://www.group-office.com/forum/');
							win.focus();
						},
						scope: this

					});
					helpMenu.addItem('-');

					if (GO.settings.config.support_link) {
						helpMenu.addItem({
							iconCls: 'ic-contact-mail',
							text: t("Contact support desk"),
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
						});
					}
					if (GO.settings.config.report_bug_link) {
						helpMenu.addItem({
							iconCls: 'ic-bug-report',
							text: t("Report a bug"),
							handler: function () {

								if (Ext.form.VTypes.email(GO.settings.config.report_bug_link)) {
									if (GO.email && GO.settings.modules.email.read_permission) {
										GO.email.showComposer({
											values: {to: GO.settings.config.report_bug_link}
										});
									} else {
										document.location = 'mailto:' + GO.settings.config.report_bug_link;
									}
								} else {
									var win = window.open(GO.settings.config.report_bug_link);
									win.focus();
								}
							},
							scope: this
						});
					}
				}

				helpMenu.addItem('-');
				helpMenu.addItem({
					iconCls: 'ic-info',
					text: t("About {product_name}").replace('{product_name}', GO.settings.config.product_name),
					handler: function () {
						if (!this.aboutDialog)
						{
							this.aboutDialog = new GO.dialog.AboutDialog();
						}
						this.aboutDialog.show();
					},
					scope: this
				});



				var userBtn = Ext.get('user-menu');
				var userMenuTpl = userBtn.dom.innerHTML;
				this.userMenuLink = new Ext.Button({
					menu: new Ext.menu.Menu({
						items: [
							{
								xtype: 'menutextitem',
								text: go.User.displayName,
								cls: 'go-display-name'
							}, '-', {
								text: t("Settings"),
								iconCls: 'ic-settings',
								handler: function () {
									if(!go.userSettingsDialog) {
										go.userSettingsDialog = new go.usersettings.UserSettingsDialog();
									}
									go.userSettingsDialog.show(go.User.id);

								},
								scope: this
							},
							{
								text: t("Help"),
								iconCls: 'ic-help',
								menu: helpMenu
							}, {
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
					this.userMenuLink.menu.insert(2, {

						text: t("System settings"),
						iconCls: 'ic-settings',
						handler: function() {
							if(!go.systemsettingsDialog) {
								go.systemsettingsDialog = new go.systemsettings.Dialog();
							}
							go.systemsettingsDialog.show();
						}
					});
				}

				GO.checker.init.defer(2000, GO.checker);
				GO.checker.on('alert', function (data) {
					if (data.notification_area)
					{
						Ext.get('notification-area').update(data.notification_area);
					}
				}, this);



				this.rendered = true;
				this.fireEvent('render');

				this.welcome();


			}, this);
		
		}.createDelegate(this);
		
		document.body.appendChild(script);
		
		
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
