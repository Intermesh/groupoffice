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
	onRead: function (action, o, response) {
		
		var result;
		try {
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
	}
});
