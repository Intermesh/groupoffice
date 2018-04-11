go.data.Store = Ext.extend(Ext.data.JsonStore, {
	
	entityStore: null,
	
	autoDestroy: true,
	
	/**
	 * true when load or loaddata has been called.
	 * 
	 * @var bool
	 */
	loaded : false,
	
	remoteSort : true,
	
	
	constructor: function (config) {
		
		config = config || {};
		config.root = "records";
		
		go.data.Store.superclass.constructor.call(this, Ext.applyIf(config, {
			idProperty:  "id",
			paramNames: {
				start: 'position', // The parameter name which specifies the start row
				limit: 'limit', // The parameter name which specifies number of rows to return
				sort: 'sort', // The parameter name which specifies the column to sort on
				dir: 'dir'       // The parameter name which specifies the sort direction
			},
			proxy: new go.data.JmapProxy(config)
		}));
		go.flux.Dispatcher.register(this);
		
		//set loaded to true on load() or loadData();
		this.on('load', function() {
			this.loaded = true;
		}, this);
	},
	receive : function(action) {	
		
		if(!this.loaded) {
			return;
		}
		
		switch(action.type) {			
			
			//quick and dirty reload of the list when client did a set
			case this.entityStore.entity.name + "Updated":				
				
				//update data when entity store has new data
				for(var i=0,l=action.payload.list.length;i < l; i++) {
					var entity = action.payload.list[i];
					if(!this.updateRecord(entity) ){
						//todo this causes many reloads because every links panel reloads on a new link. 
						
						//HOw to determine if it needs to reload?
						// 
						
						this.reload();
						break;
					}
				};
			break;
			
			case this.entityStore.entity.name + "Destroyed":				
				
				//update data when entity store has new data
				for(var i=0,l=action.payload.list.length;i < l; i++) {
					var record = this.getById(action.payload.list[i]);
					
					if(record) {
						this.remove(record);
					}					
				};
			break;
		}

	},
	
	updateRecord : function(entity) {
		var record = this.getById(entity.id);
		if(!record) {
			return false;
		}
		
		record.beginEdit();
		
		this.fields.each(function(field) {
			record.set(field.name, entity[field.name]);
		});
		
		record.endEdit();
		
		//TODO: we don't want record.commit() to fire an update event because then we will going to do unesssary set calls to the server.
			
		record.commit();
		
		return true;
	},
	destroy : function() {	
		this.fireEvent('destroy', this);
		
		go.data.Store.superclass.destroy.call(this);
	},
	
	/**
	 * Load entities by id
	 * 
	 * @param {int[]} ids
	 * @returns {void}
	 */
	loadByIds : function(ids) {
		this.entityStore.get(ids, function (items) {
			this.loadData(items);
		}, this);
	}
});