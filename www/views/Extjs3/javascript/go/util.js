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
		
		
		/*
		 * Search through group-office. 
		 * 
		 * Code can be found in go/modules/core/search/views/extjs3/Module.js
		 * @method search
		 * @param {string} query
		 */

	};

})();
