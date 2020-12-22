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

	/**
	 * Server capabilities. It's set when auth request completes in authentication manager
	 */
	capabilities : {
		maxSizeUpload: 100*1000*1024,

		maxConcurrentUpload: 4,

		maxSizeRequest:  100*1000*1024,

		maxConcurrentRequests: 4,

		maxCallInRequest: 10,

		maxObjectsInGet: 100,

		maxObjectsInSet: 1000
	},

	nextCallId: function () {
		this.callId++;

		return this.callId;
	},

	debug: function () {
		this.scheduleRequest({
			method: 'community/dev/Debugger/get',
			params: {},
			callback: function(options, success, response, clientCallId) {
				this.processDebugResponse(response, clientCallId);
			},
			scope: this
		}).catch(function() {
			//ignore error
		});
	},

	processDebugResponse : function(response, clientCallId) {
		var r;
		while(r = response.shift()) {
			var method = r.shift();
			r.push(clientCallId);
			//escape % for console.log
			r = r.map(function(i) {
				if(Ext.isString(i)) {
					i = i.replace(/%/g, "%%");
				}
				return i;
			});
			console[method].apply(null, r);
		}
	},

	abort: function (clientCallId) {
		console.warn("Abort request " + clientCallId);

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

	uploadQueue: {},

	resetUploadQueue: function() {
		this.uploadQueue = {
			totalBytes: 0,
			remainingBytes: 0,
			finished: 0,
			failed: 0,
			items: []
		};
	},

	/**
	 *
	 * @param {File} file
	 * @param {Object} cfg
	 */
	upload : function(file, cfg) {
		if(Ext.isEmpty(file) || file.name === '.DS_Store') {
			cfg.callback && cfg.callback.call(cfg.scope || this, {upload:'skipped'});
			return;
		}
		var uploadNotification = go.Notifier.msgByKey('upload');
		if(!uploadNotification) {
			this.resetUploadQueue();
			//var finished = this.uploadQueue.filter((obj) => obj.finished).length
			//create upload notification container
			uploadNotification = go.Notifier.msg({
				persistent: true,
				iconCls: 'ic-file-upload',
				title: t('Uploads'),

				updateCount: function() {

					if(!this.rendered) {
						return;
					}

					var txt = t('{finished} of {total}')
						.replace('{finished}', go.Jmap.uploadQueue.finished)
						.replace('{total}', go.Jmap.uploadQueue.items.length) + ' ' + t('files');

					txt += "<br />" + go.Jmap.uploadQueue.currentItem.file.name + " (" + go.Jmap.uploadQueue.currentItem.progress + "%)"
					uploadNotification.items.get('fileCount').update(txt);
				},

				updateProgress : function() {
					var totalBytesRemaining = 0;
					for(var i = 0, l = go.Jmap.uploadQueue.items.length; i < l; i++) {
						var q = go.Jmap.uploadQueue.items[i];
						totalBytesRemaining += (q.hasOwnProperty('remainingBytes') ? q.remainingBytes : q.file.size);
					}

					var secondsElapsed = (new Date().getTime() - started_at.getTime()) / 1000;
					var loadedBytes = go.Jmap.uploadQueue.totalBytes - totalBytesRemaining;
					var bytesPerSecond = loadedBytes / secondsElapsed;
					var secondsRemaining = Math.ceil(totalBytesRemaining / bytesPerSecond);
					var totalPercentage = loadedBytes / go.Jmap.uploadQueue.totalBytes * 100 | 0;
					var title = t('Uploads') + ' &bull; ' + totalPercentage + '%';

					if(totalPercentage > 0) {
						title += '&bull; ' + go.util.Format.timeRemaining(secondsRemaining);
					}
					uploadNotification.setTitle(title);
					uploadNotification.items.get('totalProgress').updateProgress(totalPercentage / 100);
				},

				items:[
					{
						xtype:'box',
						style: 'padding: ' + dp(16) + 'px',
						itemId: 'fileCount',
						html: t('{finished} of {total}')
							.replace('{finished}', 0)
							.replace('{total}', 1)
					},
					{
						xtype:'progress',
						animate:false,
						itemId:'totalProgress',
						height: 4,
						style: 'margin: ' + dp(8) + 'px 0'
					}
				],
				bbar: [
					'->',
					{
					text: t('Abort'),
					handler: function() {
						uploadNotification.setPersistent(false);
						for(var i = 0, l = this.uploadQueue.items.length; i < l; i++) {
							this.uploadQueue.items[i].remainingBytes = 0;
							Ext.Ajax.abort(this.uploadQueue.items[i].transactionId);
						}
					},
					scope:this
				}],
				listeners: {
					afterrender: function() {
						setTimeout(function(){
							uploadNotification.updateCount();
						})
					}
				}

			}, 'upload');
		}

		if(!go.Notifier.notificationsVisible()) {
			//show only if uploading for more than 1s
			setTimeout(function() {
				if (go.Jmap.uploadQueue.items.length > go.Jmap.uploadQueue.finished) {
					go.Notifier.showNotifications();
				}
			}, 1000);
		}

		if(this.capabilities.maxSizeUpload && file.size > this.capabilities.maxSizeUpload) {
			cfg.callback && cfg.callback.call(cfg.scope || this, {upload:'skipped'});
			cfg.failure && cfg.failure.call(cfg.scope || this, data);
			go.Jmap.uploadQueue.failed++;
			go.Notifier.msg({
				persistent: true,
				iconCls: 'ic-file-upload',
				title: t('Upload failed'),
				html:'<b>'+file.name+'</b><p class="danger">' +t('File size exceeds the maximum of {max}.').replace('{max}', go.util.humanFileSize(this.capabilities.maxSizeUpload)) + '</p>'
			});

			uploadNotification.rendered ? uploadNotification.items.get('details').expand() : uploadNotification.items.get('details').collapsed = false;

			return;
		}

		go.Notifier.toggleIcon('upload', true);

		//start upload
		uploadNotification.setPersistent(true); // cant close during upload
		var started_at = new Date(),
			queueItem = {
				file: file,
				finished: false,
				transactionId: null,
				progress: 0
			};

		go.Jmap.uploadQueue.currentItem = queueItem;

		queueItem.transactionId = Ext.Ajax.request({
			url: go.User.uploadUrl,
			timeout: 4 * 60 * 60 * 1000, //4 hours
			success: function (response) {
				if (cfg.success && response.responseText) {
					data = Ext.decode(response.responseText);
					cfg.success.call(cfg.scope || this, data, file);
				}
			},
			callback: function (response) {
				queueItem.finished = true;
				queueItem.remainingBytes = 0; // success or fail, we are done

				go.Jmap.uploadQueue.finished++;

				uploadNotification.updateCount();

				if (go.Jmap.uploadQueue.items.length <= go.Jmap.uploadQueue.finished) {
					go.Notifier.toggleIcon('upload', false); //done
					if(go.Jmap.uploadQueue.failed == 0) { // noneFailed1
						go.Notifier.remove(uploadNotification);
						go.Notifier.hideNotifications();
					}
					uploadNotification.setPersistent(false);
				}
				cfg.callback && cfg.callback.call(cfg.scope || this, response);
			},
			progress: function (e) {
				if (e.lengthComputable) {

					queueItem.remainingBytes = e.total - e.loaded;
					queueItem.progress = Math.ceil(e.loaded / e.total * 100 | 0);

					go.Jmap.uploadQueue.currentItem = queueItem;

					if(uploadNotification) {
						uploadNotification.updateProgress();
					}
				}
				cfg.progress && cfg.progress.call(cfg.scope || this, e);
			},
			failure: function (response, options) {

				if(response.isAbort) {
					return;
				}

				var data = response;
				text = '<b>' + Ext.util.Format.htmlEncode(file.name) + '</b><p class="danger">';

				if (cfg.failure && response.responseText) {
					data = Ext.decode(response.responseText);
				} else if (response.status === 413) { // "Request Entity Too Large"
					text += t('File too large');
				} else if (!response.isAbort) {
					data = Ext.decode(response.responseText);
					if(data && data.detail) {
						text += data.detail;
					}
				}
				text += "</p>";

				go.Jmap.uploadQueue.failed++;

				go.Notifier.msg({
					iconCls: 'ic-file-upload',
					title: t('Upload failed'),
					html: text
				});

				uploadNotification.updateProgress();

				go.Notifier.showNotifications();

				cfg.failure && cfg.failure.call(cfg.scope || this, data);

			},
			headers: {
				'X-File-Name': "UTF-8''" + encodeURIComponent(file.name),
				'Content-Type': file.type,
				'X-File-LastModified': Math.round(file['lastModified'] / 1000).toString()
			},
			xmlData: file // just "data" wasn't available in ext
		});
		this.uploadQueue.totalBytes += file.size;
		this.uploadQueue.items.push(queueItem);

		uploadNotification.updateCount();
	},

	/**
	 * When SSE is disabled we'll poll the server for changes every 2 minutes.
	 * This also keeps the token alive. Which expires in 30M.
	 */
	poll : function() {
		console.log("Start check for updates every 60s.");
		setInterval(function() {
			go.Db.stores().forEach(function(store) {
				store.getState().then(function(state) {
					if (state)
						store.getUpdates();
				});
			})
		}, 5000);
	},
	
	/**
	 * Initializes Server Sent Events via EventSource. This function is called in MainLayout.onAuthenticated()
	 * 
	 * Note: disable this if you want to use xdebug because it will crash if you use SSE.
	 * 
	 * @returns {Boolean}
	 */
	sse : function() {
		// return;
		try {
			if (!window.EventSource) {
				console.debug("Browser doesn't support EventSource");
				this.poll();
				return false;
			}
			
			if(!go.User.eventSourceUrl) {
				console.debug("Server Sent Events (EventSource) is disabled on the server.");
				this.poll();
				return false;
			}

			console.debug("Starting SSE");
			
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
			//
			// source.addEventListener('open', function(e) {
			// 	// Connection was opened.
			// 	console.log("SSE running");
			// 	console.log(source);
			// }, false);

			// source.addEventListener('error', function(e) {
			// 	console.warn(source);
			// 	if (source.readyState == EventSource.CLOSED) {
			// 		// Connection was closed.
			//
			// 	} else
			// 	{
			// 		console.error(e);
			// 	}
			//
			// }, false);
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
			this.timeout = null;
		}
		
		var promise = this.scheduleRequest(options);

		if(!this.paused) {
			this.delayedProcessQueue();
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

		// if(!GO.pauseCalls) {
		// 	GO.pauseCalls = 1;
		// } else {
		// 	GO.pauseCalls++
		// }
		// console.trace("pause", GO.pauseCalls);

		this.paused++;
		if (this.timeout) {
			clearTimeout(this.timeout);
			this.timeout = null;
		}
	},

	/**
	 * Continue request event execution as the next macro task.
	 */
	continue: function() {

		// if(!GO.continueCalls) {
		// 	GO.continueCalls = 1;
		// } else {
		// 	GO.continueCalls++
		// }
		// console.trace("continue", GO.continueCalls);

		if(this.paused > 0) {
			this.paused--;
		}

		if(this.paused > 0)
		{
			return;
		}

		this.delayedProcessQueue();
	},

	delayedProcessQueue : function() {
		if (this.timeout) {
			clearTimeout(this.timeout);
		}
		var me = this;
		this.timeout = setTimeout(function() {
			me.processQueue();
		}, 0);
	},

	processQueue: function () {

		this.timeout = null;

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

						var clientCallId = response[2];

						//lookup request options by client ID
						var o = this.requestOptions[clientCallId], me = this;
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
								o.callback.call(o.scope, o, success, response[1], clientCallId);
							} else {

								response[1].options = o;

								if (success) {
									o.resolve(response[1]);
								} else {
									o.reject(response[1]);
								}
							}

							delete me.requestOptions[clientCallId];

					}, this);

				// } catch(e) {					
				// 	console.error(e,"server reponse:", response.responseText);

				// 	Ext.MessageBox.alert(t("Error"), t("An error occured on the server. The console shows details."))
				// }
			},
			failure: function (response, opts) {
				if(response.isAbort) {
					console.warn('Connection aborted', response);
					return;
				}
				console.error('server-side failure with status code ' + response.status);
				console.error(response);

				for(var i = 0, l = opts.jsonData.length; i < l; i++) {
					var clientCallId = opts.jsonData[i][2];
					this.requestOptions[clientCallId].reject({message: response.responseText});
					delete this.requestOptions[clientCallId];
				}
				
				Ext.MessageBox.alert(t("Error"), t("Sorry, an error occurred") + ": " + response.responseText);
				
			}
		});

		this.requests = [];

	}
};
