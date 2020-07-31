
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
 * added: Object Entity object mapped by ID
 * changed: Object Entity object mapped by ID
 * destroyed: int[]|string[] array of ids's
 *
 * Do not instantiate directly use:
 *
 * @example
 * go.Db.store("User").query();
 * 
 */
go.data.EntityStore = Ext.extend(Ext.util.Observable, {

	state: null,
	
	data : null,
	
	notFound: null,

	/**
	 * @var {go.Entity}
	 */
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

		config = config || {};
		Ext.apply(this, config);
		
		this.addEvents({changes:true, error:true});
		
		this.notFound = [];
		this.data = {};
		this.state = null;

		this.pending = {};

		this.scheduledPromises = {};
		this.scheduled = [];
		
		this.initChanges();		
	},

	/**
	 * Inititalizes IndexedDB storage and state variables
	 *
	 * @returns {Promise<T>}
	 */
	initState : function() {
		
		var me = this;
		
		if(me.initialized) {			
			return me.initialized;
		}
		
		me.stateStore = new go.browserStorage.Store(me.entity.name);
		me.metaStore = new go.browserStorage.Store(me.entity.name + "-meta");

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

	/**
	 * Creates new changes object to use with the "changes" event.
	 */
	initChanges : function() {
		this.changes = {
			added: {},
			changed: {},
			destroyed: []
		};
	},

	/**
	 * Clear state and local data
	 *
	 * @returns {Promise|*}
	 */
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

	/**
	 * Add's item to local data object and IndexedDB
	 *
	 * @param {Object} entity
	 * @param {boolean} fireChanges True to add item to the "changes" event. When doing a regular /get request to load a
	 * 	store we don't want this. But only on /set and /changes
	 * @private
	 */
	_add : function(entity, fireChanges) {
		if(!entity.id) {
			console.error(entity);
			throw "Entity doesn't have an 'id' property";
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
	},

	/**
	 * Destroy an item from local data
	 * @param id
	 * @private
	 */
	_destroy : function(id) {
		delete this.data[id];
		this.changes.destroyed.push(id);
		this.stateStore.removeItem(id + "");
	},

	_fireChanges : function() {
		var me = this;
		// console.warn('changes', me.entity.name, me.changes.added, me.changes.changed, me.changes.destroyed);

		//Use set timeout so changes event fires after promises when set() is used.
		//This way when for example a dialog closes the dialog or stores are destroyed before it fires.
		// Other wise they are destroyed while it fires and this can lead to errors.
		setTimeout(function() {
			me.fireEvent('changes', me, me.changes.added, me.changes.changed, me.changes.destroyed);
			me.initChanges();
		}, 0);

	},

	/**
	 * Saves the JMAP state for this entity
	 *
	 * @param state
	 * @returns {*|Promise<String>}
	 */
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

	/**
	 * Get the saved JMAP entity state
	 * @returns {Promise<String>}
	 */
	getState: function() {
		var me = this;
		return this.initState().then(function() {
			return me.state;
		});
	},

	/**
	 * Get updates for this entity
	 * Does a Foo/changes request
	 *
	 * @param cb
	 * @param scope
	 * @returns {Promise<Object>}
	 */
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
			}).then(function(response) {
				if(go.util.empty(response.list)) {
					console.warn("No items in response: ", response);
					return;
				}
				for(var i = 0,l = response.list.length;i < l; i++) {
					me._add(response.list[i], true);
				}

				me._fireChanges();
			});

			return Promise.all([promise, getPromise]);
		});

	},
	
	/**
	 * Get all entities
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
	 * @return {Promise} Promise is rejected when it's not found or there are no permissions.
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

	_getSingleFromServer : function(id) {

		if(Ext.isObject(id)) {
			throw "object given";
			
		}
		if(this.pending[id]) {			
			return this.pending[id];
		}

		var me = this;

		if(me.getTimeout) {
			clearTimeout(me.getTimeout);
		}
		
		me.scheduled.push(id);
		me.scheduledPromises[id] = {};
		me.pending[id] = new Promise(function(resolve, reject){
			me.scheduledPromises[id].reject = reject;
			me.scheduledPromises[id].resolve = resolve;
		});

		if(!me.paused) {
			me.continueGet();
		}

		return this.pending[id];
	},

	/**
	 * Pause execution of /get requests
	 */
	pauseGet : function() {
		if(this.getTimeout) {
			clearTimeout(this.getTimeout);
		}

		this.paused++;
	},

	/**
	 * We use a setTimeout to group all /get requests into one HTTP requests. WHen IndexedDB is accessed the event queue is processed.
	 * We don't want that so we temporary pause the execution and continue it when done with indexedDB.
	 */
	continueGet: function() {
		var me = this;

		if(this.paused > 0) {
			this.paused--;
		}

		if(this.paused > 0)
		{
			return;
		}

		me.getTimeout = setTimeout(function() {

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

				if(response.list) {
					for (var i = 0, l = response.list.length; i < l; i++) {
						me._add(response.list[i], false);
					}
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
						if(!me.data[id]) {
							//return Promise.reject("Data not available ???");
							me.scheduledPromises[id].reject("Data not available ???");
						}
						me.scheduledPromises[id].resolve(go.util.clone(me.data[id]));
					}

					delete me.scheduledPromises[id];					
				}

				return me.setState(response.state).then(function() {
					return response;
				});
			}).catch(function(response) {
				for(var i = 0, l = response.options.params.ids.length; i < l; i++) {
					var id = response.options.params.ids[i];

					delete me.pending[id];
					me.scheduledPromises[id].reject(response);
					delete me.scheduledPromises[id];
				}
			});

			me.scheduled = [];
			me.getTimeout = null;
		});
	},

	_getSingleFromBrowserStorage : function(id) {
		var me = this;

		// check if we already fetched it.
		if(me.data[id]) {
			return Promise.resolve(go.util.clone(me.data[id]));
		}
		
		//Pause JMAP requests because indexeddb events will trigger the queue
		go.Jmap.pause();

		this.pauseGet();
		return me.initState().then(function() {			
			return me.stateStore.getItem(id + "").then(function(entity) {
				if(!entity) {
					return null;
				}				

				me.data[id] = entity;
				return go.util.clone(entity);
			});
		}).finally(function(){

			go.Jmap.continue();
			me.continueGet();
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
	 * Save an entity.
	 *
	 * Shortcut method for Foo/set
	 *
	 * @example
	 *
	 * go.Db.store("Note").save({name: "Test"}, 1).then(function(){});
	 *
	 * @param entity
	 * @param {string} id
	 * @returns {Promise}
	 */
	save : function(entity, id) {
		var p = {}, op;

		if(id) {
			op = 'update';
		}else
		{
			op = 'create';
			id = '_new_';
		}

		p[op] = {};
		p[op][id] = entity;

		return this.set(p).then(function(response) {
			if(op == 'create') {
				if(response.created && id in response.created) {
					return response.created[id];
				} else
				{
					return Promise.reject(response);
				}
			} else
			{
				if(response.updated && id in response.updated) {
					return response.updated[id];
				} else
				{
					return Promise.reject(response);
				}
			}
		});
	},

	/**
	 * Destroy a single item.
	 *
	 * Shortcut for this.set().
	 *
	 * @param {int} id
	 * @returns {Promise<object>}
	 */
	destroy : function(id) {
		return this.set( {destroy: [id]}).then(function(response) {
			if(response.destroyed.indexOf(id) == -1) {
				return Promise.reject(response);
			} else {
				return true;
			}
		});
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
		
		var me = this;

		return me.initState().then(function() {
			return go.Jmap.request({
				method: me.entity.name + "/set",
				params: params
			}).then(function(response) {
				var entity, clientId;

				if(response.created) {
					for(clientId in response.created) {
						//merge client data with server defaults.
						entity = Ext.apply(params.create[clientId], response.created[clientId] || {});
						me._add(entity, true);
					}
				}

				if(response.updated) {
					for(var serverId in response.updated) {
						//merge existing data, with updates from client and server
						entity = Ext.apply(me.data[serverId], params.update[serverId]);
						entity = Ext.apply(entity, response.updated[serverId] || {});
						me._add(entity, true);
					}
				}

				me.setState(response.newState);

				if(response.destroyed) {
					for(var i =0, l = response.destroyed.length; i < l; i++) {
						me._destroy(response.destroyed[i]);
					}
				}

				if(cb) {
					cb.call(scope || me, response.options, true, response);
				}

				me._fireChanges();

				return response;
			}).catch(function(error){
				me.fireEvent("error", error.options, error);
				if(cb) {
					cb.call(scope || me, error.options, false, error);
				}

				return error;
			})
		});
	},

	/**
	 * Merge duplicated entities into one
	 *
	 * @param ids
	 * @returns {Promise<Object>}
	 */
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

			me._fireChanges();

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

		var reqProm =  go.Jmap.request({
				method: me.entity.name + "/query",
				params: params				
		});

		var retProm = reqProm.then(function(response) {

			//if received state is newer then fetch updates
			me.getState().then(function(state){
				if(!state) {
					me.setState(response.state);
				} else if (response.state !== state) {
					me.getUpdates();
				}

			});

			if(cb) {
				cb.call(scope || me, response);
			}

			return response;
		});
		//todo there's got to be a better way to do this. Promises should be cancellable. Used in entityStoreProxy
		retProm.callId = reqProm.callId;

		return retProm;

	}
});
