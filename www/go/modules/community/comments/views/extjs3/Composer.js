go.modules.comments.Composer = Ext.extend(go.form.EntityPanel, {
	
	entityStore: "Comment",
	layout: 'border',
	cls:'go-form new-message',
	autoScroll: false,
	minComposerHeight: dp(32),

	initComponent : function() {

		this.store = new go.data.Store({
			fields: ['id', 'name', 'color'],
			entityStore: "CommentLabel"
		});
		
		this.addBtn = new Ext.Button({
			disabled: true,
			tooltip: t('Add labels'),
			iconCls: 'ic-add',
			region:"west",
			style: 'max-width: 32px',
			menu: {
				items:[]
			}
		});
		this.store.on('load', function() {
			this.loadLabels();
			if(this.store.getTotalCount() > 0) {
				this.addBtn.enable();
			}
		},this);

		this.textField = new go.form.HtmlEditor({
			iframePad: 0,
			grow: true,
			growMin: dp(32),
			//enableColors: false,
			enableFont: false,
			headingsMenu: false,
			enableFontSize: false,
			enableAlignments: false,
			enableSourceEdit: true,
			// toolbarHidden: true,
			// emptyText: t('Add comment')+'...',
			allowBlank: false,
			plugins: [new GO.plugins.HtmlEditorImageInsert(), go.form.HtmlEditor.emojiPlugin],
			height: this.minComposerHeight,
			name: 'text',
			boxMaxHeight: 200,
			boxMinHeight: this.minComposerHeight,
			listeners: {
				ctrlenter: function() {
					this.sendBtn.handler.call(this);
				},

				attach: ( field, response, file, imgEl) => {
					if(imgEl) {
						return;
					}

					this.attachmentBox.setValue(this.attachmentBox.getValue().concat([{
						blobId: response.blobId,
						name: response.name
					}]))

					this.onSync();
				},
				scope: this
			}
		});
		this.textField.on('sync', this.onSync,this);	
		this.textField.on("initialize", this.onSync, this);
		this.textField.on('afterrender', this.onSync,this);
		
		this.sendBtn = new Ext.Button({
			region:"east",
			tooltip: t('Send'),
			iconCls: 'ic-send',
			handler: async function(){
				if (Ext.isEmpty(this.textField.getValue())) {
					this.textField.focus();
					return false;
				}
				this.sendBtn.setDisabled(true);

				try {
					await this.submit();
				} catch(e) {
					GO.errorDialog.show(e.message);
				}
				this.reset(); // otherwise it will update the second time
				this.textField.setHeight(this.minComposerHeight);

				this.onSync()
				this.textField.syncValue();
				this.ownerCt.doLayout();
				this.doLayout();

				this.textField.focus();

				this.sendBtn.setDisabled(false);

			},
			scope: this
		});
		
		this.items = [

			this.addBtn,
			this.middleBox = new Ext.Container({
				region:"center",
				layout:'anchor',
				defaults: {
					anchor: "100%"
				},

				items: [
					this.commentBox = new Ext.Container({
						layout: "fit",
						items:[this.textField]
					}),
					this.chips = new go.form.Chips({
						name: 'labels',
						entityStore: 'CommentLabel',
						style:'padding-bottom:4px',
						store: this.store
					}),
					this.attachmentBox = new go.form.FormGroup({
						hideBbar: true,
						name:"attachments",
						startWithItem: false,
						itemCfg : {
							items: [{
								hideLabel: true,
								xtype: "plainfield",
								name: "name",
								submit: true,
								renderer : function(v, field) {
									return Ext.util.Format.htmlEncode(v);
								}
							},{
								hideLabel: true,
								xtype: "hidden",
								name: "blobId"
							}]
						}
					})
				]
			}),
			this.sendBtn
		]
		
		this.textField.on('afterrender', function() {
			this.store.load();
			this.onSync();
		}, this);
		
		
		go.modules.comments.Composer.superclass.initComponent.call(this);

	},

	onSync : function(me) {
		setTimeout(() => {
			this.setHeight(this.commentBox.getHeight() + this.chips.getHeight() + this.attachmentBox.getHeight());
		}, 0);
	},
	
	//grow: function(){

		//this.setHeight(this.commentBox.getHeight() + this.chips.getHeight() + this.attachmentBox.getHeight());
		// var totalHeight = this.commentBox.getHeight() + this.chips.getHeight() + this.attachmentBox.getHeight();
		// this.setHeight(totalHeight);
		// this.middleBox.setHeight(this.getHeight() + 4);
		//var headerHeight = this.ownerCt.header ? dp(48) : 0;
		// console.log(this.ownerCt.commentsContainer.getEl().dom.scrollHeight, this.getHeight(), headerHeight);
		// var h = Math.min(this.ownerCt.growMaxHeight, this.ownerCt.commentsContainer.getEl().dom.scrollHeight + this.getHeight() + headerHeight + dp(8));
		// this.ownerCt.setHeight(h);
		// this.ownerCt.doLayout();
		// this.ownerCt.scrollDown();
	//},
	
	initEntity : function(entityId,entity, section) {
		this.setValues({
			entityId: entityId,
			entity: entity,
			section: section
		});
	},
	
	loadLabels : function() {
		this.addBtn.menu.removeAll();
		this.store.each(function(r) {
			this.addBtn.menu.add({
				text: r.get('name'),
				iconCls: 'ic-label',
				record: r,
				iconStyle: 'color: #'+r.get('color'),
				handler: function(me) {
					this.chips.dataView.store.add([me.record]);
					//this.loadLabels(); //redraw
					this.doLayout();
					this.onSync();
				},
				scope:this
			});

		}, this);
	}
	
	
});
