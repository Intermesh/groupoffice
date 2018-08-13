go.modules.community.addressbook.ContactDetail = Ext.extend(go.panels.DetailView, {
	entityStore: go.Stores.get("Contact"),
	stateId: 'addressbook-contact-detail',
	
	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: 'container',
					layout: "hbox",
					cls: "go-addressbook-name-panel",
					items: [						
						this.namePanel = new Ext.BoxComponent({
							tpl: "<h3>{name}</h3><h4>{jobTitle}</h4>"
						}),
						this.starButton = new go.modules.community.addressbook.StarButton()
					],
					onLoad: function (detailView) {						
						detailView.namePanel.update(detailView.data);
						detailView.starButton.setContactId(detailView.data.id);
					},
					
				}, 
				
				{
					tpl: new Ext.XTemplate('<div class="go-addressbook-contact-avatar">\
<div class="avatar {[this.getCls(values.isOrganization)]}" style="{[this.getStyle(values.photoBlobId)]}"></div></div>', 
					{
						getCls: function (isOrganization) {
							return isOrganization ? "group" : "";
						},
						getStyle: function (photoBlobId) {
							return photoBlobId ? 'background-image: url(' + go.Jmap.downloadUrl(photoBlobId) + ')"' : "";
						}
					})
				},
				
				
				{
					xtype: "toolbar",
					buttonAlign: "center",
					items: [
						this.emailButton = new Ext.Button({
							menu: [],
							text: t("E-mail"),
							iconCls: 'ic-email'
						}),
						
						this.callButton = new Ext.Button({
							menu: [],
							text: t("Call"),
							iconCls: 'ic-phone'
						})
					]
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
					var dlg = new go.modules.community.addressbook.ContactDialog();
					dlg.show();
					dlg.load(this.data.id);
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
