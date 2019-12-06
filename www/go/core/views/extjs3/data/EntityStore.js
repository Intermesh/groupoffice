
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
	
	paused: 0,
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

		this.pending = {};

		this.scheduledPromises = {};
		this.scheduled = [];
		
		this.initChanges();		
	},
	
	initState : function() {
		
		var me = this;
		
		if(me.initialized) {			
			return me.initialized;
		}
		
		me.stateStore = new go.browserStorage.Store(me.entity.name);
		// me.stateStore = localforage.createInstance({
		// 	name: "groupoffice",
		// 	storeName: me.entity.name + "-entities"
		// });

		me.metaStore = new go.browserStorage.Store(me.entity.name + "-meta");
		
		// localforage.createInstance({
		// 	name: "groupoffice",
		// 	storeName: me.entity.name + "-meta"
		// });
		
		// me.initialized = this.clearState().then(function() {return Promise.all([			
		me.initialized = Promise.all([
			me.metaStore.getItem('notFound').then(function(v) {
				me.notFound = v || [];
				return true;
			}),
			me.metaStore.getItem('state').then(function(v) {
				me.state = v;
				return true;
			}),
			me.metaStore.getItem('isComplete').then(function(v) {
				me.isComplete = v;
				return true;
			}),
			me.metaStore.getItem('apiVersion').then(function(v) {
				me.apiVersion = v;
				return true;
			}),
			me.metaStore.getItem('apiUser').then(function(v) {
				me.apiUser = v;
				return true;
			})
		]).then(function() {
			if(!me.state) {
				return Promise.all([
					me.metaStore.setItem("apiVersion", go.User.apiVersion),
					me.metaStore.setItem("apiUser", go.User.username)
				]).then(function() {
					return me.state;
				})
			} else if(me.apiVersion !== go.User.apiVersion || me.apiUser !== go.User.username) {
				console.warn("API version or username mismatch", me.apiVersion, go.User.apiVersion, me.apiUser, go.User.username);
				return me.clearState().then(function() {
					return me.state;
				});
			} else
			{
				return me.state;
			}
		});

		return me.initialized;
		
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
		
		// this.changedIds is set by a /changes request. If this item is added because of 
		// a changes request we must fire a changes event. Not if we're loading by request.
		if(!Ext.isDefined(fireChanges)) {
			fireChanges = this.changedIds.indexOf(entity.id) > -1;
		}
		
		if(this.data[entity.id]) {			
			if(fireChanges) {
				this.changes.changed[entity.id] = go.util.clone(entity);
			}
			Ext.apply(this.data[entity.id], entity);
		} else
		{
			if(fireChanges) {
				this.changes.added[entity.id] = go.util.clone(entity);
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
		me.fireEvent('changes', me, me.changes.added, me.changes.changed, me.changes.destroyed);
		me.timeout = setTimeout(function () {
			// console.warn('changes', me.entity.name, me.changes.added, me.changes.changed, me.changes.destroyed);
			me.initChanges();
			me.timeout = null;
		}, 0);
	},
	
	setState : function(state) {
		var me = this;
		this.state = state;

		var setter = function() {
			return me.metaStore.setItem("state", state);
		};
		
		if(!state) {
			return me.clearState().then(function() {
				return setter();
			});
		}

		return setter();	
	},	
	
	getState: function() {
		var me = this;
		return this.initState().then(function() {
			return me.state;
		});
	},


	receive: function (action) {
		var me = this;	
		me.getState().then(function(state){
			switch (action.type) {
				case me.entity.name + "/get":

					// If no items are available, don't continue
					if(!action.payload.response.list){
						return;
					}
					
					// If properties was set in the request params then we don't want to 
					// store this entity. We only want to keep complete entities
					if(action.payload.options.params && action.payload.options.params.properties && action.payload.options.params.properties.length) {
						return;
					}

					//add data from get response
					for(var i = 0,l = action.payload.response.list.length;i < l; i++) {
						me._add(action.payload.response.list[i]);
					}

					me.setState(action.payload.response.state);
					break;
				case me.entity.name + "/changes" :
					//keep me array for the Foo/get response to check if an event must be fired.
					//We will only fire added and changed in _add if me came from a /changes 
					//request and not when we are loading data ourselves.
					me.changedIds = action.payload.response.changed || [];
					break;

				case me.entity.name + "/query":
//					console.log("Query state: " + state + " - " + action.payload.state);
					//if a list call was made then fetch updates if state mismatch
					if (state && action.payload.response.state !== state) {
						me.getUpdates();
						//me.setState(action.payload.state);
					}
					break;

				case me.entity.name + "/set":
					//update state from set we initiated
					me.setState(action.payload.response.newState);
					break;
			}
		});
	},

	getUpdates: function (cb, scope) {		

		var me = this;
		
		return me.getState().then(function(state){
			
			console.log("getUpdates", me.entity.name, state);
		
			if(!state) {
				console.info("No state yet so won't fetch updates");
				if(cb) {
					cb.call(scope || me, me, false);
				}
				return Promise.reject("No state yet");
			}
			
			//we need the initial request promise for the callId.
			var promise = go.Jmap.request({
				method: me.entity.name + "/changes",
				params: {
					sinceState: me.state
				}
			});
			
			promise.then(function(response) {
				if(response.removed) {
					for(var i = 0, l = response.removed.length; i < l; i++) {
						me._destroy(response.removed[i]);
					}
				}
				
				return me.setState(response.newState).then(function(){
					if(response.hasMoreChanges) {
						return me.getUpdates(cb, scope);
					} else
					{
						if(cb) {
							cb.call(scope || me, me, true);
						}

						return true;
					}
				}, me);
			}).catch(function(response) {
				return me.clearState().then(function(response) {
					if(cb) {
						cb.call(scope || me, me, false);
					}
					return response;
				});
			});

			var getPromise = go.Jmap.request({
				method: me.entity.name + "/get",
				params: {
					"#ids": {
						resultOf: promise.callId,
						path: '/changed'
					}
				}
			});

			return Promise.all([promise, getPromise]);
		});

	},
	
	/**
	 * Get all entities
	 *
	 * Note: the results are sorted in an unpredictable manner! Use query().then(return get()) for sorting.
	 *
	 * @param {function=} cb
	 * @param {object=} scope
	 * @returns {Promise}
	 */
	all : function(cb, scope) {
		var me = this;

		return me.initState().then(function() {
			if(me.isComplete) {
				return me.query().then(function(response) {										
					return me.get(response.ids).then(function(result) {
						if(cb) {
							cb.call(scope, true, result.entities);
						}
	
						return result.entities
					});				
				});
			} else
			{
				return go.Jmap.request({
					method: me.entity.name + "/get"		
				}).then(function(response) {
					
					me.metaStore.setItem('isComplete', true);
					me.isComplete = true;
					
					if(cb) {
						cb.call(scope, true, response.list);
					}

					return response.list;
				}).catch(function(response) {
					if(cb) {
						cb.call(scope, false, response);
					}
				});
			}
		});
	},
	
	/**
	 * Get a single entity
	 * 
	 * @example
	 * ```
	 * go.Db.store("Foo").single().then(function(entity) {
	 * 	console.log(entity);
	 * });
	 * ```
	 * 
	 * @param {int} id
	 * @return {Promise} 
	 */
	single: function(id) {

		var me = this;
				
		// 	return me._getSingleFromServer(id);		

		return this._getSingleFromBrowserStorage(id).then(function(entity) {
			if(entity) {
				return entity;
			} else{
				return me._getSingleFromServer(id);
			}			
		});

	},

	_getSingleFromLocalCache : function(id) {
		if(this.data[id]) {
			return Promise.resolve(go.util.clone(this.data[id]));
		}
		
		var me = this;

		if(this.notFound.indexOf(id) > -1) {
//			console.warn("Not fetching " + this.entity.name + " (" + id + ") because it was not found in an earlier attempt");
			return Promise.reject({
							id: id,
							entity: me.entity.name,
							error: "Not found (in earlier attempt on server)"
						});
		}		

		// For testing without indexeddb
		// return me._getSingleFromServer(id);
		
		return this._getSingleFromBrowserStorage(id).then(function(entity) {
			return entity;
		});
	},

	_getSingleFromServer : function(id) {

		if(Ext.isObject(id)) {
			throw "object given";
			
		}
		if(this.pending[id]) {			
			return this.pending[id];
		}

		var me = this;

		if(me.timeout) {
			clearTimeout(me.timeout);
		}
		
		me.scheduled.push(id);
		me.scheduledPromises[id] = {};
		me.pending[id] = new Promise(function(resolve, reject){
			me.scheduledPromises[id].reject = reject;
			me.scheduledPromises[id].resolve = resolve;
		});

		if(!me.paused) {
			me.continue();
		}
		
		
		return this.pending[id];
	},

	pause : function() {
		if(this.timeout) {
			clearTimeout(this.timeout);			
		}

		this.paused++;
	},

	continue: function() {
		var me = this;

		if(this.paused>0) {
			this.paused--;
		}

		if(this.paused > 0)
		{
			return;
		}

		me.timeout = setTimeout(function() {

			if(!me.scheduled.length) {
				return;
			}
			
			go.Jmap.request({
				method: me.entity.name + "/get",
				params: {
					ids: me.scheduled
				}
			}).then(function(response) {

				if(!go.util.empty(response.notFound)) {
					me.notFound = me.notFound.concat(response.notFound);
					me.metaStore.setItem("notfound", me.notFound);									
				}

				for(var i = 0, l = response.options.params.ids.length; i < l; i++) {
					var id = response.options.params.ids[i];

					delete me.pending[id];
					if(response.notFound.indexOf(id) > -1) {

						var err = {
							id: id,
							entity: me.entity.name,
							error: "Not found"
						};

						console.warn(err);
						
						me.scheduledPromises[id].reject(err);	
					} else
					{
						//this.data is filled with flux in the recieve() function.
						if(!me.data[id]) {
							//return Promise.reject("Data not available ???");
							me.scheduledPromises[id].reject("Data not available ???");
						}
						me.scheduledPromises[id].resolve(go.util.clone(me.data[id]));
					}

					delete me.scheduledPromises[id];					
				}
			});

			me.scheduled = [];
			me.timeout = null;
		});
	},

	_getSingleFromBrowserStorage : function(id) {
		var me = this;
		
		//Pause JMAP requests because indexeddb events will trigger the queue
		go.Jmap.pause();
		this.pause();
		return me.initState().then(function() {			
			return me.stateStore.getItem(id + "").then(function(entity) {		
				if(!entity) {
					return null;
				}				

				me.data[id] = entity;
				return go.util.clone(entity);
			});
		}).finally(function(){			
			//Continue JMAP
			go.Jmap.continue();
			me.continue();
		});
	},

	/**
	 * Get entities
	 * 
	 * Also see singele() for fetching a single entity
	 * 
	 * @example
	 * ```
	 * go.Db.store("Foo").get().then(function(result) {
	 * 	console.log(result.entities);
	 * });
	 * ```
	 * 
	 * @link https://jmap.io/spec-core.html#/get
	 * @param {string[]|int[]} ids
	 * @param {function} cb Callback function that is called with entities[] and notFoundIds[] 
	 * @param {object} scope
	 * @returns {Promise} called with {entities:[], notFoundIds:[]}
	 */
	get: function (ids, cb, scope) {

		if(go.util.empty(ids)) {
			if(cb) {		
				cb.call(scope || this, [], []);			
			}
			return Promise.resolve({entities: [], notFound: []});
		}

		if(!Ext.isArray(ids)) {
			throw "ids must be an array";
			
		} else{
			if(Ext.isObject(ids[0])) {
				throw "Object given";
			}
		}

		var entities = [], notFound = [], promises = [], order = {};

		ids.forEach(function(id, index) {
			//keep order for sorting later
			order[id] = index;
			promises.push(this.single(id).then(function(entity) {
					//Make sure array is sorted the same as ids
					entities.push(entity);					
				}).catch(function() {
					notFound.push(id);
				}));
		}, this);	

		return Promise.all(promises).then(function() {
			entities.sort(function (a, b) {
					return order[a.id] - order[b.id];
			});

			if(cb) {
				cb.call(scope, entities, notFound);
			}

			return {entities: entities, notFound: notFound};
		});
		
	},




	// get: function (ids, cb, scope) {

	// 	if(go.util.empty(ids)) {
	// 		if(cb) {		
	// 			cb.call(scope || this, [], []);			
	// 		}
	// 		return Promise.resolve({entities: [], notFound: []});
	// 	}

	// 	if(!Ext.isArray(ids)) {
	// 		throw "ids must be an array";
			
	// 	} else{
	// 		if(Ext.isObject(ids[0])) {
	// 			throw "Object given";
	// 		}
	// 	}

	// 	var entities = [], notFound = [], promises = [], order = {}, fetchFromServer = [], me = this;

	// 	ids.forEach(function(id, index) {
	// 		//keep order for sorting later
	// 		order[id] = index;
	// 		promises.push(this._getSingleFromLocalCache(id).then(function(entity) {
	// 				//Make sure array is sorted the same as ids
	// 				if(entity) {
	// 					entities.push(entity);					
	// 				} else{
	// 					fetchFromServer.push(id);
	// 				}
	// 			}));
	// 	}, this);	

	// 	function ret() {
	// 		entities.sort(function (a, b) {
	// 				return order[a.id] - order[b.id];
	// 		});

	// 		if(cb) {
	// 					cb.call(scope, entities, notFound);
	// 		}
		
	// 		return {entities: entities, notFound: notFound};
	// 	}

	// 	return Promise.all(promises).then(function() {

	// 		if(fetchFromServer.length == 0) {
	// 			return ret();
	// 		}

	// 		return go.Jmap.request({
	// 			method: me.entity.name + "/get",
	// 			params: {
	// 				ids: fetchFromServer
	// 			}
	// 		}).then(function(response) {
	// 				if(!go.util.empty(response.notFound)) {
	// 					me.notFound = me.notFound.concat(response.notFound);
	// 					notFound = response.notFound;
	// 					me.metaStore.setItem("notfound", me.notFound);							
	// 				}
					
	// 				entities = entities.concat(response.list);
	// 				return ret();
	// 			}
	// 		);
	// 	});

		
	// },
	
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
	 *	}).then(function(response){
	 * 
	 *  });
	 * 
	 * ```
	 * 
	 * Destroy:
	 * 
	 * ```
	 * this.entityStore.set({destroy: [1,2]}).then(function (response) {
			if (response.destroyed) {
				
			}
		});
		```
	 * 
	 * @param {object} params	 
	 * @param {function} cb A function called with success, values, response, options
	 * @param {object} scope
	 * 	 
	 * @returns {Promise} 
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
					if(cb) {
						cb.call(scope || this, options, success, response);
					}
					return;
				}
				
				var entity, clientId;				
				
				if(response.created) {
					for(clientId in response.created) {
						//merge client data with server defaults.
						entity = Ext.apply(params.create[clientId], response.created[clientId] || {});			
						this._add(entity, true);
					}
				}
				
				if(response.updated) {
					for(var serverId in response.updated) {
						//merge existing data, with updates from client and server						
						entity = Ext.apply(this.data[serverId], params.update[serverId]);
						entity = Ext.apply(entity, response.updated[serverId] || {});
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

	merge: function(ids) {
		var me = this;
		return go.Jmap.request({
			method: me.entity.name + '/merge',
			params: {
				ids: ids
			},
			
		}).then(function(response) {
			if(response.updated) {
				for(var serverId in response.updated) {
					//merge existing data, with updates from client and server						
					entity = Ext.apply(me.data[serverId], response.updated[serverId]);
					me._add(entity, true);
				}
			}
			
			me.setState(response.newState);	
			
			if(response.destroyed) {
				for(var i =0, l = response.destroyed.length; i < l; i++) {						
					me._destroy(response.destroyed[i]);
				}
			}

			return response;
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

		if(!params || !params.limit) {
			console.warn(me.entity.name + "/query call without limit");
		}

		return go.Jmap.request({
				method: me.entity.name + "/query",
				params: params				
		}).then(function(response) {
			if(cb) {
				cb.call(scope || me, response);
			}

			return response;
		});
	}
});
