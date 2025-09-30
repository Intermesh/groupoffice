GO.files.TrashGrid = function (config = {}) {

    config.layout = 'fit';
    config.split = true;
    config.paging = true;
    if (!go.util.isMobileOrTablet()) {
        config.autoExpandColumn = 'fullPath';
    }
    config.sm = new Ext.grid.RowSelectionModel();
    config.loadMask = true;
    config.enableDragDrop = false;
    config.cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: t("id"),
            name: "id",
            hidden: true
        }, {
            header: t("Type"),
            name: "entity",
            sortable: false,
            width: 50,
            renderer: function(v) {
                let cls = "filetype ";
                if(v === "d") {
                    cls += "filetype-folder";
                }
                return '<div class="go-grid-icon ' + cls + '" style="float:left;"></div>';

            }
        }, {
            header: t("Name"),
            id: "name",
            dataIndex: "name",
            name: "name"
        }, {
            header: t("Path"),
            id: "fullPath",
            dataIndex: "fullPath",
            name: "fullPath"
        }, {
            header: t("Deleted by"),
            name: "deletedByUser",
            dataIndex: "deletedByUser"
        }, {
            header: t("Deleted at"),
            name: "deletedAt",
            dataIndex: "deletedAt",
            renderer: function(v) {
                const dt = new Date(v);
                return dt.format(go.User.dateFormat);
            }
        }]
    });

    GO.files.TrashGrid.superclass.constructor.call(this, config);
};

Ext.extend(GO.files.TrashGrid, GO.grid.GridPanel, {
    applyStoredState: function (state) {
        delete state.sort;

        GO.files.FilesGrid.superclass.applyState.call(this, state);
        if (this.rendered) {
            this.reconfigure(this.store, this.getColumnModel());
            this.getColumnModel().setColumnWidth(0, this.getColumnModel().getColumnWidth(0));
        }
    }
});
