GO.plugins.HtmlEditorImageInsert = function(config) {
    config = config || {};
    Ext.apply(this, config);

    this.init = function(htmlEditor) {
        this.editor = htmlEditor;
        this.editor.on('render', this.onRender, this);
        this.editor.on('initialize', this.addImageResizeHandler, this);
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
            text: t("Insert image in the text")
        };
        element.overflowText = t("Insert image in the text");

        var menuItems = [
            {
                iconCls: 'ic-computer',
                text: t("Upload"),
                handler: function() {
                    go.util.openFileDialog({
                        multiple: true,
                        accept: "image/*",
                        directory: false,
                        autoUpload: true,
                        listeners: {
                            upload: function(response) {
                                const imageUrl = go.Jmap.downloadUrl(response.blobId);

                                const img = new Image();
                                img.src = imageUrl;
                                img.onload = () => {
                                    const editorWidth = this.editor.getWidth();
                                    const editorHeight = this.editor.getHeight();
                                    const maxWidth = editorWidth / 3;
                                    const maxHeight = editorHeight / 3;

                                    let width = img.width;
                                    let height = img.height;

                                    if (width > maxWidth || height > maxHeight) {
                                        const widthRatio = maxWidth / width;
                                        const heightRatio = maxHeight / height;
                                        const scale = Math.min(widthRatio, heightRatio);

                                        width = width * scale;
                                        height = height * scale;
                                    }

                                    const imgWrapper = `<div class="img-wrapper" style="position: relative; display: inline-block; width: ${width}px;">
                                        <img src="${imageUrl}" alt="${response.name}" width="${width}" height="${height}" class="resizable-image" />
                                        <div class="image-size" style="position: absolute; top: -20px; right: 0; background: rgba(0, 0, 0, 0.7); color: white; font-size: 12px; padding: 2px 5px; border-radius: 3px; display: none;">${width} x ${height}</div>
                                    </div>`;
                                    this.editor.focus();
                                    this.editor.insertAtCursor(imgWrapper);

                                    this.addResizeHandlesToImage(this.editor.getDoc().querySelector('.resizable-image'));
                                };
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

    showFileBrowser: function() {
        GO.files.createSelectFileBrowser();
        GO.selectFileBrowser.setFileClickHandler(this.selectImage, this, true);
        GO.selectFileBrowser.createBlobs = true;
        GO.selectFileBrowser.setFilesFilter(this.filesFilter);
        GO.selectFileBrowser.setRootID(this.root_folder_id, this.files_folder_id);
        GO.selectFileBrowserWindow.show();
        GO.selectFileBrowserWindow.show.defer(200, GO.selectFileBrowserWindow);
    },

    selectImage: function(blobs) {
        var img = '<img src="' + go.Jmap.downloadUrl(blobs[0].blobId) + '" alt="' + blobs[0].name + '" />';
        this.editor.focus();
        this.editor.insertAtCursor(img);
        GO.selectFileBrowserWindow.hide();
    },

    addImageResizeHandler: function() {
        const editorDoc = this.editor.getDoc();

        // Add CSS for resizing handles and size label
        const style = editorDoc.createElement('style');
        style.innerHTML = `
            .img-wrapper .resize-handle {
                display: none; /* Hide handles by default */
            }
            .img-wrapper:hover .resize-handle {
                display: block; /* Show handles on hover */
            }
            .resize-handle {
                width: 8px;
                height: 8px;
                background-color: #4285f4;
                position: absolute;
                cursor: nwse-resize;
                z-index: 1000;
            }
            .resize-handle.tm { top: -4px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
            .resize-handle.bm { bottom: -4px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
            .resize-handle.ml { top: 50%; left: -4px; transform: translateY(-50%); cursor: ew-resize; }
            .resize-handle.mr { top: 50%; right: -4px; transform: translateY(-50%); cursor: ew-resize; }
            .resize-handle.tl { top: -4px; left: -4px; cursor: nwse-resize; }
            .resize-handle.tr { top: -4px; right: -4px; cursor: nesw-resize; }
            .resize-handle.bl { bottom: -4px; left: -4px; cursor: nesw-resize; }
            .resize-handle.br { bottom: -4px; right: -4px; cursor: nwse-resize; }
        `;
        editorDoc.head.appendChild(style);

        editorDoc.querySelectorAll('.resizable-image').forEach(img => this.addResizeHandlesToImage(img));

        // Listen for paste events to handle pasted images
        editorDoc.addEventListener('paste', (event) => {
            setTimeout(() => {
                const pastedImages = editorDoc.querySelectorAll('img');
                pastedImages.forEach((img) => {
                    if (!img.closest('.img-wrapper')) {
                        const finalizeImageSetup = () => {
                            // Get precise image dimensions
                            const rect = img.getBoundingClientRect();
                            const width = rect.width;
                            const height = rect.height;

                            // Create wrapper without setting height
                            const wrapper = editorDoc.createElement('div');
                            wrapper.className = 'img-wrapper';
                            wrapper.style.position = 'relative';
                            wrapper.style.display = 'inline-block';
                            wrapper.style.width = `${width}px`;

                            // Apply styling to image to fit wrapper
                            img.style.margin = 0;
                            img.style.padding = 0;
                            img.style.border = 'none';
                            img.style.width = '100%';
                            img.style.height = 'auto';

                            // Create and add size label
                            const sizeLabel = editorDoc.createElement('div');
                            sizeLabel.className = 'image-size';
                            sizeLabel.style.position = 'absolute';
                            sizeLabel.style.top = '-20px';
                            sizeLabel.style.right = '0';
                            sizeLabel.style.background = 'rgba(0, 0, 0, 0.7)';
                            sizeLabel.style.color = 'white';
                            sizeLabel.style.fontSize = '12px';
                            sizeLabel.style.padding = '2px 5px';
                            sizeLabel.style.borderRadius = '3px';
                            sizeLabel.style.display = 'none';
                            sizeLabel.textContent = `${width} x ${height}`;

                            // Insert wrapper, move image inside, and add size label
                            img.parentNode.insertBefore(wrapper, img);
                            wrapper.appendChild(img);
                            wrapper.appendChild(sizeLabel);

                            this.addResizeHandlesToImage(img);
                        };

                        // Use requestAnimationFrame to ensure proper rendering before applying logic
                        requestAnimationFrame(finalizeImageSetup);
                    }
                });
            }, 10); // Delay to ensure images are pasted before processing
        });
    },

    addResizeHandlesToImage: function(img) {
        const editorDoc = this.editor.getDoc();

        const wrapper = img.parentNode;
        if (!wrapper.classList.contains('img-wrapper')) return;

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
            br: createHandle('br'),
            tm: createHandle('tm'), // Top middle
            bm: createHandle('bm'), // Bottom middle
            ml: createHandle('ml'), // Middle left
            mr: createHandle('mr')  // Middle right
        };

        const imageSizeLabel = wrapper.querySelector('.image-size');

        const startResize = (event, handle) => {
            event.preventDefault();

            const startX = event.clientX;
            const startY = event.clientY;
            const startWidth = wrapper.offsetWidth;
            const startHeight = wrapper.offsetHeight;
            const aspectRatio = startWidth / startHeight;

            imageSizeLabel.style.display = 'block';

            const onMouseMove = (moveEvent) => {
                const deltaX = moveEvent.clientX - startX;
                const deltaY = moveEvent.clientY - startY;
                let newWidth = startWidth;
                let newHeight = startHeight;

                if (handle === handles.br || handle === handles.tr || handle === handles.bl || handle === handles.tl) {
                    if (handle === handles.br || handle === handles.tr) {
                        newWidth = startWidth + deltaX;
                        newHeight = newWidth / aspectRatio;
                    } else {
                        newWidth = startWidth - deltaX;
                        newHeight = newWidth / aspectRatio;
                    }
                } else if (handle === handles.tm || handle === handles.bm) {
                    newHeight = startHeight + (handle === handles.bm ? deltaY : -deltaY);
                    newWidth = newHeight * aspectRatio;
                } else if (handle === handles.ml || handle === handles.mr) {
                    newWidth = startWidth + (handle === handles.mr ? deltaX : -deltaX);
                    newHeight = newWidth / aspectRatio;
                }

                img.style.width = `${newWidth}px`;
                img.style.height = `${newHeight}px`;
                wrapper.style.width = `${newWidth}px`;

                imageSizeLabel.textContent = `${Math.round(newWidth)} x ${Math.round(newHeight)}`;
            };

            const onMouseUp = () => {
                imageSizeLabel.style.display = 'none';
                editorDoc.removeEventListener('mousemove', onMouseMove);
                editorDoc.removeEventListener('mouseup', onMouseUp);
            };

            editorDoc.addEventListener('mousemove', onMouseMove);
            editorDoc.addEventListener('mouseup', onMouseUp);
        };

        Object.entries(handles).forEach(([key, handle]) => {
            handle.onmousedown = (event) => startResize(event, handle);
        });
    }
});
