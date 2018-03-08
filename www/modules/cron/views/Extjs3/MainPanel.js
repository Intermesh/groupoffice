//GO.cron.MainPanel = function(config){
//	
//	if(!config)
//		config = {};
//	
//	this.centerPanel = new GO.cron.CronGrid({
//		region:'center',
//		id:'cron-center-panel',
//		border:true
//	});
//	
//	//	this.cronPanel = new GO.cron.CronPanel({
//	//		region:'east',
//	//		width:400,
//	//		border:true
//	//	});
//	
//	//	this.centerPanel.on("delayedrowselect",function(grid, rowIndex, r){
//	//		this.devicePanel.load(r.data.id);
//	//	}, this);
//	//
//	config.items=[
//	this.centerPanel
//	//		,
//	//		this.devicePanel
//	];	
//	
//
//	config.tbar = new Ext.Toolbar({		
//		cls:'go-head-tb',
//		items: [{
//			xtype:'htmlcomponent',
//			html:GO.cron.lang.name,
//			cls:'go-module-title-tbar'
//		}]
//	});
//	
//	config.layout='border';
////	config.cls='go-white-bg';
//
//	GO.cron.MainPanel.superclass.constructor.call(this, config);	
//};
//
//Ext.extend(GO.cron.MainPanel, Ext.Panel, {
//
//	});

GO.moduleManager.addModule('cron', GO.cron.CronGrid, {
	title : GO.cron.lang.cronManager,
	iconCls : 'go-tab-icon-cron',
	admin :true
});
