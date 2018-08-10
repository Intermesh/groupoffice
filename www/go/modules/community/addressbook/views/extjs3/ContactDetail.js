go.modules.community.addressbook.ContactDetail = Ext.extend(go.panels.DetailView, {
	entityStore: go.Stores.get("Contact"),
	stateId: 'addressbook-contact-detai;',

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: 'panel',
					layout: "hbox",
					
					tbar: [
						this.starButton = new Ext.Button({
							xtype: "button",
							iconCls: "ic-star-border",
							handler: function() {
								var starred = this.starButton.iconCls == "ic-star", id = this.data.id + "-" + go.User.id;								
								update = {};
								update[id] = {starred: !starred};
								
								this.starButton.setIconClass(!starred ? 'ic-star' : 'ic-star-border');
								
								if(go.Stores.get("ContactStar").data[id]) {
									go.Stores.get("ContactStar").set({update: update});
								} else
								{
									update[id].contactId = this.data.id;
									update[id].userId = go.User.id;
									go.Stores.get("ContactStar").set({create: update});
								}
								
							},
							scope: this
						}),
						this.titleComp = new go.toolbar.TitleItem()
					],
					onLoad: function (detailView) {
						detailView.titleComp.setText(detailView.data.name);
						
						go.Stores.get("ContactStar").get([detailView.data.id + "-" + go.User.id], function(entities) {
							detailView.starButton.setIconClass(entities[0].starred ? 'ic-star' : 'ic-star-border');
						}, this);
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
