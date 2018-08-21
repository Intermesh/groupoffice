go.modules.community.music.ArtistGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function () {

		// Use a Group Office store that is connected with an go.data.EntityStore for automatic updates.
		this.store = new go.data.Store({
			fields: [
				'id',
				'name',
				'photo', //This is a blob id. A download URL can be retreived with go.Jmap.downloadUrl(record.data.photo)

				{name: 'createdAt', type: 'date'},
				{name: 'modifiedAt', type: 'date'},

				// You can use any entity as a store data type. This will autmatically 
				// fetch the related entity by key.
				{name: 'creator', type: go.data.types.User, key: 'createdBy'},
				{name: 'modifier', type: go.data.types.User, key: 'modifiedBy'},

				// Every entity has permission levels. GO.permissionLevels.read, write, 
				// writeAndDelete and manage
				'permissionLevel'
			],

			// The connected entity store. When Arists are changed the store will 
			// update automatically
			entityStore: go.Stores.get("Artist")
		});

		Ext.apply(this, {

			columns: [
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: dp(40),
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					width: dp(75),
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {

						//Render an avatar for the artist.
						var style = record.data.photo ? 'background-image: url(' + go.Jmap.downloadUrl(record.data.photo) + ')"' : '';

						return '<div class="user">\
											<div class="avatar" style="' + style + '"></div>\
											<div class="wrap single">' + record.get('name') + '</div>\
										</div>';
					}
				},
				{
					xtype: "datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{
					xtype: "datecolumn",
					hidden: false,
					id: 'modifiedAt',
					header: t('Modified at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifiedAt'
				},
				{
					hidden: true,
					header: t('Created by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'creator',
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				},
				{
					hidden: true,
					header: t('Modified by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifier',
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				}
			],

			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>'
			},

			autoExpandColumn: 'name',

			// Change to true to remember grid state
			stateful: false,
			stateId: 'music-arist-grid'
		});

		go.modules.community.music.ArtistGrid.superclass.initComponent.call(this);
	}
});
