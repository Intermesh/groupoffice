go.data.GroupingStore = Ext.extend(Ext.data.GroupingStore, {
	
	autoDestroy: true,
	
	constructor: function (config) {
		
		config = config || {};
		config.root = "records";

		Ext.applyIf(this, go.data.StoreTrait);
		
		this.addCustomFields(config);
		
		go.data.GroupingStore.superclass.constructor.call(this, Ext.apply(config, {
			paramNames: {
				start: 'position', // The parameter name which specifies the start row
				limit: 'limit', // The parameter name which specifies number of rows to return
				sort: 'sort', // The parameter name which specifies the column to sort on
				dir: 'dir'       // The parameter name which specifies the sort direction
			},
			proxy: new go.data.EntityStoreProxy(config),
			reader: new Ext.data.JsonReader(config)
		}));
		
		this.setup();
		
	},

	loadData : function(o, append){
		var old = this.loading;
		this.loading = true;
			
		if(this.proxy instanceof go.data.EntityStoreProxy) {
			this.proxy.preFetchEntities(o.records, function() {
				go.data.GroupingStore.superclass.loadData.call(this, o, append);	
				this.loading = old;		
			}, this);
		} else
		{
			go.data.GroupingStore.superclass.loadData.call(this, o, append);	
			this.loading = old;
		}
	},

	sort : function(fieldName, dir) {
		//Reload first page data set on sort
		if(this.lastOptions && this.lastOptions.params) {
			this.lastOptions.params.position = 0;
			this.lastOptions.add = false;
		}
		
		return go.data.GroupingStore.superclass.sort.call(this, fieldName, dir);
	},

	destroy : function() {	
		this.fireEvent('beforedestroy', this);
		
		go.data.GroupingStore.superclass.destroy.call(this);
		
		this.fireEvent('destroy', this);
	}
});
