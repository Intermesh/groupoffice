Ext.define('go.modules.community.history.HistoryDetailPanel',{
	extend: go.modules.community.history.LogEntryGrid,

	entityId:null,
	entity:null,

	autoHeight: true,
	maxHeight: dp(300),//gridtrait implements this.

	title: t("History"),
	collapsible: true,

	stateId: "history-detail",


	onLoad: function (dv) {
		var id = dv.model_id ? dv.model_id : dv.currentId; //model_id is from old display panel
		var type = dv.entity || dv.model_name || dv.entityStore.entity.name;

		if(this.entityId === id) {
			return;
		}

		this.entityId = id;
		this.entity = type;

		this.store.setFilter('entity', {
			entity: this.entity,
			entityId: this.entityId
		});

		this.store.load();
	}
});