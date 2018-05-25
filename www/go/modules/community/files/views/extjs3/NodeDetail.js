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
<h4 class="title s4 right">{[values.size && fm.fileSize(values.size)]}</h4>\
<tpl if="values.metaData"><figure class="contain" style="max-height: 200px;background-image:url({[go.Jmap.downloadUrl(values.metaData.thumbnail)]});"></tpl>\
<tpl if="!values.metaData"><div class="preview filetype {[this.icon(values)]}"></div></tpl>\
</figure>\
<tpl if="values.metaData && values.metaData.data3"><label class="center">{values.metaData.data3} x {values.metaData.data4}</label></tpl>\
<hr />\
<p class="pad">\
<label>Location</label>\
<span>{[this.drawPath(values.name)]}</span></p><hr>',{
					drawPath: function(name) {
						var str = this.browser.getCurrentRootNode() + ' <i class="icon small">chevron_right</i> ';
							nodes = go.Stores.get('Node').get(this.browser.getPath());
						for(var i = 0; i < nodes.length; i++) {
							str += nodes[i].name 
							if(i < nodes.length-1) {
								str += ' <i class="icon small">chevron_right</i> ';
							}
						}
						return str;
					},
					icon: function(values) {
						//todo: find thumb in metadata
						return go.util.contentTypeClass(values.contentType, values.name);
					},
					browser:this.browser
				})},{
					tpl:go.panels.CreateModifyTpl
				},{
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
