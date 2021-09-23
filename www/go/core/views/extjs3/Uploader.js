(function() {
	var uploadNotification;

	go.Uploader = {

		resetUploadQueue: function() {
			this.uploadQueue = {
				totalBytes: 0,
				remainingBytes: 0,
				finished: 0,
				failed: 0,
				items: []
			};

			this.index = 0;
			this.abort = false;
		},

		getNotification : function(){
			uploadNotification = go.Notifier.msgByKey('upload');
			if(!uploadNotification) {
				//var finished = go.Uploader.uploadQueue.filter((obj) => obj.finished).length
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
							.replace('{finished}', go.Uploader.uploadQueue.finished)
							.replace('{total}', go.Uploader.uploadQueue.items.length) + ' ' + t('files');

						txt += "<br />" + go.Uploader.uploadQueue.currentItem.file.name + " (" + go.Uploader.uploadQueue.currentItem.progress + "%)"
						uploadNotification.items.get('fileCount').update(txt);
					},

					updateProgress : function() {
						var totalBytesRemaining = 0;
						for(var i = 0, l = go.Uploader.uploadQueue.items.length; i < l; i++) {
							var q = go.Uploader.uploadQueue.items[i];
							totalBytesRemaining += (q.hasOwnProperty('remainingBytes') ? q.remainingBytes : q.file.size);
						}

						var secondsElapsed = (new Date().getTime() - go.Uploader.startedAt.getTime()) / 1000,
						 loadedBytes = go.Uploader.uploadQueue.totalBytes - totalBytesRemaining,
						 bytesPerSecond = loadedBytes / secondsElapsed,
						 secondsRemaining = Math.ceil(totalBytesRemaining / bytesPerSecond),
						 totalPercentage = loadedBytes / go.Uploader.uploadQueue.totalBytes * 100 | 0,
						 title = t('Uploads') + ' &bull; ' + totalPercentage + '%'

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
								go.Uploader.abort = true;
								for(var i = 0, l = go.Uploader.uploadQueue.items.length; i < l; i++) {
									go.Uploader.uploadQueue.items[i].remainingBytes = 0;
									if(go.Uploader.uploadQueue.items[i].transactionId) {
										Ext.Ajax.abort(go.Uploader.uploadQueue.items[i].transactionId);
									} else
									{
										var cfg = go.Uploader.uploadQueue.items[i].cfg;
										cfg.callback && cfg.callback.call(cfg.scope || this, {isAbort: true});
										cfg.failure && cfg.failure.call(cfg.scope || this, {isAbort: true});
									}
								}

								this.finish();

							},
							scope:this
						}]
					// listeners: {
					// 	afterrender: function() {
					// 		setTimeout(function(){
					// 			uploadNotification.updateCount();
					// 		})
					// 	}
					// }

				}, 'upload');
			}

			return uploadNotification;
		},

		/**
		 *
		 * @param {File} file
		 * @param {Object} cfg
		 */
		addFile : function(file, cfg) {
			if(Ext.isEmpty(file) || file.name === '.DS_Store') {
				cfg.callback && cfg.callback.call(cfg.scope || this, {upload:'skipped'});
				return;
			}

			var notification, me = this;

			if(!go.Notifier.notificationsVisible() || me.notificationsTimeout) {
				//show only if uploading for more than 1s
				me.notificationsTimeout = setTimeout(function() {
					me.notificationsTimeout = null;
					if (me.uploadQueue.items.length > me.uploadQueue.finished) {
						go.Notifier.showNotifications();
						notification = me.getNotification();
						notification.updateCount();
					}
				}, 1000);
			}

			if(go.Jmap.capabilities.maxSizeUpload && file.size > go.Jmap.capabilities.maxSizeUpload) {
				cfg.callback && cfg.callback.call(cfg.scope || this, {upload:'skipped'});
				cfg.failure && cfg.failure.call(cfg.scope || this, data);

				go.Uploader.uploadQueue.failed++;

				go.Notifier.msg({
					persistent: false,
					iconCls: 'ic-file-upload',
					title: t('Upload failed'),
					description:'<b>'+file.name+'</b><p class="danger">' +t('File size exceeds the maximum of {max}.').replace('{max}', go.util.humanFileSize(go.Jmap.capabilities.maxSizeUpload)) + '</p>'
				});
				return;
			}

			go.Notifier.toggleIcon('upload', true);

			var queueItem = {
					file: file,
					cfg: cfg,
					finished: false,
					transactionId: null,
					progress: 0
				};

			go.Uploader.uploadQueue.totalBytes += file.size;
			go.Uploader.uploadQueue.items.push(queueItem);

			if(this.index === 0) {
				this.doUpload();
			}


		},

		index: 0,

		finish : function() {
			go.Notifier.toggleIcon('upload', false); //done
			if(uploadNotification) {
				go.Notifier.remove(uploadNotification);
			}
			uploadNotification = null;
			if(go.Uploader.uploadQueue.failed === 0) {
				go.Notifier.hideNotifications();
			}
			this.resetUploadQueue();
		},

		doUpload : function() {

			if(this.abort) {
				return;
			}

			if(!go.Uploader.uploadQueue.items[this.index]) {
				return this.finish();
			}

			if(this.index === 0) {
				this.startedAt = new Date();
			}

			queueItem = go.Uploader.uploadQueue.items[this.index];
			var cfg = queueItem.cfg, file = queueItem.file;
			go.Uploader.uploadQueue.currentItem = queueItem;
			this.index++;

			queueItem.transactionId = Ext.Ajax.request({
				url: go.User.uploadUrl,
				timeout: 4 * 60 * 60 * 1000, //4 hours
				scope: this,
				success: function (response) {
					if (cfg.success && response.responseText) {
						data = Ext.decode(response.responseText);
						cfg.success.call(cfg.scope || this, data, file);
					}
				},
				callback: function (response) {
					queueItem.finished = true;
					queueItem.remainingBytes = 0; // success or fail, we are done

					go.Uploader.uploadQueue.finished++;

					if(uploadNotification) {
						uploadNotification.updateCount();
					}

					if(!response.isAbort) {
						this.doUpload();
					}

					cfg.callback && cfg.callback.call(cfg.scope || this, response);
				},
				progress: function (e) {
					if (e.lengthComputable) {

						queueItem.remainingBytes = e.total - e.loaded;
						queueItem.progress = Math.ceil(e.loaded / e.total * 100 | 0);

						go.Uploader.uploadQueue.currentItem = queueItem;

						if(uploadNotification) {
							uploadNotification.updateProgress();
						}
					}
					cfg.progress && cfg.progress.call(cfg.scope || this, e);
				},
				failure: function (response) {

					if(response.isAbort) {
						return;
					}

					var data = response;
					text = '<b>' + Ext.util.Format.htmlEncode(file.name) + '</b><p class="danger">';

					if (cfg.failure && response.responseText) {
						data = Ext.decode(response.responseText);
					} else if (response.status === 413) { // "Request Entity Too Large"
						text += t('File size exceeds the maximum of {max}.').replace('{max}', go.util.humanFileSize(go.Jmap.capabilities.maxSizeUpload))
					} else if (!response.isAbort) {
						data = Ext.decode(response.responseText);
						if(data && data.detail) {
							text += data.detail;
						}
					}
					text += "</p>";

					go.Uploader.uploadQueue.failed++;

					go.Notifier.msg({
						persistent: false,
						iconCls: 'ic-file-upload',
						title: t('Upload failed'),
						description: text
					});

					if(uploadNotification) {
						uploadNotification.updateProgress();
					}

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
		}
	}
})();

go.Uploader.resetUploadQueue();