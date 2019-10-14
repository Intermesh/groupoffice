go.data.GroupingStore = Ext.extend(Ext.data.GroupingStore, {
	
	entityStore: null,
	
	autoDestroy: true,
	
	/**
	 * true when load or loaddata has been called.
	 * 
	 * @var bool
	 */
	loaded : false,
	
	loading : false,
	
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
		
		this.setup();
		
	},
	setup : go.data.Store.prototype.setup,	
	initEntityStore : go.data.Store.prototype.initEntityStore,	
	onChanges : go.data.Store.prototype.onChanges,	
	updateRecord : go.data.Store.prototype.updateRecord,
	destroy : go.data.Store.prototype.destroy
});

