/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: modules.js 22335 2018-02-06 16:25:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.modules.MainPanel = function(config) {
	if (!config) {
		config = {};
	}
	
	
	var reader = new Ext.data.JsonReader({
			root: 'results',
			totalProperty: 'total',
			fields: ['name', 'package', 'localizedName',  'description', 'id', 'sort_order', 'admin_menu', 'aclId', 'icon', 'enabled', 'warning', 'buyEnabled','not_installable', 'isRefactored','installed'],
			id: 'name'
		});

	this.store = new GO.data.GroupingStore({
		url: GO.url('modules/module/store'),
		reader: reader,
		sortInfo: {
			field: 'name',
			direction: 'ASC'
		},
		groupField: 'package',
		remoteGroup:false,
		remoteSort:false
	});
	
	this.store.on('load',function(){
		if(!this.store.reader.jsonData.has_license)
			this.trialButton.show();
		else
			this.trialButton.hide();
		
	}, this);

	config.tbar = new Ext.Toolbar({
		items: [
			{
				iconCls: 'ic-refresh',
				tooltip: t("Refresh"),
				handler: function() {
					this.store.load();
				},
				scope: this
			},
//			{
//				iconCls: 'btn-settings',
//				text: "Install license file",
//				cls: 'x-btn-text-icon',
//				hidden: GO.settings.config.product_name!=='Group-Office',
//				handler: function() {
//					if(!this.installLicenseDialog){
//						this.installLicenseDialog = new GO.modules.InstallLicenseDialog({
//							
//						});						
//					}					
//					this.installLicenseDialog.show();
//				},
//				scope: this
//			},
			{
				iconCls: 'btn-settings',
				text: t("Buy licenses", "modules"),
				cls: 'x-btn-text-icon',
				hidden: GO.settings.config.product_name != 'Group-Office',
				handler: function() {				
					window.open('https://www.group-office.com/shop/');					
				},
				scope: this
			},


			this.trialButton = new Ext.Button({
				iconCls: 'btn-settings',
				text: t("30 day trial license", "modules"),
				cls: 'x-btn-text-icon',
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


	
	var cols = [
		{
			header: t("Name"),
			dataIndex: 'name',
			id: 'name',
			renderer: this.iconRenderer
		}, 
		checkColumn,
		{
			header: t("Sort order", "modules"),
			dataIndex: 'sort_order',
			sortable:true,
			id: 'sort_order',
			editor: new GO.form.NumberField({
				allowBlank: false,
				decimals:0
			})
		},{
			header:'',
			dataIndex:'actions',
			renderer:function(val, meta, record, rowIndex, columnIndex, store){
				meta.css += 'mo-actions-column';			
				if(record.data.installed){
					return '<a href="#" onclick="GO.moduleManager.deleteModule(\''+record.data.id+'\',\''+record.data.name+'\');"><span class="go-icon-mo-delete"></span></a>';
				} else {
					return '';
				}
			}
		},{
			header: "Package",
			dataIndex: 'package',
			id: 'package',
			renderer: function(v) {
				return v.ucFirst();
			}
		}
	];
	
	if(GO.settings.config.debug) {
		cols.push(actions);
	}
	config.cm = new Ext.grid.ColumnModel(cols);
		
	config.plugins = [actions];
	config.clicksToEdit = 1;
	config.loadMask=true;
	
	var store = this.store;

	config.view = new Ext.grid.GroupingView({
		hideGroupedColumn:true,
		enableRowBody: true,
		showPreview: true,
		showGroupName: false,
//		autoFill: true,
		startCollapsed:true,
		emptyText: t("No items to display"),
//		groupTextTpl: '{text}<tpl if="values.rs[0].data.buyEnabled"><div class="mo-buy">Buy licenses</div></tpl>',
//		processEvent: function(name, e){
//			
//			
//        Ext.grid.GroupingView.superclass.processEvent.call(this, name, e);
//				
//				var buyLink = Ext.get(e.getTarget('.mo-buy', this.mainBody));
//				if(buyLink){
//					
//					if(name == 'mousedown' && e.button == 0){
//						var group = buyLink.parent('.x-grid-group');
//						var row = group.query('.x-grid3-row');					
//						var rowIndex = this.findRowIndex(row[0]);
//						var record = store.getAt(rowIndex);
//
//						GO.modules.showBuyDialog(record);
//					}
//				}else
//				{
//				
//					var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
//					if(hd){
//							// group value is at the end of the string
//							var field = this.getGroupField(),
//									prefix = this.getPrefix(field),
//									groupValue = hd.id.substring(prefix.length),
//									emptyRe = new RegExp('gp-' + Ext.escapeRe(field) + '--hd');
//
//							// remove trailing '-hd'
//							groupValue = groupValue.substr(0, groupValue.length - 3);
//
//							// also need to check for empty groups
//							if(groupValue || emptyRe.test(hd.id)){
//									this.grid.fireEvent('group' + name, this.grid, field, groupValue, e);
//							}
//							if(name == 'mousedown' && e.button == 0){
//									this.toggleGroup(hd.parentNode);
//							}
//					}
//				}
//
//    },
		getRowClass: function(record, rowIndex, p, store) {
			if (this.showPreview && record.data.description.length) {
				p.body = record.data.description;
				return 'x-grid3-row-expanded';
			}
			return 'x-grid3-row-collapsed';
		}
	});


//	config.ddGroup = 'ModulesGridDD';
//
//	config.enableDragDrop = true;

	config.autoExpandColumn='name';
	config.layout = 'fit';
	config.sm = new Ext.grid.RowSelectionModel({
		singleSelect: false
	});
	config.paging = false;

	GO.modules.MainPanel.superclass.constructor.call(this, config);

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
	
	

};

Ext.extend(GO.modules.MainPanel,Ext.grid.EditorGridPanel, {
	
	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,

			actions: [{
					iconCls: 'ic-delete',
					qtip: t("Delete")
				}]
		});

		actions.on({
			scope: this,
			action: function (grid, record, action, row, col, e, target) {
				
				if(!record.data.id) {
					return;
				}
				
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
			}
		});

		return actions;

	},
	

	afterRender: function() {

		GO.modules.MainPanel.superclass.afterRender.call(this);
		
		
//
//		var notifyDrop = function(dd, e, data) {
//			var sm = this.getSelectionModel();
//			var rows = sm.getSelections();
//			var cindex = dd.getDragData(e).rowIndex;
//			if (cindex == 'undefined') {
//				cindex = this.store.data.length - 1;
//			}
//			for (var i = 0; i < rows.length; i++) {
//				var rowData = this.store.getById(rows[i].id);
//
//				if (!this.copy) {
//					this.store.remove(this.store.getById(rows[i].id));
//				}
//
//				this.store.insert(cindex, rowData);
//			}
//			;
//
//			this.save();
//
//		};
//
//		var ddrow = new Ext.dd.DropTarget(this.getView().mainBody, {
//			ddGroup: 'ModulesGridDD',
//			copy: false,
//			notifyDrop: notifyDrop.createDelegate(this)
//		});

		this.store.load();

	},
//	save: function() {
//		var modules = new Array();
//
//		for (var i = 0; i < this.store.data.items.length; i++) {
//			modules[i] = {
//				id: this.store.data.items[i].get('id'),
//				sort_order: i,
//				admin_menu: this.store.data.items[i].get('admin_menu')
//			};
//		}
//
//		GO.request({
//			maskEl: this.container,
//			url: 'modules/module/saveSortOrder',
//			params: {
//				modules: Ext.encode(modules)
//			},
//			scope: this
//		});
//	},
//	uninstallModule : function() {
//
//		var uninstallModules = Ext.encode(this.selModel.selections.keys);
//
//		switch (this.selModel.selections.keys.length) {
//			case 0 :
//				Ext.MessageBox.alert(t("Error"),
//					t("You didn't select an item."));
//				return false;
//				break;
//
//			case 1 :
//				var strConfirm = t("Are you sure you want to delete the selected item?");
//				break;
//
//			default :
//				var t = new Ext.Template(t("Are you sure you want to delete the {count} items?"));
//				var strConfirm = t.applyTemplate({
//					'count' : this.selModel.selections.keys.length
//				});
//				break;
//		}
//
//		Ext.MessageBox.confirm(t("Confirm"), strConfirm,
//			function(btn) {
//				if (btn == 'yes') {
//					this.store.baseParams.uninstall_modules = uninstallModules;
//
//					this.store.reload({
//						callback : function() {
//							if (!this.store.reader.jsonData.uninstallSuccess) {
//								Ext.MessageBox
//								.alert(
//									t("Error"),
//									this.store.reader.jsonData.uninstallFeedback);
//							}
//
//							this.store.reload();
//						},
//						scope : this
//					});
//
//					delete this.store.baseParams.uninstall_modules;
//				}
//			}, this);
//
//	},

	showPermissions: function(moduleId, name, acl_id) {
		if (!this.permissionsWin) {
			this.permissionsWin = new GO.modules.ModulePermissionsWindow();
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
	iconRenderer: function(name, cell, record) {
		return '<div class="mo-title" style="background-image:url(' + record.data["icon"] + ')">'
						+ record.data.localizedName +'</div>';
	},
	warningRenderer: function(name, cell, record) {
		return record.data.warning != '' ?
						'<div class="go-icon go-warning-msg" ext:qtip="' + Ext.util.Format.htmlEncode(record.data.warning) + '"></div>' : '';
	},
	buyRenderer: function(name, cell, record) {
		return record.data.buyEnabled ? '<a  class="normal-link" onclick="GO.modules.showBuyDialog(\'' + record.data.id + '\');">'+t("Buy licenses", "modules")+'</a>' : '';
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
				var responseParams = Ext.decode(response.responseText);

				if (!responseParams.success) {
					GO.errorDialog.show(responseParams.feedback);
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
		
		if(record.data.id) {
			params.update = {};
			params.update[record.data.id] = {
				enabled: record.data.enabled,
				sort_order: record.data.sort_order
			};
			go.Stores.get("Module").set(params, function(options, success, response) {

				if(record.data.enabled && record.isModified("enabled")) {
					//record.set('aclId', response['created'][record.data.id].aclId);
					this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);
					this.store.load();				
				}

				record.commit();

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
					record.set('enabled', true);										
					record.set('id', response['list'][0].id);
					record.set('aclId', response['list'][0].aclId);
					this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);					
					record.commit();
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
	}

});


GO.moduleManager.deleteModule = function(moduleId, name){
	
	var modulePanel = GO.mainLayout.getModulePanel("modules");
	
	Ext.MessageBox.confirm(GO.modules.lang.cmdUninstall,GO.modules.lang.cmdUninstallMessage.replace('{0}',name), function(clickedBtn){
		if(clickedBtn === 'yes'){			
			GO.request({
				maskEl:modulePanel.getEl(),
				url: 'modules/module/delete',
				params: {
					id: moduleId
				},
				scope: this,
				success: function(response, options, result) {
					Ext.Msg.alert(GO.modules.lang.cmdUninstall, GO.modules.lang.cmdUninstallMessageSuccess.replace('{0}',name));
					modulePanel.getStore().load();
				}
			});
		}
	},this);
};

go.Modules.register('core', 'modules' ,{
  mainPanel: GO.modules.MainPanel,
  title: t("Modules", "modules"),
	iconCls: 'go-tab-icon-modules',
	admin: true,
  entities: ["Module"]
});
