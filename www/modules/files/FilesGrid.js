GO.files.FilesGrid = function (config) {

    config = config || {};
    config.layout = 'fit';
    config.split = true;
    config.paging = true;
    if (!go.util.isMobileOrTablet())
        config.autoExpandColumn = 'name';
    config.sm = new Ext.grid.RowSelectionModel();
    config.loadMask = true;
    config.enableDragDrop = true;
    config.ddGroup = 'FilesDD';

    config.viewConfig = {
        emptyText: '<div class="go-dropzone">' + t('Drop files here') + '</div>',
        getRowClass: function (record, rowIndex, rowParams, store) {

            if (GO.files.isContentExpired(record.json.content_expire_date)) {
                return 'content-expired';
            } else {
                return '';
            }
        }
    };

    GO.files.FilesGrid.superclass.constructor.call(this, config);
};
Ext.extend(GO.files.FilesGrid, GO.grid.GridPanel, {
    applyStoredState: function (state) {
        delete state.sort;

        GO.files.FilesGrid.superclass.applyState.call(this, state);
        if (this.rendered) {
            this.reconfigure(this.store, this.getColumnModel());
            this.getColumnModel().setColumnWidth(0, this.getColumnModel().getColumnWidth(0));
        }

        //this.enableState.defer(500,this);
    },

    initComponent: function() {
        function onFilesDeleteKey(key, e) {
            if (e.target.tagName === "INPUT") {
                return;
            }

            const params = {
                deleteParam: "trash_keys",
                callback: function () {
                    const treeNode = this.treePanel.getNodeById(this.folder_id);
                    if (treeNode) {
                        delete treeNode.attributes.children;
                        treeNode.reload();
                    }
                }
            };
            // Ctrl + Delete will permanently delete a file or folder instead of trashing it
            if (e.ctrlKey) {
                delete params.deleteParam;
            }
            this.deleteSelected(params);
        }
        GO.files.FilesGrid.superclass.initComponent.call(this);
        this.keys.shift();
        this.keys.push({
            key: Ext.EventObject.DELETE,
            fn: onFilesDeleteKey,
            scope:this
        });

    }
});