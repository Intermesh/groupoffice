go.modules.community.tasks.TasklistDialog = Ext.extend(go.form.Dialog, {
	title: t("Tasklist", "tasks"),
	entityStore: "Tasklist",
	titleField: "name",
	width: dp(800),
	height: dp(600),
	initFormItems: function () {
		this.addPanel(new go.permissions.SharePanel());

		return [{
			xtype: 'fieldset',
			items: [{
				xtype: 'hidden',
				allowBlank: false,
				value: 'list',
				name: 'role'
			},{
				xtype: 'textfield',
				name: 'name',
				fieldLabel: t("Name"),
				anchor: '100%',
				allowBlank: false
			},new go.users.UserCombo({
				fieldLabel: t('Owner'),
				hiddenName: 'ownerId',
				value: go.User.id
			})]
		}];
	},

	createColumnsGrid: function() {
		var store = new go.data.Store({
			fields: ['id', {name:'name'}, {name:'color'}],
			entityStore: "CommentLabel"
		}),grid = new go.form.GridField({
			hideHeaders: false,
			fieldLabel: t('Columns'),
			cls:'',
			itemId: 'columnsGrid',
			displayField: 'columns',
			store: store,
			tbar: [{
				iconCls: 'ic-add',
				text: t('Add'),
				handler: function () {
					var r = new store.recordType({
						id: 0,
						name: '',
						color: ''
					});
					grid.stopEditing();
					store.insert(0, r);
					grid.startEditing(0, 1);
				}
			}, {
				iconCls: 'ic-delete',
				text: t('Delete'),
				handler: function () {
					var sel = grid.selModel.getSelectedCell();
					if (sel) {
						// sel now contains an array of [row, col]
						store.removeAt(sel[0]);
					}
				}
			}],
			columns: [{
				hidden: true,
				header: 'ID',
				width: 40,
				sortable: true,
				dataIndex: 'id'
			}, {
				id: 'name',
				header: t('Name'),
				width: 175,
				sortable: true,
				dataIndex: 'name',
				editor: new Ext.form.TextField()
			}, {
				header: t('Color'),
				width: 70,
				dataIndex: 'color',
				editor: new GO.form.ColorField(),
				renderer: function(value,meta,record) {
					return '<div style="background-color: #'+value+';width:45px;height:19px;"></div>';
				},
			}],
			viewConfig: {
				emptyText: 	'<i>label</i><p>' +t("No columns created") + '</p>'
			}
		});
		return grid;
	}
});
