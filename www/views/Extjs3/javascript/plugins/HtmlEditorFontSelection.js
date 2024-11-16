Ext.namespace("Ext.ux.form.HtmlEditor");

Ext.ux.form.HtmlEditor.FontSelector = Ext.extend(Ext.util.Observable, {
    availableFonts: [
        "Verdana", "Arial", "Tahoma", "Helvetica", "Times New Roman", "Georgia",
        "Courier New", "Comic Sans MS", "Impact"
    ],

    defaultFontPriority: ["Verdana", "Arial", "Tahoma"],
    currentFontFamily: null,
    currentFontSize: null,

    constructor: function(config) {
        Ext.apply(this, config || {});
        this.savedSelection = null;
        Ext.ux.form.HtmlEditor.FontSelector.superclass.constructor.call(this);
    },

init: function(editor) {
    this.editor = editor;

    // Add existing initialization code
    this.ensureViewportMeta();
    this.addMobileStyles();

    editor.on("initialize", () => {
        this.attachSelectionListeners();
        this.addPlaceholderOnChange();
        this.addKeyHandling();
        this.setupFormatHandlers();
    });

    editor.on("render", this.addFontSelectors, this);
    editor.on("initialize", this.setDefaultStyles, this);
    editor.on("initialize", this.addDynamicDropdownUpdates, this);
},

    ensureViewportMeta: function() {
        if (!document.querySelector('meta[name="viewport"]')) {
            const meta = document.createElement('meta');
            meta.name = 'viewport';
            meta.content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no';
            document.head.appendChild(meta);
        }
    },

    addMobileStyles: function() {
        const styleId = 'html-editor-mobile-styles';
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                .x-html-editor-wrap {
                    -webkit-text-size-adjust: 100%;
                    -moz-text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                    text-size-adjust: 100%;
                }
                .x-html-editor-wrap iframe {
                    -webkit-text-size-adjust: 100%;
                    -moz-text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                    text-size-adjust: 100%;
                }
            `;
            document.head.appendChild(style);
        }
    },

    getCurrentBlockStyle: function() {
        const editor = this.editor;
        const doc = editor.getDoc();
        const selection = doc.getSelection();

        if (!selection.rangeCount) return {};

        const range = selection.getRangeAt(0);
        let currentNode = range.startContainer;

        // If we're in a text node, get its parent
        if (currentNode.nodeType === 3) {
            currentNode = currentNode.parentNode;
        }

        // Check if we're inside a table cell
        const cell = currentNode.closest('td, th');
        if (cell) {
            // Look for existing font styles in the cell
            const computedStyle = window.getComputedStyle(cell);
            return {
                fontFamily: computedStyle.fontFamily.replace(/['"]/g, '') || this.currentFontFamily,
                fontSize: computedStyle.fontSize || this.currentFontSize
            };
        }

        return {
            fontFamily: this.fontCombo.getValue() || this.currentFontFamily,
            fontSize: this.fontSizeCombo.getValue() || this.currentFontSize
        };
    },

    addFontSelectors: function() {
        const editor = this.editor;
        const filteredFonts = this.availableFonts.filter((font) => this.isFontAvailable(font));

        this.fontStore = new Ext.data.ArrayStore({
            fields: ["font"],
            data: filteredFonts.map((font) => [font])
        });

        this.fontCombo = new Ext.form.ComboBox({
            store: this.fontStore,
            displayField: "font",
            typeAhead: true,
            mode: "local",
            triggerAction: "all",
            selectOnFocus: true,
            editable: false,
            width: 150,
            tpl: '<tpl for="."><div class="x-combo-list-item" style="font-family: {font};">{font}</div></tpl>',
            listeners: {
                beforeexpand: () => {
                    this.saveSelection();
                    return true;
                },
                expand: () => {
                    setTimeout(() => {
                        editor.focus();
                        this.restoreSelection();
                    }, 10);
                },
                select: (combo, record) => {
                    const font = record.data.font;
                    editor.focus();
                    this.restoreSelection();
                    this.applyFont(font);
                },
                scope: this
            }
        });

        this.fontSizeCombo = new Ext.form.ComboBox({
            store: new Ext.data.ArrayStore({
                fields: ["size"],
                data: Array.from({ length: 69 }, (_, i) => [`${i + 4}px`])
            }),
            displayField: "size",
            typeAhead: true,
            mode: "local",
            triggerAction: "all",
            selectOnFocus: true,
            editable: false,
            width: 100,
            listeners: {
                beforeexpand: () => {
                    this.saveSelection();
                    return true;
                },
                expand: () => {
                    setTimeout(() => {
                        editor.focus();
                        this.restoreSelection();
                    }, 10);
                },
                select: (combo, record) => {
                    const size = record.data.size.replace("px", "");
                    editor.focus();
                    this.restoreSelection();
                    this.applyFontSize(size);
                },
                scope: this
            }
        });

        const toolbar = editor.getToolbar();
        toolbar.insert(0, this.fontCombo);
        toolbar.insert(1, this.fontSizeCombo);
        toolbar.doLayout();
    },
        setDefaultStyles: function() {
        const editor = this.editor;
        const doc = editor.getDoc();

        const defaultFont = this.defaultFontPriority.find(font => this.availableFonts.includes(font));
        if (defaultFont) {
            doc.body.style.fontFamily = defaultFont;
            this.fontCombo.setValue(defaultFont);
            this.currentFontFamily = defaultFont;
        }

        // Set default font size and additional mobile-specific styles
        doc.body.style.fontSize = "12px";
        doc.body.style.webkitTextSizeAdjust = "100%";
        doc.body.style.mozTextSizeAdjust = "100%";
        doc.body.style.msTextSizeAdjust = "100%";
        doc.body.style.textSizeAdjust = "100%";

        // Prevent mobile browsers from inflating text
        doc.body.style.maxHeight = "none";
        doc.body.style.wordWrap = "break-word";
        doc.body.style.webkitNbspMode = "space";
        doc.body.style.lineHeight = "normal";

        this.fontSizeCombo.setValue("12px");
        this.currentFontSize = "12px";

        // Add general styles to the iframe's head
        const style = doc.createElement('style');
        style.textContent = `
            body {
                -webkit-text-size-adjust: 100% !important;
                -moz-text-size-adjust: 100% !important;
                -ms-text-size-adjust: 100% !important;
                text-size-adjust: 100% !important;
            }
            p {
                margin: 0;
                -webkit-text-size-adjust: 100% !important;
                -moz-text-size-adjust: 100% !important;
                -ms-text-size-adjust: 100% !important;
                text-size-adjust: 100% !important;
            }
            span {
                -webkit-text-size-adjust: 100% !important;
                -moz-text-size-adjust: 100% !important;
                -ms-text-size-adjust: 100% !important;
                text-size-adjust: 100% !important;
            }
        `;
        doc.head.appendChild(style);

        // Add table-specific styles
        const tableStyles = `
            table {
                border-collapse: collapse;
                width: 100%;
            }
            td, th {
                border: 1px solid #ddd;
                padding: 8px;
                font-family: ${this.currentFontFamily};
                font-size: ${this.currentFontSize};
                -webkit-text-size-adjust: 100% !important;
                -moz-text-size-adjust: 100% !important;
                -ms-text-size-adjust: 100% !important;
                text-size-adjust: 100% !important;
            }
        `;

        const tableStyle = doc.createElement('style');
        tableStyle.textContent = tableStyles;
        doc.head.appendChild(tableStyle);
    },

    updateCurrentFontSettings: function(node) {
        const computedStyle = window.getComputedStyle(node);
        const fontFamily = computedStyle.fontFamily.replace(/['"]/g, "").split(",")[0];
        const fontSize = computedStyle.fontSize;

        if (fontFamily) {
            this.currentFontFamily = fontFamily;
            this.fontCombo.setValue(fontFamily);
        }

        if (fontSize) {
            this.currentFontSize = fontSize;
            this.fontSizeCombo.setValue(fontSize);
        }
    },

    addDynamicDropdownUpdates: function() {
        const editor = this.editor;
        const doc = editor.getDoc();

        const updateDropdowns = () => {
            const selection = doc.getSelection();
            if (!selection.rangeCount) return;

            const range = selection.getRangeAt(0);
            const node = range.startContainer.nodeType === 3 ? range.startContainer.parentNode : range.startContainer;

            this.updateCurrentFontSettings(node);
        };

        doc.addEventListener("mouseup", updateDropdowns);
        doc.addEventListener("keyup", (e) => {
            if (e.key !== "Enter" && e.key !== "Tab") {
                updateDropdowns();
            }
        });
    },

    setSelectionSafely: function(range, node, shouldCollapse = true) {
        try {
            if (node.nodeType === 3) { // Text node
                range.setStart(node, 0);
                range.setEnd(node, node.length);
            } else {
                // For element nodes, try to select first text node
                const firstText = node.querySelector('*') || node;
                range.selectNodeContents(firstText);
            }
            if (shouldCollapse) {
                range.collapse(true);
            }
            return true;
        } catch (e) {
            console.warn('Error setting range:', e);
            return false;
        }
    },

    addKeyHandling: function() {
        const editor = this.editor;
        const doc = editor.getDoc();

        // Add default styling for table cells
        const style = doc.createElement('style');
        style.textContent = `
            td, th {
                -webkit-text-size-adjust: 100% !important;
                -moz-text-size-adjust: 100% !important;
                -ms-text-size-adjust: 100% !important;
                text-size-adjust: 100% !important;
            }
        `;
        doc.head.appendChild(style);

        doc.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                this.handleEnterKey(e);
            } else if (e.key === "Tab") {
                e.preventDefault();
                this.handleTabKey(e);
            } else if (e.key === "Backspace") {
                this.handleBackspaceKey(e);
            }
        });

        // Add input event listener for table cells
        doc.addEventListener("input", (e) => {
            const cell = e.target.closest('td, th');
            if (cell) {
                const styles = this.getCurrentBlockStyle();
                if (!cell.style.fontFamily) {
                    cell.style.fontFamily = styles.fontFamily;
                }
                if (!cell.style.fontSize) {
                    cell.style.fontSize = styles.fontSize;
                }
            }
        });
    },
    handleEnterKey: function(e) {
    const editor = this.editor;
    const doc = editor.getDoc();
    const selection = doc.getSelection();

    if (!selection.rangeCount) return;

    e.preventDefault();
    const range = selection.getRangeAt(0);
    let currentNode = range.startContainer;

    // Check if we're in a table cell
    let cell = currentNode;
    if (cell.nodeType === 3) { // Text node
        cell = cell.parentNode;
    }
    cell = cell.closest('td, th');

    if (cell) {
        // Get current styles
        const styles = this.getCurrentBlockStyle();

        // Create a new div or p with proper styling inside the cell
        const newBlock = doc.createElement('p');
        newBlock.style.margin = '0';
        newBlock.style.fontFamily = styles.fontFamily;
        newBlock.style.fontSize = styles.fontSize;
        newBlock.style.webkitTextSizeAdjust = "100%";
        newBlock.style.textSizeAdjust = "100%";
        newBlock.innerHTML = '&#8203;';

        // Insert the new block at cursor position
        range.insertNode(newBlock);

        // Set cursor inside new block
        range.selectNodeContents(newBlock);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);

        editor.focus();
        editor.syncValue();
        return;
    }

    // Get the block element we're in
    let currentBlock = currentNode;
    if (currentNode.nodeType === 3) { // Text node
        currentBlock = currentNode.parentNode;
    }
    while (currentBlock && !['P', 'DIV', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(currentBlock.nodeName)) {
        currentBlock = currentBlock.parentNode;
    }

    // Use selected font and size from combos
    const selectedFont = this.fontCombo.getValue() || this.currentFontFamily;
    const selectedSize = this.fontSizeCombo.getValue() || this.currentFontSize;

    // Create new paragraph with selected styles
    const newParagraph = doc.createElement('p');
    newParagraph.style.margin = '0';
    newParagraph.style.fontFamily = selectedFont;
    newParagraph.style.fontSize = selectedSize;
    newParagraph.style.webkitTextSizeAdjust = "100%";
    newParagraph.style.textSizeAdjust = "100%";
    newParagraph.style.lineHeight = "normal";

    try {
        if (!currentBlock || currentBlock === doc.body) {
            // If we're not in a block element, create one
            newParagraph.innerHTML = '&#8203;';
            range.insertNode(newParagraph);
        } else {
            // Split the current block at cursor position
            const clonedRange = range.cloneRange();

            // Get content after cursor
            clonedRange.setStart(range.endContainer, range.endOffset);
            clonedRange.setEndAfter(currentBlock.lastChild);

            // Extract and store the content that will go in the new paragraph
            const fragment = clonedRange.extractContents();

            // If there's content after cursor, move it to new paragraph
            if (fragment.textContent.trim() || fragment.childNodes.length > 0) {
                newParagraph.appendChild(fragment);
            } else {
                newParagraph.innerHTML = '&#8203;';
            }

            // Insert new paragraph after current block
            currentBlock.parentNode.insertBefore(newParagraph, currentBlock.nextSibling);

            // Clean up empty spans in both blocks
            [currentBlock, newParagraph].forEach(block => {
                const emptySpans = block.querySelectorAll('span:empty');
                emptySpans.forEach(span => span.remove());
                if (!block.textContent.trim()) {
                    block.innerHTML = '&#8203;';
                }
            });
        }

        // Set cursor to start of new paragraph
        const newRange = doc.createRange();
        const textNode = newParagraph.firstChild || newParagraph;
        if (textNode.nodeType === 3) {
            newRange.setStart(textNode, 0);
            newRange.setEnd(textNode, 0);
        } else {
            newRange.selectNodeContents(textNode);
            newRange.collapse(true);
        }

        selection.removeAllRanges();
        selection.addRange(newRange);

    } catch (e) {
        console.error('Error handling enter key:', e);
        // Fallback: insert basic paragraph
        newParagraph.innerHTML = '&#8203;';
        if (currentBlock) {
            currentBlock.parentNode.insertBefore(newParagraph, currentBlock.nextSibling);
        } else {
            range.insertNode(newParagraph);
        }

        const newRange = doc.createRange();
        newRange.selectNodeContents(newParagraph);
        newRange.collapse(true);
        selection.removeAllRanges();
        selection.addRange(newRange);
    }

    editor.focus();
    editor.syncValue();
},

    handleTabKey: function(e) {
        const editor = this.editor;
        const doc = editor.getDoc();
        const selection = doc.getSelection();

        if (!selection.rangeCount) return;

        e.preventDefault();

        // Use selected font and size from combos
        const selectedFont = this.fontCombo.getValue() || this.currentFontFamily;
        const selectedSize = this.fontSizeCombo.getValue() || this.currentFontSize;

        const tabSpan = doc.createElement('span');
        tabSpan.style.fontFamily = selectedFont;
        tabSpan.style.fontSize = selectedSize;
        tabSpan.style.webkitTextSizeAdjust = "100%";
        tabSpan.style.textSizeAdjust = "100%";
        tabSpan.innerHTML = '\u2003\u2003';

        try {
            const range = selection.getRangeAt(0);
            if (!range.collapsed) {
                range.deleteContents();
            }

            range.insertNode(tabSpan);

            // Set cursor after tab
            const newRange = doc.createRange();
            newRange.setStartAfter(tabSpan);
            newRange.setEndAfter(tabSpan);
            selection.removeAllRanges();
            selection.addRange(newRange);

        } catch (e) {
            console.error('Error inserting tab:', e);
            // Fallback to execCommand
            const html = `<span style="font-family: ${selectedFont}; font-size: ${selectedSize};">&emsp;&emsp;</span>`;
            doc.execCommand("insertHTML", false, html);
        }

        editor.focus();
        editor.syncValue();
    },
    handleBackspaceKey: function(e) {
    const editor = this.editor;
    const doc = editor.getDoc();
    const selection = doc.getSelection();

    if (!selection.rangeCount) return;

    const range = selection.getRangeAt(0);
    if (!range.collapsed) return;

    // Fix for table cell check - handle both text and element nodes
    let currentNode = range.startContainer;
    if (currentNode.nodeType === 3) { // Text node
        currentNode = currentNode.parentNode;
    }

    // Now we can safely use closest()
    const cell = currentNode.closest('td, th');
    if (cell) {
        // Let default backspace behavior work in tables
        return;
    }

    let currentBlock = currentNode;
    while (currentBlock && !['P', 'DIV', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(currentBlock.nodeName)) {
        currentBlock = currentBlock.parentNode;
    }

    if (!currentBlock || currentBlock === doc.body) return;

    const previousBlock = currentBlock.previousSibling;
    if (this.isAtStartOfNode(range) && previousBlock) {
        e.preventDefault();

        try {
            // Handle merging blocks
            if (currentBlock.textContent.trim() === '') {
                currentBlock.remove();
            } else {
                // Set cursor to end of previous block
                const newRange = doc.createRange();
                this.setSelectionSafely(newRange, previousBlock, false);
                selection.removeAllRanges();
                selection.addRange(newRange);

                // Move content
                while (currentBlock.firstChild) {
                    previousBlock.appendChild(currentBlock.firstChild);
                }
                currentBlock.remove();
            }
        } catch (e) {
            console.error('Error handling backspace:', e);
        }
    }

    editor.focus();
    editor.syncValue();
},

restoreSelection: function() {
    var editor = this.editor;
    var win = editor.getWin();
    var doc = editor.getDoc();

    try {
        if (!this.savedSelection) return false;

        var startNode = this.getNodeFromPath(doc.body, this.savedSelection.start);
        var endNode = this.getNodeFromPath(doc.body, this.savedSelection.end);

        if (!startNode || !endNode) {
            console.warn("Could not restore selection - nodes not found");
            return false;
        }

        var range = doc.createRange();
        var selection = win.getSelection();

        try {
            // Safely set start position
            var startOffset = Math.min(this.savedSelection.startOffset,
                startNode.nodeType === 3 ? startNode.length : startNode.childNodes.length);

            // Safely set end position
            var endOffset = Math.min(this.savedSelection.endOffset,
                endNode.nodeType === 3 ? endNode.length : endNode.childNodes.length);

            range.setStart(startNode, startOffset);
            range.setEnd(endNode, endOffset);

            selection.removeAllRanges();
            selection.addRange(range);

            return true;
        } catch (e) {
            console.warn("Could not set range:", e);

            // Fallback: try to at least set a collapsed range at start
            try {
                range = doc.createRange();
                range.setStart(startNode, 0);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                return true;
            } catch (fallbackError) {
                console.error("Fallback range failed:", fallbackError);
                return false;
            }
        }
    } catch (error) {
        console.error("Error restoring selection:", error);
        return false;
    }
},

saveSelection: function() {
    var editor = this.editor;
    var win = editor.getWin();
    var doc = editor.getDoc();

    try {
        var selection = win.getSelection();
        if (!selection.rangeCount) return;

        var range = selection.getRangeAt(0);

        // Get path and verify nodes exist
        var startPath = this.getNodePath(range.startContainer);
        var endPath = this.getNodePath(range.endContainer);

        if (!startPath || !endPath) {
            console.warn("Could not save selection - invalid node path");
            return;
        }

        // Store selection state
        this.savedSelection = {
            start: startPath,
            startOffset: range.startOffset,
            end: endPath,
            endOffset: range.endOffset,
            collapsed: range.collapsed,
            startIsText: range.startContainer.nodeType === 3,
            endIsText: range.endContainer.nodeType === 3
        };
    } catch (error) {
        console.error("Error saving selection:", error);
        this.savedSelection = null;
    }
},

getNodeFromPath: function(root, path) {
    if (!path || !path.length) return null;

    var node = root;

    try {
        for (var i = 0; i < path.length; i++) {
            var pathItem = path[i];
            var found = false;

            // Handle case where node has no children
            if (!node.childNodes || !node.childNodes.length) {
                return node;
            }

            // Safely get child node
            var targetIndex = Math.min(pathItem.index, node.childNodes.length - 1);
            node = node.childNodes[targetIndex];

            // Verify node type matches
            if (node.nodeType !== pathItem.nodeType || node.nodeName !== pathItem.nodeName) {
                // Try to find a matching node
                for (var j = 0; j < node.parentNode.childNodes.length; j++) {
                    var sibling = node.parentNode.childNodes[j];
                    if (sibling.nodeType === pathItem.nodeType &&
                        sibling.nodeName === pathItem.nodeName) {
                        node = sibling;
                        found = true;
                        break;
                    }
                }

                if (!found) {
                    // If no matching node found, return the current one
                    return node;
                }
            }
        }

        return node;
    } catch (e) {
        console.error("Error in getNodeFromPath:", e);
        return null;
    }
},

getNodePath: function(node) {
    if (!node) return null;

    var path = [];
    var current = node;

    try {
        while (current && current.parentNode && current !== this.editor.getDoc().body) {
            var parent = current.parentNode;
            var index = 0;
            var found = false;

            for (var i = 0; i < parent.childNodes.length; i++) {
                if (parent.childNodes[i] === current) {
                    index = i;
                    found = true;
                    break;
                }
            }

            if (!found) {
                console.warn("Node not found in parent's children");
                return null;
            }

            path.unshift({
                index: index,
                nodeType: current.nodeType,
                nodeName: current.nodeName
            });

            current = parent;
        }

        return path;
    } catch (e) {
        console.error("Error in getNodePath:", e);
        return null;
    }
},
    findClosestTableCell: function(node) {
        // Start from the node itself
        let current = node;

        // If it's a text node, start from its parent
        if (current.nodeType === 3) {
            current = current.parentNode;
        }

        // Walk up the DOM tree until we find a table cell or hit the body
        while (current && current !== this.editor.getDoc().body) {
            if (current.nodeName === 'TD' || current.nodeName === 'TH') {
                return current;
            }
            current = current.parentNode;
        }
        return null;
    },

applyFont: function(font) {
    var editor = this.editor;
    var doc = editor.getDoc();
    this.currentFontFamily = font;
    var currentFontSize = this.fontSizeCombo.getValue();
    var self = this;

    setTimeout(function() {
        try {
            if (self.restoreSelection()) {
                var selection = editor.getWin().getSelection();
                var range = selection.getRangeAt(0);

                // Check if we're in a table cell
                var cell = self.findClosestTableCell(range.startContainer);
                if (cell && range.collapsed) {
                    // First, set default style for future content in the cell
                    cell.setAttribute('data-default-font', font);

                    // Create a new span at cursor position
                    var span = doc.createElement('span');
                    span.style.fontFamily = font;
                    span.style.fontSize = currentFontSize;
                    span.style.webkitTextSizeAdjust = "100%";
                    span.style.textSizeAdjust = "100%";

                    // Apply current formatting state
                    var formatState = self.getFormatState();
                    if (formatState.bold) span.style.fontWeight = 'bold';
                    if (formatState.italic) span.style.fontStyle = 'italic';
                    if (formatState.underline) span.style.textDecoration = 'underline';

                    span.innerHTML = '&#8203;'; // Zero-width space

                    // Insert at current position
                    range.insertNode(span);

                    // Move cursor inside the span
                    var newRange = doc.createRange();
                    newRange.setStartAfter(span.firstChild);
                    newRange.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(newRange);

                    // Add input handler for this cell if not already added
                    if (!cell.getAttribute('data-format-handler')) {
                        cell.setAttribute('data-format-handler', 'true');

                        var inputHandler = function(e) {
                            var target = e.target;
                            var defaultFont = cell.getAttribute('data-default-font');
                            if (!target.style.fontFamily && defaultFont) {
                                target.style.fontFamily = defaultFont;
                            }

                            // Apply current format state
                            var formatState = self.getFormatState();
                            if (formatState.bold && !target.style.fontWeight) {
                                target.style.fontWeight = 'bold';
                            }
                            if (formatState.italic && !target.style.fontStyle) {
                                target.style.fontStyle = 'italic';
                            }
                            if (formatState.underline && !target.style.textDecoration) {
                                target.style.textDecoration = 'underline';
                            }
                        };

                        cell.addEventListener('input', inputHandler);
                    }
                } else if (!range.collapsed) {
                    // Handle selected text
                    var fragment = range.extractContents();
                    var wrapper = doc.createElement('div');
                    wrapper.appendChild(fragment);

                    var processNode = function(node) {
                        if (node.nodeType === 3 && node.textContent.trim()) { // Text node
                            var span = doc.createElement('span');
                            span.style.fontFamily = font;
                            span.style.webkitTextSizeAdjust = "100%";
                            span.style.textSizeAdjust = "100%";

                            // Preserve existing styles
                            var parent = node.parentNode;
                            if (parent.style) {
                                if (parent.style.fontSize) span.style.fontSize = parent.style.fontSize;
                                if (parent.style.fontWeight === 'bold') span.style.fontWeight = 'bold';
                                if (parent.style.fontStyle === 'italic') span.style.fontStyle = 'italic';
                                if (parent.style.textDecoration === 'underline') span.style.textDecoration = 'underline';
                            }

                            node.parentNode.insertBefore(span, node);
                            span.appendChild(node);
                        } else if (node.nodeType === 1) { // Element node
                            if (node.nodeName === 'SPAN') {
                                node.style.fontFamily = font;
                            }
                            // Process children
                            for (var i = 0; i < node.childNodes.length; i++) {
                                processNode(node.childNodes[i]);
                            }
                        }
                    };

                    // Process all nodes in the fragment
                    for (var i = 0; i < wrapper.childNodes.length; i++) {
                        processNode(wrapper.childNodes[i]);
                    }

                    // Insert the modified content back
                    range.insertNode(wrapper);

                    // Move the content out of the wrapper
                    while (wrapper.firstChild) {
                        wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                    }
                    wrapper.remove();

                    // Restore selection
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    // Handle collapsed selection outside table
                    var formatState = self.getFormatState();
                    var styleString = 'font-family:' + font + '; font-size:' + currentFontSize + ';' +
                        '-webkit-text-size-adjust: 100%; text-size-adjust: 100%;';

                    if (formatState.bold) styleString += ' font-weight: bold;';
                    if (formatState.italic) styleString += ' font-style: italic;';
                    if (formatState.underline) styleString += ' text-decoration: underline;';

                    var placeholder = '<span style="' + styleString + '">&#8203;</span>';
                    doc.execCommand("insertHTML", false, placeholder);
                }

                self.saveSelection();
                doc.body.focus();
                editor.syncValue();
                editor.focus();
            }
        } catch (error) {
            console.error("Error applying font:", error);
        }
    }, 10);
},

getFormatState: function() {
    const editor = this.editor;
    const doc = editor.getDoc();

    return {
        bold: doc.queryCommandState('bold'),
        italic: doc.queryCommandState('italic'),
        underline: doc.queryCommandState('underline')
    };
},


applyFontSize: function(size) {
    var editor = this.editor;
    var doc = editor.getDoc();
    this.currentFontSize = size + "px";
    var currentFontFamily = this.fontCombo.getValue();
    var self = this;

    setTimeout(function() {
        try {
            if (self.restoreSelection()) {
                var selection = editor.getWin().getSelection();
                var range = selection.getRangeAt(0);

                // Check if we're in a table cell
                var cell = self.findClosestTableCell(range.startContainer);
                if (cell && range.collapsed) {
                    // First, set default style for future content in the cell
                    cell.setAttribute('data-default-size', size + 'px');

                    // Create a new span at cursor position
                    var span = doc.createElement('span');
                    span.style.fontFamily = currentFontFamily;
                    span.style.fontSize = size + 'px';
                    span.style.webkitTextSizeAdjust = "100%";
                    span.style.textSizeAdjust = "100%";

                    // Apply current formatting state
                    var formatState = self.getFormatState();
                    if (formatState.bold) span.style.fontWeight = 'bold';
                    if (formatState.italic) span.style.fontStyle = 'italic';
                    if (formatState.underline) span.style.textDecoration = 'underline';

                    span.innerHTML = '&#8203;'; // Zero-width space

                    // Insert at current position
                    range.insertNode(span);

                    // Move cursor inside the span
                    var newRange = doc.createRange();
                    newRange.setStartAfter(span.firstChild);
                    newRange.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(newRange);

                    // Add input handler for this cell if not already added
                    if (!cell.getAttribute('data-size-handler')) {
                        cell.setAttribute('data-size-handler', 'true');

                        var inputHandler = function(e) {
                            var target = e.target;
                            var defaultSize = cell.getAttribute('data-default-size');
                            if (!target.style.fontSize && defaultSize) {
                                target.style.fontSize = defaultSize;
                            }

                            // Apply current format state
                            var formatState = self.getFormatState();
                            if (formatState.bold && !target.style.fontWeight) {
                                target.style.fontWeight = 'bold';
                            }
                            if (formatState.italic && !target.style.fontStyle) {
                                target.style.fontStyle = 'italic';
                            }
                            if (formatState.underline && !target.style.textDecoration) {
                                target.style.textDecoration = 'underline';
                            }
                        };

                        cell.addEventListener('input', inputHandler);
                    }
                } else if (!range.collapsed) {
                    // Handle selected text
                    var fragment = range.extractContents();
                    var wrapper = doc.createElement('div');
                    wrapper.appendChild(fragment);

                    // Process text nodes and their containers within the extracted content
                    var processNode = function(node) {
                        if (node.nodeType === 3 && node.textContent.trim()) { // Text node
                            var span = doc.createElement('span');
                            span.style.fontSize = size + 'px';
                            span.style.webkitTextSizeAdjust = "100%";
                            span.style.textSizeAdjust = "100%";

                            // Preserve existing font family
                            var parent = node.parentNode;
                            var existingFontFamily = null;
                            while (parent && parent !== wrapper) {
                                if (parent.style && parent.style.fontFamily) {
                                    existingFontFamily = parent.style.fontFamily;
                                    break;
                                }
                                parent = parent.parentNode;
                            }
                            span.style.fontFamily = existingFontFamily || currentFontFamily;

                            // Preserve formatting
                            if (parent.style) {
                                if (parent.style.fontWeight === 'bold') span.style.fontWeight = 'bold';
                                if (parent.style.fontStyle === 'italic') span.style.fontStyle = 'italic';
                                if (parent.style.textDecoration === 'underline') span.style.textDecoration = 'underline';
                            }

                            node.parentNode.insertBefore(span, node);
                            span.appendChild(node);
                        } else if (node.nodeType === 1) { // Element node
                            // For existing spans, update font size
                            if (node.nodeName === 'SPAN') {
                                node.style.fontSize = size + 'px';
                                // Preserve existing styles
                                var existingStyles = node.getAttribute('style');
                                if (existingStyles) {
                                    node.style.fontSize = size + 'px';
                                }
                            }
                            // Process children
                            Array.from(node.childNodes).forEach(processNode);
                        }
                    };

                    // Process all nodes in the fragment
                    Array.from(wrapper.childNodes).forEach(processNode);

                    // Insert the modified content back
                    range.insertNode(wrapper);

                    // Move the content out of the wrapper
                    while (wrapper.firstChild) {
                        wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                    }
                    wrapper.remove();

                    // Restore selection
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    // Handle collapsed selection outside table
                    var formatState = self.getFormatState();
                    var styleString = 'font-family:' + currentFontFamily + '; font-size:' + size + 'px;' +
                        '-webkit-text-size-adjust: 100%; text-size-adjust: 100%;';

                    if (formatState.bold) styleString += ' font-weight: bold;';
                    if (formatState.italic) styleString += ' font-style: italic;';
                    if (formatState.underline) styleString += ' text-decoration: underline;';

                    var placeholder = '<span style="' + styleString + '">&#8203;</span>';
                    doc.execCommand("insertHTML", false, placeholder);
                }

                self.saveSelection();
                doc.body.focus();
                editor.syncValue();
                editor.focus();
            }
        } catch (error) {
            console.error("Error applying font size:", error);
        }
    }, 10);
},

    isFontAvailable: function(font) {
        const testString = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        const testSize = "72px";
        const defaultFont = "monospace";

        const canvas = document.createElement("canvas");
        const context = canvas.getContext("2d");

        context.font = `${testSize} ${defaultFont}`;
        const defaultWidth = context.measureText(testString).width;

        context.font = `${testSize} ${font}, ${defaultFont}`;
        const testWidth = context.measureText(testString).width;

        return testWidth !== defaultWidth;
    },

    attachSelectionListeners: function() {
        const editor = this.editor;
        const doc = editor.getDoc();

        doc.addEventListener("mouseup", () => this.saveSelection());
        doc.addEventListener("keyup", (e) => {
            if (e.key !== "Enter" && e.key !== "Tab") {
                this.saveSelection();
            }
        });
        doc.addEventListener("keydown", (e) => {
            if (e.key !== "Enter" && e.key !== "Tab") {
                this.saveSelection();
            }
        });
    },

    addPlaceholderOnChange: function() {
        this.fontCombo.on("select", () => {
            if (!this.savedSelection || this.savedSelection.collapsed) {
                this.insertStyledPlaceholder(this.fontCombo.getValue(), this.fontSizeCombo.getValue());
            }
        });

        this.fontSizeCombo.on("select", () => {
            if (!this.savedSelection || this.savedSelection.collapsed) {
                this.insertStyledPlaceholder(this.fontCombo.getValue(), this.fontSizeCombo.getValue());
            }
        });
    },

    insertStyledPlaceholder: function(font, size) {
        const editor = this.editor;
        const doc = editor.getDoc();

        const placeholder = `<span style="font-family:${font}; font-size:${size};
            -webkit-text-size-adjust: 100%; text-size-adjust: 100%;">&#8203;</span>`;
        doc.execCommand("insertHTML", false, placeholder);

        doc.body.focus();
        editor.syncValue();
        editor.focus();
    },

setupFormatHandlers: function() {
    var editor = this.editor;
    var doc = editor.getDoc();
    var self = this;

    // Watch for format changes
    var formatCommands = ['bold', 'italic', 'underline'];
    formatCommands.forEach(function(command) {
        editor.on(command, function() {
            var selection = doc.getSelection();
            if (!selection.rangeCount) return;

            var range = selection.getRangeAt(0);
            var cell = self.findClosestTableCell(range.startContainer);

            if (cell) {
                // Update current node with new format
                var currentNode = range.startContainer;
                if (currentNode.nodeType === 3) {
                    currentNode = currentNode.parentNode;
                }

                var formatState = self.getFormatState();
                if (command === 'bold') {
                    currentNode.style.fontWeight = formatState.bold ? 'bold' : '';
                }
                if (command === 'italic') {
                    currentNode.style.fontStyle = formatState.italic ? 'italic' : '';
                }
                if (command === 'underline') {
                    currentNode.style.textDecoration = formatState.underline ? 'underline' : '';
                }
            }
        });
    });
}
});

// Register the plugin
Ext.preg("htmleditorfontselector", Ext.ux.form.HtmlEditor.FontSelector);
