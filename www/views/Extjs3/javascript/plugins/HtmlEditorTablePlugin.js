Ext.ns("Ext.ux.form.HtmlEditor");

Ext.ux.form.HtmlEditor.NEWTablePlugin = function(config) {
    Ext.apply(this, config);
    Ext.ux.form.HtmlEditor.NEWTablePlugin.superclass.constructor.call(this);
};

Ext.extend(Ext.ux.form.HtmlEditor.NEWTablePlugin, Ext.util.Observable, {
    // Initialize plugin
    init: function(editor) {
        var me = this;
        me.editor = editor;
        me.editor.on('render', me.onEditorRender, me);
        me.editor.on('initialize', me.onEditorInitialize, me);
        me.editor.on('activate', me.addResizeHandlesToAllTables, me);

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
            // Reapply resize handles after sync
            me.addResizeHandlesToAllTables();
        };
    },

    onEditorRender: function() {
        var me = this;
        if (me.editor.getToolbar()) {
            me.editor.getToolbar().addButton({
                text: 'table_chart',
                tooltip: 'Insert/Modify Table',
                handler: me.showTableConfigDialog,
                cls: 'icons-toolbar-tblicon x-btn-text',
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

    onEditorInitialize: function() {
        var me = this;
        var editorDoc = me.editor.getDoc();

        // Add resize handles to any existing tables
        me.addResizeHandlesToAllTables();

        // Monitor for clicks in case new tables are added
        Ext.fly(editorDoc).on('click', function() {
            me.addResizeHandlesToAllTables();
        });
    },

    addResizeHandlesToAllTables: function() {
        var me = this;
        var editorDoc = me.editor.getDoc();
        if (!editorDoc) return;

        var tables = editorDoc.getElementsByTagName('table');
        Ext.each(tables, function(table) {
            me.addResizeHandle(table);
        });
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

        return element && element.nodeName === 'TABLE' ? element : null;
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

        var existingTable = me.getSelectedTable();

        if (existingTable) {
            existingTable.setAttribute('summary', summary);
            existingTable.setAttribute('cellspacing', cellSpacing);
            existingTable.setAttribute('cellpadding', cellPadding);
            existingTable.setAttribute('border', borders);

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

            me.adjustTableRowsAndColumns(existingTable, rows, columns, alignment);
            me.addResizeHandle(existingTable);
        } else {
            var totalWidth = columns * 100;
            var tableHTML = '<table border="' + borders + '" cellspacing="' + cellSpacing +
                          '" cellpadding="' + cellPadding + '"' +
                          (summary ? ' summary="' + summary + '"' : '') +
                          ' align="' + alignment + '"' +
                          ' style="width: ' + totalWidth + 'px;">';

            if (caption) {
                tableHTML += '<caption>' + caption + '</caption>';
            }

            for (var i = 0; i < rows; i++) {
                tableHTML += '<tr>';
                for (var j = 0; j < columns; j++) {
                    tableHTML += '<td style="text-align: ' + alignment + '; width: 100px; height: 30px;">&nbsp;</td>';
                }
                tableHTML += '</tr>';
            }
            tableHTML += '</table><br><br>';

            me.editor.insertAtCursor(tableHTML);

            setTimeout(function() {
                var doc = me.editor.getDoc();
                var tables = doc.getElementsByTagName('table');
                var newTable = tables[tables.length - 1];
                if (newTable) {
                    me.addResizeHandle(newTable);
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

    adjustTableRowsAndColumns: function(table, rows, columns, alignment) {
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
                cell.style.textAlign = alignment;
            }
        }
    },

    addResizeHandle: function(table) {
        var me = this;
        var editorDoc = me.editor.getDoc();

        table.style.tableLayout = 'fixed';

        // Remove any existing handle
        var existingHandle = table.querySelector('.resize-handle');
        if (existingHandle) {
            existingHandle.parentNode.removeChild(existingHandle);
        }

        // Create wrapper div if it doesn't exist
        var wrapper = table.parentNode;
        if (!wrapper || !wrapper.classList.contains('table-wrapper')) {
            wrapper = editorDoc.createElement('div');
            wrapper.className = 'table-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block'; // This ensures wrapper fits table size
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }

        var resizeHandle = editorDoc.createElement('div');
        resizeHandle.className = 'resize-handle';
        resizeHandle.setAttribute('data-editor-helper', 'true');

        // Enhanced hiding properties
        resizeHandle.style.width = '10px';
        resizeHandle.style.height = '10px';
        resizeHandle.style.backgroundColor = '#0066cc';
        resizeHandle.style.border = '1px solid #003366';
        resizeHandle.style.position = 'absolute';
        resizeHandle.style.cursor = 'nwse-resize';
        resizeHandle.style.zIndex = '1000';
        resizeHandle.style.right = '0';
        resizeHandle.style.bottom = '0';
        resizeHandle.style.display = 'none';
        resizeHandle.style.visibility = 'hidden';
        resizeHandle.style.opacity = '0';
        resizeHandle.style.pointerEvents = 'none';
        resizeHandle.spellcheck = false;

        // Add handle to wrapper instead of table
        wrapper.appendChild(resizeHandle);

        function showHandle() {
            resizeHandle.style.display = 'block';
            resizeHandle.style.visibility = 'visible';
            resizeHandle.style.opacity = '1';
            resizeHandle.style.pointerEvents = 'auto';
        }

        function hideHandle() {
            resizeHandle.style.display = 'none';
            resizeHandle.style.visibility = 'hidden';
            resizeHandle.style.opacity = '0';
            resizeHandle.style.pointerEvents = 'none';
        }

        // Adjust event listeners to work with wrapper
        Ext.fly(wrapper).on('mouseenter', showHandle);

        Ext.fly(wrapper).on('mouseleave', function(e) {
            if (!e.getRelatedTarget() || !Ext.fly(e.getRelatedTarget()).hasClass('resize-handle')) {
                hideHandle();
            }
        });

        Ext.fly(resizeHandle).on('mousedown', function(e) {
            e.preventDefault();
            showHandle();

            var startWidth = table.offsetWidth;
            var startHeight = table.offsetHeight;
            var startX = e.getPageX();
            var startY = e.getPageY();

            var onMouseMove = function(moveEvent) {
                var width = startWidth + (moveEvent.getPageX() - startX);
                var height = startHeight + (moveEvent.getPageY() - startY);
                table.style.width = width + 'px';
                table.style.height = height + 'px';

                Ext.each(table.rows, function(row) {
                    var rowHeight = height / table.rows.length;
                    row.style.height = rowHeight + 'px';
                    Ext.each(row.cells, function(cell) {
                        cell.style.height = rowHeight + 'px';
                    });
                });
            };

            var onMouseUp = function(upEvent) {
                Ext.fly(editorDoc).un('mousemove', onMouseMove);
                Ext.fly(editorDoc).un('mouseup', onMouseUp);

                var rect = wrapper.getBoundingClientRect();
                var mouseX = upEvent.getPageX();
                var mouseY = upEvent.getPageY();

                if (mouseX < rect.left || mouseX > rect.right ||
                    mouseY < rect.top || mouseY > rect.bottom) {
                    hideHandle();
                }
            };

            Ext.fly(editorDoc).on('mousemove', onMouseMove);
            Ext.fly(editorDoc).on('mouseup', onMouseUp);
        });

        // Clean up function for when editor syncs content
        var cleanup = function() {
            if (wrapper && wrapper.parentNode) {
                var parent = wrapper.parentNode;
                parent.insertBefore(table, wrapper);
                parent.removeChild(wrapper);
            }
        };

        // Add cleanup to editor's sync event if not already added
        if (!me._cleanupAdded) {
            me.editor.on('sync', cleanup);
            me._cleanupAdded = true;
        }
    }
});

Ext.reg('newtableplugin', Ext.ux.form.HtmlEditor.NEWTablePlugin);
