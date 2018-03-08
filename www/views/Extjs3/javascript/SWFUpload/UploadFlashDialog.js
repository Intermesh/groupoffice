GO.UploadFlashDialog = function(config){
	
	if(!config)
	{
		config={};
	}

	this.uploadPanel = config.uploadPanel;

	config.items=[this.uploadPanel];
	config.layout='fit';
	config.maximizable=true;
	config.modal=false;
	config.width=500;
	config.height=400;
	config.resizable=false;	
	config.minizable=true;
	config.closeAction='hide';	
	config.html='items';
	config.buttons=[{	
		text: GO.lang.uploadFiles,
		handler: function()
		{
			if(this.uploadPanel.store.getCount()>0)
				this.uploadPanel.startUpload();
			else
				this.hide();
		},
		scope:this
	},{
		text: GO.lang.cmdCancel,
		handler: function()
		{
			this.uploadPanel.removeAllFiles();
			this.hide();
		},
		scope:this
	}];
	
	GO.UploadFlashDialog.superclass.constructor.call(this, config);

	this.addEvents({
		'fileUploadSuccess' : true,
		'fileUploadComplete' : true
	});

	this.uploadPanel.on('fileUploadSuccess', function(obj, file, data)
	{
		this.fireEvent('fileUploadSuccess', obj, file, data);
	},this)


	this.uploadPanel.on('fileUploadComplete', function()
	{
		this.removeAllFiles();
	});
	this.uploadPanel.on('fileUploadComplete', function()
	{
		this.fireEvent('fileUploadComplete');
		
		this.hide();
	},this);

	this.uploadPanel.on('swfUploadLoaded', function()
	{
	}, this)
	
}

Ext.extend(GO.UploadFlashDialog, Ext.Window,{
		
	show : function()
	{
		if(!this.rendered)
			this.render(Ext.getBody());
		
		GO.UploadFlashDialog.superclass.show.call(this);
	}
	
});