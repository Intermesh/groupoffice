go.modules.community.addressbook.ContactDetail = Ext.extend(go.panels.DetailView, {
	entityStore: go.Stores.get("Contact"),
	stateId: 'addressbook-contact-detai;',

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: 'readmore',
					onLoad: function (detailView) {
						this.setText("<h3>" + detailView.data.name + "</h3>");
					}
				}
			]
		});


		go.modules.community.addressbook.ContactDetail.superclass.initComponent.call(this);

		//go.CustomFields.addDetailPanels(this);

		this.add(new go.links.LinksDetailPanel());

		if (go.Modules.isAvailable("legacy", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}

		if (go.Modules.isAvailable("legacy", "files")) {
			this.add(new go.modules.files.FilesDetailPanel());
		}
	},

	onLoad: function () {

		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < GO.permissionLevels.write);

		go.modules.community.addressbook.ContactDetail.superclass.onLoad.call(this);
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
						var noteEdit = new go.modules.community.addressbook.ContactForm();
						noteEdit.show();
						noteEdit.load(this.data.id);
					},
					scope: this
				},
				
				new go.detail.addButton({
					detailPanel: this
				}),

				{
					iconCls: 'ic-more-vert',
					menu: [
						{
							iconCls: "btn-print",
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
