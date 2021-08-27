go.groups.GroupMemberWindow = Ext.extend(go.Window, {
  title: t("Members"),
  layout: "fit",
  width: dp(800),
  height: dp(600),
  initComponent: function () {

    this.grid = new go.grid.GridPanel({
      store: new go.data.Store({
        fields: [
          'id',
          'username',
          'displayName',
          'avatarId'          
        ],
        filters: {
          'group': {
            groupId: null
          }
        },
        sortInfo: {
          field: 'displayName',
          direction: 'ASC'
        },
        entityStore: "UserDisplay"
      }),

      tbar: [
        '->',
      {
        xtype: 'tbsearch',
        filters: [
          'text'
        ]
      }
      ],
      columns: [
        {
          id: 'name',
          header: t('Name'),
          width: dp(200),
          sortable: false,
          dataIndex: 'displayName',
          renderer: function (value, metaData, record, rowIndex, colIndex, store) {

            var style = record.get('avatarId') ? 'background-image: url(' + go.Jmap.thumbUrl(record.get("avatarId"), {w: 40, h: 40, zc: 1}) + ')"' : "";

            return '<div class="user"><div class="avatar" style="' + style + '"></div>' +
              '<div class="wrap">' +
              '<div class="displayName">' + value + '</div>' +
              '<small class="username">' + Ext.util.Format.htmlEncode(record.get('username')) + '</small>' +
              '</div>' +
              '</div>';
          }
        }
        
      ],
      viewConfig: {
        emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
        forceFit: true,
        autoFill: true
      }

    });

    this.items = [this.grid];

    go.groups.GroupMemberWindow.superclass.initComponent.call(this);
  },

  load: function(groupId) {
    this.grid.store.setFilter("group", {groupId: groupId});
    this.grid.store.load();
    var me = this;
    go.Db.store("Group").single(groupId).then(function(group) {
      me.setTitle(t("Members") + ": " + Ext.util.Format.htmlEncode(group.name));
    });

    return this;
  }
});