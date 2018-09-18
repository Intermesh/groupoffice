/**
 * 
 * A add menu button for detail views. 
 * 
 * Each detailview panel component can have a property "addMenuItems". These
 * will be added to this menu button.
 */

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
		}, this);
	},
	
	addMenuItems : function(detailPanel, comp) {		
		if(!comp.addButtonItems) {
			return;
		}
		
		this.menu.add(comp.addButtonItems);
	}
	
});
