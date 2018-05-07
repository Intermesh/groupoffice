go.modules.community.files.NodeDetail = Ext.extend(go.panels.DetailView, {
	entityStore: go.Stores.get("Node"),
	stateId: 'fs-node-detail',
	
	initComponent: function () {

		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [
				{
					tpl: '<h3 class="title s8">{name}</h3>\
<h4 class="title s4 right">{[values.size && fm.fileSize(values.size)]}</h4>\
<tpl if="values.blobId"><figure class="contain" style="max-height: 200px;background-image:url({[go.Jmap.downloadUrl(values.blobId)]});"></tpl>\
<tpl if="!values.blobId"><div class="preview filetype folder"></div></tpl>\
</figure>\
<hr />\
<p class="pad">\
<label>Location</label>\
<span>{location}</span></p>\
<hr>'+go.panels.CreateModifyTpl
				}
			]
		});

		go.modules.community.files.NodeDetail.superclass.initComponent.call(this);

		go.CustomFields.addDetailPanels(this);

		//this.add(new go.links.LinksDetailPanel());

		if (go.Modules.isAvailable("community", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}
	},


	onLoad: function () {

		this.getTopToolbar().getComponent("download").setDisabled(!this.data.blobId);
		this.getTopToolbar().getComponent("preview").setDisabled(!this.data.blobId);

		go.modules.community.files.NodeDetail.superclass.onLoad.call(this);
	},

	initToolbar: function () {

		var items = this.tbar || [];
		
		items = items.concat([
			'->',
			{
				iconCls: 'ic-open-in-new',
				tooltip: t("Preview"),
				itemId: "preview",
				handler: function (btn, e) {
					var nodeEdit = new go.modules.community.files.NodeForm();
					nodeEdit.show();
					nodeEdit.load(this.data.id);
				},
				scope: this
			},{
				iconCls: 'ic-file-download',
				tooltip: t("Download"),
				itemId: "download",
				handler: function (btn, e) {
					go.Jmap.downloadUrl(this.data.blobId);
				},
				scope: this
			},{
				iconCls: 'ic-person-add',
				tooltip: t("Share"),
				handler: function (btn, e) {
					alert('todo');
				},
				scope: this
			},{
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
			}
		]);

		var tbarCfg = {
			disabled: true,
			items: items
		};


		return new Ext.Toolbar(tbarCfg);


	}
});
