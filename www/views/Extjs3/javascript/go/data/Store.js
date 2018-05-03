
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
		
		if(!this.baseParams) {
			this.baseParams = {};
		}
		
		if(!this.baseParams.filter) {
			this.baseParams.filter = {};
		}
		
		//set loaded to true on load() or loadData();
		this.on('load', function() {
			this.loaded = true;
		}, this)
		
		this.on('update', this.onUpdate, this);
		
		this.entityStore.on('changes', this.onChanges, this);
	},
	
	onChanges : function(entityStore, added, changed, destroyed) {		
		if(!this.loaded) {
			return;
		}
		
		if(added.length) {
			this.reload();
			return;
		}
		
		for(var i = 0, l = changed.length; i < l; i++) {
			if(!this.updateRecord(changed[i]) ){
				this.reload();
				break;
			}
		}
		
		for(var i = 0, l = destroyed.length; i < l; i++) {
			var record = this.getById(destroyed[i]);					
			if(record) {
				this.remove(record);
			}
		}

	},
	
	updateRecord : function(id) {
		

		var record = this.getById(id);
		if(!record) {
			return false;
		}
		
		var entity = this.entityStore.get([id])[0];
		
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
		
		this.entityStore.set(p, function(){
			//todo handle response
		});
		
	}
});
