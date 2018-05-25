go.Blob = {
	cfg: null,
	upload : function(file, cfg) {
		if(Ext.isEmpty(file))
			return;
		this.cfg = cfg;
		go.Blob.checkHash(file);
	},
	
	checkHash: function(file) {
		var cryptoObj = window.crypto || window.msCrypto;
		if(!cryptoObj || !cryptoObj.subtle) {
			return go.Blob.processFile(file);
		} 
		var cfg = this.cfg,
			 reader = new FileReader();
		reader.onloadend = function(e) {
			cryptoObj.subtle.digest('SHA-1',e.target.result).then(function (hash) {
				Ext.Ajax.request({
					url: go.User.uploadUrl,
					headers: {'X-BlobId': go.Blob.hex(hash)},
					method: 'GET',
					success: function(response, opts) {
						if(response.status == 204) {
							go.Blob.processFile(file);
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
	},
	hex: function(buffer) {
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
	},
	processFile: function(file) {
		if(!file.type) {
			go.Blob.inspectHeader(file);
		} else {
			go.Blob.startUpload(file);
		}	
	},
	// If file.type is not know by the browser inspect the file header to determine mimeType
	// https://en.wikipedia.org/wiki/List_of_file_signatures
	inspectHeader: function(file) {
		var fileReader = new FileReader();
		fileReader.onloadend = function(e) {
			var arr = (new Uint8Array(e.target.result)).subarray(0, 4),
			header = "";
			for(var i = 0; i < arr.length; i++) {
				header += arr[i].toString(16);
			}
			//console.log(header);
			switch (header) {
				case "89504e47":
					 type = "image/png";
					 break;
				case "47494638":
					 type = "image/gif";
					 break;
				case "ffd8ffe0":
				case "ffd8ffe1":
				case "ffd8ffe2":
				case "ffd8ffe3":
				case "ffd8ffe8":
					 type = "image/jpeg";
					 break;
				case "4f676753":
					var ext = go.util.contentTypeClass(null, file.name);
					if(ext === 'ogv') {
						type = "video/ogg";
					} else {
						type = 'audio/ogg';
					}
					break;
				
				default:
					 type = "unknown"; // Or you can use the blob.type as fallback
					 break;
			}
			go.Blob.startUpload(file, type);
		};
		fileReader.readAsArrayBuffer(file);
	},
	startUpload: function(file, type) {
		var cfg = this.cfg;
		type = type || file.type;
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
				'Content-Type': type,
				'X-File-LastModifed': Math.round(file['lastModified'] / 1000).toString()
			},
			xmlData: file, // just "data" wasn't available in ext
			scope:cfg.scope || this
		});
		
	}
};