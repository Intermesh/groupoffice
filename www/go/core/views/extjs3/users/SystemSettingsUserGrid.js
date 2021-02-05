
/* global Ext, go, BaseHref, GO */

/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MainPanel.js 19225 2015-06-22 15:07:34Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

go.users.SystemSettingsUserGrid = Ext.extend(go.grid.GridPanel, {
	iconCls: 'ic-account-box',

	initColumns: function(fields, columns) {
		return {fields:fields, columns:columns};
	},

	initComponent: function () {
		
		this.title = t("Users");



		var cols = this.initColumns([
			'id',
			'username',
			'displayName',
			'avatarId',
			'loginCount',
			'authenticationMethods',
			'personalGroup',
			'enabled',
			{name: 'createdAt', type: 'date'},
			{name: 'modifiedAt', type: 'date'},
			{name: 'lastLogin', type: 'date'}
		], [{
				id: 'name',
				header: t('Name'),
				width: dp(200),
				sortable: true,
				dataIndex: 'displayName',
				renderer: function (value, metaData, record, rowIndex, colIndex, store) {
					return '<div class="user">' + go.util.avatar(value, record.data.avatarId)  +
						'<div class="wrap">'+
						'<div class="displayName">' + value + '</div>' +
						'<small class="username">' + Ext.util.Format.htmlDecode(record.get('username')) + '</small>' +
						'</div>'+
						'</div>';
				}
			},
			{
				xtype:"datecolumn",
				id: 'createdAt',
				header: t('Created at'),
				width: dp(160),
				sortable: true,
				dataIndex: 'createdAt',
				hidden: false
			},
			{
				xtype:"datecolumn",
				id: 'modifiedAt',
				header: t('Modified at'),
				width: dp(160),
				sortable: true,
				dataIndex: 'createdAt',
				hidden: true
			},
			{
				xtype:"datecolumn",
				id: 'lastLogin',
				header: t('Last login'),
				width: dp(160),
				sortable: true,
				dataIndex: 'lastLogin',
				hidden: false
			},{
				id: 'loginCount',
				align: "right",
				header: t('Logins'),
				width: dp(100),
				sortable: true,
				dataIndex: 'loginCount',
				hidden: false
			},{
				header: t('Authentication'),
				width: dp(100),
				sortable: false,
				renderer: function(v) {
					var result = '';

					for(var i = 0, method; method = v[i]; i++) {
						result += '<i title="'+method.name+'" class="icon go-module-icon-'+method.id+'"></i> ';
					}
					return result;
				},
				dataIndex: 'authenticationMethods'
			},{
				header: "ID",
				width: dp(100),
				hidden: true,
				dataIndex: 'id',
				sortable: true
			}]);


		this.store = new go.data.Store({
			fields: cols.fields,
			entityStore: "User"
		});

		Ext.apply(this, {
			tbar: [{
				iconCls: 'ic-people-outline',
				text: t('Show disabled'),
				enableToggle:true,
				toggleHandler: function(btn, state) {

					this.store.setFilter('disabled', state ? {showDisabled: true} : null);
					this.store.load();
				},
				scope:this
			}, '->', {
				xtype: 'tbsearch',
				filters: [
					'text'
				]
			},{
				iconCls: 'ic-add',
				tooltip: t('Add'),
				handler: function (e, toolEl) {
					var dlg = new go.users.CreateUserWizard();
					dlg.show();
				}
			},{
				iconCls: 'ic-more-vert',
				menu: [
					{
						iconCls: 'ic-settings',
						text: t("User defaults"),
						handler: function() {
							var dlg = new go.users.UserDefaultsWindow();
							dlg.show();
						}
					},'-', {
						iconCls: 'ic-cloud-upload',
						text: t("Import"),
						handler: function() {
							go.util.importFile(
											'User',
											".csv",
											{},
											{
												labels: {
													username: t("Username"),
													displayName: t("Display name"),
													password: t("Password"),
													email: t("E-mail"),
													recoveryEmail: t("Recovery e-mail"),
													groups: t("Groups")
												}
											});
						},
						scope: this
					}, {
						iconCls: 'ic-cloud-download',
						text: t("Export"),
						menu: [
							{
								text: 'Microsoft Excel',
								iconCls: 'filetype filetype-xls',
								handler: function() {
									go.util.exportToFile(
										'User',
										Object.assign(go.util.clone(this.store.baseParams), this.store.lastOptions.params, {limit: 0, position: 0}),
										'xlsx');
								},
								scope: this
							},{
								text: 'Comma Separated Values',
								iconCls: 'filetype filetype-csv',
								handler: function() {
									go.util.exportToFile(
										'User',
										Object.assign(go.util.clone(this.store.baseParams), this.store.lastOptions.params, {limit: 0, position: 0}),
										'csv');
								},
								scope: this
							},
							{
								iconCls: 'filetype filetype-json',
								text: 'JSON',
								handler: function() {
									go.util.exportToFile(
										'User',
										Ext.apply(this.store.baseParams, this.store.lastOptions.params, {limit: 0, start: 0}),
										'json');
								},
								scope: this
							}
						],
						scope: this
					}
				]
			}],
			columns: cols.columns,
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				totalDisplay: true,
				actionConfig: {
					scope: this,
					menu: this.initMoreMenu()
				},
				getRowClass: function(record) {
					if(!record.json.enabled)
						return 'go-user-disabled';
				}
			},
			// config options for stateful behavior
			stateful: true,
			stateId: 'users-grid'
		});

		go.users.SystemSettingsUserGrid.superclass.initComponent.call(this);
		
		this.on('viewready', function() {
			this.store.load();
		}, this);
		
		this.on('rowdblclick', function(grid, rowIndex, e) {
			this.edit(this.store.getAt(rowIndex).id);
		}); 
	},

	initMoreMenu : function() {
		this.moreMenu = new Ext.menu.Menu({
			items: [
				{
					itemId: "view",
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function(item) {
						var record = this.store.getAt(item.parentMenu.rowIndex);
						this.edit(record.id);
					},
					scope: this
				},{
					itemId:"loginAs",
					iconCls: 'ic-swap-horiz',
					text: t("Login as this user"),
					handler: function(item) {
						var record = this.store.getAt(item.parentMenu.rowIndex);
						//Drop local data
						go.browserStorage.deleteDatabase().then(function() {

							go.Jmap.request({
								method: "User/loginAs",
								params: {userId: record.id},
								callback: function(options, success, result) {
									if(!result.success) {
										Ext.MessageBox.alert(t("Error"), t("Failed to login as this user"));
										return;
									}

									//reload client
									document.location = BaseHref;
								}
							});
						});
					},
					scope: this
				},
				"-"
				,{
					itemId: "archive",
					iconCls: "ic-archive",
					text: t("Archive user"),
					handler: function(item) {

						var record = this.store.getAt(item.parentMenu.rowIndex);

						Ext.MessageBox.confirm(
							t("Confirm"),
							t("Archiving a user will disable them and make their items invisible. Are you sure?"),
							function(btn) {
								if(btn !== 'yes') {
									return;
								}
								var id = record.id, params = {};

								if(id === go.User.id) {
									Ext.MessageBox.alert(t('Error'), t('You can\' t archive yourself'));
									return;
								}
								params.update = {};
								params.update[id] = {'enabled': false, 'archive': true};

								go.Db.store("User").set(params, function(options, success, response) {
									if (response.notUpdated && response.notUpdated[id] && response.notUpdated[id].validationErrors && response.notUpdated[id].validationErrors.currentPassword) {
										Ext.MessageBox.alert(t('Error'), t('Error while saving the data'));
										return;
									}
								});
							});
					},
					scope: this
				}
				,{
					itemId:"delete",
					iconCls: 'ic-delete',
					text: t("Delete"),
					handler: function(item) {
						var record = this.store.getAt(item.parentMenu.rowIndex);

						this.getSelectionModel().selectRecords([record]);
						this.deleteSelected();
					},
					scope: this
				}
			],
			listeners: {
				scope: this,
				show: function(menu) {

					var record = this.store.getAt(menu.rowIndex);

					var archiveItm  = menu.find('itemId','archive'), loginItm = menu.find('itemId', 'loginAs');
					if(archiveItm.length > 0) {
						archiveItm[0].setDisabled(!record.data.enabled);
					}
					if(loginItm.length > 0 ) {
						loginItm[0].setDisabled(!record.data.enabled);
					}
				}
			}
		});

		return this.moreMenu
	},

	edit : function(id) {
		var dlg = new go.usersettings.UserSettingsDialog();
		dlg.load(id).show();
	}
	
});

