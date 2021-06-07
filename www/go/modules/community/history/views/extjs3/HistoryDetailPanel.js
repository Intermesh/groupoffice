Ext.define('go.modules.community.history.HistoryDetailPanel',{
	extend: go.modules.community.history.LogEntryGrid,

	entityId:null,
	entity:null,

	autoHeight: true,

	title: t("History"),
	collapsible: true,

	stateId: "history-detail",
	initComponent: function () {

		go.modules.community.history.HistoryDetailPanel.superclass.initComponent.call(this);

		this.on("viewready" , ()  => {
			this.autoHeight = false;
			this.getView().scroller.setStyle("max-height", "300px");
			this.getView().scroller.setStyle("overflow-y", "auto");
		});
	},

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