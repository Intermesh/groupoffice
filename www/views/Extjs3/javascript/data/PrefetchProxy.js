GO.data.PrefetchProxy = function(conn){
	GO.data.PrefetchProxy.superclass.constructor.call(this, conn);
	this.fields = conn.fields || [];
};

Ext.extend(GO.data.PrefetchProxy, Ext.data.HttpProxy, {


	onRead : function(action, o, response) {
		var result;
		try {
			result = o.reader.read(response);
		}catch(e){


			this.fireEvent('loadexception', this, o, response, e);

			this.fireEvent('exception', this, 'response', action, o, response, e);
			o.request.callback.call(o.request.scope, null, o.request.arg, false);
			return;
		}
		if (result.success === false) {


			this.fireEvent('loadexception', this, o, response);


			var res = o.reader.readResponse(action, response);
			this.fireEvent('exception', this, 'remote', action, o, res, null);
		}
		else {
			this.fireEvent('load', this, o, o.request.arg);
		}

		this.preFetchEntities(result.records, function () {
			o.request.callback.call(o.request.scope, result, o.request.arg, result.success);
		});
	},


	/**
	 * Prefetches all data for fields of type "promise".
	 */
	preFetchEntities: function (records, cb, scope) {

		var promiseFields = this.getPromiseFields();
		if (!promiseFields.length) {
			cb.call(scope);
			return;
		}

		var promises = [], me = this;

		records.forEach(function (record) {

			promiseFields.forEach(function(f) {
				promises.push(f.promise(record.json).then(function(data){
					record.data[f.name] = data;
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
	}


});