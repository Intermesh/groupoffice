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
								'<a class="x-grid3-focus" tabIndex="-1"></a>',
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
		} else {
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
	},
	doRender : function(columns, records, store, startRow, colCount, stripe) {
		let templates = this.templates,
			cellTemplate = templates.cell,
			rowTemplate = templates.row,
			last = colCount - 1,
			// buffers
			rowBuffer = [],
			colBuffer = [],
			rowParams,
			meta = {},
			len = records.length,
			alt,
			column,
			record, rowIndex;
		//build up each row's HTML
		for (let j = 0; j < len; j++) {
			let tstyle = 'width:' + this.getTotalWidth() + ';', rowCFStyle = false;

			record    = records[j];
			colBuffer = [];

			rowIndex = j + startRow;

			if(!rowCFStyle) {
				rowCFStyle = this.getRowCFStyle(record, columns);
				if(rowCFStyle) {
					tstyle += rowCFStyle;
				}
			}

			//build up each column's HTML
			for (let i = 0; i < colCount; i++) {
				column = columns[i];

				meta.id    = column.id;
				meta.css   = i === 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
				meta.attr  = meta.cellAttr = '';
				meta.style = column.style;
				meta.value = column.renderer.call(column.scope, record.data[column.name], meta, record, rowIndex, i, store);

				if (Ext.isEmpty(meta.value)) {
					meta.value = '&#160;';
				}

				if (this.markDirty && record.dirty && typeof record.modified[column.name] != 'undefined') {
					meta.css += ' x-grid3-dirty-cell';
				}

				colBuffer[colBuffer.length] = cellTemplate.apply(meta);
			}

			alt = [];
			//set up row striping and row dirtiness CSS classes
			if (stripe && ((rowIndex + 1) % 2 === 0)) {
				alt[0] = 'x-grid3-row-alt';
			}

			if (record.dirty) {
				alt[1] = ' x-grid3-dirty-row';
			}
			rowParams = {tstyle: tstyle};
			rowParams.cols = colCount;

			if (this.getRowClass) {
				alt[2] = this.getRowClass(record, rowIndex, rowParams, store);
			}

			rowParams.alt   = alt.join(' ');
			rowParams.cells = colBuffer.join('');

			rowBuffer[rowBuffer.length] = rowTemplate.apply(rowParams);
		}

		return rowBuffer.join('');
	},

	getRowCFStyle: function(record, columns) {
		for (let i = 0, l = columns.length; i < l; i++) {
			const column = columns[i];
			const val = record.data[column.name]

			if(!go.util.empty(val) && typeof column.scope.rowRenderer === "function") {
				return column.scope.rowRenderer(val);
			}


		}
		return false;
	}
});