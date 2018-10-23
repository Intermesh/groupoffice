Ext.namespace('GO.base.upload');

GO.base.upload.PluploadPanel = Ext.extend(Ext.ux.PluploadPanel, {
	
	constructor: function(config) {
		config = config || {};
		
		var maxFileSize = Math.floor(GO.settings.config.max_file_size/1024/1024)+'mb';
		Ext.applyIf(config, {
			url: GO.url('core/plupload'),
			//the only required parameter

			runtimes: 'html5,html4',
			// first available runtime will be used

			multipart: true,
			multipart_params: {
				param1: 1, 
				param2: 2
			},
			// works as baseParams for store. 
			// Accessible via this.uploader.settings.multipart_params after init
			// multipart must be true
			chunk_size: '2m',

			max_file_size: maxFileSize,
			max_quota_size: false,

	//		resize: {
	//			width: 640, 
	//			height: 480, 
	//			quality: 60
	//		},

//			flash_swf_url: BaseHref+'views/Extjs3/javascript/plupload/plupload/js/plupload.flash.swf',
	//		silverlight_xap_url: BaseHref+'views/Extjs3/javascript/plupload/plupload/js/plupload.silverlight.xap',
			// urls must be set properly or absent, otherwise uploader fail to initialize

			//			filters: [  {
			//				title : "Image files", 
			//				extensions : "jpg,JPG,gif,GIF,png,PNG"
			//			},
			//
			//			{
			//				title : "Zip files", 
			//				extensions : "zip,ZIP"
			//			},
			//
			//			{
			//				title : "Text files", 
			//				extensions : "txt,TXT"
			//			}
			//			],

			runtime_visible: GO.settings.config.debug, // show current runtime in statusbar

			// icon classes for toolbar buttons
			addButtonCls: 'btn-add',
			uploadButtonCls: 'btn-up',
			cancelButtonCls: 'btn-delete',
			deleteButtonCls: 'btn-delete',

			// localization
			addButtonText: t("Add"),
			uploadButtonText: t("Upload"),
			cancelButtonText: t("Cancel"),
			deleteButtonText: t("Remove"),
			deleteSelectedText: '<b>'+t("Remove selected")+'</b>',
			deleteUploadedText: t("Remove upload"),
			deleteAllText: t("Remove all"),

			statusQueuedText: t("Queued"),
			statusUploadingText: t("Uploading ({0}%)"),
			statusFailedText: '<span style="color: red">'+t("Failed")+'</span>',
			statusDoneText: '<span style="color: green">'+t("Done")+'</span>',

			statusInvalidSizeText: t("Too big"),
			statusInvalidExtensionText: t("Invalid file type"),

			emptyText: '<div class="plupload_emptytext"><span>'+t("Upload queue is empty")+'</span></div>',
			emptyDropText: '<div class="plupload_emptytext"><span>'+t("Drop files here")+'</span></div>',

			progressText: t("{0}/{1} ({3} failed) ({5}/s)")
		// params are number of
		// {0} files sent
		// {1} total files
		// {2} files successfully uploaded
		// {3} failed files
		// {4} files left in queue
		// {5} current upload speed 


		});

		GO.base.upload.PluploadPanel.superclass.constructor.call(this, config);
	},
	
	FilesAdded: function(uploader, files) {
		
		GO.base.upload.PluploadPanel.superclass.FilesAdded.call(this, uploader, files);
		
		if(GO.settings.upload_quickselect) {
			var fileSize = 0,
			max = uploader.settings.max_file_size;
			for(var i=0; i<files.length; i++) {
				fileSize += files[i].size;
			}
			// auto start after adding files

			setTimeout(function(){
				if(fileSize < max) {
					uploader.start();
				}
			},10);
		}
	}
	
});
