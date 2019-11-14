go.modules.community.notes.NoteGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function () {

		this.store = new go.data.Store({
			fields: [
				'id', 
				'name', 
				'content', 
				'excerpt', 
				{name: 'createdAt', type: 'date'}, 
				{name: 'modifiedAt', type: 'date'}, 
				{name: 'creator', type: "relation"},
				{name: 'modifier', type: "relation"},
				'permissionLevel'
			],
			entityStore: "Note"
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
					dataIndex: 'name'
				},
				{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{					
					xtype:"datecolumn",
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
					renderer: function(v) {
						return v ? v.displayName : "-";
					}
				},
				{	
					hidden: true,
					header: t('Modified by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifier',
					renderer: function(v) {
						return v ? v.displayName : "-";
					}
				}
			],
			viewConfig: {
				totalDisplay: true,
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>'
//				enableRowBody: true,
//				showPreview: true,
//				getRowClass: function (record, rowIndex, p, store) {
//					if (this.showPreview) {
//						p.body = '<p>' + record.data.excerpt + '</p>';
//						return 'x-grid3-row-expanded';
//					}
//					return 'x-grid3-row-collapsed';
//				}
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'notes-grid'
		});

		go.modules.community.notes.NoteGrid.superclass.initComponent.call(this);
	}
});

