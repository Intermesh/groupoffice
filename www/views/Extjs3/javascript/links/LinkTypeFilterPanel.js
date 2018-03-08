

GO.LinkTypeFilterPanel = function(config)
{
	if(!config)
	{
		config = {};
	}

	config.split=true;
	config.resizable=true;
	config.autoScroll=true;
	config.collapsible=false;
	//config.header=false;
//	config.collapseMode='mini';
	config.allowNoSelection=true;
	
	if(!config.title)
		config.title=t("Type");

	if(!config.filesupport) // Load only the models that can handle files then set to true else false
		config.filesupport = false;
	
	if(!config.for_links) // Load only the models that can handle files then set to true else false
		config.for_links = false;
	
	if(!config.filter_model_type_ids){
		config.filter_model_type_ids = [];
	}

	config.store = new GO.data.JsonStore({				
		fields: ['id','name','model', 'checked'],
		baseParams:{
			filesupport:config.filesupport,
			for_links: config.for_links,
			filter_model_type_ids:Ext.encode(config.filter_model_type_ids)
		},
		url:GO.url('search/modelTypes'),
		autoLoad:true
	});


//	config.store = config.store || GO.linkTypesStore;

	GO.LinkTypeFilterPanel.superclass.constructor.call(this, config);
}

Ext.extend(GO.LinkTypeFilterPanel, GO.grid.MultiSelectGrid,{
	setFileSupport : function(filesupport){
		this.store.baseParams.filesupport = filesupport;
		
		if(this.store.loaded) // Prevent double loading
			this.store.load();
	}
});

