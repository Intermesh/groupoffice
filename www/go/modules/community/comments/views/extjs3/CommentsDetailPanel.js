go.modules.comments.CommentsDetailPanel = Ext.extend(Ext.Panel, {
	entityId:null, 
	entity:null,
	section: null,
	height: dp(150),

	growMaxHeight: dp(800),
	title: t("Comments"),
	//
	/// Collapsilbe was turn off because of height recaculation issues in HtmlEditor
	//
	collapsible: true,
	animCollapse: false,

	hideMode: "offsets", //required for htmleditor
	collapseFirst:false,
	layout:'border',	
	titleCollapse: true,
	stateId: "comments-detail",
	initComponent: function () {

		this.on('destroy', function() {
			this.store.destroy();
		}, this);

		this.on("expand", function() {
			this.updateView();

			// this.composer.textField.syncSize();
		}, this);


		if(go.User.isAdmin && this.title) {
			this.tools = [{			
				id: "gear",
				handler: function () {		
					var dlg = new go.modules.comments.Settings();					
					dlg.show(go.User.id);
				}
			}];
		}

		this.store = new go.data.Store({
			fields: [
				'id', 
				'categoryId', 
				'categoryName',
				'entityId', 
				{name: 'createdAt', type: 'date'}, 
				{name: 'modifiedAt', type: 'date'}, 
				'modifiedBy',
				'createdBy', 
				{name: "creator", type: "relation"},
				'text',
				{name: "permissionLevel", type: "int"},
				{name: "labels", type: "relation"}
			],
			entityStore: "Comment"
		});
		
		this.store.on('load', function(store,records,options) {		
			this.updateView(options);
		}, this);

		this.store.on('remove', function() {
			this.updateView();
		}, this);

		this.contextMenu = new Ext.menu.Menu({
			items:[{
				iconCls: 'ic-delete',
				text: t("Delete"),
				handler: function() {

					Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
						if (btn !== "yes") {
							return;
						}
						go.Db.store("Comment").set({destroy: [this.contextMenu.record.id]});
					}, this);
				
				},
				scope:this
			},{
				iconCls: 'ic-edit',
				text: t("Edit"),
				handler: function() {
					var dlg = new go.modules.comments.CommentForm();					
					dlg.load(this.contextMenu.record.id).show();
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
			scrollUp: true
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
					
		go.modules.comments.CommentsDetailPanel.superclass.initComponent.call(this);
	},

	onLoad: function (dv) {
		var id = dv.model_id ? dv.model_id : dv.currentId; //model_id is from old display panel
		var type = dv.entity || dv.model_name || dv.entityStore.entity.name;
		if(this.entityId === id) {
			return;
		}
		
		this.entityId = id;
		this.entity = type;
		this.composer.initEntity(this.entityId, this.entity, this.section);

		this.store.setFilter('entity', {
			entity: this.entity,
			entityId: this.entityId,
			section: this.section
		});
		
		this.store.load();
	},
		
	updateView : function(o) {
		if(this.collapsed || !this.commentsContainer.rendered) {
			return;
		}
		o = o || {};

		this.composer.textField.setValue('');
		var prevStr;
		var initScrollHeight = (this.store.getCount() == this.commentsContainer.pageSize) ? 0 : this.commentsContainer.getEl().dom.scrollHeight,
			 initScrollTop = this.commentsContainer.getEl().dom.scrollTop;

		this.commentsContainer.removeAll();
		this.store.each(function(r) {
			
			var labelText ='', mineCls = r.get("createdBy") == go.User.id ? 'mine' : '';
			var readMore = new go.detail.ReadMore({
				cls: mineCls
			});
			var creator = r.get("creator");
			if(!creator) {
				creator = {
					displayName: t("Unknown user")
				};
			}
			var avatar = {
				xtype:'box',
				autoEl: {tag: 'span','ext:qtip': t('{author} wrote at {date}')
					.replace('{author}', creator.displayName)
					.replace('{date}', Ext.util.Format.date(r.get('createdAt'),go.User.dateTimeFormat))},
				cls: 'photo '+mineCls
			};
			if(creator.avatarId) { 
				avatar.style = 'background-image: url('+go.Jmap.thumbUrl(creator.avatarId, {w: 40, h: 40, zc: 1})+');background-color: transparent;';
			} else {
				avatar.html = go.util.initials(creator.displayName);
				avatar.style = 'background-image: none';
			}

			for(var i = 0, l = r.data.labels.length; i < l; i++){
				labelText += '<i class="icon" title="' + r.data.labels[i].name + '" style="color: #' + r.data.labels[i].color + '">label</i>';
			} 
			
			readMore.setText(r.get('text'));
			readMore.insert(1, {xtype:'box',html:labelText, cls: 'tags ' +mineCls});
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
				
				if(r.data.permissionLevel > go.permissionLevels.read) {
					this.contextMenu.record = r;
					this.contextMenu.showAt(e.xy);
				}

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
			_this.body.setHeight(Math.max(50,Math.min(_this.growMaxHeight,height + _this.composer.getHeight())));
			_this.doLayout();
			scroll.scroll("b", initScrollTop + (scroll.dom.scrollHeight));
		});

	}
});
