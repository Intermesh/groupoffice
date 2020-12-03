go.detail.ReadMore = Ext.extend(Ext.Container, {	
	initComponent: function() {
		this.contentConfig = this.contentConfig || {};

		this.items =  [Ext.apply(this.contentConfig,{
			xtype: 'box',
			itemId: 'content',
			cls: 'content',
		}), {
			xtype: "container",
			cls: "more",
			items: [{
					xtype: "button",
					text: t("More"),
					iconCls: "ic-expand-more",
					handler: function (btn) {
						this.getEl().dom.classList.add('expanded');
					},
					scope: this
				}]
		}];
	
		go.detail.ReadMore.superclass.initComponent.call(this);
		
		this.addClass('text-crop');
		
		this.getComponent('content').on('render', function() {
			this.setText(this.text);
		}, this, {singe: true});
	},
	
	text: "",

	setText: function (text) {
		this.text = text;
		if(this.rendered) {
			this.getEl().dom.classList.remove('expanded');
			var content = this.getComponent('content');
			if(content) {
				content.update(text);
			} else
			{
				console.log("wtf?");
			}
		} 
	}
});

Ext.reg('readmore', go.detail.ReadMore);
