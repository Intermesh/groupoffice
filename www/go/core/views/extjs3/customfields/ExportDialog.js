go.customfields.ExportDialog = Ext.extend(go.Window, {
    title: t('Export fieldsets to JSON-file'),
    initComponent: function() {
        this.panel = new go.customfields.ExportPanel();
        this.items = [
            this.panel
        ];
        this.supr().initComponent.call(this);
    },
    setEntity: function(entity) {
        this.panel.entity = entity;
        this.setTitle(t('Export fieldsets to JSON-file') + ': ' + go.Entities.get(entity).title);
    }
});

go.customfields.ExportPanel = Ext.extend(go.grid.GridPanel, {
    width: dp(1000),
    height: dp(800),
    entity: '',
    autoScroll: true,
    initComponent: function () {
        this.columns = [
            new GO.grid.CheckColumn({
                header: t('Export'),
                dataIndex: 'export'
            }), {
                header: t('Fieldset'),
                dataIndex: 'name'
            }
        ];
        this.store = new Ext.data.ArrayStore({
            fields: [
                'name',
                'id',
                'export'
            ]
        });

        this.tbar = [
            {
                iconCls: 'ic-save',
                tooltip: t('Save'),
                handler: function() {
                    var fieldSetIds = [];
                    this.store.each(function(record) {
                        if (record.data.export) {
                            fieldSetIds.push(record.data.id);
                        }
                    });
                    var params = {
                        fieldSetIds: fieldSetIds,
                        entity: this.entity
                    }
                    go.Jmap.request({
                        method: 'FieldSet/exportToJson',
                        params: params,
                        scope: this,
                        callback: function(request, tmp, response, callId) {
                            go.util.downloadFile(go.Jmap.downloadUrl(response.blobId, false));
                        }
                    });
                },
                scope: this
            }
        ]
        this.supr().initComponent.call(this);

        this.on('render', function () {
            this.load();
        }, this);



    },
    load: function() {
        this.loading = true;
        this.store.removeAll();
        go.Db.store('FieldSet').query({
            filter: {
                entities: [this.entity]
            }
        }, function(response) {
            if (!response.ids.length) {
                this.store.loadData([], false);
                this.loading = false;
                return;
            }
            go.Db.store('FieldSet').get(response.ids, function(fieldSets) {
                var storeData = [];
                fieldSets.forEach(function(fs) {
                    storeData.push([
                        fs.name,
                        fs.id,
                        false
                    ]);
                });
                this.store.loadData(storeData, true)
                this.loading = false;
            }, this);
        }, this);
    }
})