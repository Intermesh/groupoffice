/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: modules.js 18445 2014-11-11 09:58:34Z mschering $
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
			fields: ['name', 'package', 'description', 'id', 'sort_order', 'admin_menu', 'acl_id', 'icon', 'enabled', 'warning', 'buyEnabled','not_installable'],
			id: 'id'
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
		cls: 'go-head-tb',
		items: [
			{
				xtype: 'htmlcomponent',
				html: GO.modules.lang.name,
				cls: 'go-module-title-tbar'
			}, {
				iconCls: 'btn-refresh',
				text: GO.lang.cmdRefresh,
				cls: 'x-btn-text-icon',
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
				text: GO.modules.lang.buyLicenses,
				cls: 'x-btn-text-icon',
				hidden: GO.settings.config.product_name != 'Group-Office',
				handler: function() {				
					window.open('https://www.group-office.com/shop/');					
				},
				scope: this
			},


			this.trialButton = new Ext.Button({
				iconCls: 'btn-settings',
				text: GO.modules.lang.trialLicense,
				cls: 'x-btn-text-icon',
				hidden:true,
				handler: function() {
					Ext.MessageBox.confirm(
						GO.modules.lang.trialLicense,
						GO.modules.lang.trialLicenseText,
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
		header: GO.modules.lang.enabled,
		dataIndex: 'enabled',
		width: 100,
		disabled_field:'not_installable',
		listeners: {
			scope: this,
			change: function(record, checked) {
				GO.request({
					maskEl:this.getEl(),
					url: 'modules/module/update',
					params: {
						id: record.id,
						enabled: checked
					},
					scope: this,
					success: function(response, options, result) {

						if (result.acl_id) {
							record.set('acl_id', result.acl_id);

							if (record.data.enabled) {
								this.showPermissions(record.data.id, record.data.name, record.data.acl_id);
								this.store.load();
							}
						}
						record.commit();
					}
				});
			}
		}
	});

	config.cm = new Ext.grid.ColumnModel([
		{
			header: GO.lang['strName'],
			dataIndex: 'name',
			id: 'name',
			renderer: this.iconRenderer
		}, 
		checkColumn,
		{
			header: GO.modules.lang.sort_order,
			dataIndex: 'sort_order',
			sortable:true,
			id: 'sort_order',
			editor: new GO.form.NumberField({
				allowBlank: false,
				decimals:0
			})
		},
		{
			header: "Package",
			dataIndex: 'package',
			id: 'package'
			
		}
	]);
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
		emptyText: GO.lang.strNoItems,
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
				p.body = '<div class="mo-description">' + record.data.description + '</div>';
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

		if (moduleRecord.data.acl_id) {
			this.showPermissions(moduleRecord.data.id, moduleRecord.data.name, moduleRecord.data.acl_id);
		}
	}, this);

};

Ext.extend(GO.modules.MainPanel,Ext.grid.EditorGridPanel, {
	

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
//				Ext.MessageBox.alert(GO.lang['strError'],
//					GO.lang['noItemSelected']);
//				return false;
//				break;
//
//			case 1 :
//				var strConfirm = GO.lang['strDeleteSelectedItem'];
//				break;
//
//			default :
//				var t = new Ext.Template(GO.lang['strDeleteSelectedItems']);
//				var strConfirm = t.applyTemplate({
//					'count' : this.selModel.selections.keys.length
//				});
//				break;
//		}
//
//		Ext.MessageBox.confirm(GO.lang['strConfirm'], strConfirm,
//			function(btn) {
//				if (btn == 'yes') {
//					this.store.baseParams.uninstall_modules = uninstallModules;
//
//					this.store.reload({
//						callback : function() {
//							if (!this.store.reader.jsonData.uninstallSuccess) {
//								Ext.MessageBox
//								.alert(
//									GO.lang['strError'],
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
						+ name + '</div>';
	},
	warningRenderer: function(name, cell, record) {
		return record.data.warning != '' ?
						'<div class="go-icon go-warning-msg" ext:qtip="' + Ext.util.Format.htmlEncode(record.data.warning) + '"></div>' : '';
	},
	buyRenderer: function(name, cell, record) {
		return record.data.buyEnabled ? '<a href="#" class="normal-link" onclick="GO.modules.showBuyDialog(\'' + record.data.id + '\');">'+GO.modules.lang.buyLicenses+'</a>' : '';
	},

	/**
	 * Submit the record
	 * 
	 * @param array record
	 * @returns {undefined}
	 */
	submitRecord : function(record){
		var url = GO.url('modules/module/updateModuleModel');

		Ext.Ajax.request({
			method:'POST',
			url: url,
			params : {
				id:record.data.id
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


GO.moduleManager.addModule('modules', GO.modules.MainPanel, {
	title: GO.modules.lang.modules,
	iconCls: 'go-tab-icon-modules',
	admin: true
});