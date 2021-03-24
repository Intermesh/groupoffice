go.form.CKEditor5 = Ext.extend(Ext.form.TextArea, {

    editorConfig: {},

    editorId: null,

    editor: null,

    /**
     *
     */
    initComponent: function () {

        go.form.CKEditor5.superclass.initComponent.call(this);

        this.addEvents({
            editorReady: true,
        });

        this.on('afterrender', function () {
            var me = this;

            Ext.apply(me.editorConfig, {
                height: me.getHeight(),
                width: me.getWidth(),
                resize_enabled: false,
                toolbar: {
                    items: [
                        'undo', 'redo', '|',
                        'bold', 'italic', 'underline', '|',
                        'fontfamily', 'fontsize', '|',
                        'alignment', '|',
                        'fontColor', 'fontBackgroundColor',
                        'link', '|', 'bulletedList', 'numberedList', 'todoList', '|',
                        'outdent', 'indent', '|',
                        'code', 'codeBlock', '|',
                        'insertTable', '|',
                        'uploadImage', 'blockQuote', '|',
                    ]
                },
                language: go.User.language,
                /*simpleUpload: {
                    // The URL that the images are uploaded to.
                    uploadUrl: go.User.uploadUrl,

                    // Enable the XMLHttpRequest.withCredentials property.
                    withCredentials: true,

                    // Headers sent along with the XMLHttpRequest to the upload server.
                    headers: {
                        Authorization: Ext.Ajax.defaultHeaders.Authorization,
                    }
                }*/
            });

            window.CKEDITORS = window.CKEDITORS || {};

            ClassicEditor
                .create(document.querySelector('#' + me.el.id), me.editorConfig)
                .then(function (editor) {

                    //global variable
                    CKEDITORS[me.el.id] = editor;

                    //local editor values
                    me.editor = editor;
                    me.editorId = me.el.id;
                    me.editor.name = me.name;

                    //change event
                    me.editor.model.document.on('change:data', function () {
                        me.fireEvent('editorChange', me.editor, me.editor.getData());
                    });

                    //resize
                    me.editor.editing.view.change(function (writer) {
                        var height = me.lastSize.height - Ext.fly(me.editor.ui.view.toolbar.element).getHeight();
                        writer.setStyle('height', height + 'px', me.editor.editing.view.document.getRoot());
                    });

                    //fire ready
                    me.fireEvent("editorReady", me, me.editor);
                })
                .catch(function (err) {
                    console.error(err)
                });
        }, this);

        //editor ready
        this.on('editorReady', function (editor) {
            editor.editor.isReadOnly = false;

            if (this.value) {
                editor.setData(this.value);
            }
        }, this);

        //editor change
        this.on('editorChange', function (editor, value) {
            go.form.CKEditor5.superclass.setValue.call(this, value);
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
                autocomplete: 'off'
            };
        }
        go.form.CKEditor5.superclass.afterRender.call(this);
    },

    /**
     * @param value
     */
    setValue: function (value) {
        go.form.CKEditor5.superclass.setValue.call(this, value);
        if (this.editor) {
            this.editor.setData(value);
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
        if (!Ext.isEmpty(CKEDITORS[this.editorId])) {
            delete CKEDITORS[this.editorId];
        }
    },

    /**
     * @param textarea
     * @param width
     * @param height
     */
    resize: function (textarea, width, height) {
        if (!Ext.isEmpty(CKEDITORS[this.editorId])) {
            CKEDITORS[this.editorId].resize(width, height);
        }
    },
});

Ext.reg("ckeditor5", go.form.CKEditor5);