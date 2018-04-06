go.modules.comments.CommentsDetailPanel = Ext.extend(Ext.Panel, {

	model_name: null,
	title: t("Comments", "comments"),
	collapsible: true,	
	titleCollapse: true,
	stateId: "comments-detail",
	initComponent: function () {

		this.store = new go.data.Store({
				fields: ['id', 'modseq', 'categoryId', 'categoryName', 'entityId', 'entityId', 'createdAt', 'modifiedAt', 'modifiedBy', 'createdBy', 'deletedAt', 'comment'],
				entityStore: go.stores.Comment
			}),

//		this.store = new GO.data.JsonStore({
//			url: GO.url('comments/comment/store'),
//			baseParams: {
//				task: 'comments',
//				limit: 10
//			},
//			fields: ['id', 'modseq', 'categoryId', 'categoryName', 'entityId', 'entityId', 'createdAt', 'modifiedAt', 'modifiedBy', 'createdBy', 'deletedAt', 'comment'],
//			remoteSort: true
//		});

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
					
					var commentEdit = new go.modules.comments.CommentForm();
						commentEdit.show(this.entityId, this.entity);
//						commentEdit.load(this.data.id);
					
					
//					var dlg = GO.comments.showCommentDialog(0, {
//						link_config: {
//							entity: this.entity,
//							entityId: this.entity
//
//						}
//					});
//					
//					dlg.on('hide', function(){
//						this.onLoad(this);
//					}, this, {single: true});
				}
			}
		]

		go.modules.comments.CommentsDetailPanel.superclass.initComponent.call(this);

	},

	onLoad: function (dv) {
		this.entityId = dv.model_id ? dv.model_id : dv.currentId ;//model_id is from old display panel
		this.entity = dv.model_name || dv.entity || dv.entityStore.entity.name;

		this.removeAll();
		this.store.load({
			params: {
				filter:[{
					entity: this.entity,
					entityId: this.entityId
				}]
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
