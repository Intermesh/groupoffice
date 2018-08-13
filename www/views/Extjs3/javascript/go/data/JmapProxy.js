go.data.JmapProxy = Ext.extend(Ext.data.HttpProxy, {

	constructor: function (config) {
		config = config || {};
		
		this.entityStore = config.entityStore;
		
		
		go.data.JmapProxy.superclass.constructor.call(this, Ext.apply(config, {
			url: BaseHref + 'jmap.php' //we don't need this url but ext complains about it if it's missing
		}));
		
		this.conn = go.Jmap;
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

		var me = this;
		
		// If a currently running read request is found, abort it
		if (action == Ext.data.Api.actions.read && this.activeRequest[action]) {
			//console.trace();
			go.Jmap.abort(this.activeRequest[action]);
		}
		this.activeRequest[action] = me.getItemList(this.entityStore.entity.name + "/query", params, function (getItemListResponse) {
			me.entityStore.get(getItemListResponse.ids, function (items) {
				var data = {
					total: getItemListResponse.total,
					records: items,
					success: true
				};
								
				me.activeRequest[action] = undefined;
				
				if (action === Ext.data.Api.actions.read) {
					me.onRead(action, o, data);
				} else {
					me.onWrite(action, o, data, rs);
				}
			});

		});


	},
	
//exact copy from httpproxy only it uses o.reader.readRecords instead.
	onRead: function (action, o, response, entitiesFetched) {
		
		var result;
		try {
			if(!entitiesFetched && this.fetchEntities(action, o, response)) {
				return;
			}
			result = o.reader.readRecords(response);			
		} catch (e) {
			// @deprecated: fire old loadexception for backwards-compat.
			// TODO remove
			this.fireEvent('loadexception', this, o, response, e);
			
			console.log(e);

			this.fireEvent('exception', this, 'response', action, o, response, e);
			o.request.callback.call(o.request.scope, null, o.request.arg, false);
			return;
		}
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
	},

	getItemList: function (method, params, callback) {	
		
		//transfort sort parameters to jmap style
		if(params.sort) {
			params.sort = [params.sort + " " + params.dir];
			delete params.dir;
		}
		
		
		
		return go.Jmap.request({
			method: method,
			params: params,
			callback: function(options, success, response) {
				callback.call(this, response);
			}
		});
	},
	
	
	//Prefetches all data of type go.data.types.Entity defined in go.Entities
	fetchEntities : function(action, o, response) {		
		
		var fields = this.getEntityFields(o);
		if(!fields.length) {				
		 return false;
		}	
		
		var count = 0, called = 0, me = this;
		
		function callback(options, success, result) {
			called++;			
			if(count == called) {				
				me.onRead.call(me, action, o, response, true)
			}
		}
		
		//group entities by type so one single request can be made
		var types = {};
		
		response.records.forEach(function(r) {
			fields.forEach(function(f) {	
				
				var key = f.type.getKey.call(f, r);				
				if(!key) {
					return true;
				}				
				
				if(!types[f.type.entity]) {
					types[f.type.entity] = [];
				}
				
				if(types[f.type.entity].indexOf(key) == -1) {
					types[f.type.entity].push(key);
				}
			});
		});
		
		for(var entity in types) {
			count++; //count number of requests and check if an equal number of callbacks has been called before proceeding with onRead.
			var store = go.Stores.get(entity);
			store.get(types[entity], callback);
		}
		
		return count > 0;
	},
	
	getEntityFields : function(o) {
		
		var f = [],  Record = o.reader.recordType,
            fields = Record.prototype.fields;
		
		fields.each(function(field) {
			if(field.type.entity) {				
				f.push(field);				
			}
		});
		
		return f;
	}
});
