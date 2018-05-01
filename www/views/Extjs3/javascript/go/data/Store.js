
/**
 * 
 * 
 * //Inserting records will trigger server update too:
 * var store = this.noteGrid.store;
						var myRecordDef = Ext.data.Record.create(store.fields);

						store.insert(0, new myRecordDef({
							name: "New",
							content: "Testing",
							noteBookId: this.addNoteBookId
						}));
						
						store.commitChanges();
 */
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
		}, this)
		
		this.on('update', this.onUpdate, this);
	},
	receive : function(action) {	
		
		if(!this.loaded) {
			return;
		}
		
		switch(action.type) {			
			
			case this.entityStore.entity.name + "Created":				
			case this.entityStore.entity.name + "Updated":				
				
				//update data when entity store has new data
				for(var id in action.payload.list) {
					if(!this.updateRecord(id, action.payload.list[id]) ){
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
	
	updateRecord : function(id, entity) {
		

		var record = this.getById(id);
		if(!record) {
			return false;
		}
		
//		if(record.isModified()) {
//			alert("Someone modified your record!");
//		}
		
		
		this.serverUpdate = true;
		record.beginEdit();
		this.fields.each(function(field) {
			record.set(field.name, entity[field.name]);
		});
		
		record.id = entity.id;
		
		
		record.endEdit();
		record.commit();
		
		this.serverUpdate = false;
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
	},
	
	onUpdate : function(store, record, operation) {
		
		if(this.serverUpdate) {
			return;
		}
		
		
		if(operation != Ext.data.Record.COMMIT) {
			return;
		}
		
		var p = {};
		
		key = record.phantom ? 'create' : 'update';
		p[key] = {};
		p[key][record.id] = record.data;
		
		this.entityStore.set(p);
		
	}
});
