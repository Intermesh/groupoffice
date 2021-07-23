go.modules.SystemSettingsModuleGrid = Ext.extend(Ext.Panel, {
	iconCls: 'ic-extension',
	autoExpandColumn: 'name',
	//layout: 'fit',
	paging: false,
	clicksToEdit: 1,
	loadMask: true,
	itemId: "modules", //makes it routable
	cls: 'go-modules',
	// bodyCssClass: 'x-view-tiles go-module-tile',
	title: t('Modules'),

	autoScroll:true,

	emptyText: '<div class="empty-state"><i class="icon">cloud_upload</i><br>'+
		'<h3>'+t('No modules available')+'</h3>'+
		'<small>'+t('Check your installation folder and try running a database check')+'</small></div>',

	initComponent: function() {


		this.store = new GO.data.JsonStore({
			url: GO.url("modules/module/store"),
			fields:['name', 'package', 'localizedPackage', 'localizedName',  'description', 'id', 'sort_order', 'admin_menu', 'aclId', 'icon', 'enabled', 'warning','author', 'buyEnabled','not_installable', 'isRefactored','installed'],
			remoteSort: false,
			idProperty: 'name'

		});

		this.store.on('beforeload', () => {
			this.getEl().mask(t("Loading..."));
		});

		this.store.on('load', () => {

			this.store.multiSort([
				{field: 'localizedPackage', direction: 'ASC'},
				{field: 'name', direction: 'ASC'}
			]);

			this.getEl().unmask();
		});
		this.store.on('exception', () => {
			this.getEl().unmask();
		});

		this.tbar = [{
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
		},this.trialButton = new Ext.Button({
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
		}),' ',{
			xtype:'buttongroup',
			items: [{
				xtype:'button',
				text:t('All'),
				pressed:true,
				toggleGroup:'installedFilter',
				handler: function() {
					delete this.store.baseParams.isInstalled;
					this.store.load();
				},scope:this
			},{
				xtype:'button',
				text:t('Installed'),
				toggleGroup:'installedFilter',
				handler: function() {
					this.store.baseParams.isInstalled = true;
					this.store.load();
				},scope:this
			},{
				xtype:'button',
				text:t('Available'),
				toggleGroup:'installedFilter',
				handler: function() {
					this.store.baseParams.isInstalled = false;
					this.store.load();
				},scope:this
			}]
		},'->',{
			xtype:'tbsearch',
			store: this.store,
			onSearch: function(v) {
				this.store.baseParams['query'] = v;
				this.store.load();
			}
		}];

		this.store.on('update', this.draw,this);
		this.store.on('datachanged', this.draw,this);

		this.on('afterrender', function() {
			this.store.load();
		},this);

		go.modules.SystemSettingsModuleGrid.superclass.initComponent.call(this);
	},

	draw: function(store) {

		this.trialButton.setVisible(!store.reader.jsonData.has_license);

		this.removeAll();

		let lastPackage = null;

		store.each(function(r){
			var hasEditPermission = r.get("permissionLevel") < go.permissionLevels.manage;
			var isInstalled = r.data.id != null;

			if(r.data.localizedPackage != lastPackage) {
				this.add({
					xtype: "box",
					autoEl: "h3",
					html: r.data.localizedPackage
				});
				lastPackage = r.data.localizedPackage;

				this.packageContainer = this.add({
					xtype: 'container',
					cls: 'x-view-tiles go-module-tile'
				})
			}

			this.packageContainer.add({
				xtype:'container',
				cls: 'tile',
				listeners: {
					render: function(c) {
						var author = r.data.author ? '<br><br>'+t('Author')+': '+r.data.author : '';
						// new Ext.ToolTip({
						//     target: c.getEl(),
						//     anchor: 'left',
						//     html: r.data.description + author
						// });
					}
				},
				items:[
				// 	{
				// 	xtype:'box',
				// 	cls: 'corner',
				// 	autoEl:{tag: 'span'},
				// 	html: r.data.localizedPackage
				// },{
				// 	xtype:'numberfield',
				// 	allowBlank: false,
				// 	disabled: !isInstalled,
				// 	decimals:0,
				// 	fieldLabel: t('Sort order'),
				// 	value: r.data.sort_order,
				// 	record: r,
				// 	listeners:{
				// 		change: function(btn, newValue) {
				// 			btn.record.set("sort_order", newValue);
				// 			this.submitRecord(btn.record);
				// 		},
				// 		scope:this
				// 	}
				// },
					{
						xtype:'box',
						cls: 'thumb',
						style: 'background-image:url(' + go.Jmap.downloadUrl('core/moduleIcon/'+(r.data.package || "legacy")+'/'+r.data.name) + '&mtime='+go.User.session.cacheClearedAt+')',
					}, {
						xtype: 'box',
						cls: 'text',
						style:'font-weight:bold;',
						html: r.data.localizedName
					}, {
						xtype: 'box',
						cls: 'text',
						style: 'font-size:12px;',
						html: r.data.description,
					},{
						xtype:'toolbar',
						items:[{
							xtype:'button',
							cls: isInstalled ? 'primary' : '',
							isInstalled : isInstalled,
							text: r.data.enabled ? t('Disable') : (isInstalled ? t('Enable') : t('Install')),
							disabled: r.data.not_installable,
							record: r,
							handler: function(btn) {
								btn.record.set("enabled", !btn.record.data.enabled);
								this.enableModule(btn.record);
							},
							scope:this
						}, '->', {
							xtype:'button',
							iconCls: 'ic-share',
							disabled: !isInstalled,
							record: r,
							handler: function(btn) {
								var record = btn.record;
								this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);
							},
							scope: this
						}, {
							xtype:'button',
							iconCls: 'ic-delete',
							disabled: !isInstalled,
							record: r,
							handler: function(btn) {this.deleteModule(btn.record);},
							scope:this
						}]
					}
				]
			});
		},this);

		if(store.getTotalCount() < 1) {
			this.add({
				xtype: 'box', html: '<div class="empty-state"><i class="icon">cloud_upload</i><br>' +
					'<h3>' + t('No modules available') + '</h3>' +
					'<small>' + t('Check your installation folder and try running a database check') + '</small></div>'
			});
		}
		this.doLayout();

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

	enableModule: function(record) {

		if(record.data.isRefactored) {
			return this.submitJmap(record);
		}

		GO.request({
			maskEl:this.getEl(),
			url: 'modules/module/update',
			params: {
				id: record.data.name,
				enabled: record.data.enabled
			},
			scope: this,
			success: function(response, options, result) {

				if (result.aclId) {
					record.set('aclId', result.aclId);
					record.set('id', result.id);
					record.set("enabled", record.data.enabled);
					if (record.data.enabled) {
						this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);
						//this.store.load();
					}
				}
				record.commit();
			}
		});
	},

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
				sort_order: record.data.sort_order ? record.data.sort_order : 0
			};
			go.Db.store("Module").set(params, function(options, success, response) {

				if(success){
					if(record.data.enabled && record.isModified("enabled")) {
						// record.set('aclId', response['created'][record.data.id].aclId);
						this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);
						this.store.load();
					}
					record.commit();
				} else
				{
					Ext.MessageBox.alert(t("Error"), response.message);
					this.store.load();
				}

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
					if(success) {
						record.set('enabled', true);
						record.set('id', response['list'][0].id);
						record.set('aclId', response['list'][0].aclId);
						this.showPermissions(record.data.name, t(record.data.name, record.data.name), record.data.aclId);
						record.commit();
					} else
					{
						Ext.MessageBox.alert(t("Error"), response.message);
						this.store.load();
					}
				},
				scope: this
			});

		}


	},

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

	deleteModule: function(record) {
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