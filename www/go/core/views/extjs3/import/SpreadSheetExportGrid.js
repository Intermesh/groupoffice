go.import.SpreadSheetExportGrid = Ext.extend(go.grid.GridPanel, {

	viewConfig: {
		forceFit: true,
		autoFill: true
	},

	hideHeaders: true,

	multiSelectToolbarEnabled: false,

	entityStore: null,

	extension: "csv",

	constructor: function (config) {

		config = config || {};

		var selModel = new Ext.grid.CheckboxSelectionModel();

		Ext.apply(config, {
			tbar: [{xtype: "selectallcheckbox"}],
			store: new go.data.Store({
				fields: ['name', 'label'],
				id: "name",
				remoteSort: false
			}),
			selModel: selModel,
			columns: [
				selModel,
				{
					id: 'name',
					header: t('Name'),
					sortable: false,
					dataIndex: 'label',
					hideable: false,
					draggable: false,
					menuDisabled: true
				}
			]
		});



		this.supr().constructor.call(this, config);

		go.Jmap.request({
			method: this.entityStore.entity.name + "/exportColumns",
			params: {
				extension: this.extension
			}
		}).then((columns) => {
			let records = [];

			//convert mapping object to array
			for(const name in columns) {
				if(!columns[name].label) {
					columns[name].label = name;
				}
				records.push(columns[name]);
			}

			config.store.loadData({records: records});
			config.store.sort('label', 'ASC');
		});
	},



	getSelection: function() {
		var selected = this.getSelectionModel().getSelections();
		return selected.map(function (r) {
			return r.id;
		});
	},

	loadSelection: function (ids) {

		if (!ids) {
			return;
		}

		var me = this, records = [], record;
		ids.forEach(function (id) {
			record = me.store.getById(id);
			if (record) {
				records.push(record);
			}
		});

		this.getSelectionModel().suspendEvents(false);
		this.getSelectionModel().selectRecords(records, false);
		this.getSelectionModel().resumeEvents();

	}
});
