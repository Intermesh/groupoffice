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
				limit: 0
			},
			fields: ['id', 'model_id', 'category_id', 'category_name', 'model_name', 'user_name', 'ctime', 'mtime', 'comments'],
			remoteSort: true
		});

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
				
				var me = this;
				this.store.each(function(record) {
					var readMore = new go.detail.ReadMore();
					readMore.setText(record.get('comments'));
					this.add({
						xtype:"panel",
						tbarCfg:{
							style:'border-bottom:none;border-top:1px solid #E0E0E0;'
						},
						tbar:[
							{
								xtype:"tbtitle",
								style:'font-size:'+dp(16)+'px; height:'+dp(14)+'px;',
								text:t('{author} wrote at {date}').replace('{author}', record.get('user_name')).replace('{date}', record.get('ctime'))
							},
							'->',
							{
								iconCls: 'ic-more-vert',
								menu:[
									{
										iconCls: "ic-edit",
										text: t("Edit"),
										handler: function () {
											var dlg = GO.comments.showCommentDialog(record.get('id'), {
												link_config: {
													model_name: me.model_name,
													model_id: me.model_id
												}
											});

											dlg.on('hide', function(){
												me.onLoad(me);
											}, this, {single: true});
										},
										scope: this
									},{
										iconCls: "ic-delete",
										text: t("Delete"),
										handler: function () {
											
											Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function(btn) {
												if(btn == 'yes') {
													GO.request({
														url:'comments/comment/delete',
														params:{					
															id:record.get('id')				
														},
														success: function(response, options, result){
															if(!result.success){
																alert(result.feedback);
															}
															me.onLoad(me);
														},
														scope:this
													});
												}
											});
										},
										scope: this
									}
								]
							}
						],
						items: [
//							{
//								xtype:'box',
//								autoEl: 'h5',					
//								cls:'pad',
//								html: '<div class="icons">'++'<a class="right show-on-hover"><i class="icon">delete</i></a></div>'
//							},
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
