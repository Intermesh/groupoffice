Ext.ns("Ext.ux.form.HtmlEditor");

Ext.ux.form.HtmlEditor.NEWTablePlugin = function(config) {
	this.sessionId = Math.random().toString(36).substring(2, 10);
	Ext.apply(this, config);
	Ext.ux.form.HtmlEditor.NEWTablePlugin.superclass.constructor.call(this);
};

Ext.extend(Ext.ux.form.HtmlEditor.NEWTablePlugin, Ext.util.Observable, {
	// Initialize plugin
	init: function(editor) {
		var me = this;
		me.editor = editor;
		me.editor.on('render', me.onEditorRender, me);

		me.editor.on('sync', function() {
			// After syncing (switching modes), reattach resize handlers to our tables
			var doc = editor.getDoc();
			if (doc) {
				var tables = doc.getElementsByTagName('table');
				Ext.each(tables, function(table) {
					// Only add resize handlers to tables with our session ID
					if (table.id && table.id.indexOf('GO-' + me.sessionId) === 0) {
						me.addResizeHandle(table);
					}
				});
			}
		});

		// Override the pushValue method to clean up resize handles before syncing
		var originalPushValue = editor.pushValue;
		editor.pushValue = function() {
			// Remove all resize handles before syncing
			var doc = editor.getDoc();
			if (doc) {
				var handles = doc.querySelectorAll('.resize-handle');
				Ext.each(handles, function(handle) {
					handle.parentNode.removeChild(handle);
				});
			}
			// Call original pushValue method
			originalPushValue.call(editor);
		};
	},

	getSelectedTable: function() {
		var me = this;
		var doc = me.editor.getDoc();
		var selection = doc.getSelection();
		if (!selection.rangeCount) return null;

		var range = selection.getRangeAt(0);
		var element = range.commonAncestorContainer;

		while (element && element.nodeName !== 'TABLE') {
			element = element.parentNode;
		}

		// Only return the table if it's one of ours
		if (element && element.nodeName === 'TABLE' &&
			element.id && element.id.startsWith('GO-' + me.sessionId)) {
			return element;
		}

		return null;
	},

	onEditorRender: function() {
		var me = this;

		if (me.editor.getToolbar()) {
			me.editor.getToolbar().addButton({
				itemId: 'table_chart',
				tooltip: {
					title: t("Table"),
					text: t("Table")
				},
				overflowText: t("Table"),
				handler: me.showTableConfigDialog,
				iconCls: 'ic-view-comfy',
				scope: me
			});
		}

		// Dynamically add CSS for the custom toolbar icon
		var style = document.createElement('style');
		style.type = 'text/css';
		style.innerHTML = [
			'.icons-toolbar-tblicon .x-btn-text{',
			'    font-family: "Icons";',
			'    font-size: 17px;',
			'    color: black;',
			'    display: flex;',
			'    align-items: center;',
			'    justify-content: center;',
			'    padding: 0px;',
			'    border: 0px;',
			'    overflow: hidden;',
			'}'
		].join('');
		document.head.appendChild(style);

		// Add handler for source edit mode toggle
		if (me.editor.sourceEditMode) {
			var doc = me.editor.getDoc();
			var handles = doc.querySelectorAll('.resize-handle');
			Ext.each(handles, function(handle) {
				handle.parentNode.removeChild(handle);
			});
		}
	},

	showTableConfigDialog: function() {
		var me = this;
		var table = me.getSelectedTable();

		if (!me.tableConfigWindow) {
			me.rowsField = new Ext.form.NumberField({ fieldLabel: 'Rows', allowBlank: false, minValue: 1, value: 2 });
			me.columnsField = new Ext.form.NumberField({ fieldLabel: 'Columns', allowBlank: false, minValue: 1, value: 2 });
			me.captionField = new Ext.form.TextField({ fieldLabel: 'Caption' });
			me.summaryField = new Ext.form.TextField({ fieldLabel: 'Summary' });
			me.cellSpacingField = new Ext.form.NumberField({ fieldLabel: 'Cell Spacing', minValue: 0, value: 1 });
			me.cellPaddingField = new Ext.form.NumberField({ fieldLabel: 'Cell Padding', minValue: 0, value: 1 });
			me.bordersCheckbox = new Ext.form.Checkbox({ fieldLabel: 'Borders', checked: true });
			me.alignmentCombo = new Ext.form.ComboBox({
				fieldLabel: 'Alignment',
				store: new Ext.data.ArrayStore({
					fields: ['value'],
					data: [['left'], ['center'], ['right']]
				}),
				displayField: 'value',
				valueField: 'value',
				mode: 'local',
				editable: false,
				triggerAction: 'all',
				value: 'left'
			});

			me.tableConfigWindow = new Ext.Window({
				title: 'Insert/Modify Table',
				width: 400,
				modal: true,
				layout: 'form',
				bodyStyle: 'padding:10px',
				closeAction: 'hide',
				items: [{
					layout: 'column',
					defaults: { layout: 'form', columnWidth: 0.5, bodyStyle: 'padding:5px' },
					items: [
						{ items: [me.rowsField, me.captionField, me.cellSpacingField, me.bordersCheckbox] },
						{ items: [me.columnsField, me.summaryField, me.cellPaddingField, me.alignmentCombo] }
					]
				}],
				buttons: [{
					text: 'Insert/Update Table',
					handler: me.insertOrUpdateTable,
					scope: me
				}, {
					text: 'Cancel',
					handler: function() { me.tableConfigWindow.hide(); }
				}]
			});
		}

		if (table) {
			me.populateTableConfigDialog(table);
		} else {
			me.rowsField.setValue(2);
			me.columnsField.setValue(2);
			me.captionField.setValue('');
			me.summaryField.setValue('');
			me.cellSpacingField.setValue(1);
			me.cellPaddingField.setValue(1);
			me.bordersCheckbox.setValue(true);
			me.alignmentCombo.setValue('left');
		}

		me.tableConfigWindow.show();
	},

	populateTableConfigDialog: function(table) {
		var me = this;
		var caption = table.getElementsByTagName('caption').length ?
			table.getElementsByTagName('caption')[0].textContent : '';
		var summary = table.getAttribute('summary') || '';
		var cellSpacing = table.getAttribute('cellspacing') || 1;
		var cellPadding = table.getAttribute('cellpadding') || 1;
		var borders = table.getAttribute('border') === '1';
		var alignment = table.getAttribute('align') || 'left';

		me.rowsField.setValue(table.rows.length);
		me.columnsField.setValue(table.rows[0] ? table.rows[0].cells.length : 0);
		me.captionField.setValue(caption);
		me.summaryField.setValue(summary);
		me.cellSpacingField.setValue(cellSpacing);
		me.cellPaddingField.setValue(cellPadding);
		me.bordersCheckbox.setValue(borders);
		me.alignmentCombo.setValue(alignment);
	},

	insertOrUpdateTable: function() {
		var me = this;
		var rows = me.rowsField.getValue();
		var columns = me.columnsField.getValue();
		var caption = me.captionField.getValue();
		var summary = me.summaryField.getValue();
		var cellSpacing = me.cellSpacingField.getValue();
		var cellPadding = me.cellPaddingField.getValue();
		var borders = me.bordersCheckbox.getValue() ? 1 : 0;
		var alignment = me.alignmentCombo.getValue();

		// Get the FontSelector plugin instance
		var fontSelector = me.editor.plugins.filter(function(plugin) {
			return plugin.ptype === 'htmleditorfontselector';
		})[0];

		var currentFontFamily = fontSelector ? fontSelector.currentFontFamily : 'Helvetica';
		var currentFontSize = fontSelector ? fontSelector.currentFontSize : '14px';

		var existingTable = me.getSelectedTable();

		if (existingTable) {
			existingTable.setAttribute('summary', summary);
			existingTable.setAttribute('cellspacing', cellSpacing);
			existingTable.setAttribute('cellpadding', cellPadding);
			existingTable.setAttribute('border', borders);
			existingTable.style.marginBottom = '1em';

			if (caption) {
				var captionEl = existingTable.getElementsByTagName('caption')[0];
				if (!captionEl) {
					captionEl = existingTable.createCaption();
				}
				captionEl.textContent = caption;
			} else {
				var existingCaption = existingTable.getElementsByTagName('caption')[0];
				if (existingCaption) {
					existingTable.removeChild(existingCaption);
				}
			}

			me.adjustTableRowsAndColumns(existingTable, rows, columns, alignment, currentFontFamily, currentFontSize);
			me.addResizeHandle(existingTable);
			me.editor.syncValue();
		} else {
			var totalWidth = columns * 100;
			var tableId = 'GO-' + me.sessionId + '-' + new Date().getTime();
			tableHTML = '<table id="' + tableId + '" ' +
				'border="' + borders + '" ' +
				'cellspacing="' + cellSpacing + '" ' +
				'cellpadding="' + cellPadding + '" ' +
				'align="' + alignment + '" ' +
				(summary ? ' summary="' + summary + '"' : '') +
				' style="' +
				'width: ' + totalWidth + 'px !important; ' +
				'margin-bottom: 1em !important; ' +
				'border-collapse: separate !important; ' +
				'border-spacing: 0 !important; ' +
				'table-layout: fixed !important;">';


			if (caption) {
				tableHTML += '<caption>' + caption + '</caption>';
			}

			for (var i = 0; i < rows; i++) {
				tableHTML += '<tr>';
				for (var j = 0; j < columns; j++) {
					tableHTML += '<td style="text-align: ' + alignment +
						'; width: 100px; height: 30px; border: 1px solid #ddd; ' +
						' font-family: ' + currentFontFamily + ';' +
						' font-size: ' + currentFontSize + ';' +
						'-webkit-text-size-adjust: 100% !important; ' +
						'-moz-text-size-adjust: 100% !important; ' +
						'-ms-text-size-adjust: 100% !important; ' +
						'text-size-adjust: 100% !important;">&nbsp;</td>';
				}
				tableHTML += '</tr>';
			}
			tableHTML += '</table>';

			me.editor.insertAtCursor(tableHTML);
			me.editor.syncValue();

			setTimeout(function() {
				var doc = me.editor.getDoc();
				var newTable = doc.getElementById(tableId);
				if (newTable) {
					me.addResizeHandle(newTable);
					me.editor.syncValue();
					var cells = newTable.getElementsByTagName('td');
					Ext.each(cells, function(cell) {
						cell.style.width = '100px';
						cell.style.height = '30px';
					});
				}
			}, 0);

		}

		me.tableConfigWindow.hide();
	},

	adjustTableRowsAndColumns: function(table, rows, columns, alignment, fontFamily, fontSize) {
		var currentRows = table.rows.length;
		var currentCols = table.rows[0] ? table.rows[0].cells.length : 0;

		table.style.width = (columns * 100) + 'px';

		if (rows > currentRows) {
			for (var i = currentRows; i < rows; i++) {
				var newRow = table.insertRow();
				for (var j = 0; j < columns; j++) {
					var newCell = newRow.insertCell();
					newCell.innerHTML = '&nbsp;';
					newCell.style.width = '100px';
					newCell.style.height = '30px';
					newCell.style.textAlign = alignment;
				}
			}
		} else if (rows < currentRows) {
			for (var i = currentRows - 1; i >= rows; i--) {
				table.deleteRow(i);
			}
		}

		for (var i = 0; i < rows; i++) {
			var row = table.rows[i];
			var currentColsInRow = row.cells.length;

			if (columns > currentColsInRow) {
				for (var j = currentColsInRow; j < columns; j++) {
					var newCell = row.insertCell();
					newCell.innerHTML = '&nbsp;';
					newCell.style.width = '100px';
					newCell.style.height = '30px';
					newCell.style.textAlign = alignment;
				}
			} else if (columns < currentColsInRow) {
				for (var j = currentColsInRow - 1; j >= columns; j--) {
					row.deleteCell(j);
				}
			}

			for (var j = 0; j < columns; j++) {
				var cell = row.cells[j];
				cell.style.width = '100px';
				cell.style.height = '30px';
				cell.style.fontFamily = fontFamily;
				cell.style.fontSize = fontSize;
				cell.style.textAlign = alignment;
				cell.style.border = '1px solid #ddd';
				//cell.style.padding = '8px';
				cell.style.webkitTextSizeAdjust = '100%';
				cell.style.mozTextSizeAdjust = '100%';
				cell.style.msTextSizeAdjust = '100%';
				cell.style.textSizeAdjust = '100%';
			}
		}
	},

	addResizeHandle: function(table) {
		if (!table.id || !table.id.startsWith('GO-' + this.sessionId)) {
			return;
		}

		var me = this;
		var editorDoc = me.editor.getDoc();
		var editorWin = me.editor.getWin();
		var resizeHandle = null;
		var deleteHandle = null;

		// Store the original width as a data attribute when first adding handles
		if (!table.getAttribute('data-original-width')) {
			table.setAttribute('data-original-width', table.offsetWidth);
		}
		var originalWidth = parseInt(table.getAttribute('data-original-width'));
		var columnCount = table.rows[0].cells.length;
		var columnWidth = Math.floor(originalWidth / columnCount);

		// Set initial fixed layout
		table.style.cssText += [
			'table-layout: fixed !important;',
			'width: ' + originalWidth + 'px !important;',
			'position: relative !important;',
			'border-collapse: separate !important'
		].join(';');

		function createHandles() {
			// Remove any existing handles first
			removeHandles();

			// Create resize handle
			resizeHandle = editorDoc.createElement('div');
			resizeHandle.className = 'resize-handle';
			resizeHandle.setAttribute('data-editor-helper', 'true');

			resizeHandle.style.cssText = [
				'width: 10px !important',
				'height: 10px !important',
				'background-color: #0066cc !important',
				'border: 1px solid #003366 !important',
				'position: absolute !important',
				'cursor: nwse-resize !important',
				'z-index: 9999 !important',
				'right: -5px !important',
				'bottom: -5px !important',
				'display: block !important',
				'pointer-events: auto !important'
			].join(';');

			// Create delete handle
			deleteHandle = editorDoc.createElement('div');
			deleteHandle.className = 'delete-handle';
			deleteHandle.setAttribute('data-editor-helper', 'true');

			deleteHandle.style.cssText = [
				'width: 10px !important',
				'height: 10px !important',
				'background-color: #cc0000 !important',
				'border: 1px solid #660000 !important',
				'position: absolute !important',
				'cursor: pointer !important',
				'z-index: 9999 !important',
				'right: -5px !important',
				'top: -5px !important',
				'display: block !important',
				'pointer-events: auto !important'
			].join(';');

			// Insert handles as direct children of table
			table.insertBefore(resizeHandle, table.firstChild);
			table.insertBefore(deleteHandle, table.firstChild);

			// Ensure all cells maintain the correct width
			Ext.each(table.rows, function(row) {
				Ext.each(row.cells, function(cell) {
					cell.style.width = columnWidth + 'px';
				});
			});
		}

		function removeHandles() {
			var handles = table.querySelectorAll('.resize-handle, .delete-handle');
			handles.forEach(function(handle) {
				if (handle && handle.parentNode) {
					handle.parentNode.removeChild(handle);
				}
			});
			resizeHandle = null;
			deleteHandle = null;
		}

		function deleteTable(e) {
			e.preventDefault();
			e.stopPropagation();

			if (table && table.parentNode) {
				table.parentNode.removeChild(table);
				me.editor.syncValue();
			}
		}

		function startResize(e) {
			e.preventDefault();
			e.stopPropagation();
			me._isResizing = true;

			var startWidth = table.offsetWidth;
			var startHeight = table.offsetHeight;
			var startX = e.pageX;
			var startY = e.pageY;
			var numCols = table.rows[0].cells.length;
			var numRows = table.rows.length;
			var minColWidth = 10;
			var minRowHeight = 10;
			var minTableWidth = minColWidth * numCols;
			var minTableHeight = minRowHeight * numRows;

			function onMouseMove(moveEvent) {
				moveEvent.preventDefault();
				moveEvent.stopPropagation();

				var newWidth = Math.max(minTableWidth, startWidth + (moveEvent.pageX - startX));
				var newColWidth = Math.floor(newWidth / numCols);
				var newHeight = Math.max(minTableHeight, startHeight + (moveEvent.pageY - startY));
				var newRowHeight = newHeight / numRows;

				table.style.width = newWidth + 'px';

				Ext.each(table.rows, function(row) {
					Ext.each(row.cells, function(cell) {
						cell.style.width = newColWidth + 'px';
						cell.style.height = newRowHeight + 'px';
					});
				});
			}

			function onMouseUp(upEvent) {
				upEvent.preventDefault();
				upEvent.stopPropagation();

				me._isResizing = false;
				editorDoc.removeEventListener('mousemove', onMouseMove);
				editorWin.removeEventListener('mouseup', onMouseUp);
				document.removeEventListener('mouseup', onMouseUp);

				// Update the stored original width after resize
				table.setAttribute('data-original-width', table.offsetWidth);

				setTimeout(function() {
					createHandles();
					if (editorDoc.defaultView.getSelection) {
						var selection = editorDoc.defaultView.getSelection();
						var firstCell = table.rows[0].cells[0];
						var range = editorDoc.createRange();
						range.selectNodeContents(firstCell);
						selection.removeAllRanges();
						selection.addRange(range);
					}
				}, 0);
			}

			editorDoc.addEventListener('mousemove', onMouseMove);
			editorWin.addEventListener('mouseup', onMouseUp);
			document.addEventListener('mouseup', onMouseUp);
		}

		function handleMouseDown(e) {
			var isInsideEditor = false;
			try {
				isInsideEditor = e.target.ownerDocument === editorDoc;
			} catch(err) {
				isInsideEditor = false;
			}

			if (isInsideEditor) {
				if (resizeHandle && e.target === resizeHandle) {
					startResize(e);
					return;
				}
				if (deleteHandle && e.target === deleteHandle) {
					deleteTable(e);
					return;
				}

				var target = e.target;
				var isInsideTable = false;
				while (target && target !== editorDoc.body) {
					if (target === table) {
						isInsideTable = true;
						break;
					}
					target = target.parentNode;
				}

				if (isInsideTable) {
					createHandles();
				} else {
					removeHandles();
				}
			}
		}

		// Add mousedown listener to editor document with capture phase
		editorDoc.addEventListener('mousedown', handleMouseDown, true);
	}

});
Ext.reg('newtableplugin', Ext.ux.form.HtmlEditor.NEWTablePlugin);
