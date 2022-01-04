/**
 * Supports extra features:
 *
 * {
 * 	totalDisplay: true,
 *
 * 	actionConfig: {
 * 		handler: function(btn) {
 * 			// btn.rowIndex is available
 * 		},
 * 		scope: this,
 * 		menu:  [{
 * 				itemId: "view",
 * 				iconCls: 'ic-edit',
 * 				text: t("Edit"),
 * 				handler: function(item) {
 * 					//use item.parentMenu.rowIndex to find record
 * 					var record = this.store.getAt(item.parentMenu.rowIndex);
 * 					this.edit(record.id);
 * 				},
 * 				scope: this
 * 			}]
 * 		}
 * 	}
 */
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

		this.initActionButton();
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
	},

	onRowSelect : function(row) {

			this.addRowClass(row, this.selectedRowClass);

			this.showActionButton(row);

	},

	initActionButton : function() {
		if(this.actionConfig) {
			this.actionBtn = new Ext.Button({
				menuAlign: 'tr-br?',
				iconCls: this.actionConfig.iconCls || 'ic-more-vert',
				cls: "primary",
				style: "position:absolute; z-index: 99999999; left: -9999999; top: -9999999",
				renderTo: this.el,
				handler:  (btn, e) => {

					if(this.actionConfig.handler) {
						this.actionConfig.handler.call(this.actionConfig.scope || this, btn, e);
					}
				},
				scope: this.actionConfig.scope,
				menu: this.actionConfig.menu
			});
			// this.scroller.dom.addEventListener("scroll", () => {
			// 	this.actionBtn.hide()
			// 	if(this.actionBtn.menu) {
			// 		this.actionBtn.menu.hide();
			// 	}
			// });
		}
	},

	showActionButton : function(rowIndex) {

  	if(!this.actionBtn) {
  		return;
		}

		this.actionBtn.show();

  	var rowEl = Ext.get(this.getRow(rowIndex));

		var offset = (rowEl.getHeight() - this.actionBtn.getHeight()) / 2;

		var y = rowEl.getY() + offset;

		var x = this.scroller.getX() + this.scroller.getWidth() - dp(40);
		if(this.scrollOffset) {
			x -= this.scrollOffset;
		}
		var pos = [x,y];
		this.actionBtn.getEl().setXY(pos);
		this.actionBtn.rowIndex = rowIndex;
		if(this.actionBtn.menu) {
			this.actionBtn.menu.rowIndex = rowIndex;
		}

		if(GO.util.isMobileOrTablet()) {
			this.actionBtn.showMenu();
		} else
		{
			if(this.actionBtn.menu) {
				this.actionBtn.menu.hide();
			}
		}

		return this.actionBtn;
	},


	// onRowOut : function(e, target) {
	// 	var row = this.findRowIndex(target);
	//
	// 	if (row !== false && !e.within(this.getRow(row), true)) {
	// 		this.removeRowClass(row, this.rowOverCls);
	//
	// 	}
	//
	// 	if(this.actionBtn && !e.within(this.actionBtn.el, true)) {
	// 		this.actionBtn.hide();
	// 	}
	//
	// },

	destroy : function() {
		this.callParent(arguments);
		if(this.actionBtn) {
			this.actionBtn.destroy();
		}
	}
});