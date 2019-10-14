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
	resizable:false,
	maximizable:false,
	maximized: true,
	iconCls: 'ic-settings',
	title: t("System settings"),
	
	initComponent: function () {
		
		this.saveButton = new Ext.Button({
			text: t('Save'),
			handler: this.submit,
			scope:this
		});
				

		
		this.tabPanel = new Ext.TabPanel({
			headerCfg: {cls:'x-hide-display'},
			region: "center",
			items: []
		});
		
		
		this.tabStore = new Ext.data.ArrayStore({
			fields: ['name', 'icon', 'visible'],
			data: []
		});
		
		this.selectMenu = new Ext.Panel({
			region:'west',
			cls: 'go-sidenav',
			layout:'fit',
			width:dp(300),
			items:[this.selectView = new Ext.DataView({
				xtype: 'dataview',
				cls: 'go-nav',
				store:this.tabStore,
				singleSelect: true,
				overClass:'x-view-over',
				itemSelector:'div',
				tpl:'<tpl for=".">\
					<div><i class="icon {icon}"></i>\
					<span>{name}</span></div>\
				</tpl>',
				columns: [{dataIndex:'name'}],
				listeners: {
					selectionchange: function(view, nodes) {		
						if(nodes.length) {
							this.tabPanel.setActiveTab(nodes[0].viewIndex);
						} else
						{
							//restore selection if user clicked outside of view
							view.select(this.tabPanel.items.indexOf(this.tabPanel.getActiveTab()));
						}
					},
					scope:this
				}
			})]
		});
		
		Ext.apply(this,{
			width:dp(1000),
			height:dp(800),
			layout:'border',
			closeAction:'hide',
			items: [
				this.selectMenu,
				this.tabPanel
			],
			buttons:[
				this.saveButton
			]
		});
		
		this.addEvents({
			'loadStart' : true,
			'loadComplete' : true,
			'submitStart' : true,
			'submitComplete' : true
		});
		
		this.addPanel(go.systemsettings.GeneralPanel);
		this.addPanel(go.systemsettings.AppearancePanel);
		this.addPanel(go.systemsettings.NotificationsPanel);
		this.addPanel(go.systemsettings.AuthenticationPanel);
		
		this.loadModulePanels();
		
		go.systemsettings.Dialog.superclass.initComponent.call(this);
	},
	
	loadModulePanels : function() {
		var available = go.Modules.getAvailable(), config, pnl, i, i1;
		
		for(i = 0, l = available.length; i < l; i++) {
			
			config = go.Modules.getConfig(available[i].package, available[i].name);
			
			if(!config.systemSettingsPanels) {
				continue;
			}
			
			for(i1 = 0, l2 = config.systemSettingsPanels.length; i1 < l2; i1++) {
				pnl = eval(config.systemSettingsPanels[i1]);				
				this.addPanel(pnl);
			}
		}
	},
	
	show: function(){
		go.systemsettings.Dialog.superclass.show.call(this);
		this.selectView.select(this.tabStore.getAt(0));
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
			if(this.submitCount == 0) {
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
	addPanel : function(panelClass, position){
		var cfg = {
			header: false,
			loaded:false,
			submitted:false
		};
		
		var pnl = new panelClass(cfg);
		
			var menuRec = new Ext.data.Record({
			'name':pnl.title,
			'icon':pnl.iconCls,
			'visible':true
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
