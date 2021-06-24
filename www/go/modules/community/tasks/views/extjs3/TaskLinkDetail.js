go.modules.community.tasks.TaskLinkDetail = Ext.extend(go.modules.community.tasks.TaskGrid,{
	autoHeight: true,
	maxHeight: dp(300),
	title: t("Tasks"),
	collapsible: true,
	initComponent: function() {

		this.view = new go.grid.GroupingView({
			emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
			totalDisplay: false,
			actionConfig: {
				scope: this,
				menu: this.initMoreMenu()
			},
			hideGroupedColumn: true,
			showGroupName: false
		});

		this.supr().initComponent.call(this);


		this.on('rowdblclick', (grid, rowIndex) => {

			const record = grid.store.getAt(rowIndex);

			this.open(record.id);
		});

		this.store.on("datachanged", function () {
			this.setVisible(this.store.getCount() > 0);

			if(!this.origTitle) {
				this.origTitle = this.title;
			}
			var count = this.store.getTotalCount();
			if(this.store.hasMore) {
				count += '+';
			}
			var badge = "<span class='badge'>" + count + "</span>";
			this.setTitle(this.origTitle + badge);
		}, this);

		go.Db.store("Link").on("changes", this.onLinkChanges, this);

		this.on('destroy', () => {
			go.Db.store("Link").un("changes", this.onLinkChanges, this);
		});
	},

	onLinkChanges: function(store, added, changed, detroyed)  {
		if(!this.store.loaded || this.store.loading || !this.store.lastOptions) {
			return;
		}

		this.store.reload();
	},

	onLoad: function (dv) {

		this.detailView = dv;

		this.store.setFilter("link" , {link: {
				entity: dv.entity ? dv.entity : dv.entityStore.entity.name, //dv.entity exists on old DetailView or display panels
				id: dv.model_id ? dv.model_id : dv.currentId //model_id is from old display panel
			}})

		this.store.baseParams.position = 0;
		this.store.load();
	},

	initMoreMenu: function (node, e, record) {
		return {
			items: [{
				itemId: "edit",
				iconCls: 'ic-edit',
				text: t("Edit"),
				handler: function (item) {
					const record = this.store.getAt(item.parentMenu.rowIndex);
					const dlg = new go.modules.community.tasks.TaskDialog();
					dlg.load(record.id).show();
				},
				scope: this
			}, {
				itemId: "delete",
				iconCls: "ic-delete",
				text: t("Unlink"),
				handler: function (item) {
					Ext.MessageBox.confirm(t("Delete"), t("Are you sure you want to unlink this item?"), function (btn) {

						const filter = this.store.getFilter("link").link,
							record = this.store.getAt(item.parentMenu.rowIndex),
							linkId = "Task-" + record.data.id + "-" + filter.entity + "-" + filter.id;

						if (btn == "yes") {
							go.Db.store("Link").set({
								destroy: [linkId]
							});
						}
					}, this);
				},
				scope: this
			}, {
				itemId: "open",
				iconCls: "ic-open-in-new",
				text: t("Open"),
				handler: function (item) {
					// var win = new go.links.LinkDetailWindow({
					// 	entity: this.linkMoreMenu.record.data.toEntity
					// });
					//
					// win.load(this.linkMoreMenu.record.data.toId);
					const record = this.store.getAt(item.parentMenu.rowIndex);
					//go.Entities.get("Task").goto(record.data.id);
					this.open(id);


				},
				scope: this
			}]
		};
	},

	open : (id) => {

		const win = new go.links.LinkDetailWindow({
			entity: "Task"
		});

		win.load(id);
	}

});
