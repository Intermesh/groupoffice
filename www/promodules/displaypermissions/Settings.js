/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Settings.js 22307 2018-02-01 14:07:32Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
go.Modules.register("legacy", 'displaypermissions', {
	userSettingsPanels: ["GO.displaypermissions.SettingsPanel"]
});

GO.displaypermissions.SettingsPanel = Ext.extend(Ext.Panel, {
	border: false,
	title : t("Display permissions",),
	iconCls: 'ic-settings',
	layout: "fit",
	onLoad: function(user) {
		this.userId = user.id;
		this.grid.store.baseParams = { user_id: this.userId };
		this.grid.store.load();
	},
	initComponent: function() {
		this.grid = new GO.grid.GridPanel({
			//height: dp(500),
			title: t("Permissions overview", "displaypermissions"),
			store: new GO.data.GroupingStore({
				url: GO.url('displaypermissions/permission/store'),
				reader: new Ext.data.JsonReader({
					root: 'results',
					totalProperty: 'total',
					fields: ['model_type_name', 'model_id', 'model_name', 'permission_level'],
					id: 'id'
				}),
				sortInfo: {
					field: 'model_name',
					direction: 'ASC'
				},
				groupField: 'model_type_name',
				remoteGroup: true,
				remoteSort: true
			}),
			view: new Ext.grid.GroupingView({
				scrollOffset: 2,
				hideGroupedColumn: true,
				emptyText: t("No permissions found", "displaypermissions")
			}),
			sm: new Ext.grid.RowSelectionModel(),
			cm: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true,
					groupable: false
				},
				columns: [{
					id: 'model_name',
					width: 400,
					header: t("Name"),
					dataIndex: 'model_name'
				}, {
					header: t("Model type", "displaypermissions"),
					dataIndex: 'model_type_name',
					width: 60,
					hidden: true,
					groupable: true
				}, {
					id: 'permission_level',
					width: 300,
					header: t("Permission level", "displaypermissions"),
					dataIndex: 'permission_level',
					renderer: function(v,metadata,record) {
						switch (v) {
							case '50':
								return t("Manage");
								break;
							case '40':
								return t("Write and delete");
								break;
							case '30':
								return t("Write");
								break;
							case '20':
								if (record.data.model_type_name==t("E-mail Account", "email"))
									return t("Use account", "email");
								else
									return t("Read and Create only");
								break;
							case '15':
								if (record.data.model_type_name==t("E-mail Account", "email"))
									return t("Read only and delegated", "email");
								else
									return 15;
								break;
							case '10':
								return t("Read only");
								break;
							default:
								return '';
								break;
						}
					}
				}]
			}),
			paging: false
		});

		this.items = [this.grid];
		GO.displaypermissions.SettingsPanel.superclass.initComponent.call(this);
	}
});
