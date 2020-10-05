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

go.modules.community.multi_instance.MainPanel = Ext.extend(go.grid.GridPanel, {

	initComponent: function () {

		var actions = this.initRowActions();

		this.store = new go.data.Store({
			fields: [
				'id',
				'hostname',
				'userCount',
				{name: 'createdAt', type: 'date'},
				{name: 'lastLogin', type: 'date'},
				'adminDisplayName',
				'adminEmail',
				'enabled',
				'loginCount',
				'usersMax',
				'version',
				'isTrial',
				{name: 'storageUsage', type: "int"},
				{name: 'storageQuota', type: "int"}
			],
			entityStore: "Instance",
			filters: {
				enabled: {enabled: true},
				isTrial: {}
			}
		});

		Ext.apply(this, {
			plugins: [actions],
			tbar: [{
					iconCls: 'ic-block',
					text: t('Show disabled'),
					enableToggle: true,
					toggleHandler: function (btn, state) {
						this.store.setFilter('enabled', state ? {} : {enabled: true});
						this.store.load();
					},
					scope: this
				}, {
					iconCls: 'ic-star',
					text: t('Show trials'),
					enableToggle: true,
					pressed: true,
					toggleHandler: function (btn, state) {
						this.store.setFilter('isTrial', state ? {} : {isTrial: false});
						this.store.load();
					},
					scope: this
				},

				'->',

				{
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.community.multi_instance.InstanceDialog();
						dlg.show();
					}
				}, {
					xtype: "tbsearch"
				}, {
					iconCls: 'ic-more-vert',
					menu: [{
							iconCls: 'ic-email',
							text: t("E-mail selected"),
							handler: function () {
								var records = this.getSelectionModel().getSelections();

								var str = "";
								Ext.each(records, function (r) {
									if (r.data.adminEmail && str.indexOf(r.data.adminEmail) == -1) {
										str += '"' + r.data.adminDisplayName.replace(/"/g, '\\"') + '" &lt;' + r.data.adminEmail + '&gt;, ';
									}
								});

								Ext.MessageBox.alert("E-mail addresses", str);
							},
							scope: this
						},{
							iconCls: 'ic-download',
							text: t("Download site config"),
							handler: function () {
								window.open(go.Jmap.downloadUrl('community/multi_instance/siteConfig'));
							},
							scope: this
						}]
				}

			],
			columns: [
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: 40,
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'hostname',
					header: t('Hostname'),
					width: 200,
					sortable: true,
					dataIndex: 'hostname',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						if (!record.data.enabled) {
							metaData.css = "deactivated";
						}

						return value;
					}
				},
				{
					id: 'isTrial',
					header: t('Trial'),
					width: dp(60),
					sortable: true,
					dataIndex: 'isTrial',
					renderer: go.grid.ColumnRenderers.check
				},
				{
					xtype: "datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: 160,
					sortable: true,
					dataIndex: 'createdAt',
					hidden: false
				},
				{
					xtype: "datecolumn",
					id: 'lastLogin',
					header: t('Last login'),
					width: 160,
					sortable: true,
					dataIndex: 'lastLogin',
					hidden: false
				}, {
					id: 'userCount',
					header: t('User count'),
					width: 160,
					sortable: true,
					dataIndex: 'userCount',
					hidden: false,
					align: "right"
				}, {
					id: 'loginMax',
					header: t('Maximum users'),
					width: 160,
					sortable: true,
					dataIndex: 'usersMax',
					hidden: false,
					align: "right"
				}, {
					id: 'loginCount',
					header: t('Login count'),
					width: 160,
					sortable: true,
					dataIndex: 'loginCount',
					hidden: false,
					align: "right"
				}, {
					header: t('Admin name'),
					width: 160,
					sortable: true,
					dataIndex: 'adminDisplayName'
				}, {
					header: t('Admin E-mail'),
					width: 160,
					sortable: true,
					dataIndex: 'adminEmail'
				}, {
					header: t('Storage quota'),
					width: 160,
					sortable: true,
					dataIndex: 'storageQuota',
					type: "int",
					renderer: GO.util.format.fileSize,
					align: "right"
				}, {
					header: t('Storage usage'),
					width: 160,
					sortable: true,
					dataIndex: 'storageUsage',
					renderer: GO.util.format.fileSize,
					align: "right"
				}, {
					header: t('Version'),
					width: 160,
					sortable: true,
					dataIndex: 'version'
				},
				actions

			],
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true,
				totalDisplay: true
			},
			autoExpandColumn: 'hostname',
			// config options for stateful behavior
			stateful: true,
			stateId: 'multi_instance-grid'
		});

		go.modules.community.multi_instance.MainPanel.superclass.initComponent.call(this);

		this.on('render', function () {
			this.store.load();
		}, this);

		this.on("rowdblclick", function (grid, rowIndex, e) {
			this.edit(grid.store.getAt(rowIndex).data.id);
		}, this);
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
			actions: [{
					iconCls: 'ic-more-vert'
				}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				this.showMoreMenu(record, e);
			},
			scope: this
		});

		return actions;

	},

	showMoreMenu: function (record, e) {
		if (!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId: "login",
						iconCls: 'ic-lock-open',
						text: t("Login as administrator"),
						handler: function () {

							var win = window.open("about:blank", "groupoffice_instance");
							go.Jmap.request({
								method: "community/multi_instance/Instance/login",
								params: {
									id: this.moreMenu.record.get('id')
								},
								callback: function (options, success, result) {

									//POST access token to popup for enhanced security
									var f = document.createElement("form");
									f.setAttribute('method', "post");
									f.setAttribute('target', "groupoffice_instance");
									f.setAttribute('action', document.location.protocol + "//" + this.moreMenu.record.get('hostname') + ':' + document.location.port);

									var i = document.createElement("input"); //input element, text
									i.setAttribute('type', "hidden");
									i.setAttribute('name', "accessToken");
									i.setAttribute('value', result.accessToken);
									f.appendChild(i);

									var body = document.getElementsByTagName('body')[0];
									body.appendChild(f);
									f.submit();
									body.removeChild(f);

								},
								scope: this
							});
						},
						scope: this
					}, '-',
					{
						itemId: "deactivate",
						iconCls: 'ic-block',
						text: t("Deactivate"),
						handler: function () {

							var update = {};
							update[this.moreMenu.record.id] = {enabled: !this.moreMenu.record.data.enabled};

							go.Db.store("Instance").set({
								update: update
							});

						},
						scope: this
					}, {
						itemId: "edit",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {

							this.edit(this.moreMenu.record.data.id);

						},
						scope: this
					}, {
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							this.getSelectionModel().selectRecords([this.moreMenu.record]);
							this.deleteSelected();
						},
						scope: this
					}

				]
			});
		}

		this.moreMenu.record = record;
		this.moreMenu.items.item("deactivate").setText(record.data.enabled ? t("Deactivate instance") : t("Activate instance"));
		this.moreMenu.showAt(e.getXY());
	},

	edit: function (instanceId) {
		var dlg = new go.modules.community.multi_instance.InstanceDialog();
		dlg.load(instanceId).show();
	}
});

