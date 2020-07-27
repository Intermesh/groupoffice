go.users.UserGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function() {
		Ext.apply(this, {
			store: new go.data.Store({
				fields: [
					'id',
					'username',
					'displayName',
					'avatarId',
					'email'
				],
				baseParams: {filter: {}},
				entityStore: "User"
			}),

			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'displayName',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var style = record.get('avatarId') ? 'background-image: url(' + go.Jmap.thumbUrl(record.get("avatarId"), {
							w: 40,
							h: 40,
							zc: 1
						}) + ')"' : "";

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
				autoFill: true,
				totalDisplay: true
			}
		});

		this.supr().initComponent.call(this);
	}
});
