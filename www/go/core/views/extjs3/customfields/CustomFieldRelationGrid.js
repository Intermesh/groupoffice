go.customfields.CustomFieldRelationGrid = Ext.extend(go.grid.GridPanel, {
	layout: "fit",
	collapsible: true,

	entityId:null,
	entity:null,
	fieldId: null,
	currentId: null,

	autoHeight: true,
	maxHeight: dp(300),//gridtrait implements this.

	title: "",
	hidden: true,

	store: null,
	viewConfig: {
		emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
		totalDisplay: false
	},
	columns: [],

	initComponent: function() {
		this.addListener('cellclick', this.onCellClick, this);
		this.addListener('celldblclick', this.onCellClick, this);
		go.customfields.CustomFieldRelationGrid.superclass.initComponent.call(this);
	},


	onLoad: function(dv) {
		if(this.fieldId && (dv.currentId || dv.model_id)) {
			const tgtId = dv.currentId || dv.model_id;
			go.Db.store("Field").single(this.fieldId).then( (response) => {
				const dbName = response.databaseName;
				this.store.setFilter("conditions", {
					"operator": "AND",
					"conditions": [{
						[dbName]: tgtId
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
	},

	onCellClick: function(cb, rowIdx, colIdx, evt) {
		evt.preventDefault();
		const record = this.store.getAt(rowIdx),
			win = new go.links.LinkDetailWindow({
			entity: this.store.entityStore.entity.name
		});
		win.load(record.id);
	}



});

Ext.reg('customfieldrelationgrid', go.customfields.CustomFieldRelationGrid);
