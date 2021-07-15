go.modules.community.notes.NoteBookGrid = Ext.extend(go.NavGrid, {

	initComponent: function () {
		Ext.apply(this, {

			menuItems: [
				{
					itemId: "edit",
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function() {
						var dlg = new go.modules.community.notes.NoteBookDialog();
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
							go.Db.store("NoteBook").set({destroy: [this.moreMenu.record.id]});
						}, this);
					},
					scope: this
				}
			],
			store: new go.data.Store({
				fields: ['id', 'name', 'aclId', "permissionLevel"],
				entityStore: "NoteBook",
				sortInfo: {
					field: "name"
				}
			})
		});

		go.modules.community.notes.NoteBookGrid.superclass.initComponent.call(this);

		this.on('beforeshowmenu', (menu, record) => {
			menu.getComponent("edit").setDisabled(record.get("permissionLevel") < go.permissionLevels.manage);
			menu.getComponent("delete").setDisabled(record.get("permissionLevel") < go.permissionLevels.manage);
		});
	}
});
