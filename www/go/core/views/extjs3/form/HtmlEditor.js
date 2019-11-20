go.form.HtmlEditor = Ext.extend(GO.form.HtmlEditor, {
	 
	iframePad:0,
	
	emptyTextRegex: '<span[^>]+[^>]*>{0}<\/span>',
   emptyTextTpl: '<span style="color:#ccc;">{0}</span>',
   emptyText: '',
	hideToolbar: false,

	initComponent: function() {
		go.form.HtmlEditor.superclass.initComponent.apply(this);
		
		this.on('initialize', function(){
			if(this.hideToolbar) {
				this.tb.hide();
			}
			if(Ext.isEmpty(this.emptyText)) {
				return;
			}
			// Ext.EventManager.on(this.getEditorBody(),{
			// 	focus:this.handleEmptyText,
			// 	blur:this.applyEmptyText,
			// 	scope:this
			// });
			
		},this);
	},
	
	createToolbar: Ext.form.HtmlEditor.prototype.createToolbar.createSequence(function (editor) {
		this.tb.enableOverflow = true;
	}),

	// applyEmptyText: function() {
	// 	var value = this.getValue();
	// 	if(Ext.isEmpty(value)) {
	// 		var emptyText = go.util.Format.string(this.emptyTextTpl,this.emptyText);
	// 		go.form.HtmlEditor.superclass.setValue.apply(this, [emptyText]);
	// 	}
	// },
	// handleEmptyText: function() {
	// 	var value = this.getValue(),
	// 		regex = new RegExp(go.util.Format.string( this.emptyTextRegex,this.emptyText ) );
	// 	if(!Ext.isEmpty(value) && regex.test(value)) {
	// 		go.form.HtmlEditor.superclass.setValue.apply(this, ['']);
	// 	}
	// },
	// setValue : function(v){
	// 	go.form.HtmlEditor.superclass.setValue.apply(this, arguments);
	// 	//this.applyEmptyText();
	// 	return this;
  //  },

	setDesignMode : function(readOnly){
		this.getEditorBody().contentEditable = readOnly;
   },
	
	onResize : function(w, h){
	  Ext.form.HtmlEditor.superclass.onResize.apply(this, arguments);
	  if(this.el && this.iframe){
			if(Ext.isNumber(w)){
				 var aw = w - this.wrap.getFrameWidth('lr');
				 this.el.setWidth(aw);
				 this.tb.setWidth(aw);
				 this.iframe.style.width = Math.max(aw, 0) + 'px';
			}
			if(Ext.isNumber(h)){
				 var ah = h - this.wrap.getFrameWidth('tb') - this.tb.el.getHeight();
				 this.el.setHeight(ah);
				 this.iframe.style.height = Math.max(ah, 0) + 'px';
				 var bd = this.getEditorBody();
				 if(bd){
					 // bd.style.height = Math.max((ah - (this.iframePad*2)), 0) + 'px';
				 }
			}
		}
   }
});

go.form.HtmlEditor.emojiPlugin = {
	init: function(cmp){
		cmp.on('render', function() {
			cmp.getToolbar().addButton(new Ext.Button({
				tooltip: t('Emoji'),
				overflowText: t('Emoji'),
				iconCls: 'ic-mood',
				menu: {
					xtype:'emojimenu',
					handler: function(menu,emoji) {
						cmp.insertAtCursor(emoji);
					},
					scope: this
				}
			}));
		}, this);
	}
};
