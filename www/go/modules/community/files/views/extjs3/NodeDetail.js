go.modules.files.NodeDetail = Ext.extend(go.panels.DetailView, {
	entityStore: go.Stores.get("Folder"),
	stateId: 'fs-node-detail',

	//model_name: "go\\modules\\community\\notes\\model\\Note", //only for backwards compatibility with older panels.

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: 'readmore',
					onLoad: function (detailView) {
						this.setText("<h3>" + detailView.data.name + "</h3>" + detailView.data.content);
					}
				}
			]
		});


		go.modules.files.NodeDetail.superclass.initComponent.call(this);

		go.CustomFields.addDetailPanels(this);

		this.add(new go.links.LinksDetailPanel());

		if (go.Modules.isAvailable("community", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}

		if (go.Modules.isAvailable("community", "files")) {
			this.add(new go.modules.files.FilesDetailPanel());
		}
	},


	onLoad: function () {

		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < GO.permissionLevels.write);

		go.modules.files.NodeDetail.superclass.onLoad.call(this);
	},

	initToolbar: function () {



		var items = this.tbar || [];
		
		items = items.concat([
				'->',
				{
					itemId: "edit",
					iconCls: 'ic-edit',
					tooltip: t("Edit"),
					handler: function (btn, e) {
						var nodeEdit = new go.modules.files.NodeForm();
						nodeEdit.show();
						nodeEdit.load(this.data.id);
					},
					scope: this
				},

				{
					iconCls: 'ic-more-vert',
					menu: [
						{
							iconCls: "ic-print",
							text: t("Print"),
							handler: function () {
								this.body.print({title: this.data.name});
							},
							scope: this
						}

					]
				}]);

		var tbarCfg = {
			disabled: true,
			items: items
		};


		return new Ext.Toolbar(tbarCfg);


	}
});