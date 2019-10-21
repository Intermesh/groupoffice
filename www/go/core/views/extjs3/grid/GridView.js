Ext.define('go.grid.GridView', {
  extend: 'Ext.grid.GridView',
	htmlEncode: true,
	totalDisplay: false,
	masterTpl: new Ext.Template(
		'<div class="x-grid3" hidefocus="true">',
				'<div class="x-grid3-viewport">',
						'<div class="x-grid3-header">',
								'<div class="x-grid3-header-inner">',
										'<div class="x-grid3-header-offset" style="{ostyle}">{header}</div>',
								'</div>',
								'<div class="x-clear"></div>',
						'</div>',
						'<div class="x-grid3-scroller">',
								'<div class="x-grid3-body" style="{bstyle}">{body}</div>',
								'<a href="#" class="x-grid3-focus" tabIndex="-1"></a>',
						'</div>',
						'<div class="go-grid-total" title="' + Ext.util.Format.htmlEncode(t("Click to hide")) + '"></div>',
				'</div>',
				'<div class="x-grid3-resize-marker">&#160;</div>',
				'<div class="x-grid3-resize-proxy">&#160;</div>',
		'</div>'),		

		initElements: function() {
			this.callParent(arguments);

			if(this.totalDisplay) {
				this.totalDisplay = this.el.child('div.go-grid-total');
				this.totalDisplay.setRight(this.scrollOffset);
				this.totalDisplay.on("click", function() {
					this.totalDisplay.hide();
				}, this);

				this.setTotalCount(this.totalCount);
			} else{
				var td = this.el.child('div.go-grid-total');
				if(td) {
					td.remove();
				}
			}			
		},

		totalCount: 0,
		setTotalCount: function(c) {
			this.totalCount = c;
			if(Ext.isBoolean(this.totalDisplay)){
				return; //not rendered
			}
			if(c) {
				this.totalDisplay.update(c + " " +t("items"));
				this.totalDisplay.show();
			} else {
				this.totalDisplay.hide();
			}
		}
});