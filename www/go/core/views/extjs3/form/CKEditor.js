go.form.CKEditor = Ext.extend(Ext.form.TextArea, {

    editorConfig: {},

    editorId: null,

    editor: null,

    constructor: function(config) {

        config = config || {};
        config.editorConfig = config.editorConfig || {};

        if (config.grow) {
            config.editorConfig.extraPlugins = 'autogrow';
            config.editorConfig.autoGrow_minHeight = 200;
            config.editorConfig.autoGrow_maxHeight = 600;
            config.editorConfig.autoGrow_bottomSpace = 50;
        }

        go.form.CKEditor.superclass.constructor.call(this, config);
    },

    /**
     *
     */
    initComponent: function () {

        go.form.CKEditor.superclass.initComponent.call(this);

        this.addEvents({
            editorReady: true,
            attach: true,
        });

        this.on('afterrender', function () {
            var me = this;

            Ext.applyIf(me.editorConfig, {
                resize_enabled: false,
                language: go.User.language,
                toolbar: [
                    {
                        name: 'go',
                        items: [
                            'Format',
                            'Undo', 'Redo', '-',
                            'Bold', 'Italic', 'Underline', '-',
                            'TextColor', 'BGColor',
                            'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-',
                            'Link', '-', 'NumberedList', 'BulletedList', '-',
                            'Outdent', 'Indent', '-',
                            'PasteText', 'PasteFromWord', 'HorizontalRule', '-',
                            'Subscript', 'Superscript', 'Image', 'Smiley', '-',
                            'Source',
                        ]
                    },
                ],
            });

            me.editor = CKEDITOR.replace(me.el.id, me.editorConfig);
            me.editorId = me.el.id;
            me.editor.name = me.name;

            me.editor.on("instanceReady", function (ev) {

                //copy paste via word button
                me.editor.on("beforeCommandExec", function (event) {
                    // Show the paste dialog for the paste buttons and right-click paste
                    if (event.data.name === "paste") {
                        event.editor._.forcePasteDialog = true;
                    }
                    // Don't show the paste dialog for Ctrl+Shift+V
                    if (event.data.name === "pastetext" && event.data.commandData.from === "keystrokeHandler") {
                        event.cancel();
                    }
                });

                //keydown - submit
                me.editor.on('key', function (evt) {
                    if (Ext.EventObject.ctrlKey && Ext.EventObject.ENTER === evt.data.domEvent.$.keyCode) {
                        this.fireEvent('ctrlenter', this);
                    }
                }, me);

                //change
                me.editor.on('change', function () {
                    me.fireEvent('editorChange', me.editor, me.editor.getData());
                });

                //resize
                me.editor.resize(me.lastSize.width, me.lastSize.height);

                //fire ready
                me.fireEvent("editorReady", me, me.editor);
            });

            me.editor.latestTransferId = null;
            me.editor.on('paste', function (evt) {
                var dataObj = evt.data,
                    dataTransfer = dataObj.dataTransfer,
                    filesCount = dataTransfer.getFilesCount(),
                    file;

                if (!filesCount || me.editor.latestTransferId === dataTransfer.id) {
                    return;
                }

                for (var i = 0; i < filesCount; i++) {
                    file = dataTransfer.getFile(i);

                    go.Jmap.upload(file, {
                        success: function (response) {
                            if (file.type.match(/^image\//)) {
                                evt.data.dataValue = '<img style="max-width: 100%" src="' + go.Jmap.downloadUrl(response.blobId, true) + '" alt="' + file.name + '" />';
                            } else {
                                //evt.data.dataValue = '<a href="' + go.Jmap.downloadUrl(response.blobId) + '">' + file.name + '</a>';
                            }
                            evt.data.type = 'html';
                            me.editor.fire('paste', evt.data);
                            me.fireEvent('attach', me, response, file)
                        },
                        scope: this,
                    });
                }

                me.editor.latestTransferId = dataTransfer.id
                evt.stop();
            });

        }, this);

        //editor ready
        this.on('editorReady', function (editor) {
            editor.editor.setReadOnly(false);

            if (this.value) {
                editor.editor.setData(this.value);
            }
        }, this);

        //editor change
        this.on('editorChange', function (editor, value) {
            go.form.CKEditor.superclass.setValue.call(this, value);
            this.fireEvent('change', this, value);
        }, this, {buffer: 1000});

        //editor resize
        this.on('resize', function (field, adjWidth, adjHeight) {
            if (this.editor && this.editor.status === 'ready') {
                this.editor.resize(adjWidth || this.container.getWidth(), adjHeight || this.container.getHeight());
            }
        }, this);
    },

    /**
     *
     */
    afterRender: function () {
        if (!this.el) {
            this.defaultAutoCreate = {
                tag: 'textarea',
                style: "width:100%;height:100%;",
                autocomplete: 'off'
            };
        }
        go.form.CKEditor.superclass.afterRender.call(this);
    },

    /**
     * @param value
     */
    setValue: function (value) {
        go.form.CKEditor.superclass.setValue.call(this, value);
        if (this.editor && this.editor.editor) {
            this.editor.editor.setData(value);
        }
    },

    /**
     * @returns {string|*}
     */
    getValue: function () {
        if (this.editor) {
            return this.editor.getData();
        }
        return '';
    },

    /**
     *
     */
    destroy: function () {
        if (!Ext.isEmpty(CKEDITOR.instances[this.editorId])) {
            delete CKEDITOR.instances[this.editorId];
        }

        delete this.editorConfig;
        delete this.editorId;
        delete this.editor;
    },

    /**
     * @param textarea
     * @param width
     * @param height
     */
    resize: function (textarea, width, height) {
        if (!Ext.isEmpty(CKEDITOR.instances[this.editorId])) {
            CKEDITOR.instances[this.editorId].resize(width, height);
        }
    },
});

Ext.reg("ckeditor", go.form.CKEditor);