go.modules.community.addressbook.customfield.ContactRelationGrid = Ext.extend(go.customfields.CustomFieldRelationGrid, {
	autoExpandColumn: 'name',
	stateId: 'contactRelationGrid',
	columns: [{
		header: t('ID'),
		width: dp(80),
		dataIndex: 'id',
		hidden:true,
		align: "right"
	},{
		id: 'name',
		header: t('Name'),
		dataIndex: 'name',
		sortable: true,
		width:300,
		renderer: function (v, fld, rec) {
			return v ? go.util.avatar(rec.data.name,rec.data.photoBlobId, "")+' '+v : "-";
		}
	}],
	store: {
		xtype: "gostore",
		fields: [
			'id',
			'name',
			'avatarId'
		],
		baseParams: {sort: [{property: "id", isAscending:false}]},
		entityStore: "Contact"
	}
});


Ext.reg('Contactrelationgrid', go.modules.community.addressbook.customfield.ContactRelationGrid);
