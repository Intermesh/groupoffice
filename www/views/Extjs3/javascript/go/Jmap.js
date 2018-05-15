go.Jmap = {

	requests: [],

	requestOptions: {},

	callId: 0,

	timeout: null,

	nextCallId: function () {
		this.callId++;

		return this.callId;
	},

	debug: function () {
		var clientCallId = "clientCallId-" + this.nextCallId();
		this.requests.push(['community/dev/Debugger/get', {}, clientCallId]);

		this.requestOptions[clientCallId] = {
			method: 'community/dev/Debugger/get',
			params: {}
		};
	},

	abort: function (clientCallId) {
		console.log("Abort request " + clientCallId);

		for (var i = 0, l = this.requests.length; i < l; i++) {
			if (this.requests[i][2] == clientCallId) {
				this.requests.splice(i, 1);
				break;
			}
		}

		delete this.requestOptions[clientCallId];
	},
	
	/**
	 * Find 
	 * @param {string} method
	 * @returns {Array|Boolean}
	 */
	findRequestByMethod: function(method) {
		for(var i = 0, l = this.requests.length; i < l; i++) {
			if(this.requests[i][0] == method) {
				return this.requests[i];
			}
		}
		
		return false;
	},
	
	get: function(cb, scope) {
		Ext.Ajax.request({
			url: BaseHref + 'jmap.php',
			method: 'GET',
			callback: function (opts, success, response) {
				var data;
				if(success && response.responseText) {
					data = Ext.decode(response.responseText);
				}
				cb.call(scope, data, opts, success, response);
			}
		});
	},
	
	downloadUrl: function(blobId) {
		if (!blobId) {
			return '';
		}
		return go.User.downloadUrl.replace('{blobId}', blobId);
	},
	upload : function(file, cfg) {
		if(Ext.isEmpty(file))
			return;
		function start() {
			Ext.Ajax.request({
				url: go.User.uploadUrl,
				method: 'PUT',
				useDefaultHeader: false,
				success: function(response, opts) {
					if(response.responseText) {
						data = Ext.decode(response.responseText);
					}
					cfg.success && cfg.success.call(this, data, response, opts)
				},
				failure: cfg.failure || Ext.emptyFn,
				progress: cfg.progress || Ext.emptyFn,
				headers: {
					'X-File-Name': file.name,
					'Content-Type': file.type,
					'X-File-LastModifed': Math.round(file['lastModified'] / 1000).toString()
				},
				xmlData: file, // just "data" wasn't available in ext
				scope:cfg.scope || this
			});
		}
		function hex(buffer) {
			var hexCodes = [],
				view = new DataView(buffer);
			for (var i = 0; i < view.byteLength; i += 4) {
			  var value = view.getUint32(i),
			   stringValue = value.toString(16),
			   padding = '00000000',
			   paddedValue = (padding + stringValue).slice(-padding.length);
			  hexCodes.push(paddedValue);
			}
			return hexCodes.join("");
		}
		var cryptoObj = window.crypto || window.msCrypto;
		if(cryptoObj && cryptoObj.subtle) {
			var reader = new FileReader();
				reader.onloadend = function(e) {
					cryptoObj.subtle.digest('SHA-1',e.target.result).then(function (hash) {
						Ext.Ajax.request({
							url: go.User.uploadUrl,
							headers: {'X-BlobId': hex(hash)},
							method: 'GET',
							success: function(response, opts) {
								if(response.status == 204) {
									start();
								}
								if(response.responseText) {
									data = Ext.decode(response.responseText);
									cfg.success && cfg.success.call(cfg.scope || this, data, response, opts);
								}
							}
						})
					});
				};
			reader.readAsArrayBuffer(file);
		} else {
			start();
		}
	},

	/**
	 * 
	 * 
	 * @param {type} options
	 * 
	 * An object containing:
	 * 
	 * method: jmap method
	 * params: jmap method parameters
	 * callback: function to call after request. Arghuments are options, success, response
	 * scope: callback function scope
	 * dispatchAfterCallback: dispatch the response after the callback. Defaults to false.
	 * 
	 * @returns {String}
	 */
	request: function (options) {
		if(!options.method) {
			throw "method is required";
		}

		var me = this;

		if (me.timeout) {
			clearTimeout(me.timeout);
		}

		var clientCallId = "clientCallId-" + this.nextCallId();

		this.requests.push([options.method, options.params || {}, clientCallId]);

		this.requestOptions[clientCallId] = options;

		me.timeout = setTimeout(function () {

			if (!me.requests.length) {
				//All requests aborted
				return;
			}

			me.debug();

			Ext.Ajax.request({
				url: BaseHref + 'jmap.php',
				method: 'POST',
				jsonData: me.requests,
				success: function (response, opts) {
					var responses = JSON.parse(response.responseText);

					responses.forEach(function (response) {

						//lookup request options by client ID
						var o = me.requestOptions[response[2]];
						if (!o) {
							//aborted
							return true;
						}
						if (response[1][0] == "error") {
							console.log('server-side JMAP failure', response);							
						}

						go.flux.Dispatcher.dispatch(response[0], response[1]);

						var success = response[1][0] !== "error";
						if (o.callback) {
							if (!o.scope) {
								o.scope = me;
							}
							o.callback.call(o.scope, o, success, response[1]);
						}

						//cleanup request options
						delete me.requestOptions[response[2]];
					});
				},
				failure: function (response, opts) {
					console.log('server-side failure with status code ' + response.status);
				}
			});

			me.requests = [];
			me.timeout = null;

		}, 0);

		return clientCallId;
	}
};
