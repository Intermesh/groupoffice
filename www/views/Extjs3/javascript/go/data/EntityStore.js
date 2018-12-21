
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
		
//		if(!go.User.loaded) {
//			go.User.on("load", function(){
//				this.initState(cb);
//			}, this, {single: true});
//			return;
//		}

		if(this.initialized) {
			cb.call(this);
			return;
		}
		
		this.stateStore = localforage.createInstance({
			name: "groupoffice",
			storeName: this.entity.name + "-entities"
		});
		
		this.metaStore = localforage.createInstance({
			name: "groupoffice",
			storeName: this.entity.name + "-meta"
		});

		var me = this;
		this.metaStore.getItems(["notfound", "state", "isComplete"], function(err, r) {
			me.notFound = r.notFound || [];
			me.state = r.state;
			me.isComplete = r.isComplete;
			me.initialized = true;
			
			cb.call(me, this);
		});
		
	},
	
	initChanges : function() {
		this.changes = {
			added: {},
			changed: {},
			destroyed: []
		};
	},
	
	clearState : function() {
		console.warn("State cleared for " + this.entity.name);
		this.state = null;
		this.data = {};	

		this.metaStore.clear();
		this.stateStore.clear();
	},
	
	_add : function(entity) {
		
		if(!entity.id) {
			console.error(entity);
			throw "Entity doesn't have an 'id' property";
		}
		
		if(this.data[entity.id]) {			
			this.changes.changed[entity.id] = entity;
			Ext.apply(this.data[entity.id], entity);
		} else
		{
			this.changes.added[entity.id] = entity;
			this.data[entity.id] = entity;
		}		
		
		
		//remove from not found.
		var i = this.notFound.indexOf(entity.id);
		if(i > -1) {
			this.notFound.splice(i, 1);
			this.metaStore.setItem("notfound", this.notFound);
		}
		
		this.stateStore.setItem(entity.id + "", entity);
		
		this._fireChanges();
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
			me.fireEvent('changes', me, me.changes.added, me.changes.changed, me.changes.destroyed);			
			me.initChanges();
			me.timeout = null;
		}, 0);
	},
	
	setState : function(state, cb, scope) {
		this.state = state;
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
		
			if(state) {
				var clientCallId = go.Jmap.request({
					method: this.entity.name + "/getUpdates",
					params: {
						sinceState: this.state
					},
					callback: function(options, success, response) {
						if(success) {
							this.setState(response.newState, function(){
								if(response.hasMoreUpdates) {
									this.getUpdates(cb, scope);
								} else
								{
									if(cb) {
										cb.call(scope || this, this);
									}
								}
							}, this);
							
						} else
						{					
							this.clearState();
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
			} else
			{
				go.Jmap.request({
					method: this.entity.name + "/get",
					callback: function (options, success, response) {

						this.setState(response.state);

						if(cb) {
							cb.call(scope || this, this);
						}
					},
					scope: this
				});
			}
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
				this.getUpdates(function() {
					var me = this;
					this.stateStore.getItems(null, function(err,entities) {				
						for(var key in entities) {		
							if(entities[key]) {
								me.data[entities[key].id] = entities[key];
							}
						}
						cb.call(scope, me.data);
					});					
				});
			} else
			{
				go.Jmap.request({
					method: this.entity.name + "/get",
					callback: function (options, success, response) {
						if(!success) {
							return;
						}

						this.metaStore.setItem('isComplete', true);

						cb.call(scope, response.list);
					},
					scope: this
				});
			}
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
		
		if(go.util.empty(ids)) {
			if(cb) {		
				cb.call(scope || this, [], this);			
			}
			return;
		}
		
		if(!Ext.isArray(ids)) {
			throw "ids must be an array";
		}		
		
		var entities = [], unknownIds = [], notFoundIds = [];

		for (var i = 0, l = ids.length; i < l; i++) {
			var id = ids[i];
			if(!id) {
				throw "Empty ID passed to EntityStore.get()";
			}
			if(this.data[id]) {
				entities.push(this.data[id]);
			} else if(this.notFound.indexOf(id) > -1) {
				//entities.push(null);
				//notFoundIds.push(id);
			} else
			{
				unknownIds.push(id);
			}			
		}
		
		if (unknownIds.length) {		
			this.initState(function() {
				
				this.stateStore.getItems(unknownIds, function(err,entities) {
					unknownIds = unknownIds.filter(function(id){
						return !entities[id+""];
					});

					for(var key in entities) {					
						if(entities[key]) {
							this.data[entities[key].id] = entities[key];
						}
					}


					if(!unknownIds.length) {
						return this.get(ids, cb, scope);					
					}

					go.Jmap.request({
						method: this.entity.name + "/get",
						params: {
							ids: unknownIds
						},
						callback: function (options, success, response) {
							if(!success) {
								return;
							}

							if(!go.util.empty(response.notFound)) {
								this.notFound = this.notFound.concat(response.notFound);
								this.metaStore.setItem("notfound", this.notFound);								
								console.log("Item not found", response);						
							}
							this.get(ids, cb, scope);
						},
						scope: this
					});
				}.createDelegate(this));
			});
			
			return;			
		}	
		
		if(cb) {		
			var notFoundIds = this.notFound.filter(function(i) {			
				return ids.indexOf(i) > -1;	
			});
			cb.call(scope || this, entities, notFoundIds);
		}
		
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
				
	 * go.Stores.get("Foo").set({
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
						this._add(entity);
					}
				}
				
				if(response.updated) {
					for(var serverId in response.updated) {
						//merge existing data, with updates from client and server						
						entity = Ext.apply(this.data[serverId], params.update[serverId]);
						entity = Ext.apply(entity, response.updated[serverId]);
						this._add(entity);
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
		return go.Jmap.request({
			method: this.entity.name + "/query",
			params: params,
			callback: function(options, success, response) {
				
				if(!success) {
					throw this.entity.name + "/query failed!";
				}
				
				cb.call(scope || this, response);
			},
			scope: scope || this
		});
	}
});
