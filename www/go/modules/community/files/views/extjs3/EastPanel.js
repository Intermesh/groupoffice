go.modules.community.files.EastPanel = Ext.extend(Ext.Panel, {
	region: 'east',
	width: dp(560),
	split: true,
	layout: 'card',
	initComponent: function() {
		this.items = [
			new go.modules.community.files.NodeDetail({
				browser: this.browser,
				tbar: [{
					cls: 'go-narrow',
					iconCls: "ic-arrow-back",
					handler: function () {
						this.westPanel.show();
					},
					scope: this
				}]
			}),
			new Ext.Panel({
				cls: 'go-detail-view',
				tbar:['->',{
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
				}],
				items: [
					{tpl:new Ext.XTemplate('<h3 class="title s8">{values.length} Items selected</h3>\\n\
						<h4 class="title s4 right">{[this.size(values)]}</h4>\
						<div class="detail-preview"><div class="preview filetype multiple"></div></div>\
						<hr />\
						<p class="pad">\
						<label>Location</label>\
						<span>{[this.drawPath()]}</span></p><hr>',{
							drawPath: function() {
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
							size: function(ids) {
								var size = 0;
								var nodes = go.Stores.get('Node').get(ids);
								for(var i = 0; i < nodes.length; i++) {
									size += parseInt(nodes[i].size) || 0;
								}
								return Ext.util.Format.fileSize(size);
							},
							browser:this.browser
						})
					}
				],
				load: function(ids) {
					this.items.itemAt(0).update(ids);
				}
			})
		];
		go.modules.community.files.EastPanel.superclass.initComponent.call(this);
	}
	
});