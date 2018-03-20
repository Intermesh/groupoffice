go.form.multiselect.Window = Ext.extend(go.Window, {
	width: dp(600),
	height: dp(600),
	title: t("Add"),
	layout: "fit",
	field: null,
	initComponent: function () {
		
		
		this.grid = new go.grid.GridPanel({
			viewConfig: {
				emptyText: t("No items to display")
			},
			store: new go.data.Store({
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
			}),
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
				rowclick : function(grid, rowIndex, e) {
					
					var r = {}, selected = grid.store.getAt(rowIndex);
					r[this.field.idField] = selected.data.id;
					this.field.store.loadData({records: [r]}, true);
				
					this.field._isDirty = true;
					this.close();
				},
				scope: this
			}
		});
		
		this.items = [this.grid];

		go.form.multiselect.Window.superclass.initComponent.call(this);
		
		this.on("render", function() {
			this.grid.store.load();
		});
	}

});
