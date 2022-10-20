go.modules.community.tasks.TasklistsGrid = Ext.extend(go.NavGrid, {
	autoHeight: true,
	scrollLoader: false,
	showMoreLoader: true,
	loadMorePageSize: 20,
	cls: "go-tasks-task-list",

	initColumns: function() {

		go.modules.community.tasks.TasklistsGrid.superclass.initColumns.call(this);


		this.columns.push({
			id: 'group',
			header: t('Group'),
			sortable: false,
			dataIndex: 'group',
			// groupRenderer: function(v, un, r, rowIndex, colIndex, ds) {
			// 	if(!v) {
			// 		return "";
			// 	}
			// }
		})

	},

	initComponent: function () {

		this.view = new go.grid.GroupingView({
			showGroupName: false,
			emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
			totalDisplay: false,
			hideGroupedColumn: true,
			forceFit: true,
			autoFill: true,
			emptyGroupText: ""
		});

		Ext.apply(this, {
			store: new go.data.GroupingStore({
				groupField: "group",
				remoteGroup: true,
				fields: [
					'id',
					'name',
					{name: "group", type: "relation", mapping: "group.name"}
				],
				entityStore: "TaskList",
				filters: {role: {role: 'list'}},
				sortInfo: {
					field: 'name',
					direction: 'ASC'
				}
			}),

			menuItems: [
				{
					itemId: "edit",
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function() {
						var dlg = new go.modules.community.tasks.TasklistDialog();
						dlg.load(this.moreMenu.record.id).show();
					},
					scope: this
				},{
					itemId: "delete",
					iconCls: 'ic-delete',
					text: t("Delete"),
					handler: function() {
						Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
							if (btn != "yes") {
								return;
							}
							go.Db.store("TaskList").set({destroy: [this.moreMenu.record.id]});
						}, this);
					},
					scope: this
				}
			],

			stateful: true,
			stateId: 'task-lists-grid'
		});

		go.modules.community.tasks.TasklistsGrid.superclass.initComponent.call(this);


		this.on('beforeshowmenu', (menu, record) => {
			menu.getComponent("edit").setDisabled(record.get("permissionLevel") < go.permissionLevels.manage);
			menu.getComponent("delete").setDisabled(!go.Modules.get("community", 'tasks').userRights.mayChangeTasklists || record.get("permissionLevel") < go.permissionLevels.manage);
		});
	},

	

});
