Ext.namespace('go.modules.comments');

go.modules.comments.CommentsDetailPanel = Ext.extend(Ext.Panel, {

	model_name: null,
	title: t("Comments", "comments"),
	collapsible: true,	
	titleCollapse: true,
	stateId: "comments-detail",
	initComponent: function () {

		this.store = new GO.data.JsonStore({
			url: GO.url('comments/comment/store'),
			baseParams: {
				task: 'comments',
				limit: 10
			},
			fields: ['id', 'model_id', 'category_id', 'category_name', 'model_name', 'user_name', 'ctime', 'mtime', 'comments'],
			remoteSort: true
		});

		this.bbar = [
//			"->",
//			{
//				text: t("Browse"),
//				scope: this,
//				handler: function () {
//					var dlg = GO.comments.browseComments(this.model_id, this.model_name);
//					dlg.on('hide', function(){
//						this.store.reload();
//					}, this, {single: true});
//				}
//			},
			{
				text: t("Add"),
				scope: this,
				handler: function () {
					var dlg = GO.comments.showCommentDialog(0, {
						link_config: {
							model_name: this.model_name,
							model_id: this.model_id

						}
					});
					
					dlg.on('hide', function(){
						this.onLoad(this);
					}, this, {single: true});
				}
			}
		]

		go.modules.comments.CommentsDetailPanel.superclass.initComponent.call(this);

	},

	onLoad: function (dv) {

		this.model_id = dv.model_id ? dv.model_id : dv.currentId //model_id is from old display panel
		this.model_name = dv.model_name || dv.entity || dv.entityStore.entity.name;

		this.removeAll();
		this.store.load({
			params: {
				model_name: this.model_name,
				model_id: this.model_id
			},
			callback: function() {
				
				this.store.each(function(r) {
					var readMore = new go.detail.ReadMore();
					readMore.setText(r.get('comments'));
					this.add({
						xtype:"container",												
						items: [
							{
								xtype:'box',
								autoEl: 'h5',					
								cls:'pad',
								html: t('{author} wrote at {date}').replace('{author}', r.get('user_name')).replace('{date}', r.get('ctime'))
							},
							readMore
						]
					});
				}, this);
				
				this.doLayout();
			},
			scope: this
		});
	}

});
