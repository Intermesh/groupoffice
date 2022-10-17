/**
 * Supports extra features:
 *
 * Row rendering options in custom fields
 */
Ext.define('GO.grid.GridView', {
	extend: 'Ext.grid.GridView',

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
					console.log("GO.grid.GridView rowCFStyle: " + rowCFStyle);
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