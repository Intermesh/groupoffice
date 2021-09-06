/* global go */

/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @author Merijn Schering <mschering@intermesh.nl>
 */
go.systemsettings.Dialog = Ext.extend(go.Window, {
	
	modal:true,
	resizable:true,
	maximizable:true,
	//maximized: true,
	iconCls: 'ic-settings',
	title: t("System settings"),	
	width:dp(1000),
	height:dp(800),
	layout:'responsive',
	closeAction: "hide",
	stateId: "go-system-settings",

	initComponent: function () {
		
		this.saveButton = new Ext.Button({
			text: t('Save'),
			handler: this.submit,
			scope:this,
			cls: "primary"
		});		
		
		this.tabPanel = new Ext.TabPanel({
			headerCfg: {cls:'x-hide-display'},
			region: "center",
			items: [],
			hideMode: "offsets"
		});
		
		
		this.tabStore = new Ext.data.ArrayStore({
			fields: ['name', 'iconCls', 'itemId'],
			data: []
		});
		
		this.selectMenu = new go.NavMenu({
			region:'west',
			width:dp(300),
			store: this.tabStore,
			listeners: {
				selectionchange: function(view, nodes) {	
					if(nodes.length) {
						this.tabPanel.setActiveTab(nodes[0].viewIndex);
					} else
					{
						//restore selection if user clicked outside of view
						view.select(this.tabPanel.items.indexOf(this.tabPanel.getActiveTab()));
					}

					var activeTab = this.tabPanel.getActiveTab();
					if(activeTab && activeTab.itemId) {
						go.Router.setPath("systemsettings/" + activeTab.itemId);
					} else{
						go.Router.setPath("systemsettings");
					}

					this.tabPanel.show();
				},
				scope:this
			}
		});		

		Ext.apply(this,{
			
			items: [
				this.selectMenu,
				this.tabPanel
			]
		});


		Ext.apply(this, {buttons:[this.saveButton]});


		this.tools = [{
			id: "left",
			cls: 'go-show-tablet',
			handler: function () {
				this.selectMenu.show();
			},
			scope: this
		}];

		this.selectMenu.on("show", function() {
			var tool = this.getTool("left");
			tool.dom.classList.add('go-hide')
		},this);

		this.tabPanel.on("show", function() {
			var tool = this.getTool("left");
			tool.dom.classList.remove('go-hide')
		}, this);

		
		this.addEvents({
			'loadStart' : true,
			'loadComplete' : true,
			'submitStart' : true,
			'submitComplete' : true
		});

		if(go.User.isAdmin) {
			this.addPanel(go.systemsettings.GeneralPanel);
			this.addPanel(go.systemsettings.AppearancePanel);
			this.addPanel(go.systemsettings.NotificationsPanel);
			this.addPanel(go.systemsettings.AuthenticationPanel);
			this.addPanel(go.defaultpermissions.SystemSettingsPanel);
		}
		let c = go.User.capabilities['go:core:core'] || {};
		if(c.mayChangeCustomFields)
			this.addPanel(go.customfields.SystemSettingsPanel);
		if(c.mayChangeUsers)
			this.addPanel(go.users.SystemSettingsUserGrid);
		if(c.mayChangeGroups)
			this.addPanel(go.groups.SystemSettingsGroupGrid);
		if(go.User.isAdmin) {
			this.addPanel(go.modules.SystemSettingsModuleGrid);
			this.addPanel(go.tools.SystemSettingsTools);
			this.addPanel(go.oauth.SystemSettingsPanel);
			this.addPanel(go.cron.SystemSettingsCronGrid, null, 'divider');
		}

		this.loadModulePanels();
		
		go.systemsettings.Dialog.superclass.initComponent.call(this);

		this.on("hide", function() {
			go.Router.setPath("");

			//reload to make sure settings apply
			window.location.replace(window.location.pathname);
		}, this);
	},
	
	loadModulePanels : function() {
		var available = go.Modules.getAvailable(), config, pnl, i, i1, sepAdded = false;

		for(i = 0, l = available.length; i < l; i++) {

			// if(!available[i].userRights.mayManage) {
			// 	continue;
			// }
			
			config = go.Modules.getConfig(available[i].package, available[i].name);
			
			if(!config.systemSettingsPanels) {
				continue;
			}
			
			if(available[i].package != 'core' && !sepAdded) {
				// this.selectMenu.addSeparator();
				// sepAdded = true;
			}
			
			for(i1 = 0, l2 = config.systemSettingsPanels.length; i1 < l2; i1++) {
				pnl = eval(config.systemSettingsPanels[i1]);

				this.addPanel(pnl, null, null, pnl.prototype.itemId ? null : {
					itemId: available[i].package + "-" + available[i].name
				});
			}
		}
	},

	setActiveItem: function(itemId) {
		var record = this.tabStore.find('itemId', itemId);		
		if(record) {
			this.selectMenu.select(this.tabStore.getAt(record));
		}
	},
	
	show: function(){
		go.systemsettings.Dialog.superclass.show.call(this);
		if(!GO.util.isTabletScreenSize()) {
			this.selectMenu.select(this.tabStore.getAt(0));
		}
		this.load();
	},

	submit : function(){		
		
		this.submitCount = 0;
		// loop through child panels and call onSubmitStart function if available
		this.tabPanel.items.each(function(tab) {			
			if(tab.rendered && tab.onSubmit) {
				this.submitCount++;
				tab.onSubmit(this.onSubmitComplete, this);			
			}
		},this);	
	},
	
	load: function() {
		// loop through child panels and call onSubmitStart function if available
		this.tabPanel.items.each(function(tab) {			
			if(tab.onLoad) {
				tab.onLoad(this.onLoadComplete, this);			
			}
		},this);
	},
	
	onSubmitComplete : function(tab, success) {
		if(success) {
			this.submitCount--;
			if(this.submitCount === 0) {

				this.fireEvent("submit", this);

				this.hide();
			}
		}
	},
	
	onLoadComplete : function() {
		
	},
	
	
	
	
	/**
	 * Add a panel to the tabpanel of this dialog
	 * 
	 * @param string panelID
	 * @param string panelClass
	 * @param object panelConfig
	 * @param int position
	 * @param boolean passwordProtected
	 */
	addPanel : function(panelClass, position,cls, cfg){

		cfg = cfg || {};

		Ext.applyIf(cfg, {
			header: false,
			loaded:false,
			submitted:false
		});
		
		var pnl = new panelClass(cfg);

		if(!pnl.hasPermission) {
			console.warn("System setting panel " + pnl.title + " does not extend 'go.systemsettings.Panel'");
		} else {
			if (!pnl.hasPermission()) {
				return;
			}
		}
		
		var menuRec = new Ext.data.Record({
			itemId: pnl.itemId,
			name: pnl.title,
			iconCls: pnl.iconCls,
			cls: cls
		});
		
		if(Ext.isEmpty(position)){
			this.tabPanel.add(pnl);
			this.tabStore.add(menuRec);
		}else{
			this.tabPanel.insert(position,pnl);
			this.tabStore.insert(position,menuRec);
		}
	}

});
