GO.form.HtmlEditor = function (config) {
        config = config || {};

        Ext.applyIf(config, {
                border: false,
                enableFont: false,
                headingsMenu: true
        });

        config.plugins = config.plugins || [];

        if (!Ext.isArray(config.plugins)) {
                config.plugins = [config.plugins];
        }

        var spellcheckInsertPlugin = new GO.plugins.HtmlEditorSpellCheck(this);
        var hrPlugin = new Ext.ux.form.HtmlEditor.HR();
        var ioDentPlugin = new Ext.ux.form.HtmlEditor.IndentOutdent();
        var tblPlugin = new Ext.ux.form.HtmlEditor.NEWTablePlugin();
        var fontPlugin = new Ext.ux.form.HtmlEditor.FontSelector();


        if (GO.settings.pspellSupport) {
                config.plugins.unshift(spellcheckInsertPlugin);
        }

        config.plugins.unshift(
                tblPlugin,
                fontPlugin,
                ioDentPlugin,
                hrPlugin,
        );

        if(config.headingsMenu) {
                var headingMenu = new Ext.ux.form.HtmlEditor.HeadingMenu();
                config.plugins.unshift(headingMenu);
        }

        GO.form.HtmlEditor.superclass.constructor.call(this, config);
};

Ext.extend(GO.form.HtmlEditor, Ext.form.HtmlEditor, {

        iframePad:dp(8),

        toolbarHidden: false,

        headingsMenu: true,

        initComponent: function() {
                GO.form.HtmlEditor.superclass.initComponent.apply(this);

                if(this.grow && !this.height) {
                        this.height = this.growMinHeight;
                }

                this.on('afterrender', function() {
                        if(this.grow && this.growMinHeight <= dp(46)) {
                                this.tb.hide();
                        }
                }, this);
                this.on('initialize', function(){

                        if(this.grow) {
                                this.doGrow();
                                this.on("sync", this.doGrow, this);
                        }
                },this);

                this.on('activate', function() {
                        this.registerSubmitKey();
                }, this);

                this.on('resize', function() {
                        if (this.tb.layout.moreMenu) {
                                this.tb.layout.moreMenu.fireEvent('beforerender', this.tb.layout.moreMenu);
                                this.tb.layout.moreMenu.doLayout();
                        }
                }, this);

        },

        emptyTextRegex: '<span[^>]+[^>]*>{0}<\/span>',
        emptyTextTpl: '<span style="color:#ccc;">{0}</span>',
        emptyText: '',

        registerSubmitKey: function() {
                var doc = this.getDoc();
                if (Ext.isGecko){
                        Ext.EventManager.on(doc, {
                                keydown: this.fireSubmit,
                                scope: this
                        });
                }

                if (Ext.isIE || Ext.isWebKit || Ext.isOpera) {
                        Ext.EventManager.on(doc, 'keydown', this.fireSubmit,
                                this);
                }
        },

        fireSubmit : function(e) {
                if (e.ctrlKey && Ext.EventObject.ENTER == e.getKey()) {
                        e.preventDefault();
                        this.fireEvent('ctrlenter',this);
                        return false;
                }
        },

        initEditor: function() {
                GO.form.HtmlEditor.superclass.initEditor.call(this);
                this.addEvents({attach: true});

                var doc = this.getEditorBody();
                doc.addEventListener('selectionchange', this.updateToolbar.createDelegate(this));
                doc.addEventListener('paste', this.onPaste.createDelegate(this));
                doc.addEventListener('drop', this.onDrop.createDelegate(this));
                doc.addEventListener('keyup', this.onKeyUp.createDelegate(this));

                // Add button update listener for mouse events
                doc.addEventListener('mouseup', this.updateToolbarButtons.createDelegate(this));

                // Add click handler to close menus when clicking outside
                doc.addEventListener('mousedown', function(e) {

                        var menuWasVisible = false;

                        if (this.formatButton && this.formatButton.menu && this.formatButton.menu.isVisible()) {
                                var menuEl = this.formatButton.menu.getEl();
                                var buttonEl = this.formatButton.getEl();
                                var clickedEl = e.target;

                                if (!menuEl.contains(clickedEl) && !buttonEl.contains(clickedEl)) {
                                        this.formatButton.menu.hide();
                                        menuWasVisible = true;
                                }
                        }

                        if (this.alignButton && this.alignButton.menu && this.alignButton.menu.isVisible()) {
                                var alignMenuEl = this.alignButton.menu.getEl();
                                var alignButtonEl = this.alignButton.getEl();

                                if (!alignMenuEl.contains(clickedEl) && !alignButtonEl.contains(clickedEl)) {
                                        this.alignButton.menu.hide();
                                        var menuWasVisible = false;
                                }
                        }

                        if (this.listButton && this.listButton.menu && this.listButton.menu.isVisible()) {
                                var listMenuEl = this.listButton.menu.getEl();
                                var listButtonEl = this.listButton.getEl();

                                if (!listMenuEl.contains(clickedEl) && !listButtonEl.contains(clickedEl)) {
                                        this.listButton.menu.hide();
                                        var menuWasVisible = false;
                                }
                        }

                        if (this.indentButton && this.indentButton.menu && this.indentButton.menu.isVisible()) {
                                var indentMenuEl = this.indentButton.menu.getEl();
                                var indentButtonEl = this.indentButton.getEl();

                                if (!indentMenuEl.contains(clickedEl) && !indentButtonEl.contains(clickedEl)) {
                                        this.indentButton.menu.hide();
                                        var menuWasVisible = false;
                                }
                        }

                        /*
        if (menuWasVisible) {
                setTimeout(function() {
                        editor.getWin().focus();
                        editor.focus();
                }.createDelegate(editor), 50);
        }
        */
                }.createDelegate(this));

                //Fix for Tooltips in the way of email #276
                doc.addEventListener("mouseenter", function() {
                        setTimeout(function() {
                                Ext.QuickTips.getQuickTip().hide();
                        }, 500);

                });

                go.ActivityWatcher.registerDocument(doc);

                //other browsers are already registered in parent function
                if(Ext.isGecko) {
                        Ext.EventManager.on(doc, 'keydown', this.fixKeys, this);
                }

                if(Ext.isChrome && navigator.appVersion.indexOf("Chrome/96.") > -1) {
                        console.warn("Disable spell check because it's slow on Chrome v96.")

                        doc.spellcheck = false;
                }
        },

        debounceTimeout : null,

        onKeyUp : function(e) {

                //Only run on enter, space or tab
                if(!this.debounceTimeout && e.keyCode != 13 && e.keyCode != 32 && e.keyCode != 9) {
                        return;
                }

                this.scheduleAutoLink();
        },

        scheduleAutoLink : function() {
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = setTimeout( () => {
                        clearTimeout(this.debounceTimeout);
                        this.debounceTimeout = undefined;

                        this.storeCursorPosition();
                        var h = this.getEditorBody().innerHTML;
                        var anchored = Autolinker.link(h, {
                                stripPrefix: false,
                                stripTrailingSlash: false,
                                className: "normal-link",
                                newWindow: true,
                                phone: false,
                                urls: {
                                        schemeMatches : true,
                                        wwwMatches    : false,
                                        tldMatches    : false
                                }
                        });

                        if(h != anchored) {
                                this.getEditorBody().innerHTML = anchored;
                                this.restoreCursorPosition();
                        }else
                        {
                                this.forgetCursorPosition();
                        }

                        console.warn("autolink");

                }, 500);
        },

        storeCursorPosition : function() {

                var win = this.getWin(),
                        doc = this.getDoc(),
                        sel, range, el, frag, node, lastNode, firstNode;

                sel = win.getSelection();
                if (sel.getRangeAt && sel.rangeCount) {
                        range = sel.getRangeAt(0);

                        el = doc.createElement("div");
                        el.innerHTML = "<div style='display:none' id='go-stored-cursor'></div>";
                        frag = doc.createDocumentFragment();
                        while ((node = el.firstChild)) {
                                lastNode = frag.appendChild(node);
                        }
                        firstNode = frag.firstChild;
                        range.insertNode(frag);

                        if (lastNode) {
                                range = range.cloneRange();
                                range.setStartAfter(lastNode);
                                range.setStartBefore(firstNode);
                                // range.collapse(true);
                                sel.removeAllRanges();
                                sel.addRange(range);
                        }
                }
        },

        forgetCursorPosition: function() {
                var cursor = this.getDoc().getElementById("go-stored-cursor");
                if(cursor) {
                        cursor.remove();
                }
        },

        restoreCursorPosition : function() {
                var doc = this.getDoc(), sel = this.getWin().getSelection();
                var cursor = doc.getElementById("go-stored-cursor");
                if(!cursor) {
                        return false;
                }
                var range = new Range();
                range.setStart(cursor, 0);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
                cursor.remove();
        },

        onDrop: function(e) {
                if(!e.dataTransfer.files) {
                        return;
                }
                //prevent browser from navigating to dropped file
                e.preventDefault();
                //make sure editor has focus
                this.focus();
                //this is needed if the editor has not been activated yet.
                this.updateToolbar();

                Array.from(e.dataTransfer.files).forEach(file => {
                        if (file.type.match(/^image\//)) {
                                const reader = new FileReader();
                                reader.onload = (event) => {
                                        const img = new Image();
                                        img.onload = () => {
                                                const domId = Ext.id();
                                                const width = img.width > 600 ? '600px' : img.width + 'px';
                                                const imgHtml = '<img id="' + domId + '" src="' + event.target.result + '" ' +
                                                        'style="width: ' + width + '; height: auto;" ' +
                                                        'alt="' + file.name + '" />';

                                                this.insertAtCursor(imgHtml);
                                                this.fireEvent('attach', this, {
                                                        dataUrl: event.target.result,
                                                        type: file.type,
                                                        width: img.width,
                                                        height: img.height
                                                }, file, null);
                                        };
                                        img.src = event.target.result;
                                };
                                reader.readAsDataURL(file);
                        }
        });
},

        onPaste: function(e) {
                var clipboardData = e.clipboardData;
                if (clipboardData.items) {
                        // Chrome/safari has clipBoardData.items
                        for (var i = 0; i < clipboardData.items.length; i++) {
                                var item = clipboardData.items[i];
                                // Some times clipboard data holds multiple versions. When copy pasting from excel you get html, plain text and an image.
                                // We prefer to use the html in that case so we exit if found.
                                if (item.type == 'text/html') {
                                        e.preventDefault();
                                        item.getAsString((s) => {
                                                const inlined = go.util.convertStyleToInline(s);
                                                this.execCmd("insertHTML", inlined);
                                        });
                                        return;
                                }
                                if (item.type.match(/^image\//)) {
                                        e.preventDefault();
                                        var reader = new FileReader();
                                        reader.onload = (event) => {
                                                const img = new Image();
                                                img.onload = () => {
                                                        const domId = Ext.id();
                                                        const width = img.width > 600 ? '600px' : img.width + 'px';
                                                        const imgHtml = '<img id="' + domId + '" src="' + event.target.result + '" ' +
                                                                'style="width: ' + width + '; height: auto;" ' +
                                                                'alt="pasted image" />';
                                                        this.insertAtCursor(imgHtml);
                                                        this.fireEvent('attach', this, {
                                                                dataUrl: event.target.result,
                                                                type: item.type,
                                                                width: img.width,
                                                                height: img.height
                                                        }, null);
                                                };
                                                img.src = event.target.result;
                                        };
                                reader.readAsDataURL(item.getAsFile());
                                return;
                        }
                }
        } else {
                // Firefox
                if (-1 === Array.prototype.indexOf.call(clipboardData.types, 'text/plain')) {
                        this.findImageInEditor();
                }
        }
},

        convertImageToInline: function(imgElement) {
                // Create a canvas and draw the image
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const loadImage = () => {
                        canvas.width = imgElement.naturalWidth;
                        canvas.height = imgElement.naturalHeight;
                        ctx.drawImage(imgElement, 0, 0);

                        try {
                                const dataURL = canvas.toDataURL('image/png');
                                imgElement.src = dataURL;
                                imgElement.style.maxWidth = '100%';
                                imgElement.style.height = 'auto';
                                imgElement.style.aspectRatio = `${canvas.width} / ${canvas.height}`;
                        } catch (err) {
                                console.error('Error converting image to inline:', err);
                        }
                };

                if (imgElement.complete) {
                        loadImage();
                } else {
                        imgElement.onload = loadImage;
                }
        },

        findImageInEditor: function() {
                const el = this.getDoc();
                const images = el.getElementsByTagName('img');
                const timespan = Math.floor(1000 * Math.random());

                // Mark existing images
                for (let i = 0; i < images.length; i++) {
                        images[i]["_paste_marked_" + timespan] = true;
                }

                // Check for new images after a short delay
                setTimeout(() => {
                        const newImages = el.getElementsByTagName('img');
                        for (let i = 0; i < newImages.length; i++) {
                                const img = newImages[i];
                                if (!img["_paste_marked_" + timespan]) {
                                        // Check if the image is a blob URL or regular URL
                                        if (img.src.startsWith('blob:') || img.src.startsWith('http')) {
                                                this.handleImage(img.src);
                                                img.remove(); // Remove the original image as it will be replaced
                                        }
                                }
                        }
                }, 1);
        },

        handleImage: function(src) {
                const loader = new Image();
                const me = this;

                loader.onload = function() {
                        const domId = Ext.id();
                        const width = loader.width > 600 ? '600px' : loader.width + 'px';
                        const imgHtml = '<img id="' + domId + '" src="' + src + '" ' +
                                'style="width: ' + width + '; height: auto;" ' +
                                'alt="pasted image" />';

                        // If it's a blob URL, we need to convert it to base64
                        if (src.startsWith('blob:')) {
                                fetch(src)
                                        .then(response => response.blob())
                                        .then(blob => {
                                                const reader = new FileReader();
                                                reader.onload = function(e) {
                                                        const finalImgHtml = '<img id="' + domId + '" src="' + e.target.result + '" ' +
                                                                'style="width: ' + width + '; height: auto;" ' +
                                                                'alt="pasted image" />';
                                                        me.insertAtCursor(finalImgHtml);
                                                        me.fireEvent('attach', me, {
                                                                dataUrl: e.target.result,
                                                                width: loader.width,
                                                                height: loader.height
                                                        }, null);
                                                };
                                                reader.readAsDataURL(blob);
                                        });
                        } else {
                                me.insertAtCursor(imgHtml);
                                me.fireEvent('attach', me, {
                                        dataUrl: src,
                                        width: loader.width,
                                        height: loader.height
                                }, null);
                        }
                };

                loader.src = src;
        },
        insertImage : function(src) {
                var domId = Ext.id(), img = '<img style="max-width: 100%" id="' + domId + '" src="' + src + '" alt="pasted image" />';
                this.insertAtCursor(img);

                return  this.getDoc().getElementById(domId);
        },

        /**
         * Executes a Midas editor command directly on the editor document.
         * For visual commands, you should use {@link #relayCmd} instead.
         * <b>This should only be called after the editor is initialized.</b>
         * @param {String} cmd The Midas command
         * @param {String/Boolean} value (optional) The value to pass to the command (defaults to null)
         */
        execCmd: function (cmd, value) {
                var doc = this.getDoc();

                if (cmd === 'createlink' && Ext.isGecko) {
                        // If firefox is used, then manually add the "a" tag to the text
                        var t = this.getSelectedText();
                        if (t.length < 1) {
                                value = '<a href="' + value + '">' + value + "</a>";
                                this.insertAtCursor(value);
                        }
                }

                doc.execCommand(cmd, false, value === undefined ? null : value);

                this.syncValue();
        },

        getSelectedText: function () {

                var frame = this.iframe;
                var frameWindow = frame.contentWindow;
                var frameDocument = frameWindow.document;

                if (frameDocument.getSelection)
                        return frameDocument.getSelection().toString();
                else if (frameDocument.selection)
                        return frameDocument.selection.createRange().text;
        },

        setValue: function (value) {

                if (this.win && Ext.isChrome && this.activated) {

                        //set cursor position on top
                        var range = this.win.document.createRange();
                        range.setStart(this.win.document.body, 0);
                        range.setEnd(this.win.document.body, 0);

                        var sel = this.win.document.getSelection();

                        if(sel) {
                                sel.removeAllRanges();
                                sel.addRange(range);
                        }
                }
                GO.form.HtmlEditor.superclass.setValue.call(this, value);
        },

        /**
         * Automatically grow field with content
         */
        grow: false,

        /**
         * Minimum height for field
         */
        growMinHeight: dp(46),

        /**
         * Maximum height for field
         */
        growMaxHeight: dp(480),

        doGrow : function() {
                var body = this.getEditorBody();

                body.style.height = 'auto';
                body.style.display = 'inline-block';

                body.style.minHeight =  this.growMinHeight + "px";
                body.style.padding = window.getComputedStyle(this.el.dom).padding;
                body.style.boxSizing = "border-box";
                body.style.width = "100%";
                body.style.lineHeight = dp(20) + "px";

                var h =  Math.max(this.growMinHeight, body.offsetHeight); // 400  max height

                if(h > dp(48)) {
                        this.tb.show();
                        //workaround for combo
                        if(this.tb.items.itemAt(0).wrap) {
                                this.tb.items.itemAt(0).wrap.dom.style.width = "100px";
                        }
                        this.tb.doLayout();
                } else {
                        this.tb.hide();
                }

                h +=  this.tb.el.getHeight();

                if(this.growMaxHeight && h > this.growMaxHeight) {
                        h = this.growMaxHeight;
                }

                this.setHeight(h);

        },

        onFirstFocus: function () {

                this.initPunctuationCorrection();

                this.activated = true;
                this.disableItems(this.readOnly);
                if (Ext.isGecko) {
                        try {
                                this.execCmd('useCSS', true);
                                this.execCmd('styleWithCSS', false);
                                this.execCmd('insertBrOnReturn', false);
                                this.execCmd('enableObjectResizing', true);
                        } catch (e) {
                                console.warn(e);
                        }
                }
                this.fireEvent('activate', this);
        },

        cleanupSeparators: function(toolbar) {
                var items = toolbar.items.items;

                if(items[0] instanceof Ext.Toolbar.Separator) {
                        toolbar.remove(items[0]);
                }

                for(var i = items.length - 1; i > 0; i--) {
                        if(items[i] instanceof Ext.Toolbar.Separator && items[i-1] instanceof Ext.Toolbar.Separator) {
                                toolbar.remove(items[i]);
                        }
                }
                toolbar.doLayout();
        },

        createToolbar: Ext.form.HtmlEditor.prototype.createToolbar.createSequence(function(editor) {
                var tb = this.tb;

                tb.enableOverflow = true;

                tb.on('overflowchange', function(toolbar, hasOverflow) {
                        if (hasOverflow && toolbar.layout && toolbar.layout.more) {
                                toolbar.layout.moreMenu.on('beforerender', function(menu) {
                                        toolbar.enableOverflow = false;
                                        toolbar.enableOverflow = true;
                                        toolbar.doLayout();
                                        toolbar.items.items.forEach(function(item) {
                                                if (item.getXType() === 'tbseparator' || item.getXType() === 'combo') {
                                                        return;
                                                }
                                                var toolbarItem = tb.items.find(function(tbItem) {
                                                        return tbItem.id === item.id ||
                                                                (tbItem.itemId && tbItem.itemId === item.itemId) ||
                                                                (tbItem.iconCls && tbItem.iconCls === item.iconCls);
                                                });

                                                if (toolbarItem) {
                                                        toolbarItem.setText('');
                                                        toolbarItem.setTooltip('');
                                                        toolbarItem.text='';
                                                        toolbarItem.tooltip='';
                                                }
                                        });


                                        toolbar.layout.hiddenItems.forEach(function(item) {

                                                if (item.getXType() === 'tbseparator' || ( GO.util.isMobileOrTablet() && item.itemId == 'table_chart') ) {
                                                        if (item.el) {
                                                                item.el.remove();
                                                        }

                                                        item.destroy();
                                                        return;
                                                }

                                                var toolbarItem = tb.items.find(function(tbItem) {
                                                        return tbItem.id === item.id ||
                                                                (tbItem.itemId && tbItem.itemId === item.itemId) ||
                                                                (tbItem.iconCls && tbItem.iconCls === item.iconCls);
                                                });

                                                if (toolbarItem) {

                                                        if (toolbarItem.itemId === 'forecolor') {
                                                                toolbarItem.setText('Font Color');
                                                                toolbarItem.setTooltip('Font Color');
                                                        }

                                                        if (toolbarItem.itemId === 'backcolor') {
                                                                toolbarItem.setText('BG Color');
                                                                toolbarItem.setTooltip('BG Color');
                                                        }

                                                        if (toolbarItem.itemId === 'align') {
                                                                toolbarItem.setText('Align');
                                                                toolbarItem.setTooltip('Align');
                                                        }

                                                        if (toolbarItem.itemId === 'list') {
                                                                toolbarItem.setText('List');
                                                                toolbarItem.setTooltip('List');
                                                        }
                                                }
                                        });

                                });
                        }
                });

                // Find all buttons we want to combine
                var boldButton = tb.items.find(function(item) { return item.itemId === 'bold'; });
                var italicButton = tb.items.find(function(item) { return item.itemId === 'italic'; });
                var underlineButton = tb.items.find(function(item) { return item.itemId === 'underline'; });
                var justifyleftButton = tb.items.find(function(item) { return item.itemId === 'justifyleft'; });
                var justifycenterButton = tb.items.find(function(item) { return item.itemId === 'justifycenter'; });
                var justifyrightButton = tb.items.find(function(item) { return item.itemId === 'justifyright'; });
                var numberedListButton = tb.items.find(function(item) { return item.itemId === 'insertorderedlist'; });
                var bulletListButton = tb.items.find(function(item) { return item.itemId === 'insertunorderedlist'; });
                var forecolorButton = tb.items.find(function(item) { return item.itemId === 'forecolor'; });
                var backcolorButton = tb.items.find(function(item) { return item.itemId === 'backcolor'; });
                var hyperlinkButton = tb.items.find(function(item) { return item.itemId === 'createlink'; });
                var increasefontsizeButton = tb.items.find(function(item) { return item.itemId === 'increasefontsize'; });
                var decreasefontsizeButton = tb.items.find(function(item) { return item.itemId === 'decreasefontsize'; });
                var sourceedit = tb.items.find(function(item) { return item.itemId === 'sourceedit'; });

                var imageButton = null;

                tb.items.each(function(item) {

                        if(item.itemId === 'htmlEditorImage') {
                                imageButton = item;
                                return false;
                        }
                });

                // At the start of createToolbar, before the tb.on('add'):
                var handledButtons = {};
                var pendingButtons = {
                        image: null,
                        table: null,
                        emoji: null
                };
                var toolbarInitialized = false;
                var isRepositioning = false;

                // Updated add handler
                tb.on('add', function(toolbar, component) {

                        if (handledButtons[component.id] || isRepositioning || toolbarInitialized) {
                                return;
                        }


                        // Handle indent/outdent buttons
                        if (component.iconCls === 'x-edit-indent' || component.iconCls === 'x-edit-outdent') {
                                tb.remove(component);
                                this.cleanupSeparators(tb);
                                return;
                        }

                        if (GO.util.isMobileOrTablet()) {
                                if (component.iconCls === 'x-edit-hr') {
                                        tb.remove(component);
                                        this.cleanupSeparators(tb);
                                        return;
                                }
                        }

                        // Store the reference to the component
                        if (component.itemId === 'htmlEditorImage') {
                                handledButtons[component.id] = true;
                                pendingButtons.image = component;
                        } else if (component.itemId === 'table_chart') {
                                handledButtons[component.id] = true;
                                pendingButtons.table = component;
                        } else if (component.tooltip === 'Emoji') {
                                handledButtons[component.id] = true;
                                pendingButtons.emoji = component;
                        }

                        var formatButton = tb.items.find(function(item) {
                                return item.itemId === 'format';
                        });

                        if (formatButton && !isRepositioning) {
                                var insertIndex = tb.items.indexOf(formatButton) + 1;

                                // Set flag to prevent recursion
                                isRepositioning = true;

                                // Insert buttons in order
                                if (pendingButtons.image) {
                                        if (tb.items.indexOf(pendingButtons.image) !== -1) {
                                                tb.remove(pendingButtons.image, false);
                                        }
                                        tb.insert(insertIndex++, pendingButtons.image);
                                }

                                if (pendingButtons.table) {
                                        if (tb.items.indexOf(pendingButtons.table) !== -1) {
                                                tb.remove(pendingButtons.table, false);
                                        }
                                        tb.insert(insertIndex++, pendingButtons.table);
                                }

                                if (pendingButtons.emoji) {
                                        if (tb.items.indexOf(pendingButtons.emoji) !== -1) {
                                                tb.remove(pendingButtons.emoji, false);
                                        }
                                        tb.insert(insertIndex++, pendingButtons.emoji);
                                }

                                // Add a separator after our buttons
                                //tb.insert(insertIndex, new Ext.Toolbar.Separator());

                                this.cleanupSeparators(tb);
                                tb.enableOverflow = true;
                                tb.doLayout();

                                // Reset repositioning flag
                                isRepositioning = false;
                                if (pendingButtons.image && pendingButtons.table && pendingButtons.emoji) {
                                        toolbarInitialized = true;
                                }
                        }
                }.createDelegate(this));

                // Get reference item for positioning
                var referenceButton = tb.items.itemAt(0);
                var insertIndex = referenceButton ? tb.items.indexOf(referenceButton) + 1 : 0;

                // Remove existing buttons
                if (italicButton) tb.remove(italicButton);
                if (underlineButton) tb.remove(underlineButton);
                if (boldButton) tb.remove(boldButton);
                if (justifycenterButton) tb.remove(justifycenterButton);
                if (justifyrightButton) tb.remove(justifyrightButton);
                if (justifyleftButton) tb.remove(justifyleftButton);
                if (numberedListButton) tb.remove(numberedListButton);
                if (bulletListButton) tb.remove(bulletListButton);
                if (increasefontsizeButton) tb.remove(increasefontsizeButton);
                if (decreasefontsizeButton) tb.remove(decreasefontsizeButton);
                if (sourceedit) tb.remove(sourceedit);

                if (forecolorButton) {
                        if (GO.util.isMobileOrTablet()) {
                                tb.remove(forecolorButton);
                                forecolorButton=null;
                        } else {
                                forecolorButton.menu.on('select', function(pallette, color) {
                                        var buttonEl = forecolorButton.btnEl;
                                        if (buttonEl) {
                                                buttonEl.setStyle({
                                                        'border-bottom': '5px solid #' + color,
                                                        'padding-bottom': '1px'
                                                });
                                        }
                                });
                        }
                }

                if (backcolorButton) {
                        if (GO.util.isMobileOrTablet()) {
                                tb.remove(backcolorButton);
                        }
                }

                if (hyperlinkButton) {
                        if (GO.util.isMobileOrTablet()) {
                                tb.remove(hyperlinkButton);
                        }
                }

                // Create the formatting menu
                var formatMenu = new Ext.menu.Menu({
                        minWidth: 120, // Ensure menu is wide enough for text
                        style: 'overflow: hidden', // Prevent scrollbar
                        items: [{
                                xtype: 'menucheckitem',
                                itemId: 'bold',
                                text: 'Bold',
                                iconCls: 'x-edit-bold',
                                style: 'padding: 3px 24px 3px 24px;',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        var formatType = this.itemId;

                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }

                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();

                                                // Execute the formatting command
                                                editor.relayCmd(formatType);

                                                // Immediately check the new state and update UI
                                                setTimeout(function() {
                                                        var newState = doc.queryCommandState(formatType);

                                                        // Update the menu item's checked state
                                                        editor.formatButton.menu.items.each(function(item) {
                                                                if (item.itemId === formatType) {
                                                                        item.setChecked(newState, true);
                                                                }
                                                        });

                                                        // Update the button icon if needed
                                                        if (newState) {
                                                                editor.formatButton.setIconClass('x-edit-' + formatType);
                                                                editor.lastUsedFormat = formatType;
                                                        } else {
                                                                // If this format was turned off, find another active format or use the last one
                                                                var activeFormat = null;
                                                                ['bold', 'italic', 'underline'].forEach(function(fmt) {
                                                                        if (doc.queryCommandState(fmt)) {
                                                                                activeFormat = fmt;
                                                                        }
                                                                });
                                                                editor.formatButton.setIconClass('x-edit-' + (activeFormat || editor.lastUsedFormat));
                                                        }

                                                        // Force toolbar update
                                                        editor.updateToolbarButtons();
                                                }, 10);

                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }, {
                                xtype: 'menucheckitem',
                                itemId: 'italic',
                                text: 'Italic',
                                iconCls: 'x-edit-italic',
                                style: 'padding: 3px 24px 3px 24px;',
                                handler: function() {
                                        // Same handler as bold, but with 'italic' as formatType
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        var formatType = this.itemId;

                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }

                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();

                                                editor.relayCmd(formatType);

                                                setTimeout(function() {
                                                        var newState = doc.queryCommandState(formatType);

                                                        editor.formatButton.menu.items.each(function(item) {
                                                                if (item.itemId === formatType) {
                                                                        item.setChecked(newState, true);
                                                                }
                                                        });

                                                        if (newState) {
                                                                editor.formatButton.setIconClass('x-edit-' + formatType);
                                                                editor.lastUsedFormat = formatType;
                                                        } else {
                                                                var activeFormat = null;
                                                                ['bold', 'italic', 'underline'].forEach(function(fmt) {
                                                                        if (doc.queryCommandState(fmt)) {
                                                                                activeFormat = fmt;
                                                                        }
                                                                });
                                                                editor.formatButton.setIconClass('x-edit-' + (activeFormat || editor.lastUsedFormat));
                                                        }

                                                        editor.updateToolbarButtons();
                                                }, 10);

                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }, {
                                xtype: 'menucheckitem',
                                itemId: 'underline',
                                text: 'Underline',
                                iconCls: 'x-edit-underline',
                                style: 'padding: 3px 24px 3px 24px;',
                                handler: function() {
                                        // Same handler as bold, but with 'underline' as formatType
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        var formatType = this.itemId;

                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }

                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();

                                                editor.relayCmd(formatType);

                                                setTimeout(function() {
                                                        var newState = doc.queryCommandState(formatType);

                                                        editor.formatButton.menu.items.each(function(item) {
                                                                if (item.itemId === formatType) {
                                                                        item.setChecked(newState, true);
                                                                }
                                                        });

                                                        if (newState) {
                                                                editor.formatButton.setIconClass('x-edit-' + formatType);
                                                                editor.lastUsedFormat = formatType;
                                                        } else {
                                                                var activeFormat = null;
                                                                ['bold', 'italic', 'underline'].forEach(function(fmt) {
                                                                        if (doc.queryCommandState(fmt)) {
                                                                                activeFormat = fmt;
                                                                        }
                                                                });
                                                                editor.formatButton.setIconClass('x-edit-' + (activeFormat || editor.lastUsedFormat));
                                                        }

                                                        editor.updateToolbarButtons();
                                                }, 10);

                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }]
                });

                // Create the align menu
                var alignMenu = new Ext.menu.Menu({
                        minWidth: 120, // Ensure menu is wide enough for text
                        style: 'overflow: hidden',
                        items: [{
                                xtype: 'menucheckitem',
                                itemId: 'justifyleft',
                                text: 'Align Left',
                                iconCls: 'x-edit-justifyleft',
                                style: 'padding: 3px 24px 3px 24px;',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }
                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();
                                                editor.relayCmd('justifyleft');
                                                editor.alignButton.setIconClass('x-edit-justifyleft');
                                                editor.lastUsedAlign = 'justifyleft';
                                                alignMenu.items.each(function(item) {
                                                        item.setChecked(item.itemId === 'justifyleft', true);
                                                });
                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }, {
                                xtype: 'menucheckitem',
                                itemId: 'justifycenter',
                                text: 'Center',
                                iconCls: 'x-edit-justifycenter',
                                style: 'padding: 3px 24px 3px 24px;',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }
                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();
                                                editor.relayCmd('justifycenter');
                                                editor.alignButton.setIconClass('x-edit-justifycenter');
                                                editor.lastUsedAlign = 'justifycenter';
                                                alignMenu.items.each(function(item) {
                                                        item.setChecked(item.itemId === 'justifycenter', true);
                                                });
                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }, {
                                xtype: 'menucheckitem',
                                itemId: 'justifyright',
                                text: 'Align Right',
                                iconCls: 'x-edit-justifyright',
                                style: 'padding: 3px 24px 3px 24px;',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }
                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();
                                                editor.relayCmd('justifyright');
                                                editor.alignButton.setIconClass('x-edit-justifyright');
                                                editor.lastUsedAlign = 'justifyright';
                                                alignMenu.items.each(function(item) {
                                                        item.setChecked(item.itemId === 'justifyright', true);
                                                });
                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }]
                });

                // Create the list menu
                var listMenu = new Ext.menu.Menu({
                        items: [{
                                xtype: 'menucheckitem',
                                itemId: 'insertorderedlist',
                                text: 'Numbered List',
                                iconCls: 'x-edit-insertorderedlist',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }
                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();
                                                editor.relayCmd('insertorderedlist');
                                                editor.listButton.setIconClass('x-edit-insertorderedlist');
                                                editor.lastUsedList = 'insertorderedlist';
                                                listMenu.items.each(function(item) {
                                                        item.setChecked(item.itemId === 'insertorderedlist', true);
                                                });
                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }, {
                                xtype: 'menucheckitem',
                                itemId: 'insertunorderedlist',
                                text: 'Bullet List',
                                iconCls: 'x-edit-insertunorderedlist',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }
                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();
                                                editor.relayCmd('insertunorderedlist');
                                                editor.listButton.setIconClass('x-edit-insertunorderedlist');
                                                editor.lastUsedList = 'insertunorderedlist';
                                                listMenu.items.each(function(item) {
                                                        item.setChecked(item.itemId === 'insertunorderedlist', true);
                                                });
                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }]
                });

                // Create the indent menu
                var indentMenu = new Ext.menu.Menu({
                        items: [{
                                xtype: 'menucheckitem',
                                itemId: 'indent',
                                text: 'Increase Indent',
                                iconCls: 'x-edit-indent',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }
                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();
                                                editor.relayCmd('indent');
                                                editor.indentButton.setIconClass('x-edit-indent');
                                                editor.lastUsedIndent = 'indent';
                                                indentMenu.items.each(function(item) {
                                                        item.setChecked(item.itemId === 'indent', true);
                                                });
                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }, {
                                xtype: 'menucheckitem',
                                itemId: 'outdent',
                                text: 'Decrease Indent',
                                iconCls: 'x-edit-outdent',
                                handler: function() {
                                        var win = editor.getWin();
                                        var doc = win.document;
                                        setTimeout(function() {
                                                var range = doc.createRange();
                                                var sel = win.getSelection();
                                                try {
                                                        if (sel.rangeCount > 0) {
                                                                range = sel.getRangeAt(0);
                                                        } else {
                                                                range.selectNodeContents(doc.body);
                                                                range.collapse(false);
                                                        }
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                } catch(e) {
                                                        console.warn('Error restoring cursor:', e);
                                                }
                                                win.focus();
                                                doc.body.focus();
                                                editor.focus();
                                                editor.relayCmd('outdent');
                                                editor.indentButton.setIconClass('x-edit-outdent');
                                                editor.lastUsedIndent = 'outdent';
                                                indentMenu.items.each(function(item) {
                                                        item.setChecked(item.itemId === 'outdent', true);
                                                });
                                        }.createDelegate(editor), 50);
                                },
                                checked: false
                        }]
                });

                // Create button configurations
                var formatButton = {
                        itemId: 'format',
                        text: '',
                        iconCls: 'x-edit-bold',
                        menu: formatMenu,
                        tooltip: Ext.QuickTips && Ext.QuickTips.isEnabled() ? {
                                title: 'Text Formatting',
                                text: 'Apply bold, italic, or underline formatting to selected text'
                        } : undefined
                };

                var alignButton = {
                        itemId: 'align',
                        text: '',
                        iconCls: 'x-edit-justifyleft',
                        menu: alignMenu,
                        tooltip: Ext.QuickTips && Ext.QuickTips.isEnabled() ? {
                                title: 'Text Alignment',
                                text: 'Change text alignment'
                        } : undefined
                };

                var listButton = {
                        itemId: 'list',
                        text: '',
                        iconCls: 'x-edit-insertorderedlist',
                        menu: listMenu,
                        tooltip: Ext.QuickTips && Ext.QuickTips.isEnabled() ? {
                                title: 'Lists',
                                text: 'Insert numbered or bullet list'
                        } : undefined
                };

                var indentButton = {
                        itemId: 'indentation',
                        text: '',
                        iconCls: 'x-edit-indent',
                        menu: indentMenu,
                        tooltip: Ext.QuickTips && Ext.QuickTips.isEnabled() ? {
                                title: 'Indentation',
                                text: 'Increase or decrease indentation'
                        } : undefined
                };

                // Add the new buttons in desired order
                this.formatButton = tb.insert(insertIndex, formatButton);
                this.lastUsedFormat = 'bold';
                insertIndex++;

                if (forecolorButton) {
                        tb.remove(forecolorButton, false);
                        tb.insert(insertIndex, forecolorButton);
                        var forecolorIndex = tb.items.indexOf(forecolorButton);
                        var itemBeforeForecolor = tb.items.itemAt(forecolorIndex - 1);
                        if (itemBeforeForecolor instanceof Ext.Toolbar.Separator) {
                                tb.remove(itemBeforeForecolor);
                        }
                        insertIndex++;
                }

                if (!GO.util.isMobileOrTablet()) {
                        this.alignButton = tb.insert(insertIndex, alignButton);
                        this.lastUsedAlign = 'justifyleft';
                        insertIndex++;

                        this.listButton = tb.insert(insertIndex, listButton);
                        this.lastUsedList = 'insertorderedlist';
                        insertIndex++;

                        this.indentButton = tb.insert(insertIndex, indentButton);
                        this.lastUsedIndent = 'indent';
                }

                this.cleanupSeparators(tb);
                tb.enableOverflow = true;
        }),

        updateToolbarButtons: function() {
                // Update format, align, list, and indent buttons
                this.updateFormatButtonIcon();
                this.updateAlignButtonIcon();
                this.updateListButtonIcon();
                this.updateIndentButtonIcon();

                // Update the forecolor button's underline to match current text color
                var forecolorButton = this.tb.items.find(function(item) {
                        return item.itemId === 'forecolor';
                });

                if (forecolorButton && forecolorButton.btnEl) {
                        var currentColor = this.getCurrentTextColor();
                        // Set the underline color of the forecolor button
                        forecolorButton.btnEl.setStyle({
                                'border-bottom': '5px solid ' + (currentColor || '#000000'),
                                'padding-bottom': '1px'
                        });
                }
        },

        getCurrentTextColor: function() {
                if (!this.getDoc()) return '#000000';

                var selection = this.getWin().getSelection();
                if (!selection || !selection.rangeCount) return '#000000';

                var range = selection.getRangeAt(0);
                var element = range.commonAncestorContainer;

                // If text node, get parent element
                if (element.nodeType === Node.TEXT_NODE) {
                        element = element.parentElement;
                }

                // Get computed color style
                var color = this.getWin().getComputedStyle(element).color || '#000000';
                return color;
        },

        updateFormatButtonIcon: function() {
                if (!this.formatButton) {
                        return;
                }

                var doc = this.getDoc();
                try {
                        var menu = this.formatButton.menu;
                        var activeFormat = null;
                        var hasActiveFormat = false;

                        // Update each menu item's state immediately
                        menu.items.each(function(item) {
                                var state = doc.queryCommandState(item.itemId);
                                item.setChecked(state, true);

                                if (state) {
                                        activeFormat = item.itemId;
                                        hasActiveFormat = true;
                                }
                        });

                        // Set the button icon to either the active format or last used
                        if (!hasActiveFormat) {
                                activeFormat = this.lastUsedFormat || 'bold';
                        }

                        this.formatButton.setIconClass('x-edit-' + activeFormat);

                        // Ensure the active format is at the top of the menu
                        var activeItem = menu.items.find(function(item) {
                                return item.itemId === activeFormat;
                        });
                        if (activeItem) {
                                menu.remove(activeItem, false);
                                menu.insert(0, activeItem);
                        }

                } catch(e) {
                        console.warn('Error updating format button:', e);
                }
        },
        updateAlignButtonIcon: function() {
                if (!this.alignButton) {
                        return;
                }

                var doc = this.getDoc();
                try {
                        var menu = this.alignButton.menu;
                        var activeAlign = null;

                        // Determine which alignment is active
                        if (doc.queryCommandState('justifyleft')) {
                                activeAlign = 'justifyleft';
                        } else if (doc.queryCommandState('justifycenter')) {
                                activeAlign = 'justifycenter';
                        } else if (doc.queryCommandState('justifyright')) {
                                activeAlign = 'justifyright';
                        } else {
                                activeAlign = this.lastUsedAlign || 'justifyleft';
                        }

                        this.alignButton.setIconClass('x-edit-' + activeAlign);

                        // Find the active item and move it to the top
                        var activeItem = menu.items.find(function(item) { return item.itemId === activeAlign; });
                        if (activeItem) {
                                menu.remove(activeItem, false);
                                menu.insert(0, activeItem);
                        }

                        // Update checked states
                        menu.items.each(function(item) {
                                var state = doc.queryCommandState(item.itemId);
                                item.setChecked(state);
                        });
                } catch(e) {
                        console.warn('Error updating align button:', e, e.stack);
                }
        },

        updateListButtonIcon: function() {
                if (!this.listButton) {
                        return;
                }

                var doc = this.getDoc();
                try {
                        var menu = this.listButton.menu;
                        var activeList = null;

                        // Determine which list type is active
                        if (doc.queryCommandState('insertorderedlist')) {
                                activeList = 'insertorderedlist';
                        } else if (doc.queryCommandState('insertunorderedlist')) {
                                activeList = 'insertunorderedlist';
                        } else {
                                activeList = this.lastUsedList || 'insertorderedlist';
                        }

                        this.listButton.setIconClass('x-edit-' + activeList);

                        // Find the active item and move it to the top
                        var activeItem = menu.items.find(function(item) { return item.itemId === activeList; });
                        if (activeItem) {
                                menu.remove(activeItem, false);
                                menu.insert(0, activeItem);
                        }

                        // Update checked states
                        menu.items.each(function(item) {
                                item.setChecked(doc.queryCommandState(item.itemId));
                        });
                } catch(e) {
                        console.warn('Error updating list button:', e);
                }
        },

        updateIndentButtonIcon: function() {
                if (!this.indentButton) {
                        return;
                }

                var doc = this.getDoc();
                try {
                        var menu = this.indentButton.menu;
                        var activeIndent = this.lastUsedIndent || 'indent';

                        this.indentButton.setIconClass('x-edit-' + activeIndent);

                        // Move active item to top
                        var activeItem = menu.items.find(function(item) { return item.itemId === activeIndent; });
                        if (activeItem) {
                                menu.remove(activeItem, false);
                                menu.insert(0, activeItem);
                        }
                } catch(e) {
                        console.warn('Error updating indent button:', e);
                }
        },


        updateToolbar: function() {
                if(this.readOnly) {
                        return;
                }

                // Keep the activation logic
                if(!this.activated) {
                        this.onFirstFocus();
                        return;
                }

                // Don't update buttons if we're in the middle of an action
                if(this.isUpdating) {
                        return;
                }

                try {
                        this.isUpdating = true;

                        // Update our custom toolbar buttons
                        if (this.formatButton) {
                                this.updateFormatButtonIcon();
                        }
                        if (this.alignButton) {
                                this.updateAlignButtonIcon();
                        }
                        if (this.listButton) {
                                this.updateListButtonIcon();
                        }
                        if (this.indentButton) {
                                this.updateIndentButtonIcon();
                        }

                        // Hide any open menus
                        Ext.menu.MenuMgr.hideAll();

                        // Sync value
                        this.syncValue();
                } finally {
                        this.isUpdating = false;
                }
        },
        initPunctuationCorrection: function () {
                if (GO.settings.auto_punctuation != 1)
                        return;

                var me = this;
                var doc = me.getDoc();

                Ext.EventManager.on(doc, 'keydown', me.correctPunctuation, me);
        },

        lastKey: false,
        capNext: true,

        correctPunctuation: function (ev) {
                ev = ev.browserEvent;
                if (!this.capNext) {
                        if(ev.key === 'Enter' || (ev.key === ' ' && ['.', '!', '?'].indexOf(this.lastKey) !== -1)) {
                                this.capNext = true;
                        }
                } else {
                        if (ev.key.match(/^[a-z]$/)) {
                                ev.preventDefault();
                                this.insertAtCursor(ev.key.toUpperCase());
                                this.capNext = false;
                        } else if (ev.key != ' ' && ev.key != 'Enter') {
                                this.capNext = false;
                        }
                }
                this.lastKey = ev.key;
        },

        // getFontStyle :  function() {
        //      var style = getComputedStyle(this.getEl().dom);
        //      return "font-size: " + style['font-size'] + ';font-family: '+style['font-family'];
        // },
        //
        // getEditorFrameStyle : function() {
        //      return 'body,p,td,div,span{' + this.getFontStyle() + '};body{border: 0; margin: 0; padding: {0}px; height: {1}px; cursor: text}body p{margin:0px;}';
        // },
        //
        // getDocMarkup: function () {
        //      console.warn( this.getEditorFrameStyle());
        //      var h = Ext.fly(this.iframe).getHeight() - this.iframePad * 2;
        //      return String.format('<html><head><meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" /><style type="text/css">' + this.getEditorFrameStyle() + '</style></head><body></body></html>', this.iframePad, h);
        // },
        fixKeys: function (e) { // load time branching for fastest keydown performance

                var k = e.getKey(), doc;
                if(
                        Ext.isWebKit && e.shiftKey && k == e.ENTER && (doc = this.getDoc()) &&
                        (doc.queryCommandState('insertorderedlist') || doc.queryCommandState('insertunorderedlist'))
                ) {
                        e.stopEvent();
                        this.execCmd('InsertHtml',Ext.isGecko ? '<br />' : '<br /><br />');
                        this.deferFocus();
                } else if (k == e.TAB) {
                        e.preventDefault();
                        doc = this.getDoc();
                        if (doc.queryCommandState('insertorderedlist') || doc.queryCommandState('insertunorderedlist')) {
                                this.execCmd(e.shiftKey ? 'outdent' : 'indent');
                        }else {
                                this.execCmd('InsertText', '\t');
                        }
                        this.deferFocus();
                }


        },

        //Overwritten to fix font size bug in chrome
        adjustFont: function (btn) {
                var adjust = btn.getItemId() == 'increasefontsize' ? 1 : -1,
                        doc = this.getDoc(),
                        v = parseInt(doc.queryCommandValue('FontSize') || 2, 10);
                if (Ext.isAir) {


                        if (v <= 10) {
                                v = 1 + adjust;
                        } else if (v <= 13) {
                                v = 2 + adjust;
                        } else if (v <= 16) {
                                v = 3 + adjust;
                        } else if (v <= 18) {
                                v = 4 + adjust;
                        } else if (v <= 24) {
                                v = 5 + adjust;
                        } else {
                                v = 6 + adjust;
                        }
                        v = v.constrain(1, 6);
                } else {
                        v = Math.max(1, v + adjust);
                }
                this.execCmd('FontSize', v);
        },

        createLink: function () {
                var url = prompt(this.createLinkText, this.defaultLinkValue);
                if (url && url != 'http:/' + '/') {
                        if (Ext.isSafari) {
                                this.execCmd("createlink", url);
                                this.updateToolbar();
                        } else
                        {
                                this.relayCmd("createlink", url);
                        }
                }
        },

        setDesignMode : function(readOnly){
                this.getEditorBody().contentEditable = readOnly;
        },

        onResize : function(w, h){
                Ext.form.HtmlEditor.superclass.onResize.apply(this, arguments);
                if(this.el && this.iframe){
                        if(Ext.isNumber(w)){
                                var aw = w - this.wrap.getFrameWidth('lr');
                                this.el.setWidth(aw);
                                this.tb.setWidth(aw);
                                this.iframe.style.width = Math.max(aw, 0) + 'px';
                        }
                        if(Ext.isNumber(h)){
                                var ah = h - this.wrap.getFrameWidth('tb') - this.tb.el.getHeight();
                                this.el.setHeight(ah);
                                this.iframe.style.height = Math.max(ah, 0) + 'px';
                                var bd = this.getEditorBody();
                                if(bd){
                                        bd.style.height = Math.max((ah - (this.iframePad*2)), 0) + 'px';
                                }
                        }
                }
        }

});

                                        Ext.reg('xhtmleditor', GO.form.HtmlEditor);
