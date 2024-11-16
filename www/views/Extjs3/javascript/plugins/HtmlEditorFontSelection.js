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

        // Add meta viewport tag if not present
        this.ensureViewportMeta();

        // Add mobile-specific styles to prevent text inflation
        this.addMobileStyles();

        editor.on("initialize", () => {
            this.attachSelectionListeners();
            this.addPlaceholderOnChange();
            this.addKeyHandling();
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
        doc.body.style.fontSize = "10px";
        doc.body.style.webkitTextSizeAdjust = "100%";
        doc.body.style.mozTextSizeAdjust = "100%";
        doc.body.style.msTextSizeAdjust = "100%";
        doc.body.style.textSizeAdjust = "100%";

        // Prevent mobile browsers from inflating text
        doc.body.style.maxHeight = "none";
        doc.body.style.wordWrap = "break-word";
        doc.body.style.webkitNbspMode = "space";
        doc.body.style.lineHeight = "normal";

        this.fontSizeCombo.setValue("10px");
        this.currentFontSize = "10px";

        // Add styles to the iframe's head
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
    },
    handleEnterKey: function(e) {
        const editor = this.editor;
        const doc = editor.getDoc();
        const selection = doc.getSelection();

        if (!selection.rangeCount) return;

        e.preventDefault();
        const range = selection.getRangeAt(0);

        // Get the block element we're in
        let currentBlock = range.startContainer;
        if (currentBlock.nodeType === 3) { // Text node
            currentBlock = currentBlock.parentNode;
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
        newParagraph.style.mozTextSizeAdjust = "100%";
        newParagraph.style.msTextSizeAdjust = "100%";
        newParagraph.style.textSizeAdjust = "100%";
        newParagraph.style.lineHeight = "normal";

        try {
            if (!currentBlock || currentBlock === doc.body) {
                // If we're not in a block element, create one
                newParagraph.innerHTML = '&#8203;';
                if (range.startContainer === doc.body) {
                    doc.body.appendChild(newParagraph);
                } else {
                    range.insertNode(newParagraph);
                }
            } else {
                // Split the current block at cursor position
                const clonedRange = range.cloneRange();
                clonedRange.selectNodeContents(currentBlock);
                clonedRange.setStart(range.endContainer, range.endOffset);

                // Get content that should go in new paragraph
                const fragment = clonedRange.extractContents();

                // If there's content after cursor, move it to new paragraph
                if (fragment.textContent.trim() || fragment.childNodes.length > 0) {
                    newParagraph.appendChild(fragment);
                } else {
                    newParagraph.innerHTML = '&#8203;';
                }

                // Insert new paragraph after current block
                if (currentBlock.nextSibling) {
                    currentBlock.parentNode.insertBefore(newParagraph, currentBlock.nextSibling);
                } else {
                    currentBlock.parentNode.appendChild(newParagraph);
                }

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
            currentBlock.parentNode.insertBefore(newParagraph, currentBlock.nextSibling);

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

        let currentBlock = range.startContainer;
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

    isAtStartOfNode: function(range) {
        if (!range.collapsed) return false;

        const node = range.startContainer;
        const offset = range.startOffset;

        if (node.nodeType === 3) { // Text node
            if (offset !== 0) return false;

            // Check previous siblings
            let previousNode = node;
            while (previousNode = previousNode.previousSibling) {
                if (previousNode.textContent.trim()) return false;
            }
            return true;
        }

        return offset === 0;
    },

    saveSelection: function() {
        const editor = this.editor;
        const win = editor.getWin();
        const doc = editor.getDoc();

        try {
            const selection = win.getSelection();
            if (!selection.rangeCount) return;

            const range = selection.getRangeAt(0);

            const startPath = this.getNodePath(range.startContainer);
            const endPath = this.getNodePath(range.endContainer);

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
        }
    },

    restoreSelection: function() {
        const editor = this.editor;
        const win = editor.getWin();
        const doc = editor.getDoc();

        try {
            if (!this.savedSelection) return false;

            const startNode = this.getNodeFromPath(doc.body, this.savedSelection.start);
            const endNode = this.getNodeFromPath(doc.body, this.savedSelection.end);

            if (!startNode || !endNode) {
                console.warn("Could not restore selection - nodes not found");
                return false;
            }

            const range = doc.createRange();
            const selection = win.getSelection();

            try {
                range.setStart(startNode, this.savedSelection.startOffset);
                range.setEnd(endNode, this.savedSelection.endOffset);

                selection.removeAllRanges();
                selection.addRange(range);

                return true;
            } catch (e) {
                console.warn("Could not set range:", e);
                return false;
            }
        } catch (error) {
            console.error("Error restoring selection:", error);
            return false;
        }
    },

    getNodePath: function(node) {
        const path = [];
        let current = node;

        while (current && current.parentNode && current !== this.editor.getDoc().body) {
            const parent = current.parentNode;
            let index = 0;
            let found = false;

            for (let i = 0; i < parent.childNodes.length; i++) {
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
    },
getNodeFromPath: function(root, path) {
        let node = root;

        for (let i = 0; i < path.length; i++) {
            const pathItem = path[i];
            let found = false;

            for (let j = 0; j < node.childNodes.length; j++) {
                const child = node.childNodes[j];
                if (j === pathItem.index &&
                    child.nodeType === pathItem.nodeType &&
                    child.nodeName === pathItem.nodeName) {
                    node = child;
                    found = true;
                    break;
                }
            }

            if (!found) {
                node = node.childNodes[pathItem.index] || node.lastChild;
                if (!node) return null;
            }
        }

        return node;
    },

    applyFont: function(font) {
        const editor = this.editor;
        const doc = editor.getDoc();
        this.currentFontFamily = font;
        const currentFontSize = this.fontSizeCombo.getValue();

        setTimeout(() => {
            try {
                if (this.restoreSelection()) {
                    const selection = editor.getWin().getSelection();
                    const range = selection.getRangeAt(0);

                    if (!range.collapsed) {
                        const fragment = range.extractContents();
                        const wrapper = doc.createElement('div');
                        wrapper.appendChild(fragment);

                        // Process text nodes within the extracted content
                        const textNodes = [];
                        const walk = doc.createTreeWalker(
                            wrapper,
                            NodeFilter.SHOW_TEXT,
                            null,
                            false
                        );

                        let node;
                        while (node = walk.nextNode()) {
                            textNodes.push(node);
                        }

                        // Apply font to text nodes
                        textNodes.forEach(textNode => {
                            if (textNode.textContent.trim()) {
                                const span = doc.createElement('span');
                                span.style.fontFamily = font;
                                span.style.webkitTextSizeAdjust = "100%";
                                span.style.textSizeAdjust = "100%";

                                // Preserve existing font size from immediate parent
                                const parentSpan = textNode.parentNode;
                                if (parentSpan.nodeName === 'SPAN' && parentSpan.style.fontSize) {
                                    span.style.fontSize = parentSpan.style.fontSize;
                                } else {
                                    span.style.fontSize = currentFontSize;
                                }

                                // If parent is already a span, replace its font family
                                if (parentSpan.nodeName === 'SPAN') {
                                    parentSpan.style.fontFamily = font;
                                } else {
                                    textNode.parentNode.insertBefore(span, textNode);
                                    span.appendChild(textNode);
                                }
                            }
                        });

                        // Insert the modified content back
                        range.insertNode(wrapper);

                        // Move the content out of the wrapper
                        while (wrapper.firstChild) {
                            wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                        }
                        wrapper.remove();

                    } else {
                        const placeholder = `<span style="font-family:${font}; font-size:${currentFontSize};
                            -webkit-text-size-adjust: 100%; text-size-adjust: 100%;">&#8203;</span>`;
                        doc.execCommand("insertHTML", false, placeholder);
                    }

                    this.saveSelection();
                    doc.body.focus();
                    editor.syncValue();
                    editor.focus();
                }
            } catch (error) {
                console.error("Error applying font:", error);
            }
        }, 10);
    },

    applyFontSize: function(size) {
        const editor = this.editor;
        const doc = editor.getDoc();
        this.currentFontSize = size + "px";
        const currentFontFamily = this.fontCombo.getValue();

        setTimeout(() => {
            try {
                if (this.restoreSelection()) {
                    const selection = editor.getWin().getSelection();
                    const range = selection.getRangeAt(0);

                    if (!range.collapsed) {
                        const fragment = range.extractContents();
                        const wrapper = doc.createElement('div');
                        wrapper.appendChild(fragment);

                        // Process text nodes within the extracted content
                        const textNodes = [];
                        const walk = doc.createTreeWalker(
                            wrapper,
                            NodeFilter.SHOW_TEXT,
                            null,
                            false
                        );

                        let node;
                        while (node = walk.nextNode()) {
                            textNodes.push(node);
                        }

                        // Apply font size to text nodes
                        textNodes.forEach(textNode => {
                            if (textNode.textContent.trim()) {
                                const span = doc.createElement('span');
                                span.style.fontSize = `${size}px`;
                                span.style.webkitTextSizeAdjust = "100%";
                                span.style.textSizeAdjust = "100%";

                                // Preserve existing font family from immediate parent
                                const parentSpan = textNode.parentNode;
                                if (parentSpan.nodeName === 'SPAN' && parentSpan.style.fontFamily) {
                                    span.style.fontFamily = parentSpan.style.fontFamily;
                                } else {
                                    span.style.fontFamily = currentFontFamily;
                                }

                                // If parent is already a span, replace its font size
                                if (parentSpan.nodeName === 'SPAN') {
                                    parentSpan.style.fontSize = `${size}px`;
                                } else {
                                    textNode.parentNode.insertBefore(span, textNode);
                                    span.appendChild(textNode);
                                }
                            }
                        });

                        // Insert the modified content back
                        range.insertNode(wrapper);

                        // Move the content out of the wrapper
                        while (wrapper.firstChild) {
                            wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                        }
                        wrapper.remove();

                    } else {
                        const placeholder = `<span style="font-family:${currentFontFamily}; font-size:${size}px;
                            -webkit-text-size-adjust: 100%; text-size-adjust: 100%;">&#8203;</span>`;
                        doc.execCommand("insertHTML", false, placeholder);
                    }

                    this.saveSelection();
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
    }
});

// Register the plugin
Ext.preg("htmleditorfontselector", Ext.ux.form.HtmlEditor.FontSelector);
