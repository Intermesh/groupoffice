go.detail.addButton = Ext.extend(Ext.Button, {
	tooltip: t('Add'),
	iconCls: 'ic-add',
	menu:[],
	detailPanel: null,
	initComponent: function() {
		go.detail.addButton.superclass.initComponent.call(this);
		
		this.detailPanel.on('add', this.addMenuItems, this);
		
		this.detailPanel.on('load', function(dv) {			
			this.setDisabled(dv.data.permissionLevel < GO.permissionLevels.write);
		})
	},
	
	addMenuItems : function(detailPanel, comp) {		
		if(!comp.addButtonItems) {
			return;
		}
		
		this.menu.add(comp.addButtonItems);
	}
	
});
