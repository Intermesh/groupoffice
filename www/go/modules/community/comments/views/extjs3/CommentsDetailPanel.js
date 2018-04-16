go.modules.comments.CommentsDetailPanel = Ext.extend(Ext.Panel, {
	entityId:null, 
	entity:null,
	
	title: t("Comments", "comments"),
	collapsible: true,
	collapseFirst:false,
	tools:[{
		id: "gear",
		handler: function () {		
			if(!go.modules.comments.detailSettingsForm) {
				go.modules.comments.detailSettingsForm = new go.modules.comments.CommentDetailSettingsForm();
			}
			go.modules.comments.detailSettingsForm.show(go.User.id);
		}
	}],
	titleCollapse: true,
	stateId: "comments-detail",
	initComponent: function () {

		this.store = new go.data.Store({
			fields: ['id', 'modseq', 'categoryId', 'categoryName', 'entityId', 'entityId', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'modifiedBy','createdBy', 'deletedAt', 'comment'],
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

		this.bbar = [
			{
				text: t("Add"),
				menu:[
					{
						text: t("Comment"),
						handler: function () {		
							var commentEdit = new go.modules.comments.CommentForm();
							commentEdit.show(this.entityId, this.entity);
						},
						scope: this
					},
					"-",
					{
						text: t("Browse"),
						handler: function () {
							var commentGrid = new go.modules.comments.CommentForm();
							commentEdit.show(this.entityId, this.entity);
							
							var dlg = GO.comments.browseComments(this.model_id, this.model_name);
						},
						scope: this
					}
				],
				scope: this				
			}
		]

		go.modules.comments.CommentsDetailPanel.superclass.initComponent.call(this);

	},
	
	receive: function(action) {
		if(action.type === "UserUpdated") {
			
			// Update the comment setting in the main user object.
			// This is needed because the main user object is not listening to the User store with Flux.
			// Better is to fix that, so the go.User object is updated automatically on a change.
			// @TODO: implement this in the go.User object so it auto-updates
			go.User.commentSettings = go.User.commentSettings || {};
			if(action.payload.list[0].commentSettings){
				go.User.commentSettings.enableQuickAdd = action.payload.list[0].commentSettings.enableQuickAdd;
			}
			
			this.updateView();
		}
	},

	onLoad: function (dv) {
		this.entityId = dv.model_id ? dv.model_id : dv.currentId ;//model_id is from old display panel
		this.entity = dv.model_name || dv.entity || dv.entityStore.entity.name;

		this.store.load({
			params: {
				filter:[{
					entity: this.entity,
					entityId: this.entityId
				}]
			},
			scope: this
		});
	},
		
	updateView : function() {

		if(!this.quickAddForm){
			this.quickAddForm = new go.modules.comments.QuickaddForm();
			this.add(this.quickAddForm);
		}
		
		this.quickAddForm.setBaseParams({entity: this.entity,entityId: this.entityId});
		var enableQuickaddForm = go.User.commentSettings && go.User.commentSettings.enableQuickAdd?true:false;
		this.quickAddForm.setVisible(enableQuickaddForm);
		
		if(!this.commentsContainer){
			this.commentsContainer = new Ext.Container();
			this.add(this.commentsContainer);
		} else {
			this.commentsContainer.removeAll();
		}
		
		var userStore = go.Stores.get("User");

		this.store.each(function(r) {
			var readMore = new go.detail.ReadMore();
			readMore.setText(r.get('comment'));
			this.commentsContainer.add({
				xtype:"container",												
				items: [
					{
						xtype:'box',
						autoEl: 'h5',					
						cls:'pad',
						html: t('{author} wrote at {date}').replace('{author}', userStore.data[r.get("createdBy")].displayName).replace('{date}', Ext.util.Format.date(r.get('createdAt'),go.User.dateTimeFormat))
					},
					readMore
				]
			});
		}, this);

		this.doLayout();
	}
});
