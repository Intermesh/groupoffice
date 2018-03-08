Ext.namespace('GO.base.upload');

GO.base.upload.PluploadButton = Ext.extend(Ext.Button, {
	constructor: function(config) {
			
		Ext.applyIf(config, {
			iconCls: 'btn-upload',
			text: GO.lang.upload,
			window_width: 640,
			window_height: 480,
			window_title: GO.lang.upload,
			clearOnClose: false, //clear queue after window is closed (actually window is hidden )		
			upload_config: {}
		});

		this.uploadpanel = new GO.base.upload.PluploadPanel(config.upload_config);
		
		var title = config.window_title || config.text || 'Upload files';
		
		title += " ("+GO.lang.strMax+": "+this.uploadpanel.max_file_size+")";

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
				scope: this
			}
		});

		this.handler = function () { 
			this.window.show(); 
			this.uploadpanel.doLayout();
		};
        
		GO.base.upload.PluploadButton.superclass.constructor.apply(this, arguments);
	}
});