Ext.namespace('GO.base.upload');

GO.base.upload.PluploadMenuItem = Ext.extend(Ext.menu.Item, {
	constructor: function(config) {
			
		Ext.applyIf(config, {
			iconCls: 'ic-file-upload',
			text: t("Upload"),
			window_width: 640,
			window_height: 480,
			window_title: t("Upload"),
			clearOnClose: false, //clear queue after window is closed (actually window is hidden )		
			upload_config: {}
		});

		this.uploadpanel = new GO.base.upload.PluploadPanel(config.upload_config);
		
		var title = config.window_title || config.text || 'Upload files';
		
		title += " ("+t("Maximum")+": "+this.uploadpanel.max_file_size+")";
		

		this.window = new GO.Window({ 
			title: title,
			width: config.window_width || 640, 
			height: config.window_height || 380, 
			layout: 'fit', 
			items: this.uploadpanel, 
			closeAction: 'hide',
			listeners: {
				hide: function (window) {
					if ( this.clearOnClose ) {
						this.uploadpanel.onDeleteAll();
					}
				},
				show: function (window) {
						if (this.uploadpanel.uploader.features.triggerDialog && GO.settings.upload_quickselect) {
							var input = document.getElementById(this.uploadpanel.uploader.id + '_html5');
							if (input && !input.disabled) { // for some reason FF (up to 8.0.1 so far) lets to click disabled input[type=file]
								input.click();
							}
						}
				},
				scope: this
			}
		});

		this.handler = function () { 
			this.window.show(); 
			this.uploadpanel.doLayout();
			
		};
        
		GO.base.upload.PluploadMenuItem.superclass.constructor.apply(this, arguments);
	},
	lowerMaxFileSize: function(new_max_filesize) {
		
		if(new_max_filesize <= 0)
			new_max_filesize=1;// needs one bytes or plupload doesn't
		this.uploadpanel.max_quota_size = new_max_filesize
		var humanSize = (new_max_filesize==1) ? '0B': GO.util.format.fileSize(this.uploadpanel.max_file_size);
		
		if(typeof humanSize === 'undefined') {
			this.window.setTitle("("+t("Maximum")+": "+humanSize+")");
		} else {
			this.window.setTitle(t("Upload"));
		}
		
		if(this.uploadpanel.uploader){
			this.uploadpanel.uploader.settings.max_quota_size = this.uploadpanel.max_quota_size;
		}
		//this.render();
		//this.uploader.max_file_size = this.max_file_size;
	}
});
