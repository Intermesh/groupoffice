/* global Ext, go, BaseHref */

go.data.JmapProxy = Ext.extend(Ext.data.HttpProxy, {

	method: null,
	
	constructor: function (config) {
		config = config || {};

		this.fields = config.fields;
		this.method = config.method;

		go.data.JmapProxy.superclass.constructor.call(this, Ext.apply(config, {
			url: go.Jmap.getApiUrl() //we don't need this url but ext complains about it if it's missing
		}));

		this.conn = go.Jmap;
	},

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
		
		
		go.Jmap.request({
			method: this.method,
			params: params,
			scope: this,
			callback: function (options, success, response) {
				var data = {
					total: response.total,
					records: response.list,
					metaData: response.metaData,
					success: true
				};

				if (action === Ext.data.Api.actions.read) {
					this.onRead(action, o, data);
				} else {
					this.onWrite(action, o, data, rs);
				}
			}
		});
	},

	onRead: function (action, o, response) {

		var result;

		this.preFetchEntities(response.records, function(){
			result = o.reader.readRecords(response);

			if (result.success === false) {
				// @deprecated: fire old loadexception for backwards-compat.
				// TODO remove
				this.fireEvent('loadexception', this, o, response);

				// Get DataReader read-back a response-object to pass along to exception event
				var res = o.reader.readResponse(action, response);
				this.fireEvent('exception', this.store, 'remote', action, o, res, null);
			} else {
				this.fireEvent('load', this.store, o, o.request.arg);
			}
			o.request.callback.call(o.request.scope, result, o.request.arg, result.success);
		}, this);
	},

	// for promise field
	preFetchEntities: function(records, cb, scope) {
		var promiseFields = go.data.EntityStoreProxy.prototype.getPromiseFields.call(this);

		if (!promiseFields.length) {
			cb.call(scope);
			return;
		}

		var promises = [];

		records.forEach(function (record) {

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
	}
});

