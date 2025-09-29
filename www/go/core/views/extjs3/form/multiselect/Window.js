go.form.multiselect.Window = Ext.extend(go.Window, {
	width: dp(600),
	height: dp(600),
	title: t("Add"),
	layout: "fit",
	field: null,
	initComponent: function () {


		const storeConfig = this.field.storeConfig || {};

		Ext.apply(storeConfig, {
			sortInfo: {
				field: this.field.displayField
			},
			fields: ['id', this.field.displayField],
			entityStore: this.field.entityStore,
			baseParams: this.field.storeBaseParams,
			listeners: {
				load: function() {
					var ids = this.field.getIds();
					this.grid.store.filterBy(function(r) {
						return ids.indexOf(r.id) === -1;
					});
				},
				scope: this
			}
		});
		
		
		this.grid = new go.grid.GridPanel({
			tbar: [ '->', {
				xtype: "tbsearch"
			}],
			viewConfig: {
				emptyText: t("No items to display")
			},
			multiSelectToolbarEnabled: false,
			store: new go.data.Store(storeConfig),
			columns: [
				{
					id: 'name',
					header: t('Name'),
					sortable: false,
					dataIndex: this.field.displayField,
					hideable: false,
					draggable: false,
					menuDisabled: true
				}
			],
			autoExpandColumn: "name",
			listeners: {
				rowdblclick : function(grid, rowIndex, e) {
                    /*
					var r = {}, selected = grid.store.getAt(rowIndex);
					r[this.field.idField] = selected.data.id;
					this.field.store.loadData({records: [r]}, true);
                    */
                    const r = this.field.getValue(), selected = grid.store.getAt(rowIndex);
                    if(this.field.valueIsId) {
                        r.push(selected.id);
                    } else {
                        r.push({[this.field.idField] : selected.id})
                    }
                    this.field.setValue(r);
                    this.field._isDirty = true;
					this.close();
				},
				scope: this
			}
		});
		
		this.items = [this.grid];

		this.buttons = [
			"->",
			{
				text: t("Ok"),
				handler: function() {
                    const records = this.field.getValue(), selected = this.grid.getSelectionModel().getSelections();
                    selected.forEach((r) => {
                        if(this.field.valueIsId) {
                            records.push(r.data.id);
                        } else {
                            records.push({[this.field.idField] : r.id})
                        }
					})
                    this.field.setValue(records);
                    this.field._isDirty = true;
                    this.close();
					// this.field.store.loadData({records: records}, true);
                    //
					// this.field._isDirty = true;
					// this.close();
				},
				scope: this
			}
		]

		go.form.multiselect.Window.superclass.initComponent.call(this);
		
		this.on("render", function() {
			this.grid.store.load();
		});
	}

});
