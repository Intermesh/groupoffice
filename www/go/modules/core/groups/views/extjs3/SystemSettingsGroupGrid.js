
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

go.modules.core.groups.SystemSettingsGroupGrid = Ext.extend(go.grid.GridPanel, {
	iconCls: 'ic-group',
	initComponent: function () {

		var actions = this.initRowActions();

		this.title = t("Groups");

		this.store = new go.data.Store({
			baseParams: {
				filter: {
					excludeEveryone: true,
					hideUsers: true
				}
			},
			fields: [
				'id',
				'name',
				'isUserGroupFor',
				'users',
				'memberCount'
			],
			entityStore: go.Stores.get("Group")
		});

		this.store.on('load', this.onStoreLoad, this);

		Ext.apply(this, {
			plugins: [actions],
			tbar: ['->',
				{
					xtype: 'tbsearch'
				}, {
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.core.groups.GroupDialog();
						dlg.show();
					}
				}, {
					iconCls: 'ic-settings',
					tooltip: t("Group defaults"),
					handler: function() {
						var dlg = new go.modules.core.groups.GroupDefaultsWindow();
						dlg.show();
					}
				}

			],
			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'displayName',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						
						var memberStr = t("Loading members..."),
										members = record.get('users').column('userId'),
										users = go.Stores.get('User').get(members),
										max = 5;
						
						if(users) {
							memberStr = "";
							users.slice(0, max).forEach(function (user) {
								if (memberStr != "") {
									memberStr += ", "
								}
								memberStr += user.displayName;
							});
						}

						var more = members.length - max;
						if (more > 0) {
							memberStr += t(" and {count} more").replace('{count}', more);
						}
						

						return '<div class="user"><div class="avatar group"></div>' +
										'<div class="wrap">' +
										'<div class="displayName">' + record.get('name') + '</div>' +
										'<small class="username">' + memberStr + '</small>' +
										'</div>' +
										'</div>';
					}
				},
				actions
			],
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			}
			// config options for stateful behavior
//			stateful: true,
//			stateId: 'groups-grid'
		});

		go.modules.core.groups.SystemSettingsGroupGrid.superclass.initComponent.call(this);

		this.on('render', function () {
			this.store.load();
		}, this);

		this.on('rowdblclick', function (grid, rowIndex, e) {
			this.edit(this.store.getAt(rowIndex).id);
		});
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
						itemId: "view",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {
							this.edit(this.moreMenu.record.id);
						},
						scope: this
					},
					"-"
									, {
										itemId: "delete",
										iconCls: 'ic-delete',
										text: t("Delete"),
										handler: function () {
											this.getSelectionModel().selectRecords([this.moreMenu.record]);
											this.deleteSelected();
										},
										scope: this
									},
				]
			})
		}

		this.moreMenu.record = record;

		this.moreMenu.showAt(e.getXY());
	},

	edit: function (id) {

		var dlg = new go.modules.core.groups.GroupDialog();
		dlg.load(id).show();

	},

	onStoreLoad: function () {

		var records = this.store.getRange(), me = this, count = 0;
		var memberIds = [];
		
		records.forEach(function(record) {
			memberIds = memberIds.concat(record.data.users.column("userId"));			
		});
		
		var unique = memberIds.filter(function(item, i, ar){ return ar.indexOf(item) === i; });
						
		go.Stores.get('User').get(unique, function(entities, async) {	
			//all data is fetched now. Refresh grid ui.	
			if(async && me.rendered) {
				me.getView().refresh();														
			}
		});

	}

});

