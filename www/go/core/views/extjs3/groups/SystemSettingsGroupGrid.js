
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
	iconCls: 'ic-group',
	initComponent: function () {

		var actions = this.initRowActions();

		this.title = t("Groups");

		this.store = new go.data.Store({
			baseParams: {
				filter: {
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
			plugins: [actions],
			tbar: ['->',
				{
					xtype: 'tbsearch',
					filters: [
						'text'					
					]
				}, {
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
				},
				actions
			],
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true,
				totalDisplay: true
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
					"-", 
					{
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

		var dlg = new go.groups.GroupDialog();
		dlg.load(id).show();

	}

});

