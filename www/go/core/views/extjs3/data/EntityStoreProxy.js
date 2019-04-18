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
	},

	entityStore: null,

	/**
	 * @cfg {Function} doRequest Abstract method that should be implemented in all subclasses.  <b>Note:</b> Should only be used by custom-proxy developers.
	 * (e.g.: {@link Ext.data.HttpProxy#doRequest HttpProxy.doRequest},
	 * {@link Ext.data.DirectProxy#doRequest DirectProxy.doRequest}).
	 */
	doRequest: function (action, rs, params, reader, callback, scope, options) {

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
//		if (action === Ext.data.Api.actions.read && this.activeRequest[action]) {
//			//console.trace();
//			go.Jmap.abort(this.activeRequest[action]);
//		}
//		this.activeRequest[action] = 

		if (params.sort) {
			params.sort = [params.sort + " " + params.dir];
			delete params.dir;
		}
		
		this.entityStore.query(params, function (response) {

			this.entityStore.get(response.ids, function (items) {
				var data = {
					total: response.total,
					records: items,
					success: true
				};

				this.activeRequest[action] = undefined;

				if (action === Ext.data.Api.actions.read) {
					this.onRead(action, o, data);
				} else {
					this.onWrite(action, o, data, rs);
				}
			}, this);

		}, this);


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
		this.watchRelations = {};

		var fields = this.getEntityFields();
		if (!fields.length) {
			cb.call(scope);
			return;
		}

		var promises = [];

		records.forEach(function (record) {
			fields.forEach(function (field) {
					promises.push(this._addRelation(field, record));			
			}, this);
		}, this);

		Promise.all(promises).then(function() {
			cb.call(scope);
		});		
	},


	/**
	 * Create a promise that resolves the relational record data.
	 * 
	 * @param {any} field 
	 * @param {*any} record 
	 * 
	 * @retrun {Promise}
	 */
	_addRelation : function(field, record) {

		var relation = this.entityStore.entity.relations[field.name];
		if(!relation) {
			return Promise.reject("Relation " + field.name + " not found for " + this.entityStore.entity.name);
		}
		var key = this._resolveKey(relation.fk, record), me = this;

		if(!key) {
			me._applyRelationEntity(field.name, record, null);
			return Promise.resolve(null);
		}

		this._watchRelation(relation.store, key);

		if(Ext.isArray(key)) {
			return go.Db.store(relation.store).get(key).then(function(entities){
				me._applyRelationEntity(field.name, record, entities);
			});
		}

		return go.Db.store(relation.store).get([key]).then(function(entities){
			me._applyRelationEntity(field.name, record, entities[0]);
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
	 * Applies the entity data to the record.
	 * It also supports a path like "customFields.user"
	 * 
	 * This will become
	 * {
	 * 	"customFields" => {
	 * 		"user" => data
	 * 	}
	 * }
	 * @param {*} key 
	 * @param {*} record 
	 * @param {*} entities 
	 */
	_applyRelationEntity : function(key, record, entities) {
		var parts = key.split("."),last = parts.pop(), current = record;

		parts.forEach(function(p) {
			if(!current[p]) {
				current[p] = {};
			}
			current = current[p];
		});

		current[last] = entities;
	},

	/**
	 * Resolves a key path eg. "customFields.user"
	 * 
	 * @param {*} key 
	 * @param {*} data 
	 */
	_resolveKey : function(key, data) {
		var parts = key.split(".");
						
		parts.forEach(function(p) {
			if(Ext.isArray(data)) {
				var arr = [];
				data.forEach(function(i) {
					arr.push(i[p]);
				});
				data = arr;
			} else
			{
				if(!Ext.isDefined(data[p])) {
					throw "Key of relation " + key + " does not exist?";
				}
				data = data[p];
			}
			if(!data) {
				return false;
			}
		});
		
		return data;
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
