(function () {
	var ModMan = Ext.extend(Ext.util.Observable, {
		
		registered: {},
		
		register: function (name, config) {	
			
			config = config || {};
			
			this.registered[name] = config;		
			
			if(!config.panelConfig) {
				config.panelConfig = {title: config.title};
			}
			
			if(!config.requiredPermissionLevel) {
				config.requiredPermissionLevel = GO.permissionLevels.read;
			}	
			
			if(config.mainPanel) {
				go.Router.add(new RegExp(name+"$"), function() {
					GO.mainLayout.openModule(name);
				});
			}

			if (config.entities) {
				config.entities.forEach(function (e) {
					go.EntityManager.register(name, e);
				});
			}
		},
		
		/**
		 * Check if the current user has thie module
		 * 
		 * @param {string} moduleName
		 * @returns {boolean}
		 */
		isAvailable : function(moduleName) {
			
			if(!this.registered[moduleName]) {
				return false;
			}
			
			var module = this.get(moduleName);
			if(!module) {
				return false;
			}
			return module.permissionLevel >= this.registered[moduleName].requiredPermissionLevel;			
		},
		
		get : function(name) {
			var all = go.stores.Module.data;
			
			for(id in all) {
				if(all[id].name == name) {
					return all[id];
				}
			};
			
			return false;			
		},
		
		//will be called after login
		init : function() {
			go.stores.Module.getUpdates(function () {
			
				for(modName in this.registered) {
					
					if(!this.isAvailable(modName, this.registered[modName].permissionLevel)) {
						
						continue;
					}
					
					var config = this.registered[modName];
					
					if (config.mainPanel) {
						//todo GO.moduleManager is deprecated
						GO.moduleManager._addModule(modName, config.mainPanel, config.panelConfig, config.subMenuConfig);
					}
					
					if(config.initModule)
					{
						config.initModule();
					}
					
				}
				
				go.ModuleManager.fireReady();
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

	go.ModuleManager = new ModMan;

})();


go.EntityManager.register('modules', 'Module');
