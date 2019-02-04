go.modules.comments.CommentsDetailPanel = Ext.extend(Ext.Panel, {
	entityId:null, 
	entity:null,
	height: dp(150),
	title: t("Comments", "comments"),
	//
	/// Collapsilbe was turn off because of height recaculation issues in HtmlEditor
	//
	//collapsible: true,
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
		
		this.store.on('load', function(store,records,options) {
				
				var userStore = go.Stores.get("User");
								
				var creatorIds = [];
				this.store.each(function(r) {
					if(creatorIds.indexOf(r.get('createdBy')) === -1) {
						creatorIds.push(r.get('createdBy'));
					}
				}, this);
			
				if(userStore.get(creatorIds, function() {
					this.updateView(options);
				},this));
				
			}, this);
							
		go.flux.Dispatcher.register(this);
		
		this.contextMenu = new Ext.menu.Menu({
			items:[{
				iconCls: 'ic-delete',
				text: t("Delete"),
				handler: function() {
					if(confirm(t("Are you sure you want to delete the selected item?"))){
						//return;
						this.store.removeById(this.contextMenu.record.id);
						var _this = this;
						this.store.save(function(){
							_this.updateView();
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
		
		var cntrClass = Ext.extend(Ext.Container,{
			initComponent: function() {
				Ext.Container.superclass.initComponent.call(this);
				Ext.applyIf(this, go.panels.ScrollLoader);
				this.initScrollLoader();
			},
			store: this.store,
			scrollUp: true,
		});

		this.items = [
			this.commentsContainer = new cntrClass({
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
		if(action.type === "User/getUpdates") {
			this.updateView();
		}
	},

	onLoad: function (dv) {
		this.entityId = dv.model_id ? dv.model_id : dv.currentId ;//model_id is from old display panel
		this.entity = dv.entity || dv.model_name || dv.entityStore.entity.name;
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
		
	updateView : function(o) {
		o = o || {};
		this.composer.textField.setValue('');
		var userStore = go.Stores.get("User"), prevStr;
		var initScrollHeight = (this.store.getCount() == this.commentsContainer.pageSize) ? 0 : this.commentsContainer.getEl().dom.scrollHeight,
			 initScrollTop = this.commentsContainer.getEl().dom.scrollTop;

		this.commentsContainer.removeAll();
		this.store.each(function(r) {
			
			var labelText ='', mineCls = r.get("createdBy") == go.User.id ? 'mine' : '';
			var readMore = new go.detail.ReadMore({
				cls: mineCls
			});
			var avatar = {
				xtype:'box',
				autoEl: {tag: 'span','ext:qtip': t('{author} wrote at {date}')
					.replace('{author}', userStore.data[r.get("createdBy")].displayName)
					.replace('{date}', Ext.util.Format.date(r.get('createdAt'),go.User.dateTimeFormat))},
				cls: 'photo '+mineCls
			};
			if(userStore.data[r.get("createdBy")].avatarId) { 
				avatar.style = 'background-image: url('+go.Jmap.downloadUrl(userStore.data[r.get("createdBy")].avatarId)+');';
			} else {
				avatar.html = userStore.data[r.get("createdBy")].displayName.substr(0,1).toUpperCase();
			}

			if(r.json.labelIds) {
				go.Stores.get('CommentLabel').get(r.json.labelIds, function(items){
					for(var i = 0; i < items.length; i++){
						labelText += '<i class="icon" title="'+items[i].name+'" style="color: #'+items[i].color+'">label</i>';
					}
				});
			}
			readMore.setText(r.get('text'));
			readMore.add({xtype:'box',html:labelText, cls: 'tags ' +mineCls});
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
						items: [avatar,readMore]
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
		var height = 7; // padding on composer
		this.commentsContainer.items.each(function(item,i) {
			height += item.getOuterSize().height;
		});
		var _this = this;
		setTimeout(function(){
			
			var scroll = _this.commentsContainer.getEl();
			_this.body.setHeight(Math.max(50,Math.min(400,height + _this.composer.getHeight())));
			_this.doLayout();
			scroll.scroll("b", initScrollTop + (scroll.dom.scrollHeight));
		});

	}
});
