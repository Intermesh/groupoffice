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

	/**
	 * 
	 * 
	 * @param {type} options
	 * 
	 * An object containing:
	 * 
	 * method: jmap method
	 * params: jmap method parameters
	 * callback: function to call after request
	 * scope: callback function scope
	 * dispatchAfterCallback: dispatch the response after the callback. Defaults to false.
	 * 
	 * @returns {String}
	 */
	request: function (options) {

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