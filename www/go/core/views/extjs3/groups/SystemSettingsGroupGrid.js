
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

go.groups.SystemSettingsGroupGrid = Ext.extend(go.grid.GridPanel, {
	hasPermission: function() {
		const module = go.Modules.get(this.package, this.module);
		return module.userRights.mayChangeGroups;
	},
	iconCls: 'ic-group',
	itemId: "groups", //makes it routable
	initComponent: function () {
		this.title = t("Groups");
		this.store = new go.data.Store({
			filters: {
				hideUsers: {
					//excludeEveryone: true,
					hideUsers: true
				}
			},
			fields: [
				'id',
				'name',
				'isUserGroupFor',
				'aclId',
				{name: 'users', type: "relation", limit: 5}
				
			],
			entityStore: "Group"
		});

		Ext.apply(this, {
			tbar: ['->',
				{
					xtype: 'tbsearch',
					filters: [
						'text'					
					]
				}, {
					disabled: !go.Modules.get("core", "core").userRights.mayChangeGroups,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.groups.GroupDialog();
						dlg.show();
					}
				}, {
					iconCls: 'ic-settings',
					tooltip: t("Group defaults"),
					handler: function() {
						var module = go.Modules.get("core", "core");

						var win = new go.defaultpermissions.ShareWindow({
							forEntityStore: "Group"
						});
						
						win.load(module.id).show();		

					}
				}

			],

		columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {						
					
						memberStr = record.get("users").column('displayName').join(", ");								
						var more = record.json._meta.users.total - store.fields.item('users').limit;
						if(more > 0) {
							memberStr += t(" and {count} more").replace('{count}', more);
						}

						return '<div>' + value + '</div>' +
										'<small class="username">' + Ext.util.Format.htmlEncode(memberStr) + '</small>';
					}
				}
			],
			autoExpandColumn: 'name',
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				totalDisplay: true,
				actionConfig: {
					scope: this,
					menu: this.initMoreMenu()
				}
			}
			// config options for stateful behavior
//			stateful: true,
//			stateId: 'groups-grid'
		});

		go.groups.SystemSettingsGroupGrid.superclass.initComponent.call(this);

		this.on('viewready', function () {
			this.store.load();
		}, this);

		this.on('rowdblclick', function (grid, rowIndex, e) {
			this.edit(this.store.getAt(rowIndex).id);
		});
	},

	initMoreMenu: function () {
		this.moreMenu = new Ext.menu.Menu({
			items: [
				{
					disabled: !go.Modules.get("core", "core").userRights.mayChangeGroups,
					itemId: "view",
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function (item) {
						var record = this.store.getAt(item.parentMenu.rowIndex);
						this.edit(record.id);
					},
					scope: this
				},
				"-",
				{
					disabled: !go.Modules.get("core", "core").userRights.mayChangeGroups,
					itemId: "delete",
					iconCls: 'ic-delete',
					text: t("Delete"),
					handler: function (item) {
						var record = this.store.getAt(item.parentMenu.rowIndex);
						this.getSelectionModel().selectRecords([record]);
						this.deleteSelected();
					},
					scope: this
				},
			]
		});


		return this.moreMenu;
	},

	edit: function (id) {

		var dlg = new go.groups.GroupDialog();
		dlg.load(id).show();

	}

});

