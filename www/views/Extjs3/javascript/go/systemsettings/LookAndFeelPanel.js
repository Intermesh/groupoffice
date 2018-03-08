go.systemsettings.LookAndFeelPanel = Ext.extend(Ext.Panel, {

	initComponent: function () {
	
		Ext.apply(this,{
			title:t('Look & feel'),
			autoScroll:true,
			iconCls: 'ic-style',
			layout:'column',
			items: [
				{
					columnWidth: .5,//left
					items:[]
				},{
					columnWidth: .5,//right
					items: []
				}
			]
		});
		
		go.systemsettings.LookAndFeelPanel.superclass.initComponent.call(this);
	}
});

GO.mainLayout.onReady(function(){
	go.systemSettingsDialog.addPanel('system-lookandfeel', go.systemsettings.LookAndFeelPanel);
});

