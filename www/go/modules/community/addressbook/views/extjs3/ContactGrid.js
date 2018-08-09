go.modules.community.addressbook.ContactGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function () {

		this.store = new go.data.Store({
			fields: [
				'id',
				'name',
				{name: 'createdAt', type: 'date'},
				{name: 'modifiedAt', type: 'date'},
				{name: 'creator', type: go.data.types.User, key: 'createdBy'},
				{name: 'modifier', type: go.data.types.User, key: 'modifiedBy'},
				'permissionLevel',
				'photoBlobId',
				"isOrganization",
				"organizations"
			],
			entityStore: go.Stores.get("Contact")
		});
		
		var grid = this;

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
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {


						var style = "", cls = "";

						if (record.data.photoBlobId) {
							style = 'background-image: url(' + go.Jmap.downloadUrl(record.data.photoBlobId) + ')"';
						} else
						{
							cls = record.data.isOrganization ? "group" : "";
						}

						return '<div class="user"><div class="avatar ' + cls + '" style="' + style + '"></div>' +
										'<div class="wrap single">' + record.get('name') + '</div>' +
										'</div>';
					}
				},
				{
					id: 'organizations',
					header: t('Organizations'),
					sortable: false,
					dataIndex: "organizations",
					width: dp(300),
					renderer: function (v, meta, record) {
						var orgStr = t("Loading...");

						//will be processed after storeload by onStoreLoad
						var contactOrganizations = record.get('organizations');
						if (!contactOrganizations.length) {
							return "-";
						}

						var ids = [];
						contactOrganizations.forEach(function (o) {
							ids.push(o.organizationContactId);
						});



						var organizations = go.Stores.get('Contact').get(ids, function (entities, async) {
							if(async) {
								grid.getView().refresh();
							}							
						}, this);


						orgStr = "";
						organizations.forEach(function (org) {
							if (orgStr != "") {
								orgStr += ", "
							}
							orgStr += org.name;
						});


						return orgStr;

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
					dataIndex: 'modifiedAt',
					hidden: true
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
//			stateful: true,
//			stateId: 'contact-grid'
		});

		go.modules.community.addressbook.ContactGrid.superclass.initComponent.call(this);
	}
});

