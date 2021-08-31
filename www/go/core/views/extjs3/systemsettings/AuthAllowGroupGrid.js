go.systemsettings.AuthAllowGroupGrid = Ext.extend(go.grid.EditorGridPanel, {
  hasPermission: function() {
    return go.User.isAdmin;
  },
  autoHeight: true,
  initComponent: function () {

    this.store = new go.data.Store({
      fields: ['id', 'groupId', {name:'group', type: "relation", allowBlank:false}, {name:'ipPattern', allowBlank:false}],
      entityStore: "AuthAllowGroup"
    });

    var actions = this.initRowActions();

    Ext.apply(this, {
      plugins: [actions],
      tbar: [ '->',{
        iconCls: 'ic-add',
        tooltip: t('Add'),
        handler: function () {
          var r = new this.store.recordType({
            ipPattern: '',
            groupId: null
          });
          this.stopEditing();
          this.store.add(r);
          this.startEditing(this.store.getCount() - 1, 0);
        },
        scope: this
      }],
      columns: [
        {
          id: 'group',
          header: t('Group'),
          sortable: false,
          dataIndex: 'groupId',
          editor: this.groupEditor = new go.groups.GroupCombo({
            allowBlank: false
          }),
          renderer: function(v, meta, record) {
            return record.data.group ? record.data.group.name : "";
          }
        },
        {
          header: t('IP pattern'),
          width: 70,
          dataIndex: 'ipPattern',
          editor: new Ext.form.TextField({
            allowBlank: false
          })
        },
        actions
      ],
      viewConfig: {
        emptyText: 	'<i>lock</i><p>' +t("No items to display") + '</p>',
        autoFill: true
      }
    });

    go.systemsettings.AuthAllowGroupGrid.superclass.initComponent.call(this);

    this.on("afteredit", function(e) {


      if(e.field == "groupId") {
        if(!e.value) {
          return;
        }
        var groupRecord = this.groupEditor.store.getById(e.value);
        e.record.set('group', groupRecord.data);
      }

      var me = this;

      if(!e.record.isValid()) {
        return;
      }

      go.Db.store('AuthAllowGroup').save({
        ipPattern: e.record.data.ipPattern,
        groupId: e.record.data.groupId
      }, e.record.data.id).then(function() {
        //e.record.commit();
      });
    }, this);

    this.on("render", function() {
      this.store.load();
    }, this);
  },

  initRowActions : function() {
    var actions = new Ext.ux.grid.RowActions({
      menuDisabled: true,
      hideable: false,
      draggable: false,
      fixed: true,
      header: '',
      hideMode: 'display',
      keepSelection: true,
      actions: [{
        iconCls: 'ic-delete'
      }]
    });

    actions.on({
      action: function (grid, record, action, row, col, e, target) {
        Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete the selected item?"), function (btn) {

          if (btn != "yes") {
            return;
          }

          if(record.data.id) {
            go.Db.store("AuthAllowGroup").destroy(record.data.id).then(function () {
              grid.store.removeAt(row);
            });
          } else
          {
            grid.store.removeAt(row);
          }

        }, this);
      },
      scope: this
    });

    return actions;
  }
});

