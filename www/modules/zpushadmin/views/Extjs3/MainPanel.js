GO.zpushadmin.MainPanel = function(config){
	
	if(!config)
		config = {};
	
		this.centerPanel = new GO.zpushadmin.DevicesGrid({
		region:'center',
		id:'zpadmin-center-panel',
		border:true
	});
	
	this.devicePanel = new GO.zpushadmin.DevicePanel({
		region:'east',
		width:400,
		border:true
	});
	
	this.centerPanel.on("delayedrowselect",function(grid, rowIndex, r){
		this.devicePanel.load(r.data.id);
	}, this);

	config.tbar = new Ext.Toolbar({
		cls:'go-head-tb',
		items: [{
				xtype:'htmlcomponent',
				html:t("ActiveSync management", "zpushadmin"),
				cls:'go-module-title-tbar'
			},{
			itemId:'delete',
			iconCls: 'btn-delete',
			text: t("Delete"),
			cls: 'x-btn-text-icon',
			disabled:this.standardTbarDisabled,
			handler: function(){
				this.deleteSelected();
			},
			scope: this.centerPanel
		},{
			itemId:'settings',
			iconCls: 'btn-settings',
			text: t("Settings"),
			cls: 'x-btn-text-icon',
			disabled:this.standardTbarDisabled,
			handler: function(){
				this.showSettingsDialog();
			},
			scope: this.centerPanel
		},
		'-',
		new GO.form.SearchField({
			store: this.centerPanel.store,
			width:150
		})]
	});


	config.items=[
		this.centerPanel,
		this.devicePanel
	];	
	
	config.layout='border';

	GO.zpushadmin.MainPanel.superclass.constructor.call(this, config);	
};

Ext.extend(GO.zpushadmin.MainPanel, Ext.Panel, {

});

GO.moduleManager.addModule('zpushadmin', GO.zpushadmin.MainPanel, {
	title : t("ActiveSync management", "zpushadmin"),
	iconCls : 'go-tab-icon-zpushadmin',
	admin :true
});
