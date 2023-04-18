
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

	constructor : function(config) {
		go.data.EntityStore.superclass.constructor.call(this, config);

		config = config || {};
		Ext.apply(this, config);
		
		this.addEvents({changes:true, error:true});

		window.groupofficeCore.jmapds(this.entity.name).on('change', (store, changes) => {
			this.fireEvent('changes', this, changes.created, changes.updated, changes.destroyed);
		})
	},

	/**
	 * Saves the JMAP state for this entity
	 *
	 * @param state
	 * @returns {*|Promise<String>}
	 */
	setState : function(state) {
		return window.groupofficeCore.jmapds(this.entity.name).getState();
	},

	/**
	 * Get the saved JMAP entity state
	 * @returns {Promise<String>}
	 */
	getState: function() {
		return window.groupofficeCore.jmapds(this.entity.name).getState();
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

		return window.groupofficeCore.jmapds(this.entity.name).updateFromServer().then(() => {
			if(cb) {
				cb.call(scope || this, this, false);
			}
			return true;
		})

	},
	
	/**
	 * Get all entities
	 *
	 * @param {function=} cb
	 * @param {object=} scope
	 * @returns {Promise}
	 */
	all : function(cb, scope) {

		return this.getState().then(()  => {
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

					// this.metaStore.setItem('isComplete', true);
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
		return window.groupofficeCore.jmapds(this.entity.name).single(id);

	},

	/**
	 * This function makes sure the store is up to date. Should not be necessary but we ran into problems where tasks
	 * were out of date when viewed. This should always prevent that.
	 * @return {Promise<self>}
	 */
	checkState: async function() {
		await window.groupofficeCore.jmapds(this.entity.name).validateState();
		return this;
	},


	/**
	 * Get entities
	 * 
	 * Also see single() for fetching a single entity
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

		return window.groupofficeCore.jmapds(this.entity.name).get(ids).then((r) => {
			if(cb) {
				cb.call(scope, r.list, r.notFound);
			}

			return {
				entities: r.list,
				notFoundIds: r.notFound
			};
		});
		
	},


	// TODO
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
	 * @returns {Promise<Entity>}
	 */
	save : function(entity, id) {
		if(id) {
			entity.id = id;
			return window.groupofficeCore.jmapds(this.entity.name).update(entity);
		}else
		{
			return window.groupofficeCore.jmapds(this.entity.name).create(entity);
		}

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
		return window.groupofficeCore.jmapds(this.entity.name).destroy(id).then((response) => {
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

		const proms = [], response = {};

		if(params.create) {
			for (let id in params.create) {
				proms.push(
					window.groupofficeCore.jmapds(this.entity.name)
						.create(params.create[id], id)
						.then(e => {
							if(!response.created) {
								response.created = {};
							}
							response.created[id] = e;
						})
						.catch(setError => {
							if(!response.notCreated) {
								response.notCreated = {};
							}
							response.notCreated[setError.id] = setError;
						})
				);
			}
		}

		if(params.update) {
			for (let id in params.update) {
				params.update[id].id = id;
				proms.push(
					window.groupofficeCore.jmapds(this.entity.name)
						.update(params.update[id])
						.then(e => {
							if(!response.updated) {
								response.updated = {};
							}
							response.updated[e.id] = e;
						})
						.catch(setError => {
							if(!response.notUpdated) {
								response.notUpdated = {};
							}
							response.notUpdated[setError.id] = setError;
						})
				);
			}
		}

		if(params.destroy) {
			for (let id of params.destroy) {
				proms.push(
					window.groupofficeCore.jmapds(this.entity.name)
						.destroy(id)
						.then(e => {
							if(!response.destroyed) {
								response.destroyed = [];
							}
							response.destroyed.push(id);
						})
						.catch(setError => {
							if(!response.notDestroyed) {
								response.notUpdated = {};
							}
							response.notDestroyed[setError.id] = setError;
						})
				);
			}
		}

		return Promise.all(proms).then(() => {
			if(cb) {
				cb.call(scope || this, options, true, response);
			}
			return response;
		});


		//TODO Automatic state mis match??

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
	 * @returns {Promise<any>} Client call ID
	 */
	query : function(params, cb, scope) {

		const callId = window.groupofficeCore.jmapds(this.entity.name).nextCallId;
		let retProm = window.groupofficeCore.jmapds(this.entity.name)
			.query(params)
			.then(response => {
				if(cb) {
					cb.call(scope || this, response);
				}
				return response;
			})

		//todo there's got to be a better way to do this. Promises should be cancellable. Used in entityStoreProxy
		retProm.callId = callId;

		return retProm;

	}
});
