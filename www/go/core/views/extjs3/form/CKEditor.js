go.form.CKEditor = Ext.extend(Ext.form.TextArea, {

    editorConfig: {},

    editorId: null,

    editor: null,

    /**
     *
     */
    initComponent: function () {

        go.form.CKEditor.superclass.initComponent.call(this);

        this.addEvents({
            editorReady: true,
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

                //resize
                me.editor.resize(me.lastSize.width, me.lastSize.height);

                //fire ready
                me.fireEvent("editorReady", me, me.editor);
            });

            me.editor.on('change', function () {
                me.fireEvent('editorChange', me.editor, me.editor.getData());
            });

            me.editor.on('paste', function (evt) {
                var dataTransfer = evt.data.dataTransfer,
                    file;

                if(!dataTransfer.getFilesCount()) {
                    return;
                }

                for (var i=0; i < dataTransfer.getFilesCount(); i++) {
                    file = dataTransfer.getFile(i);
                    go.Jmap.upload(file, {
                        success: function(response) {
                            if (file.type.match(/^image\//)) {
                                evt.data.dataValue = '<img style="max-width: 100%" src="' + go.Jmap.downloadUrl(response.blobId, true) + '" alt="' + file.name + '" />';
                            } else {
                                evt.data.dataValue = '<a href="' + go.Jmap.downloadUrl(response.blobId) + '">' + file.name + '</a>';
                            }
                            evt.data.type = 'html';
                            me.editor.fire( 'paste', evt.data );
                        },
                        scope: this,
                    });
                }
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