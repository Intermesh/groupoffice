go.data.GroupingStore = Ext.extend(Ext.data.GroupingStore, {
	
	entityStore: null,
	
	autoDestroy: true,
	
	constructor: function (config) {
		
		config = config || {};
		config.root = "records";
		
		go.data.GroupingStore.superclass.constructor.call(this, Ext.apply(config, {
			paramNames: {
				start: 'position', // The parameter name which specifies the start row
				limit: 'limit', // The parameter name which specifies number of rows to return
				sort: 'sort', // The parameter name which specifies the column to sort on
				dir: 'dir'       // The parameter name which specifies the sort direction
			},
			proxy: new go.data.JmapProxy(config),
			reader: new Ext.data.JsonReader(config)
		}));
		
		go.flux.Dispatcher.register(this);
		
	},
	receive : go.data.Store.prototype.receive,	
	updateRecord : go.data.Store.prototype.updateRecord,
	destroy : go.data.Store.prototype.destroy
});

