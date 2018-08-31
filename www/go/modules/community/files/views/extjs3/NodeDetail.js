go.modules.community.files.NodeDetail = Ext.extend(go.panels.DetailView, {
	entityStore: go.Stores.get("Node"),
	stateId: 'fs-node-detail',
	
	initComponent: function () {

		if(!this.browser){
			throw "Parameter 'browser' is required!";
		}

		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [
				{tpl:new Ext.XTemplate('<h3 class="title s8">{name}</h3>\
<h4 class="title s4 right">{[values.size && fm.fileSize(values.size)]}</h4>\\n\
<div class="detail-preview">{[this.preview(values)]}</div>\
<tpl if="values.metaData && values.metaData.data3"><label class="center">{values.metaData.data3} x {values.metaData.data4}</label></tpl>\
<hr />\
<p class="pad">\
<label>Location</label>\
<span>{[this.drawPath(values.name)]}</span></p><hr>',{
					drawPath: function(name) {
						var path = this.browser.getPath().slice(0),
						  str = this.browser.getRootNode().text;
							path.shift();
							if(path.length) {
								str += ' <i class="icon small">chevron_right</i> ';
							}
						var nodes = go.Stores.get('Node').get(path);
						for(var i = 0; i < nodes.length; i++) {
							str += nodes[i].name 
							if(i < nodes.length-1) {
								str += ' <i class="icon small">chevron_right</i> ';
							}
						}
						return str;
					},
					preview: function(values) {
						var type = null;
						if(values.contentType) {
							type = values.contentType.split('/')[0];
						}
						switch(type) {
							case 'image':
								if(values.metaData && values.metaData.thumbnail) {
									return '<a onclick="go.Preview({blobId:\''+values.blobId+'\',contentType:\''+values.contentType+'\'})"><figure class="contain" style="background-image:url('+go.Jmap.downloadUrl(values.metaData.thumbnail)+');"></figure></a>'
								} else if (values.contentType === 'image/svg+xml') {
									return '<figure class="contain" style="background-image:url('+go.Jmap.downloadUrl(values.blobId)+');"></figure>'
								}
								break;
							case 'video':
								return '<video controls><source src="'+go.Jmap.downloadUrl(values.blobId)+'" type="'+values.contentType+'"></video>';
							case 'audio':
								return '<audio controls><source src="'+go.Jmap.downloadUrl(values.blobId)+'" type="'+values.contentType+'">Your browser does not support the audio element.</audio>'
						}
						return '<div class="preview filetype '+go.util.contentTypeClass(values.contentType, values.name)+'"></div>'
						
					},
					browser:this.browser
				})},
				new go.panels.CreateModifyTpl(),
				{
					title:t('More info'),
					collapsible:true,
					tpl: new Ext.XTemplate('<tpl if="values.metaData"><div class="pad">\
<tpl if="values.metaData.encoding"><label>Color profile</label> <span>{values.metaData.encoding}</span></tpl>\
<tpl if="values.metaData.data7"><label>Alpha channel</label> <span>{values.metaData.data7}</span></tpl>\
<tpl if="values.metaData.data2"><label>Orientation</label> <span>{values.metaData.data2}</span></tpl>\
<tpl if="values.metaData.date"><label>Last opened</label> <span>{values.metaData.date}</span></tpl>\
<tpl if="values.metaData.title"><label>Title</label> <span>{values.metaData.title}</span></tpl>\
<tpl if="values.metaData.author"><label>Author</label> <span>{values.metaData.author}</span></tpl>\
<tpl if="values.metaData.keywords"><label>Keywords</label> <span>{values.metaData.keywords}</span></tpl>\
<tpl if="values.metaData.copyright"><label>Copyright</label> <span>{values.metaData.copyright}</span></tpl>\
<tpl if="values.metaData.uri"><label>uri</label> <span>{values.metaData.uri}</span></tpl>\
<tpl if="values.metaData.creator"><label>creator</label> <span>{values.metaData.creator}</span></tpl>\
<tpl if="values.metaData.date"><label>Taken at</label> <span>{[fm.date(values.metaData.date)]}</span</tpl>\
<tpl if="values.metaData.data1"><label>camera</label> <span>{values.metaData.data1}</span></tpl>\
<tpl if="values.metaData.data3"><label>Resolution</label> <span>{values.metaData.data3} x {values.metaData.data4}</span></tpl>\
<tpl if="values.metaData.data5"><label>Location</label> <span>{values.metaData.data5} x {values.metaData.data6}</span></tpl>\
</div></tpl>')
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
					go.Preview(this.data);
				},
				scope: this
			},{
				iconCls: 'ic-file-download',
				tooltip: t("Download"),
				itemId: "download",
				handler: function (btn, e) {
					window.open(go.Jmap.downloadUrl(this.data.blobId));
				},
				scope: this
			},{
				iconCls: 'ic-person-add',
				tooltip: t("Share"),
				handler: function (btn, e) {
					if(!this.shareDialog) {
						this.shareDialog = new go.modules.community.files.ShareDialog();
					}
					this.shareDialog.load(this.currentId).show();
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
