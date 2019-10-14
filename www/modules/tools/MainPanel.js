Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
	onRender : function(ct, position){
		this.el = ct.createChild({
			tag: 'iframe', 
			id: 'iframe-'+ this.id, 
			frameBorder: 0, 
			src: this.url
			});
	}
});


GO.tools.MainPanel = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	this.runPanel = new Ext.ux.IFrameComponent();
	this.runWindow = new Ext.Window({
		title:t("Script output", "tools"),
		width:500,
		height:500,
		maximizable:true,
		closeAction:'hide',
		items:this.runPanel,
		layout:'fit'
	});

//	this.cls='tools-panel';
	
	config.tbar = new Ext.Toolbar({		
		cls:'go-head-tb',
		items: [{
			xtype:'htmlcomponent',
			html:t("Admin tools", "tools"),
			cls:'go-module-title-tbar'
		}]
	});
	
		
	this.store = new GO.data.JsonStore({
		url:GO.url('tools/tools/store'),
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['name','description','script'],
		remoteSort: true
	});
	
	var scriptList = new GO.grid.SimpleSelectList({
//		title: t("Scripts", "tools"), 
		cls: 'simple-list',
		tpl:'<tpl for=".">'+
			'<div id="{dom_id}" class="go-item-wrap">{name}<span>{description}</span></div>'+
			'</tpl>',
		store: this.store
		});
		
	scriptList.on('click', function(dataview, index){
		/*this.runWindow.show();
				this.runPanel.el.set({'src' : dataview.store.data.items[index].data.script});*/
		window.open(dataview.store.data.items[index].data.script);				
	}, this);
		
	this.items=[
	scriptList
	];
	
	GO.tools.MainPanel.superclass.constructor.call(this, config);
	
}
 
Ext.extend(GO.tools.MainPanel, Ext.Panel,{
	afterRender: function(){
		this.store.load();
		GO.tools.MainPanel.superclass.afterRender.call(this);
	}	
});


/*
 * This will add the module to the main tabpanel filled with all the modules
 */
 
GO.moduleManager.addModule('tools', GO.tools.MainPanel, {
	title : t("Admin tools", "tools"),
	iconCls : 'go-tab-icon-tools',
	admin :true
});
