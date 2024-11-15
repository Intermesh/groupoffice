Ext.ns("Ext.ux.form.HtmlEditor");

Ext.ux.form.HtmlEditor.NEWTablePlugin = Ext.extend(Ext.util.Observable, {
    init: function(editor) {
        this.editor = editor;
        this.editor.on('render', this.onEditorRender, this);
        this.editor.on('initialize', this.onEditorInitialize, this);
    },

    onEditorRender: function() {
        if (this.editor.getToolbar()) {
            this.editor.getToolbar().addButton({
                text: 'table_chart',
                tooltip: 'Insert/Modify Table',
                handler: this.showTableConfigDialog,
                cls: 'icons-toolbar-tblicon x-btn-text',
                scope: this
            });
        }

        // Dynamically add CSS for the custom toolbar icon
        const style = document.createElement('style');
        style.type = 'text/css';
        style.innerHTML = `
            .icons-toolbar-tblicon .x-btn-text{
                font-family: 'Icons';
                font-size: 17px;
                color: black;
                display: flex;
                align-items: center; /* Vertically center the content */
                justify-content: center; /* Horizontally center the content */
                padding: 0px;
                border: 0px;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
    },

    onEditorInitialize: function() {
        const editorDoc = this.editor.getDoc();

        editorDoc.addEventListener('click', () => {
            const tables = editorDoc.querySelectorAll('table');
            tables.forEach((table) => {
                if (!table.querySelector('.resize-handle')) {
                    this.addResizeHandle(table);
                }
            });
        });
    },

    showTableConfigDialog: function() {
        const table = this.getSelectedTable();

        if (!this.tableConfigWindow) {
            this.rowsField = new Ext.form.NumberField({ fieldLabel: 'Rows', allowBlank: false, minValue: 1, value: 2 });
            this.columnsField = new Ext.form.NumberField({ fieldLabel: 'Columns', allowBlank: false, minValue: 1, value: 2 });
            this.captionField = new Ext.form.TextField({ fieldLabel: 'Caption' });
            this.summaryField = new Ext.form.TextField({ fieldLabel: 'Summary' });
            this.cellSpacingField = new Ext.form.NumberField({ fieldLabel: 'Cell Spacing', minValue: 0, value: 1 });
            this.cellPaddingField = new Ext.form.NumberField({ fieldLabel: 'Cell Padding', minValue: 0, value: 1 });
            this.bordersCheckbox = new Ext.form.Checkbox({ fieldLabel: 'Borders', checked: true });
            this.alignmentCombo = new Ext.form.ComboBox({
                fieldLabel: 'Alignment',
                store: ['left', 'center', 'right'],
                mode: 'local',
                editable: false,
                triggerAction: 'all',
                value: 'left'
            });

            this.tableConfigWindow = new Ext.Window({
                title: 'Insert/Modify Table',
                width: 400,
                modal: true,
                layout: 'form',
                bodyStyle: 'padding:10px',
                closeAction: 'hide',
                items: [
                    {
                        layout: 'column',
                        defaults: { layout: 'form', columnWidth: 0.5, bodyStyle: 'padding:5px' },
                        items: [
                            { items: [this.rowsField, this.captionField, this.cellSpacingField, this.bordersCheckbox] },
                            { items: [this.columnsField, this.summaryField, this.cellPaddingField, this.alignmentCombo] }
                        ]
                    }
                ],
                buttons: [
                    { text: 'Insert/Update Table', handler: this.insertOrUpdateTable, scope: this },
                    { text: 'Cancel', handler: function() { this.tableConfigWindow.hide(); }, scope: this }
                ]
            });
        }

        if (table) {
            this.populateTableConfigDialog(table);
        }

        this.tableConfigWindow.show();
    },

    getSelectedTable: function() {
        const doc = this.editor.getDoc();
        const selection = doc.getSelection();
        if (!selection.rangeCount) return null;

        const range = selection.getRangeAt(0);
        let element = range.commonAncestorContainer;

        while (element && element.nodeName !== 'TABLE') {
            element = element.parentNode;
        }

        return element && element.nodeName === 'TABLE' ? element : null;
    },

    populateTableConfigDialog: function(table) {
        const caption = table.querySelector('caption') ? table.querySelector('caption').textContent : '';
        const summary = table.getAttribute('summary') || '';
        const cellSpacing = table.getAttribute('cellspacing') || 1;
        const cellPadding = table.getAttribute('cellpadding') || 1;
        const borders = table.getAttribute('border') === '1';
        const alignment = table.getAttribute('align') || 'left';

        this.rowsField.setValue(table.rows.length);
        this.columnsField.setValue(table.rows[0] ? table.rows[0].cells.length : 0);
        this.captionField.setValue(caption);
        this.summaryField.setValue(summary);
        this.cellSpacingField.setValue(cellSpacing);
        this.cellPaddingField.setValue(cellPadding);
        this.bordersCheckbox.setValue(borders);
        this.alignmentCombo.setValue(alignment);
    },

    insertOrUpdateTable: function() {
        const rows = this.rowsField.getValue();
        const columns = this.columnsField.getValue();
        const caption = this.captionField.getValue();
        const summary = this.summaryField.getValue();
        const cellSpacing = this.cellSpacingField.getValue();
        const cellPadding = this.cellPaddingField.getValue();
        const borders = this.bordersCheckbox.getValue() ? 1 : 0;
        const alignment = this.alignmentCombo.getValue();

        const table = this.getSelectedTable();

        if (table) {
            table.setAttribute('summary', summary);
            table.setAttribute('cellspacing', cellSpacing);
            table.setAttribute('cellpadding', cellPadding);
            table.setAttribute('border', borders);

            if (caption) {
                if (!table.querySelector('caption')) {
                    const captionElement = table.createCaption();
                    captionElement.textContent = caption;
                } else {
                    table.querySelector('caption').textContent = caption;
                }
            } else if (table.querySelector('caption')) {
                table.deleteCaption();
            }

            this.adjustTableRowsAndColumns(table, rows, columns, alignment);
        } else {
            let tableHTML = `<table border="${borders}" cellspacing="${cellSpacing}" cellpadding="${cellPadding}"`;
            if (summary) tableHTML += ` summary="${summary}"`;
            tableHTML += '>';

            if (caption) tableHTML += `<caption>${caption}</caption>`;

            for (let i = 0; i < rows; i++) {
                tableHTML += '<tr>';
                for (let j = 0; j < columns; j++) {
                    tableHTML += `<td style="text-align: ${alignment}; width: 100px; height: 30px;">&nbsp;</td>`;
                }
                tableHTML += '</tr>';
            }
            tableHTML += '</table><br>';

            this.editor.insertAtCursor(tableHTML);
        }

        const newTable = this.getSelectedTable();
        if (newTable) {
            this.addResizeHandle(newTable);
        }

        this.tableConfigWindow.hide();
    },

    adjustTableRowsAndColumns: function(table, rows, columns, alignment) {
        const currentRows = table.rows.length;
        const currentCols = table.rows[0] ? table.rows[0].cells.length : 0;

        if (rows > currentRows) {
            for (let i = currentRows; i < rows; i++) {
                const newRow = table.insertRow();
                for (let j = 0; j < columns; j++) {
                    const newCell = newRow.insertCell();
                    newCell.innerHTML = '&nbsp;';
                    newCell.style.width = '100px';
                    newCell.style.height = '30px';
                    newCell.style.textAlign = alignment;
                }
            }
        } else if (rows < currentRows) {
            for (let i = currentRows - 1; i >= rows; i--) {
                table.deleteRow(i);
            }
        }

        for (let i = 0; i < rows; i++) {
            const row = table.rows[i];
            const currentColsInRow = row.cells.length;

            if (columns > currentColsInRow) {
                for (let j = currentColsInRow; j < columns; j++) {
                    const newCell = row.insertCell();
                    newCell.innerHTML = '&nbsp;';
                    newCell.style.width = '100px';
                    newCell.style.height = '30px';
                    newCell.style.textAlign = alignment;
                }
            } else if (columns < currentColsInRow) {
                for (let j = currentColsInRow - 1; j >= columns; j--) {
                    row.deleteCell(j);
                }
            }

            for (let j = 0; j < columns; j++) {
                row.cells[j].style.textAlign = alignment;
            }
        }
    },

addResizeHandle: function(table) {
    const editorDoc = this.editor.getDoc();

    // Set table-layout to fixed to ensure consistent cell resizing
    table.style.tableLayout = 'fixed';

    // Check if the resize handle already exists
    let resizeHandle = table.querySelector('.resize-handle');
    if (resizeHandle) return; // If it exists, no need to add another

    // Create and style the resize handle
    resizeHandle = document.createElement('div');
    resizeHandle.className = 'resize-handle';
    resizeHandle.innerHTML = 'open_in_full'; // Icon content
    resizeHandle.style.fontFamily = 'Icons'; // Use specified font family
    resizeHandle.style.fontSize = '16px';
    resizeHandle.style.transform = 'rotate(180deg) scaleX(-1)';
    resizeHandle.style.position = 'absolute';
    resizeHandle.style.cursor = 'nwse-resize';
    resizeHandle.style.zIndex = '1000';
    resizeHandle.style.color = 'black';
    resizeHandle.style.right = '0';
    resizeHandle.style.bottom = '0';
    resizeHandle.style.visibility = 'visible'; // Ensure visibility
    resizeHandle.spellcheck = false; // Disable spell checking
    table.style.position = 'relative';
    table.appendChild(resizeHandle);

    // Attach mousedown event to the resize handle
    resizeHandle.onmousedown = (e) => {
        e.preventDefault();

        const startWidth = table.offsetWidth;
        const startHeight = table.offsetHeight;
        const startX = e.clientX;
        const startY = e.clientY;

        const onMouseMove = (event) => {
            const width = startWidth + (event.clientX - startX);
            const height = startHeight + (event.clientY - startY);
            table.style.width = `${width}px`;
            table.style.height = `${height}px`;

            // Set the height for each row and cell to ensure they expand vertically
            Array.from(table.rows).forEach((row) => {
                row.style.height = `${height / table.rows.length}px`;
                Array.from(row.cells).forEach((cell) => {
                    cell.style.height = `${height / table.rows.length}px`;
                });
            });
        };

        const onMouseUp = () => {
            editorDoc.removeEventListener('mousemove', onMouseMove);
            editorDoc.removeEventListener('mouseup', onMouseUp);
        };

        // Attach mousemove and mouseup to the editor's document for proper resizing within the editor
        editorDoc.addEventListener('mousemove', onMouseMove);
        editorDoc.addEventListener('mouseup', onMouseUp);
    };
}


});

Ext.preg('newtableplugin', Ext.ux.form.HtmlEditor.NEWTablePlugin);
