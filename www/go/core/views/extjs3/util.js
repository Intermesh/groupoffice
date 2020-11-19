
go.print = function(tmpl, data) {
	var paper = document.getElementById('paper');

	paper.innerHTML = Ext.isEmpty(data) ? tmpl : tmpl.apply(data);
	Ext.isIE || Ext.isSafari ? document.execCommand('print') : window.print();

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
			if (!this.uploadDialog) {
				this.uploadDialog = document.createElement("input");
				this.uploadDialog.setAttribute("type", "file");
				this.uploadDialog.onchange = function (e) {
					
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
			}
			this.uploadDialog.cfg = cfg;
			this.uploadDialog.removeAttribute('webkitdirectory');
			this.uploadDialog.removeAttribute('directory');
			this.uploadDialog.removeAttribute('multiple');

			if(cfg.accept) {
				this.uploadDialog.setAttribute('accept', cfg.accept);
			}else
			{
				this.uploadDialog.removeAttribute('accept');
			}

			if(cfg.directory) {
				this.uploadDialog.setAttribute('webkitdirectory', true);
				this.uploadDialog.setAttribute('directory', true);
			}
			if(cfg.multiple) {
				this.uploadDialog.setAttribute('multiple', true);
			}
			
			this.uploadDialog.click();
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
	 * @param {string} entity eg. "Contact"
	 * @param {string} queryParams eg. Ext.apply(this.grid.store.baseParams, this.grid.store.lastOptions.params, {limit: 0, start: 0})
	 * @param {string} extension eg "vcf", "csv" or "json"
	 * @param {object} params Extra params to send to the export method on the server.
	 * @return {undefined}
	 */
	exportToFile: function (entity, queryParams, extension, params) {
		
		Ext.getBody().mask(t("Exporting..."));
		var promise = go.Jmap.request({
			method: entity + "/query",
			params: queryParams
		});
		
		go.Jmap.request({
			method: entity + "/export",
			params: {
				extension: extension,
				"#ids": {
					resultOf: promise.callId,
					path: "/ids"
				}
			},
			scope: this,
			callback: function (options, success, response) {
				Ext.getBody().unmask();
				if(!success) {
					Ext.MessageBox.alert(t("Error"), response.message);				
				} else
				{
					go.util.downloadFile(go.Jmap.downloadUrl(response.blobId));
				}
			}
		})
	},

	/**
	 * Import a file
	 * 
	 * @param {string} entity eg. "Contact"
	 * @param {string} accept File types to accept. eg. ".csv, .vcf, text/vcard",
	 * @param {object} values Extra values to apply to all imported items. eg. {addressBookId: 1}
	 * @param {object} options Options that can be used by importers. For CSV you can provide labels. {labels: {propName: "Label"}}
	 *
	 * @example
	 *
	 * go.util.importFile(
												'Contact',
												".csv, .vcf, text/vcard",
												{addressBookId: this.addAddressBookId},
												{
													// These fields can be selected to update contacts if ID or e-mail matches
													lookupFields: {'id' : "ID", 'email': 'E-mail'},

													// This hash map is used to aid in auto selecting the right mappings. Key is possible header in CSV and value is property name in Group-Office
													aliases : {
														"Given name": "firstName",
														"First name": "firstName",

														"Middle name": "middleName",

														"Family Name": "lastName",
														"Last Name": "lastName",

														"Job Title": "jobTitle",
														"Suffix": "suffixes",
														"Web page" : {field: "urls[].url", fixed: {"type": "homepage"}},
														"Birthday" : {field: "dates[].date", fixed: {"type": "birthday"}},
														"Anniversary" : {field: "dates[].date", fixed: {"type": "anniversary"}},

														"E-mail 1 - Value": {field: "emailAddresses[].email", related: {"type": "E-mail 1 - Type"}},
														"email": {field: "emailAddresses[].email", fixed: {"type": "work"}},
														"E-mail Address": {field: "emailAddresses[].email", fixed: {"type": "work"}},
														"E-mail 2 Address": {field: "emailAddresses[].email", fixed: {"type": "work"}},
														"E-mail 3 Address": {field: "emailAddresses[].email", fixed: {"type": "work"}},

														"Primary Phone": {field: "phoneNumbers[].number", fixed: {"type": "work"}},
														"Home Phone": {field: "phoneNumbers[].number", fixed: {"type": "home"}},
														"Home Phone 2": {field: "phoneNumbers[].number", fixed: {"type": "home"}},

														"Business Phone": {field: "phoneNumbers[].number", fixed: {"type": "work"}},
														"Business Phone 2": {field: "phoneNumbers[].number", fixed: {"type": "work"}},

														"Mobile Phone": {field: "phoneNumbers[].number", fixed: {"type": "mobile"}},
														"Pager": {field: "phoneNumbers[].number", fixed: {"type": "other"}},
														"Home Fax": {field: "phoneNumbers[].number", fixed: {"type": "fax"}},

														"Other Phone": {field: "phoneNumbers[].number", fixed: {"type": "other"}},
														"Other Fax": {field: "phoneNumbers[].number", fixed: {"type": "fax"}},

														"Home Street": {
															field: "addresses[].street",
															fixed: {type: "home"},
															related: {
																street2: "Home Street 2",
																city: "Home City",
																state: "Home State",
																zipCode: "Home Postal Code",
																country: "Home Country"
															}
														},
														"Business Street": {
															field: "addresses[].street",
															fixed: {type: "work"},
								  							related: {
																street2: "Business Street 2",
																city: "Business City",
																state: "Business State",
																zipCode: "Business Postal Code",
																country: "Business Country"

															}
														},
														"Other Street": {
															field: "addresses[].street",
															fixed: {type: "other"},
															related: {
																street2: "Other Street 2",
																city: "Other City",
																state: "Other State",
																zipCode: "Other Postal Code",
																country: "Other Country"

															}
														},

														"Company" : "organizations"
													},

													// Fields with labels and possible subproperties.
													// For example e-mail and type of an array of e-mail addresses should be grouped together.
													fields: {
														prefixes: {label: t("Prefixes")},
														initials: {label: t("Initials")},
														salutation: {label: t("Salutation")},
														color: {label: t("Color")},
														firstName: {label: t("First name")},
														middleName: {label: t("Middle name")},
														lastName: {label: t("Last name")},
														name: {label: t("Name")},
														suffixes: {label: t("Suffixes")},
														gender: {label: t("Gender")},
														notes: {label: t("Notes")},
														isOrganization: {label: t("Is organization")},
														IBAN: {label: t("IBAN")},
														registrationNumber: {label: t("Registration number")},
														vatNo: {label: t("VAT number")},
														vatReverseCharge: {label: t("Reverse charge VAT")},
														debtorNumber: {label: t("Debtor number")},
														photoBlobId: {label: t("Photo blob ID")},
														language: {label: t("Language")},
														jobTitle: {label: t("Job title")},
														uid: {label: t("UUID")},
														starred: {label: t("Starred")},

														"emailAddresses": {
															label: t("E-mail address"),
															properties: {
																"email": {label: "E-mail"},
																"type": {label: t("Type")}
															}
														},

														"dates": {
															label: t("Dates"),
															properties: {
																"date": {label: "Date"},
																"type": {label: t("Type")}
															}
														},

														"dates": {
															label: t("Phone numbers"),
															properties: {
																"number": {label: "Number"},
																"type": {label: t("Type")}
															}
														},

														"urls": {
															label: t("URL's"),
															properties: {
																"url": {label: "URL"},
																"type": {label: t("Type")}
															}
														},

														"addresses": {
															label: t("Addresses"),
															properties: {
																"type": {label: t("Type")},
																"street": {label: t("Street")},
																"street 2": {label: t("Street 2")},
																"zipCode": {label: t("ZIP code")},
																"city": {label: t("City")},
																"state": {label: t("state")},
																"country": {label: t("Country")},
																"countryCode": {label: t("Country code")},
																"latitude": {label: t("Latitude")},
																"longitude": {label: t("Longitude")}
															}
														}

													}
												});
	 *
	 *
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


					if(response.name.toLowerCase().substr(-3) == 'csv') {
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

