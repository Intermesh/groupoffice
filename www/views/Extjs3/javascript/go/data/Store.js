
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
	
	loading : false,
	
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
		
		this.setup();
	
	},
	
	loadData : function(o, append){
		this.loading = true;		
		
		var ret = go.data.Store.superclass.loadData.call(this, o, append);				
		
		var me = this;
		setTimeout(function(){
			me.loading = false;
		}, 0);	
		
		return ret;
	},
	
	sort : function(fieldName, dir) {
		//Reload first page data set on sort
		if(this.lastOptions && this.lastOptions.params) {
			this.lastOptions.params.position = 0;
			this.lastOptions.add = false;
		}
		
		return go.data.Store.superclass.sort.call(this, fieldName, dir);
	},
	
	//created this because grouping store must share this.
	setup : function() {
		if(!this.baseParams) {
			this.baseParams = {};
		}
		
		if(!this.baseParams.filter) {
			this.baseParams.filter = {};
		}

		this.on('beforeload', function() {			
			this.loading = true;
		}, this)
		
		//set loaded to true on load() or loadData();
		this.on('load', function() {
			var me = this;
			setTimeout(function() {
				me.loaded = true;
				me.loading = false;
			}, 0);
		}, this)
		
		if(this.entityStore) {			
			this.initEntityStore();
		}
	},
	
	initEntityStore : function() {
		if(Ext.isString(this.entityStore)) {
			this.entityStore = go.Stores.get(this.entityStore);
			if(!this.entityStore) {
				throw "Invalid 'entityStore' property given to component"; 
			}
		}
		this.entityStore.on('changes',this.onChanges, this);		

		this.on('beforedestroy', function() {
			this.entityStore.un('changes', this.onChanges, this);
		}, this);
	},
	
	onChanges : function(entityStore, added, changed, destroyed) {
		
		if(!this.loaded || this.loading) {
			return;
		}		
		
		for(var i in added) {
			if(!this.updateRecord(added[i]) ){
				this.reload();
				return;
			}
		}
				
		for(var i in changed) {
			if(!this.updateRecord(changed[i]) ){
				this.reload();
				return;
			}
		}
		
		for(var i = 0, l = destroyed.length; i < l; i++) {
			var record = this.getById(destroyed[i]);
			if(record) {
				this.remove(record);
			}
		}

	},
	
	updateRecord : function(entity) {
		
		if(!this.data) {
			return false;
		}
		var record = this.getById(entity.id);
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
	}
	
//	onUpdate : function(store, record, operation) {
//		//debugger;
//		if(this.serverUpdate || this.loading) {
//			return;
//		}
//	
//		if(operation != Ext.data.Record.COMMIT) {
//			return;
//		}
//		
//		var p = {};
//		
//		key = record.phantom ? 'create' : 'update';
//		p[key] = {};
//		p[key][record.id] = record.data;
//		
//		store.fields.each(function(field){
//			if(field.submit === false) {
//				delete record.data[field.name];
//			}
//		});
//		
//		this.entityStore.set(p, function (options, success, response) {
//			
//			var saved = (record.phantom ? response.created : response.updated) || {};
//			if (saved[record.id]) {
//
//				//update client id with server id
//				if(record.phantom) {
////					record.id = record.data.id = response.created[record.id].id;
////					console.log(record.id);
////					record.phantom = false;
//						//remove phanto records as ext doesn't support changinhg record id.
//						this.remove(record);
//				}
//
//			} else
//			{
//				//something went wrong
//				var notSaved = (record.phantom ? response.notCreated : response.notUpdated) || {};
//				if (!notSaved[id]) {
//					notSaved[id] = {type: "unknown"};
//				}
//
//				switch (notSaved[id].type) {
//					case "forbidden":
//						Ext.MessageBox.alert(t("Access denied"), t("Sorry, you don't have permissions to update this item"));
//						break;
//
//					default:
//					
//						
//						Ext.MessageBox.alert(t("Error"), t("Sorry, something went wrong. Please try again."));
//						break;
//				}
//			}
//		}, this);
//		
//	}
});
