/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: HtmlEditorImageInsert.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.plugins.HtmlEditorImageInsert = function(config) {
        config = config || {};
        Ext.apply(this, config);

        this.init = function(htmlEditor) {
                this.editor = htmlEditor;
                this.editor.on('render', this.onRender, this);
                this.editor.on('initialize', this.addImageHandlers, this);
                let activeWrapper = null;
                let isResizing = false;
                let lastRange = null; // Store the last cursor position
        };

        this.filesFilter = 'jpg,png,gif,jpeg,bmp';
        this.addEvents({
                'insert': true
        });
};

Ext.extend(GO.plugins.HtmlEditorImageInsert, Ext.util.Observable, {
        root_folder_id: 0,
        folder_id: 0,
        isTempFile: true,

        onRender: function() {
                var element = {};
                element.itemId = 'htmlEditorImage';
                element.iconCls = 'ic-image';
                element.enableToggle = false;
                element.scope = this;
                element.clickEvent = 'mousedown';
                element.tabIndex = -1;
                element.tooltip = {
                        title: t("Image"),
                        text: t("Image")
                };
                element.overflowText = t("Image");

                var menuItems = [
                        {
                                iconCls: 'ic-computer',
                                text: t("Upload"),
                                handler: function() {
                                        go.util.openFileDialog({
                                                multiple: true,
                                                accept: "image/*",
                                                directory: false,
                                                autoUpload: false, // Change to false since we'll handle the file directly

                                                listeners: {
                                                        select: function(files) {
                                                                Array.from(files).forEach(file => {
                                                                        const reader = new FileReader();
                                                                        reader.onload = (e) => {
                                                                                const img = new Image();
                                                                                img.onload = () => {
                                                                                        const width = img.width > 600 ? '600px' : img.width + 'px';
                                                                                        const imgHtml = `<img src="${e.target.result}" alt="${file.name}" style="width: ${width}; height: auto;" />`;
                                                                                        this.editor.focus();
                                                                                        this.editor.insertAtCursor(imgHtml);
                                                                                };
                                                                                img.src = e.target.result;
                                                                        };
                                                                        reader.readAsDataURL(file);
                                                                });
                                                        },
                                                        scope: this
                                                }

                                        });
                                },
                                scope: this
                        }
                ];

                if (go.Modules.isAvailable("legacy", "files")) {
                        menuItems.push({
                                iconCls: 'ic-folder',
                                text: t("Add from Group-Office", "email").replace('{product_name}', GO.settings.config.product_name),
                                handler: function() {
                                        this.showFileBrowser();
                                },
                                scope: this
                        });
                }

                this.menu = element.menu = new Ext.menu.Menu({
                        items: menuItems
                });
                this.editor.tb.add(element);
        },

        selectImage: function(blobs) {
                // Handle files from Group-Office file browser
                const blob = blobs[0];
                fetch(go.Jmap.downloadUrl(blob.blobId))
                        .then(response => response.blob())
                        .then(fileBlob => {
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                        const img = new Image();
                                        img.onload = () => {
                                                const width = img.width > 600 ? '600px' : img.width + 'px';
                                                const imgHtml = `<img src="${e.target.result}" alt="${blob.name}" style="width: ${width}; height: auto;" />`;
                                                this.editor.focus();
                                                this.editor.insertAtCursor(imgHtml);
                                                GO.selectFileBrowserWindow.hide();
                                        };
                                        img.src = e.target.result;
                                };
                                reader.readAsDataURL(fileBlob);
                        })
                        .catch(error => {
                                console.error('Error loading image:', error);
                        });
        },

        showFileBrowser: function() {
                GO.files.createSelectFileBrowser();
                GO.selectFileBrowser.setFileClickHandler(this.selectImage, this, true);
                GO.selectFileBrowser.createBlobs = true;
                GO.selectFileBrowser.setFilesFilter(this.filesFilter);
                GO.selectFileBrowser.setRootID(this.root_folder_id, this.files_folder_id);
                GO.selectFileBrowserWindow.show();
                GO.selectFileBrowserWindow.show.defer(200, GO.selectFileBrowserWindow);
        },

        addImageHandlers: function() {
                const editorDoc = this.editor.getDoc();
                let activeWrapper = null;
                let isResizing = false;
                let lastRange = null; // Store the last cursor position

                // Add cursor control to the styles
                const style = editorDoc.createElement('style');
                style.innerHTML = `
            .img-wrapper {
                position: relative;
                display: inline-block;
                padding: 5px;
                margin: 5px;
                vertical-align: middle;
                outline: 2px solid #4285f4;
                user-select: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
            }
            .img-wrapper * {
                user-select: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
            }
            .resize-handle {
                width: 8px;
                height: 8px;
                background-color: #4285f4;
                position: absolute;
                z-index: 1000;
            }
            .resize-handle.tl { top: -4px; left: -4px; cursor: nwse-resize; }
            .resize-handle.tr { top: -4px; right: -4px; cursor: nesw-resize; }
            .resize-handle.bl { bottom: -4px; left: -4px; cursor: nesw-resize; }
            .resize-handle.br { bottom: -4px; right: -4px; cursor: nwse-resize; }
            .image-size {
                position: absolute;
                top: -20px;
                right: 0;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                font-size: 12px;
                padding: 2px 5px;
                border-radius: 3px;
            }
            /* Only hide cursor when image-editing is active */
            body.image-editing {
                caret-color: transparent !important;
            }

.img-wrapper {
    position: relative;
    display: inline-block;
    padding: 10px;
    margin: 10px;
    vertical-align: middle;
    outline: 2px solid #4285f4;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
.size-menu {
    position: absolute;
    bottom: -20px;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 12px;
    padding: 2px;
    border-radius: 3px;
    display: flex;
    gap: 2px;
}
.size-menu-option {
    padding: 2px 5px;
    cursor: pointer;
    border-radius: 2px;
    background: rgba(100, 100, 100, 0.8) !important;
    color: white;
}
.size-menu-option:hover {
    background: rgba(150, 150, 150, 0.8) !important;
}
        `;
                editorDoc.head.appendChild(style);

                const removeWrapper = () => {
                        if (activeWrapper && !isResizing && activeWrapper.parentNode) {
                                const img = activeWrapper.querySelector('img');
                                if (img) {
                                        const width = img.style.width || img.getAttribute('width');
                                        const height = img.style.height || img.getAttribute('height');

                                        activeWrapper.parentNode.insertBefore(img, activeWrapper);

                                        if (width) img.style.width = width.includes('px') ? width : width + 'px';
                                        if (height) img.style.height = height.includes('px') ? height : height + 'px';
                                }
                                activeWrapper.parentNode.removeChild(activeWrapper);
                                activeWrapper = null;

                                // Re-enable editing and remove image-editing class
                                this.editor.getDoc().designMode = 'on';
                                editorDoc.body.contentEditable = 'true';
                                editorDoc.body.classList.remove('image-editing');

                                // Restore cursor position
                                if (lastRange) {
                                        const selection = editorDoc.getSelection();
                                        selection.removeAllRanges();
                                        selection.addRange(lastRange);
                                        lastRange = null;
                                }
                        }
                };

                const startResize = (event, handle) => {
                        event.preventDefault();
                        event.stopPropagation();

                        if (!activeWrapper) {
                                console.error('No active wrapper during resize start');
                                return;
                        }

                        isResizing = true;
                        const startX = event.clientX;
                        const startWidth = activeWrapper.offsetWidth;
                        const startHeight = activeWrapper.offsetHeight;
                        const aspectRatio = startWidth / startHeight;
                        const img = activeWrapper.querySelector('img');
                        const imageSizeLabel = activeWrapper.querySelector('.image-size');

                        imageSizeLabel.style.display = 'block';

                        function onMouseMove(moveEvent) {
                                if (!isResizing) return;

                                moveEvent.preventDefault();
                                moveEvent.stopPropagation();

                                const deltaX = moveEvent.clientX - startX;
                                let newWidth;

                                if (handle.classList.contains('br') || handle.classList.contains('tr')) {
                                        newWidth = Math.max(50, startWidth + deltaX);
                                } else {
                                        newWidth = Math.max(50, startWidth - deltaX);
                                }

                                const newHeight = newWidth / aspectRatio;

                                requestAnimationFrame(() => {
                                        img.style.width = `${newWidth}px`;
                                        img.style.height = `${newHeight}px`;
                                        activeWrapper.style.width = `${newWidth}px`;
                                        imageSizeLabel.textContent = `${Math.round(newWidth)} x ${Math.round(newHeight)}`;
                                });
                        }

                        function onMouseUp(upEvent) {
                                isResizing = false;
                                imageSizeLabel.style.display = 'none';

                                editorDoc.removeEventListener('mousemove', onMouseMove, true);
                                document.removeEventListener('mousemove', onMouseMove, true);
                                editorDoc.removeEventListener('mouseup', onMouseUp, true);
                                document.removeEventListener('mouseup', onMouseUp, true);

                                upEvent.preventDefault();
                                upEvent.stopPropagation();
                        }

                        editorDoc.addEventListener('mousemove', onMouseMove, true);
                        document.addEventListener('mousemove', onMouseMove, true);
                        editorDoc.addEventListener('mouseup', onMouseUp, true);
                        document.addEventListener('mouseup', onMouseUp, true);
                };

                const wrapImage = (img) => {
                        // Store current cursor position before wrapping
                        const selection = editorDoc.getSelection();
                        if (selection.rangeCount > 0) {
                                lastRange = selection.getRangeAt(0).cloneRange();
                        }

                        removeWrapper();

                        const width = img.offsetWidth || img.width;
                        const height = img.offsetHeight || img.height;

                        const wrapper = editorDoc.createElement('div');
                        wrapper.className = 'img-wrapper';
                        wrapper.style.width = width + 'px';

                        const sizeLabel = editorDoc.createElement('div');
                        sizeLabel.className = 'image-size';
                        sizeLabel.textContent = `${Math.round(width)} x ${Math.round(height)}`;

                        img.parentNode.insertBefore(wrapper, img);
                        wrapper.appendChild(img);
                        wrapper.appendChild(sizeLabel);

                        ['tl', 'tr', 'bl', 'br'].forEach(pos => {
                                const handle = editorDoc.createElement('div');
                                handle.className = `resize-handle ${pos}`;
                                handle.addEventListener('mousedown', function(e) {
                                        startResize(e, handle);
                                }, true);
                                wrapper.appendChild(handle);
                        });

const sizeMenu = editorDoc.createElement('div');
sizeMenu.className = 'size-menu';

const options = [
    { text: 'Small', handler: () => resizeImage(img, 30) },
    { text: 'Best Fit', handler: () => resizeImage(img, 80) },
    { text: 'Original', handler: () => restoreOriginalSize(img) }
];

options.forEach(option => {
    const optionEl = editorDoc.createElement('div');
    optionEl.className = 'size-menu-option';
    optionEl.textContent = option.text;
    optionEl.addEventListener('mousedown', (e) => {
        e.stopPropagation();
        option.handler();
    }, true);
    sizeMenu.appendChild(optionEl);
});

wrapper.appendChild(sizeMenu);


                        activeWrapper = wrapper;

                        // Disable editing completely while wrapper is active
                        this.editor.getDoc().designMode = 'off';
                        editorDoc.body.contentEditable = 'false';
                        editorDoc.body.classList.add('image-editing');

                        // Clear current selection
                        const sel = editorDoc.getSelection();
                        sel.removeAllRanges();
                };

// Add these functions:
const resizeImage = (img, percentage) => {
    const wrapper = img.closest('.img-wrapper');
    if (!wrapper) return;

    const containerWidth = wrapper.parentElement.offsetWidth;
    const newWidth = (containerWidth * percentage) / 100;

    img.style.width = `${newWidth}px`;
    img.style.height = 'auto';
    wrapper.style.width = `${newWidth}px`;

    updateSizeLabel(wrapper);
};

const restoreOriginalSize = (img) => {
    const wrapper = img.closest('.img-wrapper');
    if (!wrapper) return;

    img.style.width = '';
    img.style.height = '';
    wrapper.style.width = `${img.naturalWidth}px`;

    updateSizeLabel(wrapper);
};

const updateSizeLabel = (wrapper) => {
    const img = wrapper.querySelector('img');
    const sizeLabel = wrapper.querySelector('.image-size');
    if (sizeLabel && img) {
        sizeLabel.textContent = `${Math.round(img.offsetWidth)} x ${Math.round(img.offsetHeight)}`;
    }
};
                const handleClick = (event) => {
                        if (GO.util.isMobileOrTablet()) return;
                        // Prevent default browser handling first
                        event.preventDefault();
                        event.stopPropagation();

                        // Don't handle click events during resize
                        if (isResizing) {
                                return;
                        }

                        if (event.target.closest('.resize-handle')) {
                                return; // Let the event propagate to the handle's mousedown listener
                        }

                        if (event.target.closest('.size-menu-option')) {
                                return;
                        }

                        if (event.target.tagName === 'IMG' && !event.target.closest('.img-wrapper')) {
                                // Use setTimeout to ensure the click event is fully processed
                                setTimeout(() => {
                                        wrapImage(event.target);
                                }, 0);
                                return;
                        }

                        if (!event.target.closest('.img-wrapper') && activeWrapper) {
                                removeWrapper();
                        }
                };

                // Use both mousedown and click handlers for Firefox
                editorDoc.addEventListener('mousedown', (event) => {
                        if (event.target.tagName === 'IMG' && !event.target.closest('.img-wrapper')) {
                                event.preventDefault();
                                event.stopPropagation();
                        }
                }, true);

                editorDoc.addEventListener('click', handleClick, true);

                // Add mouseup handler to prevent Firefox from removing selection
                editorDoc.addEventListener('mouseup', (event) => {
                        if (activeWrapper && !isResizing) {
                                event.preventDefault();
                                event.stopPropagation();
                                const selection = editorDoc.getSelection();
                                selection.removeAllRanges();
                        }
                }, true);

                // Additional Firefox-specific selection prevention
                editorDoc.addEventListener('selectstart', (event) => {
                        if (activeWrapper) {
                                event.preventDefault();
                                event.stopPropagation();
                                return false;
                        }
                }, true);

                // Prevent Firefox from handling the image as draggable
                editorDoc.addEventListener('dragstart', (event) => {
                        if (event.target.tagName === 'IMG' || event.target.closest('.img-wrapper')) {
                                event.preventDefault();
                                event.stopPropagation();
                                return false;
                        }
                }, true);

                // Prevent all keyboard input while wrapper is active
                editorDoc.addEventListener('keydown', (event) => {
                        if (activeWrapper) {
                                event.preventDefault();
                                event.stopPropagation();

                                if (event.key === 'Delete' || event.key === 'Backspace') {
                                        activeWrapper.parentNode.removeChild(activeWrapper);
                                        activeWrapper = null;

                                        // Re-enable editing after deletion
                                        this.editor.getDoc().designMode = 'on';
                                        editorDoc.body.contentEditable = 'true';
                                        editorDoc.body.classList.remove('image-editing');

                                        // Restore cursor position after deletion
                                        if (lastRange) {
                                                const selection = editorDoc.getSelection();
                                                selection.removeAllRanges();
                                                selection.addRange(lastRange);
                                                lastRange = null;
                                        }
                                }
                                return false;
                        }
                }, true);

                // Handle cases where we might lose the stored range
                this.editor.on('sync', () => {
                        lastRange = null;
                        removeWrapper();
                        activeWrapper = null;
                        this.editor.getDoc().designMode = 'on';
                        this.editor.getDoc().body.contentEditable = 'true';
                        this.editor.getDoc().body.classList.remove('image-editing');
                });

                this.editor.on('beforesync', () => {
                        lastRange = null;
                        removeWrapper();
                        activeWrapper = null;
                        this.editor.getDoc().designMode = 'on';
                        this.editor.getDoc().body.contentEditable = 'true';
                        this.editor.getDoc().body.classList.remove('image-editing');
                });

                // Prevent focus/selection events while wrapper is active
                editorDoc.addEventListener('selectionchange', (event) => {
                        if (activeWrapper) {
                                const selection = editorDoc.getSelection();
                                selection.removeAllRanges();
                        }
                }, true);

                // Prevent any focus events on the editor while wrapper is active
                editorDoc.addEventListener('focus', (event) => {
                        if (activeWrapper) {
                                event.preventDefault();
                                event.stopPropagation();
                        }
                }, true);
},

        addResizeHandlesToImage: function(img) {

                const editorDoc = this.editor.getDoc();
                const wrapper = img.closest('.img-wrapper');
                if (!wrapper) return;

                const createHandle = (className) => {
                        const handle = editorDoc.createElement('div');
                        handle.className = `resize-handle ${className}`;
                        wrapper.appendChild(handle);
                        return handle;
                };

                const handles = {
                        tl: createHandle('tl'),
                        tr: createHandle('tr'),
                        bl: createHandle('bl'),
                        br: createHandle('br')
                };

                const imageSizeLabel = wrapper.querySelector('.image-size');

                // Create ExtJS elements for the handles
                Object.entries(handles).forEach(([position, handle]) => {
                        const extHandle = new Ext.Element(handle);


                        extHandle.on('mousedown', (e, handleEl) => {
                                e.stopEvent();

                                const startX = e.getPageX();
                                const startWidth = wrapper.offsetWidth;
                                const startHeight = wrapper.offsetHeight;
                                const aspectRatio = startWidth / startHeight;

                                imageSizeLabel.style.display = 'block';

                                const handleMove = (moveEvent) => {
                                        moveEvent.stopEvent();

                                        const deltaX = moveEvent.getPageX() - startX;
                                        let newWidth;

                                        if (position === 'br' || position === 'tr') {
                                                newWidth = Math.max(50, startWidth + deltaX);
                                        } else {
                                                newWidth = Math.max(50, startWidth - deltaX);
                                        }

                                        const newHeight = newWidth / aspectRatio;

                                        wrapper.style.width = `${newWidth}px`;
                                        img.style.width = `${newWidth}px`;
                                        img.style.height = `${newHeight}px`;
                                        imageSizeLabel.textContent = `${Math.round(newWidth)} x ${Math.round(newHeight)}`;
                                };

                                const handleUp = () => {
                                        imageSizeLabel.style.display = 'none';

                                        // Remove both ExtJS and regular event listeners
                                        Ext.getDoc().un('mousemove', handleMove);
                                        Ext.getDoc().un('mouseup', handleUp);
                                        document.removeEventListener('mousemove', handleMove);
                                        document.removeEventListener('mouseup', handleUp);
                                };

                                // Add listeners to both the ExtJS document and regular document
                                Ext.getDoc().on('mousemove', handleMove);
                                Ext.getDoc().on('mouseup', handleUp);
                                document.addEventListener('mousemove', handleMove);
                                document.addEventListener('mouseup', handleUp);
                        });

                        // Prevent text selection when dragging
                        extHandle.on('selectstart', (e) => {
                                e.stopEvent();
                        });
                });

                // Test if handles are receiving events
                const testMouseEvent = new MouseEvent('mousedown', {
                        bubbles: true,
                        cancelable: true,
                        view: window
                });

                Object.entries(handles).forEach(([position, handle]) => {
                        handle.dispatchEvent(testMouseEvent);
                });
        }
});
