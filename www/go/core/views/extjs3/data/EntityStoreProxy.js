/* global Ext, go, BaseHref */

go.data.EntityStoreProxy = Ext.extend(Ext.data.HttpProxy, {

	constructor: function (config) {
		config = config || {};

		this.entityStore = Ext.isString(config.entityStore) ? go.Db.store(config.entityStore) : config.entityStore;		

		this.fields = config.fields;

		go.data.EntityStoreProxy.superclass.constructor.call(this, Ext.apply(config, {
			url: go.Jmap.getApiUrl() //we don't need this url but ext complains about it if it's missing
		}));

		this.conn = go.Jmap;

		this.watchRelations = {};

		this.store = config.store;
	},

	entityStore: null,

	/**
	 * @cfg {Function} doRequest Abstract method that should be implemented in all subclasses.  <b>Note:</b> Should only be used by custom-proxy developers.
	 * (e.g.: {@link Ext.data.HttpProxy#doRequest HttpProxy.doRequest},
	 * {@link Ext.data.DirectProxy#doRequest DirectProxy.doRequest}).
	 */
	doRequest: function (action, rs, params, reader, callback, scope, options) {

		//Reset watchRelation if not adding records
		if(!options.add) {			
			this.watchRelations = {};
		}


		var o = {
			request: {
				callback: callback,
				scope: scope,
				arg: options
			},
			reader: reader,
			//callback : this.createCallback(action, rs),
			scope: this
		};


		// If a currently running read request is found, abort it
		if (action === Ext.data.Api.actions.read && this.activeRequest[action]) {
			//todo there's got to be a better way to do this. Promises should be cancellable. Used in entityStoreProxy
			go.Jmap.abort(this.activeRequest[action]);
		}


		if (params.dir && params.sort) {
			params.sort = [{
				property: params.sort,
				isAscending: params.dir === "ASC"
			}];
			delete params.dir;
		}
		
		var me = this;
		var promise = this.entityStore.query(params);

		me.activeRequest[action] = promise.callId;
		var clientCallId = promise.callId;

		promise.then(function (response) {

			me.store.hasMore = response.hasMore;

			var getPromise = me.entityStore.get(response.ids).then(function(result) {

				//check if request wasn't replaced
				if(me.activeRequest[action] != clientCallId) {
					console.warn("Not handling load callback because another request replaced this one: " + me.activeRequest[action] + '!=' + clientCallId);
					return;
				}

				var data = {
					total: response.total,
					records: result.entities,
					success: true
				};
	
				me.activeRequest[action] = undefined;
	
				if (action === Ext.data.Api.actions.read) {
					me.onRead(action, o, data);
				} else {
					me.onWrite(action, o, data, rs);
				}
				
			});

			return getPromise;
		}).catch(function(response) {
			//hack to pass error message to load callback in Store.js
			o.request.arg.error = response;
			var ret = me.fireEvent('exception', me.store, 'remote', action, o, response, null);
			o.request.callback.call(o.request.scope, response, o.request.arg, false);
		});


	},

//exact copy from httpproxy only it uses o.reader.readRecords instead.
	onRead: function (action, o, response) {

		var result;

		this.preFetchEntities(response.records, function () {
			//		try {
			result = o.reader.readRecords(response);


//		} catch (e) {
//			// @deprecated: fire old loadexception for backwards-compat.
//			// TODO remove
//			this.fireEvent('loadexception', this, o, response, e);
//			
//			console.log(e);
//
//			this.fireEvent('exception', this, 'response', action, o, response, e);
//			o.request.callback.call(o.request.scope, null, o.request.arg, false);
//			return;
//		}
			if (result.success === false) {
				// @deprecated: fire old loadexception for backwards-compat.
				// TODO remove
				this.fireEvent('loadexception', this, o, response);

				// Get DataReader read-back a response-object to pass along to exception event
				var res = o.reader.readResponse(action, response);
				this.fireEvent('exception', this, 'remote', action, o, res, null);
			} else {
				this.fireEvent('load', this, o, o.request.arg);
			}
			// TODO refactor onRead, onWrite to be more generalized now that we're dealing with Ext.data.Response instance
			// the calls to request.callback(...) in each will have to be made identical.
			// NOTE reader.readResponse does not currently return Ext.data.Response
			o.request.callback.call(o.request.scope, result, o.request.arg, result.success);
		}, this);
	},

	/**
	 * Prefetches all data for fields of type "relation". 
	 */
	preFetchEntities: function (records, cb, scope) {
		

		var fields = this.getEntityFields(), hasFields = fields.length;
		var promiseFields = this.getPromiseFields();
		if (!hasFields && !promiseFields.length) {
			cb.call(scope);
			return;
		}

		var promises = [], me = this;

		records.forEach(function (record) {

			if(hasFields) {
				promises.push(go.Relations.get(this.entityStore, record, fields).then(function(result) {
					for(var store in result.watch) {
						result.watch[store].forEach(function(key) {
								me._watchRelation(store, key);
						});
					}
				}).catch(function(error) {
					console.error(error);
				}));
			}

			promiseFields.forEach(function(f) {
				promises.push(f.promise(record).then(function(data) {
					go.util.Object.applyPath(record, f.name, data);
				}));
			});

		}, this);		
		
		Promise.all(promises).catch(function(e) {
			console.error(e);
		}).finally(function(){
			cb.call(scope);
		});
	},

	/**
	 * Keeps record of relational entity stores and their id's. go.data.Stores uses this collection to listen for changes
	 * 
	 * @param {string} entity 
	 * @param {int} key 
	 */
	_watchRelation : function(entity, key) {

		if(!this.watchRelations[entity]) {
			this.watchRelations[entity] = [];
		}

		if(this.watchRelations[entity].indexOf(key) === -1) {
			this.watchRelations[entity].push(key);
		}
	},

	/**
	 * Get all fields that should resolve a related entity
	 */
	getPromiseFields: function () {
		var f = [];

		this.fields.forEach(function (field) {
			if(Ext.isString(field.type)) {
				field.type = Ext.data.Types[field.type.toUpperCase()];
			}
			if (field.type && field.type.promise) {
				f.push(field);
			}
		});

		return f;
	},

	/**
	 * Get all fields that should resolve a related entity
	 */
	getEntityFields: function () {
		var f = [];

		this.fields.forEach(function (field) {
			if(Ext.isString(field.type)) {
				field.type = Ext.data.Types[field.type.toUpperCase()];
			}
			if (field.type && field.type.prefetch) {
				f.push(field);
			}
		});

		return f;
	}
});
