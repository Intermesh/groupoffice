go.modules.SystemSettingsModuleGrid = Ext.extend(go.grid.EditorGridPanel, {
	iconCls: 'ic-extension',
	autoExpandColumn: 'name',
	layout: 'fit',
	paging: false,
	clicksToEdit: 1,
	loadMask: true,
	
	initComponent: function () {
		
		this.title = t("Modules");
		
		var reader = new Ext.data.JsonReader({
			root: 'results',
			totalProperty: 'total',
			fields: ['name', 'package', 'localizedPackage', 'localizedName',  'description', 'id', 'sort_order', 'admin_menu', 'aclId', 'icon', 'enabled', 'warning', 'buyEnabled','not_installable', 'isRefactored','installed'],
			id: 'name'
		});

		this.store = new GO.data.GroupingStore({
			url: GO.url('modules/module/store'),
			reader: reader,
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			},
			groupField: 'localizedPackage',
			remoteGroup:false,
			remoteSort:false
		});

		this.store.on('load',function(){
			if(!this.store.reader.jsonData.has_license)
				this.trialButton.show();
			else
				this.trialButton.hide();

		}, this);

		this.tbar = new Ext.Toolbar({
			items: [
				{
					iconCls: 'ic-refresh',
					tooltip: t("Refresh"),
					handler: function() {
						this.store.load();
					},
					scope: this
				},{
					iconCls: 'ic-settings',
					text: t("Buy licenses", "modules"),
					hidden: GO.settings.config.product_name != 'Group-Office',
					handler: function() {				
						window.open('https://www.group-office.com/shop/');					
					},
					scope: this
				},


				this.trialButton = new Ext.Button({
					iconCls: 'ic-settings',
					text: t("30 day trial license", "modules"),
					hidden:true,
					handler: function() {
						Ext.MessageBox.confirm(
							t("30 day trial license", "modules"),
							t("Get a free 30 day trial with unlimited users and all available modules. Click 'Yes' to continue to our shop and get your trial license. If you don't have a shop account you'll need to register.", "modules"),
							function(btn){
								if(btn==='yes'){
									window.open('https://www.group-office.com/30-day-trial?hostname='+document.domain,'groupoffice-shop');
								}
							}
						);

					},
					scope: this
				})]
		});

		var checkColumn = new GO.grid.CheckColumn({
			header: t("Enabled", "modules"),
			dataIndex: 'enabled',
			width: 100,
			disabled_field:'not_installable',
			listeners: {
				scope: this,
				change: function(record, checked) {

					if(record.data.isRefactored) {
						return this.submitJmap(record);
					}

					GO.request({
						maskEl:this.getEl(),
						url: 'modules/module/update',
						params: {
							id: record.data.name,
							enabled: checked
						},
						scope: this,
						success: function(response, options, result) {

							if (result.aclId) {
								record.set('aclId', result.aclId);
								record.set('id', result.id);
								record.set("enabled", checked);

								if (checked) {
									this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);
									//this.store.load();
								}
							}
							record.commit();
						}
					});
				}
			}
		});

		var actions = this.initRowActions();

		var cols = [{
				header: t("Name"),
				dataIndex: 'name',
				id: 'name',
				renderer: function(name, cell, record) {
					return '<div class="mo-title" style="background-image:url(' + go.Jmap.downloadUrl('core/moduleIcon/'+(record.data.package || "legacy")+'/'+record.data.name) + ')">'
									+ record.data.localizedName +'</div>';
				}
			}, 
			checkColumn,
			{
				header: t("Sort order", "modules"),
				dataIndex: 'sort_order',
				sortable:true,
				id: 'sort_order',
				editor: {
					xtype:'numberfield',
					allowBlank: false,
					decimals:0
				}
			},{
				header: "Package",
				dataIndex: 'localizedPackage',
				id: 'localizedPackage'				
			}
		];

		// if(GO.settings.config.debug) {
			cols.push(actions);
		// }

		this.cm = new Ext.grid.ColumnModel(cols);
		this.sm = new Ext.grid.RowSelectionModel({
			singleSelect: false
		});
		this.plugins = [actions];

		var store = this.store;

		this.view = new Ext.grid.GroupingView({
			hideGroupedColumn:true,
			enableRowBody: true,
			showPreview: true,
			showGroupName: false,
	//		autoFill: true,
			startCollapsed:true,
			emptyText: t("No items to display"),
			getRowClass: function(record, rowIndex, p, store) {
				if (this.showPreview && record.data.description.length) {
					p.body = record.data.description;
					return 'x-grid3-row-expanded';
				}
				return 'x-grid3-row-collapsed';
			}
		});

		this.on('afteredit', function(e){
			this.submitRecord(e.record);
		}, this);

		this.on('beforeedit', function(e){
			return e.record.data.enabled;
		}, this);


		this.on("rowdblclick", function(grid, rowIndex, event) {
			var moduleRecord = grid.store.getAt(rowIndex);

			if (moduleRecord.data.aclId) {
				this.showPermissions(moduleRecord.data.name, t(moduleRecord.data.name, moduleRecord.data.name), moduleRecord.data.aclId);
			}
		}, this);

		go.modules.SystemSettingsModuleGrid.superclass.initComponent.call(this);
		
	},
	
	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,
			actions: [				
				{
					iconCls: 'ic-more-vert'					
				}]
		});

		actions.on({
			scope: this,
			action: function (grid, record, action, row, col, e, target) {
				
				if(!record.data.id) {
					return;
				}

				this.showMoreMenu(record, e);
			}
		});

		return actions;

	},
	

	afterRender: function() {

		go.modules.SystemSettingsModuleGrid.superclass.afterRender.call(this);

		this.store.load();

	},

	showPermissions: function(moduleId, name, acl_id) {
		if (!this.permissionsWin) {
			this.permissionsWin = new go.modules.PermissionsWindow();
			this.permissionsWin.on('hide', function() {
				// Loop through the recently installed modules, allowing the user to
				// set the permissions, module by module.
				if (this.installedModules && this.installedModules.length) {
					var r = this.installedModules.shift();
					this.permissionsWin.show(r.id, r.name, r.acl_id);
				}
			}, this);
		}
		this.permissionsWin.show(moduleId, name, acl_id);
	},
	warningRenderer: function(name, cell, record) {
		return record.data.warning != '' ?
						'<div class="go-icon go-warning-msg" ext:qtip="' + Ext.util.Format.htmlEncode(record.data.warning) + '"></div>' : '';
	},

	/**
	 * Submit the record
	 * 
	 * @param array record
	 * @returns {undefined}
	 */
	submitRecord : function(record){
		
		if(record.data.isRefactored) {
			return this.submitJmap(record);
		}

		this.getEl().mask(t("Loading..."));

		var url = GO.url('modules/module/updateModuleModel');

		Ext.Ajax.request({
			method:'POST',
			url: url,
			params : {
				id:record.data.name
			},
			jsonData: {module:this.createJSON(record.data)},
			scope : this,
			callback : function (options, success,response) {

				this.getEl().unmask();
				var responseParams = Ext.decode(response.responseText);

				if (!responseParams.success) {
					GO.errorDialog.show(responseParams.feedback);
					this.store.load();
				}else{
					if(responseParams.id){
						record.set('id', responseParams.id);
					}
					record.commit();
				}
			}
		});
	},
	
	
	submitJmap : function(record) {
		
		var params = {};

		this.getEl().mask(t("Loading..."));
		
		
		if(record.data.id) {
			params.update = {};
			params.update[record.data.id] = {
				enabled: record.data.enabled,
				sort_order: record.data.sort_order ? record.data.sort_order : 0
			};
			go.Db.store("Module").set(params, function(options, success, response) {
				this.getEl().unmask();
				if(success && response.updated && response.updated[record.data.id]){
					if(record.data.enabled && record.isModified("enabled")) {						
						// record.set('aclId', response['created'][record.data.id].aclId);
						this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);
					}

					record.commit();
				} else
				{
					var msg = response.message || t("Sorry, an error occurred");
					if(response.notUpdated && response.notUpdated[record.data.id] && response.notUpdated[record.data.id].validationErrors) {
						for(var prop in response.notUpdated[record.data.id].validationErrors) {
							msg = response.notUpdated[record.data.id].validationErrors[prop].description;
						}
					}
					Ext.MessageBox.alert(t("Error"), msg);

				}
				this.store.load();

			}, this);
		} else
		{
		
			go.Jmap.request({
				method: "Module/install",
				params: {
					name: record.data.name,
					package: record.data.package
				},
				callback: function(options, success, response) {
					this.getEl().unmask();

					if(success && response['list'][0]) {
						record.set('enabled', true);										
						record.set('id', response['list'][0].id);
						record.set('aclId', response['list'][0].aclId);
						this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);					
						record.commit();
					} else
					{
						var msg = response.message || t("Sorry, an error occurred");

						Ext.MessageBox.alert(t("Error"), msg);
					}
					this.store.load();
				},
				scope: this
			});
			
		}		
		
		
	},

/**
 * Create Json string for posting to the controller
 * 
 * @param array params
 * @returns JSON String
 */
	createJSON : function(params){

		var keys, JSON={}, currentJSONlevel;
		
		for(var key in params){
			
			keys = key.split('.');
			
			currentJSONlevel = JSON;
			
			for(var i=0;i<keys.length;i++){
				if(i===(keys.length-1)){
					
					// Change true to 1 for customfields checkboxes
					if(params[key] == true){
						params[key] = '1';
					}
					
					currentJSONlevel[keys[i]]= params[key];
				}else
				{
					currentJSONlevel[keys[i]]=currentJSONlevel[keys[i]] || {};
					currentJSONlevel=currentJSONlevel[keys[i]];
				}				
			}
			
			currentJSONlevel = JSON;
			
		}

		return JSON;
	},


	showMoreMenu : function(record, e) {
		if(!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId: "edit",
						iconCls: 'ic-share',
						text: t("Permissions"),
						handler: function() {
							var record =this.moreMenu.record;
							this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);					
						},
						scope: this						
					},
					"-",
					{
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function() {

							var record =this.moreMenu.record;

							Ext.MessageBox.confirm(t("Delete"), t("All data will be lost! Are you sure you want to delete module '{item}'?").replace('{item}', record.data.name), function(cmd) {
								console.log(cmd);
								if(cmd != 'yes') {
									return;
								}
								
								if(record.data.isRefactored) {
									
									go.Jmap.request({
										method: "Module/uninstall",
										params: {
											name: record.data.name,
											package: record.data.package
										},
										callback: function() {
											record.set('enabled', false);
											record.set('id', null);
											record.commit();
										},
										scope: this
									});
								}else
								{
									GO.request({
										url: "modules/module/delete",
										params: {
											id: record.data.id
										},
										callback: function(){
											record.set('enabled', false);
											record.set('id', null);
											record.commit();
										},
										scope: this
									});
								}
								
							}, this);

						},
						scope: this						
					}
				]
			});
		}
		
		this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < go.permissionLevels.manage);
		this.moreMenu.getComponent("delete").setDisabled(record.get("permissionLevel") < go.permissionLevels.manage);
		
		this.moreMenu.record = record;
		
		this.moreMenu.showAt(e.getXY());
	}
});