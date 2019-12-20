go.systemsettings.AuthAllowGroupGrid = Ext.extend(go.grid.EditorGridPanel, {
  title: t("Allowed groups"),
  initComponent: function () {

    this.store = new go.data.Store({
      fields: ['id', {name:'group', type: "relation"}, {name:'ipPattern'}],
      entityStore: "AuthAllowGroup",
      autoLoad: true
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
          sortable: false,
          dataIndex: 'id'
        },
        {
          id: 'group',
          header: t('Group'),
          width: 175,
          sortable: false,
          dataIndex: 'groupId',
          editor: new go.groups.GroupCombo(),
          renderer: function(v, meta, data) {
            return data.group.name;
          }
        },
        {
          header: t('IP pattern'),
          width: 70,
          dataIndex: 'ipPattern',
          editor: new Ext.form.TextField()
        }
      ],
      viewConfig: {
        emptyText: 	'<i>label</i><p>' +t("No items to display") + '</p>'
      }
    });

    go.systemsettings.AuthAllowGroupGrid.superclass.initComponent.call(this);


  }
});

