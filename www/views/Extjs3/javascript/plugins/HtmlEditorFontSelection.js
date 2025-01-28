Ext.namespace("Ext.ux.form.HtmlEditor");

Ext.ux.form.HtmlEditor.FontSelector = Ext.extend(Ext.util.Observable, {
        availableFonts: [
                "Verdana", "Arial", "Tahoma", "Helvetica", "Times New Roman", "Georgia",
                "Courier New", "Comic Sans MS", "Impact"
        ],

        defaultFontPriority: ["Helvetica", "Verdana", "Arial", "Tahoma"],

        constructor: function(config) {
                Ext.apply(this, config || {});
                this.savedSelection = null;
                // Get default font from defaultFontPriority
                this.DEFAULT_FONT = this.defaultFontPriority[0];
                this.DEFAULT_SIZE = "14px";
                // Initialize current values to defaults
                this.currentFontFamily = this.DEFAULT_FONT;
                this.currentFontSize = this.DEFAULT_SIZE;
                this.fontWasDropdownOpen=false,
                        this.fontSizeWasDropdownOpen=false,
                        Ext.ux.form.HtmlEditor.FontSelector.superclass.constructor.call(this);
        },

        init: function(editor) {
                this.editor = editor;

                // Add existing initialization code
                this.ensureViewportMeta();

                editor.on("initialize", () => {
                        //this.attachSelectionListeners();
                        this.addKeyHandling();
                        this.setupFormatHandlers();
                });

                editor.on("render", this.addFontSelectors, this);
                editor.on("initialize", this.addDynamicDropdownUpdates, this);

                this.setupComposerListeners();

        },

        setupComposerListeners: function() {
                var me = this;
                var composerFound = false;

                // Function to hook up composer events
                var hookComposerEvents = function(composer) {
                        if (!composer || composerFound) return;

                        composerFound = true;

                        composer.on('reset', function() {
                                me.resetFontSelectors();
                        }, me);

                        composer.on('afterShowAndLoad', function() {
                                me.resetFontSelectors();
                        }, me);

                        composer.on('show', function() {
                                me.resetFontSelectors();
                        }, me);
                };

                // Try immediate hierarchy
                var tryHierarchy = function() {
                        if (me.editor.ownerCt && me.editor.ownerCt.ownerCt) {
                                var possibleComposer = me.editor.ownerCt.ownerCt;
                                if (possibleComposer instanceof GO.email.EmailComposer) {
                                        hookComposerEvents(possibleComposer);
                                        return true;
                                }
                        }
                        return false;
                };

                // Try GO.email.composers
                var tryComposersArray = function() {
                        if (GO.email && GO.email.composers && GO.email.composers.length > 0) {
                                var activeComposer = GO.email.composers.find(function(composer) {
                                        return composer.isVisible();
                                });
                                if (activeComposer) {
                                        hookComposerEvents(activeComposer);
                                        return true;
                                }
                        }
                        return false;
                };

                // Initial attempts
                if (!tryHierarchy() && !tryComposersArray()) {
                        // If neither method works, set up a timeout to keep trying
                        var attempts = 0;
                        var maxAttempts = 20; // 2 seconds max

                        var tryAgain = function() {
                                if (composerFound || attempts >= maxAttempts) return;

                                attempts++;

                                if (!tryHierarchy() && !tryComposersArray()) {
                                        setTimeout(tryAgain, 100);
                                }
                        };

                        // Start trying
                        setTimeout(tryAgain, 100);
                }
        },
        ensureViewportMeta: function() {
                if (!document.querySelector('meta[name="viewport"]')) {
                        const meta = document.createElement('meta');
                        meta.name = 'viewport';
                        meta.content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no';
                        document.head.appendChild(meta);
                }
        },


        resetFontSelectors: function() {
                if (!this.fontSelect || !this.fontSizeSelect) {
                        return;
                }


                this.fontSelect.setValue(this.DEFAULT_FONT);
                this.currentFontFamily = this.DEFAULT_FONT;

                this.fontSizeSelect.setValue(this.DEFAULT_SIZE);
                this.currentFontSize = this.DEFAULT_SIZE;

                if (this.editor && this.editor.getDoc()) {
                        this.updateCurrentFontSettings(this.editor.getDoc().body);
                }
        },

        getCurrentBlockStyle: function() {
                const selection = this.editor.getDoc().getSelection();
                if (!selection.rangeCount) return {};

                const range = selection.getRangeAt(0);
                let currentNode = range.startContainer;

                if (currentNode.nodeType === 3) {
                        currentNode = currentNode.parentNode;
                }

                const cell = currentNode.closest('td, th');
                if (cell) {
                        const computedStyle = window.getComputedStyle(cell);
                        return {
                                fontFamily: computedStyle.fontFamily.replace(/['"]/g, '') || this.currentFontFamily,
                                        fontSize: computedStyle.fontSize || this.currentFontSize
                                };
                }

                return {
                        fontFamily: this.fontSelect.value || this.currentFontFamily,
                        fontSize: this.fontSizeSelect.value || this.currentFontSize
                };
        },

        handleTempSpanFocus: function(shouldRemoveSpan = false) {
                const editor = this.editor;
                const doc = editor.getDoc();
                const tempSpan = doc.querySelector('span[id^="temp-fontchange-"]');

                if (!tempSpan) return;

                // Focus the editor
                editor.getWin().focus();
                editor.focus();
                tempSpan.focus();

                // Set cursor position
                const range = doc.createRange();
                const textNode = tempSpan.firstChild;

                if (textNode) {
                        range.setStart(textNode, 0);
                        range.setEnd(textNode, textNode.length);
                } else {
                        range.selectNodeContents(tempSpan);
                }

                const selection = doc.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                range.collapse(true);

                if (shouldRemoveSpan) {
                        const textNode = tempSpan.firstChild;
                        if (textNode) {
                                tempSpan.parentNode.replaceChild(textNode, tempSpan);
                        } else {
                                tempSpan.remove();
                        }
                }
        },

        handleSelectorBlur: function() {
                setTimeout(() => {
                        this.handleTempSpanFocus(true);
                }, 10);
        },

        handleSelectorChange: function(selectorType, newValue) {
                const editor = this.editor;
                const doc = editor.getDoc();

                // Check if we have a saved selection
                if (this.savedSelection) {
                        // Try to restore the saved selection
                        if (this.restoreSelection()) {
                                // If restored successfully, format the selected text
                                this.handleSelectionFormatting(selectorType, newValue);
                                this.savedSelection = null; // Clear saved selection
                                return;
                        }
                }

                const tempSpan = doc.querySelector('span[id^="temp-fontchange-"]');

                if (!tempSpan) return;

                const currentValue = selectorType === 'family' ?
                        window.getComputedStyle(tempSpan).fontFamily.replace(/['"]/g, '') :
                                window.getComputedStyle(tempSpan).fontSize;

                                if (currentValue === newValue) {
                                        // No change in value, remove span
                                        this.handleTempSpanFocus(true);
                                } else {
                                        // Value changed, update styles and remove id
                                        const fontFamily = selectorType === 'family' ? newValue : null;
                                        const fontSize = selectorType === 'size' ? newValue : null;
                                        this.updateFontStyle(fontFamily, fontSize);
                                        // Focus but don't remove span (updateFontStyle will handle the span)
                                        this.handleTempSpanFocus(false);
                                }
        },

        addFontSelectors: function() {
                const editor = this.editor;
                const toolbar = editor.getToolbar();

                // Create font family combo with select-like behavior
                this.fontSelect = new Ext.form.ComboBox({
                        editable: false,
                        forceSelection: true,
                        triggerAction: 'all',
                        mode: 'local',
                        width: 150,
                        store: new Ext.data.ArrayStore({
                                fields: ['font'],
                                data: this.availableFonts.filter(font => this.isFontAvailable(font)).map(font => [font])
                        }),
                        displayField: 'font',
                        valueField: 'font',
                        value: this.DEFAULT_FONT,
                        tpl: new Ext.XTemplate(
                                '<tpl for=".">',
                                '<div class="x-combo-list-item" style="font-family:{font};">{font}</div>',
                                '</tpl>'
                        ),
                        // Prevent text selection
                        listeners: {
                                afterrender: function(combo) {
                                        // Make the input element behave more like a select
                                        combo.el.dom.setAttribute('readonly', 'readonly');
                                        combo.el.dom.style.cursor = 'default';
                                        combo.el.on('mousedown', function(e) {
                                                e.preventDefault();
                                        });
                                },
                                expand: () => {
                                        const selection = this.editor.getDoc().getSelection();
                                        if (selection.isCollapsed || selection.type === "Caret") {
                                                this.handleSelectorExpand();
                                        } else {
                                                this.saveSelection();
                                        }
                                },
                                select: (combo, record) => {
                                        const newValue = record.get('font');
                                        const selection = this.editor.getDoc().getSelection();
                                        if (selection.isCollapsed || selection.type === "Caret") {
                                                this.handleSelectorChange('family', newValue);
                                        } else {
                                                this.handleSelectionFormatting('family', newValue);
                                                selection.removeAllRanges();
                                        }
                                        // Force focus back to editor after brief delay
                                        setTimeout(() => {
                                                const win = this.editor.getWin();
                                                const doc = this.editor.getDoc();

                                                win.focus();

                                                doc.body.focus();

                                                this.editor.focus();

                                                const tempSpan = doc.querySelector('span[id^="temp-fontchange-"]');

                                                if (tempSpan) {
                                                        const range = doc.createRange();
                                                        range.setStart(tempSpan.firstChild, 1);
                                                        range.collapse(true);

                                                        const sel = doc.getSelection();
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);

                                                        // Try forcing focus again after selection
                                                        win.focus();
                                                        doc.body.focus();
                                                        this.editor.focus();
                                                }
                                        }, 10);

                                        // Add a second timeout to double-check focus
                                        setTimeout(() => {
                                                if (document.activeElement.tagName === 'INPUT') {
                                                        this.editor.focus();
                                                }
                                        }, 50);
                                },
                                collapse: () => {
                                        // When dropdown closes, ensure editor gets focus back
                                        setTimeout(() => {
                                                this.editor.focus();
                                        }, 10);
                                }
                        }
                });

                // Create font size combo with select-like behavior
                this.fontSizeSelect = new Ext.form.ComboBox({
                        editable: false,
                        forceSelection: true,
                        triggerAction: 'all',
                        mode: 'local',
                        width: 90,
                        store: new Ext.data.ArrayStore({
                                fields: ['size'],
                                data: Array.from({length: 69}, (_, i) => [(i + 4) + 'px'])
                        }),
                        displayField: 'size',
                        valueField: 'size',
                        value: this.DEFAULT_SIZE,
                        // Prevent text selection
                        listeners: {
                                afterrender: function(combo) {
                                        // Make the input element behave more like a select
                                        combo.el.dom.setAttribute('readonly', 'readonly');
                                        combo.el.dom.style.cursor = 'default';
                                        combo.el.on('mousedown', function(e) {
                                                e.preventDefault();
                                        });
                                },
                                expand: () => {
                                        const selection = this.editor.getDoc().getSelection();
                                        if (selection.isCollapsed || selection.type === "Caret") {
                                                this.handleSelectorExpand();
                                        } else {
                                                this.saveSelection();
                                        }
                                },
                                select: (combo, record) => {
                                        const newValue = record.get('size');
                                        const selection = this.editor.getDoc().getSelection();
                                        if (selection.isCollapsed || selection.type === "Caret") {
                                                this.handleSelectorChange('size', newValue);
                                        } else {
                                                this.handleSelectionFormatting('size', newValue);
                                                selection.removeAllRanges();
                                        }
                                        // Force focus back to editor after brief delay
                                        setTimeout(() => {
                                                const win = this.editor.getWin();
                                                const doc = this.editor.getDoc();

                                                win.focus();

                                                doc.body.focus();

                                                this.editor.focus();

                                                const tempSpan = doc.querySelector('span[id^="temp-fontchange-"]');

                                                if (tempSpan) {
                                                        const range = doc.createRange();
                                                        range.setStart(tempSpan.firstChild, 1);
                                                        range.collapse(true);

                                                        const sel = doc.getSelection();
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);

                                                        // Try forcing focus again after selection
                                                        win.focus();
                                                        doc.body.focus();
                                                        this.editor.focus();
                                                }
                                        }, 10);

                                        // Add a second timeout to double-check focus
                                        setTimeout(() => {
                                                if (document.activeElement.tagName === 'INPUT') {
                                                        this.editor.focus();
                                                }
                                        }, 50);
                                },
                                collapse: () => {
                                        // When dropdown closes, ensure editor gets focus back
                                        setTimeout(() => {
                                                this.editor.focus();
                                        }, 10);
                                }
                        }
                });

                // Add items to toolbar at the beginning
                toolbar.insertButton(0, this.fontSizeSelect);
                toolbar.insertButton(0, this.fontSelect);

                // Update layout
                toolbar.doLayout();
        },
        handleSelectorExpand: function() {
                const editor = this.editor;
                const doc = editor.getDoc();
                const selection = doc.getSelection();

                if (!selection.rangeCount) return null;

                // First, check for and remove any existing temp spans
                const existingSpans = doc.querySelectorAll('span[id^="temp-fontchange-"]');
                existingSpans.forEach(span => {
                        const textNode = span.firstChild;
                        if (textNode) {
                                span.parentNode.replaceChild(textNode, span);
                        } else {
                                span.remove();
                        }
                });

                const range = selection.getRangeAt(0);
                const tempId = `temp-fontchange-${Date.now()}`;

                // Create span with current styles and space
                const span = doc.createElement('span');
                span.id = tempId;
                span.style.fontFamily = this.currentFontFamily;
                span.style.fontSize = this.currentFontSize;
                span.style.webkitTextSizeAdjust = "100%";
                span.style.textSizeAdjust = "100%";
                span.innerHTML = '&#8203;';

                // Insert span at current position
                range.insertNode(span);
                const newRange = doc.createRange();
                newRange.setStart(span.firstChild, 0);
                newRange.setEnd(span.firstChild, 1);
                selection.removeAllRanges();
                selection.addRange(newRange);

                // Simple focus
                editor.getWin().focus();
                editor.focus();
                editor.syncValue();

                selection.removeAllRanges();

                return {
                        id: tempId,
                        initialStyles: {
                                fontFamily: this.currentFontFamily,
                                fontSize: this.currentFontSize
                        }
                };
        },

        cleanupUnusedSpans: function() {
                const doc = this.editor.getDoc();
                const tempSpans = doc.querySelectorAll('span[id^="temp-fontchange-"]');
                tempSpans.forEach(span => {
                        const textNode = span.firstChild;
                        if (textNode) {
                                span.parentNode.replaceChild(textNode, span);
                        } else {
                                span.remove();
                        }
                });
        },

        calculateLineHeight: function(fontSize) {
                // Extract numeric value from font size
                const size = parseInt(fontSize);
                // Base multiplier for line height (adjust these values as needed)
                let multiplier;
                if (size <= 14) multiplier = 1.5;       // Smaller text needs more spacing
                else if (size <= 24) multiplier = 1.4;   // Medium text
                else if (size <= 36) multiplier = 1.3;   // Larger text
                else multiplier = 1.2;                   // Very large text needs less relative spacing

                return multiplier;
        },

        updateCurrentFontSettings: function(node) {
                const computedStyle = window.getComputedStyle(node);
                const fontFamily = computedStyle.fontFamily.replace(/['"]/g, "").split(",")[0];
                        let fontSize = node.style.fontSize;

                        // If no explicit size set, use computed style
                        if (!fontSize) {
                                fontSize = computedStyle.fontSize;
                        }

                        // Update the combo values and internal state
                        if (fontFamily) {
                                this.currentFontFamily = fontFamily;
                                if (this.fontSelect.getValue() !== fontFamily) {
                                        this.fontSelect.setValue(fontFamily);
                                }
                        }

                        if (fontSize) {
                                this.currentFontSize = fontSize;
                                if (this.fontSizeSelect.getValue() !== fontSize) {
                                        this.fontSizeSelect.setValue(fontSize);
                                }
                        }
        },

        addDynamicDropdownUpdates: function() {
                const editor = this.editor;
                const doc = editor.getDoc();

                const updateDropdowns = () => {
                        const selection = doc.getSelection();
                        if (!selection.rangeCount) return;

                        const range = selection.getRangeAt(0);
                        let node = range.startContainer;

                        // If it's a text node, get its parent
                        if (node.nodeType === 3) {
                                node = node.parentNode;
                        }

                        // Check for temp span specifically
                        const tempSpan = node.closest('span[id^="temp-fontchange-"]');
                        if (tempSpan) {
                                node = tempSpan;
                        }

                        // Update the ComboBox values
                        this.updateCurrentFontSettings(node);
                };

                // ExtJS event handling for the editor
                editor.on('click', updateDropdowns);
                editor.on('keyup', (editor, e) => {
                        if (e.key !== "Enter" && e.key !== "Tab") {
                                updateDropdowns();
                        }
                });

                // Also handle mouse selection
                editor.on('selectionchange', updateDropdowns);

                // Additional DOM events for redundancy
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

                // Just insert BR
                const br = doc.createElement('br');
                range.insertNode(br);

                // Add zero-width space if at the end of content
                const isAtEnd = !br.nextSibling ||
                        (br.nextSibling.nodeType === 3 && !br.nextSibling.textContent.trim());

                if (isAtEnd) {
                        const zwsp = doc.createTextNode('\u200B');
                        br.parentNode.insertBefore(zwsp, br.nextSibling);
                }

                // Move cursor after the BR
                const newRange = doc.createRange();
                newRange.setStartAfter(br);
                newRange.collapse(true);
                selection.removeAllRanges();
                selection.addRange(newRange);

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

        isAtStartOfNode: function(range) {
                try {
                        // If range is not collapsed, it's not at start
                        if (!range.collapsed) {
                                return false;
                        }

                        // Get the containing block element
                        let container = range.startContainer;

                        // If we're in a text node
                        if (container.nodeType === 3) {
                                // Check if we're at position 0 of the text node
                                if (range.startOffset > 0) {
                                        return false;
                                }
                                // If at start of text node, check if it's the first text node in its parent
                                container = container.parentNode;
                        }

                        // Navigate through previous siblings
                        let previousNode = container.previousSibling;
                        while (previousNode) {
                                // Skip empty text nodes
                                if (previousNode.nodeType === 3 && previousNode.nodeValue.trim() === '') {
                                        previousNode = previousNode.previousSibling;
                                        continue;
                                }
                                // If we find any non-empty previous node, we're not at the start
                                return false;
                        }

                        return true;
                } catch (e) {
                        console.error('Error in isAtStartOfNode:', e);
                        return false;
                }
        },

        handleBackspaceKey: function(e) {
                const editor = this.editor;
                const doc = editor.getDoc();
                const selection = doc.getSelection();

                if (!selection.rangeCount) {
                        return;
                }

                const range = selection.getRangeAt(0);
                if (!range.collapsed) return;

                // Find if we're next to a table
                let node = range.startContainer;
                let offset = range.startOffset;

                // If in text node, check if we're at the start
                if (node.nodeType === 3 && offset === 0) {
                        const prevSibling = node.parentNode.previousSibling;
                        node = node.parentNode;
                        // Check for table
                        if (prevSibling && prevSibling.nodeName === 'TABLE') {
                                e.preventDefault();
                                node.previousSibling.remove();

                                editor.syncValue();
                                editor.focus();
                                return;
                        }
                }

                editor.syncValue();
                // Let browser handle all other cases
                return;
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

        getFormatState: function() {
                const editor = this.editor;
                const doc = editor.getDoc();

                return {
                        bold: doc.queryCommandState('bold'),
                        italic: doc.queryCommandState('italic'),
                        underline: doc.queryCommandState('underline')
                };
        },


        // First, add this new method to handle span creation and style application
        createStyledSpan: function(styles) {
                var editor = this.editor;
                var doc = editor.getDoc();

                var span = doc.createElement('span');

                span.style.cssText = `
        font-family: ${styles.fontFamily || this.currentFontFamily};
        font-size: ${styles.fontSize || this.currentFontSize};
        -webkit-text-size-adjust: 100% !important;
        -moz-text-size-adjust: 100% !important;
        -ms-text-size-adjust: 100% !important;
        text-size-adjust: 100% !important;
        display: inline-block;
        vertical-align: baseline;
    `;


                // Apply formatting if present
                if (styles.bold) span.style.fontWeight = 'bold';
                if (styles.italic) span.style.fontStyle = 'italic';
                if (styles.underline) span.style.textDecoration = 'underline';

                // Add content or placeholder
                span.innerHTML = styles.content || '&#8203;';

                return span;
        },

        handleSelectionFormatting: function(type, value) {
                const editor = this.editor;
                const doc = editor.getDoc();
                const selection = doc.getSelection();

                if (!selection.rangeCount) return;

                // Save current selection
                this.saveSelection();

                const range = selection.getRangeAt(0);
                if (range.collapsed) return; // No text selected

                // Get the current styles to maintain other formatting
                const currentStyles = this.getCurrentBlockStyle();

                try {
                        // Extract the selected content
                        const selectedContent = range.extractContents();

                        // Create a new span with the updated styles
                        const span = doc.createElement('span');

                        // Set font styles based on type (family or size)
                        if (type === 'family') {
                                span.style.fontFamily = value;
                                span.style.fontSize = currentStyles.fontSize;
                        } else if (type === 'size') {
                                span.style.fontSize = value;
                                span.style.fontFamily = currentStyles.fontFamily;
                        }

                        // Set line height based on font size
                        const fontSize = type === 'size' ? value : currentStyles.fontSize;
                        const lineHeight = this.calculateLineHeight(fontSize);
                        span.style.lineHeight = lineHeight.toString();

                        // Ensure text size adjust is applied
                        span.style.webkitTextSizeAdjust = "100%";
                        span.style.textSizeAdjust = "100%";

                        // Preserve other styles
                        const formatState = this.getFormatState();
                        if (formatState.bold) span.style.fontWeight = 'bold';
                        if (formatState.italic) span.style.fontStyle = 'italic';
                        if (formatState.underline) span.style.textDecoration = 'underline';

                        // Add the selected content to the span
                        span.appendChild(selectedContent);

                        // Insert the span at the current selection
                        range.insertNode(span);

                        // Update selection to include the new span
                        range.selectNode(span);
                        selection.removeAllRanges();
                        selection.addRange(range);

                        // Update current font settings
                        if (type === 'family') {
                                this.currentFontFamily = value;
                                this.fontSelect.value = value;
                        } else if (type === 'size') {
                                this.currentFontSize = value;
                                this.fontSizeSelect.value = value;
                        }

                        // Restore editor focus
                        editor.focus();
                        editor.syncValue();
                } catch (e) {
                        console.error('Error formatting selection:', e);
                        // Try to restore the selection if something went wrong
                        this.restoreSelection();
                }
        },

        mergeTextNodes: function(container) {
                let current = container.firstChild;
                while (current && current.nextSibling) {
                        if (current.nodeType === 3 && current.nextSibling.nodeType === 3) {
                                current.nodeValue += current.nextSibling.nodeValue;
                                container.removeChild(current.nextSibling);
                        } else {
                                current = current.nextSibling;
                        }
                }
        },

        // Add method to normalize spans
        normalizeSpans: function(container) {
                const spans = container.querySelectorAll('span');
                spans.forEach(span => {
                        // Remove empty spans
                        if (!span.textContent.trim()) {
                                span.remove();
                                return;
                        }

                        // Merge adjacent spans with identical styling
                        const nextSibling = span.nextElementSibling;
                        if (nextSibling && nextSibling.tagName === 'SPAN') {
                                const spanStyle = window.getComputedStyle(span);
                                const siblingStyle = window.getComputedStyle(nextSibling);

                                if (spanStyle.fontFamily === siblingStyle.fontFamily &&
                                        spanStyle.fontSize === siblingStyle.fontSize &&
                                        spanStyle.fontWeight === siblingStyle.fontWeight &&
                                        spanStyle.fontStyle === siblingStyle.fontStyle &&
                                        spanStyle.textDecoration === siblingStyle.textDecoration) {

                                        span.innerHTML += nextSibling.innerHTML;
                                        nextSibling.remove();
                                }
                        }

                        // Merge text nodes within span
                        this.mergeTextNodes(span);
                });
        },


        updateFontStyle: function(fontFamily, fontSize) {
                const editor = this.editor;
                const doc = editor.getDoc();
                const tempSpan = doc.querySelector('span[id^="temp-fontchange-"]');

                if (!tempSpan) return;

                const currentFamilyValue = window.getComputedStyle(tempSpan).fontFamily.replace(/['"]/g, '');
                        const currentSizeValue = window.getComputedStyle(tempSpan).fontSize;
                        const newFamilyValue = fontFamily || currentFamilyValue;
                        const newSizeValue = fontSize || currentSizeValue;

                        // If no change in values, remove the temporary span
                        if (currentFamilyValue === newFamilyValue && currentSizeValue === newSizeValue) {
                                const textNode = tempSpan.firstChild;
                                if (textNode) {
                                        tempSpan.parentNode.replaceChild(textNode, tempSpan);
                                } else {
                                        this.handleTempSpanFocus(true);
                                }
                                return;
                        }

                // Update span styles
                if (fontFamily) {
                        tempSpan.style.fontFamily = fontFamily;
                        this.currentFontFamily = fontFamily;
                }
                if (fontSize) {
                        tempSpan.style.fontSize = fontSize;
                        this.currentFontSize = fontSize;
                        // Calculate and set dynamic line height based on font size
                        const lineHeight = this.calculateLineHeight(fontSize);
                        tempSpan.style.lineHeight = lineHeight;

                        // Add small vertical adjustment to align with baseline
                        const sizePx = parseInt(fontSize);
                        const vOffset = Math.max(0, Math.floor(sizePx * 0.05)); // 5% of font size
                        tempSpan.style.verticalAlign = 'baseline';
                        tempSpan.style.position = 'relative';
                        tempSpan.style.top = `${vOffset}px`;

                        // Find closest paragraph or block container
                        let container = tempSpan.closest('p, div, td, th');

                        // If container found, update its line height without changing display
                        if (container && container !== doc.body) {
                                const maxLineHeight = Math.max(
                                        parseFloat(container.style.lineHeight) || 1,
                                        lineHeight
                                );
                                container.style.lineHeight = maxLineHeight;
                                // Ensure minimum height without forcing block display
                                const minHeight = Math.ceil(sizePx * maxLineHeight);
                                container.style.minHeight = `${minHeight}px`;
                        }
                }

                // Add the new required styles
                tempSpan.style.webkitTextSizeAdjust = "100% !important";
                tempSpan.style.mozTextSizeAdjust = "100% !important";
                tempSpan.style.msTextSizeAdjust = "100% !important";
                tempSpan.style.textSizeAdjust = "100% !important";
                tempSpan.style.display = "inline-block";
                tempSpan.style.verticalAlign = "baseline";

                // Update table cell styles if within a cell
                const cell = tempSpan.closest('td, th');
                if (cell) {
                        if (fontFamily) cell.setAttribute('data-default-font', fontFamily);
                        if (fontSize) {
                                cell.setAttribute('data-default-size', fontSize);
                                const lineHeight = this.calculateLineHeight(fontSize);
                                cell.style.lineHeight = lineHeight;
                        }
                }

                // Focus and position cursor
                editor.getWin().focus();
                editor.focus();
                tempSpan.focus();

                const range = doc.createRange();
                const textNode = tempSpan.firstChild;

                if (textNode) {
                        range.setStart(textNode, 1);
                        range.setEnd(textNode, textNode.length);
                } else {
                        range.selectNodeContents(tempSpan);
                }

                const selection = doc.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                range.collapse(true);

                editor.syncValue();
                tempSpan.removeAttribute('id');
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
