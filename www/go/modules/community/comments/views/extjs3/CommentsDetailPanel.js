go.modules.comments.CommentsDetailPanel = Ext.extend(Ext.Panel, {
	entityId:null, 
	entity:null,
	height: dp(500),
	title: t("Comments", "comments"),
	collapsible: true,
	collapseFirst:false,
	layout:'border',
	tools:[{
		id: "gear",
		handler: function () {		
			if(!go.modules.comments.settingsForm) {
				go.modules.comments.settingsForm = new go.modules.comments.Settings();
			}
			go.modules.comments.settingsForm.show(go.User.id);
		}
	}],
	titleCollapse: true,
	stateId: "comments-detail",
	initComponent: function () {

		this.store = new go.data.Store({
			fields: ['id', 'categoryId', 'categoryName','entityId', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'modifiedBy','createdBy', 'text'],
			entityStore: go.Stores.get("Comment")
		});
		
		this.store.on('load', function() {
				
				var userStore = go.Stores.get("User");
								
				var creatorIds = [];
				this.store.each(function(r) {
					if(creatorIds.indexOf(r.get('createdBy')) === -1) {
						creatorIds.push(r.get('createdBy'));
					}
				}, this);
			
				if(userStore.get(creatorIds)) {
					this.updateView();
				}
				
			}, this);
							
		go.flux.Dispatcher.register(this);
		
		this.contextMenu = new Ext.menu.Menu({
			items:[{
				iconCls: 'ic-delete',
				text: t("Delete"),
				handler: function() {
					if(confirm(t("Are you sure you want to delete the selected item?"))){
						return;
						GO.request({
							url:'tickets/message/delete',
							params:{id:this.contextMenu.record.id},
							success:function(){
								this.reload();
							},
							scope:this
						});
					}
				},
				scope:this
			},{
				iconCls: 'ic-edit',
				text: t("Edit"),
				handler: function() {
					if(!this.commentForm) {
						this.commentForm = new go.modules.comments.CommentForm();
					}
					this.commentForm.load(this.contextMenu.record.id).show();
				},
				scope:this
			}]
		});

		this.items = [
			this.commentsContainer = new Ext.Container({
				region:'center',
				autoScroll:true
			}),
			this.composer = new go.modules.comments.Composer({
				margins: {left: dp(8), right: dp(8),bottom:dp(8),top:0},
				region:'south',
				height:60
			})
		];
		
//		this.composer.on('resize',function(me,w,h){
//			this.composer.setHeight(h);
//			this.syncSize();
//		},this);
			
		go.modules.comments.CommentsDetailPanel.superclass.initComponent.call(this);

	},
	
	receive: function(action) {
		if(action.type === "UserUpdated") {
			this.updateView();
		}
	},

	onLoad: function (dv) {
		this.entityId = dv.model_id ? dv.model_id : dv.currentId ;//model_id is from old display panel
		this.entity = dv.model_name || dv.entity || dv.entityStore.entity.name;
		this.composer.initEntity(this.entityId, this.entity);
		
		this.store.load({
			params: {
				filter:{
					entity: this.entity,
					entityId: this.entityId
				}
				//sort: 'createdAt DESC'
			},
			scope: this
		});
	},
		
	updateView : function() {

		this.composer.textField.setValue('');
		
		var userStore = go.Stores.get("User"), prevStr;

		this.commentsContainer.removeAll();

		this.store.each(function(r) {
			var mimeCls = r.get("createdBy") == go.User.id ? 'mine' : '';
			var readMore = new go.detail.ReadMore({
				cls: mimeCls
			});
			readMore.setText(r.get('text'));
			this.commentsContainer.add({
				xtype:"container",
				cls:'go-messages',
				items: [{
						xtype:'box',
						autoEl: 'h6',
						hidden: prevStr == go.util.Format.date(r.get('createdAt')),
						html: go.util.Format.date(r.get('createdAt'))
					},{
						xtype:'container',
						items: [{
							xtype:'box',
							autoEl: {tag: 'span','ext:qtip': t('{author} wrote at {date}')
								.replace('{author}', userStore.data[r.get("createdBy")].displayName)
								.replace('{date}', Ext.util.Format.date(r.get('createdAt'),go.User.dateTimeFormat))},
							cls: 'photo '+mimeCls,
							style: 'background-image: url('+go.Jmap.downloadUrl(userStore.data[r.get("createdBy")].avatarId)+');'
						},readMore]
					}
				]
			});
			readMore.on('render',function(me){me.getEl().on("contextmenu", function(e, target, obj){
				e.stopEvent();		
				//todo check permission
				this.contextMenu.record = r;
				this.contextMenu.showAt(e.xy);

			}, this);},this);
			prevStr = go.util.Format.date(r.get('createdAt'));
		}, this);

		this.doLayout();
		//this.setHeight(Math.max(this.getHeight(),this.commentsContainer.getHeight()));
		var scroll = this.commentsContainer.getEl();
		if(scroll) {
			scroll.scroll("b", this.commentsContainer.getHeight()+10000); 
		}
//		if(this.composer.getEl()) {
		//	this.commentsContainer.getEl().scrollIntoView(this.body);
//		}
	}
});
