go.modules.comments.CommentsDetailPanel = Ext.extend(Ext.Panel, {
	entityId:null, 
	entity:null,
	section: null,
	title: t("Comments"),
	//
	/// Collapsilbe was turn off because of height recaculation issues in HtmlEditor
	//
	collapsible: true,
	animCollapse: false, //htmleditor doesn't work with animCollapse
	showComposer: true,
	hideMode: "offsets", //required for htmleditor
	collapseFirst:false,
	titleCollapse: true,
	bodyCssClass: 'comments-container',
	autoHeight: true,
	large: false,
	stateId: "comments-detail",
	initComponent: function () {


		this.origTitle = this.title;

		this.buttonAlign = 'left';
		this.buttons = [new Ext.Button({
			text:t('Attach file'),
			iconCls: 'ic-file-upload',
			handler: () => {
				go.util.openFileDialog({
					multiple: true,
					autoUpload: true,
					listeners: {
						upload: function(response) {
							const att = this.composer.attachmentBox;
							att.setValue(att.getValue().concat([{
								blobId: response.blobId,
								name: response.name
							}]));
							this.composer.onSync();
						},
						scope: this
					}
				})
			}
		}), '->',this.scrollToTopButton = new Ext.Button({
			xtype: "button",
			iconCls: "ic-arrow-circle-up",
			text: t("Scroll to top"),
			scope: this,
			handler: function() {
				this.ownerCt.body.scrollTo("top");
			}
		})]

		this.on('destroy', function() {
			this.store.destroy();
		}, this);


		this.on("render", () => {

			this.body.dom.addEventListener("click", (e) => {
				if(e.target.tagName == "IMG") {

					const v = new GO.files.ImageViewer();

					v.show([{
						name: e.target.attributes.alt ? e.target.attributes.alt.value : e.target.attributes.src.value,
						src: e.target.attributes.src.value
					}]);
				}
			})
		});

		this.on("expand", function() {
			this.updateView();
		}, this);

		this.on("added", (cont) => {

			cont.ownerCt.on("beforeload", (dv, id) => {
				if(this.composer.textField.isDirty() && this.composer.textField.getValue() != "" && this.composer.textField.getValue() != "<br>") {
					if(confirm(t("You have an unsaved comment. Are you sure you want to discard the comment?"))) {
						this.composer.reset();
						return true;
					}
					return false;
				}
			});
		})


		if(go.User.isAdmin && this.title) {
			this.tools = [{			
				id: "gear",
				handler: function () {
					const dlg = new go.modules.comments.Settings();
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
				{name: 'date', type: 'date'},
				{name: 'modifiedAt', type: 'date'}, 
				'modifiedBy',
				'createdBy', 
				{name: "creator", type: "relation"},
				{name: "modifier", type: "relation"},
				'text',
				{name: "permissionLevel", type: "int"},
				{name: "labels", type: "relation"},
				{name: "attachments"}
			],
			entityStore: "Comment",
			baseParams: {sort: [{property: "date", isAscending:false}]},
			remoteSort: true
		});

		this.store.on("beforeload", () => {
			this.el.mask(t("Loading..."));
		});
		this.store.on('load', function(store,records,options) {		
			this.updateView(options);
			this.el.unmask();
		}, this);

		this.store.on('remove', function() {
			this.updateView();
		}, this);

		this.contextMenu = new Ext.menu.Menu({
			items:[{

				itemId: "delete",
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
				itemId: "edit",
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
				autoScroll:true,
				scope: this,

			})
		];



		if(this.showComposer) {
			this.items.push(this.composer = new go.modules.comments.Composer({
				margins: {left: dp(8), right: dp(8),bottom:dp(8),top:0},
				region:'south',
				height:60
			}));
		}
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
		if(this.composer) {
			this.composer.initEntity(this.entityId, this.entity, this.section);


		}
		this.store.setFilter('entity', {
			entity: this.entity,
			entityId: this.entityId,
			section: this.section
		});



		this.store.load();
	},
		
	updateView : function(o) {

		if(this.lastImages) {
			this.lastImages.forEach(img => {
				URL.revokeObjectURL(img.src);
			});
		}

		if(this.collapsed || !this.commentsContainer.rendered) {
			return;
		}

		if(this.large) {
			this.commentsContainer.el.dom.style.maxHeight = (document.body.offsetHeight * 0.7) + "px";
		}

		o = o || {};

		let badge = "<span class='badge'>" + this.store.getTotalCount() + '</span>';
		this.setTitle(this.origTitle + badge);
		let prevStr = null;

		let dom = this.commentsContainer.getEl().dom;
		this.curScrollPos = dom.scrollTop;
		this.curScrollHeight = dom.scrollHeight;// - dom.clientHeight;

		this.commentsContainer.removeAll();

		const imagePromises = [];

		this.store.each(function(r) {
			let labelText ='', mineCls = r.get("createdBy") == go.User.id ? 'mine' : '';

			let creator = r.get("creator");
			if(!creator) {
				creator = {
					displayName: t("Unknown user")
				};
			}


			let qtip = t('{author} wrote at {date}')
				.replace('{author}', Ext.util.Format.htmlEncode(creator.displayName))
				.replace('{date}', Ext.util.Format.date(r.get('createdAt'),go.User.dateTimeFormat));

			let modifier = r.get("modifier");
			if(!modifier) {
				modifier = {
					displayName: t("Unknown user")
				};
			}
			if(r.get('createdAt').getTime() != r.get('modifiedAt').getTime()) {

				qtip += "\n" + t("Edited by {author} at {date}")
					.replace('{author}', Ext.util.Format.htmlEncode(modifier.displayName))
					.replace('{date}', Ext.util.Format.date(r.get('modifiedAt'),go.User.dateTimeFormat));
			}

			if(r.get('createdAt').getTime() != r.get('date').getTime()) {
				qtip += "\n" + t("The date was changed to {date}")
					.replace('{date}', Ext.util.Format.date(r.get('date'),go.User.dateTimeFormat));
			}

			let avatar = {
				xtype:'box',
				autoEl: {tag: 'span'},
				cls: 'photo '+mineCls
			};

			avatar.html = go.util.avatar(creator.displayName,creator.avatarId);

			avatar.style = {
				cursor: "pointer"
			}
			avatar.listeners = {
				afterrender : (cmp) => {
					cmp.getEl().on("click" , () => {
						go.modules.community.addressbook.lookUpUserContact(creator.id);
					});
				}
			}


			for(let i = 0, l = r.data.labels.length; i < l; i++){
				labelText += '<i class="icon" title="' + r.data.labels[i].name + '" style="color: #' + r.data.labels[i].color + '">label</i>';
			}

			let readMore = new Ext.Container({
				cls: 'go-html-formatted ' + mineCls
			});

			const qs = new go.util.QuoteStripper(r.get("text")),

				quote = qs.getQuote(), quoteId = Ext.id();

			let quoteLess = qs.getBodyWithoutQuote();

			if(quote) {
				quoteLess += '<a class="normal-link" id="' + quoteId + '">' + t("More") + "</a>";
			}

			const content = Ext.create({xtype:'box',html: quoteLess, itemId:"content", cls: 'content ' +mineCls});

			readMore.insert(1, content);
			readMore.insert(1, {xtype:'box',html:labelText, cls: 'tags ' +mineCls});

			if(r.data.attachments && r.data.attachments.length) {
				let atts = "";
				r.data.attachments.forEach(a => {
					atts += `<a class="attachment" target="_blank" title="${a.name}" href="${go.Jmap.downloadUrl(a.blobId, true)}"><span class="filetype filetype-${a.name.substring(a.name.lastIndexOf(".") + 1)}"></span>${a.name}</a>`;
				})
				readMore.insert(2, {xtype: 'box', html: atts, cls: "attachments"});
			}

			// var readMore = new Ext.BoxComponent({
			// 	cls: 'go-html-formatted ' + mineCls,
			// 	html: "<div class='content'>" + r.get('text') + "</div><div class='tags "+mineCls+"'>"+labelText+"</div>"
			// });
			this.commentsContainer.insert(0,{
				xtype:"container",
				cls:'go-messages',
				items: [{
					xtype:'container',
					autoEl: {tag: 'div','title': qtip},
					items: [avatar,readMore]
				},{
					xtype:'box',
					autoEl: 'h6',
					hidden: prevStr === null || prevStr == go.util.Format.date(r.get('date')),
					html: prevStr
				}
				]
			});

			readMore.on('afterrender',function(me){



				me.getEl().on("contextmenu", function(e, target, obj){
					e.stopEvent();

					this.contextMenu.record = r;
					this.contextMenu.showAt(e.xy);

					this.contextMenu.items.get("delete").setDisabled(r.data.permissionLevel < go.permissionLevels.writeAndDelete);
					this.contextMenu.items.get("edit").setDisabled(r.data.permissionLevel < go.permissionLevels.write);

				}, this);

			},this);

			readMore.getComponent("content").on("afterrender" , (content) => {

				imagePromises.push(go.util.replaceBlobImages(content.getEl().dom));


				if(quote) {

					document.getElementById(quoteId).addEventListener("click", () => {
						content.getContentTarget().update(r.get("text"));
					});

				}
			})

			prevStr = go.util.Format.date(r.get('date'));
		}, this);


		// Put a date on top
		this.commentsContainer.insert(0,{
			xtype:"container",
			cls:'go-messages',
			items: [{
				xtype:'box',
				autoEl: 'h6',
				html: prevStr
			}
			]
		});
		this.doLayout();
		this.scrollDown(imagePromises);


	},
	scrollDown : function(imagePromises) {

		this.lastImages = [];

		Promise.all(imagePromises).then((imgs) => {

			// we will clear the memory on the next load or reset.
			this.lastImages = this.lastImages.concat(...imgs);

			const dom = this.commentsContainer.getEl().dom;
			dom.scrollTop = this.curScrollPos + (dom.scrollHeight - this.curScrollHeight);

			if (this.large) {
				this.scrollToTopButton.getEl().scrollIntoView(this.ownerCt.body);
			}
		});

	}
});
