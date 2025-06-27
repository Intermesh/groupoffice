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

		Ext.applyIf(this, go.data.FilterTrait);

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
			function( store, type, action, options, response) {

				if (response.isAbort) {
					//ignore aborts.
				} else if (response.isTimeout || response.status == 0) {

					console.warn("Connection timeout", response, options);
					if(document.visibilityState === "visible") {
						GO.errorDialog.show(t("The request timed out. The server took too long to respond. Please try again."));
					}
				} else if (response.type == "unsupportedSort") {

					// Handle invalid sort state which may happen when a (custom) column has been removed.

					console.warn("Clearing invalid sort state:", store.sortInfo);
					store.sortInfo = {};
					//caused infinite loop while developing
					if (!GO.debug) {
						store.reload();
					} else {
						GO.errorDialog.show(response.message);
					}

					//cancel further exception handling
					// return false;

				} else if (response.type == "unauthorized") {
					go.Router.login();
				} else
				{
					console.error(response);

					GO.errorDialog.show(response.message || t("Failed to send the request to the server. Please check your internet connection."));
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

		changed.forEach((id) => {
			if(this.proxy && this.proxy.watchRelations[entityStore.entity.name] && this.proxy.watchRelations[entityStore.entity.name].indexOf(id) > -1) {
				const o = go.util.clone(this.lastOptions);
				o.params = o.params || {};
				o.params.position = 0;
				o.add = false;

				if(this.lastOptions.params && this.lastOptions.params.position) {				
					o.params.limit = this.lastOptions.params.position + (this.lastOptions.limit || this.baseParams.limit || 20);
				}

				this.load(o).then(() => {
					this.fireEvent("changes", this);
				});
				return;
			}
		});
	},

	onChanges : function(entityStore, added, changed, destroyed) {

		// console.info(entityStore.entity.name, added, changed, destroyed);
		if(!this.loaded || this.loading || !this.lastOptions) {
			return;
		}		

		// if they are all empty then something is wrong
		// an exception occurred and we will also reload
		if(added.length || changed.length || !destroyed.length) {
			//we must reload because we don't know how to sort partial data.
			const o = go.util.clone(this.lastOptions);
			o.params = o.params || {};
			o.params.position = 0;
			o.add = false;
			o.keepScrollPosition = true;

			if(this.lastOptions.params && this.lastOptions.params.position) {				
				o.params.limit = this.lastOptions.params.position + (this.lastOptions.limit || this.baseParams.limit || 20);
			}

			this.load(o).then(() => {
				this.fireEvent("changes", this);
			})
			return;
		}

		destroyed.forEach((id) =>{

			const record = this.getById(id);
			if(record) {
				this.remove(record);
			}
		});

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
  }


};