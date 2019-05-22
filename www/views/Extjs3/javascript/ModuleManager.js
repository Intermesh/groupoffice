/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ModuleManager.js 22237 2018-01-24 10:24:54Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 


GO.ModuleManager = Ext.extend(function(){	
	this.addEvents({
		'moduleReady' : true,
		'moduleconstructed' : true,
		'languageLoaded' : true
	});	
	this.resumeEvents();

	
}, Ext.util.Observable,
{
	modules : {},
	modulePanels : {},
	panelConfigs : {},
	sortOrder : Array(),
	
	adminModulePanels : {},
	adminPanelConfigs : {},
	adminSortOrder : Array(),
	
	
	settingsPanels : {},
	settingsPanelConfigs : {},
	settingsSortOrder : Array(),
	
	readyFunctions : {},
	
	subMenus : {},
	
	
	addSettingsPanel : function(panelID, panelClass, panelConfig, sortPriority)
	{		
		this.settingsPanels[panelID] = panelClass;
		this.settingsPanelConfigs[panelID] = panelConfig;
		
		if(Ext.isEmpty(sortPriority))
			this.settingsSortOrder.push(panelID);
		else
			this.settingsSortOrder.splice(sortPriority,0,panelID);
	},
	
	getSettingsPanel : function(panelID)
	{
		if(this.settingsPanels[panelID])
			return new this.settingsPanels[panelID](this.settingsPanelConfigs[panelID]);
		else
			return false;				
	},
	
	getAllSettingsPanels : function(){	
		
		var panels = [];
		
		for(var i=0,l=this.settingsSortOrder.length;i<l;i++)
		{
			panels.push(this.getSettingsPanel(this.settingsSortOrder[i]));	
		}
		return panels;
	},
	
	addModule : function(moduleName, panelClass, panelConfig, subMenuConfig) {
		go.Modules.register("legacy", moduleName, {
			title: panelConfig.title,
			requiredPermissionLevel: panelConfig.requiredPermissionLevel || GO.permissionLevels.read,
			mainPanel: panelClass,
			panelConfig: panelConfig,
			subMenuConfig: subMenuConfig
		});
	},
	
	/**
	 * 
	 * @param {type} moduleName
	 * @param {type} panelClass
	 * @param {type} panelConfig
	 * @param Object subMenuConfig {title:'title',iconCls:'classname'} // title is a required property
	 * @returns {undefined}
	 */
	_addModule : function(moduleName, panelClass, panelConfig, subMenuConfig)
	{		
		//this.modules[moduleName]=true;
		if(panelClass)
		{			
			
			
			if(typeof panelClass == "string") {
				panelClass = GO.util.stringToFunction(panelClass);
			}
			
			panelConfig.inSubmenu = false;
			panelConfig.moduleName = moduleName;
			if(!panelConfig.iconCls) {
				panelConfig.iconCls = panelClass.prototype.iconCls || "go-tab-icon-"+moduleName;
			}
			panelConfig.id='go-module-panel-'+panelConfig.moduleName;

			if(!panelConfig.cls)
				panelConfig.cls = 'go-module-panel';
			
			if(!panelConfig.title && panelClass.prototype.title) {
				panelConfig.title = panelClass.prototype.title;
			}
			
			this.modulePanels[moduleName] = panelClass;
			
			// If this item needs to be inside a  submenu
			if(subMenuConfig){
				if(!this.subMenus[subMenuConfig.title]){
					this.subMenus[subMenuConfig.title] = {
						subMenuConfig:subMenuConfig,
						items:[]
					};
				}

				this.subMenus[subMenuConfig.title].items.push(panelConfig);
				panelConfig.inSubmenu = true;
			}
			
			this.panelConfigs[moduleName] = panelConfig;
			this.sortOrder.push(moduleName);
			
		}
		this.onAddModule(moduleName);
		
	},
	
	onAddModule : function(moduleName)
	{
		this.modules[moduleName]=true;
		if(this.readyFunctions[moduleName])
		{
			for(var i=0,l=this.readyFunctions[moduleName].length;i<l;i++)
			{
				var c = this.readyFunctions[moduleName][i];
				c.fn.call(c.fn.scope,moduleName,this);
			}
		}
	},
	
	onModuleReady : function(module, fn, scope)
	{
		scope=scope||window;

		if(!this.modules[module]){			
			this.readyFunctions[module] = this.readyFunctions[module] || [];			
			this.readyFunctions[module].push({
				fn:fn,
				scope: scope				
			});
		} else {
			fn.call(scope, module, this);
		}
	},
	
	getPanel : function(moduleName) {
		if(this.modulePanels[moduleName]){
			Ext.reg("module-main-"+moduleName, this.modulePanels[moduleName]);
			this.panelConfigs[moduleName].xtype = "module-main-"+moduleName;
			var p = this.panelConfigs[moduleName];
			return p;
		} else {
			return false;
		}
	},
	
	getAllPanels : function(){
		
		var panels = [];
		
		for(var i=0, l = this.sortOrder.length;i<l;i++)
		{
			panels.push(this.getPanel(this.sortOrder[i]));	
		}
		return panels;
	},

	getAllPanelConfigs : function(){
		var configs = [];

		for(var i=0, l = this.sortOrder.length;i<l;i++)
		{
			configs.push(this.panelConfigs[this.sortOrder[i]]);
		}
		return configs;
	},
	
	userHasModule : function(module){
		return go.Modules.isAvailable("legacy", module);
	},
	
	getAllSubmenus : function(){
		return this.subMenus;
	}	
});


GO.moduleManager = new GO.ModuleManager();
