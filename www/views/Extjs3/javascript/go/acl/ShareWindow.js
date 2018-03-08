go.acl.ShareWindow = Ext.extend(go.Window, {
	
	width: 400,
	height: 600,
	
	layout: 'fit',
	
	initComponent: function() {
		
		this.aclPanel = new GO.grid.PermissionsPanel();
		
		this.items = [this.aclPanel];
		
		
		go.acl.ShareWindow.superclass.initComponent.call(this);
	},
	
	load : function(aclId) {
		
		this.aclPanel.setAcl(aclId);
		
		return this;
	}
});
