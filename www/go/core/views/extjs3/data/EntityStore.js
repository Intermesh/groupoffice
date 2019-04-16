
/* global Ext, go, localforage */

/**
 * Entity store
 * 
 * The entity store is a single source of truth for all the entities.
 * It's kept up to date with flux when go.Jmap.request() calls are made.
 * Then it fires a "changes" event that other view stores or components can
 * subscribe to. The changes event will fire at the end of an event cycle.
 * It will pass:
 * 
 * store: the entity store
 * added: int[]|string[] array of ids's
 * changed: int[]|string[] array of ids's
 * detroyed: int[]|string[] array of ids's
 * 
 */
go.data.EntityStore = Ext.extend(go.flux.Store, {

	state: null,
	
	data : null,
	
	notFound: null,

	entity: null,	
	
	changes : null,
	
	/**
	 * changedIds is set by a /changes request. If this item is added because of 
	 * a changes request we must fire a changes event. Not if we're loading by request.
	 */
	changedIds : null,
	
	// Set to true when all dasta has been fetched from server
	isComplete : false,
	
	constructor : function(config) {
		go.data.EntityStore.superclass.constructor.call(this, config);
		
		this.addEvents({changes:true, error:true});
		
		this.notFound = [];
		this.data = {};
		this.state = null;
		
		this.initChanges();		
	},
	
	initState : function(cb) {
		
		var me = this;
		
		if(me.initialized) {
			if(cb) {
				cb.call(me);
				Promise.resolve();
				return;
			}
		}
		
		me.stateStore = localforage.createInstance({
			name: "groupoffice",
			storeName: me.entity.name + "-entities"
		});

		me.metaStore = localforage.createInstance({
			name: "groupoffice",
			storeName: me.entity.name + "-meta"
		});
		
		return Promise.all([
			me.metaStore.getItem('notFound', function(v) {
				me.notFound = v || [];
				return v;
			}),
			me.metaStore.getItem('state', function(v) {
				me.state = v;
				return v;
			}),
			me.metaStore.getItem('isComplete', function(v) {
				me.isComplete = v;
				return v;
			}),
			me.metaStore.getItem('apiVersion', function(v) {
				me.apiVersion = v;
				return v;
			}),
			me.metaStore.getItem('apiUser', function(v) {
				me.apiUser = v;
				return v;
			})
		]).then(function() {

			me.initialized = true;

			if(!me.state) {
				return Promise.all([
					me.metaStore.setItem("apiVersion", go.User.apiVersion),
					me.metaStore.setItem("apiUser", go.User.username)
				]);
			} else if(me.apiVersion !== go.User.apiVersion || me.apiUser !== go.User.username) {
				console.warn("API version or username mismatch", me.apiVersion, go.User.apiVersion, me.apiUser, go.User.username);
				return me.clearState();
			} else
			{
				return true;
			}
		}).then(function() {
			if(cb) {
				cb.call(me);
			}
			return true;
		});
		
	},
	
	initChanges : function() {
		this.changes = {
			added: {},
			changed: {},
			destroyed: []
		};
		
		this.changedIds = [];
	},
	
	clearState : function() {
		console.warn("State cleared for " + this.entity.name);
		this.state = null;
		this.data = {};	
		
		this.isComplete = false;

		return Promise.all([
			this.metaStore.clear(),
			this.stateStore.clear(),		
			this.metaStore.setItem("apiVersion", go.User.apiVersion),
			this.metaStore.setItem("apiUser", go.User.username)
		]);
		
	},
	
	_add : function(entity, fireChanges) {
		
		if(!entity.id) {
			console.error(entity);
			throw "Entity doesn't have an 'id' property";
		}		
		
		//ChangedIds is set by a /changes request. If this item is added because of 
		// a changes request we must fire a changes event. Not if we're loading by request.
		if(!Ext.isDefined(fireChanges)) {
			fireChanges = this.changedIds.indexOf(entity.id) > -1;
		}
		
		if(this.data[entity.id]) {			
			if(fireChanges) {
				this.changes.changed[entity.id] = entity;
			}
			Ext.apply(this.data[entity.id], entity);
		} else
		{
			if(fireChanges) {
				this.changes.added[entity.id] = entity;
			}
			this.data[entity.id] = entity;
		}
		
		
		//remove from not found.
		var i = this.notFound.indexOf(entity.id);
		if(i > -1) {
			this.notFound.splice(i, 1);
			this.metaStore.setItem("notFound", this.notFound);
		}
		
		//Localforage requires ID to be string
		this.stateStore.setItem(entity.id + "", entity);
		
		if(fireChanges) {
			this._fireChanges();
		}
	},
	
	_destroy : function(id) {
		delete this.data[id];
		this.changes.destroyed.push(id);
		this.stateStore.removeItem(id + "");
		this._fireChanges();
	},
	
	_fireChanges : function() {
		var me = this;

		if (me.timeout) {
			clearTimeout(me.timeout);
		}
		
		//delay fireevent one event loop cycle
		me.timeout = setTimeout(function () {				
			// console.log('changes', me.entity.name, me.changes.added, me.changes.changed, me.changes.destroyed);
			me.fireEvent('changes', me, me.changes.added, me.changes.changed, me.changes.destroyed);			
			me.initChanges();
			me.timeout = null;
		}, 0);
	},
	
	setState : function(state, cb, scope) {
		this.state = state;
		
		if(!state) {
			this.clearState();
		}
		
		scope = scope || this;
		this.metaStore.setItem("state", state, function() {
			if(cb) {
				cb.call(scope);
			}
		});	
	},
	
	
	getState: function(cb) {
		var me = this;
		this.initState(function(){
			cb.call(me, me.state);
		});
	},


	receive: function (action) {		
		this.getState(function(state){
			switch (action.type) {
				case this.entity.name + "/get":

					// If no items are available, don't continue
					if(!action.payload.list){
						return;
					}

					//add data from get response
					for(var i = 0,l = action.payload.list.length;i < l; i++) {
						this._add(action.payload.list[i]);
					};

					this.setState(action.payload.state);
					break;

				case this.entity.name + "/query":
//					console.log("Query state: " + state + " - " + action.payload.state);
					//if a list call was made then fetch updates if state mismatch
					if (state && action.payload.state !== state) {
						this.getUpdates();
						this.setState(action.payload.state);
					}
					break;

				case this.entity.name + "/set":
					//update state from set we initiated
					this.setState(action.payload.newState);
					break;
			}
		});
	},

	getUpdates: function (cb, scope) {		
		
		this.getState(function(state){
			
//			console.log("Get updates for state: " + state);
		
			if(!state) {
				console.info("No state yet so won't fetch updates");
				if(cb) {
					cb.call(scope || this, this, false);
				}
				return;
			}
			
			var clientCallId = go.Jmap.request({
				method: this.entity.name + "/changes",
				params: {
					sinceState: this.state
				},
				callback: function(options, success, response) {
					
					//keep this array for the Foo/get response to check if an event must be fired.
					//We will only fire added and changed in _add if this came from a /changes 
					//request and not when we are loading data ourselves.
					this.changedIds = response.changed || [];
					
					if(response.removed) {
						for(var i = 0, l = response.removed.length; i < l; i++) {
							this._destroy(response.removed[i]);
						}
					}
					if(success) {
						this.setState(response.newState, function(){
							if(response.hasMoreChanges) {
								this.getUpdates(cb, scope);
							} else
							{
								if(cb) {
									cb.call(scope || this, this, true);
								}
							}
						}, this);

					} else
					{					
						this.clearState();
						if(cb) {
							cb.call(scope || this, this, false);
						}
					}

				},
				scope: this
			});

			go.Jmap.request({
				method: this.entity.name + "/get",
				params: {
					"#ids": {
						resultOf: clientCallId,
						path: '/changed'
					}
				},
				callback: function(options, success, response) {					

				},
				scope: this
			});
		});

	},
	
	/**
	 * Get all entities
	 * 
	 * @param {function} cb
	 * @param {object} scope
	 * @returns {void}
	 */
	all : function(cb, scope) {
		this.initState(function() {
			if(this.isComplete) {
				this.getUpdates(function(store, success) {
					if(!success) {
						this.isComplete = false;
						this.all(cb, scope);
						return;
					}
					var me = this;
					
					this.stateStore.keys().then(function(keys){
						return Promise.all(keys.map(function(key){
							return me.stateStore.getItem(key).then(function(entity) {
								me.data[entity.id] = entity;
							});
						}))
					}).then(function() {
						cb.call(scope, true, me.data);
					});				
				
				});
			} else
			{
				go.Jmap.request({
					method: this.entity.name + "/get",
					callback: function (options, success, response) {
						if(!success) {
							cb.call(scope, false, null);
							return;
						}

						this.metaStore.setItem('isComplete', true);
						this.isComplete = true;
						
						cb.call(scope, true, response.list);
					},
					scope: this
				});
			}
		});
	},
	
	
	_getAlreadyLoaded : function(ids, entities, unknownIds) {		
		for (var i = 0, l = ids.length; i < l; i++) {
			var id = ids[i];
			if(!id) {
				throw "Empty ID passed to EntityStore.get()";
			}
			if(this.data[id]) {
				entities.push(Object.assign({},this.data[id]));
			} else if(this.notFound.indexOf(id) > -1) {
				//entities.push(null);
				//notFoundIds.push(id);
				console.warn("Not fetching " + this.entity.name + " (" + id + ") because it was not found in an earlier attempt");
			} else
			{
				unknownIds.push(id);
			}			
		}
	},
	
	_getFromBrowserStorage : function(unknownIds) {
		var me = this;
		return me.initState().then(function() {
			
			var itemPromises = [];
			unknownIds.forEach(function(id) { 
				itemPromises.push(
					me.stateStore.getItem(id + "").then(function(entity) {		
						if(!entity) {
							return null;
						}
						unknownIds = unknownIds.filter(function(id){
							return id != entity.id;
						});

						me.data[entity.id] = entity;						
						return entity;
					})
				);
			});
			
			return Promise.all(itemPromises).then(function() {
				return unknownIds;
			});
		});

	},
	
	_getFromServer : function(unknownIds) {
		
		var me = this;
		
		return new Promise(function(resolve, reject) {
			go.Jmap.request({
				method: me.entity.name + "/get",
				params: {
					ids: unknownIds
				},
				callback: function (options, success, response) {
					if(!success) {
						reject();
						return;
					}

					if(!go.util.empty(response.notFound)) {
						me.notFound = me.notFound.concat(response.notFound);
						me.metaStore.setItem("notfound", me.notFound);								
						console.warn("Item not found", response);						
					}

					resolve();
				},
				scope: me
			});
		});
	},

	/**
	 * Get entities
	 * 
	 * @link https://jmap.io/spec-core.html#/get
	 * @param {string[]|int[]} ids
	 * @param {function} cb Callback function that is called with entities[] and notFoundIds[] 
	 * @param {object} scope
	 * @returns void
	 */
	get: function (ids, cb, scope) {
		
		var me = this;	
		
		return new Promise(function(resolve, reject) {
		
			if(go.util.empty(ids)) {
				if(cb) {		
					cb.call(scope || me, [], me);			
				}
				resolve([], []);
				return;
			}

			if(!Ext.isArray(ids)) {
				throw "ids must be an array";
			}

			var entities = [], unknownIds = [];
			
			var doCallback = function() {
				var notFoundIds = me.notFound.filter(function(i) {			
					return ids.indexOf(i) > -1;	
				});

				if(cb) {				
					cb.call(scope || me, entities, notFoundIds);				
				}
				resolve(entities, notFoundIds);
			};
			
			me._getAlreadyLoaded(ids, entities, unknownIds);			

			if (!unknownIds.length) {
				doCallback();
				return;
			}
			
			me._getFromBrowserStorage(unknownIds).then(function(unknownIds) {
				if(!unknownIds.length) {
					return me.get(ids).then(function(e, nf) {
						entities = e;
						notFoundIds = nf;
					});
				} else
				{
					return me._getFromServer(unknownIds).then(function() {
						return me.get(ids).then(function(e, nf) {
							entities = e;
							notFoundIds = nf;
						});
					});
				}
			}).catch(function(err) {
				reject(err);					
			}).then(function() {
				doCallback();
			});
			
		});
		
	},
	
	findBy : function(fn, scope, startIndex) {
		startIndex = startIndex || 0;
		var data = Object.values(this.data);
		for(var i = startIndex, l = data.length; i < l; i++) {
			if(fn.call(scope || this, data[i])) {
				return data[i];
			}
		}
	},

	/**
	 * Create or update entities
	 * 
	 * 
	 * ```
	 * var update = {};
		update[this.moreMenu.record.id] = {enabled: !this.moreMenu.record.data.enabled};
				
	 * go.Db.store("Foo").set({
	 *		create: {"client-id-1" : {name: "test"}},
	 *		update: update,
	 *		destroy: [2]
	 *	}, function(options, success, response){}, this);
	 * 
	 * ```
	 * 
	 * Destroy:
	 * 
	 * ```
	 * this.entityStore.set({destroy: [1,2]}, function (options, success, response) {
			if (response.destroyed) {
				this.hide();
			}
		}, this);
		```
	 * 
	 * @param {object} params	 
	 * @param {function} cb A function called with success, values, response, options
	 * @param {object} scope
	 * 	 
	 * @returns {string} Client request ID
	 * 
	 * @link http://jmap.io/spec-core.html#/set
	 */
	set: function (params, cb, scope) {
		
		//params.ifInState = this.state;
		
		if(params.create && Ext.isArray(params.create)) {
			throw "'create' must be an object with client ID's as key. Not an array.";
		}
		
		if(params.update && Ext.isArray(params.update)) {
			throw "'update' must be an object with client ID's as key. Not an array.";
		}
		
		if(params.destroy && !Ext.isArray(params.destroy)) 
		{
			throw "'destroy' must be an array.";
		}

		return go.Jmap.request({
			method: this.entity.name + "/set",
			params: params,
			scope: this,
			callback: function (options, success, response) {
				
				if(!success) {
					this.fireEvent("error", options, response);
					return;
				}
				
				var entity, clientId;				
				
				if(response.created) {
					for(clientId in response.created) {
						//merge client data with server defaults.
						entity = Ext.apply(params.create[clientId], response.created[clientId]);			
						this._add(entity, true);
					}
				}
				
				if(response.updated) {
					for(var serverId in response.updated) {
						//merge existing data, with updates from client and server						
						entity = Ext.apply(this.data[serverId], params.update[serverId]);
						entity = Ext.apply(entity, response.updated[serverId]);
						this._add(entity, true);
					}
				}
				
				this.setState(response.newState);	
				
				if(response.destroyed) {
					for(var i =0, l = response.destroyed.length; i < l; i++) {						
						this._destroy(response.destroyed[i]);
					}
				}

				if(cb) {
					cb.call(scope || this, options, success, response);
				}

			}
		});
	},
	
	/**
	 * Query the API for a sorted / filtered list of entity id's
	 * 
	 * @param {object} params
	 * @param {function} cb
	 * @param {object} scope
	 * @returns {String} Client call ID
	 */
	query : function(params, cb, scope) {
		var me = this;
		return new Promise(function(resolve, reject) {
			go.Jmap.request({
				method: me.entity.name + "/query",
				params: params,
				callback: function(options, success, response) {

					if(!success) {
						throw me.entity.name + "/query failed!";
					}

					resolve(response);
					if(cb) {
						cb.call(scope || me, response);
					}
				},
				scope: me
			});
		});
	}
});
