Ext.define('go.grid.GridView', {
  extend: 'Ext.grid.GridView',
  htmlEncode: true,
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

      this.totalDisplay = this.el.child('div.go-grid-total');
      this.totalDisplay.setRight(this.scrollOffset);
			this.totalDisplay.on("click", function() {
				this.totalDisplay.hide();
			}, this);
			
		}
});