go.modules.community.addressbook.customfield.ContactRelationGrid = Ext.extend(go.customfields.CustomFieldRelationGrid, {
	autoExpandColumn: 'name',
	stateId: 'contactRelationGrid',

	initComponent: function() {
		const cols = [{
			header: t('ID'),
			width: dp(80),
			dataIndex: 'id',
			hidden:true,
			align: "right"
		},{
			id: 'name',
			header: t('Name'),
			dataIndex: 'name',
			width:300,
			renderer: function (v, fld, rec) {
				return v ? go.util.avatar(rec.data.name,rec.data.photoBlobId, "")+' '+v : "-";
			}
		}];
		this.store = new go.data.Store({
			fields: [
				'id',
				'name',
				'avatarId'
			],
			baseParams: {sort: [{property: "id", isAscending:false}]},
			entityStore: "Contact"
		});

		this.viewConfig = {
			emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
			totalDisplay: false

		};
		this.columns = cols;
		go.modules.community.addressbook.customfield.ContactRelationGrid.superclass.initComponent.call(this);
	},

	onLoad: function(dv) {
		if(this.fieldId && dv.currentId) {
			go.Db.store("Field").single(this.fieldId).then( (response) => {
				const dbName = response.databaseName;
				this.store.setFilter("conditions", {
					"operator": "AND",
					"conditions": [{
						[dbName]: dv.currentId
					}]
				}).load().then(() => {
					if(this.store.getTotalCount() === 0) {
						this.hide();
					} else if(!this.expandByDefault) {
						this.collapse();
					}
				});
			});
		}
	}
});


Ext.reg('contactrelationgrid', go.modules.community.addressbook.customfield.ContactRelationGrid);
