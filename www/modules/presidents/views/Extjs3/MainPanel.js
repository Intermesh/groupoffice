// Creates namespaces to be used for scoping variables and classes so that they are not global.
Ext.namespace('GO.presidents');

GO.presidents.MainPanel = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	this.centerPanel = new GO.presidents.PresidentsGrid({
		region:'center',
		id:'pm-center-panel',
		border:true
	});
	
	this.centerPanel.on("delayedrowselect",function(grid, rowIndex, r){
		this.eastPanel.load(r.data.id);		
	}, this);

	this.centerPanel.store.on('load', function(){
			this.eastPanel.reset();
	}, this);
	
	this.eastPanel = new GO.presidents.PresidentPanel({
		region:'east',
		id:'pm-east-panel',
		width:440,
		border:true
	});
	
	//Setup a toolbar for the grid panel
	config.tbar = new Ext.Toolbar({		
			cls:'go-head-tb',
			items: [
				{
					iconCls: 'btn-add',							
					text: GO.lang['cmdAdd'],
					cls: 'x-btn-text-icon',
					handler: function(){					
						this.centerPanel.showEditDialog();
					},
					scope: this
				},
				{
					iconCls: 'btn-delete',
					text: GO.lang['cmdDelete'],
					cls: 'x-btn-text-icon',
					handler: function(){
						this.centerPanel.deleteSelected();
					},
					scope: this
				}
			]
	});
	
	config.items=[
	this.centerPanel,
	this.eastPanel
	];	
	
	config.layout='border';
	GO.presidents.MainPanel.superclass.constructor.call(this, config);	
};

Ext.extend(GO.presidents.MainPanel, Ext.Panel, {});


/*
 * This will add the module to the main tabpanel filled with all the modules
 */
GO.moduleManager.addModule(
	'presidents', //Module alias
	GO.presidents.MainPanel, //The main panel for this module
	{
		title : 'Presidents', //Module name in startmenu
		iconCls : 'go-module-icon-presidents' //The css class with icon for startmenu
	}
);

/*
 * Add linkHandeler for linking models from this module to other GroupOffice models
 */
GO.linkHandlers["GO\\Presidents\\Model\\President"]=function(id){
	if(!GO.presidents.linkWindow){
		var presidentPanel = new GO.presidents.PresidentPanel();
		GO.presidents.linkWindow= new GO.LinkViewWindow({
			title: GO.presidents.lang.president,
			items: presidentPanel,
			presidentPanel: presidentPanel,
			closeAction:"hide"
		});
	}
	GO.presidents.linkWindow.presidentPanel.load(id);
	GO.presidents.linkWindow.show();
	return GO.presidents.linkWindow;
}

GO.linkPreviewPanels["GO\\Presidents\\Model\\President"]=function(config){
	config = config || {};
	return new GO.presidents.PresidentPanel(config);
}