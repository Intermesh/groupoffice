Ext.define('go.modules.community.history.HistoryDetailPanel',{
	extend: Ext.Panel,

	entityId:null,
	entity:null,
	height: dp(400),

	title: t("History"),
	//
	/// Collapsilbe was turn off because of height recaculation issues in HtmlEditor
	//
	collapsible: true,
	animCollapse: false, //htmleditor doesn't work with animCollapse

	hideMode: "offsets", //required for htmleditor
	collapseFirst:false,
	layout:'fit',
	titleCollapse: true,
	stateId: "history-detail",
	initComponent: function () {

		this.store = new go.data.Store({
			fields: [{name:'createdAt',type:'date'},'id','action','changes','createdBy', 'description',{name: 'creator', type: "relation"}],
			baseParams: {sort: [{property: "createdAt", isAscending:false}]},
			entityStore: "LogEntry"
		});

		this.items = [
			this.logGrid = new go.modules.community.history.LogEntryGrid({
				//region:'center',
				store: this.store
			})
		];

		go.modules.community.history.HistoryDetailPanel.superclass.initComponent.call(this);
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