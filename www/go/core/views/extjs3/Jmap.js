go.Jmap = {

	requests: [],

	requestOptions: {},

	callId: 0,

	timeout: null,

	paused: 0,

	/**
	 * Enable for XDEBUG profiling
	 */
	profile: false,

	nextCallId: function () {
		this.callId++;

		return this.callId;
	},

	debug: function () {
		this.scheduleRequest({
			method: 'community/dev/Debugger/get',
			params: {},
			callback: function(options, success, response, clientCallId) {

				var r;
				while(r = response.shift()) {
					var method = r.shift();
					r.push(clientCallId);
					console[method].apply(null, r);
				}

			}
		}).catch(function() {
			//ignore error
		});
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
	
	getApiUrl : function() {
		var url = BaseHref + 'api/jmap.php';

		if(this.profile) {
			url += '?XDEBUG_PROFILE=1';
		}
		return url;
	},
	
	get: function(cb, scope) {
		Ext.Ajax.request({
			url: this.getApiUrl(),
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
	
	downloadUrl: function(blobId, inline) {
		if (!blobId) {
			return '';
		}
		var url = go.User.downloadUrl.replace('{blobId}', blobId);
		if(inline) {
			url += '&inline=1';
		}

		return url;
	},

	thumbUrl: function(blobId, params) {
		if (!blobId) {
			return '';
		}
		var url = BaseHref + 'api/thumb.php?blob=' + blobId;

		for(var name in params) {
			url += '&' + name + '=' + encodeURIComponent(params[name]);
		}

		return url;

	},

	uploadQueue: [],
	upload : function(file, cfg) {
		if(Ext.isEmpty(file) || file.name === '.DS_Store') {
			cfg.callback && cfg.callback.call(cfg.scope || this, {upload:'skipped'});
			return;
		}
		// todo: group file upload in 1 notification
		this.uploadQueue.push(file);
		go.Notifier.toggleIcon('upload', true);

		var started_at = new Date();
		var notifyEl = go.Notifier.msg({
			persistent: true,
			iconCls: 'ic-file-upload',
			items:[
				{xtype:'box',html:'<b>'+file.name+'</b><span>...</span>'},
				{xtype:'progress',height:4,style:'margin: 7px 0'}
			],
			title: t('Uploading')+'...'
		});

		var transactionId = Ext.Ajax.request({url: go.User.uploadUrl,
			timeout: 4 * 60 * 60 * 100, //4 hours
			success: function(response) {
				if(cfg.success && response.responseText) {
					data = Ext.decode(response.responseText);
					notifyEl.setTitle('Upload complete');
					setTimeout(function () {
						go.Notifier.remove(notifyEl);
					}, 2000);
					cfg.success.call(cfg.scope || this,data, file);
				}
			},
			callback: function(response) {
				go.Jmap.uploadQueue.remove(file);
				if(Ext.isEmpty(this.uploadQueue)) {
					go.Notifier.toggleIcon('upload', false); //done
				}
				cfg.callback && cfg.callback.call(cfg.scope || this, response);
			},
			progress: function(e) {

				go.Notifier.notificationArea.expand();

					if (e.lengthComputable) {
					var seconds_elapsed = (new Date().getTime() - started_at.getTime() )/1000;
					var bytes_per_second =  seconds_elapsed ? e.loaded / seconds_elapsed : 0;
					var remaining_bytes = e.total - e.loaded;
					var seconds_remaining = seconds_elapsed ? remaining_bytes / bytes_per_second : '';
					var percentage = (e.loaded / e.total * 100 | 0);
					if(notifyEl) {
						notifyEl.setTitle(t('Uploading')+'... &bull; ' + percentage + '%');
						//notifyEl.items.items[0].getResizeEl().child('span', true).innerText = Math.round(seconds_remaining)+' '+t('seconds left');
						notifyEl.items.items[1].updateProgress(percentage/100);
					}
				}
				cfg.progress && cfg.progress.call(cfg.scope || this, e);
			},
			failure: function(response, options) {
				if(response.isAbort) {
					return;
				}
				if(cfg.failure && response.responseText) {
					data = Ext.decode(response.responseText);
					notifyEl.setTitle('Upload failed');
					cfg.failure.call(cfg.scope || this,data);
				} else if(response.status === 413) { // "Request Entity Too Large"
					notifyEl.setTitle('Upload failed: file too large');
					cfg.failure && cfg.failure.call(cfg.scope || this, response);
				} else {
					notifyEl.setTitle('Upload failed: Please check if the system is using the correct URL at System settings -> General -> URL.');
					cfg.failure && cfg.failure.call(cfg.scope || this, response);
				}

			},
			headers: {
				'X-File-Name': "UTF-8''" + encodeURIComponent(file.name),
				'Content-Type': file.type,
				'X-File-LastModified': Math.round(file['lastModified'] / 1000).toString()
			},
			xmlData: file // just "data" wasn't available in ext
		});

		//Abort upload if user destroys notification
		notifyEl.on("destroy", function() {
			console.warn("abort upload: " + transactionId);
			Ext.Ajax.abort(transactionId);
		});
	},
	
	/**
	 * Initializes Server Sent Events via EventSource. This function is called in MainLayout.onAuthenticated()
	 * 
	 * Note: disable this if you want to use xdebug because it will crash if you use SSE.
	 * 
	 * @returns {Boolean}
	 */
	sse : function() {
		try {
			if (!window.EventSource) {
				console.debug("Browser doesn't support EventSource");
				return false;
			}
			
			if(!go.User.eventSourceUrl) {
				console.debug("Not starting EventSource when xdebug is running");
				return false;
			}
			
			//filter out legacy modules
			var entities = go.Entities.getAll().filter(function(e) {return e.package != "legacy";});
			
			var url = go.User.eventSourceUrl + '?types=' + 
							entities.column("name").join(',');
			
			var source = new EventSource(url), me = this;
			
			source.addEventListener('state', function(e) {

				var data = JSON.parse(e.data);

				for(var entity in data) {
					var store = go.Db.store(entity);
					if(store) {
						(function(store) {
							store.getState().then(function(state) {
								if(!state || state == data[store.entity.name]) {
									//don't fetch updates if there's no state yet because it never was used in that case.
									return;
								}
								
								store.getUpdates();
							});
						})(store);
					}
				}
			}, false);

			source.addEventListener('open', function(e) {
				// Connection was opened.
			}, false);

			source.addEventListener('error', function(e) {
				if (e.readyState == EventSource.CLOSED) {
					// Connection was closed.					
					me.sse();
				}
			}, false);
		}
		catch(e) {
			console.error("Failed to start Server Sent Events. Perhaps the API URL in the system settings is invalid?", e);
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
	 * callback: Deprecated! Use the promise functionality. If you pass a callback you can't use the promise. Function to call after request. Arghuments are options, success, response.
	 * scope: callback function scope
	 * dispatchAfterCallback: dispatch the response after the callback. Defaults to false.
	 * 
	 * @returns {Promise} resolved with response
	 */
	request: function (options) {
		if(!options.method) {
			throw "method is required";
		}		

		if (this.timeout) {
			clearTimeout(this.timeout);
		}
		
		var promise = this.scheduleRequest(options);

		if(!this.paused) {
			this.continue();
		}
		if(!options.callback) {
			return promise;
		}
	},

	scheduleRequest: function(options) {
		var clientCallId = "clientCallId-" + this.nextCallId(), promise = new Promise(function(resolve, reject){
			options.resolve = resolve;
			options.reject = reject;
		});
		promise.callId = clientCallId;

		this.requests.push([options.method, options.params || {}, clientCallId]);
		this.requestOptions[clientCallId] = options;

		return promise;
	},

	/**
	 * Pause request execution
	 */
	pause : function() {
		this.paused++;
		if (this.timeout) {
			clearTimeout(this.timeout);
		}
	},

	/**
	 * Continue request event execution as the next macro task.
	 */
	continue: function() {
		if(this.paused>0) {
			this.paused--;
		}

		if(this.paused > 0)
		{
			return;
		}
		if (this.timeout) {
			clearTimeout(this.timeout);
		}
		var me = this;
		this.timeout = setTimeout(function() {
			me.processQueue();
		}, 0);
	},

	processQueue: function () {

		if (!this.requests.length) {
			//All requests aborted
			return;
		}

		if(GO.debug || GO.settings.config.debug) {
			this.debug();
		}

		Ext.Ajax.request({
			url: this.getApiUrl(),
			method: 'POST',
			jsonData: this.requests,
			scope: this,
			success: function (response, opts) {
				// try {
					var responses = JSON.parse(response.responseText);

					responses.forEach(function (response) {

						//lookup request options by client ID
						var o = this.requestOptions[response[2]], me = this;
						if (!o) {
							//aborted
							console.debug("Aborted");
							return true;
						}
						if (response[0] == "error") {
							console.error('server-side JMAP failure', response);							
						}

							var success = response[0] !== "error";
							if (o.callback) {
								if (!o.scope) {
									o.scope = this;
								}
								o.callback.call(o.scope, o, success, response[1], response[2]);
							} else {

								response[1].options = o;

								if (success) {
									o.resolve(response[1]);
								} else {
									o.reject(response[1]);
								}
							}

							delete me.requestOptions[response[2]];

					}, this);

				// } catch(e) {					
				// 	console.error(e,"server reponse:", response.responseText);

				// 	Ext.MessageBox.alert(t("Error"), t("An error occured on the server. The console shows details."))
				// }
			},
			failure: function (response, opts) {
				console.error('server-side failure with status code ' + response.status);
				console.error(response.responseText);

				for(var i = 0, l = opts.jsonData.length; i < l; i++) {
					var clientCallId = opts.jsonData[i][2];
					this.requestOptions[clientCallId].reject({message: response.responseText});
					delete this.requestOptions[clientCallId];
				}
				
				Ext.MessageBox.alert(t("Error"), t("Sorry, an error occurred") + ": " + response.responseText);
				
			}
		});

		this.requests = [];
		this.timeout = null;

	}
};
