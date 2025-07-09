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
				remoteSort: true,
				fields: [
					'id',
					'name',
					'color',
					{name: "group", type: "relation", mapping: "group.name"}
				],
				entityStore: this.support ? "SupportList" : "Tasklist",
				filters: {role: {role: 'list'}, subscribed: {isSubscribed:true}},
				sortInfo: {
					field: 'sortOrder',
					direction: 'ASC'
				}
			}),

			menuItems: [
				{
					itemId: "edit",
					iconCls: 'ic-edit',
					text: t("Edit")+'…',
					handler: function() {
						var dlg = new go.modules.community.tasks.TasklistDialog({entityStore: this.support ? "SupportList" : "TaskList"});
						dlg.load(this.moreMenu.record.id).show();
					},
					scope: this
				},{
					itemId: "delete",
					iconCls: 'ic-delete',
					text: t("Delete")+'…',
					handler: function() {
						Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
							if (btn != "yes") {
								return;
							}
							go.Db.store(this.support ? "SupportList" : "Tasklist").set({destroy: [this.moreMenu.record.id]});
						}, this);
					},
					scope: this
				},'-',
				{
					text: t('Unsubscribe'),
					iconCls: 'ic-remove-circle',
					handler: () => {
						this.store.entityStore.save({isSubscribed: false}, this.moreMenu.record.id);
					}
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
