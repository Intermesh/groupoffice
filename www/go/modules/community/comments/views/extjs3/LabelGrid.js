go.modules.comments.LabelGrid = Ext.extend(go.grid.EditorGridPanel, {
	paging: true,
	initComponent: function () {

		this.store = new go.data.Store({
			fields: ['id', {name:'name'}, {name:'color'}],
			entityStore: "CommentLabel"
		});

		Ext.apply(this, {
			tbar: [{
				iconCls: 'ic-add',
				text: t('Add'),
				handler: function () {
					var r = new this.store.recordType({
						id: 0,
						name: '',
						color: ''
					});
					this.stopEditing();
					this.store.insert(0, r);
					this.startEditing(0, 1);
				},
				scope: this
			}, {
				iconCls: 'ic-delete',
				text: t('Delete'),
				handler: function () {
					var sel = this.selModel.getSelectedCell();
					if (sel) {
						// sel now contains an array of [row, col]
						this.store.removeAt(sel[0]);
					}
				},
				scope: this
			}],
			columns: [
				{
					hidden: true,
					header: 'ID',
					width: 40,
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					width: 175,
					sortable: true,
					dataIndex: 'name',
					editor: new Ext.form.TextField()
				},
				{
					header: t('Color'),
					width: 70,
					dataIndex: 'color',
					editor: new GO.form.ColorField(),
					renderer: function(value,meta,record) {
						return '<div style="background-color: #'+value+';width:45px;height:19px;"></div>';
					},
				}
			],
			viewConfig: {
				emptyText: 	'<i>label</i><p>' +t("No items to display") + '</p>'
			}
		});

		go.modules.comments.LabelGrid.superclass.initComponent.call(this);
	}
});

