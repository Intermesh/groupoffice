(function () {
	function fallbackCopyTextToClipboard(text) {
		var textArea = document.createElement("textarea");
		textArea.value = text;
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			var msg = successful ? 'successful' : 'unsuccessful';
			console.log('Fallback: Copying text command was ' + msg);
		} catch (err) {
			console.error('Fallback: Oops, unable to copy', err);
		}

		document.body.removeChild(textArea);
	}

	go.util = {
		copyTextToClipboard: function (text) {
			if (!navigator.clipboard) {
				fallbackCopyTextToClipboard(text);
				return;
			}
			navigator.clipboard.writeText(text).then(function () {
				console.log('Async: Copying to clipboard was successful!');
			}, function (err) {
				console.error('Async: Could not copy text: ', err);
			});
		},

		mergeObjects: function (a, b) {
			for (var item in b) {
				if (a[item]) {
					if (typeof b[item] === 'object' && !b[item].length) {
						GO.util.mergeObjects(a[item], b[item]);
					} else {
						if (typeof a[item] === 'object' || typeof b[item] === 'object') {
							a[item] = [].concat(a[item], b[item]);
						} else {
							a[item] = [a[item], b[item]];  // assumes that merged members that are common should become an array.
						}
					}
				} else {
					a[item] = b[item];
				}
			}
			return a;
		},
		
		/**
		 * Convert bytes to a user readable format
		 * 
		 * @param int bytes
		 * @param boolean conventionDecimal
		 * @return {String}
		 */
		humanFileSize: function(bytes, conventionDecimal) {
			 var thresh = conventionDecimal ? 1000 : 1024;
			 if(Math.abs(bytes) < thresh) {
				  return bytes + ' B';
			 }
			 var units = conventionDecimal
				  ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
				  : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
			 var u = -1;
			 do {
				  bytes /= thresh;
				  ++u;
			 } while(Math.abs(bytes) >= thresh && u < units.length - 1);
			 return bytes.toFixed(1)+' '+units[u];
		},

		/**
		 * Get the class for the content type icon based on mimetype or extension
		 * @param {string} contentType
		 * @param {string} extension
		 * @returns {string} class name used in _icons.scss
		 */
		contentTypeClass: function(contentType, filename) {
			var icon, extension = filename.split('.');
			if(extension.length > 1) {
				icon = extension[extension.length-1];
			} else {
				icon = 'folder';
			}
			if(contentType !== null) {
				// todo: map contentTypes
			}
			return icon;
		},

		/**
		 * PRIVATE
		 * Needed for go.util.getDiff function to compare differences between 2 objects
		 * 
		 * @param {object} a
		 * @param {object} b
		 * @param {type} node
		 * @return {undefined}
		 */
		recursiveDiff: function(a, b, node) {
			var checked = [],
				_addNode = function(prop, value, parent){
					parent[prop] = value;
				};

			for (var prop in a) {
				if (typeof b[prop] == 'undefined') {
					_addNode(prop, '[[removed]]', node);
				} else if (JSON.stringify(a[prop]) != JSON.stringify(b[prop])) {
					// if value
					if (typeof b[prop] != 'object' || b[prop] == null) {
						_addNode(prop, b[prop], node);
					} else {
						// if array
						if (Ext.isArray(b[prop])) {
							_addNode(prop, [], node);
							go.util.recursiveDiff(a[prop], b[prop], node[prop]);
						}
						// if object
						else {
							_addNode(prop, {}, node);
							go.util.recursiveDiff(a[prop], b[prop], node[prop]);
						}
					}
				}
			}
		},

		/**
		 * Get the difference between 2 objects
		 * 
		 * @param {object} a
		 * @param {object} b
		 * @return {Array}
		 */
		getDiff: function(a, b){
			var diff = (Ext.isArray(a) ? [] : {});
			go.util.recursiveDiff(a, b, diff);
			return diff;
		},
		
		mailto : function(config) {
			var email = config.email;
			
			if(config.name) {
				email = '"' + config.name.replace(/"/g, '\"') + '" <' + config.email + '>'; 
			}
			
			document.location = "mailto:" + email;
		},
		
		callto : function(config) {
			document.location = "tel:" + config.number;
		},
		
		streetAddress : function(config) {
			window.open("https://www.openstreetmap.org/search?query=" + encodeURIComponent(config.street + ", " +config.zipCode.replace(/ /g, '') + ", " + config.country));
		},
		
		showDate : function(date) {
			console.log("No date handler");
		},
		
		/**
		 * cfg.multiple: boolean allow selecting multi files
		 * cfg.accept: string mime type or file extensions to allow for selection
		 * cfg.directory: boolean allow directory upload
		 * cfg.autoUpload: boolean jmap upload file on select
		 * cfg.listeners: {
		 *   select => (files: File[]): callback to trigger when files are selected
		 *   upload => (response: Blob): response from server after every Upload completed (if autoUpload)
		 *   uploadComplete => () when all uploads are complete (if autoUpload)
		 *   scope: same as in ext
		 * @param {object} cfg
		 */
		openFileDialog: function(cfg) {
			if (!this.uploadDialog) {
				this.uploadDialog = document.createElement("input");
				this.uploadDialog.setAttribute("type", "file");
				this.uploadDialog.onchange = function (e) {
					if(cfg.listeners.select) { 
						cfg.listeners.select.call(cfg.listeners.scope||this, this.files); 
					}
					if(!cfg.autoUpload) {
						return
					}
					var uploadCount = this.files.length;
					for (var i = 0; i < this.files.length; i++) {
						go.Jmap.upload(this.files[i], {
							success: function(response) {
								if(cfg.listeners.upload) {
									cfg.listeners.upload.call(cfg.listeners.scope||this, response);
								}
								uploadCount--;
								if(uploadCount === 0 && cfg.listeners.uploadComplete) {
									cfg.listeners.uploadComplete.call(cfg.listeners.scope||this);
								}
							}
						});
					}
				};
			}
			this.uploadDialog.removeAttribute('webkitdirectory');
			this.uploadDialog.removeAttribute('directory');
			this.uploadDialog.removeAttribute('multiple');
			this.uploadDialog.setAttribute('accept', cfg.accept || '*/*');
			if(cfg.directory) {
				this.uploadDialog.setAttribute('webkitdirectory', true);
				this.uploadDialog.setAttribute('directory', true);
			}
			if(cfg.directory) {
				this.uploadDialog.setAttribute('multiple', true);
			}
			
			this.uploadDialog.click();
		}

	};
	


})();
