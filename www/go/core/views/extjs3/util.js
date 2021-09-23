
go.print = function(tmpl, data) {
	var paper = document.getElementById('paper');

	paper.innerHTML = Ext.isEmpty(data) ? tmpl : tmpl.apply(data);
	Ext.isIE || Ext.isSafari ? document.execCommand('print') : window.print();

};

go.reload = function() {
	window.location.replace(window.location.pathname);
};

go.Colors = [
	'C62828', 'AD1457', '6A1B9A', '4527A0', '283593', '1565C0', '0277BD', '00838F',
	'00695C', '2E7D32', '558B2F', '9E9D24', 'F9A825', 'FF8F00', 'EF6C00', '424242'];

go.util =  (function () {
	var downloadFrame;


	return {

		clone: function(obj) {
			return JSON.parse(JSON.stringify(obj));
		},

		/**
		 * Grabs the first char of the first and last word.
		 *
		 * @param {string} name
		 * @returns {string}
		 */
		initials : function(name) {
			var parts = name.split(" "), l = parts.length;

			if(l > 2) {
				parts.splice(1, l - 2);
			}

			return parts.map(function(name){return name.substr(0,1).toUpperCase()}).join("");
		},

		/**
		 * Generate avatar for name with initials and color or use the blob if set.
		 *
		 * @param name
		 * @param blob
		 * @param content If empty initials will be generated from the name
		 * @returns {string}
		 */
		avatar: function(name, blob, content) {

			var style = '';
			if(!blob) {
				if(go.util.empty(content)) {
					content = this.initials(name);
				}

				for(var i=0,j=0; i<name.length; i++) {
					j += name.charCodeAt(i);
				}
				style = 'background-image:none;background-color: #'+go.Colors[j % go.Colors.length];
			} else {
				content = '&nbsp;';
				style = 'background-image: url(' + go.Jmap.thumbUrl(blob, {
					w: 40,
					h: 40,
					zc: 1
				}) + ')';
			}
			return '<span class="avatar" style="'+style+'" title="'+Ext.util.Format.htmlEncode(name)+'">'+content+'</span>';
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
		
		isEqual : function(a, b) {
			if(a === b) {
				return true;
			}
			
			if(Ext.isObject(a) && Ext.isObject(b) && JSON.stringify(a) === JSON.stringify(b) ) {
				return true;
			}
			
			return false;
		},

		empty: function (v) {

			if (!v)
			{
				return true;
			}
			if (v === '')
			{
				return true;
			}

			if (v === '0')
			{
				return true;
			}

			if (v === 'undefined')
			{
				return true;
			}

			if (v === 'null')
			{
				return true;
			}
			
			if(Ext.isArray(v) && !v.length) {
				return true;
			}
			return false;

		},

		/**
		 * Copy text to clip board
		 * 
		 * @param {string} text 
		 */
		copyTextToClipboard: function (text) {
			var al = document.activeElement;
			if (!navigator.clipboard) {				
				//fallback on workaround with textarea element
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
				if(al) {
					al.focus();
				}		
				return;
			}

			navigator.clipboard.writeText(text).then(function () {
				console.log('Async: Copying to clipboard was successful!');
				if(al) {
					al.focus();
				}
			}, function (err) {
				console.error('Async: Could not copy text: ', err);
			});
		},
		
		/**
		 * Launch email composer
		 * 
		 * @param {Object} config {name: "Merijn" email: "mschering@intermesh.nl", subject: "Hello", body: "Just saying hello!"}
		 * @return {undefined}
		 */
		mailto: function (config, event) {
			event.preventDefault();
			var email = config.email;

			if (config.name) {
				email = '"' + config.name.replace(/"/g, '\\"') + '" <' + config.email + '>';
			}

			document.location = "mailto:" + email;
		},

		callto: function (config, event) {
			event.preventDefault();
			document.location = "tel://" + config.number;
		},

		streetAddress: function (config) {

			var adr = config.street + " " + config.street2;			
			if(config.zipCode) {
				adr += ", " + config.zipCode.replace(/ /g, ''); 
			}
			if(config.country) {
				adr += ", " + config.country;
			}

			if(Ext.isSafari || Ext.isMac) {
				document.location = "http://maps.apple.com/?address=" + encodeURIComponent(adr);
			} else {
				window.open("https://www.google.com/maps/place/" + encodeURIComponent(adr));	
			}

			//window.open("https://www.openstreetmap.org/search?query=" + encodeURIComponent(config.street + ", " + config.zipCode.replace(/ /g, '') + ", " + config.country));
		},

		showDate: function (date) {
			console.log("No date handler", date);
		},
		
		/**
		 * cfg.multiple: boolean allow selecting multi files
		 * cfg.accept: string mime type or file extensions to allow for selection
		 * cfg.directory: boolean allow directory upload
		 * cfg.autoUpload: boolean jmap upload file on select
		 * cfg.listeners: {
		 *   select => (files: File[]): callback to trigger when files are selected
		 *   upload => (response: {blobId: "..."}): response from server after every Upload completed (if autoUpload)
		 *   uploadComplete => () when all uploads are complete (if autoUpload)
		 *   scope: same as in ext
		 *   
		 * @example
		 * 
		 * {
		 * 			iconCls: 'ic-computer',
		 * 			text: t("Upload"),
		 * 			handler: function() {
		 * 				go.util.openFileDialog({
		 * 					multiple: true,
		 * 					accept: "image/*",
		 * 					directory: true,
		 * 					autoUpload: true,
		 * 					listeners: {
		 * 						upload: function(response) {
		 * 							var img = '<img src="' + go.Jmap.downloadUrl(response.blobId) + '" alt="'+response.name+'" />';
		 * 							
		 * 							this.editor.focus();
		 * 							this.editor.insertAtCursor(img);
		 * 						},
		 * 						scope: this
		 * 					}
		 * 				});
		 * 			},
		 * 			scope: this
		 * 		}
		 *   
		 * @param {object} cfg
		 */
		openFileDialog: function(cfg) {
			//if (!this.uploadDialog) {
				const uploadDialog = document.createElement("input");
				uploadDialog.setAttribute("type", "file");
				uploadDialog.onchange = function (e) {
					
					var uploadCount = this.files.length, blobs = [];
					
					if(!uploadCount) {
						return;
					}
					
					if(this.cfg.listeners.select) { 
						this.cfg.listeners.select.call(this.cfg.listeners.scope||this, this.files); 
					}
					
					if(!this.cfg.autoUpload) {						
						return;
					}
					
					for (var i = 0; i < this.files.length; i++) {
						go.Jmap.upload(this.files[i], {
							success: function(response, file) {
								if(this.cfg.listeners.upload) {
									this.cfg.listeners.upload.call(this.cfg.listeners.scope||this, response);
								}
								if(cfg.directory) {
									var path = file.webkitRelativePath.split('/');
									path.pop(); // filename
									response.subfolder = path;
								}
								blobs.push(response);
							},
							callback: function(response) {
								uploadCount--;
								if(uploadCount === 0 && this.cfg.listeners.uploadComplete) {
									this.cfg.listeners.uploadComplete.call(this.cfg.listeners.scope||this, blobs);
								}
							},
							scope: this
						});
					}
					
					this.value = "";
				};
		//}
			uploadDialog.cfg = cfg;
			uploadDialog.removeAttribute('webkitdirectory');
			uploadDialog.removeAttribute('directory');
			uploadDialog.removeAttribute('multiple');

			if(cfg.accept) {
				uploadDialog.setAttribute('accept', cfg.accept);
			}else
			{
				uploadDialog.removeAttribute('accept');
			}

			if(cfg.directory) {
				uploadDialog.setAttribute('webkitdirectory', true);
				uploadDialog.setAttribute('directory', true);
			}
			if(cfg.multiple) {
				uploadDialog.setAttribute('multiple', true);
			}
			
			uploadDialog.click();
		},

		viewFile : function(url) {
			window.open(url);
		},

		/**
		 * Download an URL
		 *
		 * @param {string} url
		 */
		downloadFile: function(url) {
			if(window.navigator.standalone) {
				//somehow this is the only way a download works on a web application on the iphone.
				var win = window.open( "about:blank", "_system");
				win.focus();
				win.location = url;
			} else
			{
				// document.location.href = url; //This causes connection errors with SSE or other simulanous XHR requests
				if(!downloadFrame) {
					// downloadFrame = document.createElement('iframe');
					// downloadFrame.id="downloader";
					// downloadFrame.style.display = 'none';
					// document.body.appendChild(downloadFrame);
					var downloadFrame = document.createElement('a');
					downloadFrame.target = '_blank';
					downloadFrame.toggleAttribute("download");

				}
				//downloadFrame.src = url;
				downloadFrame.href = url;
				downloadFrame.click();
			}

		},
		
		textToHtml : function(text) {
			if(!text) {
				return text;
			}
			return Ext.util.Format.nl2br(
				Autolinker.link(
					Ext.util.Format.htmlEncode(text),
					{stripPrefix: false, stripTrailingSlash: false, className: "normal-link", newWindow: true, phone: false}
					)
			);
		},

		htmlToText: function(html) {
			let doc = new DOMParser().parseFromString(html, 'text/html');
			return doc.body.textContent || "";
		},
		
		addSlashes : function( str ) {
			return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
		},
		
		
		/*
			* Search through group-office. 
			* 
			* Code can be found in go/modules/core/search/views/extjs3/Module.js
			* @method search
			* @param {string} query
			*/
		
		
	/**
	 * Export an entity to a file
	 *
	 * @see go.modules.community.addressbook.MainPanel for an example
	 *
	 * @param {string} entity eg. "Contact"
	 * @param {string} queryParams eg. Ext.apply(this.grid.store.baseParams, this.grid.store.lastOptions.params, {limit: 0, start: 0})
	 * @param {string} extension eg "vcf", "csv" or "json"
	 * @param {object} params Extra params to send to the export method on the server.
	 * @return {undefined}
	 */
	exportToFile: function (entity, queryParams, extension, params) {
		


		function doExport(columns) {
			Ext.getBody().mask(t("Exporting..."));
			const promise = go.Jmap.request({
				method: entity + "/query",
				params: queryParams
			});

			let params = {
				extension: extension,
				"#ids": {
					resultOf: promise.callId,
					path: "/ids"
				}
			}

			if(columns)
			{
				params.columns = columns;
			}

			return go.Jmap.request({
				method: entity + "/export",
				params: params
			}).then(function (response) {
				go.util.downloadFile(go.Jmap.downloadUrl(response.blobId));
			}).catch(function(response) {
				Ext.MessageBox.alert(t("Error"), response.message);
			}).finally(function() {
				Ext.getBody().unmask();
			})
		}

		if(extension == 'csv' || extension == 'xlsx') {
			const win = new go.import.ColumnSelectDialog({
				entity: entity,
				handler: doExport,
				scope: this
			});
			win.show();
		} else
		{
			doExport();
		}

	},

	/**
	 * Import a file
	 *
	 * @see go.modules.community.addressbook.MainPanel for an example
	 * 
	 * @param {string} entity eg. "Contact"
	 * @param {string} accept File types to accept. eg. ".csv, .vcf, text/vcard",
	 * @param {object} values Extra values to apply to all imported items. eg. {addressBookId: 1}
	 * @param {object} options Options that can be used by importers. For CSV you can provide labels. {labels: {propName: "Label"}}
	 *
	 * @return {void}
	 */
	importFile : function(entity, accept, values, options) {
		go.util.openFileDialog({
			multiple: true,
			accept: accept,
			directory: false,
			autoUpload: true,
			scope: this,
			listeners: {
				upload: function (response) {
					Ext.getBody().mask(t("Importing..."));


					if(response.name.toLowerCase().substr(-3) == 'csv' || response.name.toLowerCase().substr(-4) == 'xlsx') {
						Ext.getBody().unmask();

						var dlg = new go.import.CsvMappingDialog({
							entity: entity,
							blobId: response.blobId,
							values: values,
							fields: options.fields || {},
							aliases: options.aliases || {},
							lookupFields: options.lookupFields || {id: "ID"}
						});
						dlg.show();
					}else {
						go.Jmap.request({
							method: entity + "/import",
							params: {
								blobId: response.blobId,
								values: values
							},
							callback: function (options, success, response) {

								Ext.getBody().unmask();

								if (!success) {
									Ext.MessageBox.alert(t("Error"), response.message);
								} else {
									var msg = t("Imported {count} items").replace('{count}', response.count) + ". ";

									if (response.errors && response.errors.length) {
										msg += t("{count} items failed to import. A log follows: <br /><br />").replace('{count}', response.errors.length) + response.errors.join("<br />");
									}

									Ext.MessageBox.alert(t("Success"), msg);
								}

								// if (callback) {
								// 	callback.call(scope || this, response);
								// }
							},
							scope: this
						});
					}
				},
				scope: this
			}
		});
	},

	parseEmail : function(emails) {

		if(Ext.form.VTypes.emailAddress(emails)) {
			return [{
					name: "",
					email: emails
			}];
		}

		var re  = /(?:"?([A-Z]?[^<"]*)"?\s*)?<?([^>\s,]+)/g;
		var a = [];
		while (m = re.exec(emails)) {
			if(m[1]) { m[1] = m[1].trim(); }
			console.log("Name: "  + m[1]);
			console.log("Email: " + m[2]);

			a.push({
				name: m[1],
				email: m[2]
			});
		}

		return a;
	}
	};
})();

