
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
	
	// Set to true when all data has been fetched from server
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
			
		if(this.initialized) {			
			return this.initialized;
		}
		
		this.stateStore = new go.browserStorage.Store(this.entity.name);
		this.metaStore = new go.browserStorage.Store(this.entity.name + "-meta");

		// this.initialized = this.clearState().then(function() {return Promise.all([			
		this.initialized = Promise.all([
			this.metaStore.getItem('notFound').then((v) => {
				this.notFound = v || [];
				return true;
			}),
			this.metaStore.getItem('state').then((v) => {
				this.state = v;
				return true;
			}),
			this.metaStore.getItem('isComplete').then((v) => {
				this.isComplete = v;
				return true;
			}),
			this.metaStore.getItem('apiVersion').then((v) => {
				this.apiVersion = v;
				return true;
			}),
			this.metaStore.getItem('apiUser').then((v) => {
				this.apiUser = v;
				return true;
			})
		]).then(() => {
			if(!this.state) {
				return Promise.all([
					this.metaStore.setItem("apiVersion", go.User.apiVersion),
					this.metaStore.setItem("apiUser", go.User.username)
				]).then( () => {
					return this.state;
				})
			} else if(this.apiVersion !== go.User.apiVersion || this.apiUser !== go.User.username) {
				console.warn("API version or username mismatch", this.apiVersion, go.User.apiVersion, this.apiUser, go.User.username);
				return this.clearState().then(() => {
					return this.state;
				});
			} else
			{
				return this.state;
			}
		});

		return this.initialized;

	},

	/**
	 * Creates new changes object to use with the "changes" event.
	 */
	initChanges : function() {
		this.changes = {
			added: [],
			changed: [],
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
				this.changes.changed.push(entity.id);
			}
			Ext.apply(this.data[entity.id], entity);
		} else
		{
			if(fireChanges) {
				this.changes.added.push(entity.id);
			}
			this.data[entity.id] = entity;
		}
		
		//remove from not found.
		let i = this.notFound.indexOf(entity.id);
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

		//Use set timeout so changes event fires after promises when set() is used.
		//This way when for example a dialog closes the dialog or stores are destroyed before it fires.
		// Other wise they are destroyed while it fires and this can lead to errors.
		setTimeout(() => {
			this.fireEvent('changes', this, this.changes.added, this.changes.changed, this.changes.destroyed);
			this.initChanges();
		}, 0);

	},

	/**
	 * Saves the JMAP state for this entity
	 *
	 * @param state
	 * @returns {*|Promise<String>}
	 */
	setState : function(state) {
		this.state = state;

		const setter = () => {
			return this.metaStore.setItem("state", state);
		};
		
		if(!state) {
			return this.clearState().then(() => {
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
		return this.initState().then(() => {
			return this.state;
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

		if(this.getUpdatesPromise) {
			return this.getUpdatesPromise;
		}

		this.getUpdatesPromise = this.getState().then((state) => {
			
			// console.log("getUpdates", this.entity.name, state);
		
			if(!state) {
				console.info("No state yet so won't fetch updates");
				if(cb) {
					cb.call(scope || this, this, false);
				}
				return Promise.reject("No state yet");
			}

			return go.Jmap.request({
				method: this.entity.name + "/changes",
				params: {
					sinceState: this.state
				}
			}).then((changes) => {

				if(changes.removed) {
					changes.removed.forEach((id) => {
						delete this.data[id];
						this.changes.destroyed.push(id);
						this.stateStore.removeItem(id + "");
					});
				}

				if(changes.changed) {
					changes.changed.forEach((id) => {

						if(id in this.data) {
							this.changes.changed.push(id);
						} else {
							this.changes.added.push(id);
						}
						//clear data
						delete this.data[id];
						this.stateStore.removeItem(id + "");
					});
				}


				this.setState(changes.newState).then(() => {
					if(changes.hasMoreChanges) {

						//unofficial response but we use it to process no more than 100000 changes. A resync is
						//more efficient in the webclient in that case.
						if(changes.totalChanges > 10000) {
							console.error("Too many changes " + changes.totalChanges + " > 10000 ");
							return this.clearState().then((response)  => {
								if(cb) {
									cb.call(scope || this, this, false);
								}
								return Promise.reject({type: "cannotcalculatechanges", detail: "Too many changes"})
							});
						}
						return this.getUpdates(cb, scope);
					} else
					{
						if(cb) {
							cb.call(scope || this, this, true);
						}

						this._fireChanges();

						return true;
					}
				});

			}).catch((response) => {
				return this.clearState().then((response) => {
					if(cb) {
						cb.call(scope || this, this, false);
					}
					return response;
				});
			});

		}).finally(() => {
			this.getUpdatesPromise = null;
		});

		return this.getUpdatesPromise;

	},
	
	/**
	 * Get all entities
	 *
	 * @param {function=} cb
	 * @param {object=} scope
	 * @returns {Promise}
	 */
	all : function(cb, scope) {

		return this.initState().then(()  => {
			if(this.isComplete) {
				return this.query().then((response) => {
					return this.get(response.ids).then( (result) => {
						if(cb) {
							cb.call(scope, true, result.entities);
						}
	
						return result.entities
					});				
				});
			} else
			{
				return go.Jmap.request({
					method: this.entity.name + "/get"
				}).then((response) => {

					this.metaStore.setItem('isComplete', true);
					this.isComplete = true;
					
					if(cb) {
						cb.call(scope, true, response.list);
					}

					return response.list;
				}).catch((response) => {
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


		return this._getSingleFromBrowserStorage(id).then((entity) => {
			if(entity) {
				return entity;
			} else{
				return this._getSingleFromServer(id);
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

		if(this.getTimeout) {
			clearTimeout(this.getTimeout);
		}

		this.scheduled.push(id);
		this.scheduledPromises[id] = {};
		this.pending[id] = new Promise((resolve, reject) => {
			this.scheduledPromises[id].reject = reject;
			this.scheduledPromises[id].resolve = resolve;
		});

		if(!this.paused) {
			this.continueGet();
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

		if(this.paused > 0) {
			this.paused--;
		}

		if(this.paused > 0)
		{
			return;
		}

		this.getTimeout = setTimeout(() => {

			if(!this.scheduled.length) {
				return;
			}
			var options = {
				method: this.entity.name + "/get",
				params: {
					ids: this.scheduled
				}
			};
			go.Jmap.request(options).then((response) => {

				if(!go.util.empty(response.notFound)) {
					this.notFound = this.notFound.concat(response.notFound);
					this.metaStore.setItem("notfound", this.notFound);
				}

				if(response.list) {
					for (let i = 0, l = response.list.length; i < l; i++) {
						this._add(response.list[i], false);
					}
				}

				for(let i = 0, l = options.params.ids.length; i < l; i++) {
					let id = options.params.ids[i];

					delete this.pending[id];
					if(response.notFound.indexOf(id) > -1) {

						var err = {
							id: id,
							entity: this.entity.name,
							error: "Not found"
						};

						console.warn(err);
						this.scheduledPromises[id].reject(err);
					} else
					{
						if(!this.data[id]) {
							//return Promise.reject("Data not available ???");
							this.scheduledPromises[id].reject("Data not available ???");
						}
						this.scheduledPromises[id].resolve(go.util.clone(this.data[id]));
					}

					delete this.scheduledPromises[id];
				}

				return this.setState(response.state).then(() => {
					return response;
				});
			}).catch((response) => {
				for(let i = 0, l = options.params.ids.length; i < l; i++) {
					let id = options.params.ids[i];

					delete this.pending[id];
					this.scheduledPromises[id].reject(response);
					delete this.scheduledPromises[id];
				}
			});

			this.scheduled = [];
			this.getTimeout = null;
		});
	},

	_getSingleFromBrowserStorage : function(id) {

		// check if we already fetched it.
		if(this.data[id]) {
			return Promise.resolve(go.util.clone(this.data[id]));
		}
		
		//Pause JMAP requests because indexeddb events will trigger the queue
		go.Jmap.pause();

		this.pauseGet();
		return this.initState().then(() => {
			return this.stateStore.getItem(id + "").then((entity) => {
				if(!entity) {
					return null;
				}

				this.data[id] = entity;
				return go.util.clone(entity);
			});
		}).finally(() => {

			go.Jmap.continue();
			this.continueGet();
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

		let entities = [], notFound = [], promises = [], order = {};

		ids.forEach((id, index) => {
			//keep order for sorting later
			order[id] = index;
			promises.push(this.single(id).then(function(entity) {
					//Make sure array is sorted the same as ids
					entities.push(entity);					
				}).catch(function() {
					notFound.push(id);
				}));
		});

		return Promise.all(promises).then(() => {
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
		const data = Object.values(this.data);
		for(let i = startIndex, l = data.length; i < l; i++) {
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
	 * go.Db.store("Note").save({name: "Test"}, 1).then(function(entity){});
	 *
	 * @param entity
	 * @param {string} id
	 * @returns {Promise}
	 */
	save : function(entity, id) {
		let p = {}, op;

		if(id) {
			op = 'update';
		}else
		{
			op = 'create';
			id = '_new_';
		}

		p[op] = {};
		p[op][id] = entity;

		return this.set(p).then((response) => {
			if(op == 'create') {
				if(response.created && id in response.created) {
					return this.single(response.created[id].id);
				} else
				{
					let msg = t("Failed to save");
					if(response.notCreated && id in response.notCreated) {
						msg = response.notCreated[id].description;
					}

					return Promise.reject({message: msg, response: response, error: response.notCreated[id] || null});
				}
			} else
			{
				if(response.updated && id in response.updated) {
					return this.single(id);
				} else
				{
					let msg = t("Failed to save");
					if(response.notUpdated && id in response.notUpdated) {
						msg = response.notUpdated[id].description;
					}
					return Promise.reject({message: msg, response: response, error: response.notUpdated[id] || null});
				}
			}
		});
	},

	/**
	 * Destroy a single item.
	 *
	 * Shortcut for this.set().
	 *
	 * @example
	 * ```
	 * Ext.MessageBox.confirm(t("Delete"), t("Are you sure you want to delete this item?"), function (btn) {

			if (btn == "yes") {
				go.Db.store("Tasklist").destroy(tasklistId).catch((result) => {
					GO.errorDialog.show(result.error.description);
				});
			}
		}, this);
	 * ```
	 *
	 * @param {int} id
	 * @returns {Promise<object>}
	 */
	destroy : function(id) {
		return this.set( {destroy: [id]}).then((response) => {
			if(response.destroyed.indexOf(id) == -1) {
				return Promise.reject({message: t("Failed to delete"), response: response, error: response.notDestroyed[id] || null});
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

		return this.initState().then(() => {
			const options = {
				method: this.entity.name + "/set",
				params: params
			};

			return go.Jmap.request(options).then((response) => {
				let entity, clientId;

				if(response.created) {
					for(clientId in response.created) {
						//merge client data with server defaults.
						entity = Ext.apply(params.create ? (params.create[clientId] || {}) : {}, response.created[clientId] || {});
						this._add(entity, true);
					}
				}

				if(response.updated) {
					for(let serverId in response.updated) {
						//server updated something we don't have
						if(!this.data[serverId]) {
							continue;
						}
						//merge existing data, with updates from client and server
						entity = Ext.apply(this.data[serverId], params.update[serverId]);
						entity = Ext.apply(entity, response.updated[serverId] || {});
						this._add(entity, true);
					}
				}

				this.setState(response.newState);

				if(response.destroyed) {
					for(let i =0, l = response.destroyed.length; i < l; i++) {
						this._destroy(response.destroyed[i]);
					}
				}

				if(cb) {
					cb.call(scope || this, options, true, response);
				}

				this._fireChanges();

				return response;
			}).catch((error) => {
				this.fireEvent("error", options, error);
				if(cb) {
					cb.call(scope || this, options, false, error);
				}

				return Promise.reject(error);
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

		return go.Jmap.request({
			method: this.entity.name + '/merge',
			params: {
				ids: ids
			},
			
		}).then((response) => {
			if(response.updated) {
				for(var serverId in response.updated) {
					//merge existing data, with updates from client and server						
					entity = Ext.apply(this.data[serverId], response.updated[serverId]);
					this._add(entity, true);
				}
			}

			this.setState(response.newState);
			
			if(response.destroyed) {
				for(let i =0, l = response.destroyed.length; i < l; i++) {
					this._destroy(response.destroyed[i]);
				}
			}

			this._fireChanges();

			return response;
		});
	},
	
	/**
	 * Query the API for a sorted / filtered list of entity id's
	 * 
	 * @param {object} params {@link https://jmap.io/spec-core.html#query
	 * @param {function} cb
	 * @param {object} scope
	 * @returns {String} Client call ID
	 */
	query : function(params, cb, scope) {

		if(!params || !params.limit) {
			console.warn(this.entity.name + "/query call without limit");
		}

		let reqProm =  go.Jmap.request({
				method: this.entity.name + "/query",
				params: params				
		});

		retProm = reqProm.then((response) => {

			//if received state is newer then fetch updates
			this.getState().then((state) => {
				if(!state) {
					this.setState(response.state);
				} else if (response.state !== state) {
					this.getUpdates();
				}
			});

			if(cb) {
				cb.call(scope || this, response);
			}

			return response;
		});
		//todo there's got to be a better way to do this. Promises should be cancellable. Used in entityStoreProxy
		retProm.callId = reqProm.callId;

		return retProm;

	}
});
