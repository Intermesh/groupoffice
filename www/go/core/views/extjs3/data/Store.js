
/* global go, Ext */

/**
 * Store for displaying data in components like a grid, combo, dataview etc.
 *
 * @example
 *
 * this.store = new go.data.Store({
			fields: [
				'id',
				'name',
				'firstName',
				'lastName',
				{name: 'createdAt', type: 'date'},
				{name: 'modifiedAt', type: 'date'},
				{name: 'creator', type: "relation"},
				{name: 'modifier', type: "relation"},
				{name: 'addressbook', type: "relation"},
				'starred',
				'permissionLevel',
				'photoBlobId',
				"isOrganization",
				"emailAddresses",
				"phoneNumbers",
				"dates",
				"streetAddresses",
				{name: 'organizations', type: "relation"},
				"jobTitle",
				"debtorNumber",
				"registrationNumber",
				"IBAN",
				"vatNo",
				"color"
			],
			sortInfo :{field: "name", direction: "ASC"},
			entityStore: "Contact",
			filters: {
				default: {
						permissionLevel: go.permissionLevels.write
				}
			}

		});
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

	/**
	 * WIll autodestroy if the component it belongs too is destroyed
	 *
	 * WARNING: the component is responsible for handling this, If you have a custom component you must destroy it yourself.
	 */
	autoDestroy: true,
	
	autoSave: false,
	
	remoteSort : true,
	
	enableCustomFields: true,
	
	
	constructor: function (config) {
		
		config = config || {};
		config.root = "records";

		if(!config.fields) {
			throw "'fields' are required for a Store";
		}

		Ext.applyIf(this, go.data.StoreTrait);
		
		if(!Ext.isDefined(config.enableCustomFields) || config.enableCustomFields) {
			this.addCustomFields(config);
		}

		go.data.Store.superclass.constructor.call(this, Ext.applyIf(config, {
			idProperty:  "id",
			paramNames: {
				start: 'position', // The parameter name which specifies the start row
				limit: 'limit', // The parameter name which specifies number of rows to return
				sort: 'sort', // The parameter name which specifies the column to sort on
				dir: 'dir'       // The parameter name which specifies the sort direction
			},
			proxy: config.entityStore ? 
				new go.data.EntityStoreProxy({entityStore: config.entityStore, fields: config.fields, store: this}) :
				new go.data.JmapProxy({method: config.method, fields: config.fields, store: this})
		}));        
		
		this.setup();		
	},
	
	loadData : function(o, append){
		var old = this.loading;
		this.loading = true;
			
		if(o.records) {
			this.proxy.preFetchEntities(o.records, function() {
				go.data.Store.superclass.loadData.call(this, o, append);	
				this.loading = old;		
			}, this);
		} else
		{
			go.data.Store.superclass.loadData.call(this, o, append);	
			this.loading = old;
		}
	},

	loadRecords: function(o, options, success){
		if(!this.entityStore) {
			go.data.Store.superclass.loadRecords.call(this, o, options, success);
			return;
		}

		go.Translate.runFrom(this.entityStore.entity.package, this.entityStore.entity.module, () => {
			go.data.Store.superclass.loadRecords.call(this, o, options, success);
		});
	},
	
	sort : function(fieldName, dir) {
		//Reload first page data set on sort
		if(this.lastOptions && this.lastOptions.params) {
			this.lastOptions.params.position = 0;
			this.lastOptions.add = false;
		}
		
		return go.data.Store.superclass.sort.call(this, fieldName, dir);
	},
	
	destroy : function() {	
		this.fireEvent('beforedestroy', this);

		go.data.Store.superclass.destroy.call(this);
		
		this.fireEvent('destroy', this);
	},
	
	//override Extjs writer save for entityStore
	save: function(cb) {
		var queue = {},
			 rs = this.getModifiedRecords(),
			 hasChanges = false;
		if(this.removed.length){
			hasChanges = true;
			queue.destroy = [];
			for(var r,i = 0; r = this.removed[i]; i++){
				queue.destroy.push(r.id);
			}
		}
		if(rs.length){
			hasChanges = true;
			queue.create = {};
			queue.update = {};
			for(var r,i = 0;r = rs[i]; i++){
				if(!r.isValid()) {
					continue;
				}
				var change = {}, attr;
				for(attr in r.modified) {
					change[attr] = r.data[attr];
				}
				queue[r.phantom?'create':'update'][r.id] = change;
			}
		}
		if(!hasChanges || this.fireEvent('beforesave', this, queue) === false) {
			return Promise.resolve();
		}
		//console.log(queue);
		return this.entityStore.set(queue, function(options, success, queue){
			this.commitChanges();
			if(cb) {
				cb(success);
			}
		},this);	

	},

	load: function(o) {
		o = o || {};
		
		var origCallback = o.callback, origScope = o.scope || this, me = this;

		return new Promise(function(resolve, reject) {
			o.callback = function(records, options, success) {
				if(origCallback) {
					origCallback.call(origScope, records, options, success);
				}

				if(success) {
					resolve(records);
				} else{
					if(options.error.message == "unsupportedSort") {
						return; //ignore.
					}
					//hack to pass error message from EntityStoreProxy to load callback
					reject(options.error);
				}				
			};

			if(go.data.Store.superclass.load.call(me, o) === false) {
				//beforeload handlers cancelled
				//reject();
			}
			
		});
	}	

});

Ext.reg('gostore', go.data.Store);