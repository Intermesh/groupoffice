go.modules.comments.Composer = Ext.extend(go.form.EntityPanel, {
	
	entityStore: go.Stores.get("Comment"),
	layout: 'hbox',
	cls:'go-form new-message',
	layoutConfig: {
		align: 'middle'
	},
	
	initComponent : function() {
		
		this.store = new go.data.Store({
			fields: ['id', 'name', 'color'],
			entityStore: go.Stores.get("CommentLabel")
		});
		
		this.addBtn = new Ext.Button({
			tooltip: t('Add'),
			iconCls: 'ic-add',
			menu: {
				items:[{
					iconCls: 'ic-attach-file', 
					text: t('Select file')
				},{
					iconCls: 'ic-file-upload', 
					text: t('Upload file')
				},'-'
//				},{
//					iconCls: 'ic-link', 
//					text: t('Add Link')
//				},{
//					iconCls: 'ic-label', 
//					text: t('Add Label')
//				}
			]
			}
		});
		this.store.on('load', function() {
			this.loadLabels();
		},this);
		this.store.load();
		
	
		this.textField = new go.form.HtmlEditor({
			//enableColors: false,
			enableFont: false,
			enableFontSize: false,
			enableAlignments: false,
			enableSourceEdit: false,
			hideToolbar: true,
			emptyText: 'Add comment...',
			allowBlank: false,
			plugins: [go.form.HtmlEditor.emojiPlugin],
			height: 35,
			name: 'text',
			boxMaxHeight: dp(400),
			boxMinHeight:35
		});
		this.textField.on('sync', function(me, html) {
			//me.onResize();
			var body = me.getEditorBody(),
			composer = this;
			body.style.height = 'auto';
			body.style.display = 'inline-block';
			body.style.width = '100%';
			body.style.minHeight = '17px';
			body.style.margin = '8px 0';
			
			setTimeout(function() {
				var h =  Math.max(me.boxMinHeight,Math.min(body.offsetHeight +16, me.boxMaxHeight)); // 400  max height
				if(h > 40) {
					me.tb.show();
				} else {
					me.tb.hide();
				}
				me.ownerCt.setHeight(h + me.tb.el.getHeight());
				composer.grow();
			},0)
			
      },this);
		
		
		this.sendBtn = new Ext.Button({
			tooltip: t('Send'),
			iconCls: 'ic-send',
			handler: function(){ 
				this.submit(); 
				this.textField.syncValue();
			},
			scope: this
		});
		
		this.items = [{xtype:'hidden',name:'entity'},
			{xtype:'hidden', name: 'entityId'},
			this.addBtn,
			this.middleBox = new Ext.Container({
				layout:'vbox',
				align:'stretch',
				flex:1,
				items: [
					this.commentBox = new Ext.Container({
						layout:'fit',
						frame: true,
						items:[this.textField]
					}),
					this.labelBox = new Ext.Container({style:'padding-bottom:4px'}),
					this.attachmentBox = new Ext.Container()
				]
			}),
			this.sendBtn
		]
		
		go.modules.comments.Composer.superclass.initComponent.call(this);

	},
	
	grow: function(){
		var totalHeight = this.commentBox.getHeight() + this.labelBox.getHeight() + this.attachmentBox.getHeight();
		console.log(totalHeight, this.labelBox.getHeight());
		this.setHeight(totalHeight);
		this.middleBox.setHeight(totalHeight);
		this.ownerCt.doLayout();
		this.doLayout();
	},
	
	initEntity : function(entityId,entity){
		this.form.findField('entity').setValue(entity);
		this.form.findField('entityId').setValue(parseInt(entityId));
	},
	
	loadLabels : function() {
		this.store.each(function(r) {
			this.addBtn.menu.add({
				text: r.get('name'),
				iconCls: 'ic-label',
				record: r,
				iconStyle: 'color: #'+r.get('color'),
				handler: function(me) {
					this.labelBox.add({
						xtype:'box',
						autoEl: {
							tag: 'span',
							html: '<span>o</span> '+me.record.get('name')
						},
						style: 'border: 1px solid black'
					});
					this.doLayout();
					this.grow();
				},
				scope:this
			});

		}, this);
	}
	
	
});
