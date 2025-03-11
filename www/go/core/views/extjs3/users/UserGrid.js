go.users.UserGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function() {
		Ext.apply(this, {
			store: new go.data.Store({
				fields: [
					'id',
					'description',
					'name',
					'avatarId',
					'email'
				],
				baseParams: {filter: {entity:'User'}},
				entityStore: "Principal"
			}),

			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record) {
						var style = record.get('avatarId') ? 'background-image: url(' + go.Jmap.thumbUrl(record.get("avatarId"), {
							w: 40,
							h: 40,
							zc: 1
						}) + ')"' : "";

						return '<div class="user"><div class="avatar" style="' + style + '"></div>' +
							'<div class="wrap">' +
							'<div class="displayName">' + value + '</div>' +
							'<small class="username">' + Ext.util.Format.htmlEncode(record.get('description')) + '</small>' +
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
