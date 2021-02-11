go.data.StoreTrait = {
  	
	entityStore: null,

  /**
	 * true when load or loaddata has been called.
	 * 
	 * @var bool
	 */
	loaded : false,
	
	loading : false,

  	//created this because grouping store must share this.
	setup : function() {
		if(!this.baseParams) {
			this.baseParams = {};
		}
		
		// if(!this.baseParams.filter) {
		// 	this.baseParams.filter = {};
		// }
		
		if(this.entityStore) {			
			this.initEntityStore();
    }
    
    this.on("beforeload", function() {
      this.loading = true;
    }, this);
		
		// It's important to realize that if other components register an on load 
		// listener this.loading will be false. Because this is the first event listener.
		// ScrollLoader depends on this!
		this.on("load", function() {
			this.loading = false;
			this.loaded = true;
    }, this);

		this.on('exception',
			function( store, type, action, options, response){

				if(response.isAbort) {
					//ignore aborts.
				} else if(response.isTimeout){
					console.error(response);

					GO.errorDialog.show(t("The request timed out. The server took too long to respond. Please try again."));
				}else
				{
					console.error(response);

					GO.errorDialog.show(t("Failed to send the request to the server. Please check your internet connection."));
				}
			}
			,this);
    
		this.initFilters();

		this.trackRemoved();
	},	

	trackRemoved : function() {
		this.removed = [];
		this.on("remove", function(store, record) {
			this.removed.push(record);
		}, this);
		this.on("load", function() {
			this.removed = [];
		}, this);
	},
	
	initFilters : function() {
		//JMAP remote filters. Used by setFilter()
		if(this.filters) {
			for(var name in this.filters) {
				this.setFilter(name, this.filters[name]);
			}
		} else {
			this.filters = {};
		}
	},
  
  addCustomFields : function(config) {
		if(!config.entityStore) {
			return;
		}
		
		var entity; 
		if(Ext.isString(config.entityStore)) {
			entity = go.Entities.get(config.entityStore);
			if(!entity) {
				throw "Entity '" + config.entityStore + "' is not defined!";
			}
		} else
		{
			entity = config.entityStore.entity;
		}
		
		config.fields = config.fields.concat(go.customfields.CustomFields.getFieldDefinitions(entity.name));	
	},

  initEntityStore : function() {
		if(Ext.isString(this.entityStore)) {
			this.entityStore = go.Db.store(this.entityStore);
			if(!this.entityStore) {
				throw "Invalid 'entityStore' property given to component"; 
			}
		}
		this.entityStore.on('changes',this.onChanges, this);		
		
		//reload if something goes wrong in the entity store.
		this.entityStore.on("error", this.onError, this);

		this.on('beforedestroy', function() {
			this.entityStore.un('changes', this.onChanges, this);
			this.entityStore.un("error", this.onError, this);
			this.unwatchRelations();
		}, this);

		this.watchRelations();
	},

	/**
	 * Registers a listener for changes on entity stores when fields with type = relation is used.
	 */
	watchRelations : function() {

		this.proxy.getEntityFields().forEach(function(field) {
			var relation =  this.entityStore.entity.findRelation(field.name);

			if(!relation) {
				throw "'" + field.name + "' is not a relation of '" + this.entityStore.entity.name + "'";
			}
			go.Db.store(relation.store).on("changes", this.onRelationChanges, this);
		}, this);
	},

	unwatchRelations : function() {
		this.proxy.getEntityFields().forEach(function(field) {
			var relation = this.entityStore.entity.findRelation(field.name);

			go.Db.store(relation.store).un("changes", this.onRelationChanges, this);
		}, this);
	},
	
	onError : function() {
		if(this.loaded) {
			this.reload();
		}
	},

	/**
	 * Reloads grid when a relation that is present in the grid has changed
	 * 
	 * @param {*} entityStore 
	 * @param {*} added 
	 * @param {*} changed 
	 * @param {*} destroyed 
	 */
	onRelationChanges : function(entityStore, added, changed, destroyed) {
		if(!this.proxy.watchRelations[entityStore.entity.name] || !this.lastOptions) {
			return;
		}

		for(var id in changed) {
			if(this.proxy.watchRelations[entityStore.entity.name].indexOf(changed[id].id) > -1) {
				var o = go.util.clone(this.lastOptions);
				o.params = o.params || {};
				o.params.position = 0;
				o.add = false;

				if(this.lastOptions.params && this.lastOptions.params.position) {				
					o.params.limit = this.lastOptions.params.position + (this.lastOptions.limit || this.baseParams.limit || 20);
				}

				var me = this;
				this.load(o).then(function() {
					me.fireEvent("changes", me);
				});
				return;
			}
		}		
	},

	onChanges : function(entityStore, added, changed, destroyed) {
		if(!this.loaded || this.loading || !this.lastOptions) {
			return;
		}		

		if(Object.keys(added).length || Object.keys(changed).length) {
			//we must reload because we don't know how to sort partial data.
			var o = go.util.clone(this.lastOptions);
			o.params = o.params || {};
			o.params.position = 0;
			o.add = false;

			if(this.lastOptions.params && this.lastOptions.params.position) {				
				o.params.limit = this.lastOptions.params.position + (this.lastOptions.limit || this.baseParams.limit || 20);
			}

			var me = this;
			this.load(o).then(function() {
				me.fireEvent("changes", me);
			})
			return;
		}
		
		for(var i = 0, l = destroyed.length; i < l; i++) {
			var record = this.getById(destroyed[i]);
			if(record) {
				this.remove(record);
			}
		}

		this.fireEvent("changes", this);
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
	
	removeById : function(id) {
		this.remove(this.getById(id));
  },

	/**
	 * Same as set filter but keeps existing filter values if set
	 *
	 * @param cmpId
	 * @param filter
	 * @returns {go.data.StoreTrait}
	 */
	patchFilter : function(cmpId, filter) {
		var f = this.getFilter(cmpId);
		if(!f) {
			f = {};
		}
		return this.setFilter(cmpId, Ext.apply(f, filter));
	},
  
  /**
	 * Set a filter object for a component
	 * 
	 * @param {string} cmpId
	 * @param {object} filter if null is given it's removed
	 * @returns {this}
	 */
	setFilter : function(cmpId, filter) {
		if(filter === null) {
			delete this.filters[cmpId];
		} else
		{
			this.filters[cmpId] = filter;
		}		

		var conditions = [];
		for(var cmpId in this.filters) {
			conditions.push(this.filters[cmpId]);
		}

		switch(conditions.length) {
			case 0:
				delete this.baseParams.filter;
				break;
			case 1:
				this.baseParams.filter = conditions[0];
				break;
			default:
				this.baseParams.filter = {
					operator: "AND",
					conditions: conditions
				};
				break;
		}
		
		return this;
	},
	
	getFilter : function(name) {
		return this.filters[name];
	}
};