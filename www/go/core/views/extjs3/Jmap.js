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
	uploaderCollapsed: false,
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
			//var finsished = this.uploadQueue.filter((obj) => obj.finsished).length
			//create upload notification container
			uploadNotification = go.Notifier.msg({
				persistent: true,
				iconCls: 'ic-file-upload',
				title: t('Uploads'),
				addUpload: function(item) {
					var details = this.items.get('details');
					if(!details.rendered) {
						details.render();
					}
					var comp = details.add(new Ext.Panel(item));
					details.doLayout(false, true);
					return comp;
				},
				items:[
					{xtype:'progress',animate:true,itemId:'totalProgress', height:4,style:'margin: 7px 0'},
					{xtype:'panel', itemId:'details',collapsed:false, animCollapse: false, forceLayout:true, collapsible:true, title:'Details', listeners: {
							afterrender: function() {
								this.collapse();
							}
						}}
				],
				tbar: [{xtype:'tbtext',itemId: 'fileCount', html:t('{finsished} of {total}')
						.replace('{finsished}', 0)
						.replace('{total}', 1)},'->', {
					text:t('Abort all'),
					handler: function() {
						uploadNotification.setPersistent(false);
						for(var i = 0; i < this.uploadQueue.items.length; i++) {
							this.uploadQueue.items[i].remainingBytes = 0;
							Ext.Ajax.abort(this.uploadQueue.items[i].transactionId);
						}
					},
					scope:this
				}]

			}, 'upload');
		}

		if(go.Notifier.notificationArea.collapsed) {
			this.uploaderCollapsed = true;
			go.Notifier.notificationArea.expand();
		}

		if(this.capabilities.maxSizeUpload && file.size > this.capabilities.maxSizeUpload) {
			cfg.callback && cfg.callback.call(cfg.scope || this, {upload:'skipped'});
			cfg.failure && cfg.failure.call(cfg.scope || this, data);
			go.Jmap.uploadQueue.failed++;
			uploadNotification.addUpload({
				xtype:'panel',
				title: t('Upload failed'),
				html:'<b>'+file.name+'</b><p class="danger">' +t('File size exceeds the maximum of {max}.').replace('{max}', go.util.humanFileSize(this.capabilities.maxSizeUpload)) + '</p>'
			});

			uploadNotification.items.get('details').expand();

			return;
		}

		go.Notifier.toggleIcon('upload', true);

		//start upload
		uploadNotification.setPersistent(true); // cant close during upload
		var started_at = new Date(),
			queueItem = {
				file: file,
				finished: false,
				transactionId: null
			},
			notifyEl = uploadNotification.addUpload({
				items:[
					{xtype:'box',html:'<b>'+Ext.util.Format.htmlEncode(file.name)+'</b><em>...</em>'},
					{xtype:'progress',animate:true,itemId:'bar',height:4,style:'margin: 7px 0'}
				],
				title: t('Pending')+'...',
				buttons: [{
					text:t('Abort'),
					handler: function() {
						queueItem.remainingBytes = 0;
						Ext.Ajax.abort(queueItem.transactionId);
					}
				}]
			});


		queueItem.transactionId = Ext.Ajax.request({
			url: go.User.uploadUrl,
			timeout: 4 * 60 * 60 * 1000, //4 hours
			success: function (response) {
				if (cfg.success && response.responseText) {
					data = Ext.decode(response.responseText);
					notifyEl.setTitle(t('Upload complete'));
					setTimeout(function () {
						go.Notifier.remove(notifyEl);
					}, 2000);
					cfg.success.call(cfg.scope || this, data, file);
				}
			},
			callback: function (response) {
				queueItem.finished = true;
				queueItem.remainingBytes = 0; // success or fail, we are done
				notifyEl.buttons[0].hide();
				uploadNotification.getTopToolbar().items.get('fileCount').update(t('{finsished} of {total}')
					.replace('{finsished}', ++go.Jmap.uploadQueue.finished)
					.replace('{total}', go.Jmap.uploadQueue.items.length) + ' ' + t('files'));
				if (go.Jmap.uploadQueue.items.length <= go.Jmap.uploadQueue.finished) {
					go.Notifier.toggleIcon('upload', false); //done
					if(go.Jmap.uploadQueue.failed == 0) { // noneFailed1
						go.Notifier.remove(uploadNotification);
					}
					uploadNotification.setPersistent(false);
					go.Notifier.notificationArea[go.Jmap.uploaderCollapsed ? 'collapse' : 'expand']();
					go.Jmap.uploaderCollapsed = false; // default only set to true when collapsed on first upload
				}
				cfg.callback && cfg.callback.call(cfg.scope || this, response);
			},
			progress: function (e) {
				if (e.lengthComputable) {
					var seconds_elapsed = (new Date().getTime() - started_at.getTime()) / 1000;
					var bytes_per_second = seconds_elapsed ? e.loaded / seconds_elapsed : 0;
					queueItem.remainingBytes = e.total - e.loaded;
					queueItem.remainingSeconds = seconds_elapsed ? queueItem.remainingBytes / bytes_per_second : '';
					var percentage = (e.loaded / e.total * 100 | 0);
					if (notifyEl) {
						notifyEl.setTitle(t('Uploading...') + ' &bull; ' + percentage + '%');
						var box = notifyEl.items.get(0).getResizeEl();
						if (box)
							box.child('em', true).innerText = Math.round(queueItem.remainingSeconds) + t('s');
						notifyEl.items.get('bar').updateProgress(percentage / 100);
					}

					if(uploadNotification) {
						var totalBytesRemaining = 0;
						for(var i = 0; i < go.Jmap.uploadQueue.items.length; i++) {
							var q = go.Jmap.uploadQueue.items[i];
							totalBytesRemaining += (q.hasOwnProperty('remainingBytes') ? q.remainingBytes : q.file.size);
						}
						var loadedBytes = go.Jmap.uploadQueue.totalBytes - totalBytesRemaining;
						var totalPercentage = loadedBytes / go.Jmap.uploadQueue.totalBytes * 100 | 0;
						uploadNotification.setTitle(t('Uploads') + ' &bull; ' + totalPercentage + '%');
						uploadNotification.items.get('totalProgress').updateProgress(totalPercentage / 100)
					}
				}
				cfg.progress && cfg.progress.call(cfg.scope || this, e);
			},
			failure: function (response, options) {

				var data = response,
					title = response.isAbort ? t('Upload aborted') : t('Upload failed');
				text = '<b>' + Ext.util.Format.htmlEncode(file.name) + '</b><p class="danger">';

				if (cfg.failure && response.responseText) {
					data = Ext.decode(response.responseText);
				} else if (response.status === 413) { // "Request Entity Too Large"
					text += t('File too large');
				} else if (!response.isAbort) {
					text += 'Please check if the system is using the correct URL at System settings -> General -> URL.';
				}
				text += "</p>";

				go.Jmap.uploadQueue.failed++;
				notifyEl.setTitle(title);
				notifyEl.items.get(0).update(text);
				cfg.failure && cfg.failure.call(cfg.scope || this, data);

				uploadNotification.items.get('details').expand();
				go.Jmap.uploaderCollapsed = false;

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
		uploadNotification.getTopToolbar().items.get('fileCount').update(t('{finsished} of {total}')
			.replace('{finsished}', this.uploadQueue.finished)
			.replace('{total}', this.uploadQueue.items.length) + ' ' + t('files'));
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
				console.debug("Server Sent Events (EventSource) is disabled on the server.");
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
			this.timeout = null;
		}
	},

	/**
	 * Continue request event execution as the next macro task.
	 */
	continue: function() {
		if(this.paused > 0) {
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
